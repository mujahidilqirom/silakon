@extends('layouts.app')

@section('title', 'Self Help — SILAKON LPPP UNNES')

@section('content')

@php
    /*
     * Foto dari Unsplash (lisensi gratis). Cukup ganti ID di bawah bila ingin foto lain.
     * Kalau 'photo' diisi null / gambar gagal dimuat, kartu otomatis memakai
     * latar gradasi + ikon sesuai jenis materi.
     */
    $unsplash = fn ($id) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=800&h=400&q=70";

    // Jenis materi (key harus sama dengan 'type' pada data materi)
    $types = [
        'modul' => ['label' => 'Modul Terpadu', 'icon' => '<path d="M4 5a2 2 0 0 1 2-2h14v17H6a2 2 0 0 0-2 2V5Z"/><path d="M4 20a2 2 0 0 1 2-2h14"/>'],
    ];

    // Data materi. Jika dikirim dari controller ($resources), data dummy ini tidak dipakai.
    // ID di sini harus sama dengan ID pada $resources di self-help-detail_blade.php
    $resources = $resources ?? [
        ['id' => 1, 'type' => 'artikel',   'topic' => 'Kesehatan Mental', 'meta' => '8 menit baca', 'author' => 'Dr. Sari Dewi',
         'title' => 'Mengatasi Kecemasan Ujian: Panduan Praktis untuk Mahasiswa', 'photo' => null],
        ['id' => 2, 'type' => 'video',     'topic' => 'Akademik',         'meta' => '15 menit',     'author' => 'Dr. Rina Kusumawati',
         'title' => 'Teknik Pomodoro: Belajar Efektif dengan Manajemen Waktu', 'photo' => null],
        ['id' => 3, 'type' => 'worksheet', 'topic' => 'Kesehatan Mental', 'meta' => '5 pertanyaan', 'author' => 'Dr. Sari Dewi',
         'title' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri', 'photo' => $unsplash('1553044020-8c90843adf96')],
        ['id' => 4, 'type' => 'artikel',   'topic' => 'Pribadi',          'meta' => '7 menit baca', 'author' => 'Drs. Agus Santoso',
         'title' => 'Membangun Kepercayaan Diri: Panduan Pengembangan Diri', 'photo' => $unsplash('1512314889357-e157c22f938d')],
        ['id' => 5, 'type' => 'video',     'topic' => 'Kesehatan Mental', 'meta' => '10 menit',     'author' => 'Dr. Sari Dewi',
         'title' => 'Teknik Pernapasan untuk Mengelola Stres Seketika', 'photo' => $unsplash('1544367567-0f2fcb009e0b')],
        ['id' => 6, 'type' => 'worksheet', 'topic' => 'Karier',           'meta' => '6 pertanyaan', 'author' => 'Dr. Rina Kusumawati',
         'title' => 'Refleksi Karier: Menemukan Arah Setelah Lulus', 'photo' => $unsplash('1698047681432-006d2449c631')],
        ['id' => 7, 'type' => 'artikel',   'topic' => 'Sosial',           'meta' => '6 menit baca', 'author' => 'Drs. Agus Santoso',
         'title' => 'Seni Berkomunikasi Asertif di Lingkungan Kampus', 'photo' => $unsplash('1511632765486-a01980e01a18')],
        ['id' => 8, 'type' => 'video',     'topic' => 'Karier',           'meta' => '12 menit',     'author' => 'Dr. Rina Kusumawati',
         'title' => 'Panduan Menulis CV dan Surat Lamaran yang Memikat', 'photo' => null],
        ['id' => 9, 'type' => 'worksheet', 'topic' => 'Akademik',         'meta' => '5 pertanyaan', 'author' => 'Prof. Bambang Widodo',
         'title' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu', 'photo' => $unsplash('1484480974693-6ca69a5724a2')],
    ];

    $topics = collect($resources)->pluck('topic')->unique()->sort()->values();
@endphp

{{-- ===================== HERO ===================== --}}
<section class="page-hero">
    <div class="container">
        <span class="page-hero-badge">Self Help</span>
        <h1>Sumber Daya Mandiri</h1>
        <p>
            Koleksi artikel, video, dan worksheet interaktif yang dikurasi oleh
            konselor profesional untuk mendukung perjalanan pengembangan diri Anda.
        </p>

        <div class="sh-search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" id="shSearch" placeholder="Cari materi self help..." autocomplete="off" aria-label="Cari materi self help">
        </div>
    </div>
</section>

{{-- ===================== DAFTAR MATERI ===================== --}}
<section class="section-pad sh-section">
    <div class="container">

        <div class="sh-toolbar">
            <div class="sh-tabs" role="tablist">
                <button type="button" class="sh-tab active" data-type="all">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                    Semua
                </button>
                @foreach ($types as $key => $type)
                    <button type="button" class="sh-tab" data-type="{{ $key }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $type['icon'] !!}</svg>
                        {{ $type['label'] }}
                    </button>
                @endforeach
            </div>

            <select id="shTopic" class="sh-select" aria-label="Filter topik">
                <option value="all">Semua Topik</option>
                @foreach ($topics as $topic)
                    <option value="{{ $topic }}">{{ $topic }}</option>
                @endforeach
            </select>
        </div>

        <p class="sh-count">
            Menampilkan <span id="shShown">{{ count($resources) }}</span>
            dari <span id="shTotal">{{ count($resources) }}</span> materi
        </p>

        <div class="sh-grid">
            @foreach ($resources as $r)
                @php $type = $types[$r['type']]; @endphp

                <a href="{{ url('/self-help/' . $r['id']) }}"
                   class="sh-card"
                   data-type="{{ $r['type'] }}"
                   data-topic="{{ $r['topic'] }}"
                   data-search="{{ mb_strtolower($r['title'] . ' ' . $r['topic'] . ' ' . $r['author'] . ' ' . $type['label']) }}">

                    <div class="sh-media sh-media-{{ $r['type'] }}">
                        {{-- ikon cadangan (tampil bila foto kosong / gagal dimuat) --}}
                        <svg class="sh-media-fallback" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">{!! $type['icon'] !!}</svg>

                        @if (!empty($r['photo']))
                            <img src="{{ $r['photo'] }}" alt="{{ $r['title'] }}" loading="lazy" onerror="this.remove()">
                        @endif

                        <span class="sh-type sh-type-{{ $r['type'] }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $type['icon'] !!}</svg>
                            {{ $type['label'] }}
                        </span>

                        @if ($r['type'] === 'video')
                            <span class="sh-play" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="8 5 19 12 8 19 8 5"/></svg>
                            </span>
                        @endif
                    </div>

                    <div class="sh-body">
                        <div class="sh-meta">
                            <span class="sh-topic">{{ $r['topic'] }}</span>
                            <span class="sh-duration">{{ $r['meta'] }}</span>
                        </div>
                        <h3>{{ $r['title'] }}</h3>
                        @if (!empty($r['description']))
                            <p class="sh-description">{{ IlluminateSupportStr::limit($r['description'], 110) }}</p>
                        @endif
                        <p class="sh-author">oleh {{ $r['author'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <p class="sh-empty" id="shEmpty" hidden>
            Tidak ada materi yang cocok. Coba ubah kata kunci atau filter Anda.
        </p>
    </div>
</section>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Self Help)

     Dipakai ulang dari landing: .container .section-pad
     Blok .page-hero di bawah sama dengan halaman Panduan / Konseling Kelompok;
     kalau sudah ada di CSS global, blok itu boleh dihapus dari sini.
     ===================================================================== --}}
<style>
    /* ---------- Hero ---------- */
    .page-hero {
        background: #E9F3EA;
        padding: 46px 0 46px;
        text-align: center;
    }
    .page-hero-badge {
        display: inline-block;
        background: #D7EBDB;
        color: #2F6B3F;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 999px;
        margin-bottom: 18px;
    }
    .page-hero h1 {
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 700;
        line-height: 1.2;
        margin: 0 0 14px;
    }
    .page-hero p {
        max-width: 560px;
        margin: 0 auto;
        color: #6B7280;
        font-size: 16px;
        line-height: 1.6;
    }

    /* ---------- Search ---------- */
    .sh-search {
        position: relative;
        max-width: 500px;
        margin: 32px auto 0;
    }
    .sh-search svg {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        pointer-events: none;
    }
    .sh-search input {
        width: 100%;
        height: 50px;
        padding: 0 20px 0 48px;
        font: inherit;
        font-size: 15px;
        color: #1F2937;
        background: #fff;
        border: 1px solid #DDE3D8;
        border-radius: 12px;
        outline: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        transition: border-color .15s, box-shadow .15s;
    }
    .sh-search input::placeholder { color: #9CA3AF; }
    .sh-search input:focus {
        border-color: var(--primary, #7DA381);
        box-shadow: 0 0 0 4px rgba(125, 163, 129, .18);
    }

    /* ---------- Toolbar: tab jenis + filter topik ---------- */
    .sh-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }
    .sh-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .sh-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        font: inherit;
        font-size: 13.5px;
        color: #4B5563;
        background: #fff;
        border: 1px solid #D9DED4;
        border-radius: 999px;
        cursor: pointer;
        transition: background .15s, color .15s, border-color .15s;
    }
    .sh-tab:hover { border-color: var(--primary, #7DA381); }
    .sh-tab.active {
        background: var(--primary, #7DA381);
        border-color: var(--primary, #7DA381);
        color: #fff;
    }
    .sh-select {
        height: 38px;
        padding: 0 34px 0 14px;
        font: inherit;
        font-size: 13.5px;
        color: #374151;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        border: 1px solid #D9DED4;
        border-radius: 10px;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        outline: none;
    }
    .sh-select:focus { border-color: var(--primary, #7DA381); }

    .sh-count {
        font-size: 14px;
        color: #6B7280;
        margin: 0 0 22px;
    }

    /* ---------- Grid & kartu ---------- */
    .sh-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .sh-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 16px;
        overflow: hidden;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        transition: transform .2s, box-shadow .2s;
    }
    .sh-card[hidden] { display: none; }
    .sh-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .09);
    }

    /* area gambar */
    .sh-media {
        position: relative;
        aspect-ratio: 2 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .sh-media img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s;
    }
    .sh-card:hover .sh-media img { transform: scale(1.04); }
    .sh-media-fallback { opacity: .45; }

    /* warna cadangan per jenis */
    .sh-media-artikel   { background: linear-gradient(135deg, #EAF1FB, #D6E4F6); color: #3B6FB6; }
    .sh-media-video     { background: linear-gradient(135deg, #ECEFF2, #D5DBE1); color: #64748B; }
    .sh-media-worksheet { background: linear-gradient(135deg, #FDF4DA, #F6E5B0); color: #B7861A; }

    /* badge jenis (kiri atas) */
    .sh-type {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 999px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
    }
    .sh-type-artikel   { background: #DBEAFE; color: #1E40AF; }
    .sh-type-video     { background: rgba(243, 244, 246, .95); color: #374151; }
    .sh-type-worksheet { background: #FEF3C7; color: #92400E; }

    /* tombol play (video) */
    .sh-play {
        position: absolute;
        z-index: 2;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4B5563;
        background: rgba(255, 255, 255, .88);
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .18);
        padding-left: 3px;
        transition: transform .2s;
    }
    .sh-card:hover .sh-play { transform: scale(1.08); }

    /* isi kartu */
    .sh-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 16px 20px 20px;
    }
    .sh-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .sh-topic {
        font-size: 12px;
        font-weight: 600;
        color: #166534;
        background: #E4F5E8;
        padding: 4px 12px;
        border-radius: 999px;
    }
    .sh-duration {
        font-size: 12px;
        color: #9CA3AF;
        white-space: nowrap;
    }
    .sh-body h3 {
        font-size: 15.5px;
        font-weight: 600;
        line-height: 1.45;
        margin: 0 0 10px;
    }
    .sh-author {
        margin: auto 0 0;      /* nama penulis selalu di dasar kartu */
        font-size: 13px;
        color: #9CA3AF;
    }

    /* ---------- Kosong & kredit ---------- */
    .sh-empty {
        text-align: center;
        color: #6B7280;
        padding: 48px 0 8px;
        margin: 0;
    }
    .sh-credit {
        text-align: center;
        font-size: 12px;
        color: #9CA3AF;
        margin: 36px 0 0;
    }
    .sh-credit a { color: inherit; text-decoration: underline; }

    /* ---------- Responsive ---------- */
    @media (max-width: 991px) {
        .sh-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .page-hero { padding: 48px 0 40px; }
        .sh-grid { grid-template-columns: 1fr; }
        .sh-toolbar { align-items: stretch; }
        .sh-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: none;
        }
        .sh-tabs::-webkit-scrollbar { display: none; }
        .sh-tab { flex-shrink: 0; }
        .sh-select { width: 100%; }
    }
</style>

{{-- =====================================================================
     JS TAMBAHAN: filter jenis + topik + pencarian (digabung)
     ===================================================================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var cards    = [].slice.call(document.querySelectorAll('.sh-card'));
        var tabs     = [].slice.call(document.querySelectorAll('.sh-tab'));
        var topicSel = document.getElementById('shTopic');
        var searchEl = document.getElementById('shSearch');
        var shownEl  = document.getElementById('shShown');
        var emptyEl  = document.getElementById('shEmpty');
        var activeType = 'all';

        function apply() {
            var topic = topicSel.value;
            var query = searchEl.value.trim().toLowerCase();
            var shown = 0;

            cards.forEach(function (card) {
                var match =
                    (activeType === 'all' || card.dataset.type === activeType) &&
                    (topic === 'all' || card.dataset.topic === topic) &&
                    (query === '' || card.dataset.search.indexOf(query) !== -1);

                card.hidden = !match;
                if (match) shown++;
            });

            shownEl.textContent = shown;
            emptyEl.hidden = shown !== 0;
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');
                activeType = tab.dataset.type;
                apply();
            });
        });

        topicSel.addEventListener('change', apply);
        searchEl.addEventListener('input', apply);
    });
</script>

@endsection
