{{--
    SILAKON — Halaman Login
    Simpan sebagai: resources/views/auth/login.blade.php
    Route contoh:  Route::view('/login', 'auth.login')->name('login');

    Belum terhubung backend. Ketika sudah siap:
    - ganti action form ke route login Anda
    - hapus blok <script> demo di bagian bawah
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — SILAKON LPPP UNNES</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Kalau proyek Anda pakai Vite, ganti dua baris ini dengan @vite(['resources/css/app.css']) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
@if($errors->has('session'))
    <div style="position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:20;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:10px;padding:11px 16px;font:600 13px/1.4 system-ui,sans-serif;box-shadow:0 8px 24px rgba(0,0,0,.08)">
        {{ $errors->first('session') }}
    </div>
@endif

@php
    // Foto pendamping bersifat opsional. Simpan di public/img/konseling.jpg kalau mau ditampilkan.
    $fotoPendamping = 'images/konseling.jpg';
    $adaFoto = file_exists(public_path($fotoPendamping));
@endphp

<main class="login-page">
    <div class="login-card">

        {{-- ==================== PANEL HIJAU ==================== --}}
        <section class="login-brand">
            <div class="login-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON">
            </div>

            <h1>SILAKON</h1>
            <p class="login-brand-sub">
                Sistem Layanan Konseling<br>
                LPPP Universitas Negeri Semarang
            </p>

            @if($adaFoto)
                <figure class="login-photo">
                    <img src="{{ asset($fotoPendamping) }}" alt="Sesi konseling di LPPP UNNES">
                </figure>
            @endif

            <p class="login-quote">&ldquo;Mendampingi, Mendengarkan, Memberikan Solusi&rdquo;</p>

            {{-- Lengkungan pemisah, versi layar lebar --}}
            <div class="login-curve login-curve-side" aria-hidden="true">
                <svg viewBox="0 0 86 600" preserveAspectRatio="none">
                    <path d="M86 0 C30 150, 30 450, 86 600 Z" fill="#ffffff"/>
                </svg>
            </div>

            {{-- Lengkungan pemisah, versi layar kecil --}}
            <div class="login-curve login-curve-bottom" aria-hidden="true">
                <svg viewBox="0 0 600 56" preserveAspectRatio="none">
                    <path d="M0 56 C150 6, 450 6, 600 56 Z" fill="#ffffff"/>
                </svg>
            </div>
        </section>

        {{-- ==================== PANEL FORM ==================== --}}
        <section class="login-form-side">
            <div class="login-form-inner">

                <div class="login-roles" role="radiogroup" aria-label="Pilih peran">
                    <label class="login-role">
                        <input type="radio" name="peran" value="admin" data-role checked>
                        <span>Admin</span>
                    </label>
                    <label class="login-role">
                        <input type="radio" name="peran" value="konselor" data-role>
                        <span>Konselor</span>
                    </label>
                    <label class="login-role">
                        <input type="radio" name="peran" value="koordinator" data-role>
                        <span>Koordinator</span>
                    </label>
                </div>

                <h2>Selamat Datang Kembali</h2>
                <p class="login-lead">Masuk sebagai <b data-role-title>Admin Layanan</b> SILAKON</p>

                <form method="POST" action="{{ route('login.store') }}" data-login-form>
                    @csrf
                    <input type="hidden" name="peran" value="admin" data-role-value>

                    @if($errors->any())
                        <div class="login-error" style="display:flex; margin-bottom:14px;" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <div class="login-field" data-field>
                        <label for="email">Email</label>
                        <div class="login-input-wrap">
                            <input id="email" name="email" type="email" autocomplete="username"
                                   placeholder="admin@lppp.unnes.ac.id" data-role-email required>
                        </div>
                        <div class="login-error" data-error>Masukkan email yang valid.</div>
                    </div>

                    <div class="login-field has-password" data-field>
                        <label for="password">Password</label>
                        <div class="login-input-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                   placeholder="Masukkan password" required>
                            <button type="button" class="login-toggle" data-toggle-password
                                    aria-label="Tampilkan password">
                                <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.7 6.2A9.8 9.8 0 0 1 12 6c6.4 0 10 6 10 6a17 17 0 0 1-3 3.6M6.5 7.6C3.9 9.2 2 12 2 12s3.6 7 10 7a9.6 9.6 0 0 0 4.2-.9"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/>
                                </svg>
                            </button>
                        </div>
                        <div class="login-error" data-error>Password wajib diisi.</div>
                    </div>

                    <button type="submit" class="login-submit" data-submit>
                        Masuk sebagai <span data-role-button>Admin</span>
                    </button>
                </form>

                <p class="login-foot">
                    Bukan portal internal? <a href="{{ url('/') }}">Kembali ke SILAKON</a>
                </p>
            </div>
        </section>
    </div>
</main>

{{-- Interaksi tampilan saja. Hapus saat backend sudah siap. --}}
<script>
(function () {
    const form   = document.querySelector('[data-login-form]');
    const email  = document.querySelector('[data-role-email]');
    const submit = document.querySelector('[data-submit]');

    const PERAN = {
        admin:    { judul: 'Admin Layanan', tombol: 'Admin',    email: 'admin@lppp.unnes.ac.id' },
        konselor: { judul: 'Konselor',      tombol: 'Konselor', email: 'konselor@lppp.unnes.ac.id' },
        koordinator: { judul: 'Koordinator', tombol: 'Koordinator', email: 'koordinator@lppp.unnes.ac.id' },
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
