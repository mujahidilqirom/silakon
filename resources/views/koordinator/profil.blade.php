{{--
    SILAKON — Profil Saya (Koordinator)
    Simpan sebagai: resources/views/koordinator/profil.blade.php
    Data dummy dikirim dari KoordinatorController@profil.
--}}
@extends('layouts.koordinator')

@section('judul', 'Profil Saya')
@section('subjudul', now()->translatedFormat('l, d F Y'))

@section('content')

<div style="background:#eef5ee;border:1px solid #cfe0cf;border-radius:14px;padding:16px 20px;margin-bottom:18px;color:#315b3a;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
    <div><strong>Notifikasi Telegram</strong><br><small>{{ auth()->user()->telegram_chat_id ? 'Telegram sudah terhubung.' : 'Hubungkan Telegram untuk menerima pengajuan mendesak.' }}</small></div>
    @if(auth()->user()->telegram_chat_id)
        <form method="POST" action="{{ route('telegram.unlink') }}">@csrf<button type="submit" class="k-btn-outline">Putuskan</button></form>
    @else
        <a href="{{ route('telegram.link') }}" class="k-btn-primary">Hubungkan Telegram</a>
    @endif
</div>

@if(session('sukses'))
    <div class="k-alert-sukses">{{ session('sukses') }}</div>
@endif

{{-- ============ HEAD ============ --}}
<div class="k-head-row">
    <div class="k-page-head">
        <h2>Profil Saya</h2>
        <p>Kelola informasi akun dan keamanan</p>
    </div>
</div>

<input type="checkbox" id="k-edit-toggle" class="k-edit-toggle-input" @if($errors->any() && !session('gagalPassword')) checked @endif>

<div class="k-grid k-profil-grid">

    {{-- ============ DATA DIRI ============ --}}
    <div class="k-card k-profil-card">
        <div class="k-profil-head">
            <h3>Data Diri</h3>
            <label for="k-edit-toggle" class="k-btn-outline k-edit-btn-show">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                Edit Profil
            </label>
        </div>

        {{-- --- tampilan (view mode) --- --}}
        <div class="k-profil-view">
            <div class="k-profil-avatar-wrap">
                <span class="k-profil-avatar">{{ $profil['inisial'] }}</span>
            </div>

            <dl class="k-profil-kv">
                <div>
                    <dt>Nama Lengkap</dt>
                    <dd>{{ $profil['nama'] }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $profil['email'] }}</dd>
                </div>
                <div>
                    <dt>No. WhatsApp</dt>
                    <dd>{{ $profil['wa'] }}</dd>
                </div>
                <div class="k-tanpa-garis">
                    <dt>Jabatan</dt>
                    <dd>{{ $profil['jabatan'] }}</dd>
                </div>
            </dl>
        </div>

        {{-- --- form edit (edit mode) --- --}}
        <form action="{{ url('/koordinator/profil') }}" method="POST" class="k-profil-edit-form">
            @csrf
            @method('PUT')

            <div class="k-profil-avatar-wrap">
                <span class="k-profil-avatar">{{ $profil['inisial'] }}</span>
            </div>

            <div class="k-field">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="k-input" value="{{ old('nama', $profil['nama']) }}">
                @error('nama')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="k-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="k-input" value="{{ old('email', $profil['email']) }}">
                @error('email')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="k-field">
                <label for="wa">No. WhatsApp</label>
                <input type="text" id="wa" name="wa" class="k-input" value="{{ old('wa', $profil['wa']) }}">
                @error('wa')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>
            <div class="k-field">
                <label for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" class="k-input" value="{{ old('jabatan', $profil['jabatan']) }}">
                @error('jabatan')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-profil-edit-actions">
                <label for="k-edit-toggle" class="k-btn-outline">Batal</label>
                <button type="submit" class="k-btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    {{-- ============ UBAH PASSWORD ============ --}}
    <div class="k-card k-profil-card">
        <h3 class="k-profil-card-title">Ubah Password</h3>

        <form action="{{ url('/koordinator/profil/password') }}" method="POST" class="k-password-form">
            @csrf
            @method('PUT')

            <div class="k-field">
                <label for="password_saat_ini">Password Saat Ini</label>
                <div class="k-password-wrap">
                    <input type="password" id="password_saat_ini" name="password_saat_ini" class="k-input">
                    <button type="button" class="k-eye-toggle" data-target="password_saat_ini" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password_saat_ini')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-field">
                <label for="password_baru">Password Baru</label>
                <div class="k-password-wrap">
                    <input type="password" id="password_baru" name="password_baru" class="k-input">
                    <button type="button" class="k-eye-toggle" data-target="password_baru" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password_baru')<small class="k-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="k-field">
                <label for="password_baru_confirmation">Konfirmasi Password Baru</label>
                <div class="k-password-wrap">
                    <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" class="k-input">
                    <button type="button" class="k-eye-toggle" data-target="password_baru_confirmation" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <small>Password minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol.</small>
            </div>

            <button type="submit" class="k-btn k-btn-full">Ubah Password</button>
        </form>
    </div>
</div>

{{-- ============ INFORMASI PERAN ============ --}}
<div class="k-card k-profil-peran">
    <h3 class="k-panel-title k-title-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        Informasi Peran
    </h3>

    <div class="k-peran-grid">
        <div>
            <h4>Peran yang Dimiliki</h4>
            <div class="k-peran-badges">
                @foreach($profil['peran'] as $p)
                    <span class="k-peran-badge" style="background:{{ $peranBadge[$p]['bg'] }}; color:{{ $peranBadge[$p]['warna'] }}">{{ $p }}</span>
                @endforeach
            </div>
        </div>
        <div>
            <h4>Peran Aktif Saat Ini</h4>
            <div class="k-peran-badges">
                <span class="k-peran-badge" style="background:{{ $aktifBadge['bg'] }}; color:{{ $aktifBadge['warna'] }}">{{ $profil['peran_aktif'] }}</span>
            </div>
        </div>
        <div>
            <h4>Catatan</h4>
            <div class="k-peran-note">
                Gunakan tombol <b>Ganti Peran</b> di header untuk beralih antara peran Admin, Konselor, dan Koordinator. Setiap peran memiliki akses menu yang berbeda.
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.k-eye-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.classList.toggle('is-visible', isHidden);
        });
    });
</script>
@endpush

@push('styles')
<style>
    .k-alert-sukses {
        background: #EAF3E6; color: #3F5A33; border: 1px solid #C4DCB8;
        padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 18px;
    }

    .k-edit-toggle-input { display: none; }

    .k-profil-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 22px; align-items: start; margin-bottom: 22px; }
    @media (max-width: 900px) { .k-profil-grid { grid-template-columns: 1fr; } }

    .k-profil-card { padding: 26px 28px; }
    .k-profil-card-title { margin: 0 0 22px; font-size: 16px; font-weight: 700; color: #1B2432; }

    .k-profil-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
    .k-profil-head h3 { margin: 0; font-size: 16px; font-weight: 700; color: #1B2432; }

    .k-btn-outline {
        display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
        background: #fff; color: #6E8F60; border: 1px solid #C4DCB8;
        padding: 8px 14px; border-radius: 9px; font-size: 13.5px; font-weight: 600;
        transition: background .15s ease;
    }
    .k-btn-outline:hover { background: #F3F8F1; }
    .k-btn-outline svg { width: 14px; height: 14px; }

    .k-profil-avatar-wrap { display: flex; justify-content: center; margin-bottom: 26px; }
    .k-profil-avatar {
        width: 90px; height: 90px; border-radius: 50%; background: #6E8F60; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; font-weight: 700; letter-spacing: .02em;
    }

    .k-profil-kv { margin: 0; }
    .k-profil-kv > div { padding: 14px 0; border-bottom: 1px solid #EDEEEA; }
    .k-profil-kv > div.k-tanpa-garis { border-bottom: none; padding-bottom: 0; }
    .k-profil-kv > div:first-child { padding-top: 0; }
    .k-profil-kv dt { font-size: 13px; font-weight: 700; color: #1B2432; margin-bottom: 6px; }
    .k-profil-kv dd { margin: 0; font-size: 14.5px; color: #4B5160; }

    /* toggle: default tampilkan view, sembunyikan form */
    .k-profil-edit-form { display: none; }
    #k-edit-toggle:checked ~ .k-profil-grid .k-profil-view { display: none; }
    #k-edit-toggle:checked ~ .k-profil-grid .k-profil-edit-form { display: block; }
    #k-edit-toggle:checked ~ .k-profil-grid .k-edit-btn-show { display: none; }

    .k-profil-edit-form .k-field { margin-bottom: 16px; }
    .k-profil-edit-form label { display: block; font-size: 13px; font-weight: 600; color: #1B2432; margin-bottom: 6px; }
    .k-profil-edit-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px; }

    .k-field { margin-bottom: 18px; }
    .k-field:last-of-type { margin-bottom: 20px; }
    .k-field label { display: block; font-size: 13.5px; font-weight: 600; color: #1B2432; margin-bottom: 7px; }
    .k-field small { display: block; font-size: 12.5px; color: #8A8F98; margin-top: 7px; }
    .k-field small.k-field-error { color: #D64545; }

    .k-input {
        width: 100%; box-sizing: border-box; border: 1px solid #E2E4E9; border-radius: 10px;
        padding: 11px 14px; font-size: 14px; font-family: inherit; color: #1B2432; background: #fff;
    }
    .k-input:focus { outline: none; border-color: #6E8F60; box-shadow: 0 0 0 3px rgba(110,143,96,.15); }

    .k-password-wrap { position: relative; }
    .k-password-wrap .k-input { padding-right: 44px; }
    .k-eye-toggle {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 32px; height: 32px; border: none; background: transparent; cursor: pointer;
        display: flex; align-items: center; justify-content: center; color: #9AA0A8; border-radius: 6px;
    }
    .k-eye-toggle:hover { background: #F5F6F3; color: #6B7280; }
    .k-eye-toggle svg { width: 18px; height: 18px; }
    .k-eye-toggle.is-visible { color: #6E8F60; }

    .k-btn-full { width: 100%; justify-content: center; }

    .k-profil-peran { padding: 26px 28px; }
    .k-peran-grid { display: grid; grid-template-columns: 1fr 1fr 1.4fr; gap: 28px; margin-top: 18px; }
    @media (max-width: 900px) { .k-peran-grid { grid-template-columns: 1fr; } }
    .k-peran-grid h4 { margin: 0 0 12px; font-size: 13px; font-weight: 700; color: #1B2432; }

    .k-peran-badges { display: flex; flex-wrap: wrap; gap: 8px; }
    .k-peran-badge { padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; }

    .k-peran-note {
        background: #FAFBF8; border: 1px solid #EDEEEA; border-radius: 10px;
        padding: 12px 14px; font-size: 13.5px; color: #4B5160; line-height: 1.55;
    }
</style>
@endpush
