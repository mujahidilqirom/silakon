{{--
    SILAKON — Navbar Koordinator
    Simpan sebagai: resources/views/koordinator/partials/navbar.blade.php
    Judul & subjudul diambil dari @section('judul') dan @section('subjudul') di halaman.
    Nama pengguna masih statis; nanti ganti dengan auth()->user().
--}}
@php
    $user = auth()->user(); $namaPengguna = $user?->name ?? 'Koordinator LPPP';
    $peranPengguna = 'Koordinator';
    $inisial = strtoupper(mb_substr($peranPengguna, 0, 1));
    $notifDarurat = \App\Models\Pengajuan::whereIn('data->kondisi_darurat', ['ya','mendesak','darurat'])->where(function($q){$q->whereNull('data->darurat_status')->orWhere('data->darurat_status','!=','ditangani');})->count();
    $notifTiket = \App\Models\Pengajuan::where('status', \App\Models\Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN)->count();
@endphp

<header class="k-navbar">
    <div class="k-navbar-title">
        <h1>@yield('judul', 'Dashboard Koordinator')</h1>
        <p>@yield('subjudul')</p>
    </div>

    <div class="k-navbar-right">
        <div class="k-notif-wrap"><button type="button" class="k-bell" aria-label="Notifikasi" onclick="document.getElementById('koorNotifMenu').classList.toggle('is-open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            @if($notifDarurat+$notifTiket>0)<span class="k-bell-dot"></span>@endif
        </button><div class="k-notif-menu" id="koorNotifMenu"><strong>Notifikasi</strong><a href="{{ route('koordinator.kasus') }}">{{ $notifDarurat }} kasus mendesak</a><a href="{{ route('koordinator.dashboard') }}">{{ $notifTiket }} pengajuan menunggu pemeriksaan</a></div></div>

    <div class="k-user" aria-label="Profil pengguna">
            <span class="k-avatar">{{ $inisial }}</span>
            <span class="k-user-text">
                <strong>{{ $namaPengguna }}</strong>
                <span>{{ $peranPengguna }}</span>
            </span>
    </div>
    </div>
</header>
<style>.k-notif-wrap{position:relative}.k-notif-menu{display:none;position:absolute;right:0;top:44px;width:270px;background:#fff;border:1px solid #e1e7e3;border-radius:12px;padding:10px;box-shadow:0 12px 30px #1b24321f;z-index:30}.k-notif-menu.is-open{display:block}.k-notif-menu strong{display:block;padding:6px 8px}.k-notif-menu a{display:block;padding:9px 8px;color:#34443a;text-decoration:none;font-size:13px;border-radius:8px}.k-notif-menu a:hover{background:#f3f7f3}</style>
