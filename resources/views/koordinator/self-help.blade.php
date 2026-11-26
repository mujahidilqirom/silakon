{{--
    SILAKON — Self Help (Koordinator)
    Simpan sebagai: resources/views/koordinator/self-help.blade.php
    Data dummy dikirim dari KoordinatorController@selfhelp (materi disimpan
    sementara di session lewat simpanMateriSelfHelp / updateMateriSelfHelp /
    hapusMateriSelfHelp — lihat controller).

    Form Tambah/Edit Modul disamakan dengan versi admin: tiap langkah PDF punya
    input file sendiri (file_pdf_blok[<file_id>]) dan satu langkah Pertanyaan
    boleh berisi beberapa pertanyaan. Struktur blok_json tidak berubah.
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
    <div class="k-modal k-modal-form" role="dialog" aria-modal="true" aria-labelledby="kModalMateriTitle">
        <div class="k-modal-head">
            <div class="k-modal-title">
                <span class="k-modal-ic">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                <div>
                    <h3 id="kModalMateriTitle">Tambah Materi</h3>
                    <p class="k-modal-sub">Isi detail materi self help di bawah ini.</p>
                </div>
            </div>
            <button type="button" class="k-modal-close" id="kModalMateriClose" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <form id="kFormMateri" method="POST" action="{{ route('koordinator.self-help.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="kFormMateriMethod" value="POST">

            <div class="k-modal-body">
            <div class="k-sec-label">Informasi dasar</div>

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

            <div class="k-sec-label">Isi konten</div>

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
                <div class="sh-mod-head">
                    <div>
                        <h4>Susun isi modul</h4>
                        <p>Konseli melihat langkah secara berurutan dari atas ke bawah. Seret ikon titik atau pakai tombol panah untuk mengubah urutan. Setiap langkah PDF punya file sendiri, jadi satu modul boleh berisi lebih dari satu PDF.</p>
                    </div>
                    <div class="sh-mod-tools">
                        <span class="sh-mod-count" id="kBlokCount">0 langkah</span>
                        <button type="button" class="sh-mod-toggle" id="kBlokToggle" hidden>Ciutkan semua</button>
                    </div>
                </div>

                <input type="hidden" name="blok_json" id="kBlokJson" value="[]">
                <div class="sh-blk-wrap" id="kBlokList"></div>
                <div class="sh-blk-empty" id="kBlokEmpty">Belum ada langkah. Pilih jenis langkah di bawah untuk memulai.</div>
                <p class="sh-form-error" id="kBlokError" style="display:none;"></p>

                <div class="sh-add-bar">
                    <p>Tambah langkah</p>
                    <div class="sh-add-grid" id="kAddGrid"></div>
                </div>
            </div>

            <div class="k-sec-label">Publikasi</div>

            <div class="k-form-group">
                <label for="kFieldStatus">Status</label>
                <select name="status" id="kFieldStatus" required>
                    <option value="Dipublikasi">Dipublikasi</option>
                    <option value="Draft">Draft</option>
                </select>
                <p class="k-hint">Tanggal terisi otomatis sesuai hari materi ini ditambahkan/diperbarui.</p>
            </div>

            </div>{{-- /.k-modal-body --}}

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
    .k-modal-foot { position:sticky; bottom:0; z-index:3; background:#fff; margin:22px -26px -24px; padding:14px 26px; border-top:1px solid #eef1ee; }
    @media (max-width:700px) { .k-head-add { width:100%; justify-content:center; } }

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

    /* =====================================================================
       Form Tambah / Edit Modul (disamakan dengan halaman Self Help Admin):
       header tetap, isi bisa di-scroll, tombol simpan selalu terlihat
       ===================================================================== */
    .k-modal-backdrop { backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); }
    .k-modal.k-modal-form {
        max-width: 860px; padding: 0; overflow: hidden; display: flex; flex-direction: column;
        border-radius: 20px; max-height: 92vh; max-height: 92dvh;
    }
    .k-modal-form .k-modal-head {
        align-items: center; margin: 0; padding: 18px 24px;
        border-bottom: 1px solid #eaeeea; background: #f8fbf8;
    }
    .k-modal-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .k-modal-ic {
        width: 40px; height: 40px; border-radius: 12px; background: #e3f3e8; color: #2e7d4f;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .k-modal-form .k-modal-head h3 { margin: 0; font-size: 17px; line-height: 1.3; color: #1f2a24; }
    .k-modal-form .k-modal-sub { margin-top: 2px; font-size: 12.5px; color: #7a8a80; }
    #kFormMateri { display: flex; flex-direction: column; flex: 1; min-height: 0; }
    .k-modal-body { flex: 1; min-height: 0; overflow-y: auto; padding: 22px 24px 6px; }
    .k-modal.k-modal-form .k-modal-foot {
        position: static; margin: 0; padding: 14px 24px; border-top: 1px solid #eaeeea; background: #fff;
    }
    /* Tombol footer: tinggi & margin disamakan supaya Batal dan Simpan sejajar */
    .k-modal.k-modal-form .k-modal-foot { align-items: center; }
    .k-modal.k-modal-form .k-modal-foot .k-btn,
    .k-modal.k-modal-form .k-modal-foot .k-btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        box-sizing: border-box; height: 42px; margin: 0; padding: 0 22px;
        font-size: 13.5px; line-height: 1; white-space: nowrap;
    }
    .k-sec-label {
        display: flex; align-items: center; gap: 12px; margin: 2px 0 14px;
        font-size: 13px; font-weight: 700; color: #3f6a4f;
    }
    .k-sec-label::after { content: ""; flex: 1; height: 1px; background: #e3eae5; }
    .k-sec-label:not(:first-child) { margin-top: 10px; }
    .sh-opt { font-weight: 400; color: #8a978f; }
    .sh-form-error { font-size: 12px; color: #c0392b; margin: 0; }

    /* ===== Builder modul ===== */
    .sh-mod-head{
        display:flex;align-items:flex-end;justify-content:space-between;
        flex-wrap:wrap;gap:12px;margin-bottom:14px;
    }
    .sh-mod-head h4{margin:0;font-size:15px;font-weight:700;color:#1f2a24;}
    .sh-mod-head p{margin:4px 0 0;max-width:520px;font-size:12.5px;line-height:1.55;color:#6b7a72;}
    .sh-mod-tools{display:flex;align-items:center;gap:8px;}
    .sh-mod-count{
        font-size:12px;font-weight:600;color:#3f6a4f;background:#e9f4ec;border:1px solid #cfe8d6;
        padding:5px 12px;border-radius:999px;white-space:nowrap;
    }
    .sh-mod-toggle{
        font-family:inherit;font-size:12px;font-weight:600;color:#5c6a62;background:#fff;
        border:1px solid #d8dfda;padding:5px 12px;border-radius:999px;cursor:pointer;white-space:nowrap;
    }
    .sh-mod-toggle:hover{background:#f4f7f4;}
    .sh-mod-toggle[hidden]{display:none;}

    .sh-blk-wrap{display:flex;flex-direction:column;gap:12px;}
    .sh-blk-empty{
        border:1px dashed #c9d8cd;border-radius:14px;padding:28px 16px;text-align:center;
        color:#8a978f;font-size:13px;background:#fafcfa;
    }
    .sh-blk{
        --c:#7fa285;
        background:#fff;border:1px solid #dfe7e1;border-left:4px solid var(--c);border-radius:14px;
        overflow:hidden;box-shadow:0 1px 3px rgba(31,42,36,.05);
        transition:box-shadow .15s ease,border-color .15s ease,opacity .15s ease;
    }
    .sh-blk:hover{box-shadow:0 4px 14px rgba(31,42,36,.08);}
    .sh-blk.is-dragging{opacity:.4;}
    .sh-blk.drop-before{box-shadow:0 -3px 0 #7fa285;}
    .sh-blk.drop-after{box-shadow:0 3px 0 #7fa285;}
    .sh-blk.is-invalid{border-color:#e4a29b;border-left-color:#c0392b;box-shadow:0 0 0 3px rgba(192,57,43,.08);}

    .sh-blk-head{
        display:flex;align-items:center;gap:10px;padding:10px 12px;
        background:#fbfcfb;cursor:pointer;user-select:none;
    }
    .sh-blk-handle{
        display:flex;padding:5px 3px;border-radius:6px;color:#a3afa8;cursor:grab;flex-shrink:0;
    }
    .sh-blk-handle:hover{background:#e9eee9;color:#5c6a62;}
    .sh-blk-handle:active{cursor:grabbing;}
    .sh-blk-handle svg{width:14px;height:14px;}
    .sh-blk-no{
        width:24px;height:24px;border-radius:50%;background:var(--c);color:#fff;
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
    .sh-blk-note{margin:0;font-size:12px;color:#8a978f;line-height:1.5;}
    .sh-blk-note.ok{color:#1f7a44;}
    .sh-blk-note.bad{color:#c0392b;}
    .sh-blk-video{
        display:flex;align-items:center;gap:12px;background:#f7f9f7;
        border:1px solid #e6ebe7;border-radius:10px;padding:8px;
    }
    .sh-blk-video[hidden]{display:none;}
    .sh-blk-thumb{width:136px;aspect-ratio:16/9;object-fit:cover;border-radius:7px;background:#dfe5e0;flex-shrink:0;}
    .sh-blk-video span{font-size:12.5px;color:#5c6a62;line-height:1.5;}

    /* Daftar pertanyaan dalam satu langkah */
    .sh-sq-list{display:flex;flex-direction:column;gap:8px;}
    .sh-sq{
        display:grid;grid-template-columns:26px 1fr 30px;gap:10px;align-items:start;
        background:#fffdf4;border:1px solid #f0e3a8;border-radius:11px;padding:10px;
    }
    .sh-sq-no{
        width:26px;height:26px;border-radius:50%;background:#fdf1c2;color:#8a6100;
        font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:6px;
    }
    .sh-blk .sh-sq textarea.sh-in{min-height:0;resize:vertical;}
    .sh-sq-del{
        width:30px;height:30px;margin-top:5px;border:none;border-radius:8px;background:#fdeeed;color:#c0392b;
        cursor:pointer;display:inline-flex;align-items:center;justify-content:center;
    }
    .sh-sq-del:hover{background:#fadedb;}
    .sh-sq-del svg{width:14px;height:14px;}
    .sh-sq-add{
        width:100%;font-family:inherit;font-size:13px;font-weight:600;color:#8a6100;background:#fff;
        border:1px dashed #e0c55f;padding:9px 14px;border-radius:9px;cursor:pointer;
    }
    .sh-sq-add:hover{background:#fffaf0;}

    /* Unggah PDF per langkah */
    .sh-pdf-input{display:none;}
    .sh-pdf-drop{
        display:flex;flex-direction:column;align-items:center;gap:4px;text-align:center;
        padding:24px 16px;border:2px dashed #cfb9ec;border-radius:12px;background:#faf6ff;
        cursor:pointer;transition:border-color .15s ease,background .15s ease;
    }
    .sh-pdf-drop[hidden]{display:none;}
    .sh-pdf-drop:hover,.sh-pdf-drop.is-over{border-color:#8b4fd0;background:#f3e8ff;}
    .sh-pdf-drop-ic{
        width:42px;height:42px;border-radius:12px;background:#f3e8ff;color:#6b21a8;margin-bottom:4px;
        display:flex;align-items:center;justify-content:center;
    }
    .sh-pdf-drop-ic svg{width:20px;height:20px;}
    .sh-pdf-drop b{font-size:13.5px;color:#3b1d63;font-weight:600;}
    .sh-pdf-drop small{font-size:12px;color:#8a978f;}
    .sh-pdf-card{
        display:flex;align-items:center;gap:12px;padding:12px;
        border:1px solid #e3d3f5;background:#faf6ff;border-radius:12px;
    }
    .sh-pdf-card[hidden]{display:none;}
    .sh-pdf-file-ic{
        width:40px;height:40px;border-radius:10px;background:#f3e8ff;color:#6b21a8;flex-shrink:0;
        display:flex;align-items:center;justify-content:center;
    }
    .sh-pdf-file-ic svg{width:20px;height:20px;}
    .sh-pdf-meta{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px;}
    .sh-pdf-meta b{
        font-size:13px;font-weight:600;color:#1f2a24;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .sh-pdf-meta small{font-size:12px;color:#7a8a80;}
    .sh-pdf-btns{display:flex;gap:6px;flex-shrink:0;}
    .sh-pdf-btn{
        display:inline-flex;align-items:center;gap:5px;font-family:inherit;font-size:12px;font-weight:600;
        padding:6px 10px;border-radius:8px;border:1px solid #d8dfda;background:#fff;color:#3a463f;cursor:pointer;
    }
    .sh-pdf-btn svg{width:13px;height:13px;}
    .sh-pdf-btn:hover{background:#f4f7f4;}
    .sh-pdf-btn.is-del{color:#c0392b;border-color:#f6cfcc;background:#fdeeed;}
    .sh-pdf-btn.is-del:hover{background:#fadedb;}

    /* Pemilih jenis langkah */
    .sh-add-bar{margin-top:14px;border:1px dashed #b7cbbc;border-radius:14px;padding:14px;background:#f8fbf8;}
    .sh-add-bar p{margin:0 0 10px;font-size:12.5px;font-weight:600;color:#3f6a4f;}
    .sh-add-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;}
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
    @media (max-width: 640px) {
        .k-modal.k-modal-form { padding: 0; border-radius: 16px; }
        .k-modal-form .k-modal-head, .k-modal.k-modal-form .k-modal-foot { padding-left: 16px; padding-right: 16px; }
        .k-modal-body { padding: 16px 16px 4px; }
    }
    @media (max-width: 480px) {
        .sh-blk-thumb { width: 96px; }
        .sh-blk-type span.sh-blk-type-label { display: none; }
        .sh-pdf-card { flex-wrap: wrap; }
        .sh-pdf-btns { width: 100%; }
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
            if (window.kMuatBlok) window.kMuatBlok([]);
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
            var aktif = sec.dataset.tipe === tipe;
            sec.style.display = aktif ? '' : 'none';
            // Isian jenis lain di-disable supaya tidak ikut divalidasi browser dan tidak terkirim.
            sec.querySelectorAll('input, textarea, select').forEach(function (el) { el.disabled = !aktif; });
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
    var list = document.getElementById('kBlokList'),
        hidden = document.getElementById('kBlokJson'),
        form = document.getElementById('kFormMateri'),
        empty = document.getElementById('kBlokEmpty'),
        errBox = document.getElementById('kBlokError'),
        grid = document.getElementById('kAddGrid'),
        countEl = document.getElementById('kBlokCount'),
        toggleBtn = document.getElementById('kBlokToggle');
    if (!list || !hidden || !form) return;

    var MAX_PDF = 10 * 1024 * 1024, uidSeq = 0, dragEl = null;

    function svg(path, fill) {
        return '<svg viewBox="0 0 24 24" fill="' + (fill ? 'currentColor' : 'none') + '" stroke="' + (fill ? 'none' : 'currentColor') +
               '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + path + '</svg>';
    }
    var ICON = {
        grip: svg('<circle cx="9" cy="6" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="18" r="1.4"/>', true),
        up: svg('<path d="M12 19V5M5 12l7-7 7 7"/>'),
        down: svg('<path d="M12 5v14M19 12l-7 7-7-7"/>'),
        del: svg('<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/>'),
        chev: svg('<path d="m6 9 6 6 6-6"/>'),
        upload: svg('<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>'),
        swap: svg('<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>'),
        x: svg('<path d="M18 6 6 18M6 6l12 12"/>')
    };

    // Format blok_json: {tipe, judul, isi|url|pertanyaan|instruksi, keterangan (video)}.
    // Langkah PDF: {tipe:'pdf', judul, file_pdf (path lama), file_id, nama_file, jumlah_halaman}.
    // File PDF-nya dikirim terpisah sebagai file_pdf_blok[file_id].
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
            bodyLabel: 'Daftar pertanyaan', bodyPh: '',
            note: 'Satu langkah boleh berisi beberapa pertanyaan. Konseli menjawab tiap pertanyaan pada kolom isian sendiri. Tekan Enter untuk menambah pertanyaan berikutnya.'
        },
        kegiatan: {
            label: 'Kegiatan', desc: 'Tugas atau latihan', key: 'instruksi', bg: '#ece6fa', fg: '#5b3fa0',
            icon: svg('<path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'),
            judulPh: 'mis. Latihan menulis jurnal',
            bodyLabel: 'Instruksi kegiatan', bodyPh: 'mis. Tarik napas 4 hitungan, tahan 4 hitungan, hembuskan 6 hitungan. Ulangi selama 3 menit.',
            note: 'Tulis langkah-langkahnya sejelas mungkin supaya mudah diikuti sendiri.'
        },
        pdf: {
            label: 'PDF', desc: 'Dokumen yang dibaca konseli', key: 'file_pdf', bg: '#f3e8ff', fg: '#6b21a8',
            icon: svg('<path d="M6 2h9l3 3v17H6z"/><path d="M15 2v4h4M8 13h8M8 17h6"/>'),
            judulPh: 'mis. Lembar latihan bagian 1',
            note: 'Format PDF, maksimal 10 MB per file. Untuk PDF lain, tambahkan langkah PDF baru.'
        }
    };
    var ORDER = ['teks', 'pdf', 'video', 'soal', 'kegiatan'];

    /* ---------- helper ---------- */
    function ytId(u) {
        var m = String(u || '').trim().match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/);
        return m ? m[1] : '';
    }
    function basename(p) { return String(p || '').split(/[\\/]/).pop(); }
    function fmtSize(n) {
        if (n >= 1048576) return (n / 1048576).toFixed(1).replace('.', ',') + ' MB';
        return Math.max(1, Math.round(n / 1024)) + ' KB';
    }
    function newUid() { uidSeq++; return 'f' + Date.now().toString(36) + uidSeq; }
    function field(row, f) { return row.querySelector('[data-f="' + f + '"]'); }
    function fileOf(row) {
        var i = field(row, 'file');
        return i && i.files && i.files[0] ? i.files[0] : null;
    }
    // Langkah Pertanyaan: beberapa pertanyaan disimpan ke satu nilai 'pertanyaan',
    // dipisah satu baris per pertanyaan (format sama seperti sebelumnya).
    function qList(row) { return row.querySelector('[data-qlist]'); }
    function qRenumber(row) {
        var items = qList(row).querySelectorAll('.sh-sq');
        items.forEach(function (it, i) {
            it.querySelector('.sh-sq-no').textContent = i + 1;
            it.querySelector('.sh-sq-del').style.visibility = items.length > 1 ? 'visible' : 'hidden';
        });
    }
    function qAdd(row, text, after) {
        var it = document.createElement('div');
        it.className = 'sh-sq';
        it.innerHTML = '<span class="sh-sq-no"></span>' +
            '<textarea class="sh-in" data-q rows="2" placeholder="Tulis pertanyaan..."></textarea>' +
            '<button type="button" class="sh-sq-del" title="Hapus pertanyaan" aria-label="Hapus pertanyaan">' + ICON.x + '</button>';
        var ta = it.querySelector('textarea');
        ta.value = text || '';
        ta.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || e.isComposing) return;
            e.preventDefault();
            qAdd(row, '', it).focus();
            sync();
        });
        it.querySelector('.sh-sq-del').addEventListener('click', function () {
            if (qList(row).querySelectorAll('.sh-sq').length > 1) it.remove(); else ta.value = '';
            qRenumber(row); updateSum(row); sync();
        });
        var box = qList(row);
        box.insertBefore(it, after ? after.nextSibling : null);
        qRenumber(row);
        return ta;
    }
    function qValue(row) {
        return Array.prototype.map.call(qList(row).querySelectorAll('[data-q]'), function (t) {
            return t.value.replace(/\s*\n+\s*/g, ' ').trim();
        }).filter(Boolean).join('\n');
    }
    function bodyValue(row) {
        return row.dataset.tipe === 'soal' ? qValue(row) : field(row, 'body').value;
    }

    function showError(msg) { errBox.textContent = msg; errBox.style.display = msg ? '' : 'none'; }

    /* ---------- sinkronisasi ke input hidden ---------- */
    function refreshCount() {
        var n = list.querySelectorAll('.sh-blk').length,
            pdfs = list.querySelectorAll('.sh-blk[data-tipe="pdf"]').length;
        if (countEl) countEl.textContent = n + ' langkah' + (pdfs ? ' · ' + pdfs + ' PDF' : '');
        if (toggleBtn) {
            toggleBtn.hidden = n < 2;
            toggleBtn.textContent = list.querySelector('.sh-blk:not(.is-collapsed)') ? 'Ciutkan semua' : 'Buka semua';
        }
    }

    function sync() {
        var out = [];
        list.querySelectorAll('.sh-blk').forEach(function (row) {
            var tipe = row.dataset.tipe, b = { tipe: tipe, judul: field(row, 'judul').value };
            if (tipe === 'pdf') {
                var p = row._pdf, f = fileOf(row);
                b.file_pdf = p.path || '';
                b.file_id = row.dataset.uid;
                b.nama_file = f ? f.name : (p.nama || '');
                b.jumlah_halaman = p.halaman || 0;
            } else {
                b[TYPES[tipe].key] = bodyValue(row);
                if (tipe === 'video') { var d = field(row, 'desc'); b.keterangan = d ? d.value : ''; }
            }
            out.push(b);
        });
        hidden.value = JSON.stringify(out);
        refreshCount();
    }

    function renumber() {
        var rows = list.querySelectorAll('.sh-blk');
        rows.forEach(function (row, i) {
            row.querySelector('[data-no]').textContent = i + 1;
            row.querySelector('[data-up]').disabled = (i === 0);
            row.querySelector('[data-down]').disabled = (i === rows.length - 1);
        });
        empty.style.display = rows.length ? 'none' : '';
    }

    function pdfName(row) {
        var f = fileOf(row), p = row._pdf;
        return f ? f.name : (p.nama || basename(p.path));
    }

    function updateSum(row) {
        var tipe = row.dataset.tipe, judul = field(row, 'judul').value.trim(), text = judul, suffix = '';
        if (tipe === 'soal') {
            var qs = qValue(row).split('\n').filter(Boolean);
            if (!text) text = qs[0] || '';
            if (qs.length > 1) suffix = ' (' + qs.length + ' pertanyaan)';
        } else if (!text) {
            text = tipe === 'pdf'
                ? pdfName(row)
                : field(row, 'body').value.replace(/\s+/g, ' ').trim();
        }
        if (text.length > 80) text = text.slice(0, 80) + '...';
        text += text ? suffix : '';
        var sum = row.querySelector('[data-sum]');
        sum.textContent = text || 'Belum diisi';
        sum.classList.toggle('is-empty', !text);
    }

    function updateVideo(row) {
        var box = row.querySelector('[data-video]'), note = row.querySelector('[data-vnote]');
        if (!box) return;
        var v = field(row, 'body').value.trim(), id = ytId(v);
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

    /* ---------- PDF per langkah ---------- */
    function setPnote(row, msg, cls) {
        var n = row.querySelector('[data-pnote]');
        if (!n) return;
        n.textContent = msg;
        n.className = 'sh-blk-note' + (cls ? ' ' + cls : '');
    }

    function paintPdf(row) {
        var p = row._pdf, f = fileOf(row), ada = !!(f || p.path);
        row.querySelector('[data-drop]').hidden = ada;
        row.querySelector('[data-card]').hidden = !ada;
        if (!ada) return;
        row.querySelector('[data-pname]').textContent = pdfName(row);
        var info = [f ? fmtSize(f.size) : 'Tersimpan sebelumnya'];
        if (p.halaman) info.push(p.halaman + ' halaman');
        row.querySelector('[data-pinfo]').textContent = info.join(' · ');
    }

    function readPages(row, file) {
        if (!window.pdfjsLib) return;
        var reader = new FileReader();
        reader.onload = function () {
            window.pdfjsLib.getDocument({ data: new Uint8Array(reader.result) }).promise.then(function (pdf) {
                if (fileOf(row) !== file) return;
                row._pdf.halaman = pdf.numPages;
                paintPdf(row); sync();
            }).catch(function () {});
        };
        reader.readAsArrayBuffer(file);
    }

    function onFile(row, file) {
        var p = row._pdf, inp = field(row, 'file');
        if (!file) {
            if (!p.path) { p.nama = ''; p.halaman = 0; }
            paintPdf(row); updateSum(row); sync();
            return;
        }
        if (file.type !== 'application/pdf' && !/\.pdf$/i.test(file.name)) {
            inp.value = '';
            setPnote(row, 'File harus berformat PDF.', 'bad');
        } else if (file.size > MAX_PDF) {
            inp.value = '';
            setPnote(row, 'Ukuran file ' + fmtSize(file.size) + ' melebihi batas 10 MB.', 'bad');
        } else {
            p.nama = file.name; p.halaman = 0;
            row.classList.remove('is-invalid');
            setPnote(row, TYPES.pdf.note, '');
            readPages(row, file);
        }
        paintPdf(row); updateSum(row); sync();
    }

    function wirePdf(row) {
        var inp = field(row, 'file'), drop = row.querySelector('[data-drop]');
        inp.addEventListener('change', function () { onFile(row, inp.files && inp.files[0]); });
        row.querySelector('[data-pdel]').addEventListener('click', function () {
            inp.value = '';
            row._pdf.path = ''; row._pdf.nama = ''; row._pdf.halaman = 0;
            setPnote(row, TYPES.pdf.note, '');
            paintPdf(row); updateSum(row); sync();
        });
        ['dragenter', 'dragover'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                if (dragEl) return;
                e.preventDefault();
                drop.classList.add('is-over');
            });
        });
        drop.addEventListener('dragleave', function () { drop.classList.remove('is-over'); });
        drop.addEventListener('drop', function (e) {
            if (dragEl) return;
            e.preventDefault();
            drop.classList.remove('is-over');
            var files = e.dataTransfer && e.dataTransfer.files;
            if (!files || !files.length) return;
            try { inp.files = files; } catch (err) { return; }
            onFile(row, inp.files[0]);
        });
    }

    /* ---------- membangun satu langkah ---------- */
    function build(tipe, uid) {
        var t = TYPES[tipe];
        var row = document.createElement('div');
        row.className = 'sh-blk';
        row.dataset.tipe = tipe;
        row.dataset.uid = uid;
        row.style.setProperty('--c', t.fg);

        var bodyField;
        if (tipe === 'video') {
            bodyField =
                '<div class="sh-blk-field"><label>' + t.bodyLabel + '</label>' +
                '<input class="sh-in" data-f="body" type="text" inputmode="url" autocomplete="off" placeholder="' + t.bodyPh + '">' +
                '<label>Keterangan video</label><textarea class="sh-in" data-f="desc" rows="3" placeholder="Jelaskan isi atau tujuan video ini..."></textarea>' +
                '<p class="sh-blk-note" data-vnote></p></div>' +
                '<div class="sh-blk-video" data-video hidden><img alt="" class="sh-blk-thumb"><span>Pratinjau video. Pastikan video ini boleh disematkan.</span></div>';
        } else if (tipe === 'soal') {
            bodyField =
                '<div class="sh-blk-field"><label>' + t.bodyLabel + '</label>' +
                '<div class="sh-sq-list" data-qlist></div>' +
                '<button type="button" class="sh-sq-add" data-qadd>+ Tambah pertanyaan</button>' +
                '<p class="sh-blk-note">' + t.note + '</p></div>';
        } else if (tipe === 'pdf') {
            var id = 'shPdf_' + uid;
            bodyField =
                '<div class="sh-blk-field"><label>File PDF</label>' +
                '<input class="sh-pdf-input" data-f="file" type="file" id="' + id + '" name="file_pdf_blok[' + uid + ']" accept="application/pdf">' +
                '<label class="sh-pdf-drop" for="' + id + '" data-drop>' +
                    '<span class="sh-pdf-drop-ic">' + ICON.upload + '</span>' +
                    '<b>Pilih file PDF</b><small>atau seret file ke sini. Maksimal 10 MB.</small>' +
                '</label>' +
                '<div class="sh-pdf-card" data-card hidden>' +
                    '<span class="sh-pdf-file-ic">' + t.icon + '</span>' +
                    '<span class="sh-pdf-meta"><b data-pname></b><small data-pinfo></small></span>' +
                    '<span class="sh-pdf-btns">' +
                        '<label class="sh-pdf-btn" for="' + id + '">' + ICON.swap + 'Ganti</label>' +
                        '<button type="button" class="sh-pdf-btn is-del" data-pdel>' + ICON.x + 'Hapus</button>' +
                    '</span>' +
                '</div>' +
                '<p class="sh-blk-note" data-pnote>' + t.note + '</p></div>';
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

    function add(b, collapsed) {
        b = b || {};
        var tipe = TYPES[b.tipe] ? b.tipe : 'teks', t = TYPES[tipe];
        var row = build(tipe, b.file_id || newUid());
        list.appendChild(row);

        field(row, 'judul').value = b.judul || '';
        if (tipe === 'pdf') {
            row._pdf = { path: b.file_pdf || '', nama: b.nama_file || '', halaman: parseInt(b.jumlah_halaman, 10) || 0 };
            wirePdf(row);
            paintPdf(row);
        } else if (tipe === 'soal') {
            var qs = String(b.pertanyaan || '').split(/\r?\n/).map(function (q) { return q.trim(); }).filter(Boolean);
            if (!qs.length) qs = [''];
            qs.forEach(function (q) { qAdd(row, q); });
            row.querySelector('[data-qadd]').addEventListener('click', function () {
                var items = qList(row).querySelectorAll('.sh-sq');
                qAdd(row, '', items[items.length - 1]).focus();
                sync();
            });
        } else {
            field(row, 'body').value = b[t.key] || '';
            if (tipe === 'video' && field(row, 'desc')) field(row, 'desc').value = b.keterangan || '';
            updateVideo(row);
        }
        updateSum(row);
        if (collapsed) row.classList.add('is-collapsed');

        row.addEventListener('input', function () {
            row.classList.remove('is-invalid');
            updateSum(row);
            updateVideo(row);
            sync();
        });
        row.querySelector('[data-head]').addEventListener('click', function (e) {
            if (e.target.closest('[data-tool]') || e.target.closest('[data-handle]')) return;
            row.classList.toggle('is-collapsed');
            refreshCount();
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
            if (e.target !== row) return;
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

    /* ---------- tombol tambah langkah ---------- */
    ORDER.forEach(function (key) {
        var t = TYPES[key];
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sh-add-btn';
        btn.innerHTML = '<span class="sh-add-ic" style="background:' + t.bg + ';color:' + t.fg + ';">' + t.icon + '</span>' +
                        '<span class="sh-add-txt"><b>' + t.label + '</b><small>' + t.desc + '</small></span>';
        btn.addEventListener('click', function () { window.kTambahBlok(key); });
        grid.appendChild(btn);
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var anyOpen = !!list.querySelector('.sh-blk:not(.is-collapsed)');
            list.querySelectorAll('.sh-blk').forEach(function (r) { r.classList.toggle('is-collapsed', anyOpen); });
            refreshCount();
        });
    }

    window.kTambahBlok = function (tipe) {
        var row = add({ tipe: TYPES[tipe] ? tipe : 'teks' });
        showError('');
        renumber(); sync();
        row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        field(row, 'judul').focus({ preventScroll: true });
    };
    window.kMuatBlok = function (items) {
        list.innerHTML = '';
        showError('');
        var arr = Array.isArray(items) ? items : [];
        var collapse = arr.length > 2;
        arr.forEach(function (b) { add(b, collapse); });
        renumber(); sync();
    };

    // Validasi sebelum kirim (hanya untuk jenis Modul)
    form.addEventListener('submit', function (e) {
        sync();
        var tipe = document.getElementById('kFieldType');
        if (!tipe || tipe.value !== 'modul') return;
        var rows = list.querySelectorAll('.sh-blk');
        if (!rows.length) {
            e.preventDefault();
            showError('Tambahkan minimal satu langkah untuk modul ini.');
            return;
        }
        var first = null;
        rows.forEach(function (r) {
            var kosong;
            if (r.dataset.tipe === 'pdf') {
                kosong = !fileOf(r) && !r._pdf.path;
                if (kosong) setPnote(r, 'Pilih file PDF untuk langkah ini.', 'bad');
            } else {
                kosong = bodyValue(r).trim() === '';
            }
            r.classList.toggle('is-invalid', kosong);
            if (kosong && !first) first = r;
        });
        if (first) {
            e.preventDefault();
            first.classList.remove('is-collapsed');
            refreshCount();
            showError('Ada langkah yang belum diisi. Lengkapi atau hapus langkah yang ditandai merah.');
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var target = first.dataset.tipe === 'pdf' ? field(first, 'judul')
                : first.dataset.tipe === 'soal' ? qList(first).querySelector('[data-q]')
                : field(first, 'body');
            target.focus({ preventScroll: true });
        } else {
            showError('');
        }
    });

    try { window.kMuatBlok(JSON.parse(hidden.value || '[]')); } catch (e) { window.kMuatBlok([]); }
    if (window.kApplyTipeMateri) window.kApplyTipeMateri();
})();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>if (window.pdfjsLib) window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

@endsection