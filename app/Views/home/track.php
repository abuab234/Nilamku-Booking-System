<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Lacak Pesanan - Nilamku') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .track-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            background: white;
        }

        /* Status Stepper Timeline */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .stepper-wrapper::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 20px;
            right: 20px;
            height: 4px;
            background-color: #e2e8f0;
            z-index: 1;
        }

        .stepper-progress {
            position: absolute;
            top: 24px;
            left: 20px;
            height: 4px;
            background-color: #0d6efd;
            z-index: 2;
            transition: width 0.4s ease;
        }

        .step-item {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 25%;
        }

        .step-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            border: 3px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .step-item.completed .step-circle {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .step-item.active .step-circle {
            background-color: white;
            border-color: #0d6efd;
            color: #0d6efd;
            box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.2);
            animation: pulse-ring 2s infinite;
        }

        .step-title {
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
            color: #64748b;
            text-align: center;
        }

        .step-item.active .step-title {
            color: #0d6efd;
            font-weight: 700;
        }

        .step-item.completed .step-title {
            color: #1e293b;
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }

        .ready-pulse {
            animation: ready-glow 2s infinite;
        }

        @keyframes ready-glow {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand navbar-dark bg-dark py-2 sticky-top shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/">
                <i class="bi bi-cup-hot-fill text-warning fs-4"></i>
                <span class="fs-5">Nilamku</span>
            </a>
            <a href="/" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Pesan Menu Baru
            </a>
        </div>
    </nav>

    <div class="container py-4 my-2" style="max-width: 768px;">

        <!-- Back Button & Refresh -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="/" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
            <button class="btn btn-light btn-sm border rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1" id="btnRefresh" onclick="refreshStatus(true)">
                <i class="bi bi-arrow-clockwise" id="refreshIcon"></i>
                <span id="refreshText">Segarkan Status</span>
            </button>
        </div>

        <!-- Banner Siap Diambil (Tampil Jika Status == 'siap') -->
        <div id="readyBanner" class="alert alert-info border-0 rounded-4 p-4 mb-4 shadow-sm ready-pulse" style="<?= $order->status === 'siap' ? '' : 'display: none;' ?>">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-25 text-info p-3 rounded-circle fs-2">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Pesanan Anda Siap Diambil!</h5>
                    <p class="mb-0 text-muted small">
                        Silakan menuju ke kasir / konter pengambilan Kedai Nilamku dan tunjukkan <strong>#ORD-<?= $order->id ?></strong> kepada staf kami.
                    </p>
                </div>
            </div>
        </div>

        <!-- Banner Dibatalkan (Tampil Jika Status == 'dibatalkan') -->
        <div id="cancelledBanner" class="alert alert-danger border-0 rounded-4 p-4 mb-4 shadow-sm" style="<?= $order->status === 'dibatalkan' ? '' : 'display: none;' ?>">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-danger bg-opacity-25 text-danger p-3 rounded-circle fs-2 flex-shrink-0">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                        <h5 class="fw-bold text-danger mb-0">Pesanan Dibatalkan</h5>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-bold" onclick="showCancelledModal()">
                            <i class="bi bi-info-circle me-1"></i> Rincian Pembatalan
                        </button>
                    </div>
                    <p class="mb-2 text-muted small">
                        Pesanan ini telah dibatalkan oleh pihak kedai.
                    </p>
                    <div class="p-3 bg-white bg-opacity-90 border border-danger-subtle rounded-3 text-dark mb-2">
                        <div class="small fw-bold text-danger mb-1"><i class="bi bi-chat-left-quote me-1"></i> Alasan Pembatalan:</div>
                        <div id="bannerAlasanText" class="fw-semibold text-break"><?= esc($order->alasan_batal ?: 'Tidak disertakan keterangan tambahan.') ?></div>
                    </div>
                    <a href="https://wa.me/6283176981287?text=Halo%20Admin%20Nilamku,%20pesanan%20saya%20%23ORD-<?= $order->id ?>%20(Antrean:%20<?= esc($order->nomor_antrian) ?>)%20dibatalkan%20dengan%20alasan:%20<?= urlencode($order->alasan_batal ?? '') ?>" 
                       id="bannerWaLink" 
                       target="_blank" 
                       class="btn btn-sm btn-danger rounded-pill px-3 fw-bold mt-1 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-whatsapp"></i> Hubungi CS Nilamku
                    </a>
                </div>
            </div>
        </div>

        <!-- Nomor Antrean Pelanggan -->
        <div class="card track-card p-4 mb-4 text-center border-0 shadow-sm bg-primary bg-opacity-10 border-start border-primary border-4">
            <div class="small text-uppercase fw-bold text-primary tracking-wide mb-1">Nomor Antrean Anda</div>
            <div class="display-3 fw-extrabold text-primary mb-1"><?= esc($order->nomor_antrian ?? 'A-00' . $order->id) ?></div>
            <div class="small text-muted d-flex justify-content-center align-items-center gap-2">
                <?php if (($order->metode_layanan ?? 'dine_in') === 'delivery'): ?>
                    <span class="badge bg-primary rounded-pill px-3 py-1.5"><i class="bi bi-bicycle me-1"></i> Layanan Delivery (Diantar ke Rumah)</span>
                <?php else: ?>
                    <span class="badge bg-success rounded-pill px-3 py-1.5"><i class="bi bi-shop me-1"></i> Layanan Dine-in (Makan di Tempat / Pick-up)</span>
                <?php endif; ?>
                <span class="badge bg-primary rounded-pill px-3 py-1.5">
                    <i class="bi bi-wallet2 me-1"></i> Transfer DANA
                </span>
            </div>
        </div>

        <!-- Kartu Utama Status Pesanan -->
        <div class="card track-card p-4 mb-4">
            
            <!-- Order Header & Status Badge -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                <div>
                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill mb-1 small fw-semibold">
                        Order ID: #ORD-<?= $order->id ?>
                    </span>
                    <div class="text-muted small mt-1">
                        Dipesan pada: <?= date('d M Y, H:i', strtotime($order->created_at)) ?> WIB
                    </div>
                </div>

                <div class="text-end">
                    <div id="badgeStatusContainer">
                        <?php
                            $badgeMap = [
                                'pending'    => ['class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', 'label' => 'Menunggu Konfirmasi'],
                                'diproses'   => ['class' => 'bg-primary-subtle text-primary border border-primary-subtle', 'label' => 'Sedang Diracik'],
                                'siap'       => ['class' => 'bg-info-subtle text-info border border-info-subtle', 'label' => 'Siap Diambil'],
                                'selesai'    => ['class' => 'bg-success-subtle text-success border border-success-subtle', 'label' => 'Pesanan Selesai'],
                                'dibatalkan' => ['class' => 'bg-danger-subtle text-danger border border-danger-subtle', 'label' => 'Dibatalkan'],
                            ];
                            $curr = $badgeMap[$order->status] ?? ['class' => 'bg-secondary text-light', 'label' => ucfirst($order->status)];
                        ?>
                        <span class="badge px-3 py-2 rounded-pill fs-6 fw-bold <?= $curr['class'] ?>" id="currentStatusBadge">
                            <?= $curr['label'] ?>
                        </span>
                    </div>
                    <div class="text-muted mt-1 small" style="font-size: 0.75rem;">
                        <i class="bi bi-broadcast text-success me-1"></i><span id="pollingStatusText">Live update aktif</span>
                    </div>
                </div>
            </div>

            <!-- Stepper Timeline Alur Pemesanan -->
            <?php if ($order->status !== 'dibatalkan'): ?>
                <div id="stepperContainer">
                    <?php
                        $stepOrder = ['pending' => 1, 'diproses' => 2, 'siap' => 3, 'selesai' => 4];
                        $currStepIdx = $stepOrder[$order->status] ?? 1;
                        $progressPercent = ($currStepIdx - 1) * 33.33;
                    ?>
                    <div class="stepper-wrapper">
                        <div class="stepper-progress" id="stepperProgress" style="width: calc(<?= $progressPercent ?>% - 10px);"></div>
                        
                        <!-- Step 1: Pending -->
                        <div class="step-item <?= $currStepIdx > 1 ? 'completed' : ($currStepIdx === 1 ? 'active' : '') ?>" id="step_1">
                            <div class="step-circle">
                                <i class="bi <?= $currStepIdx > 1 ? 'bi-check-lg' : 'bi-clock-history' ?>"></i>
                            </div>
                            <div class="step-title">Diterima</div>
                        </div>

                        <!-- Step 2: Diproses -->
                        <div class="step-item <?= $currStepIdx > 2 ? 'completed' : ($currStepIdx === 2 ? 'active' : '') ?>" id="step_2">
                            <div class="step-circle">
                                <i class="bi <?= $currStepIdx > 2 ? 'bi-check-lg' : 'bi-gear-wide-connected' ?>"></i>
                            </div>
                            <div class="step-title">Diracik</div>
                        </div>

                        <!-- Step 3: Siap -->
                        <div class="step-item <?= $currStepIdx > 3 ? 'completed' : ($currStepIdx === 3 ? 'active' : '') ?>" id="step_3">
                            <div class="step-circle">
                                <i class="bi <?= $currStepIdx > 3 ? 'bi-check-lg' : 'bi-cup-hot' ?>"></i>
                            </div>
                            <div class="step-title">Siap</div>
                        </div>

                        <!-- Step 4: Selesai -->
                        <div class="step-item <?= $currStepIdx === 4 ? 'completed active' : '' ?>" id="step_4">
                            <div class="step-circle">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="step-title">Selesai</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Informasi Layanan & Pengambilan / Pengantaran -->
            <div class="bg-light bg-opacity-75 rounded-4 p-3 mb-4 border">
                <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt-fill text-danger"></i> Informasi Layanan & Jadwal
                </h6>
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Jadwal / Waktu Proses:</span>
                        <strong class="text-dark">
                            <?php if (!empty($order->slot->jam_mulai)): ?>
                                <?= !empty($order->slot->tanggal) ? date('d M Y', strtotime($order->slot->tanggal)) : '-' ?>
                                (<?= substr($order->slot->jam_mulai, 0, 5) . ' - ' . substr($order->slot->jam_selesai, 0, 5) . ' WIB' ?>)
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-lightning-fill"></i> Langsung Diproses Segera
                                </span>
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <strong class="text-dark">Transfer DANA</strong>
                        <?php if (! empty($order->bukti_transfer)): ?>
                            <a href="/<?= esc($order->bukti_transfer) ?>" target="_blank" class="badge bg-primary text-decoration-none ms-1">
                                <i class="bi bi-image"></i> Lihat Bukti
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (($order->metode_layanan ?? 'dine_in') === 'delivery'): ?>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <span class="text-muted d-block">Alamat Pengantaran:</span>
                            <span class="text-dark fw-medium"><?= esc($order->alamat_pengiriman ?? '-') ?></span>
                            <div class="text-muted mt-0.5">Estimasi Jarak: <?= number_format($order->jarak_km ?? 0, 1) ?> km (Ongkir: Rp <?= number_format($order->biaya_ongkir ?? 0, 0, ',', '.') ?>)</div>
                        </div>
                    <?php else: ?>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <span class="text-muted d-block">Lokasi Pengambilan:</span>
                            <span class="text-dark fw-medium">Kedai Es Kieta Nilamku — Jl. Raya Nilam No. 24 (Konter Pick-up)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rincian Item Dipesan -->
            <div class="mb-3">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-primary"></i> Rincian Pesanan
                </h6>
                <ul class="list-unstyled mb-0" id="orderItemsList">
                    <?php 
                        $subtotalMenu = 0;
                        if (! empty($order->items)): 
                            foreach ($order->items as $it): 
                                $subtotalMenu += (float) $it->subtotal;
                    ?>
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center small">
                                <div>
                                    <strong class="text-dark"><?= esc($it->nama_item ?? $it->nama ?? 'Menu #' . $it->item_id) ?></strong>
                                    <div class="text-muted">Rp <?= number_format($it->harga_satuan, 0, ',', '.') ?> &times; <?= $it->jumlah ?></div>
                                </div>
                                <span class="fw-bold text-dark">Rp <?= number_format($it->subtotal, 0, ',', '.') ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <?php if (! empty($order->catatan)): ?>
                    <div class="alert alert-light border small text-muted mt-3 mb-0">
                        <i class="bi bi-chat-left-dots me-1"></i> Catatan Pemesan: <em>"<?= esc($order->catatan) ?>"</em>
                    </div>
                <?php endif; ?>

                <!-- Rincian Total -->
                <div class="pt-3 mt-2 border-top">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Subtotal Item</span>
                        <span>Rp <?= number_format($subtotalMenu, 0, ',', '.') ?></span>
                    </div>
                    <?php if (($order->metode_layanan ?? 'dine_in') === 'delivery'): ?>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Biaya Ongkos Kirim</span>
                            <span>Rp <?= number_format($order->biaya_ongkir ?? 0, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-bold text-dark">Total Pembayaran</span>
                        <h4 class="fw-extrabold text-primary mb-0">Rp <?= number_format($order->total_harga, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bantuan Customer Service WhatsApp & Instagram -->
        <div class="card border-0 bg-white shadow-sm rounded-4 p-3 text-center">
            <div class="small text-muted mb-2">Ada kendala atau pertanyaan seputar pesanan Anda?</div>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="https://wa.me/6283176981287?text=Halo%20Customer%20Service%20Nilamku,%20saya%20mau%20menanyakan%20status%20pesanan%20%23ORD-<?= $order->id ?>%20(Antrean:%20<?= esc($order->nomor_antrian) ?>)" 
                   target="_blank" 
                   class="btn btn-outline-success btn-sm rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-whatsapp"></i> Hubungi CS WhatsApp (0831-7698-1287)
                </a>
                <a href="https://www.instagram.com/abuab__/" 
                   target="_blank" 
                   class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-instagram"></i> Instagram @abuab__
                </a>
            </div>
        </div>

    </div>

    <!-- Modal Pop-up Notifikasi Pesanan Dibatalkan -->
    <div class="modal fade" id="modalPesananDibatalkan" tabindex="-1" aria-labelledby="modalPesananDibatalkanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalPesananDibatalkanLabel">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i> Pemberitahuan Pembatalan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-3 mb-2" style="width: 68px; height: 68px;">
                            <i class="bi bi-x-circle-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-danger mb-1">Pesanan Anda Dibatalkan</h5>
                        <p class="text-muted small mb-0">Pesanan <strong>#ORD-<?= $order->id ?></strong> (Antrean: <strong class="text-dark"><?= esc($order->nomor_antrian ?? '-') ?></strong>) tidak dapat diproses oleh kedai.</p>
                    </div>

                    <div class="card border-0 bg-light rounded-3 p-3 mb-3">
                        <div class="small fw-bold text-danger mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-chat-left-dots-fill"></i> Alasan Pembatalan dari Admin:
                        </div>
                        <div class="text-dark fw-bold p-2.5 bg-white rounded border border-danger-subtle" id="modalAlasanText" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.5;">
                            <?= esc($order->alasan_batal ?: 'Maaf, pesanan Anda tidak dapat diproses oleh staf kedai saat ini.') ?>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 small rounded-3 mb-0 d-flex gap-2">
                        <i class="bi bi-info-circle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div>
                            Bagi pesanan yang telah mentransfer pembayaran, silakan hubungi Customer Service WhatsApp kami untuk konfirmasi pengembalian dana (refund) atau pemesanan ulang.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <a href="https://wa.me/6283176981287?text=Halo%20Admin%20Nilamku,%20pesanan%20saya%20%23ORD-<?= $order->id ?>%20(Antrean:%20<?= esc($order->nomor_antrian) ?>)%20dibatalkan%20dengan%20alasan:%20<?= urlencode($order->alasan_batal ?? '') ?>" 
                       id="modalWaLink" 
                       target="_blank" 
                       class="btn btn-danger rounded-pill px-4 btn-sm fw-bold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-whatsapp"></i> Hubungi CS Nilamku
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Polling & Modal JavaScript -->
    <script>
        const orderId = <?= (int) $order->id ?>;
        const nomorAntrian = "<?= esc($order->nomor_antrian ?? '') ?>";
        let currentStatus = "<?= esc($order->status) ?>";
        let pollingTimer = null;
        let cancelModalInstance = null;

        const badgeMap = {
            'pending':    { class: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', label: 'Menunggu Konfirmasi', step: 1 },
            'diproses':   { class: 'bg-primary-subtle text-primary border border-primary-subtle', label: 'Sedang Diracik', step: 2 },
            'siap':       { class: 'bg-info-subtle text-info border border-info-subtle', label: 'Siap Diambil', step: 3 },
            'selesai':    { class: 'bg-success-subtle text-success border border-success-subtle', label: 'Pesanan Selesai', step: 4 },
            'dibatalkan': { class: 'bg-danger-subtle text-danger border border-danger-subtle', label: 'Dibatalkan', step: 0 }
        };

        function showCancelledModal() {
            const modalEl = document.getElementById('modalPesananDibatalkan');
            if (!modalEl) return;
            if (!cancelModalInstance) {
                cancelModalInstance = new bootstrap.Modal(modalEl);
            }
            cancelModalInstance.show();
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Tampilkan modal pop-up secara langsung jika status pesanan dibatalkan
            if (currentStatus === 'dibatalkan') {
                showCancelledModal();
                document.getElementById('pollingStatusText').innerText = 'Status Final';
            } else if (currentStatus !== 'selesai') {
                pollingTimer = setInterval(() => {
                    refreshStatus(false);
                }, 15000);
            } else {
                document.getElementById('pollingStatusText').innerText = 'Status Final';
            }
        });

        async function refreshStatus(isManual = false) {
            const icon = document.getElementById('refreshIcon');
            const text = document.getElementById('refreshText');

            if (isManual) {
                icon.classList.add('bi-spin');
                text.innerText = 'Memperbarui...';
            }

            try {
                const response = await fetch(`/track/${orderId}?json=1`);
                if (response.ok) {
                    const json = await response.json();
                    if (json.status === 'success' && json.data) {
                        const newStatus = json.data.status;
                        
                        if (newStatus !== currentStatus) {
                            currentStatus = newStatus;
                            updateUI(newStatus, json.data);
                        }
                    }
                }
            } catch (err) {
                console.error("Polling error:", err);
            } finally {
                if (isManual) {
                    setTimeout(() => {
                        icon.classList.remove('bi-spin');
                        text.innerText = 'Segarkan Status';
                    }, 500);
                }
            }
        }

        function updateUI(status, data = null) {
            const badgeEl = document.getElementById('currentStatusBadge');
            const readyBanner = document.getElementById('readyBanner');
            const cancelledBanner = document.getElementById('cancelledBanner');
            const info = badgeMap[status] || { class: 'bg-secondary text-light', label: status, step: 1 };

            // Update Badge
            badgeEl.className = `badge px-3 py-2 rounded-pill fs-6 fw-bold ${info.class}`;
            badgeEl.innerText = info.label;

            // Update Banner
            if (status === 'siap') {
                readyBanner.style.display = 'block';
            } else {
                readyBanner.style.display = 'none';
            }

            if (status === 'dibatalkan') {
                cancelledBanner.style.display = 'block';
                if (document.getElementById('stepperContainer')) {
                    document.getElementById('stepperContainer').style.display = 'none';
                }
                
                const reasonText = (data && data.alasan_batal) ? data.alasan_batal : 'Pesanan dibatalkan oleh pihak kedai.';
                const bannerAlasanEl = document.getElementById('bannerAlasanText');
                const modalAlasanEl = document.getElementById('modalAlasanText');
                const bannerWaLink = document.getElementById('bannerWaLink');
                const modalWaLink = document.getElementById('modalWaLink');

                if (bannerAlasanEl) bannerAlasanEl.innerText = reasonText;
                if (modalAlasanEl) modalAlasanEl.innerText = reasonText;

                const encodedReason = encodeURIComponent(reasonText);
                const waUrl = `https://wa.me/6283176981287?text=Halo%20Admin%20Nilamku,%20pesanan%20saya%20%23ORD-${orderId}%20(Antrean:%20${encodeURIComponent(nomorAntrian)})%20dibatalkan%20dengan%20alasan:%20${encodedReason}`;
                if (bannerWaLink) bannerWaLink.href = waUrl;
                if (modalWaLink) modalWaLink.href = waUrl;

                // Tampilkan popup modal peringatan
                showCancelledModal();

                clearInterval(pollingTimer);
                document.getElementById('pollingStatusText').innerText = 'Dibatalkan';
                return;
            }

            // Update Stepper
            const stepIdx = info.step;
            const progressEl = document.getElementById('stepperProgress');
            if (progressEl) {
                const progressPercent = (stepIdx - 1) * 33.33;
                progressEl.style.width = `calc(${progressPercent}% - 10px)`;
            }

            for (let i = 1; i <= 4; i++) {
                const stepItem = document.getElementById(`step_${i}`);
                if (!stepItem) continue;

                stepItem.className = 'step-item';
                const circle = stepItem.querySelector('.step-circle');

                if (i < stepIdx) {
                    stepItem.classList.add('completed');
                    circle.innerHTML = '<i class="bi bi-check-lg"></i>';
                } else if (i === stepIdx) {
                    stepItem.classList.add('active');
                    if (i === 4) {
                        stepItem.classList.add('completed');
                        circle.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                    } else if (i === 3) {
                        circle.innerHTML = '<i class="bi bi-cup-hot"></i>';
                    } else if (i === 2) {
                        circle.innerHTML = '<i class="bi bi-gear-wide-connected"></i>';
                    } else {
                        circle.innerHTML = '<i class="bi bi-clock-history"></i>';
                    }
                } else {
                    circle.innerHTML = i === 4 ? '<i class="bi bi-check-circle-fill"></i>' : (i === 3 ? '<i class="bi bi-cup-hot"></i>' : '<i class="bi bi-gear"></i>');
                }
            }

            if (status === 'selesai' || status === 'dibatalkan') {
                clearInterval(pollingTimer);
                document.getElementById('pollingStatusText').innerText = 'Status Final';
            }
        }
    </script>
</body>
</html>
