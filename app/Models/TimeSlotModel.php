<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * TimeSlotModel
 *
 * Mengelola slot waktu pemesanan beserta kapasitasnya.
 * Method kritis: lockAndConsumeSlot() menggunakan SELECT ... FOR UPDATE
 * untuk mencegah race condition saat banyak user memesan slot yang sama secara bersamaan.
 */
class TimeSlotModel extends Model
{
    protected $table            = 'time_slots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kapasitas_maks',
        'kapasitas_terpakai',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tanggal'            => 'required|valid_date[Y-m-d]',
        'jam_mulai'          => 'required',
        'jam_selesai'        => 'required',
        'kapasitas_maks'     => 'required|integer|greater_than[0]|less_than[256]',
        'kapasitas_terpakai' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    // ──────────────────────────────────────────────────────────
    // Custom Methods
    // ──────────────────────────────────────────────────────────

    /**
     * Ambil semua slot yang MASIH TERSEDIA pada tanggal tertentu.
     * Slot dianggap tersedia jika kapasitas_terpakai < kapasitas_maks.
     *
     * @param string $date Format: Y-m-d
     * @return array<object>
     */
    public function getAvailableSlots(string $date): array
    {
        return $this->db->table($this->table)
                        ->select('id, tanggal, jam_mulai, jam_selesai, kapasitas_maks, kapasitas_terpakai,
                                  (kapasitas_maks - kapasitas_terpakai) AS sisa_kapasitas')
                        ->where('tanggal', $date)
                        ->where('kapasitas_terpakai <', $this->db->escape('kapasitas_maks'), false)
                        ->orderBy('jam_mulai', 'ASC')
                        ->get()
                        ->getResult();
    }

    /**
     * Ambil slot yang tersedia dengan kolom computed sisa_kapasitas.
     * Versi lebih bersih menggunakan raw WHERE.
     *
     * @param string $date Format: Y-m-d
     * @return array<object>
     */
    public function getAvailableSlotsForDate(string $date): array
    {
        return $this->db->query(
            "SELECT id, tanggal, jam_mulai, jam_selesai,
                    kapasitas_maks, kapasitas_terpakai,
                    (kapasitas_maks - kapasitas_terpakai) AS sisa_kapasitas
             FROM time_slots
             WHERE tanggal = ?
               AND kapasitas_terpakai < kapasitas_maks
             ORDER BY jam_mulai ASC",
            [$date]
        )->getResult();
    }

    /**
     * Ambil SEMUA slot untuk tanggal tertentu (termasuk yang penuh, dengan sisa_kapasitas & is_full).
     * Jika belum ada slot pada tanggal tersebut, otomatis generate slot default (09:00 - 17:00).
     *
     * @param string $date Format: Y-m-d
     * @return array<object>
     */
    public function getSlotsForDate(string $date): array
    {
        $slots = $this->db->query(
            "SELECT id, tanggal, jam_mulai, jam_selesai,
                    kapasitas_maks, kapasitas_terpakai,
                    GREATEST(0, (kapasitas_maks - kapasitas_terpakai)) AS sisa_kapasitas,
                    IF(kapasitas_terpakai >= kapasitas_maks, 1, 0) AS is_full
             FROM time_slots
             WHERE tanggal = ?
             ORDER BY jam_mulai ASC",
            [$date]
        )->getResult();

        if (empty($slots)) {
            // Auto-generate slot standar jika tanggal yang dipilih belum memiliki data
            $defaultSlots = [
                ['09:00:00', '09:30:00'],
                ['09:30:00', '10:00:00'],
                ['10:00:00', '10:30:00'],
                ['10:30:00', '11:00:00'],
                ['11:00:00', '11:30:00'],
                ['13:00:00', '13:30:00'],
                ['13:30:00', '14:00:00'],
                ['14:00:00', '14:30:00'],
                ['14:30:00', '15:00:00'],
                ['15:00:00', '15:30:00'],
                ['15:30:00', '16:00:00'],
                ['16:00:00', '16:30:00'],
                ['16:30:00', '17:00:00'],
            ];

            $batch = [];
            foreach ($defaultSlots as $s) {
                $batch[] = [
                    'tanggal'            => $date,
                    'jam_mulai'          => $s[0],
                    'jam_selesai'        => $s[1],
                    'kapasitas_maks'     => 5,
                    'kapasitas_terpakai' => 0,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ];
            }
            $this->db->table($this->table)->insertBatch($batch);

            return $this->getSlotsForDate($date);
        }

        return $slots;
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * CRITICAL METHOD — Dieksekusi di dalam Database Transaction
     * ═══════════════════════════════════════════════════════════
     *
     * lockAndConsumeSlot($slotId, $qty)
     *
     * Mengunci baris slot dengan SELECT ... FOR UPDATE (pessimistic locking)
     * agar tidak ada dua transaksi yang bisa memodifikasi kapasitas slot
     * yang sama secara bersamaan (race condition prevention).
     *
     * Alur eksekusi:
     *   1. SELECT ... FOR UPDATE  → lock baris, blokir transaksi konkuran
     *   2. Validasi kapasitas     → gagal jika kapasitas tidak cukup
     *   3. UPDATE kapasitas       → increment kapasitas_terpakai
     *
     * WAJIB dipanggil DALAM blok transStart()/transComplete() dari pemanggil!
     * FOR UPDATE tidak efektif di luar transaksi aktif.
     *
     * @param  int $slotId  ID slot yang ingin dikonsumsi
     * @param  int $qty     Jumlah kapasitas yang dikonsumsi (default: 1 per order)
     * @return bool         true jika berhasil, false jika kapasitas tidak cukup / slot tidak ada
     * @throws DatabaseException Jika terjadi kesalahan database
     */
    public function lockAndConsumeSlot(int $slotId, int $qty = 1): bool
    {
        // ── Step 1: SELECT ... FOR UPDATE ─────────────────────
        // Raw query wajib — CI4 Query Builder tidak mendukung FOR UPDATE secara native.
        // FOR UPDATE mengunci baris ini sampai transaksi selesai (COMMIT/ROLLBACK),
        // sehingga transaksi lain yang mencoba SELECT baris yang sama akan diblokir.
        $query = $this->db->query(
            'SELECT id, kapasitas_maks, kapasitas_terpakai
             FROM time_slots
             WHERE id = ?
             FOR UPDATE',
            [$slotId]
        );

        $slot = $query->getRow();

        // ── Step 2: Validasi keberadaan dan kapasitas slot ─────
        if ($slot === null) {
            log_message('warning', "[TimeSlotModel::lockAndConsumeSlot] Slot ID {$slotId} tidak ditemukan.");
            return false;
        }

        $sisa = (int) $slot->kapasitas_maks - (int) $slot->kapasitas_terpakai;

        if ($sisa < $qty) {
            log_message('notice', "[TimeSlotModel::lockAndConsumeSlot] Slot ID {$slotId} penuh. Sisa: {$sisa}, Diminta: {$qty}.");
            return false;
        }

        // ── Step 3: UPDATE kapasitas_terpakai secara atomik ───
        $updated = $this->db->table($this->table)
                            ->set('kapasitas_terpakai', "kapasitas_terpakai + {$qty}", false)
                            ->set('updated_at', date('Y-m-d H:i:s'))
                            ->where('id', $slotId)
                            // Double-check constraint: pastikan tidak melebihi kapasitas maks
                            ->where('(kapasitas_terpakai + ' . $qty . ') <= kapasitas_maks', null, false)
                            ->update();

        if (! $updated || $this->db->affectedRows() === 0) {
            log_message('warning', "[TimeSlotModel::lockAndConsumeSlot] UPDATE gagal untuk Slot ID {$slotId}. Kemungkinan race condition terdeteksi.");
            return false;
        }

        log_message('info', "[TimeSlotModel::lockAndConsumeSlot] Slot ID {$slotId} berhasil dikonsumsi. Qty: {$qty}.");
        return true;
    }

    /**
     * Kembalikan 1 kapasitas slot (digunakan saat order dibatalkan).
     * Juga dieksekusi dalam konteks transaksi dari pemanggil.
     *
     * @param int $slotId
     * @param int $qty
     * @return bool
     */
    public function releaseSlotCapacity(int $slotId, int $qty = 1): bool
    {
        return $this->db->table($this->table)
                        ->set('kapasitas_terpakai', "GREATEST(0, kapasitas_terpakai - {$qty})", false)
                        ->set('updated_at', date('Y-m-d H:i:s'))
                        ->where('id', $slotId)
                        ->update();
    }
}
