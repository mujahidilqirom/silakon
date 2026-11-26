{{--
    SILAKON — Layout Panel Admin
    Simpan sebagai: resources/views/layouts/admin.blade.php

    Cara pakai di halaman:
        @extends('layouts.admin')
        @section('judul', 'Dashboard Admin')
        @section('content') ... @endsection
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Dashboard') — SILAKON</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">

    {{-- Pakai @vite(['resources/css/admin.css']) kalau proyek Anda memakai Vite --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-pages-responsive.css') }}">
    @stack('styles')
</head>
<body class="admin-body">

<div class="admin-shell" id="adminShell">

    @include('admin.partials.sidebar')

    <div class="admin-backdrop" data-backdrop></div>

    <div class="admin-main">
        @include('admin.partials.navbar')

        <main class="admin-content">
            @yield('content')
        </main>
    </div>
</div>

<script>
(function () {
    const shell = document.getElementById('adminShell');

    document.querySelector('[data-collapse]')?.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 900px)').matches) {
            shell.classList.remove('is-open');
            return;
        }
        shell.classList.toggle('is-collapsed');
        try { localStorage.setItem('silakon.sidebar', shell.classList.contains('is-collapsed') ? '1' : '0'); } catch (e) {}
    });

    document.querySelector('[data-burger]')?.addEventListener('click', () => shell.classList.add('is-open'));
    document.querySelector('[data-backdrop]')?.addEventListener('click', () => shell.classList.remove('is-open'));

    try {
        if (localStorage.getItem('silakon.sidebar') === '1') shell.classList.add('is-collapsed');
    } catch (e) {}
})();
</script>
@stack('scripts')
</body>
</html>
