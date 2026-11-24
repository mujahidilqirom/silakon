@extends('layouts.app')

@section('title', 'Cek Status Pengajuan — SILAKON LPPP UNNES')

@section('content')

<section class="section-pad cs-page">
    <div class="container">
        <div class="cs-wrap">

            <div class="cs-card">

                {{-- Header ringkas di dalam kartu --}}
                <div class="cs-head">
                    <div class="cs-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <div class="cs-head-text">
                        <span class="cs-eyebrow">SILAKON LPPP UNNES</span>
                        <h1 class="cs-title">Cek Status Pengajuan</h1>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="cs-alert" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('cek-status.cari') }}" method="POST" data-cs-form novalidate>
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
                        <small class="cs-hint">Diawali “SLK”, diberikan setelah pengajuan terkirim.</small>
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
                        <small class="cs-hint">Gunakan email yang sama saat mengajukan.</small>
                    </div>

                    <button type="submit" class="btn btn-primary cs-submit" data-cs-submit>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span data-cs-submit-text>Cari status pengajuan</span>
                    </button>
                </form>

                <p class="cs-footer-link">
                    Belum punya pengajuan?
                    <a href="{{ url('/ajukan-konseling') }}">Ajukan konseling</a>
                </p>
            </div>

        </div>
    </div>
</section>

<style>
    .cs-page.section-pad {
        padding-top: 32px;
        padding-bottom: 56px;
        background: radial-gradient(520px 220px at 50% 0%, rgba(75, 138, 91, .08), transparent 70%);
    }
    .cs-wrap { max-width: 480px; margin: 0 auto; }

    .cs-card {
        background: #fff;
        border: 1px solid #E3E8DF;
        border-radius: 20px;
        padding: 26px 28px 22px;
        box-shadow: 0 1px 2px rgba(31, 41, 55, .04), 0 18px 40px -24px rgba(47, 107, 63, .28);
    }

    /* Header ringkas */
    .cs-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 20px;
        border-bottom: 1px solid #EEF2EB;
    }
    .cs-icon {
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #5C9A6B, #2F6B3F);
        color: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 18px -8px rgba(47, 107, 63, .55), inset 0 1px 0 rgba(255, 255, 255, .25);
    }
    .cs-eyebrow {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        color: #2F6B3F;
        margin-bottom: 2px;
    }
    .cs-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -.01em;
        line-height: 1.25;
        margin: 0;
        color: #1F2937;
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
        margin-bottom: 18px;
    }
    .cs-alert svg { flex: 0 0 auto; margin-top: 1px; }

    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 7px;
    }
    .cs-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.45;
        color: #8A948C;
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
    .form-control.is-invalid { border-color: #E57373; background: #FFFAFA; }
    .cs-kode-input {
        font-family: 'SFMono-Regular', Consolas, monospace;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .cs-kode-input::placeholder { font-family: inherit; letter-spacing: 0; text-transform: none; }

    .cs-submit {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
        padding-top: 13px;
        padding-bottom: 13px;
    }
    .cs-submit[disabled] { opacity: .75; cursor: progress; }

    .cs-footer-link {
        text-align: center;
        margin: 18px 0 0;
        padding-top: 16px;
        border-top: 1px dashed #DDE3D9;
        font-size: 13.5px;
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
        .cs-page.section-pad { padding-top: 20px; }
        .cs-card { padding: 22px 18px 18px; border-radius: 18px; }
    }
</style>

@endsection

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('[data-cs-form]');
        if (!form) return;

        var kode = form.querySelector('#kode_pengajuan');
        var btn  = form.querySelector('[data-cs-submit]');
        var text = form.querySelector('[data-cs-submit-text]');

        // Kode otomatis huruf besar & tanpa spasi
        if (kode) {
            kode.addEventListener('input', function () {
                var pos = kode.selectionStart;
                kode.value = kode.value.toUpperCase().replace(/\s+/g, '');
                kode.setSelectionRange(pos, pos);
            });
        }

        // Tandai field kosong/tidak valid sebelum submit
        form.addEventListener('submit', function (e) {
            var firstInvalid = null;
            form.querySelectorAll('.form-control').forEach(function (el) {
                var bad = !el.checkValidity();
                el.classList.toggle('is-invalid', bad);
                if (bad && !firstInvalid) firstInvalid = el;
            });
            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
                return;
            }
            if (btn) btn.setAttribute('disabled', 'disabled');
            if (text) text.textContent = 'Mencari…';
        });

        form.addEventListener('input', function (e) {
            if (e.target.classList) e.target.classList.remove('is-invalid');
        });

        // Tombol "Back" browser: kembalikan tombol ke keadaan semula
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            if (btn) btn.removeAttribute('disabled');
            if (text) text.textContent = 'Cari status pengajuan';
        });
    })();
</script>
@endpush