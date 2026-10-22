<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Nilamku - Pesan Sekarang, Ambil Tanpa Antre') ?></title>
    <meta name="description" content="Pesan menu favoritmu di Nilamku. Pilih dine-in atau take-away, bayar via transfer DANA, nikmati tanpa antre!">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pr:  #C8673A;
            --prl: #E8855A;
            --prp: #FAE8DC;
            --bg:  #F5E6D8;
            --td:  #2D1B0E;
            --tm:  #6B4C35;
            --tmu: #A0846E;
            --bd:  #EDD5C0;
            --ok:  #22C55E;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--td); }
        .app-shell { display: flex; height: 100vh; overflow: hidden; }

        /* SIDEBAR */
        .sidebar { width: 215px; min-width: 215px; background: #fff; display: flex; flex-direction: column; padding: 26px 0 18px; border-right: 1px solid var(--bd); overflow-y: auto; }
        .s-brand { display: flex; align-items: center; gap: 10px; padding: 0 20px 22px; font-weight: 800; font-size: 1.15rem; color: var(--pr); text-decoration: none; }
        .s-icon  { width: 36px; height: 36px; background: var(--prp); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--pr); }
        .s-nav   { flex: 1; padding: 0 10px; }
        .nav-a   { display: flex; align-items: center; gap: 11px; padding: 9px 13px; border-radius: 11px; font-size: .85rem; font-weight: 500; color: var(--tm); cursor: pointer; text-decoration: none; transition: all .18s; margin-bottom: 3px; position: relative; width: 100%; background: none; border: none; text-align: left; font-family: inherit; }
        .nav-a:hover  { background: var(--prp); color: var(--pr); }
        .nav-a.active { background: var(--prp); color: var(--pr); font-weight: 700; }
        .nav-a.active::before { content:''; position:absolute; left:0; top:22%; bottom:22%; width:3px; background:var(--pr); border-radius:0 4px 4px 0; }
        .store-chip { display:flex; align-items:center; gap:7px; padding:6px 13px; border-radius:9px; font-size:.73rem; font-weight:600; margin:0 10px 13px; }
        .store-chip.open   { background:#DCFCE7; color:#166534; }
        .store-chip.closed { background:#FEE2E2; color:#991B1B; }
        .sdot { width:7px; height:7px; border-radius:50%; }
        .sdot.open   { background:var(--ok); animation: pd 1.5s infinite; }
        .sdot.closed { background:#EF4444; }
        @keyframes pd { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(.85)} }
        .s-foot { padding: 12px 10px 0; border-top: 1px solid var(--bd); margin-top: 8px; }

        /* MAIN AREA */
        .main-area { flex: 1; display: flex; overflow: hidden; }
        .menu-panel { flex: 1; overflow-y: auto; padding: 26px 22px; }
        .topbar { display:flex; align-items:center; gap:11px; margin-bottom:20px; }
        .s-wrap  { flex:1; position:relative; }
        .s-wrap input { width:100%; padding:10px 15px 10px 40px; border-radius:50px; border:1.5px solid var(--bd); background:#fff; font-size:.86rem; font-family:inherit; color:var(--td); outline:none; transition:border-color .2s; }
        .s-wrap input:focus { border-color:var(--pr); }
        .s-wrap .si { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--tmu); font-size:.88rem; }
        .btn-fl { background:var(--pr); color:#fff; border:none; border-radius:50px; padding:10px 20px; font-size:.84rem; font-weight:600; font-family:inherit; cursor:pointer; display:flex; align-items:center; gap:7px; transition:background .2s; white-space:nowrap; }
        .btn-fl:hover { background:var(--prl); }
        .m-title { font-size:1.5rem; font-weight:800; color:var(--td); margin-bottom:15px; }
        .cat-pills { display:flex; gap:7px; flex-wrap:wrap; margin-bottom:22px; }
        .cpill { padding:7px 18px; border-radius:50px; border:1.5px solid var(--bd); background:#fff; font-size:.82rem; font-weight:600; color:var(--tm); cursor:pointer; transition:all .18s; font-family:inherit; }
        .cpill:hover { border-color:var(--pr); color:var(--pr); }
        .cpill.active { background:var(--pr); border-color:var(--pr); color:#fff; }

        /* PRODUCT CARDS */
        .menu-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(205px,1fr)); gap:16px; }
        .mc  { background:#fff; border-radius:18px; padding:12px; box-shadow:0 3px 12px rgba(180,110,60,.07); border:1px solid rgba(237,213,192,.6); transition:transform .2s,box-shadow .2s; display:flex; flex-direction:column; }
        .mc:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(200,103,58,.14); }
        .mc-img-box { width:100%; height:145px; border-radius:13px; overflow:hidden; position:relative; background:linear-gradient(135deg,var(--prp),#F5DCCB); }
        .mc-img { width:100%; height:100%; object-fit:cover; display:block; }
        .mc-ph  { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:2.2rem; color:var(--prl); }
        .mc-body{ padding:10px 2px 2px; flex:1; display:flex; flex-direction:column; gap:4px; }
        .mc-hdr { display:flex; justify-content:space-between; align-items:flex-start; gap:6px; }
        .mc-nm  { font-weight:700; font-size:.9rem; color:var(--td); line-height:1.3; }
        .mc-pr  { font-weight:800; font-size:.88rem; color:var(--pr); white-space:nowrap; }
        .mc-ds  { font-size:.72rem; color:var(--tmu); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .mc-meta { display:flex; align-items:center; gap:6px; margin-top:4px; }
        .mc-ft  { display:flex; align-items:center; justify-content:space-between; margin-top:auto; padding-top:10px; }
        .bav  { background:#DCFCE7; color:#166534; font-size:.66rem; font-weight:700; padding:2px 8px; border-radius:30px; }
        .bhbs { background:#FEE2E2; color:#991B1B; font-size:.66rem; font-weight:700; padding:2px 8px; border-radius:30px; }
        .btn-add { background:var(--pr); color:#fff; border:none; border-radius:50px; padding:6px 14px; font-size:.76rem; font-weight:700; font-family:inherit; cursor:pointer; display:flex; align-items:center; gap:4px; transition:background .18s; }
        .btn-add:hover { background:var(--prl); }
        .btn-add:disabled { background:#D4B8AA; cursor:not-allowed; }
        .qty-c { display:inline-flex; align-items:center; gap:5px; background:#FAF0E6; border:1px solid var(--bd); border-radius:50px; padding:2px 5px; }
        .qb { width:24px; height:24px; border-radius:50%; border:none; background:#fff; color:var(--pr); font-size:.92rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; line-height:1; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .qb.pl { background:var(--pr); color:#fff; }
        .qb:hover { opacity:.85; }
        .qv { font-weight:700; font-size:.82rem; color:var(--td); min-width:16px; text-align:center; }

        /* CART PANEL (DESKTOP) */
        .cart-panel { width: 300px; min-width: 300px; background: #fff; border-left: 1px solid var(--bd); display: flex; flex-direction: column; overflow-y: auto; }
        .cp-in { padding: 22px 18px; display: flex; flex-direction: column; gap: 14px; }
        .cp-ttl { font-size: 1.08rem; font-weight: 800; color: var(--td); }
        .svc-tabs { display: flex; gap: 8px; }
        .stab { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 10px 4px; border: 1.5px solid var(--bd); border-radius: 12px; cursor: pointer; font-family: inherit; background: #fff; transition: all .18s; }
        .stab:hover { border-color: var(--pr); }
        .stab.sel  { border-color: var(--pr); background: var(--prp); font-weight: 700; }
        .sti { font-size: 1.15rem; }
        .stl { font-size: .72rem; font-weight: 600; color: var(--tm); }
        .stab.sel .stl { color: var(--pr); }
        .cart-list { overflow-y: auto; max-height: 220px; }
        .crow { display: flex; align-items: center; gap: 9px; padding: 9px 0; border-bottom: 1px solid var(--bd); }
        .crow:last-child { border-bottom: none; }
        .cthumb { width: 42px; height: 42px; border-radius: 9px; object-fit: cover; background: var(--prp); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--pr); overflow: hidden; }
        .cthumb img { width: 100%; height: 100%; object-fit: cover; }
        .cinfo { flex: 1; min-width: 0; }
        .cnm { font-size: .78rem; font-weight: 700; color: var(--td); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cpr { font-size: .72rem; color: var(--tmu); }
        .cem { text-align: center; padding: 24px 0; color: var(--tmu); font-size: .78rem; }
        .sum-box { background: #FAFAFA; border-radius: 12px; padding: 12px 14px; border: 1px solid var(--bd); }
        .sr { display: flex; justify-content: space-between; font-size: .78rem; color: var(--tm); margin-bottom: 5px; }
        .sr.tot { font-size: .95rem; font-weight: 800; color: var(--td); margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--bd); margin-bottom: 0; }
        .fl { font-size: .75rem; font-weight: 700; color: var(--tm); margin-bottom: 4px; }
        .fc { width: 100%; padding: 8px 12px; border: 1.5px solid var(--bd); border-radius: 10px; font-size: .8rem; font-family: inherit; color: var(--td); background: #fff; outline: none; transition: border-color .2s; }
        .fc:focus { border-color: var(--pr); }
        .fc::placeholder { color: var(--tmu); }
        .pay-box { background: var(--prp); border-radius: 11px; padding: 11px 13px; font-size: .76rem; color: var(--tm); }
        .pay-box strong { color: var(--pr); }
        .btn-cp { background: none; border: 1.5px solid var(--pr); color: var(--pr); border-radius: 6px; padding: 3px 8px; font-size: .68rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: all .18s; }
        .btn-cp:hover { background: var(--pr); color: #fff; }
        .up-lbl { border: 1.5px dashed var(--bd); border-radius: 10px; padding: 10px 12px; text-align: center; font-size: .74rem; color: var(--tmu); cursor: pointer; transition: border-color .2s; display: block; background: #fff; }
        .up-lbl:hover { border-color: var(--pr); }
        .cart-alert { background: #FEE2E2; border-radius: 8px; padding: 8px 11px; font-size: .75rem; color: #991B1B; display: none; }
        .qp { background: linear-gradient(135deg,var(--prp),#FFF5EE); border: 1px solid #EDCFB8; border-radius: 11px; padding: 9px 12px; display: flex; align-items: center; gap: 8px; font-size: .72rem; color: var(--tm); }
        .qp-ic { width: 28px; height: 28px; background: var(--pr); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; font-size: .85rem; }
        .btn-ord { width: 100%; background: var(--pr); color: #fff; border: none; border-radius: 12px; padding: 12px; font-size: .9rem; font-weight: 800; font-family: inherit; cursor: pointer; transition: background .2s,transform .15s; display: flex; align-items: center; justify-content: center; gap: 7px; }
        .btn-ord:hover { background: var(--prl); transform: translateY(-1px); }
        .btn-ord:disabled { background: #D4B8AA; cursor: not-allowed; transform: none; }

        /* FLOATING WHATSAPP */
        .wa-fl { position: fixed; background: #25D366; color: #fff; border-radius: 50px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 6px 18px rgba(37,211,102,.38); z-index: 1050; font-weight: 700; font-size: .83rem; text-decoration: none; transition: all .2s; }
        .wa-fl:hover { background: #1DAF54; color: #fff; transform: translateY(-2px); }

        /* RESPONSIVE LAYOUT */
        @media (min-width: 992px) {
            .mob-bar { display: none !important; }
            .wa-fl { right: 318px; bottom: 22px; }
        }
        @media (max-width: 991px) {
            .sidebar { display: none; }
            .cart-panel { display: none; }
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .mob-bar { display: flex !important; }
            .wa-fl { bottom: 76px; right: 18px; }
        }
        @media (max-width: 480px) {
            .menu-grid { grid-template-columns: 1fr; }
        }
        .mob-bar { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid var(--bd); padding: 11px 18px; z-index: 1040; align-items: center; justify-content: space-between; gap: 11px; }
        .cat-ld { grid-column: 1/-1; text-align: center; padding: 46px 0; color: var(--tmu); }
        .spin-r { width: 32px; height: 32px; border: 3px solid var(--bd); border-top-color: var(--pr); border-radius: 50%; animation: spin .8s linear infinite; margin: 0 auto 11px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--bd); border-radius: 10px; }
        .modal-content, .offcanvas { border-radius: 18px; border: none; }
    </style>
</head>
<body>

<div class="app-shell">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a class="s-brand" href="/">
            <div class="s-icon"><i class="bi bi-cup-hot-fill"></i></div>
            Nilamku
        </a>
        <?php if (($store_status ?? 'buka') === 'buka'): ?>
            <div class="store-chip open"><span class="sdot open"></span> Toko Buka</div>
        <?php else: ?>
            <div class="store-chip closed"><span class="sdot closed"></span> Toko Tutup</div>
        <?php endif; ?>
        <nav class="s-nav">
            <a href="/" class="nav-a active"><i class="bi bi-house-door-fill"></i> Beranda</a>
            <a href="#" class="nav-a" onclick="document.querySelector('.menu-grid')?.scrollIntoView({behavior:'smooth'});return false;"><i class="bi bi-cup-straw"></i> Menu</a>
            <button class="nav-a" data-bs-toggle="modal" data-bs-target="#modalLacak"><i class="bi bi-bag-check"></i> Lacak Pesanan</button>
            <a href="https://wa.me/6283176981287?text=Halo%20CS%20Nilamku" target="_blank" class="nav-a"><i class="bi bi-headset"></i> Bantuan CS</a>
            <a href="https://www.instagram.com/abuab__/" target="_blank" class="nav-a"><i class="bi bi-instagram"></i> Instagram</a>
            <a href="/login" class="nav-a"><i class="bi bi-shield-lock"></i> Admin</a>
        </nav>
        <div class="s-foot">
            <a href="https://wa.me/6283176981287" target="_blank" class="nav-a"><i class="bi bi-whatsapp" style="color:#25D366;"></i> 0831-7698-1287</a>
            <a href="https://www.instagram.com/abuab__/" target="_blank" class="nav-a"><i class="bi bi-instagram" style="color:#E1306C;"></i> @abuab__</a>
        </div>
    </aside>

    <!-- MAIN AREA -->
    <div class="main-area">
        <main class="menu-panel">
            <div class="topbar">
                <div class="s-wrap">
                    <i class="bi bi-search si"></i>
                    <input type="text" id="searchInput" placeholder="Cari menu..." oninput="handleSearch(this.value)">
                </div>
                <button class="btn-fl" data-bs-toggle="modal" data-bs-target="#modalFilter"><i class="bi bi-sliders"></i> Filter</button>
            </div>

            <div class="m-title" id="menuPanelTitle">Menu Nilamku</div>

            <div class="cat-pills" id="categoryPillContainer">
                <button class="cpill active" onclick="filterCategory(0, this)">Semua</button>
            </div>

            <?php if (($store_status ?? 'buka') !== 'buka'): ?>
            <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:13px;padding:13px 16px;display:flex;align-items:center;gap:11px;margin-bottom:18px;">
                <div style="font-size:1.4rem;">&#128308;</div>
                <div>
                    <div style="font-weight:700;color:#991B1B;font-size:.88rem;">Toko Sedang Tutup</div>
                    <div style="font-size:.75rem;color:#B91C1C;">Anda masih bisa melihat menu, tetapi pemesanan sementara dinonaktifkan.</div>
                </div>
            </div>
            <?php endif; ?>

            <div class="menu-grid" id="catalogContainer">
                <div class="cat-ld"><div class="spin-r"></div><div>Menyiapkan menu lezat...</div></div>
            </div>

            <!-- FAQ & INFO SECTION -->
            <div style="margin-top:44px;">
                <div style="font-size:1rem;font-weight:800;color:var(--td);margin-bottom:13px;display:flex;align-items:center;gap:9px;">
                    <i class="bi bi-question-circle-fill" style="color:var(--pr);"></i> FAQ &amp; Info Layanan
                </div>
                <div class="accordion accordion-flush" id="faqAccordion" style="background:#fff;border-radius:15px;overflow:hidden;border:1px solid var(--bd);">
                    <div class="accordion-item" style="border-bottom:1px solid var(--bd);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" style="font-size:.82rem;color:var(--td);background:#fff;" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-bag-plus me-2" style="color:var(--pr);"></i>Cara memesan?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="font-size:.78rem;color:var(--tm);">
                                1. Pilih menu &rarr; klik <strong>+ Tambah</strong>.<br>
                                2. Pilih layanan: <strong>Dine in</strong> atau <strong>Take away</strong>.<br>
                                3. Isi nama dan nomor WhatsApp.<br>
                                4. Transfer ke DANA <strong>083176981287</strong> &amp; unggah bukti.<br>
                                5. Klik <strong>Konfirmasi Pesanan</strong> &rarr; dapatkan nomor antrean.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item" style="border-bottom:1px solid var(--bd);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" style="font-size:.82rem;color:var(--td);background:#fff;" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-wallet2 me-2" style="color:var(--pr);"></i>Mengapa hanya Transfer DANA?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="font-size:.78rem;color:var(--tm);">
                                Transfer DANA memastikan pesanan terkonfirmasi otomatis dan dapur bisa segera memproses tanpa antre kasir.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item" style="border-bottom:1px solid var(--bd);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" style="font-size:.82rem;color:var(--td);background:#fff;" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-shop me-2" style="color:var(--pr);"></i>Layanan Dine-in &amp; Take Away
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="font-size:.78rem;color:var(--tm);">
                                &#8226; <strong>Dine in:</strong> Nikmati langsung di kedai Nilamku.<br>
                                &#8226; <strong>Take away:</strong> Pesanan dikemas rapi untuk dibawa pulang tanpa antre.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" style="font-size:.82rem;color:var(--td);background:#fff;" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-compass me-2" style="color:var(--pr);"></i>Cara lacak pesanan?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="font-size:.78rem;color:var(--tm);">
                                Setelah checkout Anda otomatis diarahkan ke halaman pelacakan. Jika halaman tertutup, klik <strong>Lacak Pesanan</strong> di sidebar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTACT SECTION -->
            <div style="background:#fff;border-radius:15px;padding:18px;margin-top:22px;border:1px solid var(--bd);display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-weight:800;color:var(--td);margin-bottom:4px;"><i class="bi bi-headset" style="color:var(--pr);margin-right:7px;"></i>Customer Service</div>
                    <div style="font-size:.78rem;color:var(--tmu);">Hubungi kami untuk bantuan seputar pesanan.</div>
                    <div style="font-size:.83rem;color:#25D366;font-weight:700;margin-top:6px;"><i class="bi bi-whatsapp"></i> 0831-7698-1287</div>
                    <a href="https://www.instagram.com/abuab__/" target="_blank" style="font-size:.8rem;color:#E1306C;font-weight:700;text-decoration:none;"><i class="bi bi-instagram"></i> @abuab__</a>
                </div>
                <div style="display:flex;flex-direction:column;gap:7px;">
                    <a href="https://wa.me/6283176981287?text=Halo%20CS%20Nilamku" target="_blank" style="background:#25D366;color:#fff;border-radius:9px;padding:9px 16px;font-weight:700;font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px;"><i class="bi bi-whatsapp"></i> Chat CS</a>
                    <a href="https://www.instagram.com/abuab__/" target="_blank" style="background:#fff;color:#E1306C;border:1.5px solid #E1306C;border-radius:9px;padding:8px 16px;font-weight:700;font-size:.8rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px;"><i class="bi bi-instagram"></i> Instagram</a>
                </div>
            </div>
            <div style="height:48px;"></div>
        </main>

        <!-- CART SUMMARY (DESKTOP) -->
        <aside class="cart-panel">
            <div class="cp-in">
                <div class="cp-ttl">Cart summary</div>
                <div class="cart-alert" id="checkoutAlertBox"><div id="checkoutAlertMsg"></div></div>

                <div>
                    <div class="fl">Metode Layanan</div>
                    <div class="svc-tabs">
                        <button class="stab sel" id="optDineIn" onclick="setServiceMethod('dine_in')">
                            <span class="sti">&#127869;</span>
                            <span class="stl">Dine in</span>
                        </button>
                        <button class="stab" id="optTakeAway" onclick="setServiceMethod('takeaway')">
                            <span class="sti">&#129397;</span>
                            <span class="stl">Take away</span>
                        </button>
                    </div>
                </div>

                <div class="qp">
                    <div class="qp-ic"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div>
                        <strong style="color:var(--pr);display:block;margin-bottom:2px;font-size:.76rem;">Langsung Diproses</strong>
                        <span>Pesanan segera disiapkan untuk dinikmati tanpa perlu antre di kasir.</span>
                    </div>
                </div>

                <div>
                    <div class="fl">Item Dipilih</div>
                    <div class="cart-list" id="cartItemsList">
                        <div class="cem" id="cartEmptyMessage">
                            <i class="bi bi-basket" style="font-size:1.7rem;display:block;margin-bottom:7px;opacity:.4;"></i>
                            Belum ada item dipilih
                        </div>
                    </div>
                </div>

                <div id="checkoutFormContent" style="display:none;flex-direction:column;gap:11px;">
                    <div>
                        <div class="fl">Nama Lengkap *</div>
                        <input type="text" id="customerName" class="fc" placeholder="Nama kamu...">
                    </div>
                    <div>
                        <div class="fl">Nomor WhatsApp *</div>
                        <input type="tel" id="customerPhone" class="fc" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <div class="fl">Catatan (opsional)</div>
                        <textarea id="orderNote" class="fc" rows="1" placeholder="Misal: less sugar..."></textarea>
                    </div>
                    <div class="pay-box">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <div>
                                <span style="background:var(--pr);color:#fff;padding:2px 7px;border-radius:5px;font-size:.66rem;font-weight:800;margin-right:5px;">DANA</span>
                                <strong style="font-size:.8rem;">Transfer DANA</strong>
                            </div>
                            <button class="btn-cp" onclick="navigator.clipboard.writeText('083176981287').then(()=>alert('Nomor DANA disalin!'))">Salin</button>
                        </div>
                        <div style="font-size:.78rem;"><strong>083176981287</strong> a.n. <strong>Nilamku</strong></div>
                    </div>
                    <div>
                        <div class="fl">Bukti Transfer *</div>
                        <label class="up-lbl" for="buktiTransferInput">
                            <i class="bi bi-upload" style="font-size:1rem;color:var(--pr);display:block;margin-bottom:3px;"></i>
                            Klik untuk unggah bukti (JPG/PNG)
                        </label>
                        <input type="file" id="buktiTransferInput" accept="image/png,image/jpeg,image/jpg" onchange="previewBukti(this, 'buktiPreviewContainer', 'buktiPreviewImg')" style="display:none;" required>
                        <div id="buktiPreviewContainer" style="display:none;margin-top:7px;text-align:center;">
                            <img id="buktiPreviewImg" src="" alt="Preview" style="max-height:85px;border-radius:8px;border:1px solid var(--bd);">
                        </div>
                    </div>
                    <div class="sum-box">
                        <div class="sr"><span>Subtotal Menu</span><span id="subtotalDisplay">Rp 0</span></div>
                        <div class="sr tot"><span>Grand Total</span><span id="cartTotalDisplay">Rp 0</span></div>
                    </div>
                    <?php if (($store_status ?? 'buka') === 'buka'): ?>
                        <button class="btn-ord" id="btnCheckout" onclick="processCheckout(false)">
                            <span id="btnCheckoutText"><i class="bi bi-bag-check-fill"></i> Konfirmasi Pesanan</span>
                            <span id="btnCheckoutSpinner" class="spinner-border spinner-border-sm" style="display:none;"></span>
                        </button>
                    <?php else: ?>
                        <button class="btn-ord" disabled><i class="bi bi-slash-circle"></i> Toko Sedang Tutup</button>
                    <?php endif; ?>
                </div>

                <div id="cartPrompt" style="text-align:center;padding:5px 0;">
                    <div style="font-size:.74rem;color:var(--tmu);">Pilih menu dari daftar untuk mulai memesan</div>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- FLOATING WHATSAPP -->
<a href="https://wa.me/6283176981287?text=Halo%20CS%20Nilamku" target="_blank" class="wa-fl">
    <i class="bi bi-whatsapp" style="font-size:1.05rem;"></i>
    <span>Chat CS</span>
</a>

<!-- MOBILE BOTTOM BAR -->
<div class="mob-bar">
    <div>
        <div style="font-size:.71rem;color:var(--tmu);">Total</div>
        <div style="font-weight:800;color:var(--td);" id="mobileTotalDisplay">Rp 0</div>
    </div>
    <?php if (($store_status ?? 'buka') === 'buka'): ?>
        <button class="btn-ord" style="width:auto;padding:9px 18px;font-size:.8rem;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
            <i class="bi bi-bag-check-fill"></i> Keranjang (<span id="mobileCountBadge">0</span>)
        </button>
    <?php else: ?>
        <button class="btn-ord" style="width:auto;padding:9px 16px;font-size:.8rem;" disabled><i class="bi bi-door-closed"></i> Tutup</button>
    <?php endif; ?>
</div>

<!-- MOBILE CART OFFCANVAS -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" style="width:100%;max-width:380px;background:#fff;">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--bd);">
        <h5 class="offcanvas-title fw-bold" style="color:var(--td);font-size:1.1rem;">Cart summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" style="padding:18px;">
        <div class="cart-alert" id="mob_checkoutAlertBox"><div id="mob_checkoutAlertMsg"></div></div>

        <div class="mb-3">
            <div class="fl">Metode Layanan</div>
            <div class="svc-tabs">
                <button class="stab sel" id="optDineInMob" onclick="setServiceMethod('dine_in')">
                    <span class="sti">&#127869;</span>
                    <span class="stl">Dine in</span>
                </button>
                <button class="stab" id="optTakeAwayMob" onclick="setServiceMethod('takeaway')">
                    <span class="sti">&#129397;</span>
                    <span class="stl">Take away</span>
                </button>
            </div>
        </div>

        <div class="mb-3">
            <div class="fl">Item Dipilih</div>
            <div class="cart-list" id="mob_cartItemsList" style="max-height:200px;">
                <div class="cem">Belum ada item dipilih</div>
            </div>
        </div>

        <div id="mob_checkoutFormContent" style="display:none;flex-direction:column;gap:11px;">
            <div>
                <div class="fl">Nama Lengkap *</div>
                <input type="text" id="mob_customerName" class="fc" placeholder="Nama kamu...">
            </div>
            <div>
                <div class="fl">Nomor WhatsApp *</div>
                <input type="tel" id="mob_customerPhone" class="fc" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <div class="fl">Catatan (opsional)</div>
                <textarea id="mob_orderNote" class="fc" rows="1" placeholder="Misal: less sugar..."></textarea>
            </div>
            <div class="pay-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <div>
                        <span style="background:var(--pr);color:#fff;padding:2px 7px;border-radius:5px;font-size:.66rem;font-weight:800;margin-right:5px;">DANA</span>
                        <strong style="font-size:.8rem;">Transfer DANA</strong>
                    </div>
                    <button class="btn-cp" onclick="navigator.clipboard.writeText('083176981287').then(()=>alert('Nomor DANA disalin!'))">Salin</button>
                </div>
                <div style="font-size:.78rem;"><strong>083176981287</strong> a.n. <strong>Nilamku</strong></div>
            </div>
            <div>
                <div class="fl">Bukti Transfer *</div>
                <label class="up-lbl" for="mob_buktiTransferInput">
                    <i class="bi bi-upload" style="font-size:1rem;color:var(--pr);display:block;margin-bottom:3px;"></i>
                    Klik untuk unggah bukti (JPG/PNG)
                </label>
                <input type="file" id="mob_buktiTransferInput" accept="image/png,image/jpeg,image/jpg" onchange="previewBukti(this, 'mob_buktiPreviewContainer', 'mob_buktiPreviewImg')" style="display:none;" required>
                <div id="mob_buktiPreviewContainer" style="display:none;margin-top:7px;text-align:center;">
                    <img id="mob_buktiPreviewImg" src="" alt="Preview" style="max-height:85px;border-radius:8px;border:1px solid var(--bd);">
                </div>
            </div>
            <div class="sum-box">
                <div class="sr"><span>Subtotal Menu</span><span id="subtotalDisplayMob">Rp 0</span></div>
                <div class="sr tot"><span>Grand Total</span><span id="cartTotalDisplayMob">Rp 0</span></div>
            </div>
            <?php if (($store_status ?? 'buka') === 'buka'): ?>
                <button class="btn-ord" id="mob_btnCheckout" onclick="processCheckout(true)">
                    <span id="mob_btnCheckoutText"><i class="bi bi-bag-check-fill"></i> Konfirmasi Pesanan</span>
                    <span id="mob_btnCheckoutSpinner" class="spinner-border spinner-border-sm" style="display:none;"></span>
                </button>
            <?php else: ?>
                <button class="btn-ord" disabled><i class="bi bi-slash-circle"></i> Toko Sedang Tutup</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TOKO TUTUP -->
<div class="modal fade" id="modalTokoTutup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header" style="background:var(--pr);color:#fff;border-radius:18px 18px 0 0;border:none;">
                <h5 class="modal-title fw-bold"><i class="bi bi-shop-window me-2"></i>Toko Sedang Tutup</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div style="font-size:2.8rem;margin-bottom:13px;">&#128308;</div>
                <h5 style="font-weight:800;color:var(--td);">Mohon Maaf, Kami Sedang Tidak Beroperasi</h5>
                <p style="color:var(--tmu);font-size:.82rem;margin-bottom:18px;">Saat ini Nilamku sedang tutup dan belum menerima pesanan baru. Silakan coba lagi nanti.</p>
                <a href="https://wa.me/6283176981287?text=Halo%20Admin%20Nilamku,%20kapan%20toko%20buka?" target="_blank" class="btn w-100 fw-bold rounded-3 mb-2" style="background:var(--ok);color:#fff;padding:11px;"><i class="bi bi-whatsapp me-2"></i>Tanya Jam Buka via WhatsApp</a>
                <button class="btn w-100 rounded-3" style="background:#F3F4F6;color:var(--tm);padding:11px;" data-bs-dismiss="modal">Lihat Menu Saja</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL LACAK -->
<div class="modal fade" id="modalLacak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:var(--td);"><i class="bi bi-geo-alt-fill me-2" style="color:var(--pr);"></i>Lacak Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p style="font-size:.8rem;color:var(--tmu);margin-bottom:11px;">Masukkan ID pesanan Anda.</p>
                <div style="display:flex;gap:7px;align-items:center;background:#F5F5F5;border-radius:9px;padding:4px 4px 4px 11px;border:1px solid var(--bd);margin-bottom:11px;">
                    <span style="font-weight:700;color:var(--tmu);font-size:.85rem;">#ORD-</span>
                    <input type="number" id="inputLacakId" style="border:none;background:transparent;font-size:.85rem;flex:1;padding:5px 0;outline:none;font-family:inherit;" placeholder="Contoh: 1" min="1">
                </div>
                <button onclick="goToTracking()" class="btn-ord" style="border-radius:9px;">Lacak Sekarang</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FILTER -->
<div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:var(--td);"><i class="bi bi-sliders me-2" style="color:var(--pr);"></i>Filter Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="filterCategoryList" style="display:flex;flex-direction:column;gap:6px;"></div>
                <button class="btn-ord mt-3" style="border-radius:9px;" data-bs-dismiss="modal">Terapkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const isStoreOpen = <?= (($store_status ?? 'buka') === 'buka') ? 'true' : 'false' ?>;
    let catalogData = [], allItems = [], cart = {}, selectedCategory = 0, serviceMethod = 'dine_in';

    document.addEventListener('DOMContentLoaded', () => {
        loadCatalog();
        setServiceMethod('dine_in');
        if(!isStoreOpen){
            const el = document.getElementById('modalTokoTutup');
            if(el) new bootstrap.Modal(el).show();
        }
    });

    async function loadCatalog(){
        try {
            const res = await fetch('/api/catalog');
            const json = await res.json();
            if(json.status === 'success' && json.data?.katalog){
                catalogData = json.data.katalog;
                allItems = [];
                catalogData.forEach(cat => {
                    if(cat.items) cat.items.forEach(it => allItems.push(it));
                });
                renderPills();
                renderFilterModal();
                renderCatalog();
            } else {
                document.getElementById('catalogContainer').innerHTML = '<div class="cat-ld"><div>Gagal memuat menu.</div></div>';
            }
        } catch(e) {
            document.getElementById('catalogContainer').innerHTML = '<div class="cat-ld"><div style="color:#dc2626">Gagal menghubungi server.</div></div>';
        }
    }

    function renderPills(){
        const c = document.getElementById('categoryPillContainer');
        let h = '<button class="cpill ' + (selectedCategory === 0 ? 'active' : '') + '" onclick="filterCategory(0,this)">Semua</button>';
        catalogData.forEach(cat => {
            h += '<button class="cpill ' + (selectedCategory === cat.id ? 'active' : '') + '" onclick="filterCategory(' + cat.id + ',this)">' + escH(cat.nama_kategori) + '</button>';
        });
        c.innerHTML = h;
    }

    function renderFilterModal(){
        const c = document.getElementById('filterCategoryList');
        if(!c) return;
        let h = '<label style="display:flex;align-items:center;gap:7px;font-size:.83rem;cursor:pointer;padding:7px;border-radius:7px;border:1.5px solid var(--bd);"><input type="radio" name="fc" value="0" ' + (selectedCategory === 0 ? 'checked' : '') + ' onchange="filterCategory(0,null)"> Semua Menu</label>';
        catalogData.forEach(cat => {
            h += '<label style="display:flex;align-items:center;gap:7px;font-size:.83rem;cursor:pointer;padding:7px;border-radius:7px;border:1.5px solid var(--bd);"><input type="radio" name="fc" value="' + cat.id + '" ' + (selectedCategory === cat.id ? 'checked' : '') + ' onchange="filterCategory(' + cat.id + ',null)"> ' + escH(cat.nama_kategori) + '</label>';
        });
        c.innerHTML = h;
    }

    function filterCategory(catId, btn){
        selectedCategory = catId;
        document.querySelectorAll('.cpill').forEach(el => el.classList.remove('active'));
        if(btn) btn.classList.add('active');
        const t = catId === 0 ? 'Menu Nilamku' : (catalogData.find(c => c.id === catId)?.nama_kategori || 'Menu');
        document.getElementById('menuPanelTitle').textContent = t;
        renderCatalog();
    }

    function handleSearch(v){
        renderCatalog(v.trim().toLowerCase());
    }

    function renderCatalog(q = ''){
        const ct = document.getElementById('catalogContainer');
        let items = allItems;
        if(selectedCategory !== 0) items = items.filter(it => it.category_id == selectedCategory);
        if(q) items = items.filter(it => it.nama.toLowerCase().includes(q) || (it.deskripsi || '').toLowerCase().includes(q));

        if(items.length === 0){
            ct.innerHTML = '<div class="cat-ld"><div style="font-size:1.8rem;margin-bottom:7px;">&#128530;</div><div>Tidak ada menu ditemukan.</div></div>';
            return;
        }

        let html = '';
        items.forEach(item => {
            const av = item.status_tersedia === 'tersedia';
            const cq = cart[item.id] ? cart[item.id].qty : 0;
            const pr = 'Rp ' + Number(item.harga).toLocaleString('id-ID');
            const ph = item.foto_url ? fmtGd(item.foto_url) : '';

            let ctrl;
            if(!av){
                ctrl = '<button class="btn-add" disabled>Habis</button>';
            } else if(cq === 0){
                ctrl = '<button class="btn-add" onclick="addToCart(' + item.id + ')"><i class="bi bi-plus"></i> Tambah</button>';
            } else {
                ctrl = '<div class="qty-c"><button class="qb" onclick="updateCartQty(' + item.id + ',-1)">&#8722;</button><span class="qv">' + cq + '</span><button class="qb pl" onclick="updateCartQty(' + item.id + ',1)">+</button></div>';
            }

            const imgHtml = ph 
                ? '<img class="mc-img" src="' + escH(ph) + '" alt="' + escH(item.nama) + '" loading="lazy" onerror="this.onerror=null;this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';"><div class="mc-ph" style="display:none;">&#9749;</div>'
                : '<div class="mc-ph">&#9749;</div>';

            html += '<div class="mc">' +
                '<div class="mc-img-box">' + imgHtml + '</div>' +
                '<div class="mc-body">' +
                    '<div class="mc-hdr">' +
                        '<div class="mc-nm">' + escH(item.nama) + '</div>' +
                        '<div class="mc-pr">' + pr + '</div>' +
                    '</div>' +
                    '<div class="mc-ds">' + escH(item.deskripsi || 'Menu racikan nikmat khas Nilamku.') + '</div>' +
                    '<div class="mc-ft">' +
                        '<span class="' + (av ? 'bav' : 'bhbs') + '">' + (av ? 'Tersedia' : 'Habis') + '</span>' +
                        ctrl +
                    '</div>' +
                '</div>' +
            '</div>';
        });
        ct.innerHTML = html;
    }

    function addToCart(id){
        const item = allItems.find(it => it.id == id);
        if(!item) return;
        cart[id] = cart[id] ? { ...cart[id], qty: cart[id].qty + 1 } : { item, qty: 1 };
        renderCart();
        renderCatalog();
    }

    function updateCartQty(id, d){
        if(!cart[id]) return;
        cart[id].qty += d;
        if(cart[id].qty <= 0) delete cart[id];
        renderCart();
        renderCatalog();
    }

    function renderCart(){
        const le = document.getElementById('cartItemsList');
        const fe = document.getElementById('checkoutFormContent');
        const pe = document.getElementById('cartPrompt');
        const mobLe = document.getElementById('mob_cartItemsList');
        const mobFe = document.getElementById('mob_checkoutFormContent');
        const be = document.getElementById('mobileCountBadge');
        const me = document.getElementById('mobileTotalDisplay');

        const keys = Object.keys(cart);
        let tq = 0, sub = 0;

        if(keys.length === 0){
            const emptyHtml = '<div class="cem"><i class="bi bi-basket" style="font-size:1.7rem;display:block;margin-bottom:7px;opacity:.4;"></i>Belum ada item dipilih</div>';
            if(le) le.innerHTML = emptyHtml;
            if(mobLe) mobLe.innerHTML = emptyHtml;
            if(fe) fe.style.display = 'none';
            if(mobFe) mobFe.style.display = 'none';
            if(pe) pe.style.display = 'block';
            if(be) be.textContent = '0';
            if(me) me.textContent = 'Rp 0';
            calculateTotal(0);
            return;
        }

        if(fe) fe.style.display = 'flex';
        if(mobFe) mobFe.style.display = 'flex';
        if(pe) pe.style.display = 'none';

        let h = '';
        keys.forEach(k => {
            const e = cart[k];
            const s = e.qty * Number(e.item.harga);
            tq += e.qty;
            sub += s;
            const ph = e.item.foto_url ? fmtGd(e.item.foto_url) : '';
            h += '<div class="crow">' +
                '<div class="cthumb">' + (ph ? '<img src="' + escH(ph) + '" alt="" onerror="this.style.display=\'none\'">' : '&#9749;') + '</div>' +
                '<div class="cinfo">' +
                    '<div class="cnm">' + escH(e.item.nama) + '</div>' +
                    '<div class="cpr">Rp ' + Number(e.item.harga).toLocaleString('id-ID') + '</div>' +
                '</div>' +
                '<div class="qty-c">' +
                    '<button class="qb" onclick="updateCartQty(' + e.item.id + ',-1)">&#8722;</button>' +
                    '<span class="qv">' + e.qty + '</span>' +
                    '<button class="qb pl" onclick="updateCartQty(' + e.item.id + ',1)">+</button>' +
                '</div>' +
            '</div>';
        });

        if(le) le.innerHTML = h;
        if(mobLe) mobLe.innerHTML = h;
        if(be) be.textContent = tq;
        calculateTotal(sub);
    }

    function calculateTotal(sp){
        let s = sp;
        if(s === undefined){
            s = 0;
            Object.keys(cart).forEach(k => { s += cart[k].qty * Number(cart[k].item.harga); });
        }
        const fmt = 'Rp ' + s.toLocaleString('id-ID');
        const subEl = document.getElementById('subtotalDisplay');
        const ct2 = document.getElementById('cartTotalDisplay');
        const mt = document.getElementById('mobileTotalDisplay');
        const subMob = document.getElementById('subtotalDisplayMob');
        const ctMob = document.getElementById('cartTotalDisplayMob');

        if(subEl) subEl.textContent = fmt;
        if(ct2) ct2.textContent = fmt;
        if(mt) mt.textContent = fmt;
        if(subMob) subMob.textContent = fmt;
        if(ctMob) ctMob.textContent = fmt;
    }

    function setServiceMethod(m){
        serviceMethod = (m === 'takeaway') ? 'takeaway' : 'dine_in';
        const isDine = (serviceMethod === 'dine_in');
        const d = document.getElementById('optDineIn');
        const t = document.getElementById('optTakeAway');
        if(d) d.classList.toggle('sel', isDine);
        if(t) t.classList.toggle('sel', !isDine);

        const dMob = document.getElementById('optDineInMob');
        const tMob = document.getElementById('optTakeAwayMob');
        if(dMob) dMob.classList.toggle('sel', isDine);
        if(tMob) tMob.classList.toggle('sel', !isDine);
    }

    function previewBukti(input, containerId, imgId){
        const c = document.getElementById(containerId);
        const img = document.getElementById(imgId);
        if(input.files && input.files[0]){
            const r = new FileReader();
            r.onload = e => { img.src = e.target.result; c.style.display = 'block'; };
            r.readAsDataURL(input.files[0]);
        } else {
            c.style.display = 'none';
        }
    }

    async function processCheckout(isMobile = false){
        hideAlert();
        if(!isStoreOpen){
            showAlert('Mohon maaf, saat ini kedai sedang tutup.');
            new bootstrap.Modal(document.getElementById('modalTokoTutup')).show();
            return;
        }

        const keys = Object.keys(cart);
        if(keys.length === 0){
            showAlert('Keranjang belanja masih kosong.');
            return;
        }

        const prefix = isMobile ? 'mob_' : '';
        const nameEl = document.getElementById(prefix + 'customerName') || document.getElementById('customerName');
        const phoneEl = document.getElementById(prefix + 'customerPhone') || document.getElementById('customerPhone');
        const noteEl = document.getElementById(prefix + 'orderNote') || document.getElementById('orderNote');
        const biEl = document.getElementById(prefix + 'buktiTransferInput') || document.getElementById('buktiTransferInput');

        const name = (nameEl ? nameEl.value : '').trim();
        const phone = (phoneEl ? phoneEl.value : '').trim();
        let note = (noteEl ? noteEl.value : '').trim();

        if(!name){ showAlert('Mohon isi nama lengkap Anda.', isMobile); return; }
        if(!phone){ showAlert('Mohon isi nomor WhatsApp Anda.', isMobile); return; }
        if(!biEl || !biEl.files || biEl.files.length === 0){
            showAlert('Mohon unggah bukti transfer DANA.', isMobile);
            return;
        }

        let ci = [], sm = 0;
        keys.forEach(k => {
            const e = cart[k];
            const s = e.qty * Number(e.item.harga);
            sm += s;
            ci.push({ item_id: Number(e.item.id), jumlah: Number(e.qty), subtotal: Number(s) });
        });

        if(serviceMethod === 'takeaway' && !note.toLowerCase().includes('take away') && !note.toLowerCase().includes('takeaway')){
            note = note ? '[Take Away] ' + note : '[Take Away]';
        }

        const fd = new FormData();
        fd.append('nama', name);
        fd.append('no_wa', phone);
        fd.append('catatan', note);
        fd.append('cart_items', JSON.stringify(ci));
        fd.append('total_harga', sm);
        fd.append('metode_layanan', 'dine_in');
        fd.append('metode_pembayaran', 'transfer');
        fd.append('bukti_transfer', biEl.files[0]);

        const btn = document.getElementById(prefix + 'btnCheckout') || document.getElementById('btnCheckout');
        const btt = document.getElementById(prefix + 'btnCheckoutText') || document.getElementById('btnCheckoutText');
        const bsp = document.getElementById(prefix + 'btnCheckoutSpinner') || document.getElementById('btnCheckoutSpinner');

        if(btn) btn.disabled = true;
        if(btt) btt.textContent = 'Memproses...';
        if(bsp) bsp.style.display = 'inline-block';

        try {
            const res = await fetch('/api/booking/create', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            let r;
            try { r = await res.json(); } catch(e){ throw new Error('Format respons server tidak sesuai.'); }

            if(res.ok && (r.status === 'success' || r.order_id)){
                const oid = r.order_id || (r.data ? r.data.order_id : null);
                cart = {};
                renderCart();
                window.location.href = '/track/' + oid;
                return;
            } else {
                let em = r.message;
                if(!em && r.messages) em = typeof r.messages === 'object' ? Object.values(r.messages).join('<br>') : r.messages;
                showAlert(em || 'Pemesanan gagal. Periksa formulir Anda.', isMobile);
            }
        } catch(err) {
            showAlert(err.message || 'Gagal menghubungi server.', isMobile);
        } finally {
            if(btn) btn.disabled = false;
            if(btt) btt.innerHTML = '<i class="bi bi-bag-check-fill"></i> Konfirmasi Pesanan';
            if(bsp) bsp.style.display = 'none';
        }
    }

    function showAlert(m, isMobile = false){
        const b = document.getElementById(isMobile ? 'mob_checkoutAlertBox' : 'checkoutAlertBox');
        const msg = document.getElementById(isMobile ? 'mob_checkoutAlertMsg' : 'checkoutAlertMsg');
        if(msg) msg.innerHTML = m;
        if(b){
            b.style.display = 'block';
            b.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function hideAlert(){
        const b1 = document.getElementById('checkoutAlertBox');
        const b2 = document.getElementById('mob_checkoutAlertBox');
        if(b1) b1.style.display = 'none';
        if(b2) b2.style.display = 'none';
    }

    function goToTracking(){
        const v = document.getElementById('inputLacakId').value.trim();
        if(v && Number(v) > 0) window.location.href = '/track/' + v;
        else alert('Masukkan ID pesanan yang valid.');
    }

    function escH(t){
        if(!t) return '';
        return String(t).replace(/[&<>"']/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[m]));
    }

    function fmtGd(u){
        if(!u) return '';
        u = u.trim();
        if(u.includes('lh3.googleusercontent.com/d/')) return u;
        const m1 = u.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i);
        if(m1?.[1]) return 'https://lh3.googleusercontent.com/d/' + m1[1];
        const m2 = u.match(/(?:drive|docs)\.google\.com\/.*[?&]id=([a-zA-Z0-9_-]+)/i);
        if(m2?.[1]) return 'https://lh3.googleusercontent.com/d/' + m2[1];
        const m3 = u.match(/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/i);
        if(m3?.[1]) return 'https://lh3.googleusercontent.com/d/' + m3[1];
        return u;
    }
</script>
</body>
</html>
