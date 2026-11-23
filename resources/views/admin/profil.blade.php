{{--
    SILAKON — Profil Saya (Admin)
    Simpan sebagai: resources/views/admin/profil.blade.php

    Route yang dibutuhkan (method profil() ada di AdminDashboardController):
    Route::get('/admin/profil', [AdminDashboardController::class, 'profil'])
        ->name('admin.profil');

    Profil, password, preferensi notifikasi, dan jam operasional tersimpan melalui backend.
--}}
@extends('layouts.admin')

@section('judul', 'Profil Saya')

@section('content')

<div style="max-width:760px;margin:0 auto 18px;background:#eef5ee;border:1px solid #cfe0cf;border-radius:14px;padding:16px 20px;color:#315b3a;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
    <div><strong>Notifikasi Telegram</strong><br><small>{{ auth()->user()->telegram_chat_id ? 'Telegram sudah terhubung.' : 'Hubungkan Telegram untuk menerima pengajuan mendesak.' }}</small></div>
    @if(auth()->user()->telegram_chat_id)
        <form method="POST" action="{{ route('telegram.unlink') }}">@csrf<button type="submit" class="pf-btn pf-btn-outline">Putuskan</button></form>
    @else
        <a href="{{ route('telegram.link') }}" class="pf-btn pf-btn-primary">Hubungkan Telegram</a>
    @endif
</div>

<style>
    .pf-page{max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:24px;padding:8px 0 24px;}

    .pf-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;
        padding:32px;box-shadow:0 2px 8px rgba(31,42,36,.04);
    }
    .pf-title{
        display:flex;align-items:center;gap:10px;margin:0 0 22px;
        font-size:17px;font-weight:700;color:#1f2a24;
    }
    .pf-title svg{width:16px;height:16px;color:#7fa285;flex-shrink:0;}

    .pf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
    @media (max-width:640px){ .pf-grid{grid-template-columns:1fr;} .pf-card{padding:22px;} }

    .pf-field{display:flex;flex-direction:column;gap:9px;}
    .pf-field.pf-full{margin-bottom:20px;}
    .pf-field.pf-full:last-of-type{margin-bottom:20px;}
    .pf-label{font-size:13px;font-weight:700;color:#1f2a24;}
    .pf-input{
        width:100%;box-sizing:border-box;height:46px;background:#fff;
        border:1px solid #e1e7e3;border-radius:10px;padding:0 16px;
        font-size:14px;color:#1f2a24;font-family:inherit;outline:none;
    }
    .pf-input:focus{border-color:#7fa285;box-shadow:0 0 0 3px rgba(127,162,133,.16);}
    .pf-input::placeholder{color:#8a978f;letter-spacing:2px;}

    .pf-btn{
        display:inline-flex;align-items:center;justify-content:center;
        padding:11px 24px;border-radius:10px;font-size:14px;font-weight:600;
        cursor:pointer;font-family:inherit;white-space:nowrap;
    }
    .pf-btn-primary{background:#7fa285;border:1px solid #7fa285;color:#fff;}
    .pf-btn-primary:hover{background:#6f9376;}
    .pf-btn-outline{background:#fff;border:1px solid #7fa285;color:#5c8564;}
    .pf-btn-outline:hover{background:#f1f6f2;}
    .pf-btn-primary,.pf-btn-outline{margin-top:0;}

    /* Notifikasi */
    .pf-notif-list{display:flex;flex-direction:column;gap:14px;}
    .pf-notif{
        display:flex;align-items:center;justify-content:space-between;gap:16px;
        background:#f7f9f8;border-radius:12px;padding:16px;
    }
    .pf-notif-judul{font-size:14px;font-weight:700;color:#1f2a24;margin:0 0 4px;}
    .pf-notif-desk{font-size:12.5px;color:#8a978f;margin:0;}
    .pf-notif-wrap{margin-bottom:8px;}

    .pf-switch{position:relative;display:inline-block;width:48px;height:28px;flex-shrink:0;cursor:pointer;}
    .pf-switch input{position:absolute;opacity:0;inset:0;width:100%;height:100%;margin:0;cursor:pointer;z-index:1;}
    .pf-switch span{
        position:absolute;inset:0;background:#d7dbd8;border-radius:999px;transition:background .15s;
    }
    .pf-switch span::after{
        content:"";position:absolute;top:3px;left:3px;width:22px;height:22px;
        background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .15s;
    }
    .pf-switch input:checked + span{background:#7fa285;}
    .pf-switch input:checked + span::after{transform:translateX(20px);}
    .pf-switch input:focus-visible + span{box-shadow:0 0 0 3px rgba(127,162,133,.3);}

    /* Jam operasional */
    .pf-jam-list{display:flex;flex-direction:column;gap:12px;margin-bottom:20px;}
    .pf-jam{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
        background:#f7f9f8;border-radius:12px;padding:12px 16px;min-height:26px;
    }
    .pf-jam-hari{font-size:14px;color:#1f2a24;}
    .pf-jam-range{display:flex;align-items:center;gap:14px;}
    .pf-jam-sampai{font-size:13px;color:#8a978f;}
    .pf-time{
        width:110px;box-sizing:border-box;height:34px;background:#fff;
        border:1px solid #e1e7e3;border-radius:8px;padding:0 10px;
        font-size:13px;color:#1f2a24;font-family:inherit;outline:none;
    }
    .pf-time:focus{border-color:#7fa285;}
    .pf-tutup{
        background:#fde4e4;color:#c0392b;font-size:12px;font-weight:700;
        padding:5px 12px;border-radius:999px;
    }

    /* Informasi sistem */
    .pf-info{background:linear-gradient(135deg,#f3f8f4,#e9f2eb);border-color:#9fc3a8;}
    .pf-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px 16px;}
    .pf-info-label{font-size:12px;color:#7a877f;margin:0 0 4px;}
    .pf-info-nilai{font-size:14.5px;font-weight:700;color:#1f2a24;margin:0;}

    /* Toast */
    .pf-toast{
        position:fixed;right:24px;bottom:24px;background:#1f2a24;color:#fff;
        padding:12px 18px;border-radius:10px;font-size:13.5px;
        box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:0;transform:translateY(8px);
        pointer-events:none;transition:opacity .2s,transform .2s;z-index:1100;
    }
    .pf-toast.is-show{opacity:1;transform:translateY(0);}
    .pf-toast.is-error{background:#c0392b;}
</style>

<div class="pf-page">

    {{-- Profil Admin --}}
    <section class="pf-card">
        <h2 class="pf-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
            Profil Admin
        </h2>

        <form id="pfFormProfil" action="{{ route('admin.profil.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="pf-grid">
                <div class="pf-field">
                    <label class="pf-label" for="pfNama">Nama Lengkap</label>
                    <input class="pf-input" type="text" id="pfNama" name="nama" value="{{ $profil['nama'] }}" required>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="pfEmail">Email Admin</label>
                    <input class="pf-input" type="email" id="pfEmail" name="email" value="{{ $profil['email'] }}" required>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="pfHp">Nomor HP</label>
                    <input class="pf-input" type="text" id="pfHp" name="telepon" value="{{ $profil['telepon'] }}">
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="pfJabatan">Jabatan</label>
                    <input class="pf-input" type="text" id="pfJabatan" value="{{ $profil['jabatan'] }}">
                </div>
            </div>
            <button type="submit" class="pf-btn pf-btn-primary">Simpan Perubahan</button>
        </form>
    </section>

    {{-- Ubah Password --}}
    <section class="pf-card">
        <h2 class="pf-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Ubah Password
        </h2>

        <form id="pfFormPassword" action="{{ route('admin.profil.password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="pf-field pf-full">
                <label class="pf-label" for="pfPassLama">Password Saat Ini</label>
                <input class="pf-input" type="password" id="pfPassLama" name="password_lama" placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <div class="pf-field pf-full">
                <label class="pf-label" for="pfPassBaru">Password Baru</label>
                <input class="pf-input" type="password" id="pfPassBaru" name="password_baru" placeholder="••••••••" autocomplete="new-password" minlength="8" required>
            </div>
            <div class="pf-field pf-full">
                <label class="pf-label" for="pfPassKonfirmasi">Konfirmasi Password Baru</label>
                <input class="pf-input" type="password" id="pfPassKonfirmasi" name="password_baru_confirmation" placeholder="••••••••" autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="pf-btn pf-btn-outline">Ubah Password</button>
        </form>
    </section>

    {{-- Pengaturan Notifikasi --}}
    <section class="pf-card">
        <h2 class="pf-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            Pengaturan Notifikasi
        </h2>

        <form class="pf-notif-list pf-notif-wrap" action="{{ route('admin.profil.preferensi') }}" method="POST">
            @csrf
            @foreach($notifikasi as $n)
                <div class="pf-notif">
                    <div>
                        <p class="pf-notif-judul">{{ $n['judul'] }}</p>
                        <p class="pf-notif-desk">{{ $n['deskripsi'] }}</p>
                    </div>
                    <label class="pf-switch" title="{{ $n['judul'] }}">
                        <input type="checkbox" name="notif_{{ $n['key'] }}" {{ $n['aktif'] ? 'checked' : '' }}
                               >
                        <span></span>
                    </label>
                </div>
            @endforeach
            <button type="submit" class="pf-btn pf-btn-outline">Simpan Pengaturan Notifikasi</button>
        </form>
    </section>

    {{-- Jam Operasional --}}
    <section class="pf-card">
        <h2 class="pf-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            Jam Operasional
        </h2>

        <form id="pfFormJam" action="{{ route('admin.profil.jam') }}" method="POST">
            @csrf
            <div class="pf-jam-list">
                @foreach($jamOperasional as $j)
                    <div class="pf-jam">
                        <span class="pf-jam-hari">{{ $j['hari'] }}</span>
                        @if($j['tutup'])
                            <span class="pf-tutup">Tutup</span>
                        @else
                            <div class="pf-jam-range">
                                <input type="time" class="pf-time" name="jam_{{ $j['key'] }}_buka" value="{{ $j['buka'] }}" required>
                                <span class="pf-jam-sampai">sampai</span>
                                <input type="time" class="pf-time" name="jam_{{ $j['key'] }}_tutup" value="{{ $j['tutup_jam'] }}" required>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="submit" class="pf-btn pf-btn-primary">Simpan Jam Operasional</button>
        </form>
    </section>

    {{-- Informasi Sistem --}}
    <section class="pf-card pf-info">
        <h2 class="pf-title" style="margin-bottom:20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
            Informasi Sistem
        </h2>
        <div class="pf-info-grid">
            @foreach($infoSistem as $i)
                <div>
                    <p class="pf-info-label">{{ $i['label'] }}</p>
                    <p class="pf-info-nilai">{{ $i['nilai'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

</div>

<div class="pf-toast" id="pfToast" role="status"></div>

<script>
    var pfToastTimer = null;

    function pfToast(pesan, galat) {
        var el = document.getElementById('pfToast');
        el.textContent = pesan;
        el.classList.toggle('is-error', !!galat);
        el.classList.add('is-show');
        clearTimeout(pfToastTimer);
        pfToastTimer = setTimeout(function () { el.classList.remove('is-show'); }, 2600);
    }

</script>

@endsection
