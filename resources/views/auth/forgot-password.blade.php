<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>Lupa Password — SILAKON</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <style>
        :root {
            --rp-green-900: #1f3d31;
            --rp-green-700: #3f6d5d;
            --rp-green-600: #4a7a5f;
            --rp-green-100: #e6f0ea;
            --rp-green-50: #f4f8f5;
            --rp-ink: #17231d;
            --rp-muted: #66746b;
            --rp-line: #dbe5de;
            --rp-danger: #b42318;
            --rp-danger-bg: #fef3f2;
            --rp-danger-line: #fecdca;
            --rp-ok: #16803c;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body.rp-body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--rp-ink);
            background:
                radial-gradient(900px 480px at 85% -10%, #dcebe2 0%, transparent 60%),
                radial-gradient(700px 420px at -10% 110%, #e9f1ec 0%, transparent 60%),
                var(--rp-green-50);
            -webkit-font-smoothing: antialiased;
        }
        .rp-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: max(20px, env(safe-area-inset-top)) max(16px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(16px, env(safe-area-inset-left));
        }
        .rp-wrap { width: 100%; max-width: 460px; }

        .rp-brand {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 22px; text-decoration: none; color: var(--rp-ink);
        }
        .rp-brand-mark {
            width: 40px; height: 40px; border-radius: 12px;
            display: grid; place-items: center;
            background: var(--rp-green-700); color: #fff;
            box-shadow: 0 6px 16px rgba(63, 109, 93, .28);
        }
        .rp-brand-mark svg { width: 22px; height: 22px; }
        .rp-brand-text { display: grid; line-height: 1.15; }
        .rp-brand-text strong { font-size: 18px; letter-spacing: .02em; }
        .rp-brand-text span { font-size: 11.5px; color: var(--rp-muted); letter-spacing: .04em; }

        .rp-card {
            background: #fff;
            border: 1px solid var(--rp-line);
            border-radius: 22px;
            padding: 32px 30px 28px;
            box-shadow: 0 20px 48px -18px rgba(31, 61, 49, .22), 0 2px 6px rgba(31, 61, 49, .05);
        }
        .rp-icon {
            width: 52px; height: 52px; border-radius: 16px;
            display: grid; place-items: center;
            background: var(--rp-green-100); color: var(--rp-green-700);
            margin-bottom: 16px;
        }
        .rp-icon svg { width: 26px; height: 26px; }
        .rp-card h1 { margin: 0 0 6px; font-size: 24px; line-height: 1.25; letter-spacing: -.01em; }
        .rp-lead { margin: 0 0 22px; color: var(--rp-muted); font-size: 14.5px; line-height: 1.6; }

        .rp-alert {
            display: grid; grid-template-columns: 20px minmax(0, 1fr); column-gap: 10px;
            margin-bottom: 18px; padding: 12px 14px;
            border: 1px solid var(--rp-danger-line); border-radius: 12px;
            background: var(--rp-danger-bg); color: var(--rp-danger);
            font-size: 13.5px; line-height: 1.5;
        }
        .rp-alert svg { width: 20px; height: 20px; margin-top: 1px; }

        .rp-success {
            display: grid; grid-template-columns: 20px minmax(0, 1fr); column-gap: 10px;
            margin-bottom: 18px; padding: 12px 14px;
            border: 1px solid #b7e0c4; border-radius: 12px;
            background: #f0faf3; color: #166534;
            font-size: 13.5px; line-height: 1.5;
        }
        .rp-success svg { width: 20px; height: 20px; margin-top: 1px; }
        .rp-tips {
            margin: 18px 0 0; padding: 12px 14px;
            border-radius: 12px; background: var(--rp-green-50);
            color: var(--rp-muted); font-size: 12.5px; line-height: 1.55;
        }
        .rp-field { margin-bottom: 18px; }
        .rp-field label { display: block; margin-bottom: 7px; font-size: 13.5px; font-weight: 600; }
        .rp-input {
            position: relative; display: flex; align-items: center;
        }
        .rp-input > svg.rp-lead-icon {
            position: absolute; left: 14px; width: 18px; height: 18px;
            color: #8a9890; pointer-events: none;
        }
        .rp-input input {
            width: 100%; height: 50px;
            padding: 0 46px 0 44px;
            font: inherit; font-size: 15px; color: var(--rp-ink);
            background: #fff;
            border: 1.5px solid var(--rp-line); border-radius: 12px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .rp-input input[type="email"] { padding-right: 14px; }
        .rp-input input::placeholder { color: #a3aea7; }
        .rp-input input:hover { border-color: #c4d3c9; }
        .rp-input input:focus { border-color: var(--rp-green-600); box-shadow: 0 0 0 4px rgba(74, 122, 95, .15); }
        .rp-input input.is-invalid { border-color: var(--rp-danger); }
        .rp-input input.is-invalid:focus { box-shadow: 0 0 0 4px rgba(180, 35, 24, .12); }

        .rp-toggle {
            position: absolute; right: 6px; width: 38px; height: 38px;
            display: grid; place-items: center;
            border: 0; border-radius: 10px; background: transparent;
            color: #7b8a81; cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }
        .rp-toggle:hover { background: var(--rp-green-50); color: var(--rp-green-700); }
        .rp-toggle:focus-visible { outline: 2px solid var(--rp-green-600); outline-offset: 1px; }
        .rp-toggle svg { width: 20px; height: 20px; }
        .rp-toggle .rp-eye-off { display: none; }
        .rp-toggle.is-shown .rp-eye { display: none; }
        .rp-toggle.is-shown .rp-eye-off { display: block; }

        .rp-meter { display: flex; gap: 5px; margin-top: 10px; }
        .rp-meter i { flex: 1; height: 4px; border-radius: 4px; background: #e5ece7; transition: background-color .2s ease; }
        .rp-meter[data-level="1"] i:nth-child(1) { background: #e5484d; }
        .rp-meter[data-level="2"] i:nth-child(-n+2) { background: #f0a020; }
        .rp-meter[data-level="3"] i:nth-child(-n+3) { background: #6aa84f; }
        .rp-meter[data-level="4"] i { background: var(--rp-ok); }
        .rp-meter-text { margin: 6px 0 0; min-height: 18px; font-size: 12.5px; color: var(--rp-muted); }

        .rp-hint { margin: 7px 0 0; min-height: 18px; font-size: 12.5px; color: var(--rp-muted); }
        .rp-hint.is-ok { color: var(--rp-ok); }
        .rp-hint.is-bad { color: var(--rp-danger); }

        .rp-submit {
            width: 100%; height: 52px; margin-top: 6px;
            border: 0; border-radius: 12px; cursor: pointer;
            font: inherit; font-size: 15.5px; font-weight: 700; color: #fff;
            background: linear-gradient(180deg, var(--rp-green-600) 0%, var(--rp-green-700) 100%);
            box-shadow: 0 10px 20px -8px rgba(63, 109, 93, .55);
            transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
        }
        .rp-submit:hover { filter: brightness(1.06); box-shadow: 0 12px 24px -8px rgba(63, 109, 93, .6); }
        .rp-submit:active { transform: translateY(1px); }
        .rp-submit:focus-visible { outline: 3px solid rgba(74, 122, 95, .35); outline-offset: 2px; }
        .rp-submit[disabled] { opacity: .7; cursor: progress; }

        .rp-back { margin: 18px 0 0; text-align: center; font-size: 13.5px; }
        .rp-back a { color: var(--rp-green-700); font-weight: 600; text-decoration: none; }
        .rp-back a:hover { text-decoration: underline; }
        .rp-foot { margin: 20px 0 0; text-align: center; font-size: 12px; color: var(--rp-muted); }

        @media (max-width: 480px) {
            .rp-page { align-items: start; padding-top: max(24px, env(safe-area-inset-top)); }
            .rp-card { padding: 26px 20px 22px; border-radius: 18px; }
            .rp-card h1 { font-size: 22px; }
            .rp-brand { margin-bottom: 18px; }
            /* 16px mencegah iOS memperbesar layar saat input difokuskan */
            .rp-input input { font-size: 16px; }
        }
        @media (max-width: 340px) {
            .rp-card { padding: 22px 16px 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body class="rp-body">
<main class="rp-page">
    <div class="rp-wrap">
        <a class="rp-brand" href="{{ url('/') }}" aria-label="SILAKON LPPP UNNES">
            <span class="rp-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.4-9.3-9A5.3 5.3 0 0 1 12 6.6 5.3 5.3 0 0 1 21.3 12C19 16.6 12 21 12 21Z"/></svg>
            </span>
            <span class="rp-brand-text"><strong>SILAKON</strong><span>LPPP UNNES</span></span>
        </a>

        <section class="rp-card">
            <div class="rp-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
            </div>
            <h1>Lupa Password</h1>
            <p class="rp-lead">Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur password baru.</p>

            @if(session('status'))
                <div class="rp-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.3 2.3 2.3 4.7-5"/></svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="rp-alert" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5h.01"/></svg>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf
                <div class="rp-field">
                    <label for="email">Email</label>
                    <div class="rp-input">
                        <svg class="rp-lead-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" inputmode="email" autocapitalize="off" required autofocus @error('email') class="is-invalid" @enderror>
                    </div>
                </div>
                <button class="rp-submit" type="submit" id="forgotSubmit">Kirim Tautan Reset</button>
            </form>

            <p class="rp-tips">Tidak menerima email? Periksa folder spam, atau pastikan alamat email sama dengan yang terdaftar di akun Anda.</p>

            <p class="rp-back"><a href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/') }}">&larr; Kembali ke halaman masuk</a></p>
        </section>

        <p class="rp-foot">&copy; {{ date('Y') }} LPPP UNNES &middot; SILAKON</p>
    </div>
</main>

<script>
(function () {
    const form = document.getElementById('forgotForm');
    const btn = document.getElementById('forgotSubmit');
    if (!form || !btn) return;
    form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        btn.disabled = true;
        btn.textContent = 'Mengirim…';
    });
})();
</script>
</body>
</html>