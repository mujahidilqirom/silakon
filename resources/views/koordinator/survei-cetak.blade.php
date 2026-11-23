{{--
    SILAKON — Hasil Survei (Cetak/PDF)
    Simpan sebagai: resources/views/koordinator/survei-cetak.blade.php
    Halaman polos tanpa layout admin — dibuka di tab baru, lalu disimpan
    sebagai PDF lewat dialog cetak browser (Ctrl/Cmd+P → Simpan sebagai PDF).
    Auto-cetak dipicu lewat script di bawah.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Survei — Cetak</title>
    <style>
        @page { size: A4 landscape; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px 28px; font-family: ui-sans-serif, system-ui, Arial, sans-serif; color: #1f2a24; font-size: 12.5px; }
        h1 { font-size: 19px; margin: 0 0 4px; }
        .cp-sub { color: #6b7a72; font-size: 12px; margin: 0 0 20px; }

        .cp-stats { display: flex; gap: 14px; margin-bottom: 22px; }
        .cp-stat { flex: 1; border: 1px solid #e1e7e3; border-radius: 10px; padding: 12px 14px; }
        .cp-stat b { display: block; font-size: 20px; }
        .cp-stat span { display: block; font-size: 11.5px; color: #6b7a72; margin-top: 2px; }

        h2 { font-size: 14px; margin: 22px 0 8px; border-bottom: 1px solid #e1e7e3; padding-bottom: 6px; page-break-after: avoid; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { text-align: left; padding: 6px 7px; font-size: 11.5px; border-bottom: 1px solid #eef1ee; vertical-align: top; }
        th { color: #6b7a72; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: .03em; }
        tr { page-break-inside: avoid; }

        .cp-opt-row td:first-child { color: #4b5a51; }
        .cp-opt-row b { color: #1f2a24; }

        .cp-tabel-detail { table-layout: fixed; }
        .cp-tabel-detail td { word-wrap: break-word; }
        .cp-tabel-detail th:nth-child(1) { width: 9%; }
        .cp-tabel-detail th:nth-child(2) { width: 4%; }
        .cp-tabel-detail th:nth-child(3) { width: 9%; }
        .cp-tabel-detail th:nth-child(4) { width: 6%; }
        .cp-tabel-detail th:nth-child(5) { width: 6%; }
        .cp-tabel-detail th:nth-child(6) { width: 9%; }
        .cp-tabel-detail th:nth-child(7) { width: 5%; }
        .cp-tabel-detail th:nth-child(8),
        .cp-tabel-detail th:nth-child(9),
        .cp-tabel-detail th:nth-child(10),
        .cp-tabel-detail th:nth-child(11) { width: 13%; }

        .cp-noprint { margin-top: 24px; }
        @media print { .cp-noprint { display: none; } }
    </style>
</head>
<body>

    <h1>Hasil Survei Layanan Konseling</h1>
    <p class="cp-sub">
        Pusat Layanan Bimbingan Konseling LPPP UNNES &middot; Periode: {{ $periodeLabel }}
        @if($konselorFilter !== 'semua') &middot; Konselor: {{ $konselorFilter }} @endif
        @if($jenisFilter !== 'semua') &middot; Jenis: {{ $jenisFilter }} @endif
        &middot; Dicetak {{ now()->format('d/m/Y H:i') }}
    </p>

    <div class="cp-stats">
        <div class="cp-stat"><b>{{ $ringkasan['total_mengisi'] }}</b><span>Survei Terisi (dari {{ $ringkasan['total_selesai'] }})</span></div>
        <div class="cp-stat"><b>{{ $ringkasan['persen_respon'] }}%</b><span>Tingkat Respon</span></div>
        <div class="cp-stat"><b>{{ $ringkasan['persen_puas'] }}%</b><span>Terbantu</span></div>
        <div class="cp-stat"><b>{{ $ringkasan['sikap_baik'] }}%</b><span>Sikap Memfasilitasi</span></div>
    </div>

    <h2>Distribusi Jawaban</h2>
    @foreach($distribusi as $d)
        <table>
            <thead><tr><th colspan="3">{{ $d['judul'] }}</th></tr></thead>
            <tbody>
                @foreach($d['opsi'] as $o)
                    <tr class="cp-opt-row">
                        <td>{{ $o['label'] }}</td>
                        <td>{{ $o['jumlah'] }} orang</td>
                        <td><b>{{ $o['persen'] }}%</b></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h2>Hasil per Konselor</h2>
    <table>
        <thead><tr><th>Konselor</th><th>Survei Masuk</th><th>Terbantu</th><th>Sikap Memfasilitasi</th></tr></thead>
        <tbody>
            @foreach($perKonselor as $p)
                <tr><td>{{ $p['nama'] }}</td><td>{{ $p['jumlah'] }}</td><td>{{ $p['persen_puas'] }}%</td><td>{{ $p['persen_baik'] }}%</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Ringkasan Jawaban Masuk ({{ count($responden) }})</h2>
    <table>
        <thead><tr><th>Konseli</th><th>Jenis</th><th>Konselor</th><th>Sesi</th><th>Diisi</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($responden as $r)
                <tr>
                    <td>{{ $r['nama'] }}</td><td>{{ $r['jenis'] }}</td><td>{{ $r['konselor'] }}</td>
                    <td>Ke-{{ $r['sesi'] }}</td><td>{{ $r['diisi'] }}</td><td>{{ $r['puas'] ? 'Terbantu' : 'Belum terbantu' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Detail Jawaban per Konseli</h2>
    <table class="cp-tabel-detail">
        <thead>
            <tr>
                <th>Konseli</th><th>Sesi</th><th>Konselor</th><th>Diisi</th><th>Status</th>
                <th>Perasaan Pasca Konseling</th><th>Membantu?</th>
                <th>Manfaat</th><th>Kekurangan/Kelebihan</th><th>Saran</th><th>Sikap Konselor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responden as $r)
                <tr>
                    <td>{{ $r['nama'] }}</td>
                    <td>Ke-{{ $r['sesi'] }}</td>
                    <td>{{ $r['konselor'] }}</td>
                    <td>{{ $r['diisi'] }}</td>
                    <td>{{ $r['puas'] ? 'Terbantu' : 'Belum terbantu' }}</td>
                    <td>{{ $r['jawabanFlat']['perasaan'] }}</td>
                    <td>{{ $r['jawabanFlat']['membantu'] }}</td>
                    <td>{{ $r['jawabanFlat']['manfaat'] }}</td>
                    <td>{{ $r['jawabanFlat']['kekurangan_kelebihan'] }}</td>
                    <td>{{ $r['jawabanFlat']['saran'] }}</td>
                    <td>{{ $r['jawabanFlat']['sikap'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="cp-noprint">
        <button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </p>

    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>