{{--
    SILAKON — Konten Self Help (Admin)
    Simpan sebagai: resources/views/admin/self-help.blade.php

    Route yang dibutuhkan (lihat AdminPendaftaranController):
    Route::get('/admin/self-help',  [AdminPendaftaranController::class, 'selfHelp'])
        ->name('admin.self-help');
    Route::post('/admin/self-help', [AdminPendaftaranController::class, 'simpanSelfHelp'])
        ->name('admin.self-help.store');

    Jenis konten: Artikel, Video, Worksheet. Form "Tambah Konten" berubah sesuai tipe:
      - Artikel   : judul, isi, thumbnail (opsional)
      - Video     : judul, link YouTube, keterangan
      - Worksheet : judul, file PDF, daftar pertanyaan + halaman PDF
    Tanggal otomatis (hari ini). Metadata disimpan ke session (dummy database),
    file upload masuk ke storage/app/public (butuh `php artisan storage:link`).
    Tombol aksi berupa icon (Lihat, Edit, Hapus). "Lihat" sudah aktif membuka
    halaman publik /self-help/{id} di tab baru. Edit / Hapus masih href="#",
    tinggal disambungkan ke route CRUD-nya (mis. admin.self-help.update /
    admin.self-help.destroy).
--}}
@extends('layouts.admin')

@section('judul', 'Self Help Content')

@section('content')

<style>
    /* ===== Halaman Konten Self Help ===== */
    .sh-page{ padding-bottom:8px; }

    .sh-alert{
        display:flex;align-items:center;gap:10px;
        background:#e3f3e8;color:#2e7d4f;border:1px solid #cdeadb;
        padding:14px 18px;border-radius:12px;font-size:14px;font-weight:500;
        margin-bottom:22px;
    }

    .sh-head{
        display:flex;align-items:flex-start;justify-content:space-between;
        flex-wrap:wrap;gap:14px;margin-bottom:28px;
    }
    .sh-head h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .sh-head p{margin:6px 0 0;color:#6b7a72;font-size:13.5px;}

    .sh-btn-primary{
        display:inline-flex;align-items:center;gap:8px;
        background:#7fa285;color:#fff;border:none;
        padding:13px 22px;border-radius:12px;font-size:14px;font-weight:600;
        white-space:nowrap;cursor:pointer;font-family:inherit;
        transition:background .15s ease;
    }
    .sh-btn-primary:hover{background:#6d9174;}

    /* Kartu ringkasan per tipe */
    .sh-stats{
        display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;
    }
    @media (max-width:640px){ .sh-stats{grid-template-columns:1fr;} }
    .sh-stat{
        background:#fff;border:1px solid #e1e7e3;border-radius:16px;
        padding:20px 12px 18px;text-align:center;
        box-shadow:0 2px 8px rgba(31,42,36,.04);
    }
    .sh-stat svg{width:18px;height:18px;color:#1f2a24;margin-bottom:6px;}
    .sh-stat b{display:block;font-size:22px;font-weight:700;color:#1f2a24;line-height:1.3;}
    .sh-stat span{font-size:12.5px;color:#8a978f;}

    /* Tabel */
    .sh-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;overflow:hidden;
        box-shadow:0 2px 8px rgba(31,42,36,.04);
    }
    .sh-scroll{overflow-x:auto;}
    .sh-table{width:100%;border-collapse:collapse;font-size:13.5px;color:#4b5a51;}
    .sh-table thead th{
        background:#f4f7f4;text-align:left;
        font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
        color:#5c6a62;padding:18px 12px;white-space:nowrap;
    }
    .sh-table thead th:first-child,
    .sh-table tbody td:first-child{padding-left:20px;}
    .sh-table thead th:last-child,
    .sh-table tbody td:last-child{padding-right:20px;}
    .sh-table tbody td{
        padding:18px 12px;border-top:1px solid #eef1ee;vertical-align:middle;
    }
    .sh-table tbody tr:first-child td{border-top:none;}
    .sh-table tbody tr:hover{background:#fafbfa;}
    .sh-judul{color:#1f2a24;font-weight:700;min-width:190px;line-height:1.45;}
    .sh-nowrap{white-space:nowrap;}

    .sh-badge{
        display:inline-block;font-size:12px;font-weight:600;white-space:nowrap;
        padding:5px 12px;border-radius:999px;
    }
    .sh-badge.tone-biru{background:#dfeafc;color:#2456a8;}
    .sh-badge.tone-merah{background:#fde0e0;color:#a72b2b;}
    .sh-badge.tone-kuning{background:#fdf1c2;color:#8a6100;}
    .sh-badge.tone-hijau{background:#d9f5e3;color:#1f7a44;}

    .sh-actions{display:flex;align-items:center;gap:6px;}
    .sh-icon-btn{
        display:inline-flex;align-items:center;justify-content:center;
        width:32px;height:32px;border-radius:8px;flex-shrink:0;
        border:1px solid transparent;
        text-decoration:none;cursor:pointer;transition:background .15s ease,border-color .15s ease,transform .1s ease;
    }
    .sh-icon-btn svg{width:16px;height:16px;}
    .sh-icon-btn:active{transform:scale(.93);}

    .sh-icon-btn-lihat{background:#e3f0fb;border-color:#c7e0f7;color:#2456a8;}
    .sh-icon-btn-lihat:hover{background:#d3e6f9;border-color:#a9d0f2;}

    .sh-icon-btn-edit{background:#e9f4ec;border-color:#cfe8d6;color:#2e7d4f;}
    .sh-icon-btn-edit:hover{background:#d9f5e3;border-color:#b9e6c9;}

    .sh-icon-btn-danger{background:#fdeeed;border-color:#f6cfcc;color:#c0392b;}
    .sh-icon-btn-danger:hover{background:#fadedb;border-color:#f2b8b3;}

    /* ===== Modal Tambah Konten ===== */
    .sh-modal-overlay{
        display:none;position:fixed;inset:0;background:rgba(20,28,24,.45);
        align-items:center;justify-content:center;z-index:1000;padding:20px;
    }
    .sh-modal-overlay.is-open{display:flex;}
    .sh-modal{
        background:#fff;border-radius:16px;width:100%;max-width:560px;
        max-height:90vh;overflow-y:auto;padding:26px 26px 24px;
        box-shadow:0 20px 50px rgba(0,0,0,.2);
    }
    .sh-modal-head{
        display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;
    }
    .sh-modal-head h3{margin:0;font-size:17px;font-weight:700;color:#1f2a24;}
    .sh-modal-close{
        border:none;background:#f1f3f1;color:#5c6a62;width:30px;height:30px;
        border-radius:8px;font-size:18px;line-height:1;cursor:pointer;
    }
    .sh-modal-close:hover{background:#e5e8e5;}

    .sh-form-group{margin-bottom:16px;display:flex;flex-direction:column;gap:6px;}
    .sh-form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media (max-width:480px){ .sh-form-row{grid-template-columns:1fr;} }
    .sh-form-group label{font-size:13px;font-weight:600;color:#3a463f;}
    .sh-form-group input,
    .sh-form-group select{
        border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;
        font-size:13.5px;color:#1f2a24;outline:none;font-family:inherit;background:#fff;
    }
    .sh-form-group input:focus,
    .sh-form-group select:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}
    .sh-form-error{font-size:12px;color:#c0392b;margin:0;}

    .sh-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;}
    .sh-modal-actions button{
        padding:10px 18px;border-radius:9px;font-size:13.5px;font-weight:600;
        cursor:pointer;border:none;font-family:inherit;
    }
    .sh-modal-actions .sh-btn-outline{background:#fff;border:1px solid #d8dfda;color:#3a463f;}
    .sh-modal-actions .sh-btn-fill{background:#4d7c5f;color:#fff;}
    .sh-modal-actions .sh-btn-fill:hover{background:#3f6a4f;}
    .sh-form-group textarea{
        border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;
        font-size:13.5px;color:#1f2a24;outline:none;font-family:inherit;
        resize:vertical;line-height:1.55;
    }
    .sh-form-group textarea:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}
    .sh-form-group input[type="file"]{padding:8px 10px;font-size:13px;background:#fafbfa;}
    .sh-hint{font-size:12px;color:#8a978f;margin:0;}
    .sh-opt{font-weight:400;color:#8a978f;}

    /* Daftar pertanyaan worksheet */
    .sh-q-list{display:flex;flex-direction:column;gap:10px;}
    .sh-q-row{
        display:grid;grid-template-columns:26px 1fr 84px 28px;gap:10px;align-items:start;
        background:#f7f9f7;border:1px solid #e6ebe7;border-radius:11px;padding:12px;
    }
    .sh-q-no{
        width:26px;height:26px;border-radius:50%;background:#dff5e7;color:#1f7a44;
        font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:5px;
    }
    .sh-q-row textarea,
    .sh-q-row input{width:100%;box-sizing:border-box;background:#fff;}
    .sh-q-col{display:flex;flex-direction:column;gap:4px;min-width:0;}
    .sh-q-col small{font-size:11px;color:#8a978f;}
    .sh-q-del{
        border:none;background:#fdeeed;color:#c0392b;width:28px;height:28px;border-radius:8px;
        font-size:16px;line-height:1;cursor:pointer;margin-top:3px;
    }
    .sh-q-del:hover{background:#fadedb;}
    .sh-q-add{
        margin-top:10px;background:#fff;border:1px dashed #9db8a5;color:#3f6a4f;
        padding:9px 14px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;
        font-family:inherit;width:100%;
    }
    .sh-q-add:hover{background:#eef5f0;}
    @media (max-width:480px){
        .sh-q-row{grid-template-columns:26px 1fr 28px;}
        .sh-q-row .sh-q-col.hal{grid-column:2 / 3;}
    }

    /* ===== Modal: lebar otomatis untuk jenis Modul + tombol simpan menempel di bawah ===== */
    .sh-modal{transition:max-width .2s ease;}
    .sh-modal.is-wide{max-width:820px;}
    .sh-modal-actions{
        position:sticky;bottom:0;z-index:3;background:#fff;
        margin:22px -26px -24px;padding:14px 26px;border-top:1px solid #eef1ee;
    }

    /* ===== Builder langkah modul ===== */
    .sh-blk-wrap{display:flex;flex-direction:column;gap:12px;}
    .sh-blk-empty{
        border:1px dashed #c9d8cd;border-radius:12px;padding:26px 16px;text-align:center;
        color:#8a978f;font-size:13px;background:#fafcfa;
    }
    .sh-blk{
        background:#fff;border:1px solid #dfe7e1;border-radius:14px;overflow:hidden;
        transition:box-shadow .15s ease,border-color .15s ease,opacity .15s ease;
    }
    .sh-blk.is-dragging{opacity:.4;}
    .sh-blk.drop-before{box-shadow:0 -3px 0 #7fa285;}
    .sh-blk.drop-after{box-shadow:0 3px 0 #7fa285;}
    .sh-blk.is-invalid{border-color:#e4a29b;box-shadow:0 0 0 3px rgba(192,57,43,.08);}

    .sh-blk-head{
        display:flex;align-items:center;gap:10px;padding:10px 12px;
        background:#f7f9f7;cursor:pointer;user-select:none;
    }
    .sh-blk-handle{
        display:flex;padding:5px 3px;border-radius:6px;color:#a3afa8;cursor:grab;flex-shrink:0;
    }
    .sh-blk-handle:hover{background:#e9eee9;color:#5c6a62;}
    .sh-blk-handle:active{cursor:grabbing;}
    .sh-blk-handle svg{width:14px;height:14px;}
    .sh-blk-no{
        width:24px;height:24px;border-radius:50%;background:#fff;border:1px solid #d5e0d8;color:#4b5a51;
        font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .sh-blk-type{
        display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
        padding:4px 10px 4px 8px;border-radius:999px;white-space:nowrap;flex-shrink:0;
    }
    .sh-blk-type svg{width:13px;height:13px;}
    .sh-blk-sum{
        flex:1;min-width:0;font-size:13px;color:#1f2a24;font-weight:500;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .sh-blk-sum.is-empty{color:#a3afa8;font-weight:400;font-style:italic;}
    .sh-blk-tools{display:flex;align-items:center;gap:2px;flex-shrink:0;}
    .sh-blk-tool{
        width:28px;height:28px;border:none;background:transparent;border-radius:7px;color:#6b7a72;
        cursor:pointer;display:inline-flex;align-items:center;justify-content:center;
    }
    .sh-blk-tool svg{width:15px;height:15px;}
    .sh-blk-tool:hover{background:#e6ece7;color:#1f2a24;}
    .sh-blk-tool.is-del:hover{background:#fdeeed;color:#c0392b;}
    .sh-blk-tool:disabled{opacity:.3;cursor:default;background:transparent;color:#6b7a72;}
    .sh-blk-chev{transition:transform .15s ease;}
    .sh-blk.is-collapsed .sh-blk-chev{transform:rotate(-90deg);}

    .sh-blk-body{display:flex;flex-direction:column;gap:14px;padding:16px;border-top:1px solid #eef1ee;}
    .sh-blk.is-collapsed .sh-blk-body{display:none;}
    .sh-blk-field{display:flex;flex-direction:column;gap:6px;}
    .sh-blk-field label{font-size:12.5px;font-weight:600;color:#3a463f;}
    .sh-blk .sh-in{
        width:100%;box-sizing:border-box;border:1px solid #d8dfda;border-radius:9px;padding:10px 12px;
        font-size:13.5px;line-height:1.55;color:#1f2a24;font-family:inherit;background:#fff;outline:none;
    }
    .sh-blk textarea.sh-in{resize:vertical;min-height:110px;}
    .sh-blk .sh-in:focus{border-color:#4d7c5f;box-shadow:0 0 0 3px rgba(77,124,95,.12);}
    .sh-blk-note{margin:0;font-size:12px;color:#8a978f;}
    .sh-blk-note.ok{color:#1f7a44;}
    .sh-blk-note.bad{color:#c0392b;}
    .sh-blk-video{
        display:flex;align-items:center;gap:12px;background:#f7f9f7;
        border:1px solid #e6ebe7;border-radius:10px;padding:8px;
    }
    .sh-blk-video[hidden]{display:none;}
    .sh-blk-thumb{width:136px;aspect-ratio:16/9;object-fit:cover;border-radius:7px;background:#dfe5e0;flex-shrink:0;}
    .sh-blk-video span{font-size:12.5px;color:#5c6a62;line-height:1.5;}

    /* Pemilih jenis langkah */
    .sh-add-bar{margin-top:14px;border:1px dashed #b7cbbc;border-radius:12px;padding:14px;background:#f8fbf8;}
    .sh-add-bar p{margin:0 0 10px;font-size:12.5px;font-weight:600;color:#3f6a4f;}
    .sh-add-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
    @media (max-width:700px){ .sh-add-grid{grid-template-columns:1fr 1fr;} }
    .sh-add-btn{
        display:flex;align-items:center;gap:10px;padding:10px;text-align:left;
        background:#fff;border:1px solid #d8dfda;border-radius:10px;cursor:pointer;font-family:inherit;
        transition:border-color .15s ease,background .15s ease,transform .1s ease;
    }
    .sh-add-btn:hover{border-color:#7fa285;background:#f1f7f2;}
    .sh-add-btn:active{transform:scale(.98);}
    .sh-add-ic{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .sh-add-ic svg{width:16px;height:16px;}
    .sh-add-txt{display:flex;flex-direction:column;min-width:0;}
    .sh-add-txt b{font-size:13px;color:#1f2a24;font-weight:600;line-height:1.3;}
    .sh-add-txt small{font-size:11.5px;color:#8a978f;line-height:1.35;}
    @media (max-width:480px){
        .sh-blk-thumb{width:96px;}
        .sh-blk-type span.sh-blk-type-label{display:none;}
    }
</style>

<div class="sh-page">

    @if(session('sukses'))
        <div class="sh-alert">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
            </svg>
            {{ session('sukses') }}
        </div>
    @endif

    {{-- Judul + tombol tambah --}}
    <div class="sh-head">
        <div>
            <h1>Konten Self Help</h1>
            <p>{{ $totalData }} konten tersedia</p>
        </div>
        <button type="button" class="sh-btn-primary" onclick="shOpenModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Konten
        </button>
    </div>

    {{-- Ringkasan per tipe --}}
    <div class="sh-stats">
        @foreach($statistik as $s)
            <div class="sh-stat">
                @if($s['tipe'] === 'Artikel')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                    </svg>
                @elseif($s['tipe'] === 'Video')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 3 14 9-14 9z"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>
                    </svg>
                @endif
                <b>{{ $s['nilai'] }}</b>
                <span>{{ $s['tipe'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Tabel konten --}}
    <div class="sh-card">
        <div class="sh-scroll">
            <table class="sh-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Jenis</th>
                        <th>Topik</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konten as $k)
                        <tr>
                            <td class="sh-judul">{{ $k['judul'] }}</td>
                            <td><span class="sh-badge tone-{{ $k['tipe_tone'] }}">{{ $k['tipe'] }}</span></td>
                            <td>{{ $k['topik'] }}</td>
                            <td class="sh-nowrap">{{ $k['tanggal'] }}</td>
                            <td><span class="sh-badge tone-{{ $k['status_tone'] }}">{{ $k['status'] }}</span></td>
                            <td>
                                <div class="sh-actions">
                                    <a href="{{ url('/self-help/' . $k['id']) }}" target="_blank" class="sh-icon-btn sh-icon-btn-lihat" title="Lihat konten" aria-label="Lihat konten">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <button type="button" class="sh-icon-btn sh-icon-btn-edit sh-edit-konten" title="Edit konten" aria-label="Edit konten" data-source="sh-data-{{ $k['id'] }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.self-help.destroy', $k['id']) }}" class="sh-inline-delete" onsubmit="return confirm('Yakin ingin menghapus konten ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sh-icon-btn sh-icon-btn-danger" title="Hapus konten" aria-label="Hapus konten">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <script type="application/json" id="sh-data-{{ $k['id'] }}">@json($k)</script>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:#8a978f;padding:36px 12px;">
                                Belum ada konten. Klik "Tambah Konten" untuk membuat yang pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============ MODAL: TAMBAH KONTEN ============ --}}
@php
    $baris = array_values(old('pertanyaan', [['teks' => '', 'halaman' => '']]));
@endphp
<div class="sh-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="shModalTambah">
    <div class="sh-modal">
        <div class="sh-modal-head">
        <h3 id="shModalTitle">Tambah Konten Self Help</h3>
            <button type="button" class="sh-modal-close" onclick="shCloseModal()">&times;</button>
        </div>

        <form id="shFormKonten" method="POST" action="{{ route('admin.self-help.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="shFormMethod" value="POST">

            <div class="sh-form-group">
                <label>Judul</label>
                <input type="text" name="judul" value="{{ old('judul') }}" placeholder="mis. Mengelola Stres Saat Skripsi" required>
                @error('judul') <p class="sh-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="sh-form-row">
                <div class="sh-form-group">
                    <label>Jenis</label>
                    <select name="tipe" id="shTipe" onchange="shApplyTipe()" required>
                        @foreach($opsiTipe as $t)
                            <option value="{{ $t }}" @selected(old('tipe') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('tipe') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="sh-form-group">
                    <label>Topik</label>
                    <select name="topik" required>
                        @foreach($opsiTopik as $t)
                            <option value="{{ $t }}" @selected(old('topik') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('topik') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ===== ARTIKEL ===== --}}
            <div class="sh-tipe-section" data-tipe="Artikel">
                <div class="sh-form-group">
                    <label>Isi Artikel</label>
                    <textarea name="isi" rows="10" style="min-height:200px;" placeholder="Tulis isi artikel di sini..." required>{{ old('isi') }}</textarea>
                    @error('isi') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="sh-form-group">
                    <label>Thumbnail <span class="sh-opt">(opsional)</span></label>
                    <input type="file" name="thumbnail" accept="image/*">
                    <p class="sh-hint">Format gambar (JPG/PNG), maksimal 2 MB.</p>
                    @error('thumbnail') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ===== VIDEO ===== --}}
            <div class="sh-tipe-section" data-tipe="Video" style="display:none;">
                <div class="sh-form-group">
                    <label>Link YouTube</label>
                    <input type="url" name="link_youtube" value="{{ old('link_youtube') }}" placeholder="https://www.youtube.com/watch?v=..." required>
                    @error('link_youtube') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
                <div class="sh-form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="5" placeholder="Uraikan singkat isi video ini..." required>{{ old('keterangan') }}</textarea>
                    @error('keterangan') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ===== WORKSHEET ===== --}}
            <div class="sh-tipe-section" data-tipe="Worksheet" style="display:none;">
                <div class="sh-form-group">
                    <label>File PDF</label>
                    <input type="file" name="file_pdf" id="shFilePdf" accept="application/pdf" required>
                    <input type="hidden" name="jumlah_halaman" id="shJumlahHalaman" value="{{ old('jumlah_halaman') }}">
                    <p class="sh-hint" id="shPdfInfo">Format PDF, maksimal 10 MB. Setelah dipilih, jumlah halaman akan dibaca otomatis.</p>
                    @error('file_pdf') <p class="sh-form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sh-form-group">
                    <label>Pertanyaan</label>
                    <p class="sh-hint">Isi tiap pertanyaan dan tentukan ada di halaman berapa pada PDF.</p>
                    @error('pertanyaan') <p class="sh-form-error">{{ $message }}</p> @enderror

                    <div class="sh-q-list" id="shPertanyaanList">
                        @foreach($baris as $i => $q)
                            <div class="sh-q-row">
                                <div class="sh-q-no">{{ $i + 1 }}</div>
                                <div class="sh-q-col">
                                    <textarea data-f="teks" name="pertanyaan[{{ $i }}][teks]" rows="2" placeholder="Tulis pertanyaan..." required>{{ $q['teks'] ?? '' }}</textarea>
                                    @error("pertanyaan.$i.teks") <p class="sh-form-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="sh-q-col hal">
                                    <input type="number" data-f="halaman" name="pertanyaan[{{ $i }}][halaman]" min="1" value="{{ $q['halaman'] ?? '' }}" placeholder="Hlm." required>
                                    <small>Halaman PDF</small>
                                    @error("pertanyaan.$i.halaman") <p class="sh-form-error">{{ $message }}</p> @enderror
                                </div>
                                <button type="button" class="sh-q-del" onclick="shHapusPertanyaan(this)" title="Hapus pertanyaan">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="sh-q-add" onclick="shTambahPertanyaan()">+ Tambah Pertanyaan</button>
                </div>
            </div>

            {{-- ===== MODUL TERPADU ===== --}}
            <div class="sh-tipe-section" data-tipe="Modul" style="display:none;">
                <div class="sh-form-group">
                    <label>Isi modul</label>
                    <p class="sh-hint">Susun materi langkah demi langkah. Konseli melihatnya berurutan dari atas ke bawah. Seret ikon titik di kiri atau pakai tombol panah untuk mengubah urutan.</p>
                    <input type="hidden" name="blok_json" id="shBlokJson" value="{{ old('blok_json', '[]') }}">
                    <div class="sh-blk-wrap" id="shBlokList"></div>
                    <div class="sh-blk-empty" id="shBlokEmpty">Belum ada langkah. Pilih jenis langkah di bawah untuk memulai.</div>
                    <p class="sh-form-error" id="shBlokError" style="display:none;"></p>
                    @error('blok_json') <p class="sh-form-error">{{ $message }}</p> @enderror
                    <div class="sh-add-bar">
                        <p>Tambah langkah</p>
                        <div class="sh-add-grid" id="shAddGrid"></div>
                    </div>
                </div>
            </div>

            <div class="sh-form-group">
                <label>Status</label>
                <select name="status" required>
                    @foreach(['Dipublikasi', 'Draft'] as $st)
                        <option value="{{ $st }}" @selected(old('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
                <p class="sh-hint">Tanggal terisi otomatis sesuai hari konten ini ditambahkan.</p>
                @error('status') <p class="sh-form-error">{{ $message }}</p> @enderror
            </div>

            <div class="sh-modal-actions">
                <button type="button" class="sh-btn-outline" onclick="shCloseModal()">Batal</button>
                <button type="submit" class="sh-btn-fill" id="shSubmitLabel">Simpan Konten</button>
            </div>
        </form>
    </div>
</div>

{{-- Template baris pertanyaan baru (tidak ikut terkirim karena berada di dalam <template>) --}}
<template id="shTplPertanyaan">
    <div class="sh-q-row">
        <div class="sh-q-no"></div>
        <div class="sh-q-col">
            <textarea data-f="teks" rows="2" placeholder="Tulis pertanyaan..." required></textarea>
        </div>
        <div class="sh-q-col hal">
            <input type="number" data-f="halaman" min="1" placeholder="Hlm." required>
            <small>Halaman PDF</small>
        </div>
        <button type="button" class="sh-q-del" onclick="shHapusPertanyaan(this)" title="Hapus pertanyaan">&times;</button>
    </div>
</template>

<script>
    var shEditing = false;
    var shForm = document.getElementById('shFormKonten');
    var shStoreAction = shForm.getAttribute('action');

    function shOpenModal(){
        shEditing = false;
        shForm.reset();
        shForm.setAttribute('action', shStoreAction);
        document.getElementById('shFormMethod').value = 'POST';
        document.getElementById('shModalTitle').textContent = 'Tambah Konten Self Help';
        document.getElementById('shSubmitLabel').textContent = 'Simpan Konten';
        document.getElementById('shJumlahHalaman').value = '';
        document.getElementById('shPdfInfo').textContent = 'Format PDF, maksimal 10 MB. Setelah dipilih, jumlah halaman akan dibaca otomatis.';
        if (window.shMuatBlok) window.shMuatBlok([]);
        shApplyTipe();
        document.getElementById('shModalTambah').classList.add('is-open');
    }
    function shCloseModal(){
        document.getElementById('shModalTambah').classList.remove('is-open');
    }
    // Tutup modal jika area gelap di luar kotak form diklik
    document.getElementById('shModalTambah').addEventListener('click', function (e) {
        if (e.target === this) shCloseModal();
    });

    // Tampilkan hanya isian sesuai tipe. Isian tipe lain di-disable supaya
    // tidak ikut divalidasi browser dan tidak terkirim ke server.
    function shApplyTipe(){
        var tipe = document.getElementById('shTipe').value;
        document.querySelectorAll('.sh-tipe-section').forEach(function (sec) {
            var aktif = sec.getAttribute('data-tipe') === tipe;
            sec.style.display = aktif ? '' : 'none';
            sec.querySelectorAll('input, textarea, select').forEach(function (el) {
                el.disabled = !aktif;
            });
        });
        var box = document.querySelector('#shModalTambah .sh-modal');
        if (box) box.classList.toggle('is-wide', tipe === 'Modul');
        var pdf = document.querySelector('input[name="file_pdf"]');
        if (pdf) pdf.required = !shEditing && tipe === 'Worksheet';
    }

    // Pertanyaan worksheet: nomor & nama field diurutkan ulang tiap tambah/hapus.
    var shList = document.getElementById('shPertanyaanList');

    function shRenumber(){
        var rows = shList.querySelectorAll('.sh-q-row');
        rows.forEach(function (row, i) {
            row.querySelector('.sh-q-no').textContent = i + 1;
            row.querySelector('[data-f="teks"]').name = 'pertanyaan[' + i + '][teks]';
            row.querySelector('[data-f="halaman"]').name = 'pertanyaan[' + i + '][halaman]';
            row.querySelector('.sh-q-del').style.visibility = rows.length > 1 ? 'visible' : 'hidden';
        });
    }
    function shTambahPertanyaan(){
        shList.insertAdjacentHTML('beforeend', document.getElementById('shTplPertanyaan').innerHTML);
        shRenumber();
        var rows = shList.querySelectorAll('.sh-q-row');
        rows[rows.length - 1].querySelector('textarea').focus();
    }
    function shHapusPertanyaan(btn){
        btn.closest('.sh-q-row').remove();
        shRenumber();
    }

    shApplyTipe();
    shRenumber();

    // Membaca jumlah halaman PDF di browser agar admin dapat mengisi halaman
    // pertanyaan dengan tepat sebelum materi disimpan.
    (function () {
        var input = document.getElementById('shFilePdf');
        var info = document.getElementById('shPdfInfo');
        var total = document.getElementById('shJumlahHalaman');
        if (!input) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            info.textContent = 'Membaca PDF...';
            if (!window.pdfjsLib) { info.textContent = 'PDF dipilih: ' + file.name + '. Jumlah halaman akan disimpan saat materi dikirim.'; return; }
            var reader = new FileReader();
            reader.onload = function () {
                window.pdfjsLib.getDocument({data: new Uint8Array(reader.result)}).promise.then(function (pdf) {
                    total.value = pdf.numPages;
                    info.textContent = 'PDF siap disimpan: ' + file.name + ' (' + pdf.numPages + ' halaman).';
                    document.querySelectorAll('#shPertanyaanList input[data-f="halaman"]').forEach(function (el) { el.max = pdf.numPages; });
                }).catch(function () { info.textContent = 'PDF dipilih, tetapi jumlah halaman tidak dapat dibaca otomatis.'; });
            };
            reader.readAsArrayBuffer(file);
        });
    }());

    // Isi modal dari data database saat tombol Edit ditekan.
    document.querySelectorAll('.sh-edit-konten').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var node = document.getElementById(btn.dataset.source);
            if (!node) return;
            var data = {};
            try { data = JSON.parse(node.textContent || '{}'); } catch (e) { return; }
            shEditing = true;
            shForm.setAttribute('action', '{{ url('/admin/self-help') }}/' + data.id);
            document.getElementById('shFormMethod').value = 'PUT';
            document.getElementById('shModalTitle').textContent = 'Edit Konten Self Help';
            document.getElementById('shSubmitLabel').textContent = 'Simpan Perubahan';
            shForm.querySelector('[name="judul"]').value = data.judul || '';
            document.getElementById('shTipe').value = data.tipe || 'Artikel';
            shForm.querySelector('[name="topik"]').value = data.topik || '';
            shForm.querySelector('[name="status"]').value = data.status || 'Draft';
            shForm.querySelector('[name="isi"]').value = data.isi || '';
            shForm.querySelector('[name="link_youtube"]').value = data.link_youtube || '';
            shForm.querySelector('[name="keterangan"]').value = data.keterangan || '';
            if (window.shMuatBlok) window.shMuatBlok(data.blok || []);
            document.getElementById('shJumlahHalaman').value = data.jumlah_halaman || '';
            document.getElementById('shPdfInfo').textContent = data.jumlah_halaman
                ? 'PDF tersimpan sebelumnya: ' + data.jumlah_halaman + ' halaman. Pilih file baru jika ingin menggantinya.'
                : 'Kosongkan jika tidak ingin mengganti PDF.';

            shList.innerHTML = '';
            var qs = Array.isArray(data.pertanyaan) ? data.pertanyaan : [];
            if (!qs.length) qs = [{teks:'', halaman:''}];
            qs.forEach(function (q) {
                shList.insertAdjacentHTML('beforeend', document.getElementById('shTplPertanyaan').innerHTML);
                var row = shList.lastElementChild;
                row.querySelector('[data-f="teks"]').value = q.teks || '';
                row.querySelector('[data-f="halaman"]').value = q.halaman || '';
            });
            shRenumber();
            shApplyTipe();
            document.getElementById('shModalTambah').classList.add('is-open');
        });
    });
</script>

<script>
(function () {
    var list = document.getElementById('shBlokList'),
        hidden = document.getElementById('shBlokJson'),
        form = document.getElementById('shFormKonten'),
        empty = document.getElementById('shBlokEmpty'),
        errBox = document.getElementById('shBlokError'),
        grid = document.getElementById('shAddGrid');
    if (!list || !hidden || !form) return;

    function svg(path, fill) {
        return '<svg viewBox="0 0 24 24" fill="' + (fill ? 'currentColor' : 'none') + '" stroke="' + (fill ? 'none' : 'currentColor') +
               '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + path + '</svg>';
    }
    var ICON = {
        grip: svg('<circle cx="9" cy="6" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="18" r="1.4"/>', true),
        up: svg('<path d="M12 19V5M5 12l7-7 7 7"/>'),
        down: svg('<path d="M12 5v14M19 12l-7 7-7-7"/>'),
        del: svg('<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/>'),
        chev: svg('<path d="m6 9 6 6 6-6"/>')
    };

    // Format data (blok_json) sama seperti sebelumnya: {tipe, judul, isi|url|pertanyaan|instruksi}
    var TYPES = {
        teks: {
            label: 'Teks', desc: 'Penjelasan atau artikel', key: 'isi', bg: '#e3f3e8', fg: '#1f7a44',
            icon: svg('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>'),
            judulPh: 'mis. Mengenali pemicu stres',
            bodyLabel: 'Isi materi', bodyPh: 'Tulis penjelasan di sini. Pisahkan paragraf dengan satu baris kosong.',
            note: ''
        },
        video: {
            label: 'Video', desc: 'Diputar langsung dari YouTube', key: 'url', bg: '#dfeafc', fg: '#2456a8',
            icon: svg('<path d="m6 3 14 9-14 9z"/>'),
            judulPh: 'mis. Latihan pernapasan 5 menit',
            bodyLabel: 'Link YouTube', bodyPh: 'https://www.youtube.com/watch?v=...'
        },
        soal: {
            label: 'Pertanyaan', desc: 'Konseli menjawab sendiri', key: 'pertanyaan', bg: '#fdf1c2', fg: '#8a6100',
            icon: svg('<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>'),
            judulPh: 'mis. Refleksi diri',
            bodyLabel: 'Pertanyaan untuk konseli', bodyPh: 'mis. Apa yang kamu rasakan saat situasi itu terjadi?',
            note: 'Konseli menjawab lewat kolom isian di bawah pertanyaan ini.'
        },
        kegiatan: {
            label: 'Kegiatan', desc: 'Tugas atau latihan', key: 'instruksi', bg: '#ece6fa', fg: '#5b3fa0',
            icon: svg('<path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'),
            judulPh: 'mis. Latihan menulis jurnal',
            bodyLabel: 'Instruksi kegiatan', bodyPh: 'mis. Tarik napas 4 hitungan, tahan 4 hitungan, hembuskan 6 hitungan. Ulangi selama 3 menit.',
            note: 'Tulis langkah-langkahnya sejelas mungkin supaya mudah diikuti sendiri.'
        }
    };
    var ORDER = ['teks', 'video', 'soal', 'kegiatan'];

    function ytId(u) {
        var m = String(u || '').trim().match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/);
        return m ? m[1] : '';
    }

    function sync() {
        var out = [];
        list.querySelectorAll('.sh-blk').forEach(function (row) {
            var tipe = row.dataset.tipe, b = { tipe: tipe, judul: row.querySelector('[data-f="judul"]').value };
            b[TYPES[tipe].key] = row.querySelector('[data-f="body"]').value;
            out.push(b);
        });
        hidden.value = JSON.stringify(out);
    }

    function showError(msg) { errBox.textContent = msg; errBox.style.display = msg ? '' : 'none'; }

    function renumber() {
        var rows = list.querySelectorAll('.sh-blk');
        rows.forEach(function (row, i) {
            row.querySelector('[data-no]').textContent = i + 1;
            row.querySelector('[data-up]').disabled = (i === 0);
            row.querySelector('[data-down]').disabled = (i === rows.length - 1);
        });
        empty.style.display = rows.length ? 'none' : '';
    }

    function updateSum(row) {
        var judul = row.querySelector('[data-f="judul"]').value.trim();
        var body = row.querySelector('[data-f="body"]').value.replace(/\s+/g, ' ').trim();
        var sum = row.querySelector('[data-sum]');
        var text = judul || body;
        if (text.length > 90) text = text.slice(0, 90) + '...';
        sum.textContent = text || 'Belum diisi';
        sum.classList.toggle('is-empty', !text);
    }

    function updateVideo(row) {
        var input = row.querySelector('[data-f="body"]');
        var box = row.querySelector('[data-video]');
        var note = row.querySelector('[data-vnote]');
        if (!box) return;
        var v = input.value.trim(), id = ytId(v);
        if (id) {
            box.hidden = false;
            box.querySelector('img').src = 'https://i.ytimg.com/vi/' + id + '/mqdefault.jpg';
            note.textContent = 'Link YouTube dikenali. Video akan diputar langsung di halaman konseli.';
            note.className = 'sh-blk-note ok';
        } else {
            box.hidden = true;
            note.textContent = v ? 'Link belum dikenali sebagai video YouTube.' : 'Tempel link video dari YouTube (watch, youtu.be, atau shorts).';
            note.className = 'sh-blk-note' + (v ? ' bad' : '');
        }
    }

    function build(tipe) {
        var t = TYPES[tipe];
        var row = document.createElement('div');
        row.className = 'sh-blk';
        row.dataset.tipe = tipe;

        var bodyField;
        if (tipe === 'video') {
            bodyField =
                '<div class="sh-blk-field"><label>' + t.bodyLabel + '</label>' +
                '<input class="sh-in" data-f="body" type="text" inputmode="url" autocomplete="off" placeholder="' + t.bodyPh + '">' +
                '<p class="sh-blk-note" data-vnote></p></div>' +
                '<div class="sh-blk-video" data-video hidden><img alt="" class="sh-blk-thumb"><span>Pratinjau video. Pastikan video ini boleh disematkan.</span></div>';
        } else {
            bodyField =
                '<div class="sh-blk-field"><label>' + t.bodyLabel + '</label>' +
                '<textarea class="sh-in" data-f="body" rows="5" placeholder="' + t.bodyPh + '"></textarea>' +
                (t.note ? '<p class="sh-blk-note">' + t.note + '</p>' : '') + '</div>';
        }

        row.innerHTML =
            '<div class="sh-blk-head" data-head>' +
                '<span class="sh-blk-handle" data-handle title="Seret untuk mengubah urutan">' + ICON.grip + '</span>' +
                '<span class="sh-blk-no" data-no></span>' +
                '<span class="sh-blk-type" style="background:' + t.bg + ';color:' + t.fg + ';">' + t.icon + '<span class="sh-blk-type-label">' + t.label + '</span></span>' +
                '<span class="sh-blk-sum" data-sum></span>' +
                '<span class="sh-blk-tools">' +
                    '<button type="button" class="sh-blk-tool" data-up data-tool title="Naikkan" aria-label="Naikkan langkah">' + ICON.up + '</button>' +
                    '<button type="button" class="sh-blk-tool" data-down data-tool title="Turunkan" aria-label="Turunkan langkah">' + ICON.down + '</button>' +
                    '<button type="button" class="sh-blk-tool is-del" data-del data-tool title="Hapus langkah" aria-label="Hapus langkah">' + ICON.del + '</button>' +
                    '<span class="sh-blk-tool sh-blk-chev" aria-hidden="true">' + ICON.chev + '</span>' +
                '</span>' +
            '</div>' +
            '<div class="sh-blk-body">' +
                '<div class="sh-blk-field"><label>Judul langkah <span class="sh-opt">(opsional)</span></label>' +
                '<input class="sh-in" data-f="judul" type="text" autocomplete="off" placeholder="' + t.judulPh + '"></div>' +
                bodyField +
            '</div>';
        return row;
    }

    function add(b) {
        b = b || {};
        var tipe = TYPES[b.tipe] ? b.tipe : 'teks';
        var row = build(tipe);
        list.appendChild(row);

        row.querySelector('[data-f="judul"]').value = b.judul || '';
        row.querySelector('[data-f="body"]').value = b[TYPES[tipe].key] || '';
        updateSum(row);
        updateVideo(row);

        row.addEventListener('input', function () {
            row.classList.remove('is-invalid');
            updateSum(row);
            updateVideo(row);
            sync();
        });
        row.querySelector('[data-head]').addEventListener('click', function (e) {
            if (e.target.closest('[data-tool]') || e.target.closest('[data-handle]')) return;
            row.classList.toggle('is-collapsed');
        });
        row.querySelector('[data-del]').addEventListener('click', function () {
            row.remove(); renumber(); sync();
        });
        row.querySelector('[data-up]').addEventListener('click', function () {
            if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
            renumber(); sync();
        });
        row.querySelector('[data-down]').addEventListener('click', function () {
            if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
            renumber(); sync();
        });

        // Seret & lepas: baris hanya bisa diseret lewat ikon titik supaya teks tetap bisa diseleksi.
        var handle = row.querySelector('[data-handle]');
        handle.addEventListener('mousedown', function () { row.draggable = true; });
        handle.addEventListener('mouseup', function () { row.draggable = false; });
        row.addEventListener('dragstart', function (e) {
            dragEl = row;
            row.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', 'langkah'); } catch (err) {}
        });
        row.addEventListener('dragend', function () {
            row.draggable = false;
            row.classList.remove('is-dragging');
            clearDrop();
            dragEl = null;
            renumber(); sync();
        });
        return row;
    }

    var dragEl = null;
    function clearDrop() {
        list.querySelectorAll('.drop-before, .drop-after').forEach(function (r) { r.classList.remove('drop-before', 'drop-after'); });
    }
    function dropTarget(y) {
        var rows = Array.prototype.filter.call(list.querySelectorAll('.sh-blk'), function (r) { return r !== dragEl; });
        for (var i = 0; i < rows.length; i++) {
            var box = rows[i].getBoundingClientRect();
            if (y < box.top + box.height / 2) return { before: rows[i], last: rows[rows.length - 1] };
        }
        return { before: null, last: rows[rows.length - 1] };
    }
    list.addEventListener('dragover', function (e) {
        if (!dragEl) return;
        e.preventDefault();
        var t = dropTarget(e.clientY);
        clearDrop();
        if (t.before) t.before.classList.add('drop-before');
        else if (t.last) t.last.classList.add('drop-after');
    });
    list.addEventListener('drop', function (e) {
        if (!dragEl) return;
        e.preventDefault();
        var t = dropTarget(e.clientY);
        if (t.before) list.insertBefore(dragEl, t.before); else list.appendChild(dragEl);
        clearDrop();
    });

    // Tombol pemilih jenis langkah
    ORDER.forEach(function (key) {
        var t = TYPES[key];
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sh-add-btn';
        btn.innerHTML = '<span class="sh-add-ic" style="background:' + t.bg + ';color:' + t.fg + ';">' + t.icon + '</span>' +
                        '<span class="sh-add-txt"><b>' + t.label + '</b><small>' + t.desc + '</small></span>';
        btn.addEventListener('click', function () { window.shTambahBlok(key); });
        grid.appendChild(btn);
    });

    window.shTambahBlok = function (tipe) {
        var row = add({ tipe: TYPES[tipe] ? tipe : 'teks' });
        showError('');
        renumber(); sync();
        row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        row.querySelector('[data-f="judul"]').focus({ preventScroll: true });
    };
    window.shMuatBlok = function (items) {
        list.innerHTML = '';
        showError('');
        (Array.isArray(items) ? items : []).forEach(add);
        renumber(); sync();
    };

    // Validasi sebelum kirim (hanya untuk jenis Modul)
    form.addEventListener('submit', function (e) {
        sync();
        var tipe = document.getElementById('shTipe');
        if (!tipe || tipe.value !== 'Modul') return;
        var rows = list.querySelectorAll('.sh-blk');
        if (!rows.length) {
            e.preventDefault();
            showError('Tambahkan minimal satu langkah untuk modul ini.');
            return;
        }
        var first = null;
        rows.forEach(function (r) {
            var kosong = r.querySelector('[data-f="body"]').value.trim() === '';
            r.classList.toggle('is-invalid', kosong);
            if (kosong && !first) first = r;
        });
        if (first) {
            e.preventDefault();
            first.classList.remove('is-collapsed');
            showError('Ada langkah yang belum diisi. Lengkapi atau hapus langkah yang ditandai merah.');
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            first.querySelector('[data-f="body"]').focus({ preventScroll: true });
        } else {
            showError('');
        }
    });

    try { window.shMuatBlok(JSON.parse(hidden.value || '[]')); } catch (e) { window.shMuatBlok([]); }
})();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if (window.pdfjsLib) window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

@endsection