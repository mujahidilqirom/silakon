@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil — SILAKON LPPP UNNES')

@section('content')

@php
    $program = $program ?? [
        'id' => 1,
        'title' => 'Manajemen Stres Akademik',
        // Link grup WhatsApp per program (dummy, nanti dari database)
        'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM01',
    ];

    $data = $data ?? [
        'nama'    => 'Firmansyah',
        'nim'     => '2305090018',
        'tanggal' => '8 Agustus 2026',
    ];
@endphp

<section class="section-pad reg-success-pad">
    <div class="container reg-container">
        <div class="reg-success">

            <span class="reg-success-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>

            <span class="page-hero-badge">Pendaftaran Berhasil</span>
            <h1>Pendaftaran Konseling Kelompok Berhasil</h1>
            <p>Pendaftaran Anda telah diterima. Informasi program dan tautan grup WhatsApp tersedia di halaman ini.</p>

            {{-- ===================== RINGKASAN ===================== --}}
            <dl class="reg-detail">
                <div class="reg-detail-row">
                    <dt>Nama Program</dt>
                    <dd>{{ $program['title'] }}</dd>
                </div>
                <div class="reg-detail-row">
                    <dt>Nama Peserta</dt>
                    <dd>{{ $data['nama'] }}</dd>
                </div>
                <div class="reg-detail-row">
                    <dt>NIM</dt>
                    <dd>{{ $data['nim'] }}</dd>
                </div>
                <div class="reg-detail-row">
                    <dt>Tanggal Pendaftaran</dt>
                    <dd>{{ $data['tanggal'] }}</dd>
                </div>
                <div class="reg-detail-row"><dt>Status Pendaftaran</dt><dd><span class="slot-badge slot-open">Terdaftar</span></dd></div>
            </dl>

            {{-- ===================== GRUP WHATSAPP ===================== --}}
            @if (! empty($program['wa_group']))
                <div class="reg-wa">
                    <span class="reg-wa-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.14h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.37c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.41a8.18 8.18 0 0 1 2.41 5.83c0 4.54-3.7 8.22-8.24 8.22Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.13-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07s.89 2.4 1.01 2.56c.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.28Z"/></svg>
                    </span>
                    <div class="reg-wa-text">
                        <h2>Gabung Grup WhatsApp</h2>
                        <p>
                            Informasi teknis, pengingat jadwal, dan materi sesi akan dibagikan
                            melalui grup WhatsApp program ini. Pastikan Anda bergabung sebelum
                            sesi dimulai.
                        </p>
                    </div>
                    <a href="{{ $program['wa_group'] }}" target="_blank" rel="noopener noreferrer"
                       class="btn btn-primary reg-wa-btn">
                        Gabung Sekarang
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
            @endif

            {{-- ===================== AKSI ===================== --}}
            <div class="reg-success-actions">
                <a href="{{ url('/konseling-kelompok') }}" class="reg-btn-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali ke Konseling Kelompok
                </a>
            </div>

        </div>
    </div>
</section>

@include('konseling-kelompok.partials.registration-style')

@endsection
