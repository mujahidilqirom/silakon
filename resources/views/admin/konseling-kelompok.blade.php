{{--
    SILAKON — Konseling Kelompok (Admin)
    Simpan sebagai: resources/views/admin/konseling-kelompok.blade.php

    Route yang dibutuhkan (lihat AdminPendaftaranController):
    Route::get('/admin/konseling-kelompok',  [AdminPendaftaranController::class, 'konselingKelompok'])
        ->name('admin.konseling-kelompok');
    Route::post('/admin/konseling-kelompok', [AdminPendaftaranController::class, 'simpanKonselingKelompok'])
        ->name('admin.konseling-kelompok.store');
    Route::get('/admin/konseling-kelompok/{id}/peserta', [AdminPendaftaranController::class, 'pesertaKonselingKelompok'])
        ->name('admin.konseling-kelompok.peserta');
    (+ route update / destroy untuk edit & hapus)

    Program tersimpan di database dan dapat diedit, dihapus, atau diubah statusnya.
    "Lihat Peserta" -> admin.konseling-kelompok.peserta (lihat konseling-kelompok-peserta.blade.php).

    PENTING: $program harus berisi SEMUA program (->get(), bukan ->paginate()), karena pencarian,
    filter status, dan paginasi berjalan di browser supaya bisa menangani banyak kegiatan.
--}}
@extends('layouts.admin')

@section('judul', 'Konseling Kelompok')

@section('content')

<style>
    /* ===== Halaman Konseling Kelompok ===== */
    .kk-page{ padding-bottom:8px; }

    .kk-alert{
        display:flex;align-items:center;gap:10px;
        background:#e3f3e8;color:#2e7d4f;border:1px solid #cdeadb;
        padding:14px 18px;border-radius:12px;font-size:14px;font-weight:500;
        margin-bottom:22px;
    }

    .kk-head{
        display:flex;align-items:flex-start;justify-content:space-between;
        margin-bottom:24px;flex-wrap:wrap;gap:14px;
    }
    .kk-head h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .kk-head p{margin:5px 0 0;color:#6b7a72;font-size:14px;}

    /* ===== Tombol (dasar seragam: tinggi 40px) ===== */
    .kk-btn{
        display:inline-flex;align-items:center;justify-content:center;gap:7px;
        height:40px;padding:0 16px;border-radius:10px;border:1px solid transparent;
        font:inherit;font-size:13.5px;font-weight:600;line-height:1;
        text-decoration:none;white-space:nowrap;cursor:pointer;
        transition:background .15s ease,border-color .15s ease,color .15s ease;
    }
    .kk-btn svg{width:16px;height:16px;flex-shrink:0;}
    .kk-btn:focus-visible{outline:2px solid #4d7c5f;outline-offset:2px;}
    .kk-btn-fill{background:#4d7c5f;border-color:#4d7c5f;color:#fff;}
    .kk-btn-fill:hover{background:#3f6a4f;border-color:#3f6a4f;color:#fff;}
    .kk-btn-outline{background:#fff;border-color:#d8dfda;color:#3a463f;}
    .kk-btn-outline:hover{background:#f5f7f5;border-color:#c5cfc8;}
    .kk-btn-danger{background:#fff;border-color:#f1cfcb;color:#c0392b;}
    .kk-btn-danger:hover{background:#fceceb;border-color:#e9b4ae;}
    .kk-btn-lg{height:44px;padding:0 20px;font-size:14px;}

    /* ===== Toolbar: cari + filter status ===== */
    .kk-toolbar{display:flex;flex-direction:column;gap:14px;margin-bottom:24px;scroll-margin-top:96px;}
    .kk-search{position:relative;max-width:420px;width:100%;}
    .kk-search svg{
        position:absolute;left:15px;top:50%;width:16px;height:16px;transform:translateY(-50%);
        color:#a3a9a6;pointer-events:none;
    }
    .kk-search input{
        width:100%;height:44px;padding:0 16px 0 40px;font:inherit;font-size:14px;color:#1f2a24;
        background:#fff;border:1px solid #d8dfda;border-radius:12px;
    }
    .kk-search input::placeholder{color:#a3a9a6;}
    .kk-search input:focus-visible{outline:2px solid #4d7c5f;outline-offset:1px;}

    .kk-tabs{display:flex;flex-wrap:wrap;gap:8px;}
    .kk-tab{
        display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 16px;
        background:#eceeec;border:1px solid transparent;border-radius:999px;
        font:inherit;font-size:13.5px;font-weight:600;color:#4f5a56;cursor:pointer;white-space:nowrap;
    }
    .kk-tab:hover{background:#e3e6e3;}
    .kk-tab.is-active{background:#fff;border-color:#d8dfda;color:#1f2a24;box-shadow:0 1px 2px rgba(20,30,25,.06);}
    .kk-tab:focus-visible{outline:2px solid #4d7c5f;outline-offset:2px;}
    .kk-tab em{
        font-style:normal;font-size:12px;font-weight:700;min-width:22px;height:20px;padding:0 6px;
        display:inline-grid;place-items:center;border-radius:999px;background:rgba(0,0,0,.07);color:#4f5a56;
    }
    .kk-tab.is-active em{background:#e3f3e8;color:#2e7d4f;}

    /* ===== Grid kartu ===== */
    .kk-grid{
        display:grid;grid-template-columns:repeat(2,minmax(0,1fr));
        gap:24px;margin-bottom:8px;
    }
    .kk-grid > .admin-card{
        padding:24px;display:flex;flex-direction:column;min-width:0;
    }
    .kk-grid > .admin-card[hidden]{display:none;}

    .kk-card-head{
        display:flex;align-items:flex-start;justify-content:space-between;gap:12px;
        margin-bottom:16px;
    }
    .kk-card-head h3{
        margin:0;font-size:16px;font-weight:700;color:#1f2a24;line-height:1.4;
        min-width:0;overflow-wrap:anywhere;
    }

    .kk-badge{
        flex-shrink:0;white-space:nowrap;font-size:12px;font-weight:600;
        padding:5px 12px;border-radius:999px;margin-top:1px;
    }
    .kk-badge.tone-hijau{background:#e3f3e8;color:#2e7d4f;}
    .kk-badge.tone-kuning{background:#fef3c7;color:#a8710a;}
    .kk-badge.tone-abu{background:#eef0ee;color:#667066;}

    .kk-meta{display:flex;flex-direction:column;gap:10px;margin-bottom:20px;}
    .kk-meta span{
        display:flex;align-items:flex-start;gap:9px;
        font-size:13.5px;color:#4b5a51;line-height:1.45;overflow-wrap:anywhere;
    }
    .kk-meta svg{width:15px;height:15px;flex-shrink:0;color:#8a978f;margin-top:2px;}

    .kk-progress-head{
        display:flex;align-items:center;justify-content:space-between;
        font-size:13px;color:#5c6a62;margin-bottom:8px;
    }
    .kk-progress-head span:last-child{font-weight:700;color:#1f2a24;}
    .kk-progress-track{
        width:100%;height:8px;border-radius:999px;background:#e9ede9;overflow:hidden;margin-bottom:20px;
    }
    .kk-progress-fill{height:100%;border-radius:999px;}
    .kk-progress-fill.tone-hijau{background:#4d7c5f;}
    .kk-progress-fill.tone-merah{background:#e0483f;}

    .kk-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:22px;}
    .kk-stat{border-radius:10px;padding:12px 6px;text-align:center;}
    .kk-stat b{display:block;font-size:19px;font-weight:700;color:#1f2a24;line-height:1.3;}
    .kk-stat span{font-size:12px;color:#5c6a62;}
    .kk-stat.tone-biru{background:#e2ecfb;}
    .kk-stat.tone-hijau{background:#e3f3e8;}
    .kk-stat.tone-kuning{background:#fdf3cf;}

    /* ===== Tombol aksi kartu: 1 tombol utama + 3 tombol sekunder sama lebar ===== */
    .kk-actions{display:flex;flex-direction:column;gap:10px;margin-top:auto;}
    .kk-actions > .kk-btn{width:100%;}
    .kk-actions-sec{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;}
    .kk-actions-sec > .kk-btn,
    .kk-actions-sec .kk-share,
    .kk-actions-sec form{min-width:0;}
    .kk-actions-sec .kk-btn,
    .kk-actions-sec .kk-share-btn{width:100%;padding:0 8px;}
    .kk-actions-sec form{margin:0;display:flex;}
    .kk-actions-sec form .kk-btn{flex:1;}

    /* ===== Tombol Bagikan (dropdown) ===== */
    .kk-share{position:relative;}
    .kk-share.is-open .kk-share-btn{background:#f5f7f5;border-color:#c5cfc8;}
    .kk-share-menu{
        display:none;position:absolute;left:50%;bottom:calc(100% + 8px);transform:translateX(-50%);
        min-width:190px;background:#fff;border:1px solid #e1e7e2;border-radius:12px;
        box-shadow:0 12px 30px rgba(0,0,0,.14);padding:6px;z-index:50;
    }
    .kk-share.is-open .kk-share-menu{display:block;}
    .kk-share-menu a,.kk-share-menu button{
        display:flex;align-items:center;gap:10px;width:100%;
        padding:10px 12px;border-radius:8px;border:none;background:transparent;
        font-size:13.5px;font-weight:600;color:#2f3b34;text-decoration:none;
        cursor:pointer;text-align:left;font-family:inherit;
    }
    .kk-share-menu a:hover,.kk-share-menu button:hover{background:#f1f5f2;}
    .kk-share-menu svg{width:17px;height:17px;flex-shrink:0;}
    .kk-share-menu .ic-wa{color:#25a244;}
    .kk-share-menu .ic-copy{color:#4d7c5f;}

    /* ===== Kosong ===== */
    .kk-empty{
        padding:48px 16px;text-align:center;color:#7d8582;font-size:14px;
        background:#fff;border:1px solid #e7eae6;border-radius:20px;
    }

    /* ===== Footer: jumlah, per halaman, paginasi ===== */
    .kk-foot{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-top:24px;}
    .kk-foot-left{display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
    .kk-count{font-size:14px;color:#7d8582;}
    .kk-perpage{display:flex;align-items:center;gap:8px;font-size:14px;color:#7d8582;}
    .kk-pager{display:flex;gap:8px;flex-wrap:wrap;}
    .kk-page-btn{
        min-width:40px;height:34px;display:grid;place-items:center;padding:0 10px;
        background:#fff;border:1px solid #e7eae6;border-radius:8px;
        font:inherit;font-size:14px;font-weight:600;color:#1a2321;cursor:pointer;
    }
    .kk-page-btn svg{width:14px;height:14px;}
    .kk-page-btn:hover:not(:disabled){border-color:#7a9c7c;}
    .kk-page-btn.is-active{background:#7a9c7c;border-color:#7a9c7c;color:#fff;}
    .kk-page-btn:disabled{opacity:.45;cursor:default;}
    .kk-page-btn.is-gap{border-color:transparent;background:transparent;cursor:default;min-width:24px;padding:0;}
    .kk-page-btn:focus-visible{outline:2px solid #4d7c5f;outline-offset:2px;}

    /* ===== Form (input & select seragam) ===== */
    .kk-form-group{margin-bottom:16px;display:flex;flex-direction:column;gap:6px;min-width:0;}
    .kk-form-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
    .kk-form-group label{font-size:13px;font-weight:600;color:#3a463f;}
    .kk-form-group label small{font-weight:400;color:#8a978f;}
    .kk-form-group input,
    .kk-form-group select{
        width:100%;height:42px;border:1px solid #d8dfda;border-radius:10px;padding:0 12px;
        font-size:13.5px;color:#1f2a24;outline:none;font-family:inherit;background:#fff;
    }
    .kk-form-group select{
        appearance:none;-webkit-appearance:none;cursor:pointer;padding-right:40px;
        background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231a2321' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
    }
    .kk-form-group input:focus,
    .kk-form-group select:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}
    .kk-form-error{font-size:12px;color:#c0392b;margin:0;}

    /* ===== Modal Buat / Edit Program ===== */
    .kk-modal-overlay{
        display:none;position:fixed;inset:0;background:rgba(20,28,24,.45);
        align-items:center;justify-content:center;z-index:1000;padding:20px;
    }
    .kk-modal-overlay.is-open{display:flex;}
    .kk-modal{
        background:#fff;border-radius:16px;width:100%;max-width:520px;
        max-height:90vh;overflow-y:auto;padding:26px 26px 24px;
        box-shadow:0 20px 50px rgba(0,0,0,.2);
    }
    .kk-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;}
    .kk-modal-head h3{margin:0;font-size:17px;font-weight:700;color:#1f2a24;line-height:1.3;}
    .kk-modal-close{
        flex-shrink:0;border:none;background:#f1f3f1;color:#5c6a62;width:32px;height:32px;
        border-radius:8px;font-size:18px;line-height:1;cursor:pointer;
    }
    .kk-modal-close:hover{background:#e5e8e5;}
    .kk-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;}

    .kk-foot[hidden],.kk-pager[hidden],.kk-empty[hidden]{display:none;}

    /* ===== Responsive ===== */
    @media (max-width:900px){
        .kk-grid{grid-template-columns:1fr;}
    }
    @media (max-width:640px){
        .kk-head{margin-bottom:18px;}
        .kk-head > .kk-btn{width:100%;}
        .kk-search{max-width:none;}
        .kk-grid{gap:16px;}
        .kk-grid > .admin-card{padding:20px 18px;}
        .kk-foot{flex-direction:column;align-items:stretch;}
        .kk-foot-left{justify-content:space-between;}
        .kk-pager{justify-content:center;}
    }
    @media (max-width:520px){
        .kk-form-row{grid-template-columns:1fr;gap:0;}
        .kk-modal-overlay{align-items:flex-end;padding:0;}
        .kk-modal{max-width:none;max-height:92vh;border-radius:18px 18px 0 0;padding:22px 18px 20px;}
        .kk-modal-actions .kk-btn{flex:1;}
        .kk-stat b{font-size:17px;}
    }
</style>

<div class="kk-page">

    @if(session('sukses'))
        <div class="kk-alert">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
            </svg>
            {{ session('sukses') }}
        </div>
    @endif

    <div class="kk-head">
        <div>
            <h1>Konseling Kelompok</h1>
            <p>{{ $totalAktif }} program aktif &middot; {{ count($program) }} program total</p>
        </div>
        <button type="button" class="kk-btn kk-btn-fill kk-btn-lg" onclick="kkNewProgram()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Buat Program
        </button>
    </div>

    {{-- ============ TOOLBAR: CARI + FILTER STATUS ============ --}}
    @php
        $daftarStatus = collect($program)->pluck('status')->unique()->values();
    @endphp
    <div class="kk-toolbar" id="kkToolbar">
        <div class="kk-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" id="kkSearch" placeholder="Cari nama program, lokasi, atau fasilitator..." aria-label="Cari program" autocomplete="off">
        </div>

        <div class="kk-tabs" id="kkTabs" role="tablist" aria-label="Filter status">
            <button type="button" class="kk-tab is-active" role="tab" aria-selected="true" data-status="">
                Semua <em>{{ count($program) }}</em>
            </button>
            @foreach($daftarStatus as $st)
                <button type="button" class="kk-tab" role="tab" aria-selected="false" data-status="{{ $st }}">
                    {{ $st }} <em>{{ collect($program)->where('status', $st)->count() }}</em>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============ DAFTAR PROGRAM ============ --}}
    <div class="kk-grid" id="kkGrid">
        @foreach($program as $p)
            <div class="admin-card"
                 data-status="{{ $p['status'] }}"
                 data-cari="{{ strtolower($p['judul'] . ' ' . $p['lokasi'] . ' ' . $p['fasilitator']) }}">
                <div class="kk-card-head">
                    <h3>{{ $p['judul'] }}</h3>
                    <span class="kk-badge tone-{{ $p['status_tone'] }}">{{ $p['status'] }}</span>
                </div>

                <div class="kk-meta">
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        {{ $p['tanggal'] }} &middot; {{ $p['waktu'] }}
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $p['lokasi'] }}
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        {{ $p['fasilitator'] }}
                    </span>
                </div>

                <div class="kk-progress-head">
                    <span>Pendaftar</span>
                    <span>{{ $p['terdaftar'] }}/{{ $p['kuota'] }}</span>
                </div>
                <div class="kk-progress-track">
                    <div class="kk-progress-fill tone-{{ $p['progress_tone'] }}" style="width: {{ $p['pct'] }}%"></div>
                </div>

                <div class="kk-stats">
                    <div class="kk-stat tone-biru">
                        <b>{{ $p['kuota'] }}</b>
                        <span>Kuota</span>
                    </div>
                    <div class="kk-stat tone-hijau">
                        <b>{{ $p['terdaftar'] }}</b>
                        <span>Terdaftar</span>
                    </div>
                    <div class="kk-stat tone-kuning">
                        <b>{{ $p['sisa'] }}</b>
                        <span>Sisa</span>
                    </div>
                </div>

                @php
                    $linkDaftar = url('/konseling-kelompok/' . $p['id'] . '/daftar');
                    $teksWa = "Halo! Pendaftaran program Konseling Kelompok SILAKON dibuka:\n\n"
                        . "*" . $p['judul'] . "*\n"
                        . $p['tanggal'] . " · " . $p['waktu'] . "\n"
                        . $p['lokasi'] . "\n\n"
                        . "Daftar di sini: " . $linkDaftar;
                @endphp

                <div class="kk-actions">
                    <a href="{{ route('admin.konseling-kelompok.peserta', $p['id']) }}" class="kk-btn kk-btn-fill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Lihat Peserta
                    </a>

                    <div class="kk-actions-sec">
                        @if($p['status'] !== 'Selesai')
                        <button type="button" class="kk-btn kk-btn-outline" onclick="kkEditProgram(event,this)" data-id="{{ $p['id'] }}" data-judul="{{ $p['judul'] }}" data-tanggal="{{ $p['tanggal'] }}" data-waktu="{{ $p['waktu'] }}" data-lokasi="{{ $p['lokasi'] }}" data-fasilitator="{{ $p['fasilitator'] }}" data-kuota="{{ $p['kuota'] }}" data-tautan_grup="{{ $p['tautan_grup'] }}" data-status="{{ $p['status'] }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                            </svg>
                            Edit
                        </button>

                        <div class="kk-share">
                            <button type="button" class="kk-btn kk-btn-outline kk-share-btn" onclick="kkToggleShare(this, event)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                                    <path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>
                                </svg>
                                Bagikan
                            </button>
                            <div class="kk-share-menu">
                                <a href="https://wa.me/?text={{ rawurlencode($teksWa) }}" target="_blank" rel="noopener">
                                    <svg class="ic-wa" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.04 2a9.9 9.9 0 0 0-8.43 15.1L2 22l5.05-1.32A9.9 9.9 0 1 0 12.04 2Zm5.8 14.06c-.25.7-1.45 1.34-2 1.4-.52.06-1.17.09-1.89-.12a17 17 0 0 1-1.71-.63c-3-1.3-4.95-4.33-5.1-4.53-.15-.2-1.22-1.62-1.22-3.09 0-1.47.77-2.19 1.04-2.49.27-.3.6-.37.8-.37h.57c.18 0 .43-.07.67.51.25.6.85 2.07.92 2.22.08.15.13.33.03.52-.1.2-.15.33-.3.5-.15.18-.31.39-.45.52-.15.15-.3.31-.13.6.18.3.78 1.28 1.67 2.07 1.15 1.02 2.12 1.34 2.42 1.49.3.15.48.12.65-.08.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.98.3.15.5.22.57.35.08.12.08.72-.17 1.42Z"/>
                                    </svg>
                                    WhatsApp
                                </a>
                                <button type="button" data-url="{{ $linkDaftar }}" onclick="kkCopyLink(this)">
                                    <svg class="ic-copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                    <span>Salin Tautan</span>
                                </button>
                            </div>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('admin.konseling-kelompok.destroy', $p['id']) }}" onsubmit="return confirm('Hapus program ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="kk-btn kk-btn-danger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="kk-empty" id="kkEmpty" hidden>
        @if(count($program) === 0)
            Belum ada program. Klik "Buat Program" untuk menambahkan kegiatan pertama.
        @else
            Tidak ada program yang cocok dengan pencarian atau filter.
        @endif
    </div>

    {{-- ============ FOOTER: JUMLAH, PER HALAMAN & PAGINASI ============ --}}
    <div class="kk-foot" id="kkFoot">
        <div class="kk-foot-left">
            <span class="kk-count" id="kkCount" aria-live="polite"></span>
            <label class="kk-perpage">
                Tampilkan
                <select class="kk-select" id="kkPerPage" aria-label="Jumlah program per halaman" style="height:36px;border:1px solid #e7eae6;border-radius:8px;padding:0 12px;font:inherit;font-size:14px;font-weight:500;color:#1a2321;background:#fff;cursor:pointer;">
                    <option value="6" selected>6</option>
                    <option value="12">12</option>
                    <option value="24">24</option>
                </select>
                per halaman
            </label>
        </div>
        <nav class="kk-pager" id="kkPager" aria-label="Halaman"></nav>
    </div>
</div>

{{-- ============ MODAL: BUAT / EDIT PROGRAM ============ --}}
<div class="kk-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="kkModalBuatProgram">
    <div class="kk-modal" role="dialog" aria-modal="true" aria-labelledby="kkModalTitle">
        <div class="kk-modal-head">
            <h3 id="kkModalTitle">Buat Program Konseling Kelompok</h3>
            <button type="button" class="kk-modal-close" onclick="kkCloseModal()" aria-label="Tutup">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.konseling-kelompok.store') }}" id="kkProgramForm">
            @csrf
            <input type="hidden" name="_method" id="kkProgramMethod" value="POST">

            <div class="kk-form-group">
                <label for="kk-judul">Nama Program</label>
                <input type="text" id="kk-judul" name="judul" value="{{ old('judul') }}" placeholder="mis. Manajemen Waktu Efektif" required>
                @error('judul') <p class="kk-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="kk-form-row">
                <div class="kk-form-group">
                    <label for="kk-tanggal">Tanggal</label>
                    <input type="text" id="kk-tanggal" name="tanggal" value="{{ old('tanggal') }}" placeholder="mis. 20 Okt 2025" required>
                    @error('tanggal') <p class="kk-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="kk-form-group">
                    <label for="kk-waktu">Waktu</label>
                    <input type="text" id="kk-waktu" name="waktu" value="{{ old('waktu') }}" placeholder="mis. 09.00 – 12.00" required>
                    @error('waktu') <p class="kk-form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="kk-form-group">
                <label for="kk-lokasi">Lokasi</label>
                <input type="text" id="kk-lokasi" name="lokasi" value="{{ old('lokasi') }}" placeholder="mis. Ruang Seminar LPPP" required>
                @error('lokasi') <p class="kk-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="kk-form-group">
                <label for="kk-fasilitator">Fasilitator</label>
                <input type="text" id="kk-fasilitator" name="fasilitator" value="{{ old('fasilitator') }}" placeholder="mis. Dr. Sari Dewi, M.Psi" required>
                @error('fasilitator') <p class="kk-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="kk-form-row">
                <div class="kk-form-group">
                    <label for="kk-kuota">Kuota Peserta</label>
                    <input type="number" id="kk-kuota" name="kuota" min="1" value="{{ old('kuota') }}" placeholder="mis. 20" required>
                    @error('kuota') <p class="kk-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="kk-form-group">
                    <label for="kk-status">Status</label>
                    <select id="kk-status" name="status">
                        @foreach(['Pendaftaran Dibuka', 'Draft', 'Selesai'] as $opt)
                            <option value="{{ $opt }}" @selected(old('status', 'Pendaftaran Dibuka') === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="kk-form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="kk-form-group">
                <label for="kk-tautan">Tautan Grup WhatsApp <small>(opsional)</small></label>
                <input type="url" id="kk-tautan" name="tautan_grup" value="{{ old('tautan_grup') }}" placeholder="https://chat.whatsapp.com/...">
                @error('tautan_grup') <p class="kk-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="kk-modal-actions">
                <button type="button" class="kk-btn kk-btn-outline" onclick="kkCloseModal()">Batal</button>
                <button type="submit" class="kk-btn kk-btn-fill" id="kk-submit">Simpan Program</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ============ Modal buat / edit ============ */
    const kkStoreUrl  = @json(route('admin.konseling-kelompok.store'));
    const kkFields    = ['judul','tanggal','waktu','lokasi','fasilitator','kuota','tautan_grup'];
    const kkModal     = document.getElementById('kkModalBuatProgram');
    const kkForm      = document.getElementById('kkProgramForm');

    function kkOpenModal(){ kkModal.classList.add('is-open'); }
    function kkCloseModal(){ kkModal.classList.remove('is-open'); }

    function kkMode(judul, tombol, action, method){
        document.getElementById('kkModalTitle').textContent = judul;
        document.getElementById('kk-submit').textContent = tombol;
        kkForm.action = action;
        document.getElementById('kkProgramMethod').value = method;
        kkForm.querySelectorAll('.kk-form-error').forEach(function(el){ el.remove(); });
    }

    // Buka form kosong untuk program baru
    function kkNewProgram(){
        kkMode('Buat Program Konseling Kelompok', 'Simpan Program', kkStoreUrl, 'POST');
        kkFields.forEach(function(k){ var x = kkForm.querySelector('[name="'+k+'"]'); if (x) x.value = ''; });
        document.getElementById('kk-status').value = 'Pendaftaran Dibuka';
        kkOpenModal();
    }

    // Buka form berisi data program yang diedit
    function kkEditProgram(e, el){
        e.preventDefault();
        kkMode('Edit Program Konseling Kelompok', 'Simpan Perubahan', '/admin/konseling-kelompok/' + el.dataset.id, 'PUT');
        kkFields.forEach(function(k){ var x = kkForm.querySelector('[name="'+k+'"]'); if (x) x.value = el.dataset[k] || ''; });
        document.getElementById('kk-status').value = el.dataset.status || 'Pendaftaran Dibuka';
        kkOpenModal();
    }

    kkModal.addEventListener('click', function(e){ if (e.target === this) kkCloseModal(); });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && kkModal.classList.contains('is-open')) kkCloseModal();
    });

    /* ============ Bagikan ============ */
    function kkToggleShare(btn, e){
        e.stopPropagation();
        var wrap = btn.closest('.kk-share');
        var willOpen = !wrap.classList.contains('is-open');
        document.querySelectorAll('.kk-share.is-open').forEach(function(el){ el.classList.remove('is-open'); });
        if (willOpen) wrap.classList.add('is-open');
    }
    document.addEventListener('click', function(e){
        if (!e.target.closest('.kk-share')) {
            document.querySelectorAll('.kk-share.is-open').forEach(function(el){ el.classList.remove('is-open'); });
        }
    });
    function kkCopyLink(btn){
        var url = btn.getAttribute('data-url');
        var label = btn.querySelector('span');
        function done(){
            label.textContent = 'Tersalin!';
            setTimeout(function(){
                label.textContent = 'Salin Tautan';
                btn.closest('.kk-share').classList.remove('is-open');
            }, 1400);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(done);
        } else {
            var t = document.createElement('textarea');
            t.value = url; t.style.position = 'fixed'; t.style.opacity = '0';
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); done(); } catch(err) {}
            document.body.removeChild(t);
        }
    }

    /* ============ Cari + filter status + paginasi ============ */
    (function () {
        var cards   = Array.from(document.querySelectorAll('#kkGrid > .admin-card'));
        var empty   = document.getElementById('kkEmpty');
        var foot    = document.getElementById('kkFoot');
        var count   = document.getElementById('kkCount');
        var pager   = document.getElementById('kkPager');
        var perPage = document.getElementById('kkPerPage');
        var search  = document.getElementById('kkSearch');
        var tabs    = Array.from(document.querySelectorAll('#kkTabs .kk-tab'));
        var toolbar = document.getElementById('kkToolbar');

        var ICON_PREV = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
        var ICON_NEXT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';

        var statusAktif = '';
        var hasil = cards.slice();
        var halaman = 1;

        function saring() {
            var kata = search.value.trim().toLowerCase();
            hasil = cards.filter(function (c) {
                return (!kata || c.dataset.cari.indexOf(kata) !== -1) &&
                       (!statusAktif || c.dataset.status === statusAktif);
            });
            halaman = 1;
            tampilkan(false);
        }

        function tombol(isi, opsi) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'kk-page-btn' + (opsi.aktif ? ' is-active' : '');
            b.innerHTML = isi;
            if (opsi.label) b.setAttribute('aria-label', opsi.label);
            if (opsi.aktif) b.setAttribute('aria-current', 'page');
            if (opsi.disabled) b.disabled = true;
            if (opsi.ke) b.addEventListener('click', function () { halaman = opsi.ke; tampilkan(true); });
            return b;
        }

        function daftarHalaman(total, aktif) {
            if (total <= 7) return Array.from({ length: total }, function (_, i) { return i + 1; });
            var set = new Set([1, total, aktif, aktif - 1, aktif + 1]);
            if (aktif <= 3) { set.add(2); set.add(3); set.add(4); }
            if (aktif >= total - 2) { set.add(total - 1); set.add(total - 2); set.add(total - 3); }
            var urut = Array.from(set).filter(function (n) { return n >= 1 && n <= total; }).sort(function (a, b) { return a - b; });
            var out = [];
            urut.forEach(function (n, i) {
                if (i && n - urut[i - 1] > 1) out.push('...');
                out.push(n);
            });
            return out;
        }

        function tampilkan(gulir) {
            var total = hasil.length;
            var per = parseInt(perPage.value, 10);
            var totalHalaman = Math.max(1, Math.ceil(total / per));
            if (halaman > totalHalaman) halaman = totalHalaman;

            var awal = (halaman - 1) * per;
            var akhir = Math.min(awal + per, total);
            var tampil = new Set(hasil.slice(awal, akhir));

            cards.forEach(function (c) { c.hidden = !tampil.has(c); });
            empty.hidden = total > 0;
            foot.hidden = cards.length === 0;
            count.textContent = total ? (awal + 1) + '-' + akhir + ' dari ' + total + ' program' : '0 program';

            pager.innerHTML = '';
            pager.hidden = totalHalaman <= 1;
            if (totalHalaman > 1) {
                pager.appendChild(tombol(ICON_PREV, { label: 'Halaman sebelumnya', disabled: halaman <= 1, ke: halaman - 1 }));
                daftarHalaman(totalHalaman, halaman).forEach(function (n) {
                    if (n === '...') {
                        var g = document.createElement('span');
                        g.className = 'kk-page-btn is-gap';
                        g.textContent = '…';
                        pager.appendChild(g);
                    } else {
                        pager.appendChild(tombol(String(n), { aktif: n === halaman, ke: n, label: 'Halaman ' + n }));
                    }
                });
                pager.appendChild(tombol(ICON_NEXT, { label: 'Halaman berikutnya', disabled: halaman >= totalHalaman, ke: halaman + 1 }));
            }

            if (gulir) toolbar.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        search.addEventListener('input', saring);
        perPage.addEventListener('change', function () { halaman = 1; tampilkan(false); });
        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                statusAktif = t.dataset.status;
                tabs.forEach(function (x) {
                    var on = x === t;
                    x.classList.toggle('is-active', on);
                    x.setAttribute('aria-selected', on);
                });
                saring();
            });
        });

        tampilkan(false);
    })();
</script>

@endsection
