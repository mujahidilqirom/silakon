{{--
    SILAKON — Data Master (Admin)
    Simpan sebagai: resources/views/admin/data-master.blade.php

    Route yang dibutuhkan (lihat AdminDataMasterController):
    Route::get('/admin/data-master', [AdminDataMasterController::class, 'index'])
        ->name('admin.data-master');

    Tab "Konselor": hanya cari & lihat detail (tanpa tambah / edit / hapus).
    Nomor WhatsApp bisa diklik -> buka https://wa.me/{nomor}.
    Tab "Template Pesan": daftar template di kiri, pratinjau + edit di kanan (dummy, belum ke backend).
    Tab lain masih placeholder.
--}}
@extends('layouts.admin')

@section('judul', 'Data Master')

@section('content')

<style>
    /* ===== Halaman Data Master ===== */
    .dm-page{ padding-bottom:8px; }

    .dm-head{margin-bottom:22px;}
    .dm-head h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .dm-head p{margin:6px 0 0;color:#6b7a72;font-size:13.5px;}

    /* Tab */
    .dm-tabs{
        display:inline-flex;flex-wrap:wrap;gap:4px;
        background:#f1f4f1;border-radius:14px;padding:4px;margin-bottom:22px;
    }
    .dm-tab{
        padding:9px 16px;border-radius:10px;font-size:13.5px;font-weight:600;
        color:#5c6a62;text-decoration:none;white-space:nowrap;
    }
    .dm-tab:hover{color:#1f2a24;}
    .dm-tab.is-active{background:#fff;color:#1f2a24;box-shadow:0 1px 4px rgba(31,42,36,.1);}

    /* Kartu tabel */
    .dm-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;overflow:hidden;
        box-shadow:0 2px 8px rgba(31,42,36,.04);
    }
    .dm-card-head{
        display:flex;align-items:center;justify-content:space-between;
        flex-wrap:wrap;gap:12px;padding:20px 20px;
    }
    .dm-card-head h2{margin:0;font-size:15px;font-weight:700;color:#1f2a24;}

    .dm-search{position:relative;width:280px;max-width:100%;}
    .dm-search svg{
        position:absolute;left:13px;top:50%;transform:translateY(-50%);
        width:15px;height:15px;color:#8a978f;pointer-events:none;
    }
    .dm-search input{
        width:100%;box-sizing:border-box;background:#fff;
        border:1px solid #d8dfda;border-radius:10px;
        padding:9px 12px 9px 36px;font-size:13.5px;color:#1f2a24;
        font-family:inherit;outline:none;
    }
    .dm-search input:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}

    .dm-scroll{overflow-x:auto;}
    .dm-table{width:100%;border-collapse:collapse;font-size:13.5px;color:#4b5a51;}
    .dm-table thead th{
        background:#f4f7f4;text-align:left;
        font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
        color:#5c6a62;padding:16px 12px;white-space:nowrap;
    }
    .dm-table thead th:first-child,
    .dm-table tbody td:first-child{padding-left:20px;}
    .dm-table thead th:last-child,
    .dm-table tbody td:last-child{padding-right:20px;}
    .dm-table tbody td{padding:17px 12px;border-top:1px solid #eef1ee;vertical-align:middle;}
    .dm-table tbody tr:hover{background:#fafbfa;}
    .dm-nama{color:#1f2a24;font-weight:700;white-space:nowrap;}
    .dm-nowrap{white-space:nowrap;}

    .dm-wa{
        display:inline-flex;align-items:center;gap:7px;white-space:nowrap;
        color:#2e7d4f;font-weight:500;text-decoration:none;
    }
    .dm-wa:hover{color:#1f5f3a;text-decoration:underline;}
    .dm-wa svg{width:15px;height:15px;flex-shrink:0;}

    .dm-badge{
        display:inline-block;font-size:12px;font-weight:600;white-space:nowrap;
        padding:5px 12px;border-radius:999px;
    }
    .dm-badge.tone-hijau{background:#d9f5e3;color:#1f7a44;}
    .dm-badge.tone-abu{background:#eef0ee;color:#667066;}
    .dm-badge.tone-netral{background:#e8eee9;color:#3f5247;}

    .dm-btn-detail{
        display:inline-flex;align-items:center;gap:6px;
        background:#eef7f0;border:1px solid #b9d6c2;color:#3f6a4f;
        padding:6px 13px;border-radius:8px;font-size:12.5px;font-weight:500;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .dm-btn-detail:hover{background:#e0f0e5;}
    .dm-btn-detail svg{width:13px;height:13px;}

    .dm-btn-nonaktifkan{
        display:inline-flex;align-items:center;gap:6px;
        background:#fdf3d9;border:1px solid #eeda9c;color:#a9760a;
        padding:6px 13px;border-radius:8px;font-size:12.5px;font-weight:500;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .dm-btn-nonaktifkan:hover{background:#fbebc0;}
    .dm-btn-nonaktifkan svg{width:13px;height:13px;}

    .dm-btn-hapus-pill{
        display:inline-flex;align-items:center;gap:6px;
        background:#fdecea;border:1px solid #f3c6c0;color:#c0392b;
        padding:6px 13px;border-radius:8px;font-size:12.5px;font-weight:500;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .dm-btn-hapus-pill:hover{background:#fbdedb;}
    .dm-btn-hapus-pill svg{width:13px;height:13px;}

    .dm-aksi-group{display:flex;gap:8px;flex-wrap:wrap;}

    .dm-empty{text-align:center;color:#8a978f;padding:36px 12px !important;}

    /* Panel tab — switch via JS, tanpa reload halaman */
    .dm-tabpanel{display:none;}
    .dm-tabpanel.is-active{display:block;}

    .dm-btn-hijau{
        display:inline-flex;align-items:center;gap:7px;
        background:#4d7c5f;color:#fff;border:none;
        padding:10px 16px;border-radius:10px;font-size:13.5px;font-weight:600;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .dm-btn-hijau:hover{background:#3f6a4f;}
    .dm-btn-hijau svg{width:14px;height:14px;}

    /* ===== Fakultas & Prodi (accordion) ===== */
    .fp-list{display:flex;flex-direction:column;}
    .fp-item{border-top:1px solid #eef1ee;}
    .fp-item:first-child{border-top:none;}
    .fp-row{
        display:flex;align-items:center;gap:12px;padding:18px 20px;
        cursor:pointer;user-select:none;
    }
    .fp-chevron{width:16px;height:16px;color:#8a978f;flex-shrink:0;transition:transform .15s;}
    .fp-item.is-open .fp-chevron{transform:rotate(90deg);}
    .fp-nama-wrap{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0;}
    .fp-nama{font-weight:700;color:#1f2a24;font-size:14.5px;}
    .fp-singkatan{color:#8a978f;font-weight:500;font-size:13.5px;}
    .fp-count{
        display:inline-block;font-size:12px;font-weight:600;white-space:nowrap;
        padding:4px 11px;border-radius:999px;background:#e8eee9;color:#3f5247;
    }
    .fp-actions{margin-left:auto;display:flex;gap:8px;flex-shrink:0;}

    .fp-btn-tambah-prodi{
        display:inline-flex;align-items:center;gap:6px;
        background:#eef7f0;border:1px solid #b9d6c2;color:#3f6a4f;
        padding:6px 13px;border-radius:8px;font-size:12.5px;font-weight:500;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .fp-btn-tambah-prodi:hover{background:#e0f0e5;}
    .fp-btn-tambah-prodi svg{width:12px;height:12px;}

    .fp-icon-btn{
        width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;
        border-radius:8px;cursor:pointer;font-family:inherit;flex-shrink:0;
    }
    .fp-icon-btn svg{width:14px;height:14px;}
    .fp-icon-btn.fp-hapus{background:#fdecea;border:1px solid #f3c6c0;color:#c0392b;}
    .fp-icon-btn.fp-hapus:hover{background:#fbdedb;}
    .fp-icon-btn.fp-edit{background:#fff;border:1px solid #d8dfda;color:#5c6a62;}
    .fp-icon-btn.fp-edit:hover{background:#f4f6f4;}

    .fp-prodi-list{display:none;padding:0 20px 16px 48px;}
    .fp-item.is-open .fp-prodi-list{display:block;}
    .fp-prodi-row{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        padding:11px 0;border-top:1px solid #f5f7f5;
    }
    .fp-prodi-row:first-child{border-top:none;}
    .fp-prodi-nama{color:#3a463f;font-size:13.5px;}
    .fp-prodi-actions{display:flex;gap:6px;}
    .fp-prodi-empty{color:#8a978f;font-size:13px;padding:12px 0;}

    /* ===== Kategori Masalah (grid 2 kolom) ===== */
    .km-grid{
        display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:18px 20px;
    }
    @media (max-width:640px){ .km-grid{grid-template-columns:1fr;} }

    .km-item{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        border:1px solid #e1e7e3;border-radius:12px;padding:13px 15px;
    }
    .km-info{display:flex;align-items:center;gap:10px;flex-wrap:wrap;min-width:0;}
    .km-nama{font-weight:700;color:#1f2a24;font-size:13.5px;}
    .km-actions{display:flex;gap:7px;flex-shrink:0;}

    .km-icon-btn{
        width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;
        border-radius:8px;cursor:pointer;font-family:inherit;flex-shrink:0;border:1px solid transparent;
    }
    .km-icon-btn svg{width:14px;height:14px;}
    .km-icon-btn.tone-nonaktifkan{background:#fdf3d9;border-color:#eeda9c;color:#a9760a;}
    .km-icon-btn.tone-nonaktifkan:hover{background:#fbebc0;}
    .km-icon-btn.tone-aktifkan{background:#eef7f0;border-color:#b9d6c2;color:#3f6a4f;}
    .km-icon-btn.tone-aktifkan:hover{background:#e0f0e5;}
    .km-icon-btn.tone-hapus{background:#fdecea;border-color:#f3c6c0;color:#c0392b;}
    .km-icon-btn.tone-hapus:hover{background:#fbdedb;}

    .km-empty{padding:36px 12px;}

    /* ===== Template Pesan ===== */
    .dm-badge.tone-biru{background:#e0e9fb;color:#2a4a9f;}
    .tp-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:20px;align-items:start;}
    @media (max-width:820px){ .tp-layout{grid-template-columns:1fr;} }

    .tp-list-head{padding:18px 16px 14px;font-size:15px;font-weight:700;color:#1f2a24;}
    .tp-item{
        display:block;width:100%;box-sizing:border-box;text-align:left;
        background:#fff;border:none;border-top:1px solid #e1e7e3;
        border-left:3px solid transparent;padding:14px 16px 14px 14px;
        cursor:pointer;font-family:inherit;
    }
    .tp-item:hover{background:#f7faf7;}
    .tp-item.is-active{background:#eaf3ec;border-left-color:#4d7c5f;}
    .tp-item-nama{display:block;font-size:14px;font-weight:700;color:#1f2a24;margin-bottom:8px;}
    .tp-badge{font-size:11.5px;padding:4px 11px;}

    .tp-pane{display:none;padding:24px;}
    .tp-pane.is-active{display:block;}
    .tp-pane-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;}
    .tp-pane-head h2{margin:0 0 8px;font-size:16px;font-weight:700;color:#1f2a24;}

    .tp-btn-edit{
        display:inline-flex;align-items:center;gap:7px;flex-shrink:0;
        background:#eef7f0;border:1px solid #9dbfa9;color:#3f6a4f;
        padding:8px 15px;border-radius:9px;font-size:13px;font-weight:600;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .tp-btn-edit:hover{background:#e0f0e5;}
    .tp-btn-edit svg{width:14px;height:14px;}

    .tp-var-label{margin:0 0 10px;font-size:13.5px;font-weight:600;color:#4b5a51;}
    .tp-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
    .tp-chip{
        display:inline-block;background:#e3ebfb;color:#1f3fa8;
        font-size:12.5px;font-weight:600;padding:5px 10px;border-radius:6px;
        border:none;font-family:inherit;
    }
    button.tp-chip{cursor:pointer;}
    button.tp-chip:hover{background:#d3dffa;}

    .tp-preview{
        background:#f4f6f4;border:1px solid #e1e7e3;border-radius:12px;
        padding:18px 20px;font-size:14px;line-height:1.65;color:#2a352e;
        white-space:pre-wrap;word-break:break-word;
    }
    .tp-var{background:#e3ebfb;color:#1f3fa8;font-weight:600;padding:1px 6px;border-radius:5px;}

    .tp-textarea{
        width:100%;box-sizing:border-box;min-height:220px;resize:vertical;
        border:1px solid #d8dfda;border-radius:10px;padding:12px 14px;
        font-size:14px;line-height:1.6;font-family:inherit;color:#1f2a24;outline:none;
    }
    .tp-textarea:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}

    /* Placeholder tab lain */
    .dm-placeholder{
        background:#fff;border:1px dashed #cfd8d2;border-radius:18px;
        text-align:center;color:#8a978f;padding:56px 20px;font-size:14px;
    }
    .dm-placeholder b{display:block;color:#3a463f;font-size:15px;margin-bottom:6px;}

    /* ===== Modal Detail ===== */
    .dm-modal-overlay{
        display:none;position:fixed;inset:0;background:rgba(20,28,24,.45);
        align-items:center;justify-content:center;z-index:1000;padding:20px;
    }
    .dm-modal-overlay.is-open{display:flex;}
    .dm-modal{
        background:#fff;border-radius:16px;width:100%;max-width:440px;
        max-height:90vh;overflow-y:auto;padding:26px 26px 24px;
        box-shadow:0 20px 50px rgba(0,0,0,.2);
    }
    .dm-modal-head{
        display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;
    }
    .dm-modal-head h3{margin:0;font-size:17px;font-weight:700;color:#1f2a24;}
    .dm-modal-close{
        border:none;background:#f1f3f1;color:#5c6a62;width:30px;height:30px;
        border-radius:8px;font-size:18px;line-height:1;cursor:pointer;
    }
    .dm-modal-close:hover{background:#e5e8e5;}

    .dm-profil{
        display:flex;align-items:center;gap:14px;margin-bottom:20px;
        padding-bottom:20px;border-bottom:1px solid #eef1ee;
    }
    .dm-avatar{
        width:52px;height:52px;border-radius:50%;background:#7fa285;color:#fff;
        display:flex;align-items:center;justify-content:center;
        font-size:18px;font-weight:700;flex-shrink:0;
    }
    .dm-profil-nama{font-size:16px;font-weight:700;color:#1f2a24;margin:0 0 6px;}
    .dm-profil-tag{display:flex;flex-wrap:wrap;gap:6px;}

    .dm-detail-list{display:flex;flex-direction:column;gap:14px;margin:0;}
    .dm-detail-list div{display:flex;flex-direction:column;gap:3px;}
    .dm-detail-list dt{font-size:12px;font-weight:600;color:#8a978f;margin:0;}
    .dm-detail-list dd{font-size:14px;color:#1f2a24;margin:0;word-break:break-word;}
    .dm-detail-list a{color:#2e7d4f;text-decoration:none;}
    .dm-detail-list a:hover{text-decoration:underline;}

    .dm-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:24px;}
    .dm-modal-actions a,
    .dm-modal-actions button{
        display:inline-flex;align-items:center;gap:7px;
        padding:10px 18px;border-radius:9px;font-size:13.5px;font-weight:600;
        cursor:pointer;font-family:inherit;text-decoration:none;
    }
    .dm-modal-actions .dm-btn-outline{background:#fff;border:1px solid #d8dfda;color:#3a463f;}
    .dm-modal-actions .dm-btn-wa{background:#4d7c5f;color:#fff;border:none;}
    .dm-modal-actions .dm-btn-wa:hover{background:#3f6a4f;}
    .dm-modal-actions svg{width:15px;height:15px;}
</style>

<div class="dm-page">

    <div class="dm-head">
        <h1>Data Master</h1>
        <p>Kelola data referensi sistem</p>
    </div>

    {{-- Tab --}}
    <div class="dm-tabs">
        @foreach($tab as $t)
            <a href="{{ route('admin.data-master', ['tab' => $t['key']]) }}"
               class="dm-tab {{ $tabAktif === $t['key'] ? 'is-active' : '' }}"
               data-tab="{{ $t['key'] }}"
               onclick="return dmGantiTab(event, '{{ $t['key'] }}')">{{ $t['label'] }}</a>
        @endforeach
    </div>

    {{-- Panel: Konselor --}}
    <div class="dm-tabpanel {{ $tabAktif === 'konselor' ? 'is-active' : '' }}" id="panel-konselor">
        <div class="dm-card">
            <div class="dm-card-head">
                <h2>Daftar Konselor</h2>
                <div class="dm-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" id="dmCari" placeholder="Cari nama, email, atau no WA..." autocomplete="off">
                </div>
            </div>

            <div class="dm-scroll">
                <table class="dm-table" id="dmTabel">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No WA</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($konselor as $k)
                            <tr data-cari="{{ mb_strtolower($k['nama'] . ' ' . $k['email'] . ' ' . $k['whatsapp_tampil'] . ' ' . $k['whatsapp'] . ' ' . $k['keterangan']) }}">
                                <td class="dm-nama">{{ $k['nama'] }}</td>
                                <td class="dm-nowrap">{{ $k['email'] }}</td>
                                <td>
                                    <a href="https://wa.me/{{ $k['whatsapp'] }}" target="_blank" rel="noopener" class="dm-wa">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.7-5.4A8.4 8.4 0 1 1 21 11.5Z"/>
                                        </svg>
                                        {{ $k['whatsapp_tampil'] }}
                                    </a>
                                </td>
                                <td class="dm-nowrap">{{ $k['keterangan'] }}</td>
                                <td><span class="dm-badge tone-{{ $k['status_tone'] }}">{{ $k['status'] }}</span></td>
                                <td>
                                    <button type="button" class="dm-btn-detail"
                                        data-nama="{{ $k['nama'] }}"
                                        data-email="{{ $k['email'] }}"
                                        data-wa="{{ $k['whatsapp'] }}"
                                        data-wa-tampil="{{ $k['whatsapp_tampil'] }}"
                                        data-keterangan="{{ $k['keterangan'] }}"
                                        data-status="{{ $k['status'] }}"
                                        data-tone="{{ $k['status_tone'] }}"
                                        onclick="dmBukaDetail(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="dm-empty">Belum ada data konselor.</td></tr>
                        @endforelse

                        @if($konselor->isNotEmpty())
                            <tr id="dmKosong" style="display:none;">
                                <td colspan="6" class="dm-empty">Tidak ada konselor yang cocok dengan pencarian.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Panel: Fakultas & Prodi --}}
    <div class="dm-tabpanel {{ $tabAktif === 'fakultas-prodi' ? 'is-active' : '' }}" id="panel-fakultas-prodi">
        <div class="dm-card">
            <div class="dm-card-head">
                <h2>Fakultas dan Program Studi</h2>
                <button type="button" class="dm-btn-hijau" onclick="fpBukaTambahFakultas()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambah Fakultas
                </button>
            </div>

            <div class="fp-list" id="fpList">
                @forelse($fakultasProdi as $f)
                    <div class="fp-item" data-fakultas-id="{{ $f['id'] }}">
                        <div class="fp-row" onclick="fpToggle(this)">
                            <svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 6 6 6-6 6"/>
                            </svg>
                            <div class="fp-nama-wrap">
                                <span class="fp-nama">{{ $f['nama'] }}</span>
                                <span class="fp-count" data-count>{{ $f['prodi']->count() }} Prodi</span>
                            </div>
                            <div class="fp-actions" onclick="event.stopPropagation()">
                                <button type="button" class="fp-btn-tambah-prodi" onclick="fpBukaTambahProdi('{{ $f['id'] }}', '{{ $f['nama'] }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                    Tambah Prodi
                                </button>
                                <button type="button" class="fp-icon-btn fp-hapus" title="Hapus fakultas" onclick="fpHapusFakultas(this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="fp-prodi-list" data-prodi-list>
                            @forelse($f['prodi'] as $p)
                                <div class="fp-prodi-row" data-prodi-id="{{ $p['id'] }}">
                                    <span class="fp-prodi-nama" data-prodi-nama>{{ $p['nama'] }}</span>
                                    <span class="fp-prodi-jenjang dm-badge tone-netral" data-prodi-jenjang style="margin-left:auto">{{ $p['jenjang'] ?? 'S1' }}</span>
                                    <div class="fp-prodi-actions">
                                        <button type="button" class="fp-icon-btn fp-edit" title="Ubah prodi" onclick="fpBukaEditProdi(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="fp-icon-btn fp-hapus" title="Hapus prodi" onclick="fpHapusProdi(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="fp-prodi-empty">Belum ada prodi.</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="dm-empty">Belum ada data fakultas.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Panel: Kategori Masalah --}}
    <div class="dm-tabpanel {{ $tabAktif === 'kategori-masalah' ? 'is-active' : '' }}" id="panel-kategori-masalah">
        <div class="dm-card">
            <div class="dm-card-head">
                <h2>Kategori Masalah</h2>
                <button type="button" class="dm-btn-hijau" onclick="kmBukaTambah()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambah Kategori
                </button>
            </div>

            <div class="km-grid" id="kmGrid">
                @forelse($kategoriMasalah as $k)
                    <div class="km-item" data-kategori-id="{{ $k['id'] }}" data-status="{{ $k['status'] }}">
                        <div class="km-info">
                            <span class="km-nama">{{ $k['nama'] }}</span>
                            <span class="dm-badge tone-{{ $k['status_tone'] }}" data-km-badge>{{ $k['status'] }}</span>
                        </div>
                        <div class="km-actions">
                            <button type="button"
                                class="km-icon-btn tone-{{ $k['status'] === 'Aktif' ? 'nonaktifkan' : 'aktifkan' }}"
                                title="{{ $k['status'] === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                onclick="kmToggleStatus(this)">
                                @if($k['status'] === 'Aktif')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                @endif
                            </button>
                            <button type="button" class="km-icon-btn tone-hapus" title="Hapus kategori" onclick="kmHapusKategori(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="dm-empty km-empty">Belum ada kategori masalah.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Panel: Lokasi & Ruang --}}
    <div class="dm-tabpanel {{ $tabAktif === 'lokasi-ruang' ? 'is-active' : '' }}" id="panel-lokasi-ruang">
        <div class="dm-card">
            <div class="dm-card-head">
                <h2>Lokasi dan Ruang</h2>
                <button type="button" class="dm-btn-hijau" onclick="lokBukaTambah()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambah Lokasi
                </button>
            </div>

            <div class="dm-scroll">
                <table class="dm-table" id="lokTabel">
                    <thead>
                        <tr>
                            <th>Nama Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="lokBody">
                        @forelse($lokasiRuang as $l)
                            <tr data-lokasi-id="{{ $l['id'] }}" data-status="{{ $l['status'] }}">
                                <td class="dm-nama" data-lok-nama>{{ $l['nama'] }}</td>
                                <td data-lok-deskripsi>{{ $l['deskripsi'] }}</td>
                                <td><span class="dm-badge tone-{{ $l['status_tone'] }}" data-lok-badge>{{ $l['status'] }}</span></td>
                                <td>
                                    <div class="dm-aksi-group">
                                        <button type="button" class="dm-btn-detail" onclick="lokBukaEdit(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            Edit
                                        </button>
                                        <button type="button"
                                            class="{{ $l['status'] === 'Aktif' ? 'dm-btn-nonaktifkan' : 'dm-btn-detail' }}"
                                            data-lok-toggle
                                            onclick="lokToggleStatus(this)">
                                            @if($l['status'] === 'Aktif')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                                                Nonaktifkan
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                Aktifkan
                                            @endif
                                        </button>
                                        <button type="button" class="dm-btn-hapus-pill" onclick="lokHapus(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="dm-empty">Belum ada data lokasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Panel: Template Pesan --}}
    <div class="dm-tabpanel {{ $tabAktif === 'template-pesan' ? 'is-active' : '' }}" id="panel-template-pesan">
        <div class="tp-layout">
            {{-- Daftar template --}}
            <div class="dm-card">
                <div class="tp-list-head">Template</div>
                @foreach($templatePesan as $i => $t)
                    <button type="button" class="tp-item {{ $i === 0 ? 'is-active' : '' }}"
                            data-tp="{{ $t['id'] }}" onclick="tpPilih(this)">
                        <span class="tp-item-nama">{{ $t['nama'] }}</span>
                        <span class="dm-badge tp-badge tone-{{ $t['kanal_tone'] }}">{{ $t['kanal'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Detail template --}}
            <div class="dm-card">
                @forelse($templatePesan as $i => $t)
                    <div class="tp-pane {{ $i === 0 ? 'is-active' : '' }}" id="tp-pane-{{ $t['id'] }}"
                         data-nama="{{ $t['nama'] }}" data-isi="{{ $t['isi'] }}">
                        <div class="tp-pane-head">
                            <div>
                                <h2>{{ $t['nama'] }}</h2>
                                <span class="dm-badge tp-badge tone-{{ $t['kanal_tone'] }}">{{ $t['kanal'] }}</span>
                            </div>
                            <button type="button" class="tp-btn-edit" onclick="tpBukaEdit('{{ $t['id'] }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                Edit Template
                            </button>
                        </div>

                        <p class="tp-var-label">Variabel yang tersedia:</p>
                        <div class="tp-chips">
                            @foreach($t['variabel'] as $v)
                                <span class="tp-chip">{{ $v }}</span>
                            @endforeach
                        </div>

                        <div class="tp-preview" data-tp-preview>{!! $t['isi_html'] !!}</div>
                    </div>
                @empty
                    <div class="dm-empty">Belum ada template pesan.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Panel tab lain — masih placeholder --}}
    @foreach($tab as $t)
        @if(! in_array($t['key'], ['konselor', 'fakultas-prodi', 'kategori-masalah', 'lokasi-ruang', 'template-pesan']))
            <div class="dm-tabpanel {{ $tabAktif === $t['key'] ? 'is-active' : '' }}" id="panel-{{ $t['key'] }}">
                <div class="dm-placeholder">
                    <b>{{ $t['label'] }}</b>
                    Data ini belum tersedia. Bagian ini akan diisi setelah datanya disiapkan.
                </div>
            </div>
        @endif
    @endforeach
</div>

{{-- ============ MODAL: DETAIL KONSELOR ============ --}}
<div class="dm-modal-overlay" id="dmModalDetail">
    <div class="dm-modal">
        <div class="dm-modal-head">
            <h3>Detail Konselor</h3>
            <button type="button" class="dm-modal-close" onclick="dmTutupDetail()">&times;</button>
        </div>

        <div class="dm-profil">
            <div class="dm-avatar" id="dmAvatar"></div>
            <div>
                <p class="dm-profil-nama" id="dmNama"></p>
                <div class="dm-profil-tag">
                    <span class="dm-badge tone-netral" id="dmKeterangan"></span>
                    <span class="dm-badge" id="dmStatus"></span>
                </div>
            </div>
        </div>

        <dl class="dm-detail-list">
            <div>
                <dt>Email</dt>
                <dd><a href="#" id="dmEmail"></a></dd>
            </div>
            <div>
                <dt>No WhatsApp</dt>
                <dd><a href="#" id="dmWa" target="_blank" rel="noopener"></a></dd>
            </div>
            <div>
                <dt>Keterangan</dt>
                <dd id="dmKeteranganTeks"></dd>
            </div>
        </dl>

        <div class="dm-modal-actions">
            <button type="button" class="dm-btn-outline" onclick="dmTutupDetail()">Tutup</button>
            <a href="#" id="dmBtnWa" class="dm-btn-wa" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.7-5.4A8.4 8.4 0 1 1 21 11.5Z"/>
                </svg>
                Hubungi via WhatsApp
            </a>
        </div>
    </div>
</div>

{{-- ============ MODAL: TAMBAH FAKULTAS ============ --}}
<div class="dm-modal-overlay" id="fpModalFakultas">
    <div class="dm-modal" style="max-width:420px;">
        <div class="dm-modal-head">
            <h3>Tambah Fakultas</h3>
            <button type="button" class="dm-modal-close" onclick="fpTutup('fpModalFakultas')">&times;</button>
        </div>

        <form id="fpFormFakultas" onsubmit="return fpSimpanFakultas(event)">
            <div class="dm-detail-list" style="margin-bottom:22px;">
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Nama Fakultas</dt>
                    <dd><input type="text" id="fpInputNamaFakultas" required placeholder="mis. Fakultas Bahasa dan Seni"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Singkatan</dt>
                    <dd><input type="text" id="fpInputSingkatanFakultas" required placeholder="mis. FBS"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
            </div>
            <div class="dm-modal-actions">
                <button type="button" class="dm-btn-outline" onclick="fpTutup('fpModalFakultas')">Batal</button>
                <button type="submit" class="dm-btn-wa">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: TAMBAH / UBAH PRODI ============ --}}
<div class="dm-modal-overlay" id="fpModalProdi">
    <div class="dm-modal" style="max-width:420px;">
        <div class="dm-modal-head">
            <h3 id="fpProdiJudul">Tambah Prodi</h3>
            <button type="button" class="dm-modal-close" onclick="fpTutup('fpModalProdi')">&times;</button>
        </div>

        <form id="fpFormProdi" onsubmit="return fpSimpanProdi(event)">
            <div class="dm-detail-list" style="margin-bottom:22px;">
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Fakultas</dt>
                    <dd id="fpProdiFakultasLabel" style="color:#5c6a62;"></dd>
                </div>
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Nama Prodi</dt>
                    <dd><input type="text" id="fpInputNamaProdi" required placeholder="mis. Sastra Indonesia"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Jenjang</dt>
                    <dd><select id="fpInputJenjang" required
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;background:#fff;">
                        <option value="D3">D3</option>
                        <option value="S1" selected>S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="Profesi">Profesi</option>
                    </select></dd>
                </div>
            </div>
            <div class="dm-modal-actions">
                <button type="button" class="dm-btn-outline" onclick="fpTutup('fpModalProdi')">Batal</button>
                <button type="submit" class="dm-btn-wa">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: TAMBAH KATEGORI MASALAH ============ --}}
<div class="dm-modal-overlay" id="kmModalTambah">
    <div class="dm-modal" style="max-width:420px;">
        <div class="dm-modal-head">
            <h3>Tambah Kategori</h3>
            <button type="button" class="dm-modal-close" onclick="fpTutup('kmModalTambah')">&times;</button>
        </div>

        <form id="kmFormTambah" onsubmit="return kmSimpanKategori(event)">
            <div class="dm-detail-list" style="margin-bottom:22px;">
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Nama Kategori</dt>
                    <dd><input type="text" id="kmInputNama" required placeholder="mis. Keuangan"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
            </div>
            <div class="dm-modal-actions">
                <button type="button" class="dm-btn-outline" onclick="fpTutup('kmModalTambah')">Batal</button>
                <button type="submit" class="dm-btn-wa">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: TAMBAH / UBAH LOKASI ============ --}}
<div class="dm-modal-overlay" id="lokModal">
    <div class="dm-modal" style="max-width:440px;">
        <div class="dm-modal-head">
            <h3 id="lokModalJudul">Tambah Lokasi</h3>
            <button type="button" class="dm-modal-close" onclick="fpTutup('lokModal')">&times;</button>
        </div>

        <form id="lokForm" onsubmit="return lokSimpan(event)">
            <div class="dm-detail-list" style="margin-bottom:22px;">
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Nama Lokasi</dt>
                    <dd><input type="text" id="lokInputNama" required placeholder="mis. Ruang BK Gedung C"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
                <div>
                    <dt style="font-size:12px;font-weight:600;color:#8a978f;">Deskripsi</dt>
                    <dd><input type="text" id="lokInputDeskripsi" required placeholder="mis. Gedung C Lantai 1"
                        style="width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;outline:none;"></dd>
                </div>
            </div>
            <div class="dm-modal-actions">
                <button type="button" class="dm-btn-outline" onclick="fpTutup('lokModal')">Batal</button>
                <button type="submit" class="dm-btn-wa">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: EDIT TEMPLATE PESAN ============ --}}
<div class="dm-modal-overlay" id="tpModal">
    <div class="dm-modal" style="max-width:560px;">
        <div class="dm-modal-head">
            <h3 id="tpModalJudul">Edit Template</h3>
            <button type="button" class="dm-modal-close" onclick="fpTutup('tpModal')">&times;</button>
        </div>

        <form id="tpForm" onsubmit="return tpSimpan(event)">
            <p class="tp-var-label">Klik variabel untuk menyisipkan:</p>
            <div class="tp-chips" id="tpModalChips"></div>

            <textarea class="tp-textarea" id="tpInputIsi" required></textarea>

            <div class="dm-modal-actions">
                <button type="button" class="dm-btn-outline" onclick="fpTutup('tpModal')">Batal</button>
                <button type="submit" class="dm-btn-wa">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const dmCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    function dmRequest(url, method, body) { return fetch(url, {method, headers:{'X-CSRF-TOKEN':dmCsrf,'Accept':'application/json','Content-Type':'application/json'}, body: body ? JSON.stringify(body) : undefined}).then(async r => { if(!r.ok){ const j=await r.json().catch(()=>({})); throw new Error(j.message || 'Perubahan gagal disimpan.'); } return r.json(); }); }
    function dmReload(){ window.location.reload(); }
    // ----- Pencarian (nama, email, no WA, keterangan) -----
    (function () {
        var input  = document.getElementById('dmCari');
        if (!input) return;

        var kosong = document.getElementById('dmKosong');
        var rows   = Array.prototype.slice.call(document.querySelectorAll('#dmTabel tbody tr[data-cari]'));

        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var tampil = 0;

            rows.forEach(function (row) {
                var cocok = row.getAttribute('data-cari').indexOf(q) !== -1;
                row.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            if (kosong) kosong.style.display = tampil ? 'none' : '';
        });
    })();

    // ----- Modal detail -----
    function dmBukaDetail(btn) {
        var d = btn.dataset;
        var waUrl = 'https://wa.me/' + d.wa;

        document.getElementById('dmAvatar').textContent = d.nama.replace(/^(Dr\.|Drs\.|Prof\.)\s*/i, '').charAt(0).toUpperCase();
        document.getElementById('dmNama').textContent = d.nama;
        document.getElementById('dmKeterangan').textContent = d.keterangan;
        document.getElementById('dmKeteranganTeks').textContent = d.keterangan;

        var status = document.getElementById('dmStatus');
        status.textContent = d.status;
        status.className = 'dm-badge tone-' + d.tone;

        var email = document.getElementById('dmEmail');
        email.textContent = d.email;
        email.href = 'mailto:' + d.email;

        var wa = document.getElementById('dmWa');
        wa.textContent = d.waTampil;
        wa.href = waUrl;
        document.getElementById('dmBtnWa').href = waUrl;

        document.getElementById('dmModalDetail').classList.add('is-open');
    }
    function dmTutupDetail() {
        document.getElementById('dmModalDetail').classList.remove('is-open');
    }
    document.getElementById('dmModalDetail').addEventListener('click', function (e) {
        if (e.target === this) dmTutupDetail();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        dmTutupDetail();
        fpTutup('fpModalFakultas');
        fpTutup('fpModalProdi');
        fpTutup('kmModalTambah');
        fpTutup('lokModal');
        fpTutup('tpModal');
    });

    // ----- Ganti tab tanpa reload -----
    function dmGantiTab(e, key) {
        e.preventDefault();

        document.querySelectorAll('.dm-tab').forEach(function (a) {
            a.classList.toggle('is-active', a.dataset.tab === key);
        });
        document.querySelectorAll('.dm-tabpanel').forEach(function (panel) {
            panel.classList.toggle('is-active', panel.id === 'panel-' + key);
        });

        // Supaya link bisa dibookmark / refresh tetap di tab yang sama, tanpa memuat ulang halaman.
        var url = new URL(window.location.href);
        url.searchParams.set('tab', key);
        window.history.pushState({ tab: key }, '', url);

        return false;
    }

    window.addEventListener('popstate', function () {
        var key = new URL(window.location.href).searchParams.get('tab') || 'konselor';
        document.querySelectorAll('.dm-tab').forEach(function (a) {
            a.classList.toggle('is-active', a.dataset.tab === key);
        });
        document.querySelectorAll('.dm-tabpanel').forEach(function (panel) {
            panel.classList.toggle('is-active', panel.id === 'panel-' + key);
        });
    });

    // ----- Fakultas & Prodi -----
    var fpFakultasBerikutnya = 1000; // id sementara untuk fakultas/prodi baru (dummy, belum ke backend)
    var fpProdiBerikutnya    = 1000;
    var fpProdiFakultasAktif = null; // id fakultas yang sedang ditambah prodinya
    var fpProdiEditRow       = null; // row prodi yang sedang diubah (null kalau mode tambah)

    function fpToggle(row) {
        row.closest('.fp-item').classList.toggle('is-open');
    }

    function fpTutup(id) {
        document.getElementById(id).classList.remove('is-open');
    }
    function fpBuka(id) {
        document.getElementById(id).classList.add('is-open');
    }

    function fpBukaTambahFakultas() {
        document.getElementById('fpFormFakultas').reset();
        fpBuka('fpModalFakultas');
    }

    function fpSimpanFakultas(e) {
        e.preventDefault();
        var nama      = document.getElementById('fpInputNamaFakultas').value.trim();
        var singkatan = document.getElementById('fpInputSingkatanFakultas').value.trim();
        if (!nama || !singkatan) return false;
        dmRequest('{{ route('admin.data-master.fakultas.store') }}','POST',{nama,singkatan}).then(dmReload).catch(err=>alert(err.message)); return false;

        var id = fpFakultasBerikutnya++;
        var item = document.createElement('div');
        item.className = 'fp-item';
        item.dataset.fakultasId = id;
        item.innerHTML =
            '<div class="fp-row" onclick="fpToggle(this)">' +
                '<svg class="fp-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>' +
                '<div class="fp-nama-wrap">' +
                    '<span class="fp-nama"></span>' +
                    '<span class="fp-count" data-count>0 Prodi</span>' +
                '</div>' +
                '<div class="fp-actions" onclick="event.stopPropagation()">' +
                    '<button type="button" class="fp-btn-tambah-prodi" onclick="fpBukaTambahProdi(\'' + id + '\', this.closest(\'.fp-item\').querySelector(\'.fp-nama\').textContent)">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>' +
                        'Tambah Prodi' +
                    '</button>' +
                    '<button type="button" class="fp-icon-btn fp-hapus" title="Hapus fakultas" onclick="fpHapusFakultas(this)">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="fp-prodi-list" data-prodi-list><div class="fp-prodi-empty">Belum ada prodi.</div></div>';

        item.querySelector('.fp-nama').textContent = nama + ' (' + singkatan + ')';

        var kosong = document.querySelector('#fpList > .dm-empty');
        if (kosong) kosong.remove();
        document.getElementById('fpList').appendChild(item);

        fpTutup('fpModalFakultas');
        return false;
    }

    function fpHapusFakultas(btn) {
        if (!confirm('Hapus fakultas ini beserta seluruh program studinya?')) return;
        dmRequest('/admin/data-master/fakultas/'+btn.closest('.fp-item').dataset.fakultasId,'DELETE').then(dmReload).catch(err=>alert(err.message));
    }

    function fpBukaTambahProdi(fakultasId, labelFakultas) {
        fpProdiFakultasAktif = fakultasId;
        fpProdiEditRow = null;
        document.getElementById('fpProdiJudul').textContent = 'Tambah Prodi';
        document.getElementById('fpProdiFakultasLabel').textContent = labelFakultas;
        document.getElementById('fpFormProdi').reset();
        document.getElementById('fpInputJenjang').value = 'S1';
        fpBuka('fpModalProdi');
    }

    function fpBukaEditProdi(btn) {
        var row = btn.closest('.fp-prodi-row');
        var fakultasItem = btn.closest('.fp-item');
        fpProdiFakultasAktif = null;
        fpProdiEditRow = row;

        document.getElementById('fpProdiJudul').textContent = 'Ubah Prodi';
        document.getElementById('fpProdiFakultasLabel').textContent = fakultasItem.querySelector('.fp-nama').textContent;
        document.getElementById('fpInputNamaProdi').value = row.querySelector('[data-prodi-nama]').textContent;
        document.getElementById('fpInputJenjang').value = (row.querySelector('[data-prodi-jenjang]')?.textContent || 'S1').trim();
        fpBuka('fpModalProdi');
    }

    function fpSimpanProdi(e) {
        e.preventDefault();
        var nama = document.getElementById('fpInputNamaProdi').value.trim();
        var jenjang = document.getElementById('fpInputJenjang').value;
        if (!nama || !jenjang) return false;
        var url = fpProdiEditRow ? '/admin/data-master/prodi/'+fpProdiEditRow.dataset.prodiId : '{{ route('admin.data-master.prodi.store') }}';
        var payload = {nama, jenjang}; if(!fpProdiEditRow) payload.fakultas_id = fpProdiFakultasAktif;
        dmRequest(url, fpProdiEditRow ? 'PUT' : 'POST', payload).then(dmReload).catch(err=>alert(err.message)); return false;

        if (fpProdiEditRow) {
            // Mode ubah
            fpProdiEditRow.querySelector('[data-prodi-nama]').textContent = nama;
            fpProdiEditRow.querySelector('[data-prodi-jenjang]').textContent = jenjang;
        } else {
            // Mode tambah
            var fakultasItem = document.querySelector('.fp-item[data-fakultas-id="' + fpProdiFakultasAktif + '"]');
            if (!fakultasItem) return false;

            var list = fakultasItem.querySelector('[data-prodi-list]');
            var kosong = list.querySelector('.fp-prodi-empty');
            if (kosong) kosong.remove();

            var id = fpProdiBerikutnya++;
            var row = document.createElement('div');
            row.className = 'fp-prodi-row';
            row.dataset.prodiId = id;
            row.innerHTML =
                '<span class="fp-prodi-nama" data-prodi-nama></span>' +
                '<span class="fp-prodi-jenjang dm-badge tone-netral" data-prodi-jenjang style="margin-left:auto"></span>' +
                '<div class="fp-prodi-actions">' +
                    '<button type="button" class="fp-icon-btn fp-edit" title="Ubah prodi" onclick="fpBukaEditProdi(this)">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>' +
                    '</button>' +
                    '<button type="button" class="fp-icon-btn fp-hapus" title="Hapus prodi" onclick="fpHapusProdi(this)">' +
                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>' +
                    '</button>' +
                '</div>';
            row.querySelector('[data-prodi-nama]').textContent = nama;
            row.querySelector('[data-prodi-jenjang]').textContent = jenjang;
            list.appendChild(row);
            fakultasItem.classList.add('is-open');

            fpUpdateJumlahProdi(fakultasItem);
        }

        fpTutup('fpModalProdi');
        return false;
    }

    function fpHapusProdi(btn) {
        if (!confirm('Hapus program studi ini?')) return;
        dmRequest('/admin/data-master/prodi/'+btn.closest('.fp-prodi-row').dataset.prodiId,'DELETE').then(dmReload).catch(err=>alert(err.message)); return;
        var fakultasItem = btn.closest('.fp-item');
        var row = btn.closest('.fp-prodi-row');
        row.remove();

        var list = fakultasItem.querySelector('[data-prodi-list]');
        if (!list.querySelector('.fp-prodi-row')) {
            list.innerHTML = '<div class="fp-prodi-empty">Belum ada prodi.</div>';
        }
        fpUpdateJumlahProdi(fakultasItem);
    }

    function fpUpdateJumlahProdi(fakultasItem) {
        var jumlah = fakultasItem.querySelectorAll('.fp-prodi-row').length;
        fakultasItem.querySelector('[data-count]').textContent = jumlah + ' Prodi';
    }

    // ----- Kategori Masalah -----
    var kmIkonNonaktifkan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';
    var kmIkonAktifkan    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var kmBerikutnya = 1000; // id sementara untuk kategori baru (dummy, belum ke backend)

    function kmToggleStatus(btn) {
        var item  = btn.closest('.km-item');
        var badge = item.querySelector('[data-km-badge]');
        var aktifSekarang = item.dataset.status === 'Aktif';
        var statusBaru = aktifSekarang ? 'Nonaktif' : 'Aktif';
        dmRequest('/admin/data-master/kategori/'+item.dataset.kategoriId+'/toggle','POST').then(dmReload).catch(err=>alert(err.message)); return;

        item.dataset.status = statusBaru;
        badge.textContent   = statusBaru;
        badge.className     = 'dm-badge tone-' + (statusBaru === 'Aktif' ? 'hijau' : 'abu');

        btn.title       = statusBaru === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan';
        btn.className   = 'km-icon-btn tone-' + (statusBaru === 'Aktif' ? 'nonaktifkan' : 'aktifkan');
        btn.innerHTML   = statusBaru === 'Aktif' ? kmIkonNonaktifkan : kmIkonAktifkan;
    }

    function kmHapusKategori(btn) {
        if (!confirm('Hapus kategori masalah ini?')) return;
        dmRequest('/admin/data-master/kategori/'+btn.closest('.km-item').dataset.kategoriId,'DELETE').then(dmReload).catch(err=>alert(err.message)); return;
        var grid = document.getElementById('kmGrid');
        btn.closest('.km-item').remove();

        if (!grid.querySelector('.km-item')) {
            grid.innerHTML = '<div class="dm-empty km-empty">Belum ada kategori masalah.</div>';
        }
    }

    function kmBukaTambah() {
        document.getElementById('kmFormTambah').reset();
        fpBuka('kmModalTambah');
    }

    function kmSimpanKategori(e) {
        e.preventDefault();
        var nama = document.getElementById('kmInputNama').value.trim();
        if (!nama) return false;
        dmRequest('{{ route('admin.data-master.kategori.store') }}','POST',{nama}).then(dmReload).catch(err=>alert(err.message)); return false;

        var grid   = document.getElementById('kmGrid');
        var kosong = grid.querySelector('.km-empty');
        if (kosong) kosong.parentElement === grid ? kosong.remove() : null;

        var id = kmBerikutnya++;
        var item = document.createElement('div');
        item.className = 'km-item';
        item.dataset.kategoriId = id;
        item.dataset.status = 'Aktif';
        item.innerHTML =
            '<div class="km-info">' +
                '<span class="km-nama"></span>' +
                '<span class="dm-badge tone-hijau" data-km-badge>Aktif</span>' +
            '</div>' +
            '<div class="km-actions">' +
                '<button type="button" class="km-icon-btn tone-nonaktifkan" title="Nonaktifkan" onclick="kmToggleStatus(this)">' + kmIkonNonaktifkan + '</button>' +
                '<button type="button" class="km-icon-btn tone-hapus" title="Hapus kategori" onclick="kmHapusKategori(this)">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>' +
                '</button>' +
            '</div>';
        item.querySelector('.km-nama').textContent = nama;

        grid.appendChild(item);
        fpTutup('kmModalTambah');
        return false;
    }

    // ----- Lokasi & Ruang -----
    var lokIkonNonaktifkan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';
    var lokIkonAktifkan    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var lokBerikutnya  = 1000; // id sementara untuk lokasi baru (dummy, belum ke backend)
    var lokEditRow     = null; // row yang sedang diubah (null kalau mode tambah)

    function lokToggleStatus(btn) {
        var row   = btn.closest('tr');
        var badge = row.querySelector('[data-lok-badge]');
        var aktifSekarang = row.dataset.status === 'Aktif';
        var statusBaru = aktifSekarang ? 'Nonaktif' : 'Aktif';
        dmRequest('/admin/data-master/lokasi/'+btn.closest('tr').dataset.lokasiId+'/toggle','POST').then(dmReload).catch(err=>alert(err.message)); return;

        row.dataset.status = statusBaru;
        badge.textContent  = statusBaru;
        badge.className    = 'dm-badge tone-' + (statusBaru === 'Aktif' ? 'hijau' : 'abu');

        btn.className  = statusBaru === 'Aktif' ? 'dm-btn-nonaktifkan' : 'dm-btn-detail';
        btn.innerHTML  = (statusBaru === 'Aktif' ? lokIkonNonaktifkan + 'Nonaktifkan' : lokIkonAktifkan + 'Aktifkan');
    }

    function lokHapus(btn) {
        if (!confirm('Hapus lokasi ini?')) return;
        dmRequest('/admin/data-master/lokasi/'+btn.closest('tr').dataset.lokasiId,'DELETE').then(dmReload).catch(err=>alert(err.message)); return;
        var tbody = document.getElementById('lokBody');
        btn.closest('tr').remove();

        if (!tbody.querySelector('tr[data-lokasi-id]')) {
            tbody.innerHTML = '<tr><td colspan="4" class="dm-empty">Belum ada data lokasi.</td></tr>';
        }
    }

    function lokBukaTambah() {
        lokEditRow = null;
        document.getElementById('lokModalJudul').textContent = 'Tambah Lokasi';
        document.getElementById('lokForm').reset();
        fpBuka('lokModal');
    }

    function lokBukaEdit(btn) {
        var row = btn.closest('tr');
        lokEditRow = row;
        document.getElementById('lokModalJudul').textContent = 'Ubah Lokasi';
        document.getElementById('lokInputNama').value = row.querySelector('[data-lok-nama]').textContent;
        document.getElementById('lokInputDeskripsi').value = row.querySelector('[data-lok-deskripsi]').textContent;
        fpBuka('lokModal');
    }

    function lokSimpan(e) {
        e.preventDefault();
        var nama      = document.getElementById('lokInputNama').value.trim();
        var deskripsi = document.getElementById('lokInputDeskripsi').value.trim();
        if (!nama || !deskripsi) return false;
        var url = lokEditRow ? '/admin/data-master/lokasi/'+lokEditRow.dataset.lokasiId : '{{ route('admin.data-master.lokasi.store') }}';
        dmRequest(url, lokEditRow ? 'PUT' : 'POST', {nama,deskripsi}).then(dmReload).catch(err=>alert(err.message)); return false;

        if (lokEditRow) {
            // Mode ubah
            lokEditRow.querySelector('[data-lok-nama]').textContent = nama;
            lokEditRow.querySelector('[data-lok-deskripsi]').textContent = deskripsi;
        } else {
            // Mode tambah
            var tbody  = document.getElementById('lokBody');
            var kosong = tbody.querySelector('.dm-empty');
            if (kosong) kosong.closest('tr').remove();

            var id  = lokBerikutnya++;
            var row = document.createElement('tr');
            row.dataset.lokasiId = id;
            row.dataset.status   = 'Aktif';
            row.innerHTML =
                '<td class="dm-nama" data-lok-nama></td>' +
                '<td data-lok-deskripsi></td>' +
                '<td><span class="dm-badge tone-hijau" data-lok-badge>Aktif</span></td>' +
                '<td>' +
                    '<div class="dm-aksi-group">' +
                        '<button type="button" class="dm-btn-detail" onclick="lokBukaEdit(this)">' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>' +
                            'Edit' +
                        '</button>' +
                        '<button type="button" class="dm-btn-nonaktifkan" data-lok-toggle onclick="lokToggleStatus(this)">' + lokIkonNonaktifkan + 'Nonaktifkan</button>' +
                        '<button type="button" class="dm-btn-hapus-pill" onclick="lokHapus(this)">' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>' +
                            'Hapus' +
                        '</button>' +
                    '</div>' +
                '</td>';
            row.querySelector('[data-lok-nama]').textContent = nama;
            row.querySelector('[data-lok-deskripsi]').textContent = deskripsi;
            tbody.appendChild(row);
        }

        fpTutup('lokModal');
        return false;
    }

    // ----- Template Pesan -----
    // Catatan: "{{" dirakit lewat konkatenasi supaya tidak diproses sebagai sintaks Blade.
    var tpBuka = '{' + '{', tpTutup = '}' + '}';
    var tpAktifId = null; // id template yang sedang diedit

    function tpPilih(btn) {
        var id = btn.dataset.tp;
        document.querySelectorAll('.tp-item').forEach(function (b) {
            b.classList.toggle('is-active', b === btn);
        });
        document.querySelectorAll('.tp-pane').forEach(function (p) {
            p.classList.toggle('is-active', p.id === 'tp-pane-' + id);
        });
    }

    function tpEscape(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function tpHighlight(s) {
        return tpEscape(s).replace(/\{\{\s*(\w+)\s*\}\}/g, function (m, v) {
            return '<span class="tp-var">' + tpBuka + v + tpTutup + '</span>';
        });
    }

    function tpBukaEdit(id) {
        var pane = document.getElementById('tp-pane-' + id);
        if (!pane) return;
        tpAktifId = id;

        document.getElementById('tpModalJudul').textContent = 'Edit Template — ' + pane.dataset.nama;
        document.getElementById('tpInputIsi').value = pane.dataset.isi;

        var wadah = document.getElementById('tpModalChips');
        wadah.innerHTML = '';
        pane.querySelectorAll('.tp-chip').forEach(function (chip) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'tp-chip';
            b.textContent = chip.textContent;
            b.onclick = function () { tpSisipkan(chip.textContent); };
            wadah.appendChild(b);
        });

        fpBuka('tpModal');
    }

    function tpSisipkan(teks) {
        var ta = document.getElementById('tpInputIsi');
        var awal = ta.selectionStart, akhir = ta.selectionEnd;
        ta.value = ta.value.slice(0, awal) + teks + ta.value.slice(akhir);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = awal + teks.length;
    }

    function tpSimpan(e) {
        e.preventDefault();
        var isi = document.getElementById('tpInputIsi').value;
        if (!isi.trim()) return false;
        dmRequest('/admin/data-master/template/'+encodeURIComponent(tpAktifId),'PUT',{isi}).then(dmReload).catch(err=>alert(err.message)); return false;

        // Dummy: hanya memperbarui tampilan (belum tersimpan ke backend).
        var pane = document.getElementById('tp-pane-' + tpAktifId);
        pane.dataset.isi = isi;
        pane.querySelector('[data-tp-preview]').innerHTML = tpHighlight(isi);

        fpTutup('tpModal');
        return false;
    }

    // Klik di luar modal fakultas/prodi/kategori/lokasi/template untuk menutup
    ['fpModalFakultas', 'fpModalProdi', 'kmModalTambah', 'lokModal', 'tpModal'].forEach(function (id) {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) fpTutup(id);
        });
    });
</script>

@endsection
