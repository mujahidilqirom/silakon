@extends('layouts.app')

@section('title', 'Konseling Kelompok — SILAKON LPPP UNNES')

@section('content')

@php
    /*
     * Data program. Jika nanti dikirim dari controller (variabel $programs),
     * data dummy di bawah otomatis tidak dipakai.
     *
     * tone : warna tag -> blue | green | purple | yellow | gray | pink
     */
    $programs = $programs ?? [
        [
            'id' => 1, 'tone' => 'blue',
            'tags' => ['Kesehatan Mental', 'Akademik'],
            'title' => 'Manajemen Stres Akademik',
            'desc' => 'Program konseling kelompok yang membahas teknik-teknik efektif untuk mengelola stres akibat tekanan akademik, deadline tugas, dan ujian.',
            'date' => '15 Agustus 2025', 'time' => '09.00 – 12.00 WIB',
            'place' => 'Ruang Seminar LPPP, Gedung A Lt. 3', 'counselor' => 'Dr. Sari Dewi, M.Psi',
            'filled' => 14, 'quota' => 20,
        ],
        [
            'id' => 2, 'tone' => 'green',
            'tags' => ['Karier', 'Pengembangan Diri'],
            'title' => 'Perencanaan Karier Pasca Kampus',
            'desc' => 'Sesi interaktif untuk membantu mahasiswa semester akhir dan alumni merencanakan langkah karier, membangun CV profesional, dan mempersiapkan wawancara kerja.',
            'date' => '22 Agustus 2025', 'time' => '13.00 – 16.00 WIB',
            'place' => 'Aula LPPP, Lantai 1', 'counselor' => 'Dr. Rina Kusumawati, M.Pd',
            'filled' => 27, 'quota' => 30,
        ],
        [
            'id' => 3, 'tone' => 'purple',
            'tags' => ['Sosial', 'Pribadi'],
            'title' => 'Kecemasan Sosial & Kepercayaan Diri',
            'desc' => 'Program untuk membantu peserta membangun kepercayaan diri, mengatasi kecemasan sosial, dan mengembangkan keterampilan komunikasi yang efektif.',
            'date' => '5 September 2025', 'time' => '09.00 – 12.00 WIB',
            'place' => 'Ruang Konseling LPPP', 'counselor' => 'Drs. Agus Santoso, M.Si',
            'filled' => 8, 'quota' => 15,
        ],
        [
            'id' => 4, 'tone' => 'yellow',
            'tags' => ['Kesehatan Mental', 'Mindfulness'],
            'title' => 'Mindfulness & Kesehatan Mental',
            'desc' => 'Sesi praktis mindfulness dan teknik relaksasi untuk meningkatkan kesejahteraan mental dan keseimbangan hidup mahasiswa.',
            'date' => '12 September 2025', 'time' => '15.00 – 17.00 WIB',
            'place' => 'Taman Kampus UNNES', 'counselor' => 'Dr. Sari Dewi, M.Psi',
            'filled' => 3, 'quota' => 25,
        ],
        [
            'id' => 5, 'tone' => 'gray',
            'tags' => ['Sosial', 'Akademik'],
            'title' => 'Adaptasi Mahasiswa Baru',
            'desc' => 'Program khusus untuk mahasiswa baru dalam beradaptasi dengan kehidupan kampus, membangun jejaring sosial, dan menetapkan tujuan akademik.',
            'date' => '19 September 2025', 'time' => '08.00 – 11.00 WIB',
            'place' => 'Aula LPPP, Lantai 1', 'counselor' => 'Dra. Wulandari, M.Pd',
            'filled' => 22, 'quota' => 40,
        ],
        [
            'id' => 6, 'tone' => 'pink',
            'tags' => ['Disabilitas', 'Inklusif'],
            'title' => 'Konseling Kelompok untuk Disabilitas',
            'desc' => 'Sesi konseling kelompok khusus untuk mahasiswa berkebutuhan khusus, membahas tantangan dan strategi sukses di lingkungan akademik.',
            'date' => '26 September 2025', 'time' => '10.00 – 12.00 WIB',
            'place' => 'Ruang Aksesibel LPPP', 'counselor' => 'Dra. Wulandari, M.Pd',
            'filled' => 5, 'quota' => 12,
        ],
    ];

    // Filter kategori (nilai harus sama dengan salah satu nama tag di atas)
    $filters = ['Akademik', 'Karier', 'Kesehatan Mental', 'Sosial'];

    // Informasi pendaftaran
    $infos = [
        [
            'title' => 'Gratis',
            'desc'  => 'Semua program konseling kelompok tidak dipungut biaya.',
            'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        ],
        [
            'title' => 'Konfirmasi',
            'desc'  => 'Peserta terdaftar akan mendapat konfirmasi melalui email.',
            'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        ],
        [
            'title' => 'Hadir Tepat Waktu',
            'desc'  => 'Harap hadir 10 menit sebelum sesi dimulai.',
            'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        ],
    ];
@endphp

{{-- ===================== HERO ===================== --}}
<section class="page-hero">
    <div class="container">
        <span class="page-hero-badge">Konseling Kelompok</span>
        <h1>Program Konseling Kelompok</h1>
        <p>
            Bergabunglah dengan sesi konseling kelompok yang dipimpin konselor
            profesional untuk belajar bersama dan saling mendukung.
        </p>
    </div>
</section>

{{-- ===================== DAFTAR PROGRAM ===================== --}}
<section class="section-pad">
    <div class="container">

        <div class="program-toolbar">
            <h2>Program Tersedia (<span id="programCount">{{ count($programs) }}</span> Program)</h2>

            <div class="filter-group">
                <button type="button" class="filter-btn active" data-filter="all">Semua</button>
                @foreach ($filters as $filter)
                    <button type="button" class="filter-btn" data-filter="{{ strtolower($filter) }}">{{ $filter }}</button>
                @endforeach
            </div>
        </div>

        <div class="program-grid">
            @foreach ($programs as $p)
                @php
                    $remaining = max($p['quota'] - $p['filled'], 0);
                    $percent   = $p['quota'] > 0 ? min(round($p['filled'] / $p['quota'] * 100), 100) : 0;
                    $critical  = $remaining <= 3;   // bar merah + teks sisa slot merah

                    if ($remaining === 0) {
                        $badgeText = 'Penuh';       $badgeClass = 'slot-full';
                    } elseif ($percent >= 70) {
                        $badgeText = 'Hampir Penuh'; $badgeClass = 'slot-almost';
                    } else {
                        $badgeText = 'Tersedia';    $badgeClass = 'slot-open';
                    }
                @endphp

                <article class="program-card"
                         data-tags="{{ strtolower(implode('|', $p['tags'])) }}">
                    <div class="program-bar">
                        <span class="{{ $critical ? 'critical' : '' }}" style="width: {{ $percent }}%"></span>
                    </div>

                    <div class="program-body">
                        <div class="program-tags">
                            @foreach ($p['tags'] as $tag)
                                <span class="ptag ptag-{{ $p['tone'] }}">{{ $tag }}</span>
                            @endforeach
                        </div>

                        <h3>{{ $p['title'] }}</h3>
                        <p class="program-desc">{{ $p['desc'] }}</p>

                        <ul class="program-meta">
                            <li>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                {{ $p['date'] }}
                            </li>
                            <li>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $p['time'] }}
                            </li>
                            <li>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $p['place'] }}
                            </li>
                            <li>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                                {{ $p['counselor'] }}
                            </li>
                        </ul>

                        <div class="program-quota">
                            <div>
                                <strong>{{ $p['filled'] }}/{{ $p['quota'] }} Peserta</strong>
                                <small class="{{ $critical ? 'critical' : '' }}">{{ $remaining }} slot tersisa</small>
                            </div>
                            <span class="slot-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        </div>

                        @if ($remaining > 0)
                            <a href="{{ url('/konseling-kelompok/' . $p['id'] . '/daftar') }}" class="btn btn-primary program-btn">Daftar Sekarang</a>
                        @else
                            <span class="btn btn-primary program-btn is-disabled" aria-disabled="true">Kuota Penuh</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <p class="program-empty" id="programEmpty" hidden>
            Belum ada program pada kategori ini.
        </p>
    </div>
</section>

{{-- ===================== INFORMASI PENDAFTARAN ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <h2>Informasi Pendaftaran</h2>
            <p>Hal-hal yang perlu Anda ketahui sebelum mengikuti program.</p>
        </div>

        <div class="info-grid">
            @foreach ($infos as $info)
                <div class="info-card">
                    <span class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $info['icon'] !!}</svg>
                    </span>
                    <div class="info-text">
                        <h3>{{ $info['title'] }}</h3>
                        <p>{{ $info['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =====================================================================
     CSS TAMBAHAN (khusus halaman Konseling Kelompok)

     Dipakai ulang dari landing / halaman Panduan (pastikan sudah ada di CSS global):
       .container .section-pad .bg-soft .section-head .btn .btn-primary
       .page-hero .page-hero-badge   (dari style halaman Panduan)
     ===================================================================== --}}
<style>
    /* ---------- Hero (sama dengan halaman Panduan; boleh dipindah ke CSS global) ---------- */
    .page-hero {
        background: #E9F3EA;
        padding: 64px 0 56px;
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
        max-width: 520px;
        margin: 0 auto;
        color: #6B7280;
        font-size: 16px;
        line-height: 1.6;
    }

    /* ---------- Toolbar & filter ---------- */
    .program-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 28px;
    }
    .program-toolbar h2 {
        font-size: 22px;
        font-weight: 600;
        margin: 0;
    }
    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .filter-btn {
        background: #fff;
        color: #4B5563;
        border: 1px solid #D9DED4;
        border-radius: 999px;
        padding: 7px 18px;
        font-size: 13px;
        cursor: pointer;
        transition: background .15s, color .15s, border-color .15s;
    }
    .filter-btn:hover { border-color: var(--primary, #7DA381); }
    .filter-btn.active {
        background: var(--primary, #7DA381);
        border-color: var(--primary, #7DA381);
        color: #fff;
    }

    /* ---------- Program grid & card ---------- */
    .program-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .program-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        transition: box-shadow .2s, transform .2s;
    }
    .program-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        transform: translateY(-2px);
    }
    .program-card[hidden] { display: none; }

    /* progress bar kuota di sisi atas kartu */
    .program-bar {
        height: 5px;
        background: #ECEFE8;
    }
    .program-bar span {
        display: block;
        height: 100%;
        background: var(--primary, #7DA381);
        border-radius: 0 3px 3px 0;
    }
    .program-bar span.critical { background: #EF4444; }

    .program-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 20px 24px 24px;
    }

    .program-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }
    .ptag {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .ptag-blue   { background: #DBEAFE; color: #1E40AF; }
    .ptag-green  { background: #DCFCE7; color: #166534; }
    .ptag-purple { background: #EDE9FE; color: #5B21B6; }
    .ptag-yellow { background: #FEF3C7; color: #92400E; }
    .ptag-gray   { background: #F1F3EE; color: #4B5563; }
    .ptag-pink   { background: #FEE2E2; color: #B91C1C; }

    .program-body h3 {
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
        margin: 0 0 10px;
    }
    .program-desc {
        font-size: 13.5px;
        line-height: 1.65;
        color: #6B7280;
        margin: 0 0 16px;
    }

    .program-meta {
        list-style: none;
        padding: 0;
        margin: 0 0 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .program-meta li {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #4B5563;
    }
    .program-meta svg {
        flex-shrink: 0;
        color: #9CA3AF;
    }

    .program-quota {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: auto;          /* dorong kuota + tombol ke dasar kartu */
        padding-top: 4px;
        margin-bottom: 14px;
    }
    .program-quota strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
    }
    .program-quota small {
        font-size: 12px;
        color: #6B7280;
    }
    .program-quota small.critical { color: #EF4444; }

    .slot-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .slot-open   { background: #DCFCE7; color: #166534; }
    .slot-almost { background: #FEF3C7; color: #92400E; }
    .slot-full   { background: #FEE2E2; color: #B91C1C; }

    .program-btn {
        display: flex;
        width: 100%;
        justify-content: center;
        text-align: center;
        padding: 12px 20px;
        border-radius: 10px;
    }
    .program-btn.is-disabled {
        opacity: .5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .program-empty {
        text-align: center;
        color: #6B7280;
        padding: 40px 0 8px;
        margin: 0;
    }

    /* ---------- Informasi pendaftaran ---------- */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .info-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 18px;
        padding: 24px;
        text-align: left;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    /* garis aksen tipis di sisi atas, muncul saat hover */
    .info-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: var(--primary, #7DA381);
        opacity: 0;
        transition: opacity .2s;
    }
    .info-card:hover {
        transform: translateY(-3px);
        border-color: #CFDCCF;
        box-shadow: 0 12px 28px rgba(60, 90, 65, .10);
    }
    .info-card:hover::before { opacity: 1; }

    .info-icon {
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #E9F3EA;
        color: var(--primary, #7DA381);
        transition: background .2s, color .2s;
    }
    .info-card:hover .info-icon {
        background: var(--primary, #7DA381);
        color: #fff;
    }
    .info-text h3 {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.3;
        margin: 2px 0 6px;
    }
    .info-text p {
        font-size: 13.5px;
        line-height: 1.6;
        color: #6B7280;
        margin: 0;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 991px) {
        .program-grid { grid-template-columns: repeat(2, 1fr); }
        .info-grid { grid-template-columns: 1fr; max-width: 520px; }
    }
    @media (max-width: 640px) {
        .program-grid,
        .info-grid { grid-template-columns: 1fr; }
        .program-toolbar h2 { font-size: 19px; }
    }
</style>

{{-- =====================================================================
     JS TAMBAHAN: filter program berdasarkan kategori
     ===================================================================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var buttons = document.querySelectorAll('.filter-btn');
        var cards   = document.querySelectorAll('.program-card');
        var counter = document.getElementById('programCount');
        var empty   = document.getElementById('programEmpty');

        function applyFilter(filter) {
            var visible = 0;

            cards.forEach(function (card) {
                var tags  = (card.dataset.tags || '').split('|');
                var match = filter === 'all' || tags.indexOf(filter) !== -1;

                card.hidden = !match;
                if (match) visible++;
            });

            counter.textContent = visible;
            empty.hidden = visible !== 0;
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                applyFilter(btn.dataset.filter);
            });
        });
    });
</script>

@endsection