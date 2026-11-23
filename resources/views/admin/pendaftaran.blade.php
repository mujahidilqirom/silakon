{{--
    SILAKON — Pendaftaran Masuk (antrian verifikasi)
    Simpan sebagai: resources/views/admin/pendaftaran.blade.php

    Halaman ini HANYA menampilkan pengajuan berstatus "menunggu verifikasi"
    (tone = 'menunggu'), diurutkan dari tanggal paling lama ke paling baru
    (ascending) supaya yang masuk duluan diverifikasi duluan.

    Data ($pengajuan, $totalHalaman, $halamanAktif) tetap dikirim dari
    AdminPendaftaranController@index. Penyaringan status & pengurutan tanggal
    dilakukan di view ini sebagai pengaman, tapi idealnya dilakukan di
    controller/query, mis.:
        Pengajuan::where('status', 'menunggu')->orderBy('created_at')->paginate(10)
    Kalau begitu, hapus blok PHP "antrian" di bawah dan pakai datanya langsung.

    Aksi per baris: Detail + Verifikasi (tombol centang). Tombol verifikasi
    masih placeholder (href="#") — sambungkan ke route verifikasi (POST) milik Anda.

    Pagination, pilihan jumlah data per halaman (10/25/50), dan pencarian memakai
    query string (?page=, ?per_page=, ?q=) dan diproses di server, jadi hanya baris
    pada halaman aktif yang dirender ke HTML. Pastikan layouts.admin punya
    @stack('styles') di <head> dan @stack('scripts') sebelum </body>.
--}}
@extends('layouts.admin')

@section('judul', 'Manajemen Pengajuan')
{{-- Subjudul tidak di-override, otomatis pakai tanggal hari ini dari navbar.blade.php --}}

@push('styles')
<style>
    /* ===== Search bar ===== */
    .admin-header-actions{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }
    .admin-search{
        position:relative;
        width:220px;
    }
    .admin-search input{
        width:100%;
        padding:9px 12px 9px 34px;
        border:1px solid #E2E5E1;
        border-radius:10px;
        font-size:13.5px;
        color:#1F2A24;
        background:#fff;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .admin-search input:focus{
        border-color:#3F6D5D;
        box-shadow:0 0 0 3px rgba(63,109,93,.12);
    }
    .admin-search svg{
        position:absolute;
        left:11px; top:50%;
        transform:translateY(-50%);
        width:15px; height:15px;
        color:#9AA39C;
        pointer-events:none;
    }

    /* ===== Export buttons ===== */
    .admin-export-group{ display:flex; gap:10px; }
    .admin-btn-export{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:9px 16px;
        border-radius:10px;
        border:1px solid #E2E5E1;
        background:#fff;
        color:#1F2A24;
        font-size:13px;
        font-weight:500;
        text-decoration:none;
        cursor:pointer;
        transition:border-color .15s ease, background .15s ease;
    }
    .admin-btn-export:hover{ border-color:#3F6D5D; background:#F7F9F7; }
    .admin-btn-export svg{ width:16px; height:16px; }

    /* ===== Status pill tones khusus halaman pengajuan ===== */
    .admin-pill.tone-menunggu     { background:#FEF3C7; color:#92400E; }
    .admin-pill.tone-diverifikasi { background:#DBEAFE; color:#1E40AF; }
    .admin-pill.tone-terjadwal    { background:#CCFBF1; color:#0F766E; }
    .admin-pill.tone-ditugaskan   { background:#EDE9FE; color:#5B21B6; }
    .admin-pill.tone-selesai      { background:#DCFCE7; color:#166534; }
    .admin-pill.tone-ditolak      { background:#FEE2E2; color:#991B1B; }

    /* ===== Tabel — sedikit polish, tanpa ubah struktur admin-table lama ===== */
    .admin-table-wrap .admin-table tbody tr{
        transition:background .12s ease;
    }
    .admin-table-wrap .admin-table tbody tr:hover{
        background:#F7F9F7;
    }
    .admin-table-wrap .admin-table tbody tr[hidden]{
        display:none;
    }

    .admin-cell-name span{
        display:block;
        color:#8C948D;
        font-size:12.5px;
        margin-top:2px;
    }
    .admin-cell-type{ white-space:nowrap; }
    .admin-col-no,
    .admin-cell-no{ width:48px; min-width:48px; text-align:center; color:#68756C; font-weight:600; }
    .admin-pill.jenis-konseli{ background:#EEF3F0; color:#2F5446; white-space:nowrap; }
    .admin-priority{display:inline-flex;flex-direction:column;gap:2px;align-items:flex-start;white-space:nowrap}
    .admin-priority strong{font-size:12px}.admin-priority small{font-size:11px;color:#7A867D}
    .admin-priority.tinggi strong,.admin-priority.darurat strong{color:#B42318}.admin-priority.sedang strong{color:#A15C00}.admin-priority.rendah strong{color:#2F6A4B}.admin-priority.belum strong{color:#7A867D}
    .admin-screening-cell{display:inline-flex;flex-direction:column;gap:2px;white-space:nowrap}
    .admin-screening-cell small{font-size:11.5px;color:#66736B}

    /* ===== Kolom aksi (tombol ikon) ===== */
    .admin-aksi{
        display:flex;
        align-items:center;
        gap:8px;
    }
    .admin-btn-icon{
        width:34px;
        height:34px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0;
        border-radius:9px;
        border:1px solid transparent;
        cursor:pointer;
        text-decoration:none;
        transition:background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .admin-btn-icon svg{ width:17px; height:17px; }
    .admin-aksi form{ margin:0; display:flex; }

    .admin-flash{
        display:flex; align-items:flex-start; gap:10px;
        margin-bottom:16px; padding:12px 16px; border-radius:12px;
        font-size:13.5px; line-height:1.5;
        border:1px solid #BBE5CC; background:#EAF7F0; color:#14532D;
    }
    .admin-flash svg{ width:18px; height:18px; flex-shrink:0; margin-top:1px; }
    .admin-flash.is-error{ border-color:#FECACA; background:#FEF2F2; color:#991B1B; }
    .admin-btn-icon:focus-visible{
        outline:none;
        box-shadow:0 0 0 3px rgba(63,109,93,.28);
    }

    /* Detail: outline netral */
    .admin-btn-detail{
        background:#fff;
        border-color:#E2E5E1;
        color:#4B554E;
    }
    .admin-btn-detail:hover{
        background:#F7F9F7;
        border-color:#3F6D5D;
        color:#3F6D5D;
    }
    .admin-btn-wa{ background:#EAF7F0; border-color:#BFE5CC; color:#18864B; }
    .admin-btn-wa:hover{ background:#D9F2E2; border-color:#76C99A; color:#126B3B; }

    /* Verifikasi: hijau solid, kotak membulat */
    .admin-btn-verify{
        background:linear-gradient(180deg,#4A7E6C 0%,#3F6D5D 100%);
        color:#fff;
        box-shadow:0 1px 2px rgba(31,42,36,.14), inset 0 1px 0 rgba(255,255,255,.18);
    }
    .admin-btn-verify:hover{
        background:linear-gradient(180deg,#3F6D5D 0%,#335A4C 100%);
        transform:translateY(-1px);
        box-shadow:0 5px 12px rgba(63,109,93,.32);
    }
    .admin-btn-verify:active{
        transform:translateY(0);
        box-shadow:0 1px 2px rgba(31,42,36,.14);
    }

    .admin-kode{
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size:13px;
        letter-spacing:.2px;
        background:#EEF1ED;
        padding:3px 8px;
        border-radius:6px;
        color:#3F6D5D;
    }

    .admin-empty-state{
        text-align:center;
        padding:40px 0;
        color:#8C948D;
        font-size:14px;
    }

    /* ===== Pagination ===== */
    .admin-pagination-bar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:18px 24px 20px;
        border-top:1px solid #EEF1ED;
        flex-wrap:wrap;
        gap:12px;
    }
    .admin-pagination-info{
        font-size:13px;
        color:#6B756E;
    }
    .admin-pagination{
        display:flex;
        gap:6px;
    }
    .admin-page-btn{
        min-width:34px;
        height:34px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:8px;
        border:1px solid #E2E5E1;
        background:#fff;
        color:#4B554E;
        font-size:13px;
        font-weight:500;
        text-decoration:none;
        cursor:pointer;
        transition:border-color .15s ease, background .15s ease, color .15s ease;
    }
    .admin-page-btn:hover{ border-color:#3F6D5D; }
    .admin-page-btn.is-active{
        background:#3F6D5D;
        border-color:#3F6D5D;
        color:#fff;
    }
    .admin-page-btn.is-disabled{
        opacity:.45;
        pointer-events:none;
    }
    .admin-page-gap{
        min-width:22px;
        height:34px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        color:#9AA39C;
        font-size:13px;
    }
    .admin-pagination-left{
        display:flex;
        align-items:center;
        gap:18px;
        flex-wrap:wrap;
    }
    .admin-perpage{
        display:inline-flex;
        align-items:center;
        gap:8px;
        font-size:13px;
        color:#6B756E;
    }
    .admin-perpage select{
        height:34px;
        padding:0 28px 0 10px;
        border:1px solid #E2E5E1;
        border-radius:8px;
        background:#fff;
        color:#1F2A24;
        font-size:13px;
        font-weight:500;
        cursor:pointer;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .admin-perpage select:focus{
        border-color:#3F6D5D;
        box-shadow:0 0 0 3px rgba(63,109,93,.12);
    }

    /* =====================================================================
       RESPONSIF — ditaruh di sini (setelah gaya dasar) supaya tidak tertimpa
       ===================================================================== */
    .admin-card-head--filter{ align-items:flex-start; }
    .admin-table-wrap--pengajuan{ margin-top:24px; }
    .admin-header-actions{ min-width:0; }
    .admin-search input{ min-width:0; text-overflow:ellipsis; }
    .admin-table-wrap .admin-table td.admin-cell-name{ min-width:150px; }
    .admin-table-wrap .admin-table .admin-kode{ white-space:nowrap; }
    .admin-table-wrap .admin-table td:nth-child(5){ white-space:nowrap; }

    /* Laptop kecil / tablet: judul di atas, pencarian + export satu baris di bawahnya */
    @media (max-width:1100px){
        .admin-card-head--filter .admin-header-actions{ width:100%; }
        .admin-card-head--filter .admin-search{ flex:1 1 240px; width:auto; }
        .admin-export-group{ flex:0 0 auto; }
    }

    /* Tablet kecil & HP */
    @media (max-width:760px){
        .admin-card-head--filter .admin-header-actions{
            flex-direction:column;
            align-items:stretch;
            gap:10px;
        }
        .admin-card-head--filter .admin-search{ width:100%; flex:none; }
        .admin-search input{ font-size:16px; padding:11px 12px 11px 36px; } /* 16px: iOS tidak zoom saat fokus */
        .admin-search svg{ left:12px; }
        .admin-export-group{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:8px;
            width:100%;
        }
        .admin-btn-export{ justify-content:center; width:100%; min-height:40px; padding:10px 8px; }

        .admin-table-wrap--pengajuan{ margin-top:16px; }

        .admin-pagination-bar{
            flex-direction:column;
            align-items:stretch;
            gap:12px;
            padding:14px 16px 16px;
        }
        .admin-pagination-left{ justify-content:space-between; gap:8px 12px; }
        .admin-pagination{
            width:100%;
            justify-content:center;
            flex-wrap:wrap;
            overflow:visible;
            gap:6px;
            padding:0;
        }
        .admin-page-btn{ min-width:38px; height:38px; }
        .admin-page-gap{ height:38px; }
        .admin-perpage select{ height:38px; }
    }


    /* =====================================================================
       TABEL PAS — tanpa scroll ke samping
       Desktop/laptop: tabel selalu selebar kartu, kolom menyesuaikan.
       Layar sempit: tetap tabel, bisa digeser ke samping (min-width dijaga).
       ===================================================================== */
    .admin-card-head--filter{ flex-wrap:wrap; gap:14px 20px; }
    .admin-card-head--filter > div:first-child{ flex:1 1 280px; min-width:0; }
    .admin-card-head--filter .admin-header-actions{ flex:1 1 auto; justify-content:flex-end; flex-wrap:nowrap; }
    .admin-card-head--filter .admin-search{ flex:1 1 200px; width:auto; max-width:300px; min-width:150px; }
    .admin-export-group{ flex:0 0 auto; }

    .admin-table-wrap--pengajuan{
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        overscroll-behavior-x:contain;
        background:
            linear-gradient(to right,#fff 30%,rgba(255,255,255,0)) left center,
            linear-gradient(to left,#fff 30%,rgba(255,255,255,0)) right center,
            radial-gradient(farthest-side at 0 50%,rgba(0,0,0,.12),rgba(0,0,0,0)) left center,
            radial-gradient(farthest-side at 100% 50%,rgba(0,0,0,.12),rgba(0,0,0,0)) right center;
        background-repeat:no-repeat;
        background-size:28px 100%,28px 100%,10px 100%,10px 100%;
        background-attachment:local,local,scroll,scroll;
    }
    .admin-table-wrap--pengajuan .admin-table{
        width:100%;
        min-width:900px;   /* di bawah ini tabel digeser, bukan dipadatkan */
        table-layout:auto;
    }
    .admin-table-wrap--pengajuan .admin-table th,
    .admin-table-wrap--pengajuan .admin-table td{ white-space:nowrap; }
    .admin-table-wrap--pengajuan .admin-table td.admin-cell-name{ white-space:normal; min-width:170px; }
    .admin-table-wrap--pengajuan .admin-table td.admin-cell-name strong{ display:block; line-height:1.35; }
    .admin-table-wrap--pengajuan .admin-table th,
    .admin-table-wrap--pengajuan .admin-table td{
        padding:12px 8px;
        vertical-align:middle;
    }
    .admin-table-wrap--pengajuan .admin-table th:first-child,
    .admin-table-wrap--pengajuan .admin-table td:first-child{ padding-left:14px; padding-right:4px; }
    .admin-table-wrap--pengajuan .admin-table th:last-child,
    .admin-table-wrap--pengajuan .admin-table td:last-child{ padding-right:14px; }
    .admin-table-wrap--pengajuan .admin-col-no,
    .admin-table-wrap--pengajuan .admin-cell-no{ width:36px; min-width:0; }
        .admin-table-wrap--pengajuan .admin-kode{ font-size:12px; letter-spacing:0; padding:3px 6px; }
    .admin-table-wrap--pengajuan .admin-pill{ font-size:12px; padding:4px 10px; }
    .admin-table-wrap--pengajuan .admin-priority small{ display:block; line-height:1.35; }
    .admin-table-wrap--pengajuan .col-aksi{ width:1%; white-space:nowrap; }
    .admin-table-wrap--pengajuan .admin-aksi{ gap:6px; flex-wrap:nowrap; }
    .admin-table-wrap--pengajuan .admin-btn-icon{ width:32px; height:32px; }
    .admin-table-wrap--pengajuan .admin-btn-icon svg{ width:16px; height:16px; }

    /* Laptop kecil: rapatkan sedikit lagi */
    @media (max-width:1280px){
        .admin-table-wrap--pengajuan .admin-table th,
        .admin-table-wrap--pengajuan .admin-table td{ padding:11px 6px; font-size:13px; }
        .admin-table-wrap--pengajuan .admin-pill{ padding:4px 8px; font-size:11.5px; }
    }

    /* Layar sempit: tabel tetap, hanya header filter yang disusun ulang */
    @media (max-width:900px){
        /* Jika layout membuat .admin-card-head jadi kolom (flex-direction:column),
           flex-basis berarti TINGGI -> 280px / 100% jadi ruang kosong. Reset ke auto. */
        .admin-card-head--filter > div:first-child{ flex:0 0 auto; width:100%; }
        .admin-card-head--filter .admin-header-actions{ width:100%; flex:0 0 auto; flex-wrap:wrap; justify-content:stretch; }
        .admin-card-head--filter .admin-search{ flex:1 1 100%; max-width:none; }
    }
    @media (max-width:760px){
        /* di sini .admin-header-actions bersusun kolom -> jangan pakai flex-basis */
        .admin-card-head--filter .admin-search{ flex:0 0 auto; width:100%; }
    }

    /* HP: tabel sedikit lebih rapat */
    @media (max-width:640px){
        .admin-table-wrap--pengajuan .admin-table{ min-width:820px; }
        .admin-table-wrap--pengajuan .admin-table th,
        .admin-table-wrap--pengajuan .admin-table td{ padding:10px 8px; font-size:12.5px; }
        .admin-table-wrap--pengajuan .admin-table th{ font-size:11px; }
        .admin-table-wrap--pengajuan .admin-table td.admin-cell-name{ min-width:150px; }
        .admin-table-wrap--pengajuan .admin-btn-icon{ width:32px; height:32px; }
    }

    @media (max-width:420px){
        .admin-pagination-left{ flex-direction:column; align-items:flex-start; }
        .admin-pagination-info{ font-size:12.5px; }
        .admin-btn-export{ font-size:12.5px; gap:6px; }
    }
</style>
@endpush

@section('content')

@php
    // Terjemahan nama bulan Indonesia -> Inggris supaya bisa dibaca strtotime().
    $bulanId = [
        'januari'=>'january','jan'=>'jan','februari'=>'february','feb'=>'feb','maret'=>'march','mar'=>'mar',
        'april'=>'april','apr'=>'apr','mei'=>'may','juni'=>'june','jun'=>'jun','juli'=>'july','jul'=>'jul',
        'agustus'=>'august','agu'=>'aug','agt'=>'aug','ags'=>'aug','september'=>'september','sep'=>'sep','sept'=>'sep',
        'oktober'=>'october','okt'=>'oct','november'=>'november','nov'=>'nov','desember'=>'december','des'=>'dec',
    ];
    $keTimestamp = function ($teks) use ($bulanId) {
        $t = strtolower(trim((string) $teks));
        $t = preg_replace('/^(\d{1,2})[\/.](\d{1,2})[\/.](\d{4})/', '$1-$2-$3', $t); // dd/mm/yyyy -> dd-mm-yyyy
        $t = preg_replace_callback('/[a-z]+/', fn ($m) => $bulanId[$m[0]] ?? $m[0], $t);
        $ts = strtotime($t);
        return $ts === false ? PHP_INT_MAX : $ts; // tanggal tak terbaca -> taruh paling bawah
    };

    // Hanya yang menunggu pemeriksaan. Urutan utama berasal dari SAW;
    // pengajuan lama tetap menjadi penentu kedua bila nilainya sama.
    $semuaAntrian = collect($pengajuan)
        ->filter(fn ($r) => ($r['tone'] ?? '') === 'menunggu')
        ->sortBy(fn ($r) => [
            ($r['saw_nilai'] ?? null) === null ? 1 : 0,
            -((float) ($r['saw_nilai'] ?? 0)),
            $keTimestamp($r['created_at'] ?? $r['tanggal']),
        ])
        ->values();

    // Pencarian (?q=) — dijalankan di server supaya mencakup SEMUA data, bukan hanya halaman aktif.
    $kataKunci = trim((string) request('q', ''));
    $antrian = $kataKunci === ''
        ? $semuaAntrian
        : $semuaAntrian->filter(fn ($r) => str_contains(
            mb_strtolower($r['nama'].' '.$r['kode'].' '.$r['kategori'].' '.($r['jenis'] ?? '')),
            mb_strtolower($kataKunci)
          ))->values();

    // Jumlah data per halaman (?per_page=) — hanya 10 / 25 / 50, selain itu jatuh ke 10.
    $opsiPerHalaman = [10, 25, 50];
    $perHalaman = (int) request('per_page', 10);
    if (! in_array($perHalaman, $opsiPerHalaman, true)) {
        $perHalaman = 10;
    }

    // Pagination (?page=) — halaman dijaga agar selalu dalam rentang yang valid.
    $jumlah          = $antrian->count();
    $halamanTerakhir = max(1, (int) ceil($jumlah / $perHalaman));
    $halaman         = min(max(1, (int) request('page', 1)), $halamanTerakhir);
    $tampil          = $antrian->slice(($halaman - 1) * $perHalaman, $perHalaman)->values();
    $dari            = $jumlah === 0 ? 0 : ($halaman - 1) * $perHalaman + 1;
    $sampai          = $jumlah === 0 ? 0 : $dari + $tampil->count() - 1;

    // Nomor halaman yang ditampilkan: pertama, terakhir, dan sekitar halaman aktif ("…" untuk yang dilewati).
    $nomorHalaman = [];
    for ($n = 1; $n <= $halamanTerakhir; $n++) {
        if ($n === 1 || $n === $halamanTerakhir || abs($n - $halaman) <= 1) {
            $nomorHalaman[] = $n;
        } elseif (end($nomorHalaman) !== '…') {
            $nomorHalaman[] = '…';
        }
    }
@endphp

@if(session('sukses'))
    <div class="admin-flash" role="status">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>{{ session('sukses') }}</span>
    </div>
@endif
@if(session('galat'))
    <div class="admin-flash is-error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>
        <span>{{ session('galat') }}</span>
    </div>
@endif

<div class="admin-card">

    <div class="admin-card-head admin-card-head--filter">
        <div>
            <h2>Pendaftaran Masuk</h2>
            <p><span id="jumlahDitemukan">{{ $jumlah }}</span> pengajuan menunggu pemeriksaan, diurutkan berdasarkan rekomendasi WHO-5 + SAW</p>
        </div>

        <div class="admin-header-actions">
            {{-- Form GET: q + per_page (select di bar bawah ikut lewat atribut form="filterForm"). Ganti pencarian = kembali ke halaman 1. --}}
            <form method="GET" action="{{ url()->current() }}" id="filterForm" class="admin-search" role="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" name="q" id="pengajuanSearch" value="{{ $kataKunci }}" placeholder="Cari nama, kode, jenis, atau kategori..." autocomplete="off">
            </form>

            <div class="admin-export-group">
                <a href="{{ route('admin.pendaftaran.ekspor', array_filter(['jenis'=>'excel','status'=>$filterAktif !== 'semua' ? $filterAktif : null])) }}" class="admin-btn-export">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('admin.pendaftaran.ekspor', array_filter(['jenis'=>'pdf','status'=>$filterAktif !== 'semua' ? $filterAktif : null])) }}" class="admin-btn-export">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ============ TABEL PENGAJUAN ============ --}}
    <div class="admin-table-wrap admin-table-wrap--pengajuan">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="admin-col-no">No</th>
                    <th>Kode</th>
                    <th>Konseli</th>
                    <th>Jenis Konseli</th>
                    <th>Prioritas SAW</th>
                    <th>WHO-5 &amp; Nilai SAW</th>
                    <th>Tanggal</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="pengajuanBody">
                @foreach($tampil as $row)
                    <tr>
                        <td class="admin-cell-no" data-label="No">{{ $dari + $loop->index }}</td>
                        <td data-label="Kode"><span class="admin-kode">{{ $row['kode'] }}</span></td>
                        <td class="admin-cell-name" data-label="Konseli">
                            <strong>{{ $row['nama'] }}</strong>
                            <span>{{ $row['no'] }}</span>
                        </td>
                        <td class="admin-cell-type" data-label="Jenis Konseli"><span class="admin-pill jenis-konseli">{{ $row['jenis'] }}</span></td>
                        <td data-label="Prioritas SAW">
                            @php
                                $prioritasClass = strtolower($row['prioritas'] ?? 'belum');
                            @endphp
                            <span class="admin-priority {{ $prioritasClass }}">
                                <strong>{{ $row['prioritas'] ?? 'Belum dihitung' }}</strong>
                            </span>
                        </td>
                        <td class="admin-cell-muted" data-label="WHO-5 & Nilai SAW">
                            <span class="admin-screening-cell">
                                <small>WHO-5: {{ $row['who5_skor'] !== null ? $row['who5_skor'].'/100' : '-' }}</small>
                                <small>SAW: {{ $row['saw_nilai'] !== null ? number_format((float) $row['saw_nilai'], 4) : '-' }}</small>
                            </span>
                        </td>
                        <td class="admin-cell-muted" data-label="Tanggal">{{ $row['tanggal'] }}</td>
                        <td class="col-aksi" data-label="Aksi">
                            <div class="admin-aksi">
                                @if(!empty($row['telepon']))
                                    @php
                                        $waNomor = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $row['telepon']));
                                        $waPesan = 'Halo ' . $row['nama'] . ', kami dari SILAKON. Kami ingin mengonfirmasi pendaftaran konseling Anda dengan kode ' . $row['kode'] . '. Mohon konfirmasi bahwa data pendaftaran ini benar. Terima kasih.';
                                    @endphp
                                    <a href="https://wa.me/{{ $waNomor }}?text={{ rawurlencode($waPesan) }}" target="_blank" rel="noopener" class="admin-btn-icon admin-btn-wa"
                                       title="Konfirmasi pendaftaran melalui WhatsApp" aria-label="Konfirmasi pendaftaran {{ $row['kode'] }} melalui WhatsApp">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.7-5A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8.5 9.2c.4 2.1 2.2 4 4.3 4.4l1.3-1.1c.2-.2.5-.2.8-.1l1.4.6c.4.2.5.6.3 1-.4.8-1.3 1.2-2.2 1-3.8-.8-6.6-3.6-7.4-7.4-.2-.9.2-1.8 1-2.2.4-.2.8-.1 1 .3l.6 1.4c.1.3.1.6-.1.8L8.5 9.2Z"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.pendaftaran.show', $row['kode']) }}" class="admin-btn-icon admin-btn-detail"
                                   title="Lihat detail"
                                   aria-label="Lihat detail pengajuan {{ $row['kode'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.pendaftaran.verifikasi', $row['kode']) }}"
                                      data-konfirmasi="Verifikasi pengajuan {{ $row['kode'] }} atas nama {{ $row['nama'] }}?">
                                    @csrf
                                    <input type="hidden" name="kembali" value="daftar">
                                    <button type="submit" class="admin-btn-icon admin-btn-verify"
                                            title="Verifikasi pengajuan ini"
                                            aria-label="Verifikasi pengajuan {{ $row['kode'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($jumlah === 0)
            <div class="admin-empty-state">
                {{ $kataKunci !== '' ? 'Tidak ada pendaftaran yang cocok dengan pencarian ini.' : 'Belum ada pendaftaran masuk yang menunggu verifikasi.' }}
            </div>
        @endif
    </div>

    {{-- ============ PAGINATION ============ --}}
    <div class="admin-pagination-bar">
        <div class="admin-pagination-left">
            <label class="admin-perpage">
                Tampilkan
                <select name="per_page" form="filterForm" onchange="this.form.submit()" aria-label="Jumlah data per halaman">
                    @foreach($opsiPerHalaman as $opsi)
                        <option value="{{ $opsi }}" @selected($perHalaman === $opsi)>{{ $opsi }}</option>
                    @endforeach
                </select>
                data
            </label>
            <span class="admin-pagination-info">
                Menampilkan {{ $dari }}–{{ $sampai }} dari {{ $jumlah }} data
            </span>
        </div>

        @if($halamanTerakhir > 1)
            <nav class="admin-pagination" aria-label="Navigasi halaman">
                <a href="{{ request()->fullUrlWithQuery(['page' => $halaman - 1]) }}"
                   class="admin-page-btn {{ $halaman <= 1 ? 'is-disabled' : '' }}"
                   aria-label="Halaman sebelumnya" @if($halaman <= 1) aria-disabled="true" tabindex="-1" @endif>&lsaquo;</a>

                @foreach($nomorHalaman as $n)
                    @if($n === '…')
                        <span class="admin-page-gap" aria-hidden="true">…</span>
                    @else
                        <a href="{{ request()->fullUrlWithQuery(['page' => $n]) }}"
                           class="admin-page-btn {{ $halaman === $n ? 'is-active' : '' }}"
                           @if($halaman === $n) aria-current="page" @endif>{{ $n }}</a>
                    @endif
                @endforeach

                <a href="{{ request()->fullUrlWithQuery(['page' => $halaman + 1]) }}"
                   class="admin-page-btn {{ $halaman >= $halamanTerakhir ? 'is-disabled' : '' }}"
                   aria-label="Halaman berikutnya" @if($halaman >= $halamanTerakhir) aria-disabled="true" tabindex="-1" @endif>&rsaquo;</a>
            </nav>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
    (function () {
        var searchBox  = document.getElementById('pengajuanSearch');
        var filterForm = document.getElementById('filterForm');
        var terakhir   = searchBox.value;
        var debounceTimer = null;

        // Setelah halaman dimuat ulang oleh pencarian, kembalikan fokus ke kotak cari (kursor di akhir teks).
        if (searchBox.value !== '') {
            searchBox.focus();
            searchBox.setSelectionRange(searchBox.value.length, searchBox.value.length);
        }

        // Konfirmasi sebelum verifikasi dari tombol centang di tabel.
        document.querySelectorAll('form[data-konfirmasi]').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                if (!window.confirm(f.dataset.konfirmasi)) e.preventDefault();
            });
        });

        // Cari otomatis setelah berhenti mengetik (Enter juga langsung mencari).
        searchBox.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (searchBox.value !== terakhir) filterForm.submit();
            }, 450);
        });
    })();
</script>
@endpush