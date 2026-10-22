<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'setting_key',
        'setting_value',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil nilai pengaturan berdasarkan setting_key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $row = $this->where('setting_key', $key)->first();
        return $row ? $row->setting_value : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setSetting(string $key, string $value): bool
    {
        $existing = $this->where('setting_key', $key)->first();

        if ($existing) {
            return (bool) $this->update($existing->id, [
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return (bool) $this->insert([
            'setting_key'   => $key,
            'setting_value' => $value,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil status operasional toko ('buka' atau 'tutup'). Default: 'buka'.
     *
     * @return string
     */
    public function getStoreStatus(): string
    {
        $status = strtolower((string) $this->getSetting('store_status', 'buka'));
        return in_array($status, ['buka', 'tutup'], true) ? $status : 'buka';
    }

    /**
     * Ubah status operasional toko.
     *
     * @param string $status 'buka' | 'tutup'
     * @return bool
     */
    public function setStoreStatus(string $status): bool
    {
        $status = in_array(strtolower($status), ['buka', 'tutup'], true) ? strtolower($status) : 'buka';
        return $this->setSetting('store_status', $status);
    }

    /**
     * Beralih status toko (jika buka -> tutup, jika tutup -> buka).
     *
     * @return string Status baru setelah di-toggle
     */
    public function toggleStoreStatus(): string
    {
        $current = $this->getStoreStatus();
        $newStatus = ($current === 'buka') ? 'tutup' : 'buka';
        $this->setStoreStatus($newStatus);
        return $newStatus;
    }
}
