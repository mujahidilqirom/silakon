{{--
    SILAKON — Data Konseli (Admin)
    Simpan sebagai: resources/views/admin/data-konseli.blade.php
    Route contoh:  Route::get('/admin/data-konseli', [AdminPendaftaranController::class, 'dataKonseli'])->name('admin.data-konseli');

    Data ($konseli, $totalPerluKonfirmasi, $duplikatProfil) dikirim dari AdminPendaftaranController@dataKonseli (masih dummy).
    PENTING: $konseli harus berisi SEMUA data (->get(), bukan ->paginate()), karena pencarian, tab,
    dan paginasi berjalan di browser. $totalData, $halamanAktif, $totalHalaman tidak dipakai lagi.
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.admin')

@section('judul', 'Data Konseli')

@section('content')

<style>
    .dk {
        --dk-primary: #7a9c7c;
        --dk-primary-dark: #4d7a55;
        --dk-ink: #1a2321;
        --dk-body: #4f5a56;
        --dk-muted: #7d8582;
        --dk-line: #e7eae6;
    }

    /* Header */
    .dk-head {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
    }
    .dk-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--dk-ink); }
    .dk-head p  { margin: 6px 0 0; font-size: 14px; color: var(--dk-muted); }

    .dk-btn-merge {
        display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
        height: 44px; padding: 0 20px; background: #fff;
        border: 1px solid var(--dk-primary); border-radius: 999px;
        font: inherit; font-size: 14px; font-weight: 600; color: var(--dk-primary-dark);
        cursor: pointer;
    }
    .dk-btn-merge svg { width: 17px; height: 17px; }
    .dk-btn-merge:hover { background: #f2f7f2; }
    .dk-btn-merge:focus-visible { outline: 2px solid var(--dk-primary-dark); outline-offset: 2px; }
    .dk-head-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .dk-btn-export { display:inline-flex; align-items:center; justify-content:center; height:44px; padding:0 15px; border:1px solid var(--dk-primary); border-radius:999px; background:#fff; color:var(--dk-primary-dark); font:inherit; font-size:13px; font-weight:700; text-decoration:none; }
    .dk-btn-export.primary { background:var(--dk-primary); color:#fff; }
    .dk-btn-export:hover { filter:brightness(.97); }

    /* Pencarian */
    .dk-search { margin-bottom: 20px; max-width: 420px; }
    .dk-input {
        width: 100%; height: 46px; padding: 0 16px; font: inherit; font-size: 14px; color: var(--dk-ink);
        background: #fff; border: 1px solid var(--dk-line); border-radius: 12px;
    }
    .dk-input::placeholder { color: #a3a9a6; }
    .dk-input:focus-visible { outline: 2px solid var(--dk-primary); outline-offset: 1px; }

    /* Tab */
    .dk-tabs { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .dk-tab {
        display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 18px;
        background: #eceeec; border: 1px solid transparent; border-radius: 999px;
        font: inherit; font-size: 14px; font-weight: 600; color: var(--dk-body); cursor: pointer;
    }
    .dk-tab.is-active { background: #fff; border-color: var(--dk-line); color: var(--dk-ink); box-shadow: 0 1px 2px rgba(20, 30, 25, .05); }
    .dk-tab:focus-visible { outline: 2px solid var(--dk-primary-dark); outline-offset: 2px; }
    .dk-badge {
        display: inline-grid; place-items: center; min-width: 18px; height: 18px; padding: 0 5px;
        background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; border-radius: 999px;
    }

    /* Tabel */
    .dk-card { background: #fff; border: 1px solid var(--dk-line); border-radius: 20px; overflow: hidden; }
    .dk-table { width: 100%; min-width: 0; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
    .dk-table th {
        padding: 14px 10px; text-align: left; vertical-align: middle; line-height: 1.3;
        font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
        color: #4a524f; background: #f6f7f5;
    }
    .dk-table td {
        padding: 13px 10px; border-top: 1px solid #eff1ee; font-size: 13.5px; color: var(--dk-body);
        vertical-align: middle; line-height: 1.4; white-space: nowrap;
    }
    /* Nama & email boleh turun ke baris kedua; kolom lain satu baris */
    .dk-table td.dk-name, .dk-table td.dk-can-wrap { white-space: normal; overflow-wrap: anywhere; }
    .dk-table tr:first-child td { border-top: 0; }
    .dk-table tr[hidden] { display: none; }
    .dk-table th:last-child,  .dk-table td:last-child  { padding-right: 16px; text-align: center; }

    /* Kolom No (1) & Total Pengajuan (5) rata tengah */
    .dk-table th:nth-child(1), .dk-table td:nth-child(1) { text-align: center; padding-left: 14px; padding-right: 6px; }
    .dk-table th:nth-child(5), .dk-table td:nth-child(5) { text-align: center; }
    .dk-no { color: var(--dk-muted); font-weight: 600; font-variant-numeric: tabular-nums; }

    /* Lebar kolom: kolom pendek px tetap, Nama 18%, Email mengisi sisa ruang */
    /* Kolom ramping: Nama & Email (tanpa lebar) berbagi sisa ruang dan boleh turun baris */
    .dk-table .c-no       { width: 52px; }
    .dk-table .c-jenis    { width: 150px; }
    .dk-table .c-total    { width: 100px; }
    .dk-table .c-aktif    { width: 92px; }
    .dk-table .c-terakhir { width: 124px; }
    .dk-table .c-aksi     { width: 60px; }

    .dk-name  { font-weight: 700; color: var(--dk-ink); }
    .dk-total { font-weight: 700; color: var(--dk-ink); }
    .dk-dash  { color: #b4bab7; }
    .dk-empty { padding: 40px 16px !important; text-align: center; color: var(--dk-muted) !important; }

    .dk-pill {
        display: inline-block; padding: 4px 11px; border-radius: 999px;
        font-size: 12px; font-weight: 600; line-height: 1.3; white-space: nowrap;
    }
    .dk-pill.jenis  { background: #eceeec; color: #4b5350; white-space: normal; border-radius: 12px; }
    .dk-pill.status { text-align: center; white-space: normal; max-width: 96px; }
    .dk-pill.tone-hijau  { background: #d9f5e3; color: #1f7a4a; }
    .dk-pill.tone-kuning { background: #fdf0b8; color: #8a5a00; }
    .dk-pill.tone-biru   { background: #dce8fc; color: #1d4ed8; }
    .dk-pill.tone-merah  { background: #fbdada; color: #b91c1c; }
    .dk-btn-detail {
        width: 34px; height: 34px; display: inline-grid; place-items: center; padding: 0;
        border: 1px solid var(--dk-primary); background: #f6faf6; color: var(--dk-primary-dark);
        border-radius: 8px; cursor: pointer;
    }
    .dk-btn-detail svg { width: 17px; height: 17px; }
    .dk-btn-detail:hover { background: #e6f2e7; }
    .dk-btn-detail:focus-visible { outline: 2px solid var(--dk-primary-dark); outline-offset: 2px; }
    .dk-btn-edit { border: 1px solid #d6dfd8; background: #fff; color: var(--dk-primary-dark); border-radius: 8px; padding: 8px 14px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
    .dk-btn-edit:hover { background: #f2f7f2; }
    .dk-detail-modal { max-width: 760px; max-height: 86vh; overflow-y: auto; }
    .dk-detail-group { border: 1px solid var(--dk-line); border-radius: 12px; overflow: hidden; margin: 14px 0; }
    .dk-detail-group h3 { margin: 0; padding: 12px 15px; background: #f6f8f5; font-size: 13px; color: var(--dk-primary-dark); }
    .dk-detail-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 0; }
    .dk-detail-item { padding: 12px 15px; border-top: 1px solid #edf0ec; }
    .dk-detail-item dt { font-size: 11px; color: var(--dk-muted); margin-bottom: 3px; }
    .dk-detail-item dd { margin: 0; color: var(--dk-ink); font-size: 13px; overflow-wrap: anywhere; white-space: pre-wrap; }
    .dk-riwayat-label { margin: 22px 0 8px; font-size: 13px; font-weight: 700; color: var(--dk-ink); }
    .dk-riwayat-carousel { display: grid; grid-template-columns: 38px minmax(0, 1fr) 38px; align-items: center; gap: 8px; }
    .dk-riwayat-scroll { display: flex; gap: 14px; overflow: hidden; overscroll-behavior-x: contain; scroll-snap-type: x mandatory; padding: 0 2px 10px; scroll-behavior: smooth; }
    .dk-riwayat-scroll .dk-detail-group { flex: 0 0 100%; margin: 0; scroll-snap-align: start; }
    .dk-riwayat-nav { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid #d6dfd8; border-radius: 50%; background: #fff; color: var(--dk-primary-dark); cursor: pointer; }
    .dk-riwayat-nav:hover { background: #f2f7f2; }
    .dk-riwayat-nav:disabled { opacity: .35; cursor: not-allowed; }
    .dk-riwayat-nav svg { width: 17px; height: 17px; }
    @media(max-width:600px){ .dk-detail-grid { grid-template-columns: 1fr; } }

    /* Footer */
    .dk-foot { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-top: 24px; }
    .dk-foot-left { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .dk-count { font-size: 14px; color: var(--dk-muted); }
    .dk-perpage { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--dk-muted); }
    .dk-select {
        appearance: none; -webkit-appearance: none; height: 36px; padding: 0 32px 0 12px; font: inherit; font-size: 14px;
        font-weight: 500; color: var(--dk-ink); cursor: pointer; border: 1px solid var(--dk-line); border-radius: 8px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a2321' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center;
    }
    .dk-select:focus-visible { outline: 2px solid var(--dk-primary); outline-offset: 1px; }

    .dk-pager { display: flex; gap: 8px; flex-wrap: wrap; }
    .dk-page {
        min-width: 40px; height: 34px; display: grid; place-items: center; padding: 0 10px;
        background: #fff; border: 1px solid var(--dk-line); border-radius: 8px;
        font: inherit; font-size: 14px; font-weight: 600; color: var(--dk-ink); cursor: pointer;
    }
    .dk-page svg { width: 14px; height: 14px; }
    .dk-page:hover:not(:disabled) { border-color: var(--dk-primary); }
    .dk-page.is-active { background: var(--dk-primary); border-color: var(--dk-primary); color: #fff; }
    .dk-page:disabled { opacity: .45; cursor: default; }
    .dk-page.is-gap { border-color: transparent; background: transparent; cursor: default; min-width: 24px; padding: 0; }
    .dk-page:focus-visible { outline: 2px solid var(--dk-primary-dark); outline-offset: 2px; }

    /* ===== Kartu konfirmasi duplikat ===== */
    .dk-konfirmasi-list { display: flex; flex-direction: column; gap: 16px; }
    .dk-konfirmasi-card { background: #fff; border: 1px solid var(--dk-line); border-radius: 20px; padding: 24px; }
    .dk-konfirmasi-head { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .dk-konfirmasi-head .dk-pill.status { max-width: none; white-space: nowrap; }
    .dk-konfirmasi-head span.dk-ket { font-size: 14px; color: var(--dk-body); }

    .dk-profil-row { display: flex; align-items: center; gap: 24px; margin-bottom: 20px; }
    .dk-profil-box {
        flex: 1 1 260px; max-width: 320px; background: #f6f7f5; border: 1px solid var(--dk-line);
        border-radius: 14px; padding: 16px;
    }
    .dk-profil-box .dk-profil-nama { font-weight: 700; color: var(--dk-ink); font-size: 15px; margin-bottom: 4px; }
    .dk-profil-box .dk-profil-email { font-size: 13px; color: var(--dk-body); margin-bottom: 8px; word-break: break-all; }
    .dk-profil-box .dk-profil-meta { font-size: 13px; color: var(--dk-muted); line-height: 1.6; }
    .dk-swap-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; color: #c3c9c5; flex-shrink: 0;
    }
    .dk-swap-icon svg { width: 20px; height: 20px; }

    .dk-konfirmasi-aksi { display: flex; gap: 12px; flex-wrap: wrap; }
    .dk-btn-ya, .dk-btn-bukan {
        height: 44px; padding: 0 20px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600;
        cursor: pointer;
    }
    .dk-btn-ya { background: var(--dk-primary-dark); border: 1px solid var(--dk-primary-dark); color: #fff; }
    .dk-btn-ya:hover { background: #416a48; }
    .dk-btn-bukan { background: #fff; border: 1px solid var(--dk-line); color: var(--dk-ink); }
    .dk-btn-bukan:hover { border-color: var(--dk-primary); }

    .dk-konfirmasi-kosong { padding: 48px 16px; text-align: center; color: var(--dk-muted); background: #fff; border: 1px solid var(--dk-line); border-radius: 20px; }

    /* ===== Modal gabungkan duplikat ===== */
    .dk-modal-overlay {
        position: fixed; inset: 0; background: rgba(20, 30, 25, .45);
        display: none; align-items: center; justify-content: center; padding: 20px; z-index: 50;
    }
    .dk-modal-overlay.is-open { display: flex; }
    .dk-modal {
        width: 100%; max-width: 480px; background: #fff; border-radius: 20px;
        padding: 28px; box-shadow: 0 20px 50px rgba(20, 30, 25, .25);
    }
    .dk-modal-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 4px; }
    .dk-modal-head h2 { margin: 0; font-size: 18px; font-weight: 700; color: var(--dk-ink); }
    .dk-modal-close {
        border: none; background: transparent; cursor: pointer; color: var(--dk-muted);
        width: 28px; height: 28px; display: grid; place-items: center; border-radius: 8px;
    }
    .dk-modal-close:hover { background: #f2f3f1; color: var(--dk-ink); }
    .dk-modal-close svg { width: 18px; height: 18px; }
    .dk-modal p.dk-modal-desc { margin: 0 0 22px; font-size: 14px; color: var(--dk-body); line-height: 1.5; }

    .dk-field { margin-bottom: 18px; }
    .dk-field label { display: block; font-size: 14px; font-weight: 600; color: var(--dk-ink); margin-bottom: 8px; }
    .dk-field .dk-input { border-radius: 10px; }

    .dk-warning {
        background: #fdf3d6; border: 1px solid #f5e4a3; color: #8a5a00;
        font-size: 13px; line-height: 1.5; border-radius: 12px; padding: 14px 16px; margin-bottom: 22px;
    }

    .dk-modal-foot { display: flex; justify-content: flex-end; gap: 12px; }
    .dk-btn-batal, .dk-btn-gabung {
        height: 44px; padding: 0 22px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;
    }
    .dk-btn-batal { background: #fff; border: 1px solid var(--dk-line); color: var(--dk-ink); }
    .dk-btn-batal:hover { border-color: var(--dk-primary); }
    .dk-btn-gabung { background: var(--dk-primary-dark); border: 1px solid var(--dk-primary-dark); color: #fff; }
    .dk-btn-gabung:hover { background: #416a48; }

    /* =====================================================================
       RESPONSIF
       - Tabel SELALU tetap tabel. Di layar sempit lebar minimumnya dijaga dan
         area tabel bisa digeser ke samping (kolom Aksi menempel di kanan).
       - <= 800px  : profil duplikat ditumpuk vertikal
       - <= 560px  : mobile (aman sampai 320px)
       ===================================================================== */
    .dk { min-width: 0; }
    .dk-pager[hidden] { display: none; }
    .dk-modal { max-height: calc(100vh - 40px); max-height: calc(100dvh - 40px); overflow-y: auto; }
    .dk-modal-head h2 { min-width: 0; }
    .dk-modal-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .dk-modal-close { font-size: 22px; line-height: 1; }
    .dk-riwayat-scroll { overflow-x: auto; scrollbar-width: none; }
    .dk-riwayat-scroll::-webkit-scrollbar { display: none; }

    /* Pembungkus tabel: geser horizontal + bayangan petunjuk di tepi */
    .dk-table-wrap {
        overflow-x: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain;
        background:
            linear-gradient(to right, #fff 30%, rgba(255,255,255,0)) left center,
            linear-gradient(to left,  #fff 30%, rgba(255,255,255,0)) right center,
            radial-gradient(farthest-side at 0 50%,  rgba(0,0,0,.12), rgba(0,0,0,0)) left center,
            radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.12), rgba(0,0,0,0)) right center;
        background-repeat: no-repeat;
        background-size: 28px 100%, 28px 100%, 10px 100%, 10px 100%;
        background-attachment: local, local, scroll, scroll;
    }

    /* Desktop: tabel selalu muat, TANPA scrollbar samping.
       Hanya di layar sempit tabel diberi lebar minimum dan bisa digeser;
       tombol detail (kolom Aksi) menempel di kanan. */
    @media (max-width: 1100px) {
        .dk-table { min-width: 860px; }
        .dk-table th:last-child, .dk-table td:last-child {
            position: sticky; right: 0; z-index: 1;
            box-shadow: -1px 0 0 #eff1ee;
        }
        .dk-table td:last-child { background: #fff; }
        .dk-table th:last-child { background: #f6f7f5; }
        .dk-table td.dk-empty:last-child { position: static; box-shadow: none; }
    }

    @media (max-width: 640px) {
        .dk-table { min-width: 820px; }
        .dk-table th { padding: 12px 8px; font-size: 10.5px; }
        .dk-table td { padding: 12px 8px; font-size: 13px; }
    }

    @media (max-width: 800px) {
        .dk-profil-row { flex-direction: column; align-items: stretch; gap: 10px; }
        .dk-profil-box { flex: none; max-width: none; width: 100%; box-sizing: border-box; }
        .dk-profil-row > .dk-profil-box:nth-child(1) { order: 1; }
        .dk-profil-row > .dk-swap-icon { order: 2; align-self: center; transform: rotate(90deg); }
        .dk-profil-row > .dk-profil-box:nth-child(2) { order: 3; }
    }

    @media (max-width: 560px) {
        /* Header */
        .dk-head { flex-direction: column; align-items: stretch; gap: 14px; margin-bottom: 18px; }
        .dk-head h1 { font-size: 20px; }
        .dk-head p { margin-top: 4px; font-size: 13.5px; }
        .dk-head-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .dk-btn-export { width: 100%; box-sizing: border-box; }
        .dk-btn-merge { grid-column: 1 / -1; width: 100%; justify-content: center; }

        /* Cari + tab */
        .dk-search { max-width: none; margin-bottom: 14px; }
        .dk-input, .dk-select { font-size: 16px; }
        .dk-tabs { gap: 8px; margin-bottom: 14px; }
        .dk-tab { flex: 1 1 auto; justify-content: center; height: 42px; padding: 0 12px; font-size: 13.5px; white-space: nowrap; }

        .dk-btn-detail { width: 36px; height: 36px; }

        /* Footer */
        .dk-foot { flex-direction: column; align-items: stretch; gap: 14px; margin-top: 16px; }
        .dk-foot-left { justify-content: space-between; gap: 8px 12px; }
        .dk-count, .dk-perpage { font-size: 13px; }
        .dk-select { height: 38px; }
        .dk-pager { flex-wrap: wrap; justify-content: center; gap: 4px; }
        .dk-page { min-width: 32px; height: 36px; padding: 0 6px; }
        .dk-page.is-gap { min-width: 16px; }

        /* Konfirmasi duplikat: juga list flat */
        .dk-konfirmasi-list { gap: 0; border-top: 1px solid var(--dk-line); }
        .dk-konfirmasi-card { background: transparent; border: 0; border-bottom: 1px solid var(--dk-line); border-radius: 0; padding: 18px 0; }
        .dk-konfirmasi-head { margin-bottom: 14px; }
        .dk-profil-row { margin-bottom: 16px; }
        .dk-profil-box { background: #fff; }
        .dk-konfirmasi-aksi { flex-direction: column; gap: 8px; }
        .dk-btn-ya, .dk-btn-bukan { width: 100%; }
        .dk-konfirmasi-kosong { background: transparent; border-width: 1px 0; border-radius: 0; padding: 36px 8px; }

        /* Modal */
        .dk-modal-overlay { padding: 12px; }
        .dk-modal { max-height: calc(100vh - 24px); max-height: calc(100dvh - 24px); padding: 16px; border-radius: 16px; }
        .dk-modal-head h2 { font-size: 16px; line-height: 1.3; }
        .dk-modal p.dk-modal-desc { margin-bottom: 16px; }
        .dk-modal-foot { gap: 8px; }
        .dk-btn-batal, .dk-btn-gabung { flex: 1; padding: 0 12px; }
        .dk-detail-modal .dk-modal-head {
            position: sticky; top: 0; z-index: 2; margin: -16px -16px 12px; padding: 14px 16px;
            background: #fff; border-bottom: 1px solid var(--dk-line); align-items: center;
        }
        .dk-detail-group { margin: 12px 0; }
        .dk-detail-item { padding: 10px 12px; }
        .dk-detail-group h3 { padding: 10px 12px; }

        /* Riwayat pengajuan: tombol navigasi di bawah, geser dengan swipe */
        .dk-riwayat-carousel { grid-template-columns: 1fr 1fr; grid-template-areas: "s s" "p n"; gap: 10px; }
        .dk-riwayat-scroll { grid-area: s; gap: 10px; }
        .dk-riwayat-carousel [data-riwayat-prev] { grid-area: p; justify-self: start; }
        .dk-riwayat-carousel [data-riwayat-next] { grid-area: n; justify-self: end; }

        /* Form edit: 16px agar iOS tidak zoom saat fokus */
        .dk-edit-form input, .dk-edit-form textarea { font-size: 16px !important; }
        .dk-edit-form .dk-btn-batal, .dk-edit-form .dk-btn-ya { flex: 1; }
    }

    @media (max-width: 380px) {
        .dk-tab { padding: 0 8px; font-size: 13px; gap: 6px; }
        .dk-page { min-width: 28px; padding: 0 4px; }
        .dk-pager { gap: 3px; }
    }
</style>

<div class="dk">

    {{-- ============ JUDUL + AKSI ============ --}}
    <div class="dk-head">
        <div>
            <h1>Data Konseli</h1>
            <p>Kelola data dan riwayat konseli</p>
        </div>

        <div class="dk-head-actions">
        <a class="dk-btn-export" href="{{ route('admin.data-konseli.ekspor', ['jenis'=>'pdf']) }}">PDF</a>
        <a class="dk-btn-export primary" href="{{ route('admin.data-konseli.ekspor', ['jenis'=>'excel']) }}">Excel</a>
        <button type="button" class="dk-btn-merge" id="dk-gabung">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M6 9v6"/><path d="M18 9a9 9 0 0 1-9 9"/><path d="m15 6 3-3 3 3"/><path d="M12 6h6"/>
            </svg>
            Gabungkan Duplikat
        </button>
        </div>
    </div>

    {{-- ============ PENCARIAN ============ --}}
    <div class="dk-search">
        <input type="search" class="dk-input" id="dk-q" placeholder="Cari nama atau email konseli..." aria-label="Cari konseli" autocomplete="off">
    </div>

    {{-- ============ TAB ============ --}}
    <div class="dk-tabs" role="tablist">
        <button type="button" class="dk-tab is-active" id="dk-tab-semua" role="tab" aria-selected="true">
            Semua Konseli
        </button>
        <button type="button" class="dk-tab" id="dk-tab-konfirmasi" role="tab" aria-selected="false">
            Perlu Dikonfirmasi
            @if($totalPerluKonfirmasi > 0)
                <span class="dk-badge">{{ $totalPerluKonfirmasi }}</span>
            @endif
        </button>
    </div>

    {{-- ============ VIEW: SEMUA KONSELI ============ --}}
    <div id="dk-view-semua">
        <div class="dk-card">
            <div class="dk-table-wrap">
                <table class="dk-table">
                    <colgroup>
                        <col class="c-no"><col class="c-nama"><col class="c-jenis"><col><col class="c-total"><col class="c-aktif"><col class="c-terakhir"><col class="c-aksi">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Email</th>
                            <th>Total Pengajuan</th>
                            <th>Aktif</th>
                            <th>Terakhir Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dk-body">
                        @foreach($konseli as $k)
                            <tr data-cari="{{ strtolower($k['nama'] . ' ' . $k['email']) }}">
                                <td data-label="No" class="dk-no">{{ $loop->iteration }}</td>
                                <td data-label="Nama" class="dk-name">{{ $k['nama'] }}</td>
                                <td data-label="Jenis"><span class="dk-pill jenis">{{ $k['jenis'] }}</span></td>
                                <td data-label="Email" class="dk-can-wrap">{{ $k['email'] }}</td>
                                <td data-label="Total Pengajuan" class="dk-total">{{ $k['total_tiket'] }}</td>
                                <td data-label="Aktif"@if($k['tiket_aktif'] <= 0) class="dk-aktif-0"@endif>
                                    @if($k['tiket_aktif'] > 0)
                                        <span class="dk-pill tone-biru">{{ $k['tiket_aktif'] }} aktif</span>
                                    @else
                                        <span class="dk-dash">-</span>
                                    @endif
                                </td>
                                <td data-label="Terakhir Daftar">{{ $k['terakhir_daftar'] }}</td>
                                <td data-label="Aksi"><button type="button" class="dk-btn-detail" aria-label="Lihat detail" title="Lihat detail" data-detail="{{ base64_encode(json_encode($k['detail'], JSON_UNESCAPED_UNICODE)) }}" data-edit="{{ base64_encode(json_encode($k['edit'], JSON_UNESCAPED_UNICODE)) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></td>
                            </tr>
                        @endforeach
                        <tr id="dk-empty" hidden>
                            <td colspan="8" class="dk-empty">Tidak ada konseli yang cocok dengan pencarian.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ FOOTER: JUMLAH, PER HALAMAN & PAGINASI ============ --}}
        <div class="dk-foot">
            <div class="dk-foot-left">
                <span class="dk-count" id="dk-count" aria-live="polite"></span>

                <label class="dk-perpage">
                    Tampilkan
                    <select class="dk-select" id="dk-perpage" aria-label="Jumlah data per halaman">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    data per halaman
                </label>
            </div>

            <nav class="dk-pager" id="dk-pager" aria-label="Halaman"></nav>
        </div>
    </div>

    {{-- ============ VIEW: PERLU DIKONFIRMASI ============ --}}
    <div id="dk-view-konfirmasi" hidden>
        @if($duplikatProfil->count() > 0)
            <div class="dk-konfirmasi-list">
                @foreach($duplikatProfil as $d)
                    <div class="dk-konfirmasi-card">
                        <div class="dk-konfirmasi-head">
                            <span class="dk-pill status tone-merah">Perlu Dikonfirmasi</span>
                            <span class="dk-ket">Sistem mendeteksi dua profil yang mungkin merupakan orang yang sama.</span>
                        </div>

                        <div class="dk-profil-row">
                            <div class="dk-profil-box">
                                <div class="dk-profil-nama">{{ $d['utama']['nama'] }}</div>
                                <div class="dk-profil-email">{{ $d['utama']['email'] }}</div>
                                <div class="dk-profil-meta">
                                    {{ $d['utama']['jenis'] }} &middot; {{ $d['utama']['total_tiket'] }} pengajuan<br>
                                    Daftar: {{ $d['utama']['terakhir_daftar'] }}
                                </div>
                            </div>

                            <div class="dk-profil-box">
                                <div class="dk-profil-nama">{{ $d['kandidat']['nama'] }}</div>
                                <div class="dk-profil-email">{{ $d['kandidat']['email'] }}</div>
                                <div class="dk-profil-meta">
                                    {{ $d['kandidat']['jenis'] }} &middot; {{ $d['kandidat']['total_tiket'] }} pengajuan<br>
                                    Daftar: {{ $d['kandidat']['terakhir_daftar'] }}
                                </div>
                            </div>

                            <span class="dk-swap-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m17 3 4 4-4 4"/><path d="M3 7h18"/><path d="m7 21-4-4 4-4"/><path d="M21 17H3"/>
                                </svg>
                            </span>
                        </div>

                        <div class="dk-konfirmasi-aksi">
                            <button type="button" class="dk-btn-ya" data-aksi-konfirmasi>Ya, Sama Orang</button>
                            <button type="button" class="dk-btn-bukan" data-aksi-konfirmasi>Bukan Orang yang Sama</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="dk-konfirmasi-kosong">Tidak ada profil yang perlu dikonfirmasi saat ini.</div>
        @endif
    </div>
</div>

{{-- ============ MODAL: GABUNGKAN PROFIL DUPLIKAT ============ --}}
<div class="dk-modal-overlay" id="dk-detail-overlay">
    <div class="dk-modal dk-detail-modal" role="dialog" aria-modal="true" aria-labelledby="dk-detail-title">
        <div class="dk-modal-head"><h2 id="dk-detail-title">Detail Data Konseli</h2><div class="dk-modal-actions"><button type="button" class="dk-btn-edit" id="dk-detail-edit">Edit data</button><button type="button" class="dk-modal-close" id="dk-detail-close" aria-label="Tutup">×</button></div></div>
        <div id="dk-detail-content"></div>
    </div>
</div>

<div class="dk-modal-overlay" id="dk-modal-overlay">
    <div class="dk-modal" role="dialog" aria-modal="true" aria-labelledby="dk-modal-title">
        <div class="dk-modal-head">
            <h2 id="dk-modal-title">Gabungkan Profil Duplikat</h2>
            <button type="button" class="dk-modal-close" id="dk-modal-close" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="dk-modal-desc">Cari dan gabungkan dua profil konseli yang merupakan orang yang sama.</p>

        <div class="dk-field">
            <label for="dk-profil-utama">Profil Utama</label>
            <input type="search" class="dk-input" id="dk-profil-utama" placeholder="Cari nama konseli..." autocomplete="off">
        </div>

        <div class="dk-field">
            <label for="dk-profil-gabung">Profil yang Akan Digabung</label>
            <input type="search" class="dk-input" id="dk-profil-gabung" placeholder="Cari nama konseli..." autocomplete="off">
        </div>

        <div class="dk-warning">
                            Semua pengajuan dari profil yang digabung akan dipindahkan ke profil utama dan profil lama akan dihapus.
        </div>

        <div class="dk-modal-foot">
            <button type="button" class="dk-btn-batal" id="dk-modal-batal">Batal</button>
            <button type="button" class="dk-btn-gabung" id="dk-modal-gabung">Gabungkan</button>
        </div>
    </div>
</div>

<script>
(function () {
    const detailOverlay = document.getElementById('dk-detail-overlay');
    const detailContent = document.getElementById('dk-detail-content');
    function decodeDetail(value) {
        const bytes = Uint8Array.from(atob(value), c => c.charCodeAt(0));
        return JSON.parse(new TextDecoder('utf-8').decode(bytes));
    }
    const updateUrlTemplate = @json(route('admin.data-konseli.update', ['id' => '__ID__']));
    const esc = value => String(value ?? '-').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
    let activeEdit = null;
    function bukaDetail(detail, edit) {
        activeEdit = edit;
        const renderSection = ([judul, data]) =>
            '<section class="dk-detail-group"><h3>' + judul + '</h3><dl class="dk-detail-grid">' +
            Object.entries(data).map(([label, value]) => '<div class="dk-detail-item"><dt>' + esc(label) + '</dt><dd>' + esc(Array.isArray(value) ? value.join(', ') : value) + '</dd></div>').join('') +
            '</dl></section>'
        ;
        const entries = Object.entries(detail);
        const identitas = entries.filter(([judul]) => !judul.startsWith('Pengajuan '));
        const pengajuan = entries.filter(([judul]) => judul.startsWith('Pengajuan '));
        const sections = identitas.map(renderSection).join('') + (pengajuan.length ? '<div class="dk-riwayat-label">Riwayat pengajuan</div><div class="dk-riwayat-carousel"><button type="button" class="dk-riwayat-nav" data-riwayat-prev aria-label="Pengajuan sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button><div class="dk-riwayat-scroll" data-riwayat-scroll>' + pengajuan.map(renderSection).join('') + '</div><button type="button" class="dk-riwayat-nav" data-riwayat-next aria-label="Pengajuan berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button></div>' : '');
        const extra = Object.entries(edit.data || {}).filter(([key]) => key !== '_riwayat_pengajuan').map(([key, value]) => {
            const array = Array.isArray(value);
            const shown = array ? value.join('\n') : (value ?? '');
            return '<label style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#4f5a56"><span>' + esc(key.replaceAll('_', ' ')) + '</span><textarea rows="' + (array ? 3 : 2) + '" data-extra-key="' + esc(key) + '" data-extra-kind="' + (array ? 'array' : 'text') + '" style="width:100%;box-sizing:border-box;border:1px solid #e1e7e3;border-radius:8px;padding:8px;font:inherit;resize:vertical">' + esc(shown) + '</textarea></label>';
        }).join('');
        detailContent.innerHTML = sections + '<form class="dk-edit-form" id="dk-edit-form" hidden method="POST" action="' + updateUrlTemplate.replace('__ID__', encodeURIComponent(edit.id)) + '">' +
            '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="data_json" id="dk-data-json">' +
            '<h3 style="margin:24px 0 12px;color:#4d7a55">Ubah data konseli</h3><div class="dk-detail-grid" style="gap:12px">' +
            '<label style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#4f5a56">Nama lengkap<input name="nama" required value="' + esc(edit.nama) + '" style="padding:9px;border:1px solid #e1e7e3;border-radius:8px;font:inherit"></label>' +
            '<label style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#4f5a56">Email<input name="email" type="email" required value="' + esc(edit.email) + '" style="padding:9px;border:1px solid #e1e7e3;border-radius:8px;font:inherit"></label>' +
            '<label style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#4f5a56">NIM<input name="nim" value="' + esc(edit.nim) + '" style="padding:9px;border:1px solid #e1e7e3;border-radius:8px;font:inherit"></label>' +
            '<label style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#4f5a56">Nomor telepon<input name="telepon" value="' + esc(edit.telepon) + '" style="padding:9px;border:1px solid #e1e7e3;border-radius:8px;font:inherit"></label></div>' +
            (extra ? '<div style="display:flex;flex-direction:column;gap:10px;margin-top:14px">' + extra + '</div>' : '') +
            '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px"><button type="button" class="dk-btn-batal" id="dk-edit-cancel">Batal</button><button type="submit" class="dk-btn-ya">Simpan perubahan</button></div></form>';
        const previous = detailContent.querySelector('[data-riwayat-prev]');
        const following = detailContent.querySelector('[data-riwayat-next]');
        if (previous) previous.disabled = true;
        if (following) following.disabled = pengajuan.length <= 1;
        detailOverlay.classList.add('is-open');
    }
    document.querySelectorAll('.dk-btn-detail').forEach(btn => btn.addEventListener('click', () => bukaDetail(decodeDetail(btn.dataset.detail), decodeDetail(btn.dataset.edit))));
    document.getElementById('dk-detail-edit').addEventListener('click', () => {
        const form = document.getElementById('dk-edit-form');
        if (form) { form.hidden = false; form.scrollIntoView({behavior: 'smooth', block: 'start'}); }
    });
    detailContent.addEventListener('click', e => { if (e.target.id === 'dk-edit-cancel') document.getElementById('dk-edit-form').hidden = true; });
    detailContent.addEventListener('submit', e => {
        if (e.target.id !== 'dk-edit-form') return;
        const data = {};
        e.target.querySelectorAll('[data-extra-key]').forEach(field => {
            data[field.dataset.extraKey] = field.dataset.extraKind === 'array' ? field.value.split(/\r?\n/).map(v => v.trim()).filter(Boolean) : field.value;
        });
        e.target.querySelector('#dk-data-json').value = JSON.stringify(data);
    });
    detailContent.addEventListener('click', e => {
        const button = e.target.closest('[data-riwayat-prev], [data-riwayat-next]');
        if (!button) return;
        const scroll = detailContent.querySelector('[data-riwayat-scroll]');
        if (!scroll) return;
        const cards = Array.from(scroll.querySelectorAll('.dk-detail-group'));
        const current = Math.round(scroll.scrollLeft / scroll.clientWidth);
        const next = button.hasAttribute('data-riwayat-next') ? current + 1 : current - 1;
        if (cards[next]) cards[next].scrollIntoView({behavior: 'smooth', inline: 'start', block: 'nearest'});
        setTimeout(() => {
            const index = Math.round(scroll.scrollLeft / scroll.clientWidth);
            const prev = detailContent.querySelector('[data-riwayat-prev]');
            const nextButton = detailContent.querySelector('[data-riwayat-next]');
            if (prev) prev.disabled = index <= 0;
            if (nextButton) nextButton.disabled = index >= cards.length - 1;
        }, 350);
    });
    detailContent.addEventListener('scroll', e => {
        const scroll = e.target.closest ? e.target.closest('[data-riwayat-scroll]') : null;
        if (!scroll) return;
        const total = scroll.querySelectorAll('.dk-detail-group').length;
        const index = Math.round(scroll.scrollLeft / scroll.clientWidth);
        const prev = detailContent.querySelector('[data-riwayat-prev]');
        const nextButton = detailContent.querySelector('[data-riwayat-next]');
        if (prev) prev.disabled = index <= 0;
        if (nextButton) nextButton.disabled = index >= total - 1;
    }, true);
    document.getElementById('dk-detail-close').addEventListener('click', () => detailOverlay.classList.remove('is-open'));
    detailOverlay.addEventListener('click', e => { if (e.target === detailOverlay) detailOverlay.classList.remove('is-open'); });

    const rows        = Array.from(document.querySelectorAll('#dk-body tr[data-cari]'));
    const empty       = document.getElementById('dk-empty');
    const count       = document.getElementById('dk-count');
    const q           = document.getElementById('dk-q');
    const tabSemua    = document.getElementById('dk-tab-semua');
    const tabKonf     = document.getElementById('dk-tab-konfirmasi');
    const viewSemua   = document.getElementById('dk-view-semua');
    const viewKonf    = document.getElementById('dk-view-konfirmasi');

    // ---------- Pencarian + paginasi (hanya di tab "Semua Konseli") ----------
    const pager   = document.getElementById('dk-pager');
    const perPage = document.getElementById('dk-perpage');
    const ICON_PREV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
    const ICON_NEXT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';

    let hasil = rows.slice();
    let halaman = 1;

    function terapkanPencarian() {
        const kata = q.value.trim().toLowerCase();
        hasil = rows.filter(r => !kata || r.dataset.cari.includes(kata));
        halaman = 1;
        tampilkan();
    }

    function tombol(isi, opsi) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'dk-page' + (opsi.aktif ? ' is-active' : '');
        b.innerHTML = isi;
        if (opsi.label) b.setAttribute('aria-label', opsi.label);
        if (opsi.aktif) b.setAttribute('aria-current', 'page');
        if (opsi.disabled) b.disabled = true;
        if (opsi.ke) b.addEventListener('click', function () { halaman = opsi.ke; tampilkan(); });
        return b;
    }

    function daftarHalaman(total, aktif) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        const set = new Set([1, total, aktif, aktif - 1, aktif + 1]);
        if (aktif <= 3) { set.add(2); set.add(3); set.add(4); }
        if (aktif >= total - 2) { set.add(total - 1); set.add(total - 2); set.add(total - 3); }
        const urut = Array.from(set).filter(n => n >= 1 && n <= total).sort((a, b) => a - b);
        const out = [];
        urut.forEach(function (n, i) {
            if (i && n - urut[i - 1] > 1) out.push('...');
            out.push(n);
        });
        return out;
    }

    function tampilkan() {
        const total = hasil.length;
        const per   = parseInt(perPage.value, 10);
        const totalHalaman = Math.max(1, Math.ceil(total / per));
        if (halaman > totalHalaman) halaman = totalHalaman;

        const awal   = (halaman - 1) * per;
        const akhir  = Math.min(awal + per, total);
        const tampil = new Set(hasil.slice(awal, akhir));

        rows.forEach(function (r) { r.hidden = !tampil.has(r); });
        empty.hidden = total > 0;
        count.textContent = total ? `${awal + 1}-${akhir} dari ${total} konseli` : '0 konseli';

        // Nomor urut berlanjut antar halaman (halaman 2 mulai dari per+1, dst.)
        hasil.slice(awal, akhir).forEach(function (r, i) {
            const n = r.querySelector('.dk-no');
            if (n) n.textContent = awal + i + 1;
        });

        // Paginasi selalu tampil (tombol dinonaktifkan bila hanya satu halaman)
        pager.innerHTML = '';
        pager.hidden = false;

        pager.appendChild(tombol(ICON_PREV, { label: 'Halaman sebelumnya', disabled: halaman <= 1, ke: halaman - 1 }));
        daftarHalaman(totalHalaman, halaman).forEach(function (n) {
            if (n === '...') {
                const g = document.createElement('span');
                g.className = 'dk-page is-gap';
                g.textContent = '…';
                pager.appendChild(g);
            } else {
                pager.appendChild(tombol(String(n), { aktif: n === halaman, ke: n, label: 'Halaman ' + n }));
            }
        });
        pager.appendChild(tombol(ICON_NEXT, { label: 'Halaman berikutnya', disabled: halaman >= totalHalaman, ke: halaman + 1 }));
    }

    // ---------- Tab: tukar tampilan, bukan filter baris ----------
    function pilihTab(aktif) {
        const isSemua = aktif === 'semua';

        tabSemua.classList.toggle('is-active', isSemua);
        tabSemua.setAttribute('aria-selected', isSemua);
        tabKonf.classList.toggle('is-active', !isSemua);
        tabKonf.setAttribute('aria-selected', !isSemua);

        viewSemua.hidden = !isSemua;
        viewKonf.hidden = isSemua;
    }

    q.addEventListener('input', terapkanPencarian);
    perPage.addEventListener('change', function () { halaman = 1; tampilkan(); });
    tampilkan();
    tabSemua.addEventListener('click', () => pilihTab('semua'));
    tabKonf.addEventListener('click', () => pilihTab('perlu_dikonfirmasi'));

    // ---------- Modal "Gabungkan Profil Duplikat" ----------
    const overlay     = document.getElementById('dk-modal-overlay');
    const btnBuka      = document.getElementById('dk-gabung');
    const btnTutup     = document.getElementById('dk-modal-close');
    const btnBatal     = document.getElementById('dk-modal-batal');
    const btnGabung    = document.getElementById('dk-modal-gabung');
    const inputUtama   = document.getElementById('dk-profil-utama');
    const inputGabung  = document.getElementById('dk-profil-gabung');

    function bukaModal() {
        overlay.classList.add('is-open');
        inputUtama.value = '';
        inputGabung.value = '';
        inputUtama.focus();
    }

    function tutupModal() {
        overlay.classList.remove('is-open');
    }

    btnBuka.addEventListener('click', bukaModal);
    btnTutup.addEventListener('click', tutupModal);
    btnBatal.addEventListener('click', tutupModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) tutupModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) tutupModal();
    });

    // Stub aksi — belum terhubung ke backend.
    btnGabung.addEventListener('click', function () {
        alert('Fitur gabungkan profil akan segera terhubung ke backend.');
        tutupModal();
    });

    document.querySelectorAll('[data-aksi-konfirmasi]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            alert('Aksi konfirmasi duplikat akan segera terhubung ke backend.');
        });
    });
})();
</script>
@endsection