@extends('layouts.app')

@section('title', 'Layanan Konseling — SILAKON LPPP UNNES')

@section('content')

@php
    // Di aplikasi nyata, data ini idealnya berasal dari LayananController@index
    // (mis. Layanan::all()) — untuk sementara didefinisikan statis di sini.
    $layananList = [
        [
            'slug' => 'akademik',
            'icon_class' => 'ic-blue',
            'title' => 'Konseling Akademik',
            'description' => 'Layanan konseling untuk mengatasi permasalahan yang berkaitan dengan proses belajar, prestasi akademik, motivasi belajar, dan perencanaan studi.',
            'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
            'untuk_siapa' => [
                'Mahasiswa yang mengalami kesulitan belajar',
                'Mahasiswa dengan IPK yang menurun',
                'Mahasiswa yang kurang motivasi',
            ],
            'manfaat' => [
                'Strategi belajar efektif',
                'Perencanaan studi yang terstruktur',
                'Peningkatan motivasi akademik',
                'Manajemen waktu belajar',
            ],
        ],
        [
            'slug' => 'pribadi',
            'icon_class' => 'ic-purple',
            'title' => 'Konseling Pribadi',
            'description' => 'Layanan konseling untuk membantu individu dalam mengatasi permasalahan personal, membangun identitas diri, dan mengembangkan potensi pribadi.',
            'icon' => '<path d="M12 2a4 4 0 0 0-4 4v1a4 4 0 0 0-2 3.46V13a4 4 0 0 0 2 3.46V18a4 4 0 0 0 8 0v-1.54A4 4 0 0 0 18 13v-2.54A4 4 0 0 0 16 7V6a4 4 0 0 0-4-4Z"/>',
            'untuk_siapa' => [
                'Masalah emosional dan psikologis',
                'Konflik identitas diri',
                'Krisis kepercayaan diri',
            ],
            'manfaat' => [
                'Pemahaman diri yang lebih baik',
                'Pengelolaan emosi sehat',
                'Pengembangan potensi pribadi',
                'Kemandirian dan ketangguhan mental',
            ],
        ],
        [
            'slug' => 'sosial',
            'icon_class' => 'ic-green',
            'title' => 'Konseling Sosial',
            'description' => 'Layanan konseling untuk membantu dalam membangun hubungan sosial yang sehat, mengatasi konflik interpersonal, dan beradaptasi dengan lingkungan.',
            'icon' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
            'untuk_siapa' => [
                'Mahasiswa baru yang sulit beradaptasi',
                'Konflik dengan teman atau keluarga',
                'Masalah komunikasi',
            ],
            'manfaat' => [
                'Keterampilan komunikasi efektif',
                'Penyelesaian konflik konstruktif',
                'Adaptasi lingkungan baru',
                'Hubungan sosial yang sehat',
            ],
        ],
        [
            'slug' => 'karier',
            'icon_class' => 'ic-yellow',
            'title' => 'Konseling Karier',
            'description' => 'Layanan konseling untuk membantu dalam merencanakan karier, memilih profesi yang tepat, dan mempersiapkan diri memasuki dunia kerja.',
            'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
            'untuk_siapa' => [
                'Mahasiswa semester akhir',
                'Alumni yang mencari kerja',
                'Tenaga kependidikan yang ingin berkembang',
            ],
            'manfaat' => [
                'Pemetaan bakat dan minat',
                'Perencanaan karier yang matang',
                'Persiapan memasuki dunia kerja',
                'Pengembangan kompetensi profesional',
            ],
        ],
        [
            'slug' => 'kesehatan-mental',
            'icon_class' => 'ic-pink',
            'title' => 'Kesehatan Mental',
            'description' => 'Layanan konseling khusus untuk menangani permasalahan kesehatan mental seperti kecemasan, depresi, stres akademik, dan burnout.',
            'icon' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>',
            'untuk_siapa' => [
                'Mahasiswa yang mengalami kecemasan',
                'Mahasiswa dengan gejala depresi',
                'Individu yang mengalami burnout',
            ],
            'manfaat' => [
                'Pengelolaan stres dan kecemasan',
                'Pemulihan dari depresi ringan',
                'Teknik mindfulness dan relaksasi',
                'Rujukan ke profesional jika diperlukan',
            ],
        ],
        [
            'slug' => 'disabilitas',
            'icon_class' => 'ic-green',
            'title' => 'Layanan Disabilitas',
            'description' => 'Layanan konseling khusus yang dirancang untuk mendukung mahasiswa berkebutuhan khusus dalam menghadapi tantangan akademik dan sosial.',
            'icon' => '<circle cx="12" cy="4" r="2"/><path d="M19 13v-2a3 3 0 0 0-3-3h-2V6h-4v2H8a3 3 0 0 0-3 3v2"/><circle cx="6" cy="19" r="3"/><circle cx="18" cy="19" r="3"/><path d="M9 12v3.5"/>',
            'untuk_siapa' => [
                'Mahasiswa dengan disabilitas fisik',
                'Mahasiswa dengan gangguan belajar',
                'Mahasiswa berkebutuhan khusus lainnya',
            ],
            'manfaat' => [
                'Dukungan akademik yang disesuaikan',
                'Fasilitasi aksesibilitas kampus',
                'Pendampingan sosial dan emosional',
                'Advokasi hak-hak mahasiswa',
            ],
        ],
    ];
@endphp

{{-- ===================== HERO ===================== --}}
<section class="layanan-hero">
    <div class="container">
        <span class="eyebrow">Layanan Kami</span>
        <h1>Layanan Konseling Lengkap</h1>
        <p>SILAKON menyediakan berbagai kategori layanan konseling profesional yang dirancang untuk memenuhi kebutuhan spesifik setiap konseli.</p>
    </div>
</section>

{{-- ===================== SERVICE LIST ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="service-list">
            @foreach ($layananList as $layanan)
                <article class="service-card">
                    {{-- Info --}}
                    <div class="service-info">
                        <span class="service-icon-box {{ $layanan['icon_class'] }}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $layanan['icon'] !!}</svg>
                        </span>
                        <h3>{{ $layanan['title'] }}</h3>
                        <p>{{ $layanan['description'] }}</p>
                    </div>

                    {{-- Untuk Siapa --}}
                    <div class="service-target">
                        <p class="service-col-label">Untuk Siapa</p>
                        <ul class="service-check-list">
                            @foreach ($layanan['untuk_siapa'] as $point)
                                <li>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Manfaat --}}
                    <div class="service-benefit">
                        <p class="service-col-label">Manfaat</p>
                        <ul class="service-benefit-list">
                            @foreach ($layanan['manfaat'] as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ url('/ajukan-konseling') }}?layanan={{ $layanan['slug'] }}" class="btn btn-primary">
                            Ajukan Konseling
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== CTA ===================== --}}
<section class="cta-section">
    <div class="container">
        <h2>Tidak Tahu Harus Mulai dari Mana?</h2>
        <p>Tim kami siap membantu Anda menemukan layanan yang paling tepat untuk kebutuhan Anda.</p>
        <div class="cta-buttons">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-white">Ajukan Konseling</a>
            <a href="{{ url('/panduan') }}" class="btn btn-outline-light">Lihat Panduan</a>
        </div>
    </div>
</section>

@endsection