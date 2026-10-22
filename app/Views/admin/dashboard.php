<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dasbor Admin - Nilamku Booking') ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .stat-card { border: none; border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .table th { background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-diproses { background-color: #dbeafe; color: #1e40af; }
        .badge-siap { background-color: #cffafe; color: #0e7490; }
        .badge-selesai { background-color: #dcfce7; color: #166534; }
        .badge-dibatalkan { background-color: #fee2e2; color: #991b1b; }
        .nav-tabs .nav-link { font-weight: 500; color: #64748b; border: none; border-bottom: 3px solid transparent; }
        .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: transparent; }
    </style>
</head>
<body>
    <!-- Navbar Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/admin/dashboard">
                <i class="bi bi-cup-hot-fill text-warning"></i> Nilamku Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end align-items-center gap-3" id="navContent">
                <!-- Tombol Toggle Status Toko (Buka/Tutup) -->
                <form method="POST" action="/admin/store-status" class="m-0 d-inline-flex align-items-center">
                    <?= csrf_field() ?>
                    <?php if (($store_status ?? 'buka') === 'buka'): ?>
                        <input type="hidden" name="status" value="tutup">
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" title="Klik untuk mengubah status toko menjadi TUTUP" onclick="return confirm('Apakah Anda yakin ingin MENGUBAH status toko menjadi TUTUP? Pelanggan tidak akan bisa memesan.')">
                            <span class="spinner-grow spinner-grow-sm text-light" style="width: 0.65rem; height: 0.65rem;" role="status"></span>
                            <span>TOKO BUKA</span>
                            <span class="badge bg-white text-success ms-1 px-1.5 py-0.5" style="font-size: 0.68rem;">Ubah ke Tutup</span>
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="status" value="buka">
                        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" title="Klik untuk mengubah status toko menjadi BUKA" onclick="return confirm('Apakah Anda yakin ingin MENGUBAH status toko menjadi BUKA? Pelanggan akan dapat memesan kembali.')">
                            <i class="bi bi-x-circle-fill"></i>
                            <span>TOKO TUTUP</span>
                            <span class="badge bg-white text-danger ms-1 px-1.5 py-0.5" style="font-size: 0.68rem;">Ubah ke Buka</span>
                        </button>
                    <?php endif; ?>
                </form>

                <span class="navbar-text text-light d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle"></i>
                    <span>Halo, <strong><?= esc(session()->get('name') ?? 'Admin') ?></strong> <span class="badge bg-secondary ms-1"><?= esc(session()->get('role') ?? 'staff') ?></span></span>
                </span>
                <a href="/logout" class="btn btn-outline-danger btn-sm px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?= session()->getFlashdata('success') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?= session()->getFlashdata('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Baris Metrik Ringkasan -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm p-3 border-start border-4 border-primary bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Pesanan Hari Ini</div>
                            <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($stats['total_hari_ini'] ?? 0) ?></div>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm p-3 border-start border-4 border-warning bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Menunggu Konfirmasi</div>
                            <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($stats['total_pending'] ?? 0) ?></div>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm p-3 border-start border-4 border-success bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Total Pendapatan</div>
                            <div class="fs-3 fw-bold text-dark mt-1">Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.') ?></div>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card shadow-sm p-3 border-start border-4 border-info bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Total Menu Terdaftar</div>
                            <div class="fs-3 fw-bold text-dark mt-1"><?= count($items ?? []) ?></div>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                            <i class="bi bi-grid fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigasi Dasbor -->
        <ul class="nav nav-tabs mb-4 border-bottom" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2 pb-3" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                    <i class="bi bi-bag-check"></i>
                    <span>Manajemen Pesanan</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill ms-1"><?= count($orders ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2 pb-3" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu" type="button" role="tab">
                    <i class="bi bi-cup-straw"></i>
                    <span>Katalog Menu & Item</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?= count($items ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2 pb-3" id="finance-tab" data-bs-toggle="tab" data-bs-target="#finance" type="button" role="tab">
                    <i class="bi bi-cash-stack"></i>
                    <span>Laporan Keuangan</span>
                    <span class="badge bg-success-subtle text-success rounded-pill ms-1"><?= $financial_report['total_transaksi'] ?? 0 ?> Data</span>
                </button>
            </li>
        </ul>

        <!-- Konten Tab -->
        <div class="tab-content" id="adminTabContent">
            
            <!-- ── TAB 1: MANAJEMEN PESANAN ──────────────────────────── -->
            <div class="tab-pane fade show active" id="orders" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-list-check text-primary"></i> Daftar Antrean & Pesanan Aktif
                        </h5>
                        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload();">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                    </div>

                    <!-- Bulk Action Toolbar (Tampil saat pesanan dipilih) -->
                    <div id="bulkActionToolbar" class="bg-danger-subtle border-top border-bottom border-danger-subtle px-4 py-2.5 d-none justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" id="bulkSelectedCount">0</span>
                            <span class="small fw-bold text-danger-emphasis">Pesanan Selesai / Dibatalkan Dipilih</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light border shadow-xs" onclick="clearSelectedOrders()">
                                Batal Pilih
                            </button>
                            <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold" onclick="submitBulkDelete()">
                                <i class="bi bi-trash-fill"></i>
                                <span>Hapus Massal Terpilih</span>
                            </button>
                        </div>
                    </div>

                    <!-- Form Tersembunyi untuk Hapus Massal -->
                    <form method="POST" action="/admin/orders/delete-batch" id="formBulkDelete" class="d-none">
                        <?= csrf_field() ?>
                        <div id="bulkDeleteInputs"></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 pe-2 text-center" style="width: 44px;">
                                        <input type="checkbox" class="form-check-input" id="checkAllOrders" title="Pilih Semua Pesanan Selesai / Dibatalkan">
                                    </th>
                                    <th style="width: 95px;">Antrean</th>
                                    <th>Pemesan</th>
                                    <th>Layanan & Jadwal</th>
                                    <th>Pembayaran</th>
                                    <th>Detail Item</th>
                                    <th>Total Biaya</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4" style="width: 220px;">Ubah Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            Belum ada data pesanan yang masuk.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="ps-3 pe-2 text-center">
                                                <?php if (in_array($order->status, ['selesai', 'dibatalkan'], true)): ?>
                                                    <input type="checkbox" class="form-check-input order-bulk-cb" value="<?= $order->id ?>" data-antrian="<?= esc($order->nomor_antrian) ?>" title="Pilih #ORD-<?= $order->id ?> untuk dihapus massal">
                                                <?php else: ?>
                                                    <span class="text-muted opacity-25" title="Pesanan aktif belum dapat dihapus"><i class="bi bi-dash"></i></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="badge bg-primary fs-6 fw-bold px-2 py-1">
                                                    <?= esc($order->nomor_antrian ?? 'A-00' . $order->id) ?>
                                                </div>
                                                <div class="text-muted small mt-0.5">#<?= $order->id ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= esc($order->user_name ?? 'Tamu') ?></div>
                                                <div class="small text-muted d-flex align-items-center gap-1">
                                                    <i class="bi bi-telephone"></i> <?= esc($order->user_hp ?? '-') ?>
                                                </div>
                                                <?php if (! empty($order->catatan)): ?>
                                                    <div class="small text-muted fst-italic mt-1" title="Catatan pemesan">
                                                        <i class="bi bi-chat-left-text me-1"></i>"<?= esc($order->catatan) ?>"
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Badge Layanan -->
                                                <?php if (($order->metode_layanan ?? 'dine_in') === 'delivery'): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">
                                                        <i class="bi bi-bicycle me-1"></i> Delivery
                                                    </span>
                                                    <div class="small text-dark fw-medium text-truncate" style="max-width: 180px;" title="<?= esc($order->alamat_pengiriman ?? '') ?>">
                                                        <?= esc($order->alamat_pengiriman ?? '-') ?>
                                                    </div>
                                                    <div class="small text-muted" style="font-size: 0.72rem;">
                                                        Jarak: <?= number_format($order->jarak_km ?? 0, 1) ?> km | Ongkir: Rp <?= number_format($order->biaya_ongkir ?? 0, 0, ',', '.') ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-1">
                                                        <i class="bi bi-shop me-1"></i> Dine-in / Pick-up
                                                    </span>
                                                <?php endif; ?>

                                                <!-- Slot Pengambilan / Pemrosesan Langsung -->
                                                <div class="small text-muted mt-1">
                                                    <?php if (! empty($order->slot_mulai)): ?>
                                                        <i class="bi bi-clock me-1"></i><?= substr($order->slot_mulai, 0, 5) . '-' . substr($order->slot_selesai, 0, 5) ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 0.7rem;">
                                                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Langsung Diproses
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php if (($order->metode_pembayaran ?? 'cash') === 'transfer'): ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mb-1">
                                                            <i class="bi bi-wallet2 me-1"></i> DANA Transfer
                                                        </span>
                                                        <?php if (! empty($order->bukti_transfer)): ?>
                                                            <div>
                                                                <button type="button" class="btn btn-outline-primary btn-xs py-0.5 px-1.5" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $order->id ?>">
                                                                    <i class="bi bi-image me-1"></i> Bukti Transfer
                                                                </button>
                                                            </div>

                                                            <!-- Modal Bukti Transfer -->
                                                            <div class="modal fade" id="modalBukti<?= $order->id ?>" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h6 class="modal-title fw-bold">Bukti Transfer - Order #<?= $order->id ?> (<?= esc($order->nomor_antrian) ?>)</h6>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body text-center p-3">
                                                                            <img src="/<?= esc($order->bukti_transfer) ?>" class="img-fluid rounded shadow-sm" alt="Bukti Transfer">
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <a href="/<?= esc($order->bukti_transfer) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Buka Gambar Penuh</a>
                                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="small text-muted fst-italic" style="font-size: 0.72rem;">Belum ada bukti</div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border mb-1">
                                                            <i class="bi bi-cash me-1"></i> Tunai (Cash)
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (! empty($order->items)): ?>
                                                    <ul class="list-unstyled mb-0 small">
                                                        <?php foreach ($order->items as $it): ?>
                                                            <li>
                                                                <span class="fw-medium text-dark"><?= esc($it->item_nama ?? 'Item #' . $it->item_id) ?></span>
                                                                <span class="text-muted">&times; <?= $it->jumlah ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted small">Tidak ada item</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($order->status === 'dibatalkan'): ?>
                                                    <div class="text-decoration-line-through text-muted" style="font-size: 0.82rem;">
                                                        Rp <?= number_format($order->total_harga, 0, ',', '.') ?>
                                                    </div>
                                                    <div class="small fw-semibold text-danger d-flex align-items-center gap-1 mt-1">
                                                        <i class="bi bi-x-circle-fill" style="font-size: 0.75rem;"></i>
                                                        Bukan Pendapatan
                                                    </div>
                                                <?php else: ?>
                                                    <div class="fw-bold text-dark">
                                                        Rp <?= number_format($order->total_harga, 0, ',', '.') ?>
                                                    </div>
                                                    <?php if (($order->metode_layanan ?? 'dine_in') === 'delivery' && ! empty($order->biaya_ongkir)): ?>
                                                        <div class="text-muted" style="font-size: 0.72rem;">
                                                            Termasuk ongkir Rp <?= number_format($order->biaya_ongkir, 0, ',', '.') ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $statusClasses = [
                                                        'pending'    => 'badge-pending border border-warning-subtle',
                                                        'diproses'   => 'badge-diproses border border-primary-subtle',
                                                        'siap'       => 'badge-siap border border-info-subtle',
                                                        'selesai'    => 'badge-selesai border border-success-subtle',
                                                        'dibatalkan' => 'badge-dibatalkan border border-danger-subtle',
                                                    ];
                                                    $badgeClass = $statusClasses[$order->status] ?? 'bg-secondary text-light';
                                                ?>
                                                <span class="badge px-2.5 py-1.5 rounded-pill <?= $badgeClass ?> text-uppercase fw-semibold">
                                                    <?= $order->status ?>
                                                </span>
                                                <?php if ($order->status === 'dibatalkan' && !empty($order->alasan_batal)): ?>
                                                    <div class="small text-danger mt-1 text-truncate" style="max-width: 140px; font-size: 0.73rem;" title="Alasan: <?= esc($order->alasan_batal) ?>">
                                                        <i class="bi bi-info-circle me-1"></i><?= esc($order->alasan_batal) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if (in_array($order->status, ['selesai', 'dibatalkan'], true)): ?>
                                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                                        <span class="badge bg-light text-muted border px-2 py-1 small fw-normal">Selesai</span>
                                                        <form method="POST" action="/admin/orders/delete" class="d-inline" onsubmit="return confirm('Hapus pesanan #ORD-<?= $order->id ?> (<?= esc($order->nomor_antrian) ?>)?\n\nCatatan: Pendapatan dan rekap transaksi tetap tersimpan aman di Laporan Keuangan.');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1 d-inline-flex align-items-center gap-1" title="Hapus pesanan selesai agar antrean bersih">
                                                                <i class="bi bi-trash"></i>
                                                                <span class="d-none d-md-inline" style="font-size: 0.75rem;">Hapus</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Form Update Status Sesuai State Machine -->
                                                    <form method="POST" action="/admin/orders/update-status" class="d-inline-flex align-items-center gap-1 form-update-status" data-order-id="<?= $order->id ?>" data-order-info="#ORD-<?= $order->id ?> (<?= esc($order->nomor_antrian ?? '') ?>)">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                                        <select name="new_status" class="form-select form-select-sm" required style="width: 110px;">
                                                            <?php if ($order->status === 'pending'): ?>
                                                                <option value="diproses">Proses</option>
                                                                <option value="dibatalkan">Batalkan</option>
                                                            <?php elseif ($order->status === 'diproses'): ?>
                                                                <option value="siap">Siap</option>
                                                                <option value="dibatalkan">Batalkan</option>
                                                            <?php elseif ($order->status === 'siap'): ?>
                                                                <option value="selesai">Selesai</option>
                                                            <?php endif; ?>
                                                        </select>
                                                        <button type="submit" class="btn btn-primary btn-sm px-2" title="Simpan perubahan status">
                                                            <i class="bi bi-arrow-right-short"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: KATALOG MENU & ITEM ────────────────────────── -->
            <div class="tab-pane fade" id="menu" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-cup-straw text-primary"></i> Daftar Menu & Master Item
                        </h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahMenu">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Menu Baru
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 60px;">ID</th>
                                    <th style="width: 70px;">Foto</th>
                                    <th>Nama & Deskripsi</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Estimasi</th>
                                    <th>Status Ketersediaan</th>
                                    <th class="text-end pe-4" style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-cup-slash fs-2 d-block mb-2"></i>
                                            Belum ada item menu dalam katalog.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td class="ps-4 text-muted fw-bold"><?= $item->id ?></td>
                                            <td>
                                                <?php $thumbUrl = format_gdrive_image_url($item->foto_url); ?>
                                                <?php if ($thumbUrl): ?>
                                                    <a href="<?= esc($thumbUrl) ?>" target="_blank" title="Lihat Foto Ukuran Penuh">
                                                        <img src="<?= esc($thumbUrl) ?>" alt="<?= esc($item->nama) ?>" class="rounded-3 border shadow-xs object-fit-cover" style="width: 48px; height: 48px;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-light border rounded-3 d-flex align-items-center justify-content-center text-muted\' style=\'width: 48px; height: 48px;\'><i class=\'bi bi-image text-secondary opacity-50 fs-5\'></i></div>';">
                                                    </a>
                                                <?php else: ?>
                                                    <div class="bg-light border rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-image text-secondary opacity-50 fs-5"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= esc($item->nama) ?></div>
                                                <div class="small text-muted text-truncate" style="max-width: 300px;">
                                                    <?= esc($item->deskripsi ?? '-') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= esc($item->nama_kategori ?? 'Umum') ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                Rp <?= number_format($item->harga, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?= $item->durasi ? esc($item->durasi) . ' menit' : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($item->status_tersedia === 'tersedia'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">
                                                        <i class="bi bi-check-circle me-1"></i> Tersedia
                                                    </span>
                                                <?php elseif ($item->status_tersedia === 'habis'): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 rounded-pill">
                                                        <i class="bi bi-exclamation-circle me-1"></i> Habis
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5 rounded-pill">
                                                        <i class="bi bi-slash-circle me-1"></i> Nonaktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1">
                                                    <!-- Form Toggle Status Cepat -->
                                                    <form method="POST" action="/admin/items/toggle-status/<?= $item->id ?>">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Toggle Ketersediaan">
                                                            <i class="bi bi-power"></i>
                                                        </button>
                                                    </form>
                                                    <!-- Tombol Buka Modal Edit -->
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditMenu<?= $item->id ?>" title="Edit Menu">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <!-- Tombol Hapus Item -->
                                                    <form method="POST" action="/admin/items/delete/<?= $item->id ?>" 
                                                          onsubmit="return confirm('Hapus menu \"<?= esc($item->nama, 'js') ?>\"?\n\nMenu yang sudah dihapus tidak bisa dikembalikan.\nItem tidak dapat dihapus jika masih ada pesanan aktif yang mengandung menu ini.');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Menu Ini">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- MODAL EDIT MENU <?= $item->id ?> -->
                                                <div class="modal fade text-start" id="modalEditMenu<?= $item->id ?>" tabindex="-1" aria-labelledby="modalEditMenuLabel<?= $item->id ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="/admin/items/update/<?= $item->id ?>">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fw-bold" id="modalEditMenuLabel<?= $item->id ?>">Edit Menu #<?= $item->id ?></h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold">Nama Item <span class="text-danger">*</span></label>
                                                                        <input type="text" name="nama" class="form-control" value="<?= esc($item->nama) ?>" required>
                                                                    </div>
                                                                    <div class="row g-2 mb-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                                                                            <select name="category_id" class="form-select" required>
                                                                                <?php foreach ($categories as $cat): ?>
                                                                                    <option value="<?= $cat->id ?>" <?= $cat->id == $item->category_id ? 'selected' : '' ?>>
                                                                                        <?= esc($cat->nama_kategori) ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                                                            <input type="number" name="harga" class="form-control" value="<?= (int) $item->harga ?>" min="0" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row g-2 mb-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-semibold">Estimasi Durasi (menit)</label>
                                                                            <input type="number" name="durasi" class="form-control" value="<?= esc($item->durasi ?? '') ?>" placeholder="Misal: 5" min="1">
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label small fw-semibold">Status Ketersediaan</label>
                                                                            <select name="status_tersedia" class="form-select">
                                                                                <option value="tersedia" <?= $item->status_tersedia === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                                                                <option value="habis" <?= $item->status_tersedia === 'habis' ? 'selected' : '' ?>>Habis</option>
                                                                                <option value="nonaktif" <?= $item->status_tersedia === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                                                            <span><i class="bi bi-image text-primary me-1"></i> Foto Menu (Link Google Drive / URL)</span>
                                                                            <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.72rem;">Otomatis Muncul</span>
                                                                        </label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text bg-light text-primary"><i class="bi bi-google"></i></span>
                                                                            <input type="url" name="foto_url" id="edit_foto_url_<?= $item->id ?>" class="form-control gdrive-url-input" 
                                                                                   data-preview-target="edit_preview_<?= $item->id ?>" 
                                                                                   data-status-target="edit_status_<?= $item->id ?>"
                                                                                   value="<?= esc($item->foto_url ?? '') ?>" 
                                                                                   placeholder="https://drive.google.com/file/d/.../view?usp=sharing">
                                                                        </div>
                                                                        <div class="form-text small text-muted mt-1">
                                                                            <i class="bi bi-info-circle text-primary me-1"></i> <strong>Tips:</strong> Gunakan link share Google Drive. Pastikan izin akses diatur ke <em>"Siapa saja yang memiliki link"</em> (Anyone with the link).
                                                                        </div>

                                                                        <!-- Live Preview Box -->
                                                                        <div class="mt-2 p-2 border rounded-3 bg-light d-flex align-items-center gap-3">
                                                                            <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 56px; height: 56px;">
                                                                                <?php $editImg = format_gdrive_image_url($item->foto_url); ?>
                                                                                <img id="edit_preview_<?= $item->id ?>" src="<?= esc($editImg ?? '') ?>" alt="Preview" 
                                                                                     class="w-100 h-100 object-fit-cover <?= empty($editImg) ? 'd-none' : '' ?>"
                                                                                     onerror="handlePreviewError('edit_preview_<?= $item->id ?>', 'edit_status_<?= $item->id ?>');">
                                                                                <i class="bi bi-image text-secondary opacity-50 fs-4 <?= !empty($editImg) ? 'd-none' : '' ?>" id="edit_preview_<?= $item->id ?>_placeholder"></i>
                                                                            </div>
                                                                            <div class="small flex-grow-1" id="edit_status_<?= $item->id ?>">
                                                                                <?php if (! empty($editImg)): ?>
                                                                                    <div class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Foto menu terhubung</div>
                                                                                    <div class="text-muted" style="font-size: 0.72rem;">Gambar siap ditampilkan di katalog.</div>
                                                                                <?php else: ?>
                                                                                    <div class="text-muted"><i class="bi bi-question-circle me-1"></i> Belum ada foto. Masukkan link Google Drive di atas.</div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold">Deskripsi</label>
                                                                        <textarea name="deskripsi" class="form-control" rows="3"><?= esc($item->deskripsi ?? '') ?></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm px-3">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── TAB 3: LAPORAN KEUANGAN ────────────────────────── -->
            <div class="tab-pane fade" id="finance" role="tabpanel">
                <!-- Filter Bar & Summary Header -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center justify-content-between">
                            <div class="col-md-6">
                                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <i class="bi bi-cash-stack text-success fs-4"></i> Laporan Keuangan & Buku Kas
                                </h5>
                                <p class="text-muted small mb-0">
                                    Rekap pendapatan permanen. Total pendapatan tetap aman tersimpan meskipun pesanan selesai dihapus dari tabel antrean aktif.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" action="/admin/dashboard" class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                                    <div class="input-group input-group-sm" style="max-width: 250px;">
                                        <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar3"></i></span>
                                        <input type="date" name="filter_date" id="financeDateInput" class="form-control" value="<?= esc($filter_date ?? '') ?>" title="Pilih tanggal transaksi">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1 shadow-xs">
                                        <i class="bi bi-filter"></i> Filter
                                    </button>
                                    <?php if (! empty($filter_date)): ?>
                                        <a href="/admin/dashboard?filter_date=all#finance" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" title="Lihat rekap seluruh tanggal">
                                            <i class="bi bi-x-circle"></i> Semua
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="filterTodayFinance()">
                                            Hari Ini
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <?php if (! empty($filter_date)): ?>
                            <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fw-semibold">
                                    <i class="bi bi-calendar-check me-1"></i> Filter Aktif: <?= date('d F Y', strtotime($filter_date)) ?>
                                </span>
                                <span class="small text-muted">Hanya menampilkan transaksi keuangan yang tercatat pada tanggal ini.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KPI Cards Laporan Keuangan -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card shadow-sm p-3 border-start border-4 border-success bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">
                                        <?= ! empty($filter_date) ? 'Omzet Tanggal Ini' : 'Total Seluruh Omzet' ?>
                                    </div>
                                    <div class="fs-4 fw-bold text-success mt-1">
                                        Rp <?= number_format($financial_report['total_pendapatan'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                    <i class="bi bi-wallet2 fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card shadow-sm p-3 border-start border-4 border-primary bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Jumlah Transaksi</div>
                                    <div class="fs-4 fw-bold text-dark mt-1">
                                        <?= number_format($financial_report['total_transaksi'] ?? 0) ?> Transaksi
                                    </div>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                    <i class="bi bi-receipt fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card shadow-sm p-3 border-start border-4 border-info bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Penjualan Menu</div>
                                    <div class="fs-4 fw-bold text-dark mt-1">
                                        Rp <?= number_format($financial_report['total_menu'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                                    <i class="bi bi-cup-hot fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card stat-card shadow-sm p-3 border-start border-4 border-warning bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold">Total Ongkos Kirim</div>
                                    <div class="fs-4 fw-bold text-dark mt-1">
                                        Rp <?= number_format($financial_report['total_ongkir'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                    <i class="bi bi-bicycle fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat Keuangan -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text text-primary"></i> Rincian Buku Kas Transaksi
                            <?php if (! empty($filter_date)): ?>
                                <span class="badge bg-secondary-subtle text-secondary ms-1 fw-normal">Tanggal <?= esc($filter_date) ?></span>
                            <?php endif; ?>
                        </h6>
                        <span class="small text-muted">
                            Total <?= count($financial_report['records'] ?? []) ?> transaksi tercatat
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">No</th>
                                    <th style="width: 140px;">Waktu & Tanggal</th>
                                    <th style="width: 110px;">Antrean / ID</th>
                                    <th>Pelanggan</th>
                                    <th>Rincian Item Menu</th>
                                    <th style="width: 110px;">Layanan</th>
                                    <th class="text-end" style="width: 110px;">Ongkir</th>
                                    <th class="text-end pe-4" style="width: 150px;">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($financial_report['records'])): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                            <?= ! empty($filter_date) 
                                                ? "Tidak ada catatan transaksi keuangan pada tanggal " . esc($filter_date) . "." 
                                                : "Belum ada catatan transaksi keuangan yang tersimpan." ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($financial_report['records'] as $rec): ?>
                                        <tr>
                                            <td class="ps-4 text-muted small"><?= $no++ ?></td>
                                            <td>
                                                <div class="fw-bold text-dark small"><?= date('d M Y', strtotime($rec->tanggal)) ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem;"><?= substr($rec->waktu, 0, 5) ?> WIB</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">
                                                    <?= esc($rec->nomor_antrian ?? ('ORD-' . $rec->order_id)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= esc($rec->nama_pelanggan) ?></div>
                                                <div class="text-muted" style="font-size: 0.72rem;"><?= esc($rec->no_wa ?? '-') ?></div>
                                            </td>
                                            <td>
                                                <?php if (! empty($rec->items_decoded)): ?>
                                                    <ul class="list-unstyled mb-0 small" style="font-size: 0.82rem;">
                                                        <?php foreach ($rec->items_decoded as $item): ?>
                                                            <li>
                                                                <span class="fw-medium text-dark"><?= esc($item['nama'] ?? 'Item') ?></span>
                                                                <span class="text-muted">&times; <?= $item['jumlah'] ?? 1 ?></span>
                                                                <span class="text-muted ms-1">(Rp <?= number_format($item['subtotal'] ?? 0, 0, ',', '.') ?>)</span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">Menu Paket / Pesanan #<?= $rec->order_id ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($rec->metode_layanan ?? 'dine_in') === 'delivery'): ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                        <i class="bi bi-bicycle me-1"></i> Delivery
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border">
                                                        <i class="bi bi-shop me-1"></i> Dine-in
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-muted small">
                                                Rp <?= number_format($rec->biaya_ongkir ?? 0, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="fw-bold text-success fs-6">
                                                    Rp <?= number_format($rec->total_pendapatan ?? 0, 0, ',', '.') ?>
                                                </div>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-1.5" style="font-size: 0.65rem;">
                                                    <i class="bi bi-shield-check"></i> Permanen
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH MENU BARU -->
    <div class="modal fade" id="modalTambahMenu" tabindex="-1" aria-labelledby="modalTambahMenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="/admin/items/create">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTambahMenuLabel">
                            <i class="bi bi-plus-circle text-primary me-1"></i> Tambah Menu Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Item <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Es Kieta Pandan" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat->id ?>"><?= esc($cat->nama_kategori) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga" class="form-control" placeholder="15000" min="0" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Estimasi Durasi (menit)</label>
                                <input type="number" name="durasi" class="form-control" placeholder="5" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status Ketersediaan</label>
                                <select name="status_tersedia" class="form-select">
                                    <option value="tersedia" selected>Tersedia</option>
                                    <option value="habis">Habis</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-image text-primary me-1"></i> Foto Menu (Link Google Drive / URL)</span>
                                <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.72rem;">Otomatis Muncul</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="bi bi-google"></i></span>
                                <input type="url" name="foto_url" id="create_foto_url" class="form-control gdrive-url-input" 
                                       data-preview-target="create_preview" 
                                       data-status-target="create_status"
                                       placeholder="https://drive.google.com/file/d/.../view?usp=sharing">
                            </div>
                            <div class="form-text small text-muted mt-1">
                                <i class="bi bi-info-circle text-primary me-1"></i> <strong>Tips:</strong> Gunakan link share Google Drive. Pastikan izin akses diatur ke <em>"Siapa saja yang memiliki link"</em> (Anyone with the link).
                            </div>

                            <!-- Live Preview Box -->
                            <div class="mt-2 p-2 border rounded-3 bg-light d-flex align-items-center gap-3">
                                <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 56px; height: 56px;">
                                    <img id="create_preview" src="" alt="Preview" class="w-100 h-100 object-fit-cover d-none"
                                         onerror="handlePreviewError('create_preview', 'create_status');">
                                    <i class="bi bi-image text-secondary opacity-50 fs-4" id="create_preview_placeholder"></i>
                                </div>
                                <div class="small flex-grow-1" id="create_status">
                                    <div class="text-muted"><i class="bi bi-question-circle me-1"></i> Masukkan link Google Drive untuk melihat pratinjau langsung.</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Penjelasan singkat menu..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Tambah Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Batalkan Pesanan dengan Alasan Wajib -->
    <div class="modal fade" id="modalBatalPesanan" tabindex="-1" aria-labelledby="modalBatalPesananLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="POST" action="/admin/orders/update-status" id="formBatalPesanan">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" id="batalOrderId" value="">
                    <input type="hidden" name="new_status" value="dibatalkan">

                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalBatalPesananLabel">
                            <i class="bi bi-x-circle-fill"></i> Batalkan Pesanan Pelanggan
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-danger-subtle border border-danger-subtle text-danger-emphasis rounded-3 p-3 mb-3 small">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian:</div>
                            Alasan pembatalan ini akan <strong>langsung muncul sebagai pop-up</strong> di layar pelacakan pelanggan yang bersangkutan secara transparan.
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark fw-bold small">ID Pesanan & Antrean:</label>
                            <input type="text" class="form-control bg-light fw-bold text-danger" id="batalOrderInfo" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="inputAlasanBatal" class="form-label text-dark fw-bold small">
                                Alasan Pembatalan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="alasan_batal" id="inputAlasanBatal" rows="3" required placeholder="Contoh: Stok bahan menu pilihan Anda habis mendadak, mohon maaf..."></textarea>
                            <div class="form-text small mt-1">Pilih salah satu template cepat berikut jika perlu:</div>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 rounded-pill small template-alasan-btn" data-template="Stok bahan menu pilihan Anda telah habis untuk hari ini.">
                                    + Stok Habis
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 rounded-pill small template-alasan-btn" data-template="Alamat pengiriman berada di luar radius pengantaran kurir kami.">
                                    + Di Luar Radius
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 rounded-pill small template-alasan-btn" data-template="Bukti transfer tidak valid atau dana belum masuk ke rekening kedai.">
                                    + Bukti Transfer Tidak Sesuai
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 rounded-pill small template-alasan-btn" data-template="Kedai mendadak tutup sementara karena kendala operasional darurat.">
                                    + Operasional Darurat
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 rounded-pill small template-alasan-btn" data-template="Pembatalan diajukan dan disetujui atas permintaan pelanggan.">
                                    + Permintaan Pelanggan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 btn-sm fw-bold shadow-sm" id="btnSubmitBatalPesanan">
                            <i class="bi bi-x-circle me-1"></i> Konfirmasi Batalkan Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Massal -->
    <div class="modal fade" id="modalConfirmBulkDelete" tabindex="-1" aria-labelledby="modalConfirmBulkDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalConfirmBulkDeleteLabel">
                        <i class="bi bi-trash-fill"></i> Konfirmasi Hapus Massal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-3 mb-2" style="width: 64px; height: 64px;">
                            <i class="bi bi-trash3-fill fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Hapus <span id="confirmBulkCountText" class="text-danger">0</span> Pesanan Terpilih?</h5>
                        <p class="text-muted small mb-0">Pesanan yang berstatus selesai atau dibatalkan akan dihapus dari daftar antrean aktif.</p>
                    </div>

                    <div class="alert alert-success-subtle border border-success-subtle text-success-emphasis rounded-3 p-3 mb-0 small d-flex gap-2">
                        <i class="bi bi-shield-check fs-5 text-success flex-shrink-0"></i>
                        <div>
                            <strong>Pendapatan Tetap Terjamin Aman:</strong><br>
                            Seluruh histori transaksi dan total pendapatan tetap tercatat permanen di menu <strong>Laporan Keuangan & Buku Kas</strong>.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 btn-sm fw-bold shadow-sm" id="btnExecuteBulkDelete" onclick="executeConfirmedBulkDelete()">
                        <i class="bi bi-trash-fill me-1"></i> Ya, Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Konversi URL Google Drive ke URL Direct Embed CDN Google
        function convertGdriveUrlClient(url) {
            if (!url) return '';
            url = url.trim();
            if (url.includes('lh3.googleusercontent.com/d/')) return url;

            // Pattern 1: drive.google.com/file/d/{id}
            const m1 = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i);
            if (m1 && m1[1]) return 'https://lh3.googleusercontent.com/d/' + m1[1];

            // Pattern 2: parameter ?id={id} atau &id={id}
            const m2 = url.match(/(?:drive|docs)\.google\.com\/.*[?&]id=([a-zA-Z0-9_-]+)/i);
            if (m2 && m2[1]) return 'https://lh3.googleusercontent.com/d/' + m2[1];

            // Pattern 3: drive.google.com/open?id={id}
            const m3 = url.match(/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/i);
            if (m3 && m3[1]) return 'https://lh3.googleusercontent.com/d/' + m3[1];

            return url;
        }

        // Handler jika gambar gagal dimuat
        function handlePreviewError(previewId, statusId) {
            const img = document.getElementById(previewId);
            const placeholder = document.getElementById(previewId + '_placeholder');
            const status = document.getElementById(statusId);
            if (img) img.classList.add('d-none');
            if (placeholder) placeholder.classList.remove('d-none');
            if (status) {
                status.innerHTML = `
                    <div class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Gambar tidak dapat dimuat</div>
                    <div class="text-danger-emphasis" style="font-size: 0.72rem;">Pastikan izin berbagi (Share) file diatur ke <strong>"Siapa saja yang memiliki link"</strong> (Anyone with the link).</div>
                `;
            }
        }

        // Setup real-time listener pada semua input link gambar Google Drive
        function setupGdriveInputs() {
            document.querySelectorAll('.gdrive-url-input').forEach(input => {
                const previewId = input.getAttribute('data-preview-target');
                const statusId = input.getAttribute('data-status-target');
                const img = document.getElementById(previewId);
                const placeholder = document.getElementById(previewId + '_placeholder');
                const status = document.getElementById(statusId);

                const updatePreview = () => {
                    const rawVal = input.value.trim();
                    if (!rawVal) {
                        if (img) { img.src = ''; img.classList.add('d-none'); }
                        if (placeholder) placeholder.classList.remove('d-none');
                        if (status) status.innerHTML = '<div class="text-muted"><i class="bi bi-question-circle me-1"></i> Masukkan link Google Drive untuk melihat pratinjau langsung.</div>';
                        return;
                    }

                    // Deteksi jika pengguna menempelkan link folder
                    if (rawVal.includes('drive.google.com/drive/folders/')) {
                        if (img) { img.src = ''; img.classList.add('d-none'); }
                        if (placeholder) placeholder.classList.remove('d-none');
                        if (status) status.innerHTML = '<div class="text-warning-emphasis fw-semibold"><i class="bi bi-folder-x me-1"></i> Ini adalah link folder. Silakan buka file gambarnya dan salin link file tersebut.</div>';
                        return;
                    }

                    const directUrl = convertGdriveUrlClient(rawVal);
                    const isGdrive = directUrl.includes('lh3.googleusercontent.com/d/');

                    if (status) {
                        status.innerHTML = `<div class="text-muted small"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Memuat pratinjau...</div>`;
                    }

                    if (img) {
                        img.onload = () => {
                            img.classList.remove('d-none');
                            if (placeholder) placeholder.classList.add('d-none');
                            if (status) {
                                status.innerHTML = `
                                    <div class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> ${isGdrive ? 'Link Google Drive Terhubung!' : 'Gambar Berhasil Dimuat!'}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Gambar akan langsung muncul di antarmuka pelanggan dan dasbor.</div>
                                `;
                            }
                        };
                        img.onerror = () => {
                            handlePreviewError(previewId, statusId);
                        };
                        img.src = directUrl;
                    }
                };

                input.addEventListener('input', updatePreview);
                input.addEventListener('change', updatePreview);
                input.addEventListener('paste', () => setTimeout(updatePreview, 50));
            });
        }

        // Setup Batalkan Pesanan Modal Handler
        let cancelOrderModalInstance = null;

        function openCancelModal(orderId, orderInfo) {
            document.getElementById('batalOrderId').value = orderId;
            document.getElementById('batalOrderInfo').value = orderInfo;
            document.getElementById('inputAlasanBatal').value = '';
            
            const modalEl = document.getElementById('modalBatalPesanan');
            if (modalEl) {
                if (!cancelOrderModalInstance) {
                    cancelOrderModalInstance = new bootstrap.Modal(modalEl);
                }
                cancelOrderModalInstance.show();
            }
        }

        // Setup Bulk Actions (Pilih & Hapus Massal Pesanan Selesai / Dibatalkan)
        function updateBulkToolbar() {
            const checked = document.querySelectorAll('.order-bulk-cb:checked');
            const toolbar = document.getElementById('bulkActionToolbar');
            const countEl = document.getElementById('bulkSelectedCount');
            const checkAll = document.getElementById('checkAllOrders');
            const allCheckboxes = document.querySelectorAll('.order-bulk-cb');

            if (checked.length > 0) {
                toolbar.classList.remove('d-none');
                toolbar.classList.add('d-flex');
                countEl.innerText = checked.length;
            } else {
                toolbar.classList.add('d-none');
                toolbar.classList.remove('d-flex');
                countEl.innerText = '0';
            }

            if (allCheckboxes.length > 0 && checked.length === allCheckboxes.length) {
                checkAll.checked = true;
                checkAll.indeterminate = false;
            } else if (checked.length > 0) {
                checkAll.checked = false;
                checkAll.indeterminate = true;
            } else {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
        }

        function clearSelectedOrders() {
            document.querySelectorAll('.order-bulk-cb').forEach(cb => {
                cb.checked = false;
            });
            const checkAll = document.getElementById('checkAllOrders');
            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
            updateBulkToolbar();
        }

        let bulkDeleteModalInstance = null;

        function submitBulkDelete() {
            const checked = document.querySelectorAll('.order-bulk-cb:checked');
            if (checked.length === 0) {
                alert('Silakan pilih minimal 1 pesanan yang ingin dihapus.');
                return;
            }

            const countEl = document.getElementById('confirmBulkCountText');
            if (countEl) countEl.innerText = checked.length;

            const modalEl = document.getElementById('modalConfirmBulkDelete');
            if (modalEl) {
                if (!bulkDeleteModalInstance) {
                    bulkDeleteModalInstance = new bootstrap.Modal(modalEl);
                }
                bulkDeleteModalInstance.show();
            }
        }

        function executeConfirmedBulkDelete() {
            const checked = document.querySelectorAll('.order-bulk-cb:checked');
            if (checked.length === 0) return;

            const container = document.getElementById('bulkDeleteInputs');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('formBulkDelete').submit();
        }

        // Inisialisasi saat DOM siap
        document.addEventListener('DOMContentLoaded', () => {
            setupGdriveInputs();

            // Intercept form-update-status saat memilih "dibatalkan"
            document.querySelectorAll('.form-update-status').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const select = this.querySelector('select[name="new_status"]');
                    if (select && select.value === 'dibatalkan') {
                        e.preventDefault();
                        const orderId = this.getAttribute('data-order-id');
                        const orderInfo = this.getAttribute('data-order-info');
                        openCancelModal(orderId, orderInfo);
                    }
                });
            });

            // Template Alasan Cepat di Modal Pembatalan
            document.querySelectorAll('.template-alasan-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tpl = this.getAttribute('data-template');
                    const textarea = document.getElementById('inputAlasanBatal');
                    if (textarea.value.trim() === '') {
                        textarea.value = tpl;
                    } else {
                        textarea.value += ' ' + tpl;
                    }
                    textarea.focus();
                });
            });

            // Listener Select All Checkbox
            const checkAllEl = document.getElementById('checkAllOrders');
            if (checkAllEl) {
                checkAllEl.addEventListener('change', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.order-bulk-cb').forEach(cb => {
                        cb.checked = isChecked;
                    });
                    updateBulkToolbar();
                });
            }

            // Listener Individual Checkbox
            document.querySelectorAll('.order-bulk-cb').forEach(cb => {
                cb.addEventListener('change', updateBulkToolbar);
            });

            // Aktifkan tab berdasarkan URL parameter filter_date atau URL hash (misal #menu atau #finance)
            const urlParams = new URLSearchParams(window.location.search);
            const hash = window.location.hash;
            if (urlParams.has('filter_date')) {
                const financeBtn = document.querySelector('button[data-bs-target="#finance"]');
                if (financeBtn) {
                    bootstrap.Tab.getOrCreateInstance(financeBtn).show();
                }
            } else if (hash) {
                const targetBtn = document.querySelector(`button[data-bs-target="${hash}"]`);
                if (targetBtn) {
                    bootstrap.Tab.getOrCreateInstance(targetBtn).show();
                }
            }

            // Simpan hash saat tab di-klik
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', (e) => {
                    window.location.hash = e.target.getAttribute('data-bs-target');
                });
            });
        });

        function filterTodayFinance() {
            const today = new Date().toISOString().split('T')[0];
            window.location.href = `/admin/dashboard?filter_date=${today}#finance`;
        }
    </script>
</body>
</html>
