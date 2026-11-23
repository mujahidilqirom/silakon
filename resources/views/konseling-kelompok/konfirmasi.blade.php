@extends('layouts.app')

@section('title', 'Konfirmasi Pendaftaran — SILAKON LPPP UNNES')

@section('content')

@php
    $program = $program ?? [
        'id' => 1,
        'title' => 'Manajemen Stres Akademik',
        'subtitle' => 'Strategi Efektif Mengelola Tekanan Akademik',
        'date' => '15 Agustus 2025',
        'time' => '09.00 – 12.00 WIB',
        'place' => 'Ruang Seminar LPPP, Gedung A Lt. 3',
        'counselor' => 'Dr. Sari Dewi, M.Psi',
        'filled' => 14,
        'quota' => 20,
    ];

    $remaining = max($program['quota'] - $program['filled'], 0);

    // Data pendaftar dari step sebelumnya (dummy kalau diakses langsung)
    $data = $data ?? [
        'nama' => 'Firmansyah',
        'nim' => '2305090018',
        'fakultas' => 'FIK',
        'prodi' => 'pendidikan jasmani olahraga dan kesehatan',
        'semester' => 'Semester 8',
        'email' => 'firmansyahardian@students.unnes.ac.id',
        'wa' => '087737444208',
    ];

    $rows = [
        'Nama Lengkap'   => $data['nama'],
        'NIM'            => $data['nim'],
        'Fakultas'       => $data['fakultas'],
        'Program Studi'  => $data['prodi'],
        'Semester'       => $data['semester'],
        'Email UNNES'    => $data['email'],
        'Nomor WhatsApp' => $data['wa'],
    ];
@endphp

<section class="section-pad">
    <div class="container reg-container">

        <a href="{{ url('/konseling-kelompok') }}" class="reg-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Konseling Kelompok
        </a>

        <div class="reg-head">
            <span class="page-hero-badge">Formulir Pendaftaran</span>
            <h1>Pendaftaran Konseling Kelompok</h1>
            <p>Lengkapi data diri Anda untuk mendaftar program konseling kelompok.</p>
        </div>

        @include('konseling-kelompok.partials.program-summary', [
            'program'   => $program,
            'remaining' => $remaining,
        ])

        {{-- ===================== KONFIRMASI ===================== --}}
        <div class="reg-card">
            <h2>Konfirmasi Pendaftaran</h2>
            <p class="reg-card-sub">Periksa kembali data Anda sebelum mengirim pendaftaran.</p>

            <p class="reg-section-label">Program Konseling Kelompok</p>
            <dl class="reg-table">
                <div class="reg-row">
                    <dt>Nama Program</dt>
                    <dd>{{ $program['title'] }}</dd>
                </div>
            </dl>

            <p class="reg-section-label">Data Pendaftar</p>
            <dl class="reg-table">
                @foreach ($rows as $label => $value)
                    <div class="reg-row">
                        <dt>{{ $label }}</dt>
                        <dd>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <form method="POST" action="{{ url('/konseling-kelompok/' . $program['id'] . '/kirim') }}">
                @csrf

                <div class="reg-agree">
                    <input type="checkbox" id="setuju" name="setuju" value="1" required>
                    <label for="setuju">
                        Saya memastikan bahwa data yang saya masukkan sudah benar. <span class="req">*</span>
                    </label>
                </div>

                <div class="reg-actions">
                    <a href="{{ url('/konseling-kelompok/' . $program['id'] . '/daftar') }}" class="reg-btn-ghost">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary reg-btn reg-btn-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

@include('konseling-kelompok.partials.registration-style')

@endsection