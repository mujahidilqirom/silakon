{{--
    SILAKON — Template PDF Dashboard Koordinator (untuk barryvdh/laravel-dompdf)
    Simpan sebagai: resources/views/koordinator/dashboard-ekspor.blade.php
    Memakai variabel yang SAMA dengan dashboard.blade.php:
    $statistik, $tren, $darurat, $distribusi, $waktuTunggu, $tidakHadir, $tiketMacet, $konselor,
    $distribusiMode, plus $periode.
    Catatan: dompdf tidak mendukung flex/grid/CSS variable, jadi semua tata letak memakai <table>.
--}}
@php
    $warna  = ['#315D3A', '#6E8F60', '#B7C9A8', '#F59E0B', '#3B82F6', '#EF4444', '#8B5CF6'];
    $satuan = ($distribusiMode ?? 'konseli') === 'pengajuan' ? 'pengajuan' : 'konseli';

    /* ---------- Grafik batang tren (SVG) ---------- */
    $tren   = collect($tren ?? [])->values();
    $n      = max(1, $tren->count());
    $puncak = max(1, (float) $tren->max('konseli'), (float) $tren->max('sesi'));
    $skala  = max(10, (int) ceil($puncak / 10) * 10);
    $W = 690; $H = 220; $kiri = 34; $atas = 16; $bawah = 26;
    $tinggiPlot = $H - $atas - $bawah;
    $lebarGrup  = ($W - $kiri - 8) / $n;
    $lebarBatang = min(24, $lebarGrup / 2 - 4);

    $svgTren = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'">';
    $svgTren .= '<rect x="0" y="0" width="'.$W.'" height="'.$H.'" fill="#ffffff"/>';
    for ($g = 0; $g <= 4; $g++) {
        $y = $atas + $tinggiPlot - ($tinggiPlot * $g / 4);
        $svgTren .= '<line x1="'.$kiri.'" y1="'.$y.'" x2="'.($W - 8).'" y2="'.$y.'" stroke="#E3E9E4" stroke-width="1"/>';
        $svgTren .= '<text x="'.($kiri - 6).'" y="'.($y + 3).'" font-size="9" fill="#657080" text-anchor="end">'.rtrim(rtrim(number_format($skala / 4 * $g, 1, '.', ''), '0'), '.').'</text>';
    }
    foreach ($tren as $i => $t) {
        $x0 = $kiri + $i * $lebarGrup + ($lebarGrup - 2 * $lebarBatang - 3) / 2;
        foreach ([['konseli', '#315D3A'], ['sesi', '#9CB98C']] as $j => [$kunci, $warnaBatang]) {
            $nilai = (float) $t[$kunci];
            $tb    = $tinggiPlot * $nilai / $skala;
            $x     = $x0 + $j * ($lebarBatang + 3);
            $y     = $atas + $tinggiPlot - $tb;
            $svgTren .= '<rect x="'.round($x, 2).'" y="'.round($y, 2).'" width="'.round($lebarBatang, 2).'" height="'.round(max($tb, 0.5), 2).'" fill="'.$warnaBatang.'"/>';
            $svgTren .= '<text x="'.round($x + $lebarBatang / 2, 2).'" y="'.round($y - 3, 2).'" font-size="8" fill="#172033" text-anchor="middle">'.$t[$kunci].'</text>';
        }
        $svgTren .= '<text x="'.round($kiri + $i * $lebarGrup + $lebarGrup / 2, 2).'" y="'.($H - 9).'" font-size="9" fill="#657080" text-anchor="middle">'.e($t['bulan']).'</text>';
    }
    $svgTren .= '</svg>';

    /* ---------- Irisan donat dari polygon (paling aman untuk dompdf) ---------- */
    $irisan = function ($cx, $cy, $r, $a0, $a1) {
        $titik = [round($cx, 2).','.round($cy, 2)];
        $langkah = max(2, (int) ceil(($a1 - $a0) / 3));
        for ($i = 0; $i <= $langkah; $i++) {
            $a = deg2rad($a0 + ($a1 - $a0) * $i / $langkah - 90);
            $titik[] = round($cx + $r * cos($a), 2).','.round($cy + $r * sin($a), 2);
        }
        return implode(' ', $titik);
    };
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Dashboard Koordinator - {{ $periode }}</title>
<style>
    @page { margin: 26px 28px 40px 28px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #172033; margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    .judul-band { background: #315D3A; color: #ffffff; }
    .judul-band td { padding: 14px 16px; }
    .judul-band h1 { margin: 0; font-size: 18px; }
    .judul-band small { font-size: 9px; color: #DCE9DC; }
    h2 { font-size: 12px; color: #315D3A; margin: 18px 0 6px; padding-bottom: 4px; border-bottom: 2px solid #6E8F60; }
    h3 { font-size: 10px; margin: 0 0 6px; color: #172033; }
    .muted { color: #657080; }
    .kartu-wrap { border-collapse: separate; border-spacing: 5px 0; margin: 0 -5px; width: 101%; }
    .kartu { border: 1px solid #DCE5DF; background: #F7FAF7; padding: 9px 8px; text-align: left; width: 20%; vertical-align: top; }
    .kartu .nilai { font-size: 20px; font-weight: bold; color: #315D3A; margin: 3px 0 2px; }
    .kartu .label { font-size: 8.5px; font-weight: bold; }
    .kartu .sub { font-size: 7.5px; color: #657080; }
    .data th { background: #4A7A55; color: #ffffff; padding: 6px 7px; text-align: left; font-size: 8.5px; }
    .data td { border-bottom: 1px solid #E3E9E4; padding: 5px 7px; }
    .data tr.zebra td { background: #F7FAF7; }
    .c { text-align: center; }
    .r { text-align: right; }
    .kotak { border: 1px solid #DCE5DF; padding: 10px 12px; vertical-align: top; }
    .besar { font-size: 22px; font-weight: bold; margin: 2px 0; }
    .kv td { padding: 3px 0; border-bottom: 1px dotted #D9E0DC; }
    .donat-sel { width: 50%; vertical-align: top; padding: 8px 10px; border: 1px solid #E3E9E4; }
    .lg td { padding: 2px 3px; font-size: 8px; vertical-align: middle; }
    .sw { display: inline-block; width: 8px; height: 8px; }
    .bar-trek { background: #E8EEE9; height: 9px; width: 100%; }
    .bar-isi { background: #6E8F60; height: 9px; }
    .bar-isi.bad { background: #EF4444; }
    .legend-tren span { margin-right: 14px; font-size: 8.5px; }
    .tidak-pecah { page-break-inside: avoid; }
</style>
</head>
<body>

<table class="judul-band">
    <tr><td>
        <h1>Dashboard Analisis Koordinator</h1>
        <small>Rekap layanan konseling LP3 UNNES &nbsp;·&nbsp; Periode: {{ $periode }} &nbsp;·&nbsp; Dicetak {{ now()->translatedFormat('d F Y H:i') }}</small>
    </td></tr>
</table>

{{-- ============ RINGKASAN ============ --}}
<h2>Ringkasan</h2>
<table class="kartu-wrap">
    <tr>
        @foreach($statistik as $item)
            <td class="kartu">
                <div class="label">{{ $item['label'] }}</div>
                <div class="nilai">{{ $item['nilai'] }}</div>
                <div class="sub">{{ $item['sub'] ?? '' }}</div>
            </td>
        @endforeach
    </tr>
</table>

{{-- ============ TREN ============ --}}
<div class="tidak-pecah">
    <h2>Tren Bulanan</h2>
    <div class="legend-tren">
        <span><span class="sw" style="background:#315D3A"></span> Konseli</span>
        <span><span class="sw" style="background:#9CB98C"></span> Sesi</span>
    </div>
    <img src="data:image/svg+xml;base64,{{ base64_encode($svgTren) }}" width="{{ $W }}" height="{{ $H }}" alt="Grafik tren bulanan">
</div>

{{-- ============ DARURAT + METRIK ============ --}}
<div class="tidak-pecah">
<h2>Pengajuan Darurat dan Indikator Layanan</h2>
<table style="border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px; width: 101%;">
    <tr>
        <td class="kotak" style="width: 25%;">
            <h3>Pengajuan Darurat</h3>
            <table class="kv">
                @foreach($darurat as $d)
                    <tr>
                        <td>{{ $d['label'] }}</td>
                        <td class="r"><b style="color: {{ $d['warna'] ?? '#172033' }}; font-size: 12px;">{{ $d['nilai'] }}</b></td>
                    </tr>
                @endforeach
            </table>
        </td>
        <td class="kotak" style="width: 25%;">
            <h3>Waktu Tunggu Rata-rata</h3>
            <div class="besar" style="color: #4A7A55;">{{ $waktuTunggu['nilai'] }}</div>
            <div class="muted" style="margin-bottom: 4px;">{{ $waktuTunggu['ket'] ?? '' }}</div>
            <table class="kv">
                @foreach($waktuTunggu['rincian'] ?? [] as $x)
                    <tr><td>{{ $x[0] }}</td><td class="r"><b>{{ $x[1] }}</b></td></tr>
                @endforeach
            </table>
        </td>
        <td class="kotak" style="width: 25%;">
            <h3>Tingkat Tidak Hadir</h3>
            <div class="besar" style="color: #F59E0B;">{{ $tidakHadir['nilai'] }}</div>
            <div class="muted" style="margin-bottom: 4px;">{{ $tidakHadir['ket'] ?? '' }}</div>
            <table class="kv">
                @foreach($tidakHadir['rincian'] ?? [] as $x)
                    <tr><td>{{ $x[0] }}</td><td class="r"><b>{{ $x[1] }}</b></td></tr>
                @endforeach
            </table>
        </td>
        <td class="kotak" style="width: 25%;">
            <h3>Pengajuan Tertunda</h3>
            <div class="besar" style="color: #EF4444;">{{ count($tiketMacet) }}</div>
            <div class="muted" style="margin-bottom: 4px;">tertunda lebih dari 5 hari</div>
            <table class="kv">
                @foreach($tiketMacet as $t)
                    <tr><td><b>{{ $t['kode'] }}</b><br><span class="muted">{{ $t['ket'] }}</span></td></tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
</div>

{{-- ============ DISTRIBUSI (DONAT) ============ --}}
<h2>Distribusi per {{ ucfirst($satuan) }}</h2>
@foreach(collect($distribusi)->chunk(2) as $pasang)
    <table class="tidak-pecah" style="margin-bottom: 8px;">
        <tr>
            @foreach($pasang as $judul => $baris)
                @php
                    $baris = collect($baris)->values();
                    $total = $baris->sum(fn ($r) => (float) $r[1]);
                    $svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="130" height="130" viewBox="0 0 130 130">';
                    $svg  .= '<rect x="0" y="0" width="130" height="130" fill="#ffffff"/>';
                    $sudut = 0;
                    foreach ($baris as $i => $r) {
                        $porsi = $total > 0 ? (float) $r[1] / $total * 360 : 0;
                        if ($porsi > 0) {
                            $svg .= '<polygon points="'.$irisan(65, 65, 60, $sudut, $sudut + $porsi).'" fill="'.$warna[$i % 7].'" stroke="#ffffff" stroke-width="1"/>';
                        }
                        $sudut += $porsi;
                    }
                    $svg .= '<circle cx="65" cy="65" r="33" fill="#ffffff"/>';
                    $svg .= '<text x="65" y="66" font-size="15" font-weight="bold" fill="#315D3A" text-anchor="middle">'.rtrim(rtrim(number_format($total, 1, '.', ''), '0'), '.').'</text>';
                    $svg .= '<text x="65" y="78" font-size="7" fill="#657080" text-anchor="middle">total</text>';
                    $svg .= '</svg>';
                @endphp
                <td class="donat-sel">
                    <h3>{{ $judul }}</h3>
                    <table>
                        <tr>
                            <td style="width: 135px; vertical-align: top;">
                                <img src="data:image/svg+xml;base64,{{ base64_encode($svg) }}" width="130" height="130" alt="Donat {{ $judul }}">
                            </td>
                            <td style="vertical-align: top;">
                                <table class="lg">
                                    @foreach($baris as $i => $r)
                                        <tr>
                                            <td style="width: 10px;"><span class="sw" style="background: {{ $warna[$i % 7] }}"></span></td>
                                            <td>{{ $r[0] }}</td>
                                            <td class="r"><b>{{ $r[1] }}</b></td>
                                            <td class="r muted">{{ $r[2] }}%</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            @endforeach
            @if($pasang->count() < 2)
                <td class="donat-sel" style="border: none;"></td>
            @endif
        </tr>
    </table>
@endforeach

{{-- ============ BEBAN KONSELOR ============ --}}
@php $maks = max(1, (int) collect($konselor)->max('aktif')); @endphp
<div class="tidak-pecah">
<h2>Beban per Konselor</h2>
<table class="data">
    <thead>
        <tr>
            <th>Konselor</th>
            <th class="c" style="width: 90px;">Pendampingan Aktif</th>
            <th class="c" style="width: 70px;">Sesi Selesai</th>
            <th style="width: 190px;">Beban Relatif</th>
        </tr>
    </thead>
    <tbody>
        @foreach($konselor as $i => $k)
            <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                <td>{{ $k['nama'] }}</td>
                <td class="c"><b>{{ $k['aktif'] }}</b></td>
                <td class="c">{{ $k['selesai'] }}</td>
                <td>
                    <div class="bar-trek"><div class="bar-isi {{ $k['aktif'] >= $maks ? 'bad' : '' }}" style="width: {{ max(2, round($k['aktif'] / $maks * 100)) }}%"></div></div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $pdf->page_text(28, $pdf->get_height() - 28, "SILAKON · Dashboard Analisis Koordinator", $font, 7, [0.4, 0.45, 0.5]);
        $pdf->page_text($pdf->get_width() - 80, $pdf->get_height() - 28, "Hal. {PAGE_NUM} / {PAGE_COUNT}", $font, 7, [0.4, 0.45, 0.5]);
    }
</script>
</body>
</html>