@extends('layouts.app')

@section('title', 'SILAKON — Layanan Konseling Terintegrasi LPPP UNNES')

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="hero">
    <div class="container">
        <div class="hero-text">
            <span class="hero-badge">
                <span class="dot"></span>
                Layanan Aktif — LPPP UNNES
            </span>

            <h1>
                Layanan Konseling
                <span class="accent">Terintegrasi</span>
                LPPP Universitas Negeri Semarang
            </h1>

            <p class="hero-lead">
                SILAKON menghadirkan sistem layanan konseling digital yang profesional,
                mudah diakses, dan terpercaya untuk seluruh civitas akademika UNNES.
            </p>

            <div class="hero-cta">
                <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary">
                    Ajukan Konseling
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="{{ url('/layanan') }}" class="btn btn-outline">Pelajari Layanan</a>
            </div>
        </div>

        <div class="hero-media">
            <div class="photo-frame">
                <img src="{{ asset('images/hero.jpg') }}" alt="Layanan Konseling SILAKON LPPP UNNES">
            </div>

            <div class="floating-card top">
                <span class="fc-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                Data Terjamin Aman
            </div>

            <div class="floating-card bottom">
                <span class="fc-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <span>
                    <strong>Pengajuan Diverifikasi</strong>
                    <span>Respon dalam 1x24 jam</span>
                </span>
            </div>
        </div>
    </div>

    {{-- ===================== STATS BAR ===================== --}}
    <div class="stats-bar">
        <div class="container">
            <div class="stat-item">
                <div class="num">1.158+</div>
                <div class="label">Konseli Terlayani</div>
            </div>
            <div class="stat-item">
                <div class="num">21</div>
                <div class="label">Konselor Aktif</div>
            </div>
            <div class="stat-item">
                <div class="num">100+</div>
                <div class="label">Pengajuan Per Periode</div>
            </div>
            <div class="stat-item">
                <div class="num">7</div>
                <div class="label">Layanan Terintegrasi</div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== WHY SILAKON ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Mengapa SILAKON</span>
            <h2>Platform Konseling Terpercaya</h2>
            <p>Dirancang khusus untuk memenuhi kebutuhan layanan konseling sivitas akademika UNNES secara digital dan profesional.</p>
        </div>

        <div class="card-grid">
            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                </span>
                <h3>Pendaftaran Online</h3>
                <p>Ajukan konseling kapan saja dan di mana saja melalui formulir digital yang mudah digunakan.</p>
            </div>

            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                </span>
                <h3>Antrian Digital</h3>
                <p>Sistem antrian cerdas memastikan setiap konseli mendapatkan layanan secara adil dan teratur.</p>
            </div>

            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </span>
                <h3>Penjadwalan Mudah</h3>
                <p>Jadwal konseling diatur secara otomatis sesuai ketersediaan konselor dan preferensi konseli.</p>
            </div>

            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <h3>Konselor Profesional</h3>
                <p>21 konselor berpengalaman siap mendampingi dan memberikan solusi terbaik untuk Anda.</p>
            </div>

            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <h3>Data Aman &amp; Rahasia</h3>
                <p>Privasi dan kerahasiaan data konseli terjamin dengan sistem keamanan berlapis.</p>
            </div>

            <div class="feature-card">
                <span class="icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </span>
                <h3>Monitoring Status</h3>
                <p>Pantau perkembangan pengajuan konseling Anda secara real-time kapan saja.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===================== SERVICE CATEGORIES ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Kategori Layanan</span>
            <h2>Kami Siap Membantu Anda</h2>
            <p>Berbagai kategori konseling tersedia untuk membantu Anda menemukan solusi terbaik.</p>
        </div>

        <div class="category-grid">
            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </span>
                <h3>Akademik</h3>
                <p>Kesulitan belajar, nilai, motivasi akademik</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 0 0-4 4v1a4 4 0 0 0-2 3.46V13a4 4 0 0 0 2 3.46V18a4 4 0 0 0 8 0v-1.54A4 4 0 0 0 18 13v-2.54A4 4 0 0 0 16 7V6a4 4 0 0 0-4-4Z"/></svg>
                </span>
                <h3>Pribadi</h3>
                <p>Permasalahan personal, emosional, identitas diri</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 6.1H3M21 12.1H3M15.1 18H3"/></svg>
                </span>
                <h3>Sosial</h3>
                <p>Hubungan interpersonal, adaptasi lingkungan</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </span>
                <h3>Karier</h3>
                <p>Perencanaan karier, pengembangan profesi</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 10-10-5L2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>
                </span>
                <h3>Penyelesaian Studi</h3>
                <p>Kendala kelulusan, skripsi, administrasi</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                </span>
                <h3>Kesehatan Mental</h3>
                <p>Kecemasan, stres, depresi, burnout</p>
            </div>

            <div class="category-card">
                <span class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="4" r="2"/><path d="M19 13v-2a3 3 0 0 0-3-3h-2V6h-4v2H8a3 3 0 0 0-3 3v2"/><circle cx="6" cy="19" r="3"/><circle cx="18" cy="19" r="3"/><path d="M9 12v3.5"/></svg>
                </span>
                <h3>Disabilitas</h3>
                <p>Layanan khusus mahasiswa berkebutuhan khusus</p>
            </div>
        </div>
    </div>
</section>

{{-- ===================== PROCESS TIMELINE ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Alur Layanan</span>
            <h2>Proses Konseling yang Mudah</h2>
            <p>Ikuti langkah sederhana ini untuk mendapatkan layanan konseling profesional.</p>
        </div>

        <div class="timeline">
            <div class="timeline-row">
                <div class="timeline-card">
                    <h3>Ajukan Konseling</h3>
                    <p>Isi formulir pengajuan dengan data diri dan permasalahan yang ingin dikonsultasikan.</p>
                </div>
                <div class="timeline-node">1</div>
                <div class="spacer"></div>
            </div>

            <div class="timeline-row">
                <div class="spacer"></div>
                <div class="timeline-node">2</div>
                <div class="timeline-card right-slot">
                    <h3>Verifikasi</h3>
                    <p>Tim admin memverifikasi pengajuan dalam 1x24 jam kerja.</p>
                </div>
            </div>

            <div class="timeline-row">
                <div class="timeline-card">
                    <h3>Penugasan Konselor</h3>
                    <p>Konselor yang tepat ditugaskan berdasarkan kategori permasalahan.</p>
                </div>
                <div class="timeline-node">3</div>
                <div class="spacer"></div>
            </div>

            <div class="timeline-row">
                <div class="spacer"></div>
                <div class="timeline-node">4</div>
                <div class="timeline-card right-slot">
                    <h3>Penjadwalan</h3>
                    <p>Jadwal konseling ditetapkan dan konseli mendapatkan konfirmasi.</p>
                </div>
            </div>

            <div class="timeline-row">
                <div class="timeline-card">
                    <h3>Konseling</h3>
                    <p>Sesi konseling dilakukan sesuai jadwal yang telah disepakati.</p>
                </div>
                <div class="timeline-node">5</div>
                <div class="spacer"></div>
            </div>

            <div class="timeline-row">
                <div class="spacer"></div>
                <div class="timeline-node">6</div>
                <div class="timeline-card right-slot">
                    <h3>Evaluasi</h3>
                    <p>Konseli mengisi survei kepuasan untuk peningkatan layanan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== FAQ ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">FAQ</span>
            <h2>Pertanyaan Umum</h2>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question">
                    Siapa saja yang bisa menggunakan layanan SILAKON?
                    <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Layanan SILAKON dapat digunakan oleh seluruh mahasiswa, dosen, dan tenaga kependidikan yang berstatus civitas akademika aktif di Universitas Negeri Semarang.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Apakah layanan konseling ini gratis?
                    <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Ya, seluruh layanan konseling yang tersedia melalui SILAKON tidak dipungut biaya bagi seluruh civitas akademika UNNES.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Berapa lama proses verifikasi pengajuan?
                    <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Tim admin akan memverifikasi setiap pengajuan dalam waktu 1x24 jam kerja sejak formulir diserahkan.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Apakah konseling dilakukan secara online?
                    <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Konseling dapat dilakukan secara daring maupun tatap muka, menyesuaikan preferensi konseli dan ketersediaan konselor.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Bagaimana cara memantau status pengajuan saya?
                    <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Anda dapat memantau status pengajuan secara real-time melalui menu "Cek Status" menggunakan nomor pengajuan yang telah diberikan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== CTA ===================== --}}
<section class="cta-section">
    <div class="container">
        <h2>Siap Untuk Memulai?</h2>
        <p>Jangan biarkan permasalahan Anda menjadi beban sendirian. Tim konselor profesional kami siap mendampingi Anda.</p>
        <div class="cta-buttons">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-white">Ajukan Konseling Sekarang</a>
            <a href="{{ url('/tentang') }}" class="btn btn-outline-light">Tentang LPPP</a>
        </div>
    </div>
</section>

@endsection