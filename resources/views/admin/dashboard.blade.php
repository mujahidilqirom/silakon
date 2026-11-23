{{--
    SILAKON — Dashboard Admin
    Simpan sebagai: resources/views/admin/dashboard.blade.php
    Route contoh:  Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');

    Semua statistik dashboard dihitung dari data pengajuan dan jadwal pada database.
--}}
@extends('layouts.admin')

@section('judul', 'Dashboard Admin')
{{-- Subjudul tidak di-override di sini, jadi otomatis memakai tanggal hari ini dari navbar.blade.php --}}

@section('content')


{{-- ============ BANNER PENGAJUAN MENDESAK ============ --}}
@if(($kasusMendesakJumlah ?? 0) > 0)
    <div class="admin-alert-banner">
        <span class="admin-alert-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
        </span>

        <div class="admin-alert-text">
            <strong>{{ $kasusMendesakJumlah }} Pengajuan Mendesak — Perlu Tindakan Segera</strong>
            <p>
                {{ $kasusMendesakTerbaru['nomor'] }} &middot;
                {{ $kasusMendesakTerbaru['nama'] }} &middot;
                {{ $kasusMendesakTerbaru['peran'] }} — {{ $kasusMendesakTerbaru['waktu'] }} lalu
            </p>
        </div>

        <div class="admin-alert-actions">
            <a href="{{ route('admin.kasus-mendesak') }}" class="admin-btn admin-btn-warning">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                </svg>
                Lihat Pengajuan Mendesak
            </a>
            <a href="{{ route('admin.profil') }}" class="admin-btn admin-btn-outline">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>
                </svg>
                Hubungi
            </a>
        </div>
    </div>
@endif

{{-- ============ KARTU STATISTIK ============ --}}
<div class="admin-grid cols-4">
    @foreach($statistik as $item)
        <div class="admin-card admin-stat">
            <span class="admin-stat-icon tone-{{ $item['tone'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
            </span>
            <b>{{ $item['nilai'] }}</b>
            <span class="admin-stat-label">{{ $item['label'] }}</span>
            <span class="admin-stat-sub">{{ $item['sub'] }}</span>
        </div>
    @endforeach
</div>

{{-- ============ TREN & KATEGORI ============ --}}
<div class="admin-grid cols-32 is-top">

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Tren Pengajuan</h2>
                <p>6 bulan terakhir</p>
            </div>
            <span class="admin-badge">
                {{ $trenPerubahan >= 0 ? '+' : '' }}{{ $trenPerubahan }}% vs bulan lalu
            </span>
        </div>

        <div class="admin-chart">
            @foreach($tren as $bar)
                <div class="admin-bar {{ $loop->last ? 'is-current' : '' }}">
                    <span class="admin-bar-value">{{ $bar['nilai'] }}</span>
                    <span class="admin-bar-track">
                        <span class="admin-bar-fill" style="height: {{ round($bar['nilai'] / max($trenMaks ?: 1, 1) * 100) }}%"></span>
                    </span>
                    <span class="admin-bar-label">{{ $bar['bulan'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Kategori</h2>
                <p>Distribusi bulan ini</p>
            </div>
        </div>
        @php
            // Tampilkan maksimal 10 baris: 9 kategori teratas + 1 baris "Lainnya"
            // yang menjumlahkan sisanya. Jika kategori <= 10, tampilkan semuanya.
            $kategoriUrut = collect($kategori)->sortByDesc('pct')->values();

            if ($kategoriUrut->count() > 10) {
                $kategoriTampil = $kategoriUrut->take(9)->push([
                    'nama'  => 'Lainnya',
                    'pct'   => $kategoriUrut->slice(9)->sum('pct'),
                    'warna' => '#94a3b8',
                ]);
            } else {
                $kategoriTampil = $kategoriUrut;
            }
        @endphp
        <div class="admin-dist">
            @forelse($kategoriTampil as $k)
                <div class="admin-dist-row">
                    <span class="admin-dist-dot" style="background: {{ $k['warna'] }}"></span>
                    <span class="admin-dist-track">
                        <span class="admin-dist-fill" style="width: {{ $k['pct'] }}%; background: {{ $k['warna'] }}"></span>
                    </span>
                    <span class="admin-dist-pct">{{ $k['pct'] }}%</span>
                    <span class="admin-dist-name">{{ $k['nama'] }}</span>
                </div>
            @empty
                <p class="admin-cell-muted">Belum ada data kategori pada bulan ini.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ============ TABEL & PANEL KANAN ============ --}}
<div class="admin-grid cols-32">

    <div class="admin-card">
        <div class="admin-card-head is-centered">
            <h2>Pengajuan Terbaru</h2>
            <a href="{{ route('admin.pendaftaran') }}" class="admin-btn">Lihat Semua</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Konseli</th>
                        <th>Kategori</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(collect($pengajuanTerbaru)->take(5) as $row)
                        <tr>
                            <td class="admin-cell-name">
                                <strong>{{ $row['nama'] }}</strong>
                                <span>{{ $row['nomor'] }}</span>
                            </td>
                            <td class="admin-cell-muted">{{ $row['kategori'] }}</td>
                            <td class="admin-cell-muted">{{ $row['waktu'] }}</td>
                            <td><span class="admin-pill tone-{{ $row['tone'] }}">{{ $row['status'] }}</span></td>
                            <td>
                                <a href="{{ route('admin.pendaftaran.show', $row['kode'] ?? '') }}" class="admin-link">
                                    Detail
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-cell-muted">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-side-stack">
        <div class="admin-card">
            <div class="admin-card-head is-tight">
                <h2>Aksi Cepat</h2>
            </div>
            <div class="admin-actions">
                @foreach($aksiCepat as $aksi)
                    @php
                        $aksiUrl = match ($aksi['label']) {
                            'Atur Jadwal', 'Kelola Antrian' => route('admin.penjadwalan'),
                            'Lihat Laporan' => route('admin.semua-tiket'),
                            default => route('admin.pendaftaran', ['status' => 'menunggu_pemeriksaan']),
                        };
                    @endphp
                    <a href="{{ $aksiUrl }}" class="admin-action">
                        <span class="admin-action-icon tone-{{ $aksi['tone'] }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">{!! $aksi['icon'] !!}</svg>
                        </span>
                        <span>{{ $aksi['label'] }}</span>
                        @if($aksi['jumlah'])
                            <b>{{ $aksi['jumlah'] }}</b>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="admin-report">
            <div class="admin-report-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            </div>
            <h3>Laporan Bulanan</h3>
            <p>Laporan {{ $laporanBulanIni ?? \Carbon\Carbon::now()->translatedFormat('F Y') }} siap diunduh</p>
            <a href="{{ route('admin.semua-tiket') }}">
                Lihat Laporan
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </div>
</div>
@endsection