@extends('layouts.app')

@section('title', 'Pendaftaran Konseling Kelompok — SILAKON LPPP UNNES')

@section('content')

@php
    /*
     * Data program dummy. Kalau nanti dikirim dari controller ($program),
     * data di bawah otomatis tidak dipakai.
     */
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

    // Nilai lama (kalau user menekan tombol "Kembali" dari halaman konfirmasi)
    $old = session('pendaftaran_kk', []);

    $katalog = $katalog ?? [];
@endphp

<section class="section-pad">
    <div class="container reg-container">

        <a href="{{ url('/konseling-kelompok') }}" class="reg-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Konseling Kelompok
        </a>

        {{-- ===================== JUDUL ===================== --}}
        <div class="reg-head">
            <span class="page-hero-badge">Formulir Pendaftaran</span>
            <h1>Pendaftaran Konseling Kelompok</h1>
            <p>Lengkapi data diri Anda untuk mendaftar program konseling kelompok.</p>
        </div>

        {{-- ===================== RINGKASAN PROGRAM ===================== --}}
        @include('konseling-kelompok.partials.program-summary', [
            'program'   => $program,
            'remaining' => $remaining,
        ])

        {{-- ===================== FORM ===================== --}}
        <div class="reg-card">
            <h2>Data Pendaftar</h2>
            <p class="reg-card-sub">Formulir khusus untuk mahasiswa aktif UNNES.</p>

            @if ($errors->any())
                <div class="reg-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/konseling-kelompok/' . $program['id'] . '/daftar') }}">
                @csrf

                <div class="reg-field">
                    <label for="nama">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" id="nama" name="nama" placeholder="Sesuai KTM"
                           value="{{ old('nama', $old['nama'] ?? '') }}" required>
                </div>

                <div class="reg-grid-2">
                    <div class="reg-field">
                        <label for="nim">NIM <span class="req">*</span></label>
                        <input type="text" id="nim" name="nim" placeholder="10 digit NIM"
                               inputmode="numeric" maxlength="10"
                               value="{{ old('nim', $old['nim'] ?? '') }}" required>
                    </div>

                    <div class="reg-field">
                        <label for="semester">Semester <span class="req">*</span></label>
                        <select id="semester" name="semester" required>
                            <option value="">Pilih Semester</option>
                            @for ($i = 1; $i <= 14; $i++)
                                <option value="Semester {{ $i }}"
                                    @selected(old('semester', $old['semester'] ?? '') === 'Semester ' . $i)>
                                    Semester {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="reg-grid-2">
                    <div class="reg-field">
                        <label for="fakultas">Fakultas <span class="req">*</span></label>
                        <select id="fakultas" name="fakultas" required>
                            <option value="">Pilih Fakultas</option>
                            @foreach ($katalog as $f => $levels)
                                <option value="{{ $f }}"
                                    @selected(old('fakultas', $old['fakultas'] ?? '') === $f)>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reg-field">
                        <label for="prodi">Program Studi <span class="req">*</span></label>
                        <select id="prodi" name="prodi" required disabled>
                            <option value="">Pilih fakultas terlebih dahulu</option>
                        </select>
                    </div>
                </div>

                <div class="reg-field">
                    <label for="email">Email <span class="req">*</span></label>
                    <input type="email" id="email" name="email" placeholder="nama@contoh.com" value="{{ old('email', $old['email'] ?? '') }}" required>
                </div>

                <div class="reg-field">
                    <label for="wa">Nomor WhatsApp <span class="req">*</span></label>
                    <input type="tel" id="wa" name="wa" placeholder="08xxxxxxxxxx"
                           inputmode="numeric" value="{{ old('wa', $old['wa'] ?? '') }}" required>
                </div>

                <div class="reg-actions reg-actions-end">
                    <button type="submit" class="btn btn-primary reg-btn">Lanjut ke Konfirmasi &rarr;</button>
                </div>
            </form>
        </div>

    </div>
</section>

@include('konseling-kelompok.partials.registration-style')
<style>.reg-email-prefix{display:flex;align-items:center}.reg-email-prefix input{border-radius:10px 0 0 10px;flex:1}.reg-email-prefix span{height:44px;display:flex;align-items:center;padding:0 12px;background:#f3f7f3;border:1px solid #d9ded4;border-left:0;border-radius:0 10px 10px 0;color:#64748b;font-size:13px;white-space:nowrap}</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const data = @json($katalog); const fac=document.getElementById('fakultas'); const prodi=document.getElementById('prodi');
    function fill(selected){ prodi.innerHTML='<option value="">Pilih program studi</option>'; const levels=data[fac.value]||{}; Object.keys(levels).forEach(j=>{ const g=document.createElement('optgroup'); g.label=j; (levels[j]||[]).forEach(n=>{const o=document.createElement('option');o.value=n;o.textContent=n;if(n===selected)o.selected=true;g.appendChild(o);}); prodi.appendChild(g);}); prodi.disabled=!fac.value; }
    fac.addEventListener('change',()=>fill('')); fill(@json(old('prodi', $old['prodi'] ?? '')));
});
</script>

@endsection
