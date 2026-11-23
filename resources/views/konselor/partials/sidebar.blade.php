{{--
    SILAKON — Sidebar Konselor (responsive)
    Simpan sebagai: resources/views/konselor/partials/sidebar.blade.php
    CSS-nya ada di public/css/konselor.css (bagian SIDEBAR & RESPONSIF).

    Menandai menu aktif memakai @yield('menu') dari tiap halaman,
    misalnya: @section('menu', 'dashboard')

    Responsive:
      - > 1024px   : sidebar penuh (bisa diciutkan lewat tombol panah).
      - 861-1024px : jika navbar punya tombol #tombolMenu, sidebar jadi drawer;
                     jika tidak, tetap sidebar biasa.
      - 641-860px  : sama, tapi tanpa tombol #tombolMenu sidebar jadi rail ikon.
      - <= 640px   : sidebar jadi bottom navigation (menu geser horizontal,
                     tombol Keluar di ujung kanan).
--}}
@php
    $menuAktif = trim($__env->yieldContent('menu', 'dashboard'));

    $menu = [
        [
            'kunci' => 'dashboard',
            'label' => 'Dashboard',
            'url'   => route('konselor.dashboard'),
            'icon'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        ],
        [
            'kunci' => 'jadwal',
            'label' => 'Jadwal Saya',
            'url'   => route('konselor.jadwal'),
            'icon'  => '<rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/>',
        ],
        [
            'kunci' => 'kasus',
            'label' => 'Pendampingan Saya',
            'url'   => route('konselor.kasus.index'),
            'icon'  => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>',
        ],
        [
            'kunci' => 'catatan-konseling',
            'label' => 'Catatan Konseling',
            'url'   => route('konselor.catatan-konseling'),
            'icon'  => '<path d="M14 2.5H7a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7.5z"/><path d="M14 2.5v5h5"/><path d="M8.5 13h7M8.5 16.5h5"/>',
        ],
        [
            'kunci' => 'survei',
            'label' => 'Hasil Survei Saya',
            'url'   => route('konselor.survei'),
            'icon'  => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>',
        ],
        [
            'kunci' => 'profil',
            'label' => 'Profil',
            'url'   => route('konselor.profil'),
            'icon'  => '<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20.5c0-3.8 3.4-6.5 7.5-6.5s7.5 2.7 7.5 6.5"/>',
        ],
    ];
@endphp

<aside class="sidebar">

    <div class="sidebar-atas">
        <span class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SILAKON">
        </span>
        <span class="sidebar-nama">
            <b>SILAKON</b>
            <span>Portal Konselor</span>
        </span>
        <button type="button" class="sidebar-toggle" id="tombolSidebar" aria-label="Sembunyikan menu">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        </button>
    </div>

    <nav class="sidebar-menu">
        @foreach($menu as $item)
            <a href="{{ $item['url'] }}" class="sidebar-item {{ $menuAktif === $item['kunci'] ? 'aktif' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-bawah">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
        <button type="submit" class="sidebar-keluar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
            <span>Keluar</span>
        </button>
        </form>
    </div>

</aside>

{{-- Latar gelap di belakang drawer (hanya tampil di layar kecil) --}}
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<script>
(function(){
    if(window.__silakonDrawer) return;
    window.__silakonDrawer = true;

    var html = document.documentElement;
    var body = document.body;
    // Drawer hanya di 641-1024px. Di <= 640px sidebar jadi bottom navigation (diatur CSS).
    var mq = window.matchMedia('(min-width:641px) and (max-width:1024px)');

    function drawerAktif(){ return html.classList.contains('mode-drawer'); }
    function isOpen(){ return body.classList.contains('menu-terbuka'); }

    function setOpen(open){
        body.classList.toggle('menu-terbuka', open);
        var burger = document.getElementById('tombolMenu');
        if(burger){
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
            burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        }
    }

    // Mode drawer hanya aktif kalau layar tablet DAN tombol hamburger memang ada.
    function sinkron(){
        var aktif = !!document.getElementById('tombolMenu') && mq.matches;
        html.classList.toggle('mode-drawer', aktif);
        if(aktif){ body.classList.remove('sidebar-tutup'); }
        else { setOpen(false); }
        tengahkanMenuAktif();
    }

    // Bottom nav bisa digeser: pastikan menu yang aktif terlihat di tengah.
    function tengahkanMenuAktif(){
        var menu = document.querySelector('.sidebar-menu');
        var aktif = menu && menu.querySelector('.sidebar-item.aktif');
        if(!aktif || menu.scrollWidth <= menu.clientWidth) return;
        var m = menu.getBoundingClientRect(), a = aktif.getBoundingClientRect();
        menu.scrollLeft += (a.left + a.width / 2) - (m.left + m.width / 2);
    }

    // Fase capture: di mode drawer, tombol panah sidebar hanya MENUTUP drawer
    // (tidak menjalankan "ciutkan sidebar" versi desktop dari konselor.js).
    document.addEventListener('click', function(e){
        if(!drawerAktif()) return;

        if(e.target.closest('#tombolMenu')){
            setOpen(!isOpen());
            if(isOpen()){ var c = document.getElementById('tombolSidebar'); if(c) c.focus(); }
            return;
        }
        if(e.target.closest('#tombolSidebar')){
            e.preventDefault(); e.stopPropagation();
            setOpen(false);
            var b = document.getElementById('tombolMenu'); if(b) b.focus();
            return;
        }
        if(e.target.closest('#sidebarBackdrop') || e.target.closest('.sidebar a.sidebar-item')){
            setOpen(false);
        }
    }, true);

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && drawerAktif() && isOpen()){
            setOpen(false);
            var b = document.getElementById('tombolMenu'); if(b) b.focus();
        }
    });

    if(mq.addEventListener) mq.addEventListener('change', sinkron); else mq.addListener(sinkron);
    window.addEventListener('resize', tengahkanMenuAktif);

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', sinkron);
    else sinkron();
})();
</script>