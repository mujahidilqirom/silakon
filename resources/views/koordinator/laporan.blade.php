{{--
    SILAKON — Laporan & Analitik (Koordinator)
    Simpan sebagai: resources/views/koordinator/laporan.blade.php
    Data dikirim dari KoordinatorController@laporan (lihat method di controller,
    semuanya masih dummy — tinggal diganti query Eloquent nanti).
--}}
@extends('layouts.koordinator')

@section('judul', 'Laporan & Analitik')
@section('subjudul', 'Laporan layanan konseling LPPP UNNES')

@section('content')

<style>
    /* ===== Halaman Laporan & Analitik ===== */
    .lp-page{ padding-bottom:8px; }

    .lp-head-row{
        display:flex;align-items:flex-start;justify-content:space-between;
        flex-wrap:wrap;gap:14px;margin-bottom:24px;
    }
    .lp-head-row h1{margin:0;font-size:22px;font-weight:700;color:#1f2a24;}
    .lp-head-row p{margin:6px 0 0;color:#6b7a72;font-size:13.5px;}

    .lp-filters{flex:1 1 100%;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;}
    /* Terapkan + Ekspor PDF + Ekspor Excel: selalu satu baris */
    .lp-filter-actions{display:flex;flex-wrap:nowrap;align-items:center;gap:10px;margin-left:auto;}
    .lp-select{
        border:1px solid #d8dfda;border-radius:10px;padding:10px 34px 10px 14px;
        font-size:13.5px;color:#1f2a24;background:#fff;font-family:inherit;
        cursor:pointer;outline:none;appearance:none;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%235c6a62' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat;background-position:right 12px center;
    }
    .lp-select:focus{border-color:#4d7c5f;}

    .lp-btn{
        display:inline-flex;align-items:center;gap:8px;
        padding:10px 18px;border-radius:10px;font-size:13.5px;font-weight:600;
        cursor:pointer;font-family:inherit;white-space:nowrap;text-decoration:none;
        border:1px solid transparent;transition:background .15s ease,border-color .15s ease;
    }
    .lp-btn svg{width:15px;height:15px;}
    .lp-btn-outline{background:#fff;border-color:#d8dfda;color:#3a463f;}
    .lp-btn-outline:hover{background:#f4f7f4;}
    .lp-btn-fill{background:#7fa285;color:#fff;}
    .lp-btn-fill:hover{background:#6d9174;}

    /* Kartu statistik atas */
    .lp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;}
    @media (max-width:900px){ .lp-stats{grid-template-columns:repeat(2,1fr);} }
    @media (max-width:480px){ .lp-stats{grid-template-columns:1fr;} }
    .lp-stat{border-radius:16px;padding:20px 22px;}
    .lp-stat b{display:block;font-size:28px;font-weight:800;color:#1f2a24;line-height:1.2;}
    .lp-stat .lp-stat-label{display:block;font-size:13.5px;font-weight:600;color:#33403a;margin-top:4px;}
    .lp-stat .lp-stat-sub{display:block;font-size:12px;color:#6b7a72;margin-top:2px;}
    .lp-stat.tone-hijau{background:#d9f5e3;}
    .lp-stat.tone-biru{background:#dfeafc;}
    .lp-stat.tone-ungu{background:#e8e2fb;}
    .lp-stat.tone-kuning{background:#fdf1c2;}

    /* Grid dua kolom */
    .lp-grid-2{display:grid;grid-template-columns:1.4fr 1fr;gap:18px;margin-bottom:18px;}
    @media (max-width:960px){ .lp-grid-2{grid-template-columns:1fr;} }

    .lp-card{
        background:#fff;border:1px solid #e1e7e3;border-radius:18px;
        box-shadow:0 2px 8px rgba(31,42,36,.04);padding:22px 22px 20px;
    }
    .lp-card-title{margin:0 0 18px;font-size:15.5px;font-weight:700;color:#1f2a24;}

    /* Chart batang CSS murni */
    .lp-chart{display:flex;align-items:flex-end;gap:14px;height:220px;padding:0 4px;}
    .lp-chart-col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;}
    .lp-chart-bar{
        width:100%;max-width:38px;border-radius:6px 6px 3px 3px;overflow:hidden;
        display:flex;flex-direction:column;justify-content:flex-end;
    }
    .lp-chart-seg-diproses{background:#bcdac6;}
    .lp-chart-seg-selesai{background:#5f8f6c;}
    .lp-chart-label{margin-top:10px;font-size:12px;color:#8a978f;}
    .lp-chart-legend{display:flex;gap:18px;margin-top:14px;font-size:12.5px;color:#5c6a62;}
    .lp-chart-legend span{display:inline-flex;align-items:center;gap:6px;}
    .lp-chart-legend i{width:10px;height:10px;border-radius:3px;display:inline-block;}

    /* Daftar kategori dengan progress bar */
    .lp-catlist{display:flex;flex-direction:column;gap:14px;}
    .lp-cat-row-head{display:flex;justify-content:space-between;font-size:13.5px;color:#33403a;margin-bottom:6px;}
    .lp-cat-row-head b{font-weight:700;color:#1f2a24;}
    .lp-bar-track{background:#eef1ee;border-radius:999px;height:7px;overflow:hidden;}
    .lp-bar-fill{height:100%;border-radius:999px;background:#5f8f6c;}

    /* Tabel performa konselor */
    .lp-table-wrap{overflow-x:auto;}
    .lp-table{width:100%;border-collapse:collapse;font-size:13.5px;color:#4b5a51;}
    .lp-table thead th{
        text-align:left;font-size:11.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
        color:#8a978f;padding:0 10px 12px;white-space:nowrap;
    }
    .lp-table thead th:first-child{padding-left:0;}
    .lp-table tbody td{padding:14px 10px;border-top:1px solid #eef1ee;}
    .lp-table tbody td:first-child{padding-left:0;font-weight:700;color:#1f2a24;}
    .lp-table tbody tr:first-child td{border-top:none;}

    /* Kartu dengan tombol di dasar (tombol menempel ke bawah kartu) */
    .lp-card-col{display:flex;flex-direction:column;}
    .lp-card-foot{margin-top:auto;padding-top:16px;display:flex;justify-content:flex-end;}
    .lp-card-col .lp-table-wrap{margin-bottom:16px;}

    /* Jenis konseli — badge persen + progress */
    .lp-jenislist{display:flex;flex-direction:column;gap:16px;}
    .lp-jenis-row{display:flex;align-items:center;gap:12px;}
    .lp-jenis-pct{
        flex-shrink:0;width:46px;height:46px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;
        font-size:13px;font-weight:700;
    }
    .lp-jenis-body{flex:1;min-width:0;}
    .lp-jenis-label{font-size:13.5px;font-weight:600;color:#1f2a24;margin-bottom:6px;}

    /* Kartu ekspor bawah */
    .lp-export{
        display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;
    }
    .lp-export-left{display:flex;align-items:center;gap:14px;}
    .lp-export-icon{
        width:44px;height:44px;border-radius:12px;background:#eef5f0;color:#3f6a4f;
        display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .lp-export-icon svg{width:20px;height:20px;}
    .lp-export-left h4{margin:0 0 3px;font-size:14.5px;font-weight:700;color:#1f2a24;}
    .lp-export-left p{margin:0;font-size:12.5px;color:#6b7a72;}
    .lp-export-actions{display:flex;flex-wrap:nowrap;gap:10px;}
    .lp-period{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:8px 10px;background:#f7faf7;border:1px solid #e3ebe3;border-radius:12px;}
    .lp-period-label{font-size:12px;font-weight:700;color:#66756b;}
    .lp-check{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#4b5a51;white-space:nowrap;}
    .lp-check input{accent-color:#6e8f60;width:16px;height:16px;}
    .lp-export-konselor{position:relative;display:inline-block}.lp-export-konselor summary{list-style:none;cursor:pointer}.lp-export-konselor summary::-webkit-details-marker{display:none}.lp-export-konselor-form{position:absolute;right:0;bottom:calc(100% + 8px);z-index:10;width:260px;padding:14px;background:#fff;border:1px solid #dce5df;border-radius:10px;box-shadow:0 10px 25px rgba(20,40,25,.14);display:flex;flex-direction:column;gap:8px}.lp-export-konselor-form label{font-size:13px;display:flex;gap:7px;align-items:center}.lp-export-konselor-form button{margin-top:5px}

    /* =====================================================================
       RESPONSIF HP — filter dirapikan, statistik 2x2, kartu & tabel dipadatkan
       ===================================================================== */
    @media (max-width: 640px) {
        .lp-head-row { gap: 12px; margin-bottom: 16px; }
        .lp-head-row h1 { font-size: 20px; }
        .lp-head-row p { margin-top: 3px; font-size: 13px; }

        /* filter: periode 2 baris (awal / akhir), lalu tombol terapkan selebar penuh */
        .lp-filters { width: 100%; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 8px; }
        .lp-hide-m { display: none; }
        .lp-period {
            grid-column: 1 / -1; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 8px; padding: 10px; align-items: center;
        }
        .lp-period > :nth-child(1) { order: 1; }
        .lp-period > :nth-child(7) { order: 2; justify-self: end; }
        .lp-period > :nth-child(2) { order: 3; }
        .lp-period > :nth-child(3) { order: 4; }
        .lp-period > :nth-child(4) { order: 5; grid-column: 1 / -1; text-align: center; font-size: 11px; line-height: 1; }
        .lp-period > :nth-child(5) { order: 6; }
        .lp-period > :nth-child(6) { order: 7; }
        .lp-select { width: 100%; min-width: 0; padding: 9px 28px 9px 12px; font-size: 13px; background-position: right 10px center; text-overflow: ellipsis; }
        .lp-filter-actions { grid-column: 1 / -1; margin-left: 0; }
        .lp-apply { flex: 1; width: 100%; justify-content: center; padding: 9px 12px; }
        .lp-card-foot { padding-top: 0; }
        .lp-card-foot .lp-btn { width: 100%; justify-content: center; }

        /* kartu statistik: 2 x 2, ringkas */
        .lp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
        .lp-stat { padding: 12px 14px; border-radius: 14px; }
        .lp-stat b { font-size: 22px; }
        .lp-stat .lp-stat-label { margin-top: 2px; font-size: 12.5px; }
        .lp-stat .lp-stat-sub { font-size: 11px; }

        /* kartu panel */
        .lp-grid-2 { gap: 12px; margin-bottom: 12px; }
        .lp-card { padding: 14px 14px 14px; border-radius: 16px; }
        .lp-card-title { margin-bottom: 12px; font-size: 15px; }

        .lp-chart { gap: 6px; padding: 0; }
        .lp-chart-label { margin-top: 8px; font-size: 11px; }
        .lp-chart-legend { gap: 14px; margin-top: 10px; font-size: 12px; }

        .lp-catlist { gap: 12px; }
        .lp-cat-row-head { gap: 8px; margin-bottom: 5px; font-size: 13px; }

        /* tabel tetap tabel, hanya dipadatkan */
        .lp-table { font-size: 13px; }
        .lp-table thead th { padding: 0 6px 10px; font-size: 11px; letter-spacing: .03em; }
        .lp-table tbody td { padding: 11px 6px; }
        .lp-table thead th:nth-child(n+2), .lp-table tbody td:nth-child(n+2) { width: 60px; text-align: center; }

        .lp-jenislist { gap: 12px; }
        .lp-jenis-pct { width: 42px; height: 42px; border-radius: 10px; font-size: 12.5px; }

        /* ekspor: tombol selebar kartu */
        .lp-export { gap: 12px; }
        .lp-export-left { gap: 12px; align-items: flex-start; }
        .lp-export-icon { width: 38px; height: 38px; border-radius: 10px; }
        .lp-export-icon svg { width: 18px; height: 18px; }
        .lp-export-left h4 { font-size: 14px; }
        .lp-export-actions { width: 100%; gap: 8px; }
        .lp-export-actions .lp-btn { flex: 1; justify-content: center; padding: 10px 12px; }
    }
</style>

<div class="lp-page">

    @if(session('sukses'))
        <div class="k-alert k-alert-sukses">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ============ HEAD + FILTER ============ --}}
    <div class="lp-head-row">
        <div>
            <h1>Laporan & Analitik</h1>
            <p>Laporan layanan konseling LPPP UNNES</p>
        </div>

        @php
            $periodeParams = ['semua' => $semuaPeriode ? 1 : 0, 'dari_bulan' => $dariBulan, 'dari_tahun' => $dariTahun, 'sampai_bulan' => $sampaiBulan, 'sampai_tahun' => $sampaiTahun];
        @endphp
        <form method="GET" action="{{ route('koordinator.laporan') }}" class="lp-filters">
            <div class="lp-period">
                <span class="lp-period-label">Periode</span>
                <select name="dari_bulan" class="lp-select" aria-label="Bulan awal">
                    @foreach($opsiBulan as $nomor => $nama)<option value="{{ $nomor }}" @selected($dariBulan == $nomor)>{{ $nama }}</option>@endforeach
                </select>
                <select name="dari_tahun" class="lp-select" aria-label="Tahun awal">
                    @foreach($opsiTahun as $t)<option value="{{ $t }}" @selected($dariTahun == $t)>{{ $t }}</option>@endforeach
                </select>
                <span class="lp-period-label">sampai</span>
                <select name="sampai_bulan" class="lp-select" aria-label="Bulan akhir">
                    @foreach($opsiBulan as $nomor => $nama)<option value="{{ $nomor }}" @selected($sampaiBulan == $nomor)>{{ $nama }}</option>@endforeach
                </select>
                <select name="sampai_tahun" class="lp-select" aria-label="Tahun akhir">
                    @foreach($opsiTahun as $t)<option value="{{ $t }}" @selected($sampaiTahun == $t)>{{ $t }}</option>@endforeach
                </select>
                <label class="lp-check"><input type="checkbox" name="semua" value="1" @checked($semuaPeriode)> Semua data</label>
            </div>
            <div class="lp-filter-actions">
                <button type="submit" class="lp-btn lp-btn-outline lp-apply" title="Terapkan periode">Terapkan</button>
                <a href="{{ route('koordinator.laporan.ekspor', array_merge(['jenis' => 'pdf'], $periodeParams)) }}" class="lp-btn lp-btn-outline lp-hide-m" title="Ekspor PDF">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    PDF
                </a>
                <a href="{{ route('koordinator.laporan.ekspor', array_merge(['jenis' => 'excel'], $periodeParams)) }}" class="lp-btn lp-btn-fill lp-hide-m" title="Ekspor Excel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                    Excel
                </a>
            </div>
        </form>
    </div>

    {{-- ============ KARTU STATISTIK ============ --}}
    <div class="lp-stats">
        <div class="lp-stat tone-hijau">
            <b>{{ $ringkasan['total_pengajuan'] }}</b>
            <span class="lp-stat-label">Total Pengajuan</span>
            <span class="lp-stat-sub">Bulan ini</span>
        </div>
        <div class="lp-stat tone-biru">
            <b>{{ $ringkasan['sesi_selesai'] }}</b>
            <span class="lp-stat-label">Sesi Selesai</span>
            <span class="lp-stat-sub">Tingkat {{ $ringkasan['tingkat_selesai'] }}%</span>
        </div>
        <div class="lp-stat tone-ungu">
            <b>{{ $ringkasan['rata_tunggu'] }}</b>
            <span class="lp-stat-label">Rata-rata Tunggu</span>
            <span class="lp-stat-sub">hari kerja</span>
        </div>
        <div class="lp-stat tone-kuning">
            <b>{{ $ringkasan['jumlah_survei'] }}</b>
            <span class="lp-stat-label">Survei Terisi</span>
            <span class="lp-stat-sub">dari sesi yang tersedia</span>
        </div>
    </div>

    {{-- ============ TREN BULANAN + KATEGORI ============ --}}
    <div class="lp-grid-2">
        <div class="lp-card">
            <h3 class="lp-card-title">Tren Pengajuan Bulanan</h3>

            <div class="lp-chart">
                @php $maxTotal = collect($trenBulanan)->max(fn($b) => $b['diproses'] + $b['selesai']) ?: 1; @endphp
                @foreach($trenBulanan as $b)
                    @php
                        $tinggiSelesai  = round(($b['selesai']  / $maxTotal) * 190);
                        $tinggiDiproses = round(($b['diproses'] / $maxTotal) * 190);
                    @endphp
                    <div class="lp-chart-col">
                        <div class="lp-chart-bar" style="height:{{ $tinggiSelesai + $tinggiDiproses }}px;">
                            <div class="lp-chart-seg-diproses" style="height:{{ $tinggiDiproses }}px;"></div>
                            <div class="lp-chart-seg-selesai" style="height:{{ $tinggiSelesai }}px;"></div>
                        </div>
                        <span class="lp-chart-label">{{ $b['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="lp-chart-legend">
                <span><i style="background:#bcdac6"></i> Diproses</span>
                <span><i style="background:#5f8f6c"></i> Selesai</span>
            </div>
        </div>

        <div class="lp-card">
            <h3 class="lp-card-title">Kategori Konseling</h3>
            <div class="lp-catlist">
                @foreach($kategoriKonseling as $k)
                    <div>
                        <div class="lp-cat-row-head">
                            <span>{{ $k['label'] }}</span>
                            <b>{{ $k['jumlah'] }} ({{ $k['persen'] }}%)</b>
                        </div>
                        <div class="lp-bar-track">
                            <div class="lp-bar-fill" style="width:{{ $k['persen'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ PERFORMA KONSELOR + JENIS KONSELI ============ --}}
    <div class="lp-grid-2">
        <div class="lp-card lp-card-col">
            <h3 class="lp-card-title">Performa Konselor</h3>
            <div class="lp-table-wrap">
                <table class="lp-table">
                    <thead>
                        <tr>
                            <th>Konselor</th>
                            <th>Sesi</th>
                            <th>Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performaKonselor as $p)
                            <tr>
                                <td>{{ $p['nama'] }}</td>
                                <td>{{ $p['sesi'] }}</td>
                                <td>{{ $p['selesai'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="lp-card-foot">
                <details class="lp-export-konselor">
                    <summary class="lp-btn lp-btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                    Ekspor Konselor
                    </summary>
                    <form method="GET" action="{{ route('koordinator.laporan.ekspor-konselor') }}" class="lp-export-konselor-form">
                        <input type="hidden" name="dari_bulan" value="{{ $dariBulan }}"><input type="hidden" name="dari_tahun" value="{{ $dariTahun }}"><input type="hidden" name="sampai_bulan" value="{{ $sampaiBulan }}"><input type="hidden" name="sampai_tahun" value="{{ $sampaiTahun }}"><input type="hidden" name="semua" value="{{ $semuaPeriode ? 1 : 0 }}">
                        <strong>Pilih konselor</strong>
                        @forelse($daftarKonselor as $k)
                            <label><input type="checkbox" name="konselor[]" value="{{ $k->id }}"> {{ $k->name }}</label>
                        @empty <span class="muted">Belum ada konselor aktif.</span> @endforelse
                        <button type="submit" class="lp-btn lp-btn-fill">Unduh Excel</button>
                    </form>
                </details>
            </div>
        </div>

        <div class="lp-card">
            <h3 class="lp-card-title">Jenis Konseli</h3>
            <div class="lp-jenislist">
                @foreach($jenisKonseli as $j)
                    <div class="lp-jenis-row">
                        <div class="lp-jenis-pct" style="background:{{ $j['bg'] }};color:{{ $j['warna'] }};">
                            {{ $j['persen'] }}%
                        </div>
                        <div class="lp-jenis-body">
                            <div class="lp-jenis-label">{{ $j['label'] }}</div>
                            <div class="lp-bar-track">
                                <div class="lp-bar-fill" style="width:{{ $j['persen'] }}%;background:{{ $j['warna'] }};"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ EKSPOR LAPORAN LENGKAP ============ --}}
    <div class="lp-card">
        <div class="lp-export">
            <div class="lp-export-left">
                <div class="lp-export-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                </div>
                <div>
                    <h4>Ekspor Laporan Lengkap</h4>
                    <p>Unduh data lengkap untuk periode yang dipilih dalam format PDF atau Excel.</p>
                </div>
            </div>
            <div class="lp-export-actions">
                <a href="{{ route('koordinator.laporan.ekspor', array_merge(['jenis' => 'pdf'], $periodeParams)) }}" class="lp-btn lp-btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    PDF
                </a>
                <a href="{{ route('koordinator.laporan.ekspor', array_merge(['jenis' => 'excel'], $periodeParams)) }}" class="lp-btn lp-btn-fill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                    Excel
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
