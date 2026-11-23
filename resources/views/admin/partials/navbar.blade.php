{{--
    SILAKON — Partial Navbar (tanpa kolom pencarian)
    Simpan sebagai: resources/views/admin/partials/navbar.blade.php

    Judul & subjudul dikirim dari halaman lewat @section('judul') / @section('subjudul').
    Blok profil di kanan hanya tampilan (tanpa dropdown / klik).
--}}
@php
    $user = auth()->user();
    $namaPengguna = $user?->name ?? ($namaPengguna ?? 'Admin SILAKON');
    $peranPengguna = $user ? ucfirst($user->role) : ($peranPengguna ?? 'Admin');
    $inisial = strtoupper(collect(explode(' ', trim($namaPengguna)))->filter()->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode(''));
    $notifMenunggu = \App\Models\Pengajuan::where('status', \App\Models\Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN)->count();
    $notifDarurat = \App\Models\Pengajuan::whereIn('data->kondisi_darurat', ['ya','mendesak','darurat'])->where(function($q){$q->whereNull('data->darurat_status')->orWhere('data->darurat_status','!=','ditangani');})->count();
@endphp

<style>
    .admin-navbar .admin-user{cursor:default;text-decoration:none;}
    .admin-navbar .admin-user:hover{background:transparent;}
    .admin-notif-wrap{position:relative}.admin-notif-menu{display:none;position:absolute;right:0;top:44px;width:290px;background:#fff;border:1px solid #e1e7e3;border-radius:12px;padding:8px;box-shadow:0 12px 30px #1b24321f;z-index:30}.admin-notif-menu.is-open{display:block}.admin-notif-menu a{display:block;padding:10px;border-radius:8px;color:#34443a;text-decoration:none;font-size:13px}.admin-notif-menu a:hover{background:#f3f7f3}.admin-notif-menu small{display:block;color:#7b877f;margin-top:2px}
</style>

<header class="admin-navbar">
    <button type="button" class="admin-burger" data-burger aria-label="Buka menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M3 6h18M3 12h18M3 18h18"/>
        </svg>
    </button>

    <div class="admin-page-title">
        <h1>@yield('judul', 'Dashboard Admin')</h1>
        <p>@yield('subjudul', \Carbon\Carbon::now()->translatedFormat('l, j F Y'))</p>
    </div>

    <div class="admin-notif-wrap"><button type="button" class="admin-bell" aria-label="Notifikasi" onclick="document.getElementById('adminNotifMenu').classList.toggle('is-open')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>
        </svg>
        @if($notifMenunggu+$notifDarurat>0)<span class="dot"></span>@endif
    </button><div class="admin-notif-menu" id="adminNotifMenu"><strong style="padding:8px;display:block">Notifikasi</strong><a href="{{ route('admin.pendaftaran') }}">{{ $notifMenunggu }} pengajuan menunggu pemeriksaan<small>Perlu ditindaklanjuti</small></a><a href="{{ route('admin.kasus-mendesak') }}">{{ $notifDarurat }} pengajuan mendesak<small>Perlu penanganan</small></a></div></div>

    <div class="admin-user" aria-label="Profil pengguna">
        <span class="admin-avatar">{{ $inisial }}</span>
        <span class="admin-user-meta">
            <strong>{{ $namaPengguna }}</strong>
            <span>{{ $peranPengguna }}</span>
        </span>
    </div>
</header>
