{{--
    SILAKON — Dashboard Konselor
    Simpan sebagai: resources/views/konselor/dashboard.blade.php

    Statistik dashboard dihitung dari jadwal dan sesi pada database.
--}}
@extends('layouts.konselor')

@section('menu', 'dashboard')
@section('judul', 'Dashboard Konselor')
@section('subjudul', $tanggalHariIni)

@section('content')

{{-- ============ KARTU STATISTIK ============ --}}
<div class="grid kolom-4">
    @foreach($statistik as $item)
        <div class="kartu statistik">
            <span class="statistik-ikon nada-{{ $item['nada'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
            </span>
            <b>{{ $item['nilai'] }}</b>
            <span class="label">{{ $item['label'] }}</span>
            <span class="sub">{{ $item['sub'] }}</span>
        </div>
    @endforeach
</div>

{{-- ============ JADWAL HARI INI & PANEL KANAN ============ --}}
<div class="grid kolom-21">

    <div class="kartu jadwal-hari-ini">
        <div class="kartu-kepala kartu-kepala--sejajar">
            <div>
                <h2>Jadwal Hari Ini</h2>
                <p>{{ $tanggalJadwal }}</p>
            </div>
            <a href="{{ route('konselor.jadwal') }}" class="tombol">Lihat Semua</a>
        </div>

        <div class="jadwal-daftar">
            @forelse($jadwalHariIni as $sesi)
                <div class="jadwal-item {{ $sesi['status'] === 'Selesai' ? 'selesai' : '' }}">
                    <div class="jadwal-jam">
                        <b>{{ $sesi['jam'] }}</b>
                        <span>WIB</span>
                    </div>

                    <div class="jadwal-info">
                        <b>{{ $sesi['konseli'] }}</b>
                        <span>{{ $sesi['kategori'] }} &middot; {{ $sesi['ruang'] }}</span>
                    </div>

                    <div class="jadwal-aksi">
                        <span class="label nada-{{ $sesi['nada'] }}">{{ $sesi['status'] }}</span>
                        @if(in_array($sesi['status'], ['Menunggu', 'Terjadwal', 'Berlangsung'], true))
                            @if(!empty($sesi['kode']))<a href="{{ route('konselor.detail-sesi.show', ['kode' => $sesi['kode'], 'jadwal_id' => $sesi['jadwal_id'] ?? null]) }}" class="tombol tombol-isi">Mulai</a>@endif
                        @endif
                    </div>
                </div>
            @empty
                <p class="jadwal-kosong">Belum ada sesi terjadwal hari ini.</p>
            @endforelse
        </div>
    </div>

    <div class="panel-kanan">
        {{-- ============ JADWAL MENDATANG ============ --}}
        <div class="kartu">
            <div class="kartu-kepala">
                <h2>Jadwal Mendatang</h2>
            </div>

            <div class="mendatang-daftar">
                @foreach($jadwalMendatang as $sesi)
                    <div class="mendatang-item">
                        <div class="mendatang-tanggal">
                            <b>{{ $sesi['tanggal'] }}</b>
                            <span>{{ $sesi['bulan'] }}</span>
                        </div>
                        <div class="mendatang-info">
                            <b>{{ $sesi['konseli'] }}</b>
                            <span>{{ $sesi['kategori'] }} &middot; {{ $sesi['jam'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mendatang-kaki">
                    <a href="{{ route('konselor.jadwal') }}" class="tombol tombol-blok">Lihat Semua Jadwal</a>
            </div>
        </div>

        {{-- ============ CATATAN KONSELING ============ --}}
        <div class="kartu-ajakan">
            <div class="kartu-ajakan-ikon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2.5H7a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7.5z"/><path d="M14 2.5v5h5"/><path d="M8.5 13h7M8.5 16.5h5"/></svg>
            </div>
            <h3>Catatan Konseling</h3>
            <p>Dokumentasikan setiap sesi konseling dengan lengkap.</p>
            <a href="{{ route('konselor.catatan-konseling') }}" class="tombol tombol-isi" style="height:36px;">Buka Catatan</a>
        </div>
    </div>
</div>

@endsection