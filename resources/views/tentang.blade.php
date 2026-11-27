@extends('layouts.app')

@php
    $waAdminLayanan = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) data_get($kontakLayanan ?? [], 'wa_admin', '085179510121')));
    $waAdminTampil = data_get($kontakLayanan ?? [], 'wa_admin', '085179510121');
    $jamLayanan = data_get($kontakLayanan ?? [], 'jam_layanan', 'Senin–Kamis, 08.00–16.00 WIB; Jumat, 08.00–15.00 WIB');
@endphp

@section('title', 'Tentang Kami — LPPP UNNES | SILAKON')

@section('content')

{{-- ===================== HERO / PAGE HEADER ===================== --}}
<section class="about-hero">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Tentang Kami</span>
            <h1>LPPP Universitas Negeri Semarang</h1>
            <p>
                Lembaga Pengembangan Pendidikan dan Profesi (LPPP) UNNES hadir untuk
                mendukung pengembangan kompetensi dan kesejahteraan civitas akademika
                melalui berbagai layanan profesional, termasuk layanan konseling terintegrasi.
            </p>
        </div>
    </div>
</section>

{{-- ===================== PROFIL LEMBAGA ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="profile-grid">
            <div class="profile-text">
                <h2>Profil Lembaga</h2>

                <p>
                    LPPP UNNES adalah lembaga yang berfokus pada pengembangan sumber daya
                    manusia di lingkungan Universitas Negeri Semarang. Layanan konseling
                    merupakan salah satu program unggulan yang telah berjalan sejak 2005.
                </p>

                <p>
                    Dengan didukung oleh 21 konselor profesional bersertifikat, LPPP UNNES
                    telah melayani lebih dari 1.158 konseli dan terus berkomitmen untuk
                    memberikan layanan terbaik kepada seluruh civitas akademika UNNES.
                </p>

                <div class="profile-stats">
                    <div class="stat-mini-card">
                        <div class="stat-mini-num">2005</div>
                        <div class="stat-mini-label">Didirikan</div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-num">1.158+</div>
                        <div class="stat-mini-label">Konseli Terlayani</div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-num">21 Orang</div>
                        <div class="stat-mini-label">Konselor Aktif</div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-num">Sen&ndash;Jum</div>
                        <div class="stat-mini-label">Jam Operasional</div>
                    </div>
                </div>
            </div>

            <div class="profile-media">
                <div class="photo-frame">
                    {{-- TODO: ganti dengan foto asli, mis. asset('images/tentang/profil-lembaga.jpg') --}}
                    <img src="{{ asset('images/profil-lembaga.jpg') }}" alt="Sesi konseling LPPP UNNES">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== VISI & MISI ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Arah Kami</span>
            <h2>Visi &amp; Misi</h2>
            <p>Landasan yang menuntun setiap layanan konseling yang kami berikan.</p>
        </div>

        <div class="visi-banner">
            <span class="icon-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="0.5"/></svg>
            </span>
            <div class="visi-content">
                <span class="visi-label">Visi</span>
                <p>
                Menjadi Lembaga Pengembagan Pendidikan dan Profesi
                Bereputasi Dunia dan Pelopor Kecemerlangan Pendidikan yang Berwawasan Konservasi
                </p>
            </div>
        </div>

        <div class="misi-head">
            <h3>Misi</h3>
        </div>

        <div class="misi-grid">
            <div class="misi-card">
                <span class="misi-num">01</span>
                <p>Mengembangkan dan menyelenggarakan inovasi pembelajaran Bereputasi Dunia yang Berwawasan Konservasi.</p>
            </div>
            <div class="misi-card">
                <span class="misi-num">02</span>
                <p>Menyelenggarakan layanan sertifikasi profesi, kompetensi, karir, bimbingan konseling, dan disablilitas yang Berwawasan Konservasi dalam bidang kependidikan dan non kependidikan.</p>
            </div>
            <div class="misi-card">
                <span class="misi-num">03</span>
                <p>Menerapkan tata kelola yang baik dan mampu beradaptasi dan bersinergi dengan lingkungan secara berkelanjutan.</p>
            </div>
            <div class="misi-card">
                <span class="misi-num">04</span>
                <p>MMembangun dan mengembangkan kerjasama institusi dalam pengembangan Pendidikan, profesi, dan kompetensi.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===================== STRUKTUR ORGANISASI ===================== --}}
<section class="section-pad">
    <div class="container">
        <div class="section-head">
            <h2>Struktur Organisasi</h2>
        </div>

        <div class="org-structure">
            <div class="org-item">
                <span class="org-num">1</span>
                <div class="org-info">
                    <span class="org-role">Kepala LPPP</span>
                    <span class="org-name">Prof. Dr. Rodiyah, S.Pd., S.H., M.Si</span>
                </div>
            </div>

            <div class="org-item">
                <span class="org-num">2</span>
                <div class="org-info">
                    <span class="org-role">Koordinator Layanan Konseling</span>
                    <span class="org-name">Edwindha Prafitra Nugraheni, S.Pd., M.Pd., Kons.</span>
                </div>
            </div>

            <div class="org-item">
                <span class="org-num">3</span>
                <div class="org-info">
                    <span class="org-role">Admin Layanan</span>
                    <span class="org-name">Tim Tutor Sebaya LPPP</span>
                </div>
            </div>

            <div class="org-item">
                <span class="org-num">4</span>
                <div class="org-info">
                    <span class="org-role">Tim Konselor</span>
                    <span class="org-name">Tim Konselor LPPP</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== LOKASI & KONTAK ===================== --}}
<section class="section-pad bg-soft">
    <div class="container">
        <div class="info-columns">

            <div class="info-col">
                <h2>Lokasi &amp; Jam Layanan</h2>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Alamat</span>
                        <span class="info-value">Gedung LPPP, Kampus Sekaran, Gunungpati, Kota Semarang, Jawa Tengah 50229</span>
                    </div>
                </div>

                <div class="info-card info-card-schedule">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    </span>
                    <div class="schedule-block">
                        <span class="info-label">Jam Operasional</span>

                        <div class="schedule-row">
                            <span>Jam layanan</span>
                            <span>{{ $jamLayanan }}</span>
                        </div>
                        <div class="schedule-row">
                            <span>Sabtu &ndash; Minggu</span>
                            <span class="text-closed">Tutup</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-col">
                <h2>Hubungi Kami</h2>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Email</span>
                        <span class="info-value"><a href="mailto:karir@mail.unnes.ac.id">karir@mail.unnes.ac.id</a></span>
                    </div>
                </div>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Telepon</span>
                        <span class="info-value"><a href="https://wa.me/{{ $waAdminLayanan }}" target="_blank" rel="noopener">{{ $waAdminTampil }}</a></span>
                    </div>
                </div>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Instagram</span>
                        <span class="info-value"><a href="https://www.instagram.com/bkdisablitas.unnes" target="_blank" rel="noopener">@bkdisablitas.unnes</a></span>
                    </div>
                </div>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Website UNNES</span>
                        <span class="info-value">www.unnes.ac.id</span>
                    </div>
                </div>

                <div class="info-card">
                    <span class="icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                    </span>
                    <div>
                        <span class="info-label">Website LPPP</span>
                        <span class="info-value">lppp.unnes.ac.id</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ===================== CTA ===================== --}}
<section class="cta-section">
    <div class="container">
        <h2>Mulai Konseling Sekarang</h2>
        <p>Konselor kami siap mendampingi perjalanan Anda.</p>
        <div class="cta-buttons">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-white">Ajukan Konseling</a>
        </div>
    </div>
</section>

@endsection
