{{--
    SILAKON — Navbar Konselor (responsive)
    Simpan sebagai: resources/views/konselor/partials/navbar.blade.php

    Judul & subjudul diisi dari halaman:
        @section('judul', 'Dashboard Konselor')
        @section('subjudul', 'Minggu, 20 September 2026')

    Nama konselor masih statis. Nanti tinggal ganti dengan auth()->user().

    Responsive:
      - <= 1024px : muncul tombol hamburger (#tombolMenu) untuk membuka sidebar.
                    Logikanya ada di partials/sidebar.blade.php.
      - <= 640px  : judul dipersingkat (…), profil jadi avatar saja.
--}}
@php
    $user = auth()->user();
    $konselor = [
        'nama'     => $user?->name ?? 'Konselor',
        'peran'    => 'Konselor',
        'inisial'  => collect(explode(' ', trim($user?->name ?? 'K')))->filter()->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode(''),
        'notifikasi' => true,
    ];
    $notifJadwal = \App\Models\JadwalKonseling::where('konselor_id', auth()->id())->whereIn('status',['terjadwal','berlangsung'])->whereDate('tanggal','>=',today())->count();
@endphp

<header class="navbar">

    <div class="navbar-kiri">
        <button type="button" class="navbar-menu" id="tombolMenu" aria-label="Buka menu" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5h16M4 12h16M4 17.5h16"/></svg>
        </button>

        <div class="navbar-judul">
            <h1>@yield('judul', 'Dashboard Konselor')</h1>
            <p>@yield('subjudul')</p>
        </div>
    </div>

    <div class="navbar-kanan">

        <div class="konselor-notif-wrap"><button type="button" class="navbar-lonceng" aria-label="Notifikasi" onclick="document.getElementById('konselorNotifMenu').classList.toggle('is-open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8.5a6 6 0 1 0-12 0c0 6-2 7.5-2 7.5h16s-2-1.5-2-7.5"/><path d="M13.7 20a2 2 0 0 1-3.4 0"/></svg>
            @if($notifJadwal > 0)
                <span class="titik"></span>
            @endif
        </button><div class="konselor-notif-menu" id="konselorNotifMenu"><strong>Notifikasi</strong><a href="{{ route('konselor.jadwal') }}">{{ $notifJadwal }} jadwal konseling aktif<small>Lihat jadwal saya</small></a></div></div>

        <div class="navbar-profil" aria-label="Profil {{ $konselor['nama'] }}">
            <span class="navbar-avatar">{{ $konselor['inisial'] }}</span>
            <span class="navbar-profil-teks">
                <b>{{ $konselor['nama'] }}</b>
                <small>{{ $konselor['peran'] }}</small>
            </span>
        </div>

    </div>
</header>
<style>.konselor-notif-wrap{position:relative}.konselor-notif-menu{display:none;position:absolute;right:0;top:44px;width:260px;background:#fff;border:1px solid #e1e7e3;border-radius:12px;padding:10px;box-shadow:0 12px 30px #1b24321f;z-index:30}.konselor-notif-menu.is-open{display:block}.konselor-notif-menu strong{display:block;padding:6px}.konselor-notif-menu a{display:block;padding:9px 6px;color:#34443a;text-decoration:none;font-size:13px;border-radius:8px}.konselor-notif-menu a:hover{background:#f3f7f3}.konselor-notif-menu small{display:block;color:#7b877f;margin-top:2px}</style>

<style>
    /* ===== Navbar responsive ===== */
    .navbar-kiri{ display:flex; align-items:center; gap:12px; min-width:0; }
    .navbar-judul{ min-width:0; }
    .navbar-menu{ display:none; }

    @media (max-width:1024px){
        .navbar{ padding-left:16px; padding-right:16px; }
        .navbar-menu{
            display:inline-flex; align-items:center; justify-content:center;
            flex:none; width:40px; height:40px; padding:0;
            border:1px solid #E5E9E5; border-radius:12px;
            background:#fff; color:#1F2A24; cursor:pointer;
            transition:background .15s ease, transform .1s ease;
        }
        .navbar-menu:hover{ background:#F3F6F3; }
        .navbar-menu:active{ transform:scale(.94); }
        .navbar-menu svg{ width:20px; height:20px; }
    }

    @media (max-width:640px){
        .navbar{ gap:10px; padding-left:12px; padding-right:12px; }
        .navbar-kiri{ flex:1 1 auto; gap:10px; }
        .navbar-judul h1{ font-size:17px; line-height:1.25; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .navbar-judul p{ font-size:11.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .navbar-kanan{ flex:none; gap:8px; }
        /* profil: avatar saja */
        .navbar-profil{ padding:0; gap:0; background:transparent; border:0; }
        .navbar-profil-teks,
        .navbar-profil-panah{ display:none; }
    }

    @media (max-width:380px){
        .navbar-judul p{ display:none; }
    }
</style>
