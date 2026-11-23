{{--
    SILAKON — Self Help (Koordinator)
    Simpan sebagai: resources/views/koordinator/self-help.blade.php
    Data dummy dikirim dari KoordinatorController@selfhelp (materi disimpan
    sementara di session lewat simpanMateriSelfHelp / updateMateriSelfHelp /
    hapusMateriSelfHelp — lihat controller).
--}}
@extends('layouts.koordinator')

@section('judul', 'Self Help')
@section('subjudul', 'Kelola modul self help & pantau hasil pengerjaan konseli')

@section('content')

{{-- ============ HEAD ============ --}}
<div class="k-head-row">
    <div class="k-page-head">
        <h2>Self Help</h2>
        <p>Kelola modul self help dan pantau hasil pengerjaan konseli</p>
    </div>
    <button type="button" class="k-btn k-btn-add k-head-add" onclick="document.getElementById('kBtnTambahMateri').click()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        <span>Tambah Modul</span>
    </button>
</div>

{{-- ============ NOTIFIKASI ============ --}}
@if (session('sukses'))
    <div class="k-alert k-alert-sukses">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
        {{ session('sukses') }}
    </div>
@endif
@if (session('gagal'))
    <div class="k-alert k-alert-gagal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('gagal') }}
    </div>
@endif

{{-- ============ TAB SWITCHER ============ --}}
<div class="k-tabs" role="tablist">
    <button type="button" class="k-tab active" data-tab="materi">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
        Materi
    </button>
    <button type="button" class="k-tab" data-tab="hasil">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        Hasil Modul
    </button>
</div>

{{-- ===================================================================
     TAB 1 — MATERI
     =================================================================== --}}
<div class="k-tab-panel" id="tabMateri">

    <div class="k-grid k-grid-4">
        @foreach ($statistikMateri as $item)
            <div class="k-card k-stat">
                <span class="k-icon k-icon-lg tone-{{ $item['tone'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                </span>
                <b>{{ $item['nilai'] }}</b>
                <span class="k-stat-label">{{ $item['label'] }}</span>
                <span class="k-stat-sub">{{ $item['sub'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="k-card k-panel">
        <div class="k-panel-head">
            <h3 class="k-panel-title">Daftar Materi</h3>
            <div class="k-panel-head-actions">
                <form class="k-filter" onsubmit="return false">
                    <select class="k-select" id="materiJenis" aria-label="Filter jenis materi">
                        <option value="all">Semua Jenis</option>
                        <option value="modul">Modul Terpadu</option>
                    </select>
                    <input type="search" class="k-search" id="materiCari" placeholder="Cari judul materi..." aria-label="Cari materi">
                </form>
                <button type="button" class="k-btn k-btn-add" id="kBtnTambahMateri">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Tambah Materi</span>
                </button>
            </div>
        </div>

        <div class="k-table-wrap">
            <table class="k-table" id="materiTable">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Jenis</th>
                        <th>Topik</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materi as $m)
                        <tr data-jenis="{{ $m['type'] }}" data-cari="{{ mb_strtolower($m['title']) }}">
                            <td class="nama">
                                {{ $m['title'] }}
                                <div class="k-mrow-meta">
                                    <span class="k-badge k-badge-status-{{ $m['status'] === 'Dipublikasi' ? 'publikasi' : 'draft' }}">{{ $m['status'] }}</span>
                                    <span>{{ $m['topic'] }} &middot; {{ $m['date'] }}</span>
                                </div>
                            </td>
                            <td><span class="k-badge k-badge-{{ $m['type'] }}">{{ ucfirst($m['type']) }}</span></td>
                            <td>{{ $m['topic'] }}</td>
                            <td>
                                <span class="k-badge k-badge-status-{{ $m['status'] === 'Dipublikasi' ? 'publikasi' : 'draft' }}">
                                    {{ $m['status'] }}
                                </span>
                            </td>
                            <td>{{ $m['date'] }}</td>
                            <td class="c">
                                <div class="k-row-actions">
                                    <a href="{{ url('/self-help/' . $m['id']) }}" target="_blank" class="k-icon-btn" title="Lihat materi" aria-label="Lihat materi">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <button
                                        type="button"
                                        class="k-icon-btn k-edit-materi"
                                        title="Edit materi"
                                        aria-label="Edit materi"
                                        data-action="{{ route('koordinator.self-help.update', $m['id']) }}"
                                        data-title="{{ $m['title'] }}"
                                        data-type="{{ $m['type'] }}"
                                        data-topic="{{ $m['topic'] }}"
                                        data-status="{{ $m['status'] }}"
                                        data-isi="{{ $m['isi'] ?? '' }}"
                                        data-link="{{ $m['link_youtube'] ?? '' }}"
                                        data-keterangan="{{ $m['keterangan'] ?? '' }}"
                                        data-blok='@json($m['blok'] ?? [])'
                                        data-jumlah-halaman="{{ $m['jumlah_halaman'] ?? '' }}"
                                        data-pertanyaan='@json($m['pertanyaan'] ?? [])'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form action="{{ route('koordinator.self-help.destroy', $m['id']) }}" method="POST" class="k-inline-form k-hapus-materi">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="k-icon-btn k-icon-btn-danger" title="Hapus materi" aria-label="Hapus materi">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="k-table-empty" id="materiEmpty" hidden>Tidak ada materi yang cocok dengan filter.</p>
        </div>
    </div>
</div>

{{-- ===================================================================
     TAB 2 — HASIL MODUL
     =================================================================== --}}
<div class="k-tab-panel" id="tabHasil" hidden>

    <div class="k-grid k-grid-3">
        @foreach ($statistikHasil as $item)
            <div class="k-card k-stat">
                <span class="k-icon k-icon-lg tone-{{ $item['tone'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                </span>
                <b>{{ $item['nilai'] }}</b>
                <span class="k-stat-label">{{ $item['label'] }}</span>
                <span class="k-stat-sub">{{ $item['sub'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="k-card k-panel">
        <div class="k-panel-head">
            <h3 class="k-panel-title">Hasil Pengerjaan Modul</h3>
            <form class="k-filter" onsubmit="return false">
                <select class="k-select" id="hasilWorksheet" aria-label="Filter worksheet">
                    <option value="all">Semua Modul</option>
                    @foreach ($worksheetList as $w)
                        <option value="{{ $w }}">{{ $w }}</option>
                    @endforeach
                </select>
                <input type="search" class="k-search" id="hasilCari" placeholder="Cari nama / NIM..." aria-label="Cari hasil">
            </form>
        </div>

        <div class="k-table-wrap">
            <table class="k-table" id="hasilTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Fakultas / Prodi</th>
                        <th>Modul</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hasil as $h)
                        <tr data-worksheet="{{ $h['worksheet_judul'] }}" data-cari="{{ mb_strtolower($h['nama'] . ' ' . $h['nim']) }}">
                            <td class="nama">
                                {{ $h['nama'] }}
                                <div class="k-mrow-meta k-mrow-meta-col">
                                    <span>NIM {{ $h['nim'] }} &middot; {{ $h['prodi'] }}</span>
                                    <span>{{ $h['worksheet_judul'] }} &middot; {{ $h['tanggal'] }}</span>
                                </div>
                            </td>
                            <td>{{ $h['nim'] }}</td>
                            <td>{{ $h['prodi'] }}, {{ $h['fakultas'] }}</td>
                            <td>{{ $h['worksheet_judul'] }}</td>
                            <td>{{ $h['tanggal'] }}</td>
                            <td class="c">
                                <button
                                    type="button"
                                    class="k-icon-btn k-icon-btn-wide k-lihat-jawaban"
                                    title="Lihat jawaban"
                                    data-nama="{{ $h['nama'] }}"
                                    data-nim="{{ $h['nim'] }}"
                                    data-fakultas="{{ $h['fakultas'] }}"
                                    data-prodi="{{ $h['prodi'] }}"
                                    data-worksheet="{{ $h['worksheet_judul'] }}"
                                    data-tanggal="{{ $h['tanggal'] }}"
                                    data-jawaban="{{ json_encode($h['jawaban']) }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Lihat Jawaban</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="k-table-empty" id="hasilEmpty" hidden>Tidak ada hasil yang cocok dengan filter.</p>
        </div>
    </div>
</div>

{{-- ============ MODAL: DETAIL JAWABAN WORKSHEET (tidak diubah) ============ --}}
<div class="k-modal-backdrop" id="kModalBackdrop" hidden>
    <div class="k-modal" role="dialog" aria-modal="true" aria-labelledby="kModalTitle">
        <div class="k-modal-head">
            <div>
                <h3 id="kModalTitle"></h3>
                <p class="k-modal-sub" id="kModalSub"></p>
            </div>
            <button type="button" class="k-modal-close" id="kModalClose" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="k-modal-identity" id="kModalIdentity"></div>

        <div class="k-modal-body" id="kModalBody"></div>

        <div class="k-modal-foot">
            <button type="button" class="k-btn" id="kModalDownload">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh sebagai PDF
            </button>
        </div>
    </div>
</div>

{{-- ============ MODAL: TAMBAH / EDIT MATERI ============ --}}
<div class="k-modal-backdrop" id="kModalMateriBackdrop" hidden>
    <div class="k-modal" role="dialog" aria-modal="true" aria-labelledby="kModalMateriTitle">
        <div class="k-modal-head">
            <div>
                <h3 id="kModalMateriTitle">Tambah Materi</h3>
                <p class="k-modal-sub">Isi detail materi self help di bawah ini.</p>
            </div>
            <button type="button" class="k-modal-close" id="kModalMateriClose" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <form id="kFormMateri" method="POST" action="{{ route('koordinator.self-help.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="kFormMateriMethod" value="POST">

            <div class="k-form-group">
                <label for="kFieldTitle">Judul</label>
                <input type="text" name="title" id="kFieldTitle" placeholder="mis. Mengelola Stres Saat Skripsi" required>
            </div>

            <div class="k-form-row">
                <div class="k-form-group">
                    <label for="kFieldType">Jenis</label>
                    <select name="type" id="kFieldType" onchange="kApplyTipeMateri()" required>
                        <option value="modul">Modul Terpadu</option>
                    </select>
                </div>
                <div class="k-form-group">
                    <label for="kFieldTopic">Topik</label>
                    <select name="topic" id="kFieldTopic" required>
                        <option value="Kesehatan Mental">Kesehatan Mental</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Pribadi">Pribadi</option>
                        <option value="Karier">Karier</option>
                        <option value="Sosial">Sosial</option>
                    </select>
                </div>
            </div>

            {{-- Artikel --}}
            <div class="k-tipe-section" data-tipe="artikel">
                <div class="k-form-group">
                    <label for="kFieldIsi">Isi Artikel</label>
                    <textarea name="isi" id="kFieldIsi" rows="6" placeholder="Tulis isi artikel di sini..."></textarea>
                </div>
                <div class="k-form-group">
                    <label>Thumbnail <span class="k-opt">(opsional)</span></label>
                    <input type="file" name="thumbnail" accept="image/*">
                </div>
            </div>

            {{-- Video --}}
            <div class="k-tipe-section" data-tipe="video" style="display:none;">
                <div class="k-form-group">
                    <label for="kFieldLink">Link YouTube</label>
                    <input type="url" name="link_youtube" id="kFieldLink" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div class="k-form-group">
                    <label for="kFieldKeterangan">Keterangan</label>
                    <textarea name="keterangan" id="kFieldKeterangan" rows="4" placeholder="Uraikan singkat isi video ini..."></textarea>
                </div>
            </div>

            {{-- Worksheet --}}
            <div class="k-tipe-section" data-tipe="worksheet" style="display:none;">
                <div class="k-form-group">
                    <label>File PDF</label>
                    <input type="file" name="file_pdf" id="kFilePdf" accept="application/pdf">
                    <input type="hidden" name="jumlah_halaman" id="kJumlahHalaman" value="">
                    <p class="k-hint" id="kPdfInfo">Kosongkan saat edit kalau tidak ingin mengganti file yang sudah ada. Jumlah halaman dibaca otomatis saat PDF dipilih.</p>
                </div>
                <div class="k-form-group">
                    <label>Pertanyaan per halaman</label>
                    <p class="k-hint">Tentukan halaman PDF tempat pertanyaan ditampilkan.</p>
                    <div id="kPertanyaanList">
                        <div class="k-question-row">
                            <textarea name="pertanyaan[0][teks]" rows="2" placeholder="Tulis pertanyaan..." required></textarea>
                            <input type="number" name="pertanyaan[0][halaman]" min="1" placeholder="Hlm." required>
                            <button type="button" class="k-icon-btn k-icon-btn-danger" onclick="kHapusPertanyaan(this)">&times;</button>
                        </div>
                    </div>
                    <button type="button" class="k-btn-outline" onclick="kTambahPertanyaan()">+ Tambah pertanyaan</button>
                </div>
            </div>

            <div class="k-tipe-section" data-tipe="modul" style="display:none;">
                <div class="k-form-group">
                    <label>Urutan blok materi</label>
                    <p class="k-hint">Tambahkan langkah sesuai urutan. Video YouTube akan diputar langsung di halaman konseli.</p>
                    <input type="hidden" name="blok_json" id="kBlokJson" value="[]">
                    <div id="kBlokList"></div>
                    <button type="button" class="k-btn-outline" onclick="kTambahBlok()">+ Tambah Langkah</button>
                </div>
            </div>

            <div class="k-form-group">
                <label for="kFieldStatus">Status</label>
                <select name="status" id="kFieldStatus" required>
                    <option value="Dipublikasi">Dipublikasi</option>
                    <option value="Draft">Draft</option>
                </select>
                <p class="k-hint">Tanggal terisi otomatis sesuai hari materi ini ditambahkan/diperbarui.</p>
            </div>

            <div class="k-modal-foot">
                <button type="button" class="k-btn-outline" id="kBtnBatalMateri">Batal</button>
                <button type="submit" class="k-btn">Simpan Materi</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Self Help Koordinator)
     ===================================================================== --}}
<style>
    /* ---------- Grid 4 kolom (belum ada di dashboard) ---------- */
    .k-grid-4 { grid-template-columns: repeat(4, 1fr); }
    @media (max-width: 991px) { .k-grid-4 { grid-template-columns: repeat(2, 1fr); } }
    .k-question-row { display:grid; grid-template-columns:1fr 90px 34px; gap:8px; align-items:start; margin-bottom:8px; }
    .k-question-row textarea, .k-question-row input { width:100%; box-sizing:border-box; }
    @media (max-width: 640px) { .k-question-row { grid-template-columns:1fr 70px 30px; } }

    /* ---------- Notifikasi sukses / gagal ---------- */
    .k-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 18px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 500;
        margin-bottom: 18px;
    }
    .k-alert-sukses { background: #E3F3E8; color: #2E7D4F; border: 1px solid #CDEADB; }
    .k-alert-gagal  { background: #FDEEED; color: #C0392B; border: 1px solid #F6CFCC; }

    /* ---------- Tabs ---------- */
    .k-tabs {
        display: flex;
        gap: 8px;
        margin: 4px 0 20px;
        border-bottom: 1px solid #E5E9E1;
    }
    .k-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 6px;
        margin-bottom: -1px;
        font: inherit;
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: color .15s, border-color .15s;
    }
    .k-tab + .k-tab { margin-left: 14px; }
    .k-tab:hover { color: #374151; }
    .k-tab.active { color: var(--k-sage-mid, #6E8F60); border-bottom-color: var(--k-sage-mid, #6E8F60); }

    .k-tab-panel[hidden] { display: none; }

    /* ---------- Panel head (judul + form filter + tombol tambah) ---------- */
    .k-panel-head { flex-wrap: wrap; gap: 12px; }
    .k-panel-head-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-left: auto;
    }

    /* ---------- Search box ---------- */
    .k-search {
        height: 38px;
        min-width: 220px;
        padding: 0 14px;
        font: inherit;
        font-size: 13.5px;
        color: #374151;
        background: #fff;
        border: 1px solid #D9DED4;
        border-radius: 10px;
        outline: none;
        transition: border-color .15s;
    }
    .k-search::placeholder { color: #9CA3AF; }
    .k-search:focus { border-color: var(--k-sage-mid, #6E8F60); }

    /* ---------- Badge jenis materi ---------- */
    .k-badge {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 11px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .k-badge-artikel   { background: #DBEAFE; color: #1E40AF; }
    .k-badge-video     { background: #F3F4F6; color: #374151; }
    .k-badge-worksheet { background: #FEF3C7; color: #92400E; }

    /* ---------- Badge status materi ---------- */
    .k-badge-status-publikasi { background: #D9F5E3; color: #1F7A44; }
    .k-badge-status-draft     { background: #FDF1C2; color: #8A6100; }

    /* ---------- Tombol kecil (varian outline) ---------- */
    .k-btn-sm {
        padding: 6px 14px;
        font-size: 12.5px;
        border-radius: 8px;
    }
    .k-btn-danger { color: #C0392B; border-color: #F6CFCC; }
    .k-btn-danger:hover { background: #FDEEED; }
    td.c, th.c { text-align: center; }

    /* ---------- Tombol Tambah Materi (disamakan tinggi dgn filter) ---------- */
    .k-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 38px;
        padding: 0 16px;
        font-size: 13.5px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ---------- Aksi per baris (Lihat / Edit / Hapus) sebagai ikon ---------- */
    #materiTable th:last-child,
    #materiTable td:last-child {
        width: 1%;
        white-space: nowrap;
    }
    .k-row-actions {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
        justify-content: center;
    }
    .k-inline-form { display: inline-flex; margin: 0; }
    .k-inline-form button { font: inherit; }

    .k-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        flex-shrink: 0;
        border: 1px solid #E2E6DD;
        border-radius: 8px;
        background: #fff;
        color: #6B7280;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
    }
    .k-icon-btn:hover { background: #F3F5F1; border-color: #D9DED4; color: #374151; }
    .k-icon-btn:focus-visible { outline: 2px solid var(--k-sage-mid, #6E8F60); outline-offset: 1px; }
    .k-icon-btn svg { pointer-events: none; }
    .k-icon-btn-danger { color: #C0392B; border-color: #F6CFCC; }
    .k-icon-btn-danger:hover { background: #FDEEED; border-color: #F2B8B2; color: #C0392B; }

    /* ---------- Varian ikon+label ringkas (mis. "Lihat Jawaban") ---------- */
    .k-icon-btn-wide {
        width: auto;
        height: 32px;
        padding: 0 12px;
        gap: 6px;
        font: inherit;
        font-size: 12.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    #hasilTable th:last-child,
    #hasilTable td:last-child {
        width: 1%;
        white-space: nowrap;
    }

    /* ---------- Tabel kosong ---------- */
    .k-table-empty {
        text-align: center;
        color: #9CA3AF;
        font-size: 13.5px;
        padding: 28px 0 6px;
        margin: 0;
    }

    /* ---------- Modal ---------- */
    .k-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 60;
        background: rgba(17, 24, 39, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .k-modal-backdrop[hidden] { display: none; }
    .k-modal {
        width: 100%;
        max-width: 620px;
        max-height: 88vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 16px;
        padding: 26px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
    }
    .k-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .k-modal-head h3 { font-size: 17px; font-weight: 700; margin: 0 0 4px; }
    .k-modal-sub { font-size: 13px; color: #6B7280; margin: 0; }
    .k-modal-close {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6B7280;
        background: #F3F4F6;
        border: none;
        border-radius: 50%;
        cursor: pointer;
    }
    .k-modal-close:hover { background: #E5E7EB; color: #111827; }

    .k-modal-identity {
        font-size: 13px;
        color: #374151;
        background: #F3F8F3;
        border: 1px solid #DCEBDD;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 18px;
        line-height: 1.7;
    }
    .k-modal-identity b { color: #1F2937; }

    .k-modal-qa { margin-bottom: 16px; }
    .k-modal-qa p:first-child {
        font-size: 13.5px;
        font-weight: 600;
        color: #1F2937;
        margin: 0 0 6px;
    }
    .k-modal-qa p:last-child {
        font-size: 13.5px;
        line-height: 1.6;
        color: #4B5563;
        margin: 0;
        padding-left: 12px;
        border-left: 3px solid var(--k-sage-mid, #6E8F60);
        white-space: pre-wrap;
    }

    .k-modal-foot {
        border-top: 1px solid #EEF1EC;
        padding-top: 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* ---------- Form tambah / edit materi ---------- */
    .k-form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
    .k-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 480px) { .k-form-row { grid-template-columns: 1fr; } }
    .k-form-group label { font-size: 13px; font-weight: 600; color: #3A463F; }
    .k-form-group input,
    .k-form-group select,
    .k-form-group textarea {
        border: 1px solid #D8DFDA;
        border-radius: 9px;
        padding: 10px 12px;
        font-size: 13.5px;
        color: #1F2A24;
        outline: none;
        font-family: inherit;
        background: #fff;
    }
    .k-form-group textarea { resize: vertical; line-height: 1.55; }
    .k-form-group input:focus,
    .k-form-group select:focus,
    .k-form-group textarea:focus {
        border-color: var(--k-sage-mid, #6E8F60);
        box-shadow: 0 0 0 3px rgba(110, 143, 96, .12);
    }
    .k-form-group input[type="file"] { padding: 8px 10px; font-size: 13px; background: #FAFBFA; }
    .k-hint { font-size: 12px; color: #8A978F; margin: 0; }
    .k-opt { font-weight: 400; color: #8A978F; }

    /* ---------- Responsive ---------- */
    @media (max-width: 640px) {
        .k-tabs { overflow-x: auto; scrollbar-width: none; }
        .k-tabs::-webkit-scrollbar { display: none; }
        .k-panel-head { flex-direction: column; align-items: stretch; }
        .k-panel-head-actions { margin-left: 0; flex-direction: column; align-items: stretch; }
        .k-filter { flex-direction: column; align-items: stretch; }
        .k-search { min-width: 0; width: 100%; }
        .k-btn-add { width: 100%; justify-content: center; }
        .k-row-actions { justify-content: flex-start; }
    }

    /* Penyelarasan visual dengan halaman Self Help Admin */
    .k-head-row { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:28px; }
    .k-page-head h2 { margin:0; font-size:22px; font-weight:700; color:#1f2a24; }
    .k-page-head p { margin:6px 0 0; color:#6b7a72; font-size:13.5px; }
    .k-head-add { height:42px; padding:0 18px; border-radius:12px; background:#7fa285; color:#fff; border:0; }
    .k-head-add:hover { background:#6d9174; }
    .k-panel-head-actions #kBtnTambahMateri { display:none; }
    .k-card.k-stat, .k-card.k-panel { border-color:#e1e7e3; box-shadow:0 2px 8px rgba(31,42,36,.04); }
    .k-card.k-stat { border-radius:16px; }
    .k-card.k-panel { border-radius:18px; overflow:hidden; }
    .k-table thead th { background:#f4f7f4; color:#5c6a62; font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; padding:18px 12px; }
    .k-table tbody td { padding:18px 12px; border-top-color:#eef1ee; color:#4b5a51; }
    .k-table tbody tr:hover { background:#fafbfa; }
    .k-table .nama { color:#1f2a24; font-weight:700; min-width:190px; line-height:1.45; }
    .k-modal-backdrop { background:rgba(20,28,24,.45); z-index:1000; }
    .k-modal { max-width:560px; padding:26px 26px 24px; border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.2); }
    .k-modal:has(#kBlokList) { max-width:820px; }
    .k-modal-foot { position:sticky; bottom:0; z-index:3; background:#fff; margin:22px -26px -24px; padding:14px 26px; border-top:1px solid #eef1ee; }
    .k-block-row { background:#f7f9f7 !important; border-color:#e6ebe7 !important; border-radius:11px !important; padding:12px !important; }
    .k-block-row input, .k-block-row textarea, .k-block-row select { border:1px solid #d8dfda; border-radius:9px; padding:10px 12px; font:inherit; font-size:13.5px; background:#fff; box-sizing:border-box; }
    .k-block-row input:focus, .k-block-row textarea:focus, .k-block-row select:focus { outline:none; border-color:#4d7c5f; box-shadow:0 0 0 3px rgba(77,124,95,.12); }
    @media (max-width:700px) { .k-block-row { grid-template-columns:1fr !important; } .k-head-add { width:100%; justify-content:center; } }

    /* =====================================================================
       RESPONSIF HP — kartu statistik 2 kolom, tabel jadi daftar ringkas,
       filter satu baris, modal dipadatkan
       ===================================================================== */
    .k-mrow-meta { display: none; }

    @media (max-width: 640px) {
        .k-head-row { gap: 10px; margin-bottom: 14px; }
        .k-page-head h2 { font-size: 19px; }
        .k-page-head p { margin-top: 3px; font-size: 13px; }
        .k-head-add { height: 40px; }
        .k-alert { margin-bottom: 14px; padding: 11px 14px; font-size: 13px; }

        .k-tabs { gap: 0; margin: 0 0 14px; }
        .k-tab { flex: 1; justify-content: center; padding: 10px 6px; }
        .k-tab + .k-tab { margin-left: 0; }

        /* kartu statistik: 2 kolom, ringkas */
        .k-tab-panel .k-grid.k-grid-4,
        .k-tab-panel .k-grid.k-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
        .k-tab-panel .k-grid-3 > :last-child:nth-child(odd) { grid-column: 1 / -1; }
        .k-tab-panel .k-stat { padding: 12px; border-radius: 14px; }
        .k-tab-panel .k-stat .k-icon-lg { width: 32px; height: 32px; border-radius: 9px; }
        .k-tab-panel .k-stat .k-icon-lg svg { width: 16px; height: 16px; }
        .k-tab-panel .k-stat b { margin-top: 8px; font-size: 20px; }
        .k-tab-panel .k-stat-label { margin-top: 2px; font-size: 12.5px; font-weight: 700; }
        .k-tab-panel .k-stat-sub { margin-top: 2px; font-size: 11px; line-height: 1.35; }

        /* panel + filter: select dan pencarian tetap satu baris */
        .k-card.k-panel { padding: 14px 12px; border-radius: 16px; }
        .k-panel-head { gap: 10px; margin-bottom: 10px; }
        .k-panel-head .k-panel-title { margin: 0; font-size: 15px; }
        .k-panel-head-actions { width: 100%; margin-left: 0; }
        .k-panel-head .k-filter { width: 100%; flex-direction: row; flex-wrap: nowrap; align-items: center; gap: 8px; }
        .k-panel-head .k-filter .k-select {
            flex: 0 1 auto; width: auto; max-width: 46%; min-width: 0;
            padding: 0 28px 0 12px; font-size: 13px; background-position: right 10px center; text-overflow: ellipsis;
        }
        .k-panel-head .k-filter .k-search { flex: 1 1 0; width: auto; min-width: 0; }

        /* tabel -> daftar ringkas (tetap tabel, kolom sekunder pindah ke bawah judul) */
        #materiTable, #hasilTable { min-width: 0; }
        #materiTable thead, #hasilTable thead { display: none; }
        #materiTable td:nth-child(n+2):nth-child(-n+5),
        #hasilTable td:nth-child(n+2):nth-child(-n+5) { display: none; }
        #materiTable tbody td, #hasilTable tbody td { padding: 12px 4px; }
        #materiTable .nama, #hasilTable .nama { min-width: 0; font-size: 13.5px; }
        .k-mrow-meta {
            display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; margin-top: 5px;
            font-size: 12px; font-weight: 500; line-height: 1.4; color: #6B7280;
        }
        .k-mrow-meta .k-badge { padding: 2px 9px; font-size: 11px; }
        .k-mrow-meta-col { flex-direction: column; align-items: flex-start; gap: 1px; }

        /* modal */
        .k-modal-backdrop { padding: 12px; }
        .k-modal { max-height: 92vh; max-height: 92dvh; padding: 18px 16px 16px; border-radius: 14px; }
        .k-modal-head { margin-bottom: 12px; }
        .k-modal-head h3 { font-size: 16px; }
        .k-modal-foot { margin: 16px -16px -16px; padding: 12px 16px; }
        .k-modal-foot .k-btn, .k-modal-foot .k-btn-outline { flex: 1; margin-top: 0; justify-content: center; text-align: center; }
        .k-form-group { margin-bottom: 12px; }
    }

    /* HP kecil: tombol "Lihat Jawaban" jadi ikon saja */
    @media (max-width: 480px) {
        .k-lihat-jawaban { width: 32px; padding: 0; }
        .k-lihat-jawaban span { display: none; }
    }
</style>

{{-- Library export HTML -> PDF di sisi browser (dipakai tombol "Unduh sebagai PDF" di modal) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

{{-- =====================================================================
     JS: tab switcher, filter tabel Materi & Hasil, modal jawaban, export PDF,
     modal tambah/edit materi, hapus materi
     ===================================================================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ---------- Tab switcher ---------- */
        var tabs   = [].slice.call(document.querySelectorAll('.k-tab'));
        var panels = { materi: document.getElementById('tabMateri'), hasil: document.getElementById('tabHasil') };

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');
                Object.keys(panels).forEach(function (key) {
                    panels[key].hidden = key !== tab.dataset.tab;
                });
            });
        });

        /* ---------- Filter tabel Materi ---------- */
        var materiRows   = [].slice.call(document.querySelectorAll('#materiTable tbody tr'));
        var materiJenis  = document.getElementById('materiJenis');
        var materiCari   = document.getElementById('materiCari');
        var materiEmpty  = document.getElementById('materiEmpty');

        function filterMateri() {
            var jenis = materiJenis.value;
            var kata  = materiCari.value.trim().toLowerCase();
            var shown = 0;

            materiRows.forEach(function (row) {
                var match = (jenis === 'all' || row.dataset.jenis === jenis) &&
                            (kata === '' || row.dataset.cari.indexOf(kata) !== -1);
                row.hidden = !match;
                if (match) shown++;
            });
            materiEmpty.hidden = shown !== 0;
        }
        materiJenis.addEventListener('change', filterMateri);
        materiCari.addEventListener('input', filterMateri);

        /* ---------- Filter tabel Hasil ---------- */
        var hasilRows  = [].slice.call(document.querySelectorAll('#hasilTable tbody tr'));
        var hasilWs    = document.getElementById('hasilWorksheet');
        var hasilCari  = document.getElementById('hasilCari');
        var hasilEmpty = document.getElementById('hasilEmpty');

        function filterHasil() {
            var ws   = hasilWs.value;
            var kata = hasilCari.value.trim().toLowerCase();
            var shown = 0;

            hasilRows.forEach(function (row) {
                var match = (ws === 'all' || row.dataset.worksheet === ws) &&
                            (kata === '' || row.dataset.cari.indexOf(kata) !== -1);
                row.hidden = !match;
                if (match) shown++;
            });
            hasilEmpty.hidden = shown !== 0;
        }
        hasilWs.addEventListener('change', filterHasil);
        hasilCari.addEventListener('input', filterHasil);

        /* ---------- Modal: lihat jawaban ---------- */
        var backdrop     = document.getElementById('kModalBackdrop');
        var modalTitle   = document.getElementById('kModalTitle');
        var modalSub     = document.getElementById('kModalSub');
        var modalIdentity= document.getElementById('kModalIdentity');
        var modalBody    = document.getElementById('kModalBody');
        var modalClose   = document.getElementById('kModalClose');
        var modalDownload= document.getElementById('kModalDownload');
        var currentData  = null;

        function escapeHtml(str) {
            return (str || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        document.querySelectorAll('.k-lihat-jawaban').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var jawaban = [];
                try { jawaban = JSON.parse(btn.dataset.jawaban); } catch (e) {}

                currentData = {
                    nama: btn.dataset.nama, nim: btn.dataset.nim,
                    fakultas: btn.dataset.fakultas, prodi: btn.dataset.prodi,
                    worksheet: btn.dataset.worksheet, tanggal: btn.dataset.tanggal,
                    jawaban: jawaban
                };

                modalTitle.textContent = currentData.worksheet;
                modalSub.textContent   = 'Diisi pada ' + currentData.tanggal;
                modalIdentity.innerHTML =
                    '<b>' + escapeHtml(currentData.nama) + '</b> &middot; ' +
                    escapeHtml(currentData.prodi) + ', ' + escapeHtml(currentData.fakultas) +
                    ' &middot; NIM ' + escapeHtml(currentData.nim);

                var html = '';
                jawaban.forEach(function (qa, i) {
                    html += '<div class="k-modal-qa">';
                    html += '<p>' + (i + 1) + '. ' + escapeHtml(qa.pertanyaan) + '</p>';
                    html += '<p>' + (qa.jawaban ? escapeHtml(qa.jawaban) : '(belum diisi)') + '</p>';
                    html += '</div>';
                });
                modalBody.innerHTML = html;

                backdrop.hidden = false;
            });
        });

        function closeModal() { backdrop.hidden = true; currentData = null; }
        modalClose.addEventListener('click', closeModal);
        backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (!backdrop.hidden) closeModal();
            if (!modalMateriBackdrop.hidden) tutupModalMateri();
        });

        /* ---------- Unduh PDF dari modal ---------- */
        function slugify(str) {
            return (str || 'worksheet').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }

        modalDownload.addEventListener('click', function () {
            if (!currentData) return;

            var originalLabel = modalDownload.innerHTML;
            modalDownload.disabled = true;
            modalDownload.innerHTML = 'Menyiapkan PDF...';

            var printable = document.createElement('div');
            printable.style.padding = '28px';
            printable.style.fontFamily = 'Arial, Helvetica, sans-serif';
            printable.style.color = '#1F2937';
            printable.style.width = '700px';

            var html = '';
            html += '<h1 style="font-size:19px;margin:0 0 4px;">' + escapeHtml(currentData.worksheet) + '</h1>';
            html += '<p style="font-size:12px;color:#6B7280;margin:0 0 20px;">Hasil Modul Self Help &middot; SILAKON LPPP UNNES</p>';
            html += '<table style="width:100%;font-size:12.5px;border-collapse:collapse;margin-bottom:22px;">';
            html += '<tr><td style="padding:3px 0;width:120px;color:#6B7280;">Nama</td><td>: ' + escapeHtml(currentData.nama) + '</td></tr>';
            html += '<tr><td style="padding:3px 0;color:#6B7280;">Fakultas</td><td>: ' + escapeHtml(currentData.fakultas) + '</td></tr>';
            html += '<tr><td style="padding:3px 0;color:#6B7280;">Program Studi</td><td>: ' + escapeHtml(currentData.prodi) + '</td></tr>';
            html += '<tr><td style="padding:3px 0;color:#6B7280;">NIM</td><td>: ' + escapeHtml(currentData.nim) + '</td></tr>';
            html += '<tr><td style="padding:3px 0;color:#6B7280;">Tanggal Mengisi</td><td>: ' + escapeHtml(currentData.tanggal) + '</td></tr>';
            html += '</table>';
            html += '<hr style="border:none;border-top:1px solid #E5E7EB;margin:0 0 20px;">';

            currentData.jawaban.forEach(function (qa, i) {
                html += '<div style="margin-bottom:16px;">';
                html += '<p style="font-size:13px;font-weight:bold;margin:0 0 6px;">' + (i + 1) + '. ' + escapeHtml(qa.pertanyaan) + '</p>';
                html += '<p style="font-size:13px;line-height:1.65;white-space:pre-wrap;border-left:3px solid #7DA381;padding:2px 0 2px 12px;margin:0;">' +
                    (qa.jawaban ? escapeHtml(qa.jawaban) : '<span style="color:#9CA3AF;font-style:italic;">(belum diisi)</span>') + '</p>';
                html += '</div>';
            });

            printable.innerHTML = html;
            document.body.appendChild(printable);

            var filename = 'modul-' + slugify(currentData.worksheet) + '-' + (currentData.nim || 'konseli') + '.pdf';

            html2pdf().set({
                margin: 12,
                filename: filename,
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).from(printable).save().then(function () {
                document.body.removeChild(printable);
                modalDownload.disabled = false;
                modalDownload.innerHTML = originalLabel;
            }).catch(function () {
                document.body.removeChild(printable);
                modalDownload.disabled = false;
                modalDownload.innerHTML = originalLabel;
            });
        });

        /* ---------- Modal: tambah / edit materi ---------- */
        var modalMateriBackdrop = document.getElementById('kModalMateriBackdrop');
        var modalMateriTitle    = document.getElementById('kModalMateriTitle');
        var modalMateriClose    = document.getElementById('kModalMateriClose');
        var formMateri          = document.getElementById('kFormMateri');
        var formMateriMethod    = document.getElementById('kFormMateriMethod');
        var btnTambahMateri     = document.getElementById('kBtnTambahMateri');
        var btnBatalMateri      = document.getElementById('kBtnBatalMateri');
        var storeUrlMateri      = formMateri.getAttribute('action');

        function bukaModalMateri() { modalMateriBackdrop.hidden = false; }

        function tutupModalMateri() {
            modalMateriBackdrop.hidden = true;
            formMateri.reset();
            window.kApplyTipeMateri();
        }

        btnTambahMateri.addEventListener('click', function () {
            modalMateriTitle.textContent = 'Tambah Materi';
            formMateri.reset();
            formMateri.setAttribute('action', storeUrlMateri);
            formMateriMethod.value = 'POST';
            document.getElementById('kJumlahHalaman').value = '';
            document.getElementById('kPdfInfo').textContent = 'Kosongkan saat edit kalau tidak ingin mengganti file yang sudah ada. Jumlah halaman dibaca otomatis saat PDF dipilih.';
            window.kApplyTipeMateri();
            bukaModalMateri();
        });

        document.querySelectorAll('.k-edit-materi').forEach(function (btn) {
            btn.addEventListener('click', function () {
                modalMateriTitle.textContent = 'Edit Materi';
                formMateri.setAttribute('action', btn.dataset.action);
                formMateriMethod.value = 'PUT';

                document.getElementById('kFieldTitle').value       = btn.dataset.title || '';
                document.getElementById('kFieldType').value        = btn.dataset.type || 'artikel';
                document.getElementById('kFieldTopic').value       = btn.dataset.topic || '';
                document.getElementById('kFieldStatus').value      = btn.dataset.status || 'Dipublikasi';
                document.getElementById('kFieldIsi').value         = btn.dataset.isi || '';
                document.getElementById('kFieldLink').value        = btn.dataset.link || '';
                document.getElementById('kFieldKeterangan').value  = btn.dataset.keterangan || '';
                var blokField = formMateri.querySelector('[name="blok_json"]');
                if (blokField) { try { blokField.value = JSON.stringify(JSON.parse(btn.dataset.blok || '[]')); } catch (e) { blokField.value = ''; } }
                if (window.kMuatBlok) { try { window.kMuatBlok(JSON.parse(btn.dataset.blok || '[]')); } catch (e) { window.kMuatBlok([]); } }
                document.getElementById('kJumlahHalaman').value = btn.dataset.jumlahHalaman || '';
                document.getElementById('kPdfInfo').textContent = btn.dataset.jumlahHalaman
                    ? 'PDF tersimpan sebelumnya: ' + btn.dataset.jumlahHalaman + ' halaman. Pilih file baru jika ingin menggantinya.'
                    : 'Kosongkan saat edit kalau tidak ingin mengganti file yang sudah ada.';

                var list = document.getElementById('kPertanyaanList');
                list.innerHTML = '';
                var questions = [];
                try { questions = JSON.parse(btn.dataset.pertanyaan || '[]'); } catch (e) {}
                if (!questions.length) questions = [{teks:'', halaman:''}];
                questions.forEach(function (q, i) {
                    list.insertAdjacentHTML('beforeend', '<div class="k-question-row"><textarea name="pertanyaan['+i+'][teks]" rows="2" required>'+String(q.teks || '').replace(/</g,'&lt;')+'</textarea><input type="number" name="pertanyaan['+i+'][halaman]" min="1" value="'+(q.halaman || '')+'" required><button type="button" class="k-icon-btn k-icon-btn-danger" onclick="kHapusPertanyaan(this)">&times;</button></div>');
                });

                window.kApplyTipeMateri();
                bukaModalMateri();
            });
        });

        modalMateriClose.addEventListener('click', tutupModalMateri);
        btnBatalMateri.addEventListener('click', tutupModalMateri);
        modalMateriBackdrop.addEventListener('click', function (e) {
            if (e.target === modalMateriBackdrop) tutupModalMateri();
        });

        /* ---------- Hapus materi (konfirmasi sebelum submit) ---------- */
        document.querySelectorAll('.k-hapus-materi').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!confirm('Yakin ingin menghapus materi ini? Tindakan ini tidak bisa dibatalkan.')) {
                    e.preventDefault();
                }
            });
        });
    });

    /* Tampilkan hanya isian sesuai jenis materi yang dipilih (dipanggil dari
       onchange select & dari luar fungsi DOMContentLoaded supaya bisa dipakai
       ulang saat modal dibuka). */
    function kApplyTipeMateri() {
        var tipe = document.getElementById('kFieldType').value;
        document.querySelectorAll('.k-tipe-section').forEach(function (sec) {
            sec.style.display = sec.dataset.tipe === tipe ? '' : 'none';
        });
    }
    function kTambahPertanyaan() {
        var list = document.getElementById('kPertanyaanList');
        var i = list.querySelectorAll('.k-question-row').length;
        var row = document.createElement('div');
        row.className = 'k-question-row';
        row.innerHTML = '<textarea name="pertanyaan[' + i + '][teks]" rows="2" placeholder="Tulis pertanyaan..." required></textarea>' +
            '<input type="number" name="pertanyaan[' + i + '][halaman]" min="1" placeholder="Hlm." required>' +
            '<button type="button" class="k-icon-btn k-icon-btn-danger" onclick="kHapusPertanyaan(this)">&times;</button>';
        list.appendChild(row);
    }
    function kHapusPertanyaan(button) {
        var list = document.getElementById('kPertanyaanList');
        if (list.querySelectorAll('.k-question-row').length <= 1) return;
        button.closest('.k-question-row').remove();
        list.querySelectorAll('.k-question-row').forEach(function (row, i) {
            row.querySelector('textarea').name = 'pertanyaan[' + i + '][teks]';
            row.querySelector('input').name = 'pertanyaan[' + i + '][halaman]';
        });
    }
    window.kTambahPertanyaan = kTambahPertanyaan;
    window.kHapusPertanyaan = kHapusPertanyaan;
    window.kApplyTipeMateri = kApplyTipeMateri;

    // Baca jumlah halaman PDF di sisi browser dan batasi halaman pertanyaan
    // agar selalu berada dalam rentang halaman dokumen yang diunggah.
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('kFilePdf');
        var info = document.getElementById('kPdfInfo');
        var total = document.getElementById('kJumlahHalaman');
        if (!input) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            info.textContent = 'Membaca PDF...';
            if (!window.pdfjsLib) { info.textContent = 'PDF dipilih: ' + file.name + '. Jumlah halaman akan dibaca saat tersedia.'; return; }
            var reader = new FileReader();
            reader.onload = function () {
                window.pdfjsLib.getDocument({data: new Uint8Array(reader.result)}).promise.then(function (pdf) {
                    total.value = pdf.numPages;
                    info.textContent = 'PDF siap disimpan: ' + file.name + ' (' + pdf.numPages + ' halaman).';
                    document.querySelectorAll('#kPertanyaanList input[name$="[halaman]"]').forEach(function (el) { el.max = pdf.numPages; });
                }).catch(function () { info.textContent = 'PDF dipilih, tetapi jumlah halaman tidak dapat dibaca otomatis.'; });
            };
            reader.readAsArrayBuffer(file);
        });
    });
    (function () {
        var list=document.getElementById('kBlokList'), hidden=document.getElementById('kBlokJson'), form=document.getElementById('kFormMateri');
        if(!list||!hidden||!form)return;
        function esc(v){return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
        function sync(){var out=[];list.querySelectorAll('.k-block-row').forEach(function(r){var t=r.querySelector('[data-t]').value,b=r.querySelector('[data-b]').value,o={tipe:t,judul:r.querySelector('[data-j]').value};if(t==='video')o.url=b;else if(t==='soal')o.pertanyaan=b;else if(t==='kegiatan')o.instruksi=b;else o.isi=b;out.push(o);});hidden.value=JSON.stringify(out);}
        function add(b){b=b||{};var t=b.tipe||'teks',body=b.isi||b.url||b.pertanyaan||b.instruksi||'',r=document.createElement('div');r.className='k-block-row';r.style.cssText='border:1px solid #dce8df;border-radius:10px;padding:10px;margin:8px 0;display:grid;grid-template-columns:140px 1fr 1fr auto;gap:8px;align-items:start';r.innerHTML='<select data-t><option value="teks">Teks</option><option value="video">Video YouTube</option><option value="soal">Pertanyaan</option><option value="kegiatan">Kegiatan</option></select><input data-j placeholder="Judul langkah" value="'+esc(b.judul)+'"><textarea data-b rows="2" placeholder="Isi, URL video, pertanyaan, atau instruksi">'+esc(body)+'</textarea><span><button type="button" class="k-icon-btn" data-u>↑</button><button type="button" class="k-icon-btn" data-d>↓</button><button type="button" class="k-icon-btn k-icon-btn-danger" data-x>×</button></span>';list.appendChild(r);r.querySelector('[data-t]').value=t;r.querySelectorAll('input,textarea,select').forEach(function(x){x.addEventListener('input',sync);x.addEventListener('change',sync)});r.querySelector('[data-x]').onclick=function(){r.remove();sync()};r.querySelector('[data-u]').onclick=function(){if(r.previousElementSibling)list.insertBefore(r,r.previousElementSibling);sync()};r.querySelector('[data-d]').onclick=function(){if(r.nextElementSibling)list.insertBefore(r.nextElementSibling,r);sync()};sync()}
        window.kTambahBlok=function(){add()}; window.kMuatBlok=function(items){list.innerHTML='';(Array.isArray(items)&&items.length?items:[{}]).forEach(add)};form.addEventListener('submit',sync);add();
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if (window.pdfjsLib) window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

@endsection