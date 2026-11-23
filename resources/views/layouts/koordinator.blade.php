{{--
    SILAKON — Layout Koordinator
    Simpan sebagai: resources/views/layouts/koordinator.blade.php
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Dashboard Koordinator') — SILAKON</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/koordinator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/koordinator-pages-responsive.css') }}">
    @stack('styles')
</head>
<body>
    <div class="k-shell" id="kShell">

        @include('koordinator.partials.sidebar')

        <div class="k-main">
            @include('koordinator.partials.navbar')

            <main class="k-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            var shell = document.getElementById('kShell');
            var btn = document.getElementById('kSidebarToggle');
            if (!shell || !btn) return;

            try {
                if (localStorage.getItem('silakon.sidebar') === 'collapsed') {
                    shell.classList.add('is-collapsed');
                }
            } catch (e) {}

            btn.addEventListener('click', function () {
                var collapsed = shell.classList.toggle('is-collapsed');
                btn.setAttribute('aria-expanded', String(!collapsed));
                try {
                    localStorage.setItem('silakon.sidebar', collapsed ? 'collapsed' : 'open');
                } catch (e) {}
            });
        })();
        // Pada navigasi bawah mobile, geser otomatis ke menu aktif agar tidak
        // tersembunyi di sisi kanan saat jumlah menu melebihi lebar layar.
        document.addEventListener('DOMContentLoaded', function () {
            var nav = document.querySelector('.k-sidebar .k-nav');
            var active = nav && nav.querySelector('.k-nav-item.is-active');
            if (active && window.matchMedia('(max-width: 640px)').matches) {
                setTimeout(function () { active.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' }); }, 0);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
