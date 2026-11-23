{{--
    SILAKON — Konseling Kelompok (Koordinator)
    Simpan sebagai: resources/views/koordinator/konseling-kelompok.blade.php

    Route yang dibutuhkan (lihat KoordinatorController_kelompok.php):
    Route::get('/koordinator/konseling-kelompok',  [KoordinatorController::class, 'kelompok'])
        ->name('koordinator.kelompok');
    Route::post('/koordinator/konseling-kelompok', [KoordinatorController::class, 'simpanKelompok'])
        ->name('koordinator.kelompok.store');
    Route::get('/koordinator/konseling-kelompok/{id}/peserta', [KoordinatorController::class, 'pesertaKelompok'])
        ->name('koordinator.kelompok.peserta');

    Program tersimpan di database dan dapat diedit, dihapus, atau diubah statusnya.
    "Lihat Peserta" -> koordinator.kelompok.peserta (lihat konseling-kelompok-peserta.blade.php).
    Edit / Hapus menggunakan route CRUD database.
    Subjudul tidak di-override, jadi otomatis memakai tanggal hari ini dari navbar.blade.php.
--}}
@extends('layouts.koordinator')

@section('judul', 'Konseling Kelompok')

@section('content')

<style>
    .kkc {
        --kkc-primary: #7a9c7c;
        --kkc-primary-dark: #4d7a55;
        --kkc-ink: #1a2321;
        --kkc-muted: #7d8582;
        --kkc-line: #e3e7e1;
    }

    /* Notifikasi sukses */
    .kkc-alert {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 22px; padding: 14px 18px;
        background: #e3f3e8; border: 1px solid #cdeadb; border-radius: 12px;
        font-size: 14px; font-weight: 500; color: #2e7d4f;
    }
    .kkc-alert svg { width: 18px; height: 18px; flex: none; }

    /* Header */
    .kkc-head {
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-wrap: wrap; gap: 14px; margin-bottom: 24px;
    }
    .kkc-head h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--kkc-ink); }
    .kkc-head p { margin: 6px 0 0; font-size: 14px; color: var(--kkc-muted); }

    /* Tombol */
    .kkc-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 16px; border-radius: 10px; border: 1px solid transparent;
        font: inherit; font-size: 14px; font-weight: 600; text-decoration: none;
        white-space: nowrap; cursor: pointer; transition: background .15s, border-color .15s;
    }
    .kkc-btn svg { width: 16px; height: 16px; }
    .kkc-btn:focus-visible { outline: 2px solid var(--kkc-primary-dark); outline-offset: 2px; }
    .kkc-btn-solid { background: var(--kkc-primary); color: #fff; }
    .kkc-btn-solid:hover { background: #6b8d6e; color: #fff; }
    .kkc-btn-outline { background: #fff; border-color: #d8dfda; color: #3a463f; }
    .kkc-btn-outline:hover { background: #f5f7f5; }
    .kkc-btn-danger { background: #fceceb; color: #c0392b; }
    .kkc-btn-danger:hover { background: #f9dedb; }

    /* Grid kartu program */
    .kkc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .kkc-card {
        padding: 24px; background: #fff;
        border: 1px solid var(--kkc-line); border-radius: 16px;
    }

    .kkc-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 16px; }
    .kkc-card-head h2 { margin: 0; font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--kkc-ink); }

    .kkc-badge {
        flex: none; margin-top: 2px; padding: 4px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600; white-space: nowrap;
    }
    .kkc-badge.tone-hijau  { background: #d9f5e3; color: #1f6b3f; }
    .kkc-badge.tone-kuning { background: #fdefb9; color: #8a3f0b; }
    .kkc-badge.tone-abu    { background: #eef0ee; color: #667066; }

    .kkc-meta { display: flex; flex-direction: column; gap: 10px; margin: 0 0 20px; padding: 0; list-style: none; }
    .kkc-meta li { display: flex; align-items: center; gap: 9px; font-size: 14px; color: #4b5a51; }
    .kkc-meta svg { width: 15px; height: 15px; flex: none; color: #8a978f; }

    .kkc-progress-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #5c6a62; }
    .kkc-progress-head b { color: var(--kkc-ink); }
    .kkc-track { height: 8px; margin-bottom: 20px; border-radius: 999px; background: #e9ede9; overflow: hidden; }
    .kkc-fill { height: 100%; border-radius: 999px; }
    .kkc-fill.tone-hijau { background: var(--kkc-primary); }
    .kkc-fill.tone-merah { background: #ef4444; }

    .kkc-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
    .kkc-stat { padding: 14px 8px; border-radius: 10px; text-align: center; }
    .kkc-stat b { display: block; font-size: 19px; font-weight: 700; line-height: 1.3; color: var(--kkc-ink); }
    .kkc-stat span { font-size: 12px; color: #5c6a62; }
    .kkc-stat.tone-biru   { background: #e2ecfb; }
    .kkc-stat.tone-hijau  { background: #e3f3e8; }
    .kkc-stat.tone-kuning { background: #fdf3cf; }

    .kkc-actions { display: flex; align-items: center; gap: 10px; }
    .kkc-actions .kkc-btn { flex: 1; padding: 10px; font-size: 13.5px; }
    .kkc-actions .kkc-btn-danger { flex: .7; }
    .kkc-actions { flex-wrap: wrap; }

    /* Tombol Bagikan (dropdown) */
    .kkc-share { position: relative; flex: 1; display: flex; }
    .kkc-share > .kkc-btn { flex: 1; }
    .kkc-share.is-open > .kkc-btn { background: #f5f7f5; }
    .kkc-share-menu {
        display: none; position: absolute; left: 50%; bottom: calc(100% + 8px);
        transform: translateX(-50%); min-width: 190px; padding: 6px; z-index: 50;
        background: #fff; border: 1px solid var(--kkc-line); border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0,0,0,.14);
    }
    .kkc-share.is-open .kkc-share-menu { display: block; }
    .kkc-share-menu a, .kkc-share-menu button {
        display: flex; align-items: center; gap: 10px; width: 100%;
        padding: 10px 12px; border: none; border-radius: 8px; background: transparent;
        font: inherit; font-size: 13.5px; font-weight: 600; color: #2f3b34;
        text-decoration: none; text-align: left; cursor: pointer;
    }
    .kkc-share-menu a:hover, .kkc-share-menu button:hover { background: #f1f5f2; }
    .kkc-share-menu svg { width: 17px; height: 17px; flex: none; }
    .kkc-share-menu .ic-wa { color: #25a244; }
    .kkc-share-menu .ic-copy { color: var(--kkc-primary-dark); }

    .kkc-empty {
        padding: 40px 20px; text-align: center; font-size: 14px; color: var(--kkc-muted);
        background: #fff; border: 1px dashed #d8ddd6; border-radius: 16px;
    }

    /* Modal Buat Program */
    .kkc-modal {
        width: min(480px, calc(100vw - 32px)); max-height: calc(100vh - 32px);
        padding: 0; border: 0; border-radius: 20px; background: #fff; color: var(--kkc-ink);
        box-shadow: 0 24px 64px rgba(0, 0, 0, .28); overflow: auto;
    }
    .kkc-modal::backdrop { background: rgba(38, 42, 40, .5); }
    .kkc-modal form { padding: 28px; }
    .kkc-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 22px; }
    .kkc-modal-head h2 { margin: 0; font-size: 18px; font-weight: 700; }
    .kkc-modal-close {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 32px; height: 32px; padding: 0; border: 0; border-radius: 8px;
        background: transparent; color: #6b7370; cursor: pointer;
    }
    .kkc-modal-close:hover { background: #f0f3f0; }
    .kkc-modal-close:focus-visible { outline: 2px solid var(--kkc-primary-dark); outline-offset: 1px; }
    .kkc-modal-close svg { width: 18px; height: 18px; }

    .kkc-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .kkc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .kkc-field label { font-size: 14px; font-weight: 600; }
    .kkc-field input, .kkc-field select {
        width: 100%; padding: 11px 14px; font: inherit; font-size: 14px; color: var(--kkc-ink);
        background: #fff; border: 1px solid #dfe3de; border-radius: 10px;
    }
    .kkc-field input::placeholder { color: #a3a9a6; }
    .kkc-field select { min-height: 44px; appearance: auto; cursor: pointer; }
    .kkc-field input:focus-visible, .kkc-field select:focus-visible { outline: 2px solid var(--kkc-primary); outline-offset: 1px; }
    .kkc-field-help { margin-top: -1px; font-size: 12px; line-height: 1.45; color: var(--kkc-muted); }
    .kkc-error { margin: 0; font-size: 13px; color: #dc2626; }

    .kkc-modal-actions { display: flex; gap: 12px; margin-top: 24px; }
    .kkc-modal-actions .kkc-btn { flex: 1; padding: 12px 16px; font-size: 15px; }
    .kkc-btn-ghost { background: #fff; border-color: var(--kkc-primary); color: var(--kkc-primary-dark); }
    .kkc-btn-ghost:hover { background: #eef5ee; }

    @media (max-width: 900px) { .kkc-grid { grid-template-columns: 1fr; } }
    @media (max-width: 520px) { .kkc-row { grid-template-columns: 1fr; } }

    .kkc-ic-m { display: none; }

    /* ===== HP & tablet kecil: kartu program dipadatkan ===== */
    @media (max-width: 640px) {
        .kkc-alert { margin-bottom: 14px; padding: 11px 14px; font-size: 13px; }

        .kkc-head { align-items: center; gap: 10px; margin-bottom: 14px; }
        .kkc-head h1 { font-size: 18px; }
        .kkc-head p { margin-top: 2px; font-size: 13px; }
        .kkc-head .kkc-btn { padding: 9px 13px; font-size: 13px; }

        .kkc-grid { gap: 12px; }
        .kkc-card { padding: 14px; border-radius: 14px; }
        .kkc-card-head { gap: 10px; margin-bottom: 10px; }
        .kkc-card-head h2 { font-size: 15px; line-height: 1.35; }
        .kkc-badge { margin-top: 1px; padding: 3px 10px; font-size: 11px; }

        .kkc-meta { gap: 6px; margin-bottom: 12px; }
        .kkc-meta li { align-items: flex-start; gap: 8px; font-size: 13px; line-height: 1.4; }
        .kkc-meta svg { width: 14px; height: 14px; margin-top: 2px; }

        .kkc-progress-head { margin-bottom: 6px; font-size: 12.5px; }
        .kkc-track { height: 6px; margin-bottom: 12px; }

        /* 3 kotak statistik jadi satu baris tipis: "20 Kuota" */
        .kkc-stats { gap: 8px; margin-bottom: 12px; }
        .kkc-stat { display: flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 4px; border-radius: 8px; }
        .kkc-stat b { display: inline; font-size: 15px; line-height: 1.2; }
        .kkc-stat span { font-size: 11.5px; }

        /* aksi: satu baris, "Lihat Peserta" paling lebar di depan */
        .kkc .kkc-actions { position: relative; flex-wrap: nowrap; gap: 8px; }
        .kkc .kkc-actions form { display: flex; margin: 0; }
        .kkc .kkc-actions .kkc-btn { flex: none; height: 40px; padding: 0 12px; font-size: 13px; }
        .kkc .kkc-actions > .kkc-btn-solid { flex: 1 1 auto; order: -1; }
        .kkc .kkc-ic-m { display: block; width: 16px; height: 16px; }

        /* menu Bagikan: rata kanan baris aksi, tidak keluar layar */
        .kkc .kkc-share { position: static; flex: none; }
        .kkc .kkc-share-menu { left: auto; right: 0; bottom: calc(100% + 8px); transform: none; min-width: min(220px, 100%); }

        /* modal */
        .kkc-modal { width: calc(100vw - 24px); max-height: calc(100vh - 24px); max-height: calc(100dvh - 24px); border-radius: 16px; }
        .kkc-modal form { padding: 18px 16px; }
        .kkc-modal-head { margin-bottom: 14px; }
        .kkc-modal-head h2 { font-size: 16px; }
        .kkc-field { margin-bottom: 12px; }
        .kkc-field input, .kkc-field select { padding: 10px 12px; }
        .kkc-row { gap: 0; }
        .kkc-modal-actions { gap: 10px; margin-top: 16px; }
        .kkc-modal-actions .kkc-btn { padding: 11px 14px; font-size: 14px; }
    }

    /* HP kecil: Edit / Bagikan / Hapus jadi tombol ikon persegi */
    @media (max-width: 480px) {
        .kkc .kkc-actions .kkc-btn:not(.kkc-btn-solid) { width: 40px; padding: 0; }
        .kkc .kkc-btn-label { display: none; }
    }
</style>

<div class="kkc">

    @if(session('sukses'))
        <div class="kkc-alert" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
            </svg>
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ============ JUDUL ============ --}}
    <div class="kkc-head">
        <div>
            <h1>Konseling Kelompok</h1>
            <p>{{ $totalAktif }} program aktif</p>
        </div>
        <button type="button" class="kkc-btn kkc-btn-solid" id="kkc-buka">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Buat Program
        </button>
    </div>

    {{-- ============ DAFTAR PROGRAM ============ --}}
    @if(count($program) > 0)
        <div class="kkc-grid">
            @foreach($program as $p)
                <article class="kkc-card">
                    <div class="kkc-card-head">
                        <h2>{{ $p['judul'] }}</h2>
                        <span class="kkc-badge tone-{{ $p['status_tone'] }}">{{ $p['status'] }}</span>
                    </div>

                    <ul class="kkc-meta">
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            {{ $p['tanggal'] }} &middot; {{ $p['waktu'] }}
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $p['lokasi'] }}
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            {{ $p['fasilitator'] }}
                        </li>
                    </ul>

                    <div class="kkc-progress-head">
                        <span>Pendaftar</span>
                        <b>{{ $p['terdaftar'] }}/{{ $p['kuota'] }}</b>
                    </div>
                    <div class="kkc-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $p['pct'] }}" aria-label="Kuota terisi">
                        <div class="kkc-fill tone-{{ $p['progress_tone'] }}" style="width: {{ $p['pct'] }}%"></div>
                    </div>

                    <div class="kkc-stats">
                        <div class="kkc-stat tone-biru"><b>{{ $p['kuota'] }}</b><span>Kuota</span></div>
                        <div class="kkc-stat tone-hijau"><b>{{ $p['terdaftar'] }}</b><span>Terdaftar</span></div>
                        <div class="kkc-stat tone-kuning"><b>{{ $p['sisa'] }}</b><span>Sisa</span></div>
                    </div>

                    @php
                        $linkDaftar = url('/konseling-kelompok/' . $p['id'] . '/daftar');
                        $teksWa = "Halo! Pendaftaran program Konseling Kelompok SILAKON dibuka:\n\n"
                            . "*" . $p['judul'] . "*\n"
                            . $p['tanggal'] . " · " . $p['waktu'] . "\n"
                            . $p['lokasi'] . "\n\n"
                            . "Daftar di sini: " . $linkDaftar;
                    @endphp

                    <div class="kkc-actions">
                        @if($p['status'] !== 'Selesai')
                        <a href="#" class="kkc-btn kkc-btn-outline" onclick="kkcEditProgram(event,this)" data-id="{{ $p['id'] }}" data-judul="{{ $p['judul'] }}" data-tanggal="{{ $p['tanggal'] }}" data-waktu="{{ $p['waktu'] }}" data-lokasi="{{ $p['lokasi'] }}" data-fasilitator="{{ $p['fasilitator'] }}" data-kuota="{{ $p['kuota'] }}" data-tautan="{{ $p['tautan_grup'] }}" data-status="{{ $p['status'] }}" aria-label="Edit program" title="Edit"><svg class="kkc-ic-m" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg><span class="kkc-btn-label">Edit</span></a>

                        <div class="kkc-share">
                            <button type="button" class="kkc-btn kkc-btn-outline" onclick="kkcToggleShare(this, event)" aria-label="Bagikan program" title="Bagikan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                                <span class="kkc-btn-label">Bagikan</span>
                            </button>
                            <div class="kkc-share-menu">
                                <a href="https://wa.me/?text={{ rawurlencode($teksWa) }}" target="_blank" rel="noopener">
                                    <svg class="ic-wa" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2a9.9 9.9 0 0 0-8.43 15.1L2 22l5.05-1.32A9.9 9.9 0 1 0 12.04 2Zm5.8 14.06c-.25.7-1.45 1.34-2 1.4-.52.06-1.17.09-1.89-.12a17 17 0 0 1-1.71-.63c-3-1.3-4.95-4.33-5.1-4.53-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.6-.37.8-.37h.57c.18 0 .43-.07.67.51.25.6.85 2.07.92 2.22.08.15.13.33.03.52-.1.2-.15.33-.3.5-.15.18-.31.39-.45.52-.15.15-.3.31-.13.6.18.3.78 1.28 1.67 2.07 1.15 1.02 2.12 1.34 2.42 1.49.3.15.48.12.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.22.57.35.08.12.08.72-.17 1.42Z"/></svg>
                                    WhatsApp
                                </a>
                                <button type="button" data-url="{{ $linkDaftar }}" onclick="kkcCopyLink(this)">
                                    <svg class="ic-copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    <span>Salin Tautan</span>
                                </button>
                            </div>
                        </div>
                        @endif

                        <a href="{{ route('kelompok.peserta', $p['id']) }}" class="kkc-btn kkc-btn-solid">Lihat Peserta</a>
                        <form method="POST" action="{{ route('kelompok.destroy', $p['id']) }}" style="display:inline" onsubmit="return confirm('Hapus program ini?')">@csrf @method('DELETE')<button type="submit" class="kkc-btn kkc-btn-danger" aria-label="Hapus program" title="Hapus"><svg class="kkc-ic-m" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg><span class="kkc-btn-label">Hapus</span></button></form>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="kkc-empty">Belum ada program konseling kelompok.</div>
    @endif

    {{-- ============ MODAL BUAT PROGRAM ============ --}}
    <dialog class="kkc-modal" id="kkc-modal" aria-labelledby="kkc-modal-title">
        <form method="POST" action="{{ route('kelompok.store') }}" id="kkcProgramForm">
            @csrf
            <input type="hidden" name="_method" id="kkcProgramMethod" value="POST">

            <div class="kkc-modal-head">
                <h2 id="kkc-modal-title">Buat Program Konseling Kelompok</h2>
                <button type="button" class="kkc-modal-close" id="kkc-tutup-x" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="kkc-field">
                <label for="kkc-judul">Nama Program</label>
                <input type="text" id="kkc-judul" name="judul" value="{{ old('judul') }}" placeholder="mis. Manajemen Waktu Efektif" required>
                @error('judul') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>
            <div class="kkc-field kkc-status-field">
                <label for="kkc-status">Status Program</label>
                <select id="kkc-status" name="status">
                    <option value="Pendaftaran Dibuka">Pendaftaran Dibuka</option>
                    <option value="Draft">Draft</option>
                    <option value="Selesai">Selesai</option>
                </select>
                <small class="kkc-field-help">Pilih Selesai jika kegiatan sudah berakhir. Program selesai hanya dapat dilihat pesertanya atau dihapus.</small>
                @error('status') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>

            <div class="kkc-row">
                <div class="kkc-field">
                    <label for="kkc-tanggal">Tanggal</label>
                    <input type="text" id="kkc-tanggal" name="tanggal" value="{{ old('tanggal') }}" placeholder="mis. 20 Okt 2026" required>
                    @error('tanggal') <p class="kkc-error">{{ $message }}</p> @enderror
                </div>
                <div class="kkc-field">
                    <label for="kkc-waktu">Waktu</label>
                    <input type="text" id="kkc-waktu" name="waktu" value="{{ old('waktu') }}" placeholder="mis. 09.00 – 12.00" required>
                    @error('waktu') <p class="kkc-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="kkc-field">
                <label for="kkc-lokasi">Lokasi</label>
                <input type="text" id="kkc-lokasi" name="lokasi" value="{{ old('lokasi') }}" placeholder="mis. Ruang Seminar LPPP" required>
                @error('lokasi') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>

            <div class="kkc-field">
                <label for="kkc-fasilitator">Fasilitator</label>
                <input type="text" id="kkc-fasilitator" name="fasilitator" value="{{ old('fasilitator') }}" placeholder="mis. Dr. Sari Dewi, M.Psi" required>
                @error('fasilitator') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>

            <div class="kkc-field">
                <label for="kkc-kuota">Kuota Peserta</label>
                <input type="number" id="kkc-kuota" name="kuota" min="1" value="{{ old('kuota') }}" placeholder="mis. 20" required>
                @error('kuota') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>
            <div class="kkc-field">
                <label for="kkc-tautan">Tautan Grup WhatsApp <small>(opsional)</small></label>
                <input type="url" id="kkc-tautan" name="tautan_grup" value="{{ old('tautan_grup') }}" placeholder="https://chat.whatsapp.com/...">
                @error('tautan_grup') <p class="kkc-error">{{ $message }}</p> @enderror
            </div>

            <div class="kkc-modal-actions">
                <button type="button" class="kkc-btn kkc-btn-ghost" id="kkc-batal">Batal</button>
                <button type="submit" class="kkc-btn kkc-btn-solid" id="kkc-submit">Simpan Program</button>
            </div>
        </form>
    </dialog>
</div>

<script>
function kkcToggleShare(btn, e) {
    e.stopPropagation();
    const wrap = btn.closest('.kkc-share');
    const willOpen = !wrap.classList.contains('is-open');
    document.querySelectorAll('.kkc-share.is-open').forEach(el => el.classList.remove('is-open'));
    if (willOpen) wrap.classList.add('is-open');
}
function kkcEditProgram(e,el){e.preventDefault();const f=document.getElementById('kkcProgramForm');f.action='/koordinator/konseling-kelompok/'+el.dataset.id;document.getElementById('kkcProgramMethod').value='PUT';document.getElementById('kkc-modal-title').textContent='Edit Program Konseling Kelompok';document.getElementById('kkc-submit').textContent='Simpan Perubahan';['judul','tanggal','waktu','lokasi','fasilitator','kuota','tautan_grup'].forEach(k=>{const x=f.querySelector('[name="'+k+'"]');if(x)x.value=el.dataset[k]||''});document.getElementById('kkc-status').value=el.dataset.status||'Pendaftaran Dibuka';document.getElementById('kkc-modal').showModal();}
// Tutup dropdown bagikan jika klik di luar
document.addEventListener('click', e => {
    if (!e.target.closest('.kkc-share')) {
        document.querySelectorAll('.kkc-share.is-open').forEach(el => el.classList.remove('is-open'));
    }
});
function kkcCopyLink(btn) {
    const url = btn.getAttribute('data-url');
    const label = btn.querySelector('span');
    function done() {
        label.textContent = 'Tersalin!';
        setTimeout(() => {
            label.textContent = 'Salin Tautan';
            btn.closest('.kkc-share').classList.remove('is-open');
        }, 1400);
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(done);
    } else {
        // Fallback untuk http / browser lama
        const t = document.createElement('textarea');
        t.value = url; t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); done(); } catch (err) {}
        document.body.removeChild(t);
    }
}

(function () {
    const modal = document.getElementById('kkc-modal');
    const buka  = document.getElementById('kkc-buka');
    if (!modal || !buka) return;

    buka.addEventListener('click', () => modal.showModal());
    document.getElementById('kkc-batal').addEventListener('click', () => modal.close());
    document.getElementById('kkc-tutup-x').addEventListener('click', () => modal.close());

    // Klik area gelap di luar kotak form -> tutup
    modal.addEventListener('click', e => { if (e.target === modal) modal.close(); });

    // Kalau validasi server gagal, buka lagi modalnya supaya pesan error terlihat
    @if($errors->any())
        modal.showModal();
    @endif
})();
</script>
@endsection