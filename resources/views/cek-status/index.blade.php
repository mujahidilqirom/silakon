@extends('layouts.app')

@section('title', 'Cek Status Pengajuan — SILAKON LPPP UNNES')

@section('content')

<section class="section-pad cs-page">
    <div class="container">
        <div class="cs-wrap">

            <header class="cs-head">
                <div class="cs-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>

                <h1 class="cs-title">Pantau Status Pengajuan</h1>
                <p class="cs-subtitle">
                    Masukkan kode pengajuan dan email yang terdaftar untuk melihat perkembangan konseling Anda.
                </p>
            </header>

            <div class="cs-card">

                @if ($errors->any())
                    <div class="cs-alert" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('cek-status.cari') }}" method="POST" data-cs-form>
                    @csrf

                    <div class="form-group">
                        <label for="kode_pengajuan">Kode pengajuan</label>
                        <div class="cs-input">
                            <svg class="cs-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9h16M4 15h16M10 3 8 21M16 3l-2 18"/></svg>
                            <input
                                type="text"
                                id="kode_pengajuan"
                                name="kode_pengajuan"
                                class="form-control cs-kode-input"
                                placeholder="SLK-2025-08-1234"
                                value="{{ old('kode_pengajuan') }}"
                                autocomplete="off"
                                autocapitalize="characters"
                                spellcheck="false"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email terdaftar</label>
                        <div class="cs-input">
                            <svg class="cs-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="email@example.com"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary cs-submit" data-cs-submit>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span data-cs-submit-text>Cari status pengajuan</span>
                    </button>
                </form>

                <div class="cs-tips">
                    <p class="cs-tips-title">Sebelum mencari</p>
                    <ul>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Kode pengajuan diberikan setelah pengajuan berhasil dikirim
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Gunakan email yang sama dengan saat mengajukan
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            Kode diawali "SLK", contoh: SLK-2025-08-1234
                        </li>
                    </ul>
                </div>
            </div>

            <p class="cs-footer-link">
                Belum punya pengajuan?
                <a href="{{ url('/ajukan-konseling') }}">Ajukan konseling</a>
            </p>

        </div>
    </div>
</section>

<style>
    .cs-page.section-pad {
        padding-top: 36px;
        padding-bottom: 56px;
        background: radial-gradient(520px 220px at 50% 0%, rgba(75, 138, 91, .08), transparent 70%);
    }
    .cs-wrap {
        max-width: 560px;
        margin: 0 auto;
    }
    .cs-head {
        text-align: center;
        margin-bottom: 22px;
    }
    .cs-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #5C9A6B, #2F6B3F);
        color: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 18px -8px rgba(47, 107, 63, .55), inset 0 1px 0 rgba(255, 255, 255, .25);
    }
    .cs-title {
        font-size: clamp(22px, 3.4vw, 28px);
        font-weight: 700;
        letter-spacing: -.01em;
        margin: 0 0 6px;
    }
    .cs-subtitle {
        color: #6B7280;
        font-size: 14px;
        line-height: 1.6;
        margin: 0 auto;
        max-width: 440px;
    }

    .cs-card {
        background: #fff;
        border: 1px solid #E3E8DF;
        border-radius: 20px;
        padding: 26px 28px 24px;
        box-shadow: 0 1px 2px rgba(31, 41, 55, .04), 0 18px 40px -24px rgba(47, 107, 63, .28);
    }

    .cs-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #B91C1C;
        font-size: 13px;
        line-height: 1.5;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .cs-alert svg { flex: 0 0 auto; margin-top: 1px; }

    .form-group { margin-bottom: 14px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }
    .cs-input { position: relative; }
    .cs-input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA9A0;
        pointer-events: none;
        transition: color .15s;
    }
    .cs-input:focus-within .cs-input-icon { color: #2F6B3F; }

    .form-control {
        width: 100%;
        padding: 12px 14px 12px 42px;
        font-family: inherit;
        font-size: 14px;
        border: 1.5px solid #DDE3D9;
        border-radius: 12px;
        background: #FAFBF9;
        color: #1F2937;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    .form-control:hover { border-color: #C7D2C4; }
    .form-control:focus {
        border-color: #4B8A5B;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(75, 138, 91, .16);
    }
    .form-control::placeholder { color: #A3ACA5; }
    .cs-kode-input {
        font-family: 'SFMono-Regular', Consolas, monospace;
        letter-spacing: .02em;
    }
    .cs-kode-input::placeholder { font-family: inherit; letter-spacing: 0; }

    .cs-submit {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 6px;
        padding-top: 13px;
        padding-bottom: 13px;
    }
    .cs-submit[disabled] { opacity: .75; cursor: progress; }

    .cs-tips {
        border-top: 1px dashed #DDE3D9;
        margin-top: 20px;
        padding-top: 16px;
    }
    .cs-tips-title {
        font-size: 13px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 10px;
    }
    .cs-tips ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .cs-tips li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13px;
        line-height: 1.55;
        color: #4B5563;
    }
    .cs-tips li svg {
        flex: 0 0 auto;
        margin-top: 2px;
        padding: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #E9F3EA;
        color: #2F6B3F;
    }

    .cs-footer-link {
        text-align: center;
        margin-top: 18px;
        font-size: 14px;
        color: #6B7280;
    }
    .cs-footer-link a {
        color: #2F6B3F;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1.5px solid rgba(47, 107, 63, .3);
        transition: border-color .15s;
    }
    .cs-footer-link a:hover { border-color: #2F6B3F; }

    @media (max-width: 640px) {
        .cs-card { padding: 22px 18px; border-radius: 18px; }
    }
</style>

@endsection

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('[data-cs-form]');
        if (!form) return;
        form.addEventListener('submit', function () {
            var btn = form.querySelector('[data-cs-submit]');
            var text = form.querySelector('[data-cs-submit-text]');
            if (!btn || !form.checkValidity()) return;
            btn.setAttribute('disabled', 'disabled');
            if (text) text.textContent = 'Mencari…';
        });
        // Tombol "Back" browser: kembalikan tombol ke keadaan semula
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            var btn = form.querySelector('[data-cs-submit]');
            var text = form.querySelector('[data-cs-submit-text]');
            if (btn) btn.removeAttribute('disabled');
            if (text) text.textContent = 'Cari status pengajuan';
        });
    })();
</script>
@endpush