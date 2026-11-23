{{--
    SILAKON — Partial Sidebar
    Simpan sebagai: resources/views/admin/partials/sidebar.blade.php
    Dipakai oleh: resources/views/layouts/admin.blade.php

    PERBAIKAN dari versi sebelumnya:
    - 'url' sekarang pakai NAMA ROUTE (bukan '#'), lalu di-resolve pakai
      Route::has() + route(). Kalau route-nya belum dibuat, otomatis fallback
      ke '#' supaya tidak error "Route [...] not defined".
    - Begitu route baru (mis. admin.penjadwalan) sudah ada, link otomatis
      hidup tanpa perlu ubah blade ini lagi.

    Tombol "Keluar" dipisah dari <nav> ke .admin-sidebar-foot supaya selalu
    menempel di dasar sidebar (tidak ikut ter-scroll bersama menu).

    Semua gaya sidebar (termasuk badge & footer) ada di public/css/admin.css.
    Tidak ada <style> atau style inline di sini supaya tidak menimpa aturan
    responsif (rail ikon <=900px, bottom navigation <=640px).
--}}
@php
    $menuUtama = [
        ['key' => 'dashboard',        'label' => 'Dashboard',          'route' => 'admin.dashboard',   'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.6"/><rect x="14" y="3" width="7" height="7" rx="1.6"/><rect x="3" y="14" width="7" height="7" rx="1.6"/><rect x="14" y="14" width="7" height="7" rx="1.6"/>'],
        ['key' => 'pendaftaran',      'label' => 'Pengajuan Masuk',    'route' => 'admin.pendaftaran', 'icon' => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>'],
        ['key' => 'penjadwalan',      'label' => 'Penjadwalan',        'route' => 'admin.penjadwalan', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        ['key' => 'kasus-mendesak',   'label' => 'Pengajuan Mendesak', 'route' => 'admin.kasus-mendesak', 'icon' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>'],
        ['key' => 'semua-tiket',      'label' => 'Semua Pengajuan',   'route' => 'admin.semua-tiket', 'icon' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2m0 10v2m0-14v2m0 6v2"/>'],
        ['key' => 'data-konseli',     'label' => 'Data Konseli',       'route' => 'admin.data-konseli', 'icon' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>'],
        ['key' => 'konseling-kelompok',         'label' => 'Konseling Kelompok', 'route' => 'admin.konseling-kelompok',    'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>'],
        ['key' => 'survei', 'label' => 'Survei', 'route' => 'admin.survei', 'icon' => '<path d="M3 3v18h18"/><path d="M7 16v-5M12 16V8M17 16v-8"/>'],
        ['key' => 'self-help',        'label' => 'Self-help',           'route' => 'admin.self-help',   'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/>'],
        ['key' => 'data-master',      'label' => 'Data Master',        'route' => 'admin.data-master', 'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>'],
        ['key' => 'profil',           'label' => 'Profil',             'route' => 'admin.profil',      'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 14 0v1"/>'],
    ];

    // Menu aktif dikirim dari controller: return view('admin.pendaftaran', [...])->with('menuAktif', 'pendaftaran');
    $menuAktif = $menuAktif ?? 'dashboard';
    $jumlahPengajuanBaru = $jumlahPengajuanBaru ?? \App\Models\Pengajuan::where('status', \App\Models\Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN)->count();
@endphp

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-head">
        <span class="admin-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON">
        </span>
        <span class="admin-brand">
            <strong>SILAKON</strong>
            <span>Portal Admin</span>
        </span>
        <button type="button" class="admin-collapse" data-collapse aria-label="Ciutkan sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
        </button>
    </div>

    <nav class="admin-nav">
        @foreach($menuUtama as $item)
            @php
                // Kalau route belum didaftarkan di web.php, fallback ke '#'
                // supaya tidak error "Route [xxx] not defined".
                $href = \Illuminate\Support\Facades\Route::has($item['route'])
                    ? route($item['route'])
                    : '#';
            @endphp
            <a href="{{ $href }}"
               class="admin-nav-link {{ $menuAktif === $item['key'] ? 'is-active' : '' }}"
               title="{{ $item['label'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                <span>{{ $item['label'] }}</span>
                @if($item['key'] === 'pendaftaran' && ($jumlahPengajuanBaru ?? 0) > 0)
                    <span class="admin-nav-badge" aria-label="{{ $jumlahPengajuanBaru }} pengajuan baru">{{ $jumlahPengajuanBaru }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer tetap (freeze): Keluar --}}
    <div class="admin-sidebar-foot">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
        <button type="submit" class="admin-nav-link is-danger" title="Keluar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
            </svg>
            <span>Keluar</span>
        </button>
        </form>
    </div>
</aside>

<script>
// Bottom nav bisa digeser: tampilkan menu aktif di tengah.
(function(){
    var nav = document.querySelector('#adminSidebar .admin-nav');
    var aktif = nav && nav.querySelector('.admin-nav-link.is-active');
    if(!aktif || nav.scrollWidth <= nav.clientWidth) return;
    var n = nav.getBoundingClientRect(), a = aktif.getBoundingClientRect();
    nav.scrollLeft += (a.left + a.width / 2) - (n.left + n.width / 2);
})();
</script>