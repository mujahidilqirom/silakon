{{--
    SILAKON — Halaman Login
    Simpan sebagai: resources/views/auth/login.blade.php
    Gaya halaman ini ditulis di dalam file (prefix .lg-), jadi tidak bergantung pada css/login.css maupun css/app.css.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — SILAKON LPPP UNNES</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">

    <style>
        :root {
            --g-900: #2c4527;
            --g-800: #3d5a37;
            --g-700: #4f7048;
            --g-600: #5f8156;
            --g-500: #6f9062;
            --g-400: #86a87a;
            --g-100: #e8f0e4;
            --g-50: #f3f7f1;
            --ink: #101828;
            --muted: #667085;
            --line: #dfe7dc;
            --danger: #b42318;
            --danger-bg: #fef3f2;
            --danger-line: #fecdca;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body.lg-body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 500px at 90% -10%, #dcead6 0%, transparent 60%),
                radial-gradient(700px 420px at -10% 110%, #e6efe2 0%, transparent 60%),
                #f3f7f1;
            -webkit-font-smoothing: antialiased;
        }

        .lg-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: max(16px, env(safe-area-inset-top)) max(16px, env(safe-area-inset-right)) max(16px, env(safe-area-inset-bottom)) max(16px, env(safe-area-inset-left));
        }

        /* ---------- Kartu utama ---------- */
        .lg-card {
            width: 100%;
            max-width: 1040px;
            min-height: min(580px, calc(100dvh - 32px));
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 70px -28px rgba(44, 69, 39, .35), 0 2px 8px rgba(44, 69, 39, .06);
        }

        /* ---------- Panel hijau ---------- */
        .lg-brand {
            position: relative;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 6px;
            padding: 40px 56px 40px 36px;
            text-align: center; color: #fff;
            background: linear-gradient(155deg, var(--g-800) 0%, var(--g-600) 55%, var(--g-400) 100%);
            overflow: hidden;
        }
        .lg-brand::before, .lg-brand::after {
            content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .09); pointer-events: none;
        }
        .lg-brand::before { width: 260px; height: 260px; top: -90px; left: -80px; }
        .lg-brand::after { width: 300px; height: 300px; bottom: -150px; right: -40px; }
        .lg-brand > * { position: relative; z-index: 1; }

        .lg-logo {
            width: 76px; height: 76px; margin-bottom: 10px;
            display: grid; place-items: center;
            border-radius: 22px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .28);
            backdrop-filter: blur(4px);
        }
        .lg-logo img { width: 52px; height: 52px; object-fit: contain; border-radius: 8px; background: #fff; padding: 3px; }
        .lg-brand h1 { margin: 0; font-size: 34px; line-height: 1.1; letter-spacing: .04em; font-weight: 800; }
        .lg-brand-sub { margin: 6px 0 0; font-size: 14px; line-height: 1.55; color: rgba(255, 255, 255, .9); }
        .lg-quote {
            margin: 18px 0 0; max-width: 270px;
            padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, .25);
            font-size: 13.5px; font-style: italic; line-height: 1.55; color: rgba(255, 255, 255, .92);
        }
        .lg-photo { margin: 16px 0 0; width: 100%; max-width: 280px; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 255, 255, .3); }
        .lg-photo img { display: block; width: 100%; height: 130px; object-fit: cover; }

        .lg-curve { position: absolute; z-index: 2; pointer-events: none; }
        .lg-curve svg { display: block; width: 100%; height: 100%; }
        .lg-curve-side { top: 0; right: -1px; bottom: 0; width: 56px; }
        .lg-curve-bottom { display: none; left: 0; right: 0; bottom: -1px; height: 36px; }

        /* ---------- Panel form ---------- */
        .lg-form-side { display: flex; align-items: center; justify-content: center; padding: 32px 44px; }
        .lg-form-inner { width: 100%; max-width: 400px; }

        .lg-roles {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px;
            padding: 5px; margin-bottom: 22px;
            border-radius: 14px; background: var(--g-50); border: 1px solid #e7eee4;
        }
        .lg-role { position: relative; display: block; cursor: pointer; }
        .lg-role input { position: absolute; opacity: 0; inset: 0; pointer-events: none; }
        .lg-role span {
            display: block; padding: 10px 6px; border-radius: 10px;
            text-align: center; font-size: 13.5px; font-weight: 600; color: var(--muted);
            transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .lg-role:hover span { color: var(--g-800); }
        .lg-role input:checked + span {
            background: var(--g-600); color: #fff;
            box-shadow: 0 6px 14px -6px rgba(63, 90, 55, .6);
        }
        .lg-role input:focus-visible + span { outline: 2px solid var(--g-600); outline-offset: 2px; }

        .lg-form-inner h2 { margin: 0; font-size: 27px; line-height: 1.2; letter-spacing: -.015em; font-weight: 800; }
        .lg-lead { margin: 6px 0 20px; font-size: 14px; color: var(--muted); }
        .lg-lead b { color: var(--g-700); font-weight: 700; }

        .lg-alert {
            display: grid; grid-template-columns: 18px minmax(0, 1fr); column-gap: 10px;
            margin-bottom: 14px; padding: 11px 13px;
            border: 1px solid var(--danger-line); border-radius: 12px;
            background: var(--danger-bg); color: var(--danger);
            font-size: 13px; line-height: 1.5;
        }
        .lg-alert svg { width: 18px; height: 18px; margin-top: 1px; }

        .lg-field { margin-bottom: 14px; }
        .lg-field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
        .lg-input { position: relative; display: flex; align-items: center; }
        .lg-input input {
            width: 100%; height: 48px; padding: 0 14px;
            font: inherit; font-size: 14.5px; color: var(--ink);
            background: #fff; border: 1.5px solid var(--line); border-radius: 12px; outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .lg-field.has-password .lg-input input { padding-right: 48px; }
        .lg-input input::placeholder { color: #a2aca5; }
        .lg-input input:hover { border-color: #c9d6c5; }
        .lg-input input:focus { border-color: var(--g-600); box-shadow: 0 0 0 4px rgba(95, 129, 86, .16); }
        .lg-field.has-error .lg-input input { border-color: var(--danger); }
        .lg-field.has-error .lg-input input:focus { box-shadow: 0 0 0 4px rgba(180, 35, 24, .12); }
        .lg-error { display: none; margin-top: 6px; font-size: 12.5px; color: var(--danger); }
        .lg-field.has-error .lg-error { display: block; }

        .lg-toggle {
            position: absolute; right: 6px; width: 36px; height: 36px;
            display: grid; place-items: center; padding: 0;
            border: 0; border-radius: 10px; background: transparent; color: #7c8a80; cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }
        .lg-toggle:hover { background: var(--g-50); color: var(--g-700); }
        .lg-toggle:focus-visible { outline: 2px solid var(--g-600); outline-offset: 1px; }
        .lg-toggle svg { width: 20px; height: 20px; }
        .lg-toggle .icon-off { display: none; }
        .lg-toggle.is-on .icon-on { display: none; }
        .lg-toggle.is-on .icon-off { display: block; }

        .lg-forgot { margin: 0 0 16px; text-align: right; font-size: 13px; }
        .lg-forgot a, .lg-foot a { color: var(--g-700); font-weight: 600; text-decoration: none; }
        .lg-forgot a:hover, .lg-foot a:hover { text-decoration: underline; }

        .lg-submit {
            width: 100%; height: 50px; border: 0; border-radius: 12px; cursor: pointer;
            font: inherit; font-size: 15px; font-weight: 700; color: #fff;
            background: linear-gradient(180deg, var(--g-500) 0%, var(--g-700) 100%);
            box-shadow: 0 10px 20px -9px rgba(63, 90, 55, .65);
            transition: transform .12s ease, filter .15s ease, box-shadow .15s ease;
        }
        .lg-submit:hover { filter: brightness(1.06); box-shadow: 0 12px 24px -9px rgba(63, 90, 55, .7); }
        .lg-submit:active { transform: translateY(1px); }
        .lg-submit:focus-visible { outline: 3px solid rgba(95, 129, 86, .35); outline-offset: 2px; }
        .lg-submit[disabled] { opacity: .7; cursor: progress; }

        .lg-or { display: flex; align-items: center; gap: 12px; margin: 16px 0; color: #98a2b3; font-size: 12px; }
        .lg-or::before, .lg-or::after { content: ""; flex: 1; height: 1px; background: #e4eae1; }

        .lg-google {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; height: 48px; border: 1.5px solid var(--line); border-radius: 12px;
            background: #fff; color: #24352b; text-decoration: none; font-weight: 700; font-size: 14px;
            transition: background-color .15s ease, border-color .15s ease;
        }
        .lg-google:hover { background: var(--g-50); border-color: #c9d6c5; }
        .lg-google:focus-visible { outline: 2px solid var(--g-600); outline-offset: 2px; }
        .lg-google svg { width: 19px; height: 19px; flex: none; }

        .lg-foot { margin: 16px 0 0; text-align: center; font-size: 13px; color: var(--muted); }

        .lg-toast {
            position: fixed; top: max(14px, env(safe-area-inset-top)); left: 50%; transform: translateX(-50%);
            z-index: 30; max-width: calc(100vw - 32px);
            padding: 11px 16px; border: 1px solid #fed7aa; border-radius: 12px;
            background: #fff7ed; color: #9a3412; font-size: 13px; font-weight: 600; line-height: 1.4;
            box-shadow: 0 10px 26px rgba(0, 0, 0, .1);
        }

        /* ---------- Layar pendek (laptop kecil): rapatkan supaya semua terlihat tanpa scroll ---------- */
        @media (min-width: 901px) and (max-height: 700px) {
            .lg-card { min-height: 0; }
            .lg-brand { padding-top: 28px; padding-bottom: 28px; }
            .lg-photo { display: none; }
            .lg-form-side { padding-top: 22px; padding-bottom: 22px; }
            .lg-roles { margin-bottom: 16px; }
            .lg-form-inner h2 { font-size: 24px; }
            .lg-lead { margin-bottom: 14px; }
            .lg-field { margin-bottom: 11px; }
            .lg-input input { height: 44px; }
            .lg-forgot { margin-bottom: 12px; }
            .lg-submit { height: 46px; }
            .lg-or { margin: 12px 0; }
            .lg-google { height: 44px; }
            .lg-foot { margin-top: 12px; }
        }
        @media (min-width: 901px) and (max-height: 600px) {
            .lg-page { padding-top: 10px; padding-bottom: 10px; }
            .lg-lead { display: none; }
            .lg-form-inner h2 { margin-bottom: 12px; }
        }

        /* ---------- Tablet & HP: panel hijau jadi header ---------- */
        @media (max-width: 900px) {
            .lg-page { align-items: start; }
            .lg-card { grid-template-columns: 1fr; max-width: 520px; min-height: 0; border-radius: 24px; margin: 0 auto; }
            .lg-brand {
                flex-direction: row; justify-content: flex-start; text-align: left;
                flex-wrap: wrap; column-gap: 16px; row-gap: 4px;
                padding: 26px 24px 52px;
            }
            .lg-brand::before { width: 180px; height: 180px; top: -70px; left: auto; right: -50px; }
            .lg-brand::after { display: none; }
            .lg-logo { width: 62px; height: 62px; margin: 0; border-radius: 18px; flex: none; }
            .lg-logo img { width: 42px; height: 42px; }
            .lg-brand h1 { font-size: 26px; align-self: center; flex: 1 1 0; min-width: 0; }
            .lg-brand-sub { flex-basis: 100%; margin: 8px 0 0; font-size: 13px; }
            .lg-brand-sub br { display: none; }
            .lg-quote, .lg-photo { display: none; }
            .lg-curve-side { display: none; }
            .lg-curve-bottom { display: block; }
            .lg-form-side { padding: 14px 28px 28px; }
            .lg-form-inner { max-width: none; }
            .lg-form-inner h2 { font-size: 24px; }
        }
        @media (max-width: 480px) {
            .lg-page { padding: max(10px, env(safe-area-inset-top)) 10px max(10px, env(safe-area-inset-bottom)); }
            .lg-card { border-radius: 20px; }
            .lg-brand { padding: 22px 18px 48px; }
            .lg-brand h1 { font-size: 23px; }
            .lg-form-side { padding: 10px 18px 24px; }
            .lg-role span { font-size: 12.5px; padding: 10px 2px; }
            /* 16px mencegah iOS memperbesar layar saat input difokuskan */
            .lg-input input { font-size: 16px; }
        }
        @media (max-width: 340px) {
            .lg-roles { gap: 2px; padding: 4px; }
            .lg-role span { font-size: 11.5px; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body class="lg-body">
@if($errors->has('session'))
    <div class="lg-toast" role="alert">{{ $errors->first('session') }}</div>
@endif

@php
    // Foto pendamping bersifat opsional. Simpan di public/images/konseling.jpg kalau mau ditampilkan.
    $fotoPendamping = 'images/konseling.jpg';
    $adaFoto = file_exists(public_path($fotoPendamping));
@endphp

<main class="lg-page">
    <div class="lg-card">

        {{-- ==================== PANEL HIJAU ==================== --}}
        <section class="lg-brand">
            <div class="lg-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON">
            </div>

            <h1>SILAKON</h1>
            <p class="lg-brand-sub">
                Sistem Layanan Konseling<br>
                LPPP Universitas Negeri Semarang
            </p>

            @if($adaFoto)
                <figure class="lg-photo">
                    <img src="{{ asset($fotoPendamping) }}" alt="Sesi konseling di LPPP UNNES">
                </figure>
            @endif

            <p class="lg-quote">&ldquo;Mendampingi, Mendengarkan, Memberikan Solusi&rdquo;</p>

            <div class="lg-curve lg-curve-side" aria-hidden="true">
                <svg viewBox="0 0 56 600" preserveAspectRatio="none"><path d="M56 0 C14 150, 14 450, 56 600 Z" fill="#ffffff"/></svg>
            </div>
            <div class="lg-curve lg-curve-bottom" aria-hidden="true">
                <svg viewBox="0 0 600 36" preserveAspectRatio="none"><path d="M0 36 C150 4, 450 4, 600 36 Z" fill="#ffffff"/></svg>
            </div>
        </section>

        {{-- ==================== PANEL FORM ==================== --}}
        <section class="lg-form-side">
            <div class="lg-form-inner">

                <div class="lg-roles" role="radiogroup" aria-label="Pilih peran">
                    <label class="lg-role">
                        <input type="radio" name="peran" value="admin" data-role checked>
                        <span>Admin</span>
                    </label>
                    <label class="lg-role">
                        <input type="radio" name="peran" value="konselor" data-role>
                        <span>Konselor</span>
                    </label>
                    <label class="lg-role">
                        <input type="radio" name="peran" value="koordinator" data-role>
                        <span>Koordinator</span>
                    </label>
                </div>

                <h2>Selamat Datang Kembali</h2>
                <p class="lg-lead">Masuk sebagai <b data-role-title>Admin Layanan</b> SILAKON</p>

                <form method="POST" action="{{ route('login.store') }}" data-login-form novalidate>
                    @csrf
                    <input type="hidden" name="peran" value="admin" data-role-value>

                    @if($errors->any() && !$errors->has('session'))
                        <div class="lg-alert" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5h.01"/></svg>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <div class="lg-field" data-field>
                        <label for="email">Email</label>
                        <div class="lg-input">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username"
                                   inputmode="email" autocapitalize="off"
                                   placeholder="admin@lppp.unnes.ac.id" data-role-email required>
                        </div>
                        <div class="lg-error" data-error>Masukkan email yang valid.</div>
                    </div>

                    <div class="lg-field has-password" data-field>
                        <label for="password">Password</label>
                        <div class="lg-input">
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                   placeholder="Masukkan password" required>
                            <button type="button" class="lg-toggle" data-toggle-password aria-label="Tampilkan password">
                                <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10.7 6.2A9.8 9.8 0 0 1 12 6c6.4 0 10 6 10 6a17 17 0 0 1-3 3.6M6.5 7.6C3.9 9.2 2 12 2 12s3.6 7 10 7a9.6 9.6 0 0 0 4.2-.9"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/>
                                </svg>
                            </button>
                        </div>
                        <div class="lg-error" data-error>Password wajib diisi.</div>
                    </div>

                    <p class="lg-forgot"><a href="{{ route('password.request') }}">Lupa password?</a></p>

                    <button type="submit" class="lg-submit" data-submit>
                        Masuk sebagai <span data-role-button>Admin</span>
                    </button>
                </form>

                <div class="lg-or"><span>atau</span></div>

                <a href="{{ route('google.redirect') }}" class="lg-google">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.6 5.9c4.5-4.1 7-10.2 7-17.6z"/>
                        <path fill="#FBBC05" d="M10.5 28.7a14.5 14.5 0 0 1 0-9.4l-7.9-6.1a24 24 0 0 0 0 21.6l7.9-6.1z"/>
                        <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.6-5.9c-2.1 1.4-4.9 2.3-8.3 2.3-6.3 0-11.6-4.1-13.5-9.9l-7.9 6.1C6.5 42.6 14.6 48 24 48z"/>
                    </svg>
                    Masuk dengan Google
                </a>

                <p class="lg-foot">
                    Bukan portal internal? <a href="{{ url('/') }}">Kembali ke SILAKON</a>
                </p>
            </div>
        </section>
    </div>
</main>

<script>
(function () {
    const form   = document.querySelector('[data-login-form]');
    const email  = document.querySelector('[data-role-email]');
    const submit = document.querySelector('[data-submit]');
    if (!form) return;

    const PERAN = {
        admin:       { judul: 'Admin Layanan', tombol: 'Admin',       email: 'admin@lppp.unnes.ac.id' },
        konselor:    { judul: 'Konselor',      tombol: 'Konselor',    email: 'konselor@lppp.unnes.ac.id' },
        koordinator: { judul: 'Koordinator',   tombol: 'Koordinator', email: 'koordinator@lppp.unnes.ac.id' },
    };

    document.querySelectorAll('[data-role]').forEach((radio) => {
        radio.addEventListener('change', () => {
            const p = PERAN[radio.value];
            document.querySelector('[data-role-title]').textContent  = p.judul;
            document.querySelector('[data-role-button]').textContent = p.tombol;
            const roleValue = document.querySelector('[data-role-value]');
            if (roleValue) roleValue.value = radio.value;
            email.placeholder = p.email;
        });
    });

    const toggle = document.querySelector('[data-toggle-password]');
    toggle.addEventListener('click', () => {
        const input = document.getElementById('password');
        const lihat = input.type === 'password';
        input.type = lihat ? 'text' : 'password';
        toggle.classList.toggle('is-on', lihat);
        toggle.setAttribute('aria-label', lihat ? 'Sembunyikan password' : 'Tampilkan password');
        input.focus();
    });

    form.addEventListener('submit', (e) => {
        let valid = true;
        form.querySelectorAll('[data-field]').forEach((field) => {
            const input = field.querySelector('input');
            const isi   = input.value.trim();
            const salah = input.type === 'email'
                ? !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(isi)
                : isi === '';
            field.classList.toggle('has-error', salah);
            if (salah) valid = false;
        });

        if (!valid) {
            e.preventDefault();
            form.querySelector('.has-error input')?.focus();
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Memproses...';
    });

    form.addEventListener('input', (e) => {
        e.target.closest('[data-field]')?.classList.remove('has-error');
    });
})();
</script>
</body>
</html>