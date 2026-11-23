{{--
    SILAKON — Sidebar Koordinator
    Simpan sebagai: resources/views/koordinator/partials/sidebar.blade.php
    Logo: public/images/logo.png
--}}
@php
    use Illuminate\Support\Facades\Route;

    $menu = [
        [
            'label' => 'Dashboard Analisis',
            'route' => 'koordinator.dashboard',
            'aktif' => 'koordinator.dashboard',
            'icon'  => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        ],
        [
            'label' => 'Pengajuan Mendesak',
            'route' => 'koordinator.kasus',
            'aktif' => 'koordinator.kasus',
            'icon'  => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        ],
        [
            'label' => 'Ringkasan Pengajuan',
            'route' => 'koordinator.catatan-konseling',
            'aktif' => 'koordinator.catatan-konseling',
            'icon'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        ],
        [
            'label' => 'Konseling Kelompok',
            'route' => 'kelompok',
            'aktif' => 'kelompok',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
        [
            'label' => 'Self-help',
            'route' => 'koordinator.self-help',
            'aktif' => 'koordinator.self-help',
            'icon'  => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
        ],
        [
            'label' => 'Laporan',
            'route' => 'koordinator.laporan',
            'aktif' => 'koordinator.laporan',
            'icon'  => '<line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/>',
        ],
        [
            'label' => 'Hasil Survei',
            'route' => 'koordinator.survei',
            'aktif' => 'koordinator.survei*',
            'icon'  => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
        ],
        [
            'label' => 'Kelola Pengguna',
            'route' => 'pengguna',
            'aktif' => 'pengguna',
            'icon'  => '<circle cx="18" cy="15" r="3"/><circle cx="9" cy="7" r="4"/><path d="M10 15H6a4 4 0 0 0-4 4v2"/><path d="m21.7 16.4-.9-.3"/><path d="m15.2 13.9-.9-.3"/><path d="m16.6 18.7.3-.9"/><path d="m19.1 12.2.3-.9"/><path d="m19.6 18.7-.4-1"/><path d="m16.8 12.3-.4-1"/><path d="m14.3 16.6 1-.4"/><path d="m20.7 13.8 1-.4"/>',
        ],
        [
            'label' => 'Pengaturan',
            'route' => 'koordinator.pengaturan',
            'aktif' => 'koordinator.pengaturan',
            'icon'  => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
        ],
        [
            'label' => 'Log Aktivitas',
            'route' => 'koordinator.log',
            'aktif' => 'koordinator.log*',
            'icon'  => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
        ],
        [
            'label' => 'Profil',
            'route' => 'koordinator.profil',
            'aktif' => 'koordinator.profil*',
            'icon'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        ],
    ];
@endphp

<aside class="k-sidebar" aria-label="Menu utama">
    <div class="k-brand">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON" class="k-brand-logo">
        <div class="k-brand-text">
            <strong>SILAKON</strong>
            <span>Portal Koordinator</span>
        </div>
        <button type="button" class="k-collapse" id="kSidebarToggle" aria-label="Ciutkan sidebar" aria-expanded="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
    </div>

    <nav class="k-nav">
        @foreach($menu as $m)
            <a href="{{ Route::has($m['route']) ? route($m['route']) : '#' }}"
               class="k-nav-item {{ request()->routeIs($m['aktif']) ? 'is-active' : '' }}"
               title="{{ $m['label'] }}"
               @if(request()->routeIs($m['aktif'])) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $m['icon'] !!}</svg>
                <span>{{ $m['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="k-sidebar-foot">
        <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="k-nav-item is-danger" title="Keluar" style="width:100%;border:0;background:transparent;text-align:left;cursor:pointer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            <span>Keluar</span>
        </button>
        </form>
    </div>
</aside>
