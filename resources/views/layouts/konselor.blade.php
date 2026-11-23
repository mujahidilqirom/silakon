{{--
    SILAKON — Layout Portal Konselor
    Simpan sebagai: resources/views/layouts/konselor.blade.php
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Portal Konselor') — SILAKON</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/konselor.css') }}">
</head>
<body>

<div class="konselor-app">

    @include('konselor.partials.sidebar')

    <div class="konselor-utama">

        @include('konselor.partials.navbar')

        <main class="konselor-isi">
            @yield('content')
        </main>

    </div>
</div>

<script src="{{ asset('js/konselor.js') }}"></script>
@stack('script')
</body>
</html>