{{--
    SILAKON — Daftar Peserta Konseling Kelompok (Koordinator)
    Simpan sebagai: resources/views/koordinator/konseling-kelompok-peserta.blade.php

    Route yang dibutuhkan:
    Route::get('/koordinator/konseling-kelompok/{id}/peserta', [KoordinatorController::class, 'pesertaKelompok'])
        ->name('koordinator.kelompok.peserta');

    Data dummy dari controller (pesertaKelompok). Pencarian nama / NIM / kode
    berjalan di browser. Tombol "Detail" masih href="#", tinggal disambungkan.
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.koordinator')

@section('judul', 'Daftar Peserta')

@section('content')

<style>
    .kkp {
        --kkp-primary: #7a9c7c;
        --kkp-primary-dark: #4d7a55;
        --kkp-ink: #1a2321;
        --kkp-muted: #7d8582;
        --kkp-line: #e3e7e1;
    }

    /* Breadcrumb */
    .kkp-crumb { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; font-size: 14px; color: var(--kkp-muted); }
    .kkp-crumb a { display: inline-flex; align-items: center; color: var(--kkp-muted); text-decoration: none; }
    .kkp-crumb a:hover { color: var(--kkp-primary-dark); }
    .kkp-crumb svg { width: 14px; height: 14px; flex: none; }
    .kkp-sep { color: #a7b1ab; }
    .kkp-now { font-weight: 500; color: var(--kkp-ink); }

    /* Header */
    .kkp-head { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    .kkp-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 14px; color: var(--kkp-primary-dark); text-decoration: none; }
    .kkp-back:hover { text-decoration: underline; }
    .kkp-back svg { width: 14px; height: 14px; }
    .kkp-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--kkp-ink); }
    .kkp-head p { margin: 6px 0 0; font-size: 14px; color: var(--kkp-muted); }

    .kkp-stats { display: flex; gap: 12px; }
    .kkp-stat { min-width: 72px; padding: 12px 16px; border-radius: 12px; text-align: center; }
    .kkp-stat b { display: block; font-size: 20px; font-weight: 700; line-height: 1.3; color: var(--kkp-ink); }
    .kkp-stat span { font-size: 12px; white-space: nowrap; color: #5c6a62; }
    .kkp-stat.tone-biru   { background: #e2ecfb; }
    .kkp-stat.tone-hijau  { background: #e3f3e8; }
    .kkp-stat.tone-kuning { background: #fdf3cf; }

    /* Pencarian */
    .kkp-search { position: relative; width: min(400px, 100%); margin-bottom: 20px; }
    .kkp-search svg {
        position: absolute; left: 16px; top: 50%; width: 16px; height: 16px;
        transform: translateY(-50%); color: #a3a9a6; pointer-events: none;
    }
    .kkp-search input {
        display: block; width: 100%; height: 46px; padding: 0 16px 0 42px;
        font: inherit; font-size: 14px; color: var(--kkp-ink);
        background: #fff; border: 1px solid #dfe3de; border-radius: 12px;
    }
    .kkp-search input::placeholder { color: #a3a9a6; }
    .kkp-search input:focus-visible { outline: 2px solid var(--kkp-primary); outline-offset: 1px; }

    /* Tabel */
    .kkp-card { background: #fff; border: 1px solid var(--kkp-line); border-radius: 16px; overflow-x: auto; }
    .kkp-table { width: 100%; min-width: 980px; border-collapse: collapse; }
    .kkp-table th {
        padding: 14px 12px; text-align: left; white-space: nowrap;
        background: #f6f8f5; font-size: 12px; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase; color: #6b7370;
    }
    .kkp-table td {
        padding: 16px 12px; border-top: 1px solid #eceeea;
        font-size: 13px; color: #4a524f; vertical-align: middle;
    }
    .kkp-table th:first-child, .kkp-table td:first-child { padding-left: 16px; }
    .kkp-table th:last-child,  .kkp-table td:last-child  { padding-right: 16px; }
    .kkp-table tbody tr:hover td { background: #fafbf9; }
    .kkp-kode { font-weight: 700; color: var(--kkp-primary-dark); white-space: nowrap; }
    .kkp-nama { font-size: 14px; font-weight: 700; color: var(--kkp-ink); white-space: nowrap; }
    .kkp-nowrap { white-space: nowrap; }

    .kkp-badge { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .kkp-badge.tone-hijau  { background: #d9f5e3; color: #1f6b3f; }
    .kkp-badge.tone-biru   { background: #dfeafc; color: #2456a8; }
    .kkp-badge.tone-merah  { background: #fde0e0; color: #a72b2b; }
    .kkp-badge.tone-kuning { background: #fdefb9; color: #8a3f0b; }

    .kkp-btn {
        display: inline-flex; align-items: center; padding: 7px 14px; border-radius: 8px;
        background: #eef5ee; border: 1px solid #b8ceb9; color: var(--kkp-primary-dark);
        font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap;
        transition: background .15s, border-color .15s;
    }
    .kkp-btn:hover { background: #e2eee3; border-color: var(--kkp-primary); }
    .kkp-btn:focus-visible { outline: 2px solid var(--kkp-primary-dark); outline-offset: 2px; }

    .kkp-table td.kkp-empty { padding: 36px 12px; text-align: center; color: var(--kkp-muted); }

    @media (max-width: 720px) {
        .kkp-stats { width: 100%; }
        .kkp-stat { flex: 1; min-width: 0; }
    }
</style>

<div class="kkp">

    {{-- ============ BREADCRUMB ============ --}}
    <nav class="kkp-crumb" aria-label="Breadcrumb">
        <a href="{{ Route::has('koordinator.dashboard') ? route('koordinator.dashboard') : url('/koordinator') }}" aria-label="Beranda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </a>
        <span class="kkp-sep" aria-hidden="true">&rsaquo;</span>
        <a href="{{ route('kelompok') }}">Konseling Kelompok</a>
        <span class="kkp-sep" aria-hidden="true">&rsaquo;</span>
        <span class="kkp-now" aria-current="page">Daftar Peserta</span>
    </nav>

    {{-- ============ JUDUL + RINGKASAN KUOTA ============ --}}
    <div class="kkp-head">
        <div>
            <a href="{{ route('kelompok') }}" class="kkp-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
            <h1>Daftar Peserta</h1>
            <p>{{ $program['judul'] }} &middot; {{ $program['tanggal'] }}</p>
        </div>

        <div class="kkp-stats">
            <div class="kkp-stat tone-biru"><b>{{ $kuota }}</b><span>Kuota</span></div>
            <div class="kkp-stat tone-hijau"><b>{{ $terdaftar }}</b><span>Terdaftar</span></div>
            <div class="kkp-stat tone-kuning"><b>{{ $sisa }}</b><span>Sisa Kuota</span></div>
        </div>
    </div>

    {{-- ============ PENCARIAN ============ --}}
    <div class="kkp-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="kkp-cari" placeholder="Cari nama atau NIM..." aria-label="Cari peserta" autocomplete="off">
    </div>

    {{-- ============ TABEL PESERTA ============ --}}
    <div class="kkp-card">
        <table class="kkp-table" id="kkp-tabel">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Nama Lengkap</th>
                    <th scope="col">NIM</th>
                    <th scope="col">Fakultas</th>
                    <th scope="col">Prodi</th>
                    <th scope="col">Smt</th>
                    <th scope="col">Tgl Daftar</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peserta as $i => $p)
                    <tr data-cari="{{ mb_strtolower($p['nama'] . ' ' . $p['nim']) }}">
                        <td>{{ $i + 1 }}</td>
                        <td class="kkp-nama">{{ $p['nama'] }}</td>
                        <td class="kkp-nowrap">{{ $p['nim'] }}</td>
                        <td>{{ $p['fakultas'] }}</td>
                        <td>{{ $p['prodi'] }}</td>
                        <td>{{ $p['semester'] }}</td>
                        <td class="kkp-nowrap">{{ $p['tgl_daftar'] }}</td>
                        <td><span class="kkp-badge tone-{{ $p['status_tone'] }}">{{ $p['status'] }}</span></td>
                        <td class="kkp-actions">
                            <a href="mailto:{{ $p['email'] ?? '' }}" class="kkp-btn">Email</a>
                            @php($wa = preg_replace('/\D+/', '', (string) ($p['wa'] ?? '')))
                            @if($wa)
                                @if(str_starts_with($wa, '0')) @php($wa = '62'.substr($wa, 1)) @endif
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="kkp-btn">WhatsApp</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="kkp-empty">Belum ada peserta yang mendaftar di program ini.</td>
                    </tr>
                @endforelse

                @if($peserta->isNotEmpty())
                    <tr id="kkp-kosong" hidden>
                        <td colspan="9" class="kkp-empty">Tidak ada peserta yang cocok dengan pencarian.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const input  = document.getElementById('kkp-cari');
    const kosong = document.getElementById('kkp-kosong');
    const rows   = document.querySelectorAll('#kkp-tabel tbody tr[data-cari]');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = input.value.trim().toLowerCase();
        let tampil = 0;

        rows.forEach(function (row) {
            const cocok = row.dataset.cari.includes(q);
            row.hidden = !cocok;
            if (cocok) tampil++;
        });

        if (kosong) kosong.hidden = tampil > 0;
    });
})();
</script>
@endsection
