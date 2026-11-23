<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="navbar-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON" class="logo-img">
                    <span class="brand-text">
                        SILAKON
                        <small>LPPP UNNES</small>
                    </span>
                </a>
                <p>
                    Sistem Layanan Konseling<br>
                    Lembaga Pengembangan Pendidikan dan Profesi<br>
                    Universitas Negeri Semarang<br>
                    "Mendampingi, Mendengarkan, Memberikan Solusi"
                </p>
            </div>

            <div class="footer-col">
                <h4>Tautan Cepat</h4>
                <ul>
                    <li><a href="{{ url('/') }}">Beranda</a></li>
                    <li><a href="{{ url('/tentang') }}">Tentang Kami</a></li>
                    <li><a href="{{ url('/layanan') }}">Layanan</a></li>
                    <li><a href="{{ url('/panduan') }}">Panduan</a></li>
                    <li><a href="{{ url('/self-help') }}">Self Help</a></li>
                    <li><a href="{{ url('/cek-status') }}">Cek Status</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Portal Internal</h4>
                <ul>
                    <li><a href="{{ url('/login') }}">Login Admin</a></li>
                    <li><a href="{{ url('/login') }}">Login Konselor</a></li>
                </ul>
                <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary">Ajukan Konseling</a>
            </div>

            <div class="footer-col">
                <h4>Kontak</h4>
                <ul class="footer-contact">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        Gedung LPPP, Kampus Sekaran, Gunungpati, Semarang 50229
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z" opacity="0"/><path d="m3 6 9 7 9-7"/><rect x="3" y="5" width="18" height="14" rx="2"/></svg>
                        <a href="mailto:karir@mail.unnes.ac.id">karir@mail.unnes.ac.id</a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                        <a href="https://wa.me/6285179510121" target="_blank" rel="noopener">+62 851 7951 0121</a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        <a href="https://www.instagram.com/bkdisablitas.unnes" target="_blank" rel="noopener">@bkdisablitas.unnes</a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        lppp.unnes.ac.id
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} SILAKON — LPPP Universitas Negeri Semarang. Hak Cipta Dilindungi.</span>
            <span>v1.0.0 — Sistem Layanan Konseling Terintegrasi</span>
        </div>
    </div>
</footer>
