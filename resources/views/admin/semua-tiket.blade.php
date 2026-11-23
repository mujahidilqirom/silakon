{{--
    SILAKON — Semua Pengajuan (Admin)
    Simpan sebagai: resources/views/admin/semua-tiket.blade.php
    Route contoh:  Route::get('/admin/semua-tiket', [AdminPendaftaranController::class, 'semuaTiket'])->name('admin.semua-tiket');

    Data ($tiket, $statistik, $opsiStatus, $opsiKonselor) dikirim dari database melalui
    AdminPendaftaranController@semuaTiket.

    PENTING: $tiket harus berisi SEMUA data (pakai ->get(), bukan ->paginate()),
    karena pencarian, filter, dan paginasi dijalankan di browser supaya saling bekerja sama.
    Variabel $totalData, $halamanAktif, $totalHalaman tidak dipakai lagi.
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.

    FILTER/SORT TANGGAL: tiap tiket sebaiknya punya key 'tanggal_iso' (format Y-m-d, mis. 2026-11-21)
    dari controller. Jika tidak ada, view mencoba membaca 'tanggal_pengajuan' dengan Carbon::parse().
--}}
@extends('layouts.admin')

@section('judul', 'Semua Pengajuan')

@section('content')

@php
    // Tanggal ISO (Y-m-d) untuk filter & sort tanggal
    $tglIso = function ($t) {
        if (!empty($t['tanggal_iso'])) return $t['tanggal_iso'];
        try { return \Carbon\Carbon::parse($t['tanggal_pengajuan'])->format('Y-m-d'); }
        catch (\Throwable $e) { return ''; }
    };
    // Opsi filter hasil diambil dari data yang ada
    $opsiHasil = collect($tiket)->pluck('hasil')->filter()->unique()->sort()->values();
@endphp

<style>
    .st {
        --st-primary: #7a9c7c;
        --st-primary-dark: #4d7a55;
        --st-ink: #1a2321;
        --st-body: #4f5a56;
        --st-muted: #7d8582;
        --st-line: #e7eae6;
    }

    .st-head { margin-bottom: 24px; }
    .st-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--st-ink); }
    .st-head p  { margin: 6px 0 0; font-size: 14px; color: var(--st-muted); }
    .st-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
    .st-export { display:flex; gap:8px; flex-wrap:wrap; }
    .st-export a { display:inline-flex; align-items:center; gap:7px; height:40px; padding:0 14px; border:1px solid var(--st-line); border-radius:10px; background:#fff; color:var(--st-ink); font-size:13px; font-weight:700; text-decoration:none; }
    .st-export a.primary { background:#5f8967; border-color:#5f8967; color:#fff; }
    .st-export a:hover { filter:brightness(.97); }
    .st-export button { display:inline-flex; align-items:center; gap:7px; height:40px; padding:0 14px; border:1px solid #5f8967; border-radius:10px; background:#5f8967; color:#fff; font:inherit; font-size:13px; font-weight:700; cursor:pointer; }
    .st-export-modal { width:min(520px,calc(100vw - 28px)); border:0; border-radius:18px; padding:0; box-shadow:0 24px 70px rgba(0,0,0,.25); color:var(--st-ink); }
    .st-export-modal::backdrop { background:rgba(18,28,21,.48); }
    .st-export-modal form { padding:24px; }
    .st-export-modal h3 { margin:0 0 5px; font-size:18px; }
    .st-export-modal p { margin:0 0 20px; font-size:13px; color:var(--st-muted); }
    .st-export-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .st-export-grid label { display:flex; flex-direction:column; gap:6px; font-size:12px; font-weight:700; color:var(--st-body); }
    .st-export-grid select { height:42px; border:1px solid var(--st-line); border-radius:9px; padding:0 10px; font:inherit; background:#fff; }
    .st-export-all { display:flex; align-items:center; gap:8px; margin:16px 0; font-size:13px; font-weight:600; color:var(--st-body); }
    .st-export-all input { width:16px; height:16px; accent-color:#5f8967; }
    .st-export-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:22px; }
    .st-export-actions button, .st-export-actions a { height:40px; padding:0 15px; border-radius:9px; border:1px solid var(--st-line); background:#fff; color:var(--st-ink); font:inherit; font-size:13px; font-weight:700; text-decoration:none; cursor:pointer; }
    .st-export-actions a.primary { background:#5f8967; border-color:#5f8967; color:#fff; }
    @media(max-width:560px){.st-export-grid{grid-template-columns:1fr;}}

    /* Kartu statistik */
    .st-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .st-stat {
        padding: 24px 20px 20px; background: #fff;
        border: 1px solid var(--st-line); border-radius: 20px;
    }
    .st-stat-label { display: block; font-size: 13px; color: var(--st-muted); }
    .st-stat-value { display: block; margin-top: 8px; font-size: 30px; font-weight: 700; line-height: 1.2; }
    .st-stat-value.tone-hijau  { color: #7a9c7c; }
    .st-stat-value.tone-kuning { color: #f59e0b; }
    .st-stat-value.tone-terang { color: #22c55e; }
    .st-stat-value.tone-merah  { color: #ef4444; }

    /* Filter */
    .st-filters { display: flex; gap: 10px; flex-wrap: nowrap; align-items: center; margin-bottom: 20px; }
    .st-search { position: relative; flex: 1 1 150px; min-width: 130px; }
    .st-filters .st-select { flex: 1 1 0; min-width: 0; padding: 0 30px 0 12px; font-size: 13px; text-overflow: ellipsis; background-position: right 10px center; }
    .st-search svg {
        position: absolute; left: 16px; top: 50%; width: 16px; height: 16px;
        transform: translateY(-50%); color: #a3a9a6; pointer-events: none;
    }
    .st-input, .st-select {
        height: 46px; font: inherit; font-size: 14px; color: var(--st-ink);
        background: #fff; border: 1px solid var(--st-line); border-radius: 12px;
    }
    .st-input { width: 100%; padding: 0 16px 0 41px; }
    .st-input::placeholder { color: #a3a9a6; }
    .st-select {
        appearance: none; -webkit-appearance: none; padding: 0 40px 0 20px; font-weight: 500; cursor: pointer;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a2321' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
    }
    .st-input:focus-visible, .st-select:focus-visible { outline: 2px solid var(--st-primary); outline-offset: 1px; }

    .st-date-group { display: flex; align-items: center; gap: 6px; flex: 0 1 auto; font-size: 12.5px; font-weight: 600; color: var(--st-muted); }
    .st-date { width: 128px; min-width: 0; height: 46px; padding: 0 8px; font: inherit; font-size: 13px; color: var(--st-ink); background: #fff; border: 1px solid var(--st-line); border-radius: 12px; }
    .st-date:focus-visible, .st-reset:focus-visible { outline: 2px solid var(--st-primary); outline-offset: 1px; }
    .st-reset { flex: none; height: 46px; padding: 0 16px; font: inherit; font-size: 14px; font-weight: 600; color: var(--st-ink); background: #fff; border: 1px solid var(--st-line); border-radius: 12px; cursor: pointer; }
    .st-reset:hover { border-color: var(--st-primary); }

    /* Tabel — muat di lebar kontainer, tanpa scroll horizontal */
    .st-card { background: #fff; border: 1px solid var(--st-line); border-radius: 20px; overflow: hidden; }
    .st-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .st-table th {
        padding: 14px 12px; text-align: left; vertical-align: middle; white-space: nowrap;
        font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        color: #4a524f; background: #f6f7f5;
    }
    .st-table td {
        padding: 14px 12px; border-top: 1px solid #eff1ee; font-size: 13.5px; color: var(--st-body);
        vertical-align: middle; line-height: 1.4;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; /* satu baris, teks panjang dipotong ... */
    }
    /* Konseli, konselor & hasil boleh turun ke baris berikutnya; kolom lain tetap satu baris */
    .st-table td.st-name, .st-table td.st-can-wrap {
        white-space: normal; overflow-wrap: anywhere; text-overflow: clip;
    }
    .st-table tr:first-child td { border-top: 0; }
    .st-table tr[hidden] { display: none; }
    .st-table th:first-child, .st-table td:first-child { padding-left: 18px; }
    .st-table th:last-child,  .st-table td:last-child  { padding-right: 18px; }

    /* Lebar kolom: kolom pendek px tetap, 3 kolom teks (konseli, konselor, hasil) berbagi sisa ruang */
    .st-table .c-no       { width: 52px; }
    .st-table .c-kode     { width: 150px; }
    .st-table .c-tanggal  { width: 100px; }
    .st-table .c-status   { width: 150px; }
    .st-table .c-catatan  { width: 84px; }
    .st-table .c-survei   { width: 84px; }
    .st-table td.st-no, .st-table th.st-no { text-align: center; padding-left: 8px; padding-right: 8px; color: var(--st-muted); font-weight: 600; }

    .st-code  { white-space: nowrap; font-size: 13px; font-weight: 700; letter-spacing: .02em; color: var(--st-primary); }
    .st-name  { font-weight: 700; color: var(--st-ink); }
    .st-dash  { color: #b4bab7; }
    .st-empty { padding: 40px 16px !important; text-align: center; color: var(--st-muted) !important; }

    .st-pill {
        display: inline-block; padding: 4px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 600; line-height: 1.3; white-space: nowrap;
    }
    .st-pill.tone-hijau  { background: #d9f5e3; color: #1f7a4a; }
    .st-pill.tone-kuning { background: #fdf0b8; color: #8a5a00; }
    .st-pill.tone-biru   { background: #dce8fc; color: #1d4ed8; }
    .st-pill.tone-abu    { background: #eceeec; color: #4b5350; }
    .st-result { color: var(--st-body); }

    /* Footer */
    .st-foot { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-top: 24px; }
    .st-foot-left { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .st-count { font-size: 14px; color: var(--st-muted); }
    .st-perpage { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--st-muted); }
    .st-perpage .st-select { height: 36px; padding: 0 32px 0 12px; border-radius: 8px; background-position: right 10px center; }

    .st-pager { display: flex; gap: 8px; flex-wrap: wrap; }
    .st-page {
        min-width: 40px; height: 34px; display: grid; place-items: center; padding: 0 10px;
        background: #fff; border: 1px solid var(--st-line); border-radius: 8px;
        font: inherit; font-size: 14px; font-weight: 600; color: var(--st-ink); cursor: pointer;
    }
    .st-page svg { width: 14px; height: 14px; }
    .st-page:hover:not(:disabled) { border-color: var(--st-primary); }
    .st-page.is-active { background: var(--st-primary); border-color: var(--st-primary); color: #fff; }
    .st-page:disabled { opacity: .45; cursor: default; }
    .st-page.is-gap { border-color: transparent; background: transparent; cursor: default; min-width: 24px; padding: 0; }
    .st-page:focus-visible { outline: 2px solid var(--st-primary-dark); outline-offset: 2px; }

    /* ===== Responsive ===== */
    .st { max-width: 100%; min-width: 0; }

    /* Laptop kecil / tablet: filter boleh turun baris; tabel TETAP tabel, kolom pakai persen, tanpa scroll */
    @media (max-width: 1100px) {
        .st-filters { flex-wrap: wrap; }
        .st-search { flex: 1 1 100%; }
        .st-filters .st-select { flex: 1 1 150px; }
        .st-date-group { flex: 1 1 200px; }
        .st-date-group .st-date { flex: 1; width: auto; }
        .st-reset { flex: 1 1 100px; }
    }
    /* Mode responsif: tabel boleh digeser kanan-kiri, kolom No & Kode dibekukan (sticky) */
    @media (max-width: 1180px) {
        .st-card { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
        .st-table { min-width: 1000px; }
        .st-table th { padding: 12px 10px; font-size: 10.5px; letter-spacing: .04em; }
        .st-table td { padding: 12px 10px; font-size: 13px; white-space: normal; overflow-wrap: anywhere; text-overflow: clip; }
        .st-table .c-no      { width: 44px; }
        .st-table .c-kode    { width: 120px; }
        .st-table .c-konseli { width: 150px; }
        .st-table .c-konselor{ width: 150px; }
        .st-table .c-tanggal { width: 96px; }
        .st-table .c-status  { width: 140px; }
        .st-table .c-catatan { width: 84px; }
        .st-table .c-hasil   { width: 120px; }
        .st-table .c-survei  { width: 84px; }
        .st-code { white-space: normal; overflow-wrap: anywhere; }

        .st-table th.st-no, .st-table td.st-no,
        .st-table th.st-kode, .st-table td.st-kode { position: sticky; z-index: 2; }
        .st-table td.st-no, .st-table td.st-kode { background: #fff; }
        .st-table th.st-no, .st-table th.st-kode { background: #f6f7f5; z-index: 3; }
        .st-table th.st-no, .st-table td.st-no { left: 0; padding-left: 4px; padding-right: 4px; }
        .st-table th.st-kode, .st-table td.st-kode { left: 44px; box-shadow: 1px 0 0 var(--st-line), 6px 0 8px -6px rgba(26,35,33,.18); }
    }

    /* Tablet portrait */
    @media (max-width: 900px) {
        .st-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .st-stat { padding: 20px 18px 16px; }
        .st-stat-value { font-size: 26px; }
        .st-search { flex: 1 1 100%; }
        .st-filters .st-select { flex: 1 1 calc(50% - 6px); min-width: 0; }
        .st-date-group { flex: 1 1 calc(50% - 6px); min-width: 0; }
        .st-date-group .st-date { flex: 1; min-width: 0; }
        .st-reset { flex: 1 1 calc(50% - 6px); min-width: 0; }
        }

    /* HP */
    @media (max-width: 560px) {
        .st-head { margin-bottom: 18px; gap: 12px; }
        .st-head h1 { font-size: 19px; }
        .st-head p { font-size: 13px; }
        .st-export { width: 100%; }
        .st-export button { width: 100%; justify-content: center; height: 42px; }

        .st-stats { gap: 10px; margin-bottom: 18px; }
        .st-stat { padding: 14px 14px 12px; border-radius: 16px; }
        .st-stat-label { font-size: 12px; }
        .st-stat-value { font-size: 22px; margin-top: 4px; }

        .st-filters { gap: 10px; margin-bottom: 16px; }
        /* dua dropdown filter tetap berdampingan, font & tinggi dikecilkan */
        .st-filters .st-select {
            flex: 1 1 calc(50% - 5px); min-width: 0;
            height: 38px; font-size: 12.5px; padding: 0 28px 0 12px;
            background-position: right 10px center; background-size: 14px;
            text-overflow: ellipsis;
        }
        .st-input { height: 40px; font-size: 13px; padding-left: 38px; }
        .st-search svg { left: 13px; width: 15px; height: 15px; }
        .st-card { border-radius: 16px; }

        /* footer: jumlah, per halaman, lalu paginasi — rapi satu kolom */
        .st-foot { flex-direction: column; align-items: stretch; gap: 12px; margin-top: 18px; }
        .st-foot-left { flex-direction: column; align-items: flex-start; gap: 10px; }
        .st-perpage .st-select { flex: none; height: 38px; font-size: 14px; }
        .st-pager { justify-content: center; gap: 6px; }
        .st-page { min-width: 36px; height: 36px; padding: 0 8px; }

        /* modal ekspor */
        .st-export-modal form { padding: 18px; }
        .st-export-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
        .st-export-grid select { font-size: 16px; }
        .st-export-actions { flex-wrap: wrap; }
        .st-export-actions button, .st-export-actions a {
            flex: 1 1 0; height: 44px; display: inline-flex; align-items: center; justify-content: center;
        }
    }

    /* HP kecil (<= 380px, termasuk 375 x 667) */
    @media (max-width: 380px) {
        .st-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .st-stat-value { font-size: 20px; }
        .st-export-grid { grid-template-columns: 1fr; }
        .st-page { min-width: 34px; }
    }

    /* HP: kolom No & Kode dibuat lebih ramping agar area geser lebih luas */
    @media (max-width: 560px) {
        .st-table { min-width: 940px; }
        .st-table .c-no   { width: 38px; }
        .st-table .c-kode { width: 108px; }
        .st-table th.st-kode, .st-table td.st-kode { left: 38px; padding-left: 8px; padding-right: 8px; }
        .st-code { font-size: 12px; }
        .st-date-group { flex: 1 1 calc(50% - 5px); font-size: 12px; }
        .st-date { height: 38px; font-size: 12px; padding: 0 6px; }
        .st-reset { height: 38px; font-size: 13px; padding: 0 12px; }
    }

    /* Layar pendek (landscape HP): dialog ekspor tetap bisa di-scroll */
    @media (max-height: 520px) {
        .st-export-modal { max-height: 94vh; overflow: auto; }
    }
</style>

<div class="st">

    {{-- ============ JUDUL ============ --}}
    <div class="st-head">
        <div><h1>Semua Pengajuan</h1><p>Manajemen pengajuan konseling secara menyeluruh</p></div>
        <div class="st-export">
            <button type="button" id="st-open-export">Ekspor Semua Data</button>
        </div>
    </div>

    {{-- ============ KARTU STATISTIK ============ --}}
    <div class="st-stats">
        @foreach($statistik as $s)
            <div class="st-stat">
                <span class="st-stat-label">{{ $s['label'] }}</span>
                <b class="st-stat-value tone-{{ $s['tone'] }}">{{ $s['nilai'] }}</b>
            </div>
        @endforeach
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="st-filters">
        <div class="st-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" class="st-input" id="st-q" placeholder="Cari kode pengajuan atau nama konseli..." aria-label="Cari pengajuan" autocomplete="off">
        </div>

        <select class="st-select" id="st-status" aria-label="Filter status">
            <option value="">Semua Status</option>
            @foreach($opsiStatus as $o)
                <option value="{{ $o }}">{{ $o }}</option>
            @endforeach
        </select>

        <select class="st-select" id="st-konselor" aria-label="Filter konselor">
            <option value="">Semua Konselor</option>
            @foreach($opsiKonselor as $o)
                <option value="{{ $o }}">{{ $o }}</option>
            @endforeach
            <option value="__none">Belum ditugaskan</option>
        </select>

        <select class="st-select" id="st-hasil" aria-label="Filter hasil">
            <option value="">Semua Hasil</option>
            @foreach($opsiHasil as $o)
                <option value="{{ $o }}">{{ $o }}</option>
            @endforeach
            <option value="__none">Belum ada hasil</option>
        </select>

        <label class="st-date-group">Dari
            <input type="date" class="st-date" id="st-dari" aria-label="Tanggal mulai">
        </label>
        <label class="st-date-group">Sampai
            <input type="date" class="st-date" id="st-sampai" aria-label="Tanggal akhir">
        </label>
        <select class="st-select" id="st-sort" aria-label="Urutkan tanggal">
            <option value="baru" selected>Tanggal terbaru</option>
            <option value="lama">Tanggal terlama</option>
        </select>
        <button type="button" class="st-reset" id="st-reset">Reset</button>
    </div>

    {{-- ============ TABEL TIKET ============ --}}
    <div class="st-card">
        <table class="st-table">
            <colgroup>
                <col class="c-no"><col class="c-kode"><col class="c-konseli"><col class="c-konselor"><col class="c-tanggal">
                <col class="c-status"><col class="c-catatan"><col class="c-hasil"><col class="c-survei">
            </colgroup>
            <thead>
                <tr>
                    <th class="st-no">No</th>
                    <th class="st-kode">Kode</th>
                    <th>Konseli</th>
                    <th>Konselor</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Hasil</th>
                    <th>Survei</th>
                </tr>
            </thead>
            <tbody id="st-body">
                @foreach($tiket as $t)
                    <tr data-cari="{{ strtolower($t['kode'] . ' ' . $t['konseli'] . ' ' . ($t['konselor'] ?? '')) }}"
                        data-status="{{ $t['status'] }}"
                        data-konselor="{{ $t['konselor'] ?? '__none' }}"
                        data-hasil="{{ $t['hasil'] ?: '__none' }}"
                        data-tanggal="{{ $tglIso($t) }}">
                        <td data-label="No" class="st-no"></td>
                        <td data-label="Kode" class="st-kode"><a class="st-code" href="{{ route('admin.pendaftaran.show', $t['kode']) }}" title="Lihat detail pengajuan">{{ $t['kode'] }}</a></td>
                        <td data-label="Konseli" class="st-name">{{ $t['konseli'] }}</td>
                        <td data-label="Konselor" class="st-can-wrap">{!! $t['konselor'] ? e($t['konselor']) : '<span class="st-dash">-</span>' !!}</td>
                        <td data-label="Tanggal">{{ $t['tanggal_pengajuan'] }}</td>
                        <td data-label="Status"><span class="st-pill tone-{{ $t['status_tone'] }}">{{ $t['status'] }}</span></td>
                        <td data-label="Catatan">
                            <span class="st-pill {{ $t['catatan'] === 'sudah' ? 'tone-hijau' : 'tone-kuning' }}">
                                {{ $t['catatan'] === 'sudah' ? 'Sudah' : 'Belum' }}
                            </span>
                        </td>
                        <td data-label="Hasil" class="st-can-wrap">
                            @if($t['hasil'])
                                <span class="st-result">{{ $t['hasil'] }}</span>
                            @else
                                <span class="st-dash">-</span>
                            @endif
                        </td>
                        <td data-label="Survei">
                            @if($t['survei'] === 'sudah')
                                <span class="st-pill tone-hijau">Sudah</span>
                            @elseif($t['survei'] === 'belum')
                                <span class="st-pill tone-abu">Belum</span>
                            @else
                                <span class="st-dash">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr id="st-empty" hidden>
                    <td colspan="9" class="st-empty">Tidak ada pengajuan yang cocok dengan pencarian atau filter.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ============ FOOTER: JUMLAH, PER HALAMAN & PAGINASI ============ --}}
    <div class="st-foot">
        <div class="st-foot-left">
            <span class="st-count" id="st-count" aria-live="polite"></span>

            <label class="st-perpage">
                Tampilkan
                <select class="st-select" id="st-perpage" aria-label="Jumlah data per halaman">
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                per halaman
            </label>
        </div>

        <nav class="st-pager" id="st-pager" aria-label="Halaman"></nav>
    </div>
</div>

<dialog class="st-export-modal" id="st-export-modal">
    <form method="dialog">
        <h3>Ekspor Semua Data Pengajuan</h3>
        <p>File berisi seluruh field yang diisi konseli pada formulir pengajuan konseling.</p>
        <div class="st-export-grid">
            <label>Dari bulan
                <select id="st-export-from-month">@foreach(range(1,12) as $m)<option value="{{ $m }}" @selected($m === now()->month)>{{ now()->setMonth($m)->translatedFormat('F') }}</option>@endforeach</select>
            </label>
            <label>Dari tahun
                <select id="st-export-from-year">@foreach(range(now()->year - 5, now()->year + 1) as $y)<option value="{{ $y }}" @selected($y === now()->year)>{{ $y }}</option>@endforeach</select>
            </label>
            <label>Sampai bulan
                <select id="st-export-to-month">@foreach(range(1,12) as $m)<option value="{{ $m }}" @selected($m === now()->month)>{{ now()->setMonth($m)->translatedFormat('F') }}</option>@endforeach</select>
            </label>
            <label>Sampai tahun
                <select id="st-export-to-year">@foreach(range(now()->year - 5, now()->year + 1) as $y)<option value="{{ $y }}" @selected($y === now()->year)>{{ $y }}</option>@endforeach</select>
            </label>
        </div>
        <label class="st-export-all"><input type="checkbox" id="st-export-all-data"> Semua data pengajuan</label>
        <div class="st-export-actions">
            <button type="button" id="st-close-export">Batal</button>
            <a id="st-export-pdf" href="#">PDF</a>
            <a class="primary" id="st-export-excel" href="#">Excel</a>
        </div>
    </form>
</dialog>

<script>
(function () {
    const modal = document.getElementById('st-export-modal');
    const open = document.getElementById('st-open-export');
    if (!modal || !open) return;
    const all = document.getElementById('st-export-all-data');
    const fields = ['st-export-from-month','st-export-from-year','st-export-to-month','st-export-to-year'].map(id => document.getElementById(id));
    const pdf = document.getElementById('st-export-pdf');
    const excel = document.getElementById('st-export-excel');
    function updateLinks() {
        const params = new URLSearchParams({ jenis:'pdf', semua: all.checked ? '1' : '0', dari_bulan:fields[0].value, dari_tahun:fields[1].value, sampai_bulan:fields[2].value, sampai_tahun:fields[3].value });
        pdf.href = '{{ route('admin.semua-tiket.ekspor') }}?' + params.toString();
        params.set('jenis','excel'); excel.href = '{{ route('admin.semua-tiket.ekspor') }}?' + params.toString();
        fields.forEach(field => field.disabled = all.checked);
    }
    open.addEventListener('click', () => { updateLinks(); modal.showModal(); });
    document.getElementById('st-close-export').addEventListener('click', () => modal.close());
    all.addEventListener('change', updateLinks); fields.forEach(field => field.addEventListener('change', updateLinks));
})();
(function () {
    const rows    = Array.from(document.querySelectorAll('#st-body tr[data-cari]'));
    const empty   = document.getElementById('st-empty');
    const count   = document.getElementById('st-count');
    const pager   = document.getElementById('st-pager');
    const q       = document.getElementById('st-q');
    const status  = document.getElementById('st-status');
    const kons    = document.getElementById('st-konselor');
    const hslSel  = document.getElementById('st-hasil');
    const dari    = document.getElementById('st-dari');
    const sampai  = document.getElementById('st-sampai');
    const sortSel = document.getElementById('st-sort');
    const body    = document.getElementById('st-body');
    const perPage = document.getElementById('st-perpage');

    const ICON_PREV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
    const ICON_NEXT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';

    let hasil = rows.slice();   // baris yang lolos pencarian/filter
    let halaman = 1;

    function filterBaris() {
        const kata = q.value.trim().toLowerCase();
        hasil = rows.filter(function (r) {
            return (!kata || r.dataset.cari.includes(kata)) &&
                   (!status.value || r.dataset.status === status.value) &&
                   (!kons.value || r.dataset.konselor === kons.value) &&
                   (!hslSel.value || r.dataset.hasil === hslSel.value) &&
                   (!dari.value || (r.dataset.tanggal && r.dataset.tanggal >= dari.value)) &&
                   (!sampai.value || (r.dataset.tanggal && r.dataset.tanggal <= sampai.value));
        });

        // Urutkan berdasarkan tanggal (baris tanpa tanggal selalu di akhir)
        const lama = sortSel.value === 'lama';
        hasil.sort(function (a, b) {
            const x = a.dataset.tanggal, y = b.dataset.tanggal;
            if (!x && !y) return 0;
            if (!x) return 1;
            if (!y) return -1;
            return lama ? x.localeCompare(y) : y.localeCompare(x);
        });

        // Batasi rentang tanggal agar "Dari" tidak melebihi "Sampai"
        sampai.min = dari.value || '';
        dari.max = sampai.value || '';
        halaman = 1;
        tampilkan();
    }

    function tombol(isi, opsi) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'st-page' + (opsi.aktif ? ' is-active' : '');
        b.innerHTML = isi;
        if (opsi.label) b.setAttribute('aria-label', opsi.label);
        if (opsi.aktif) b.setAttribute('aria-current', 'page');
        if (opsi.disabled) b.disabled = true;
        if (opsi.ke) b.addEventListener('click', function () { halaman = opsi.ke; tampilkan(); });
        return b;
    }

    // Daftar nomor halaman dengan "..." bila terlalu banyak
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

        const awal  = (halaman - 1) * per;
        const akhir = Math.min(awal + per, total);
        const potong = hasil.slice(awal, akhir);
        const tampil = new Set(potong);

        rows.forEach(function (r) { r.hidden = !tampil.has(r); });
        // Susun ulang baris sesuai urutan & beri nomor lanjut antar halaman
        potong.forEach(function (r, i) {
            r.querySelector('.st-no').textContent = awal + i + 1;
            body.insertBefore(r, empty);
        });
        empty.hidden = total > 0;
        count.textContent = total ? `${awal + 1}-${akhir} dari ${total} pengajuan` : '0 pengajuan';

        // Render paginasi
        pager.innerHTML = '';
        pager.hidden = totalHalaman <= 1;
        if (totalHalaman <= 1) return;

        pager.appendChild(tombol(ICON_PREV, { label: 'Halaman sebelumnya', disabled: halaman <= 1, ke: halaman - 1 }));
        daftarHalaman(totalHalaman, halaman).forEach(function (n) {
            if (n === '...') {
                const g = document.createElement('span');
                g.className = 'st-page is-gap';
                g.textContent = '…';
                pager.appendChild(g);
            } else {
                pager.appendChild(tombol(String(n), { aktif: n === halaman, ke: n, label: 'Halaman ' + n }));
            }
        });
        pager.appendChild(tombol(ICON_NEXT, { label: 'Halaman berikutnya', disabled: halaman >= totalHalaman, ke: halaman + 1 }));
    }

    q.addEventListener('input', filterBaris);
    [status, kons, hslSel, dari, sampai, sortSel].forEach(el => el.addEventListener('change', filterBaris));
    document.getElementById('st-reset').addEventListener('click', function () {
        q.value = ''; status.value = ''; kons.value = ''; hslSel.value = '';
        dari.value = ''; sampai.value = ''; sortSel.value = 'baru';
        filterBaris();
    });
    perPage.addEventListener('change', function () { halaman = 1; tampilkan(); });

    filterBaris();
})();
</script>
@endsection