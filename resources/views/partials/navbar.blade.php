<header class="navbar">
    <div class="container">
        <a href="{{ url('/') }}" class="navbar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON" class="logo-img">
            <span class="brand-text">
                SILAKON
                <small>LPPP UNNES</small>
            </span>
        </a>

        <nav class="navbar-links" id="navbar-links">
            <a href="{{ url('/') }}"
            class="{{ request()->is('/') ? 'active' : '' }}">
                Beranda
            </a>

            <a href="{{ url('/tentang') }}"
            class="{{ request()->is('tentang') ? 'active' : '' }}">
                Tentang
            </a>

            <a href="{{ url('/layanan') }}"
            class="{{ request()->is('layanan') ? 'active' : '' }}">
                Layanan
            </a>

            <a href="{{ url('/panduan') }}"
            class="{{ request()->is('panduan') ? 'active' : '' }}">
                Panduan
            </a>

            <a href="{{ url('/konseling-kelompok') }}"
            class="{{ request()->is('konseling-kelompok') ? 'active' : '' }}">
                Konseling Kelompok
            </a>

            <a href="{{ url('/self-help') }}"
            class="{{ request()->is('self-help') ? 'active' : '' }}">
                Self Help
            </a>

            <a href="{{ url('/cek-status') }}"
            class="{{ request()->is('cek-status') ? 'active' : '' }}">
                Cek Status
            </a>

            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary navbar-links-cta">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Ajukan Konseling
            </a>
        </nav>

        <div class="navbar-actions">
            <a href="{{ url('/ajukan-konseling') }}" class="btn btn-primary navbar-actions-cta" aria-label="Ajukan Konseling">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                <span>Ajukan Konseling</span>
            </a>

            <button type="button" class="navbar-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navbar-links">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <div class="navbar-overlay" data-navbar-overlay hidden></div>
</header>