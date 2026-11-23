<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Models\JadwalKonseling;
use App\Helpers\KonseliDisplay;

/**
 * SILAKON — Jadwal Saya (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/JadwalController.php
 *
 * Data masih statis (dummy). Nanti tinggal diganti dengan query ke
 * model Jadwal/Sesi (mis. Jadwal::where('konselor_id', ...)),
 * struktur array dipertahankan supaya view tidak perlu diubah.
 */
class JadwalController extends Controller
{
    public function index()
    {
        $semuaRecords = JadwalKonseling::with('pengajuan.konseli')->where('konselor_id', auth()->id())->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])->latest('tanggal')->get();
        $records = $semuaRecords->whereIn('status', ['terjadwal', 'berlangsung'])->values();
        $jadwal = $records->map(function ($item) {
            $display = KonseliDisplay::from($item->pengajuan);
            return [
                'kode' => $item->pengajuan->kode_pengajuan, 'nama' => $display['nama'],
                'nim' => $display['nim'], 'prodi' => $display['prodi'], 'fakultas' => $display['fakultas'],
                'jenis_konseli' => $display['jenis'], 'subjek_konseling' => $display['subjek'],
                'wali_nama' => $display['wali_nama'], 'mahasiswa_nama' => $display['mahasiswa_nama'],
                'telepon' => $display['telepon'],
            'kategori' => collect((array) data_get($item->pengajuan->data, 'lingkup_hambatan', []))->flatten()->filter()->implode(', ') ?: 'Individual', 'hari' => $item->tanggal->translatedFormat('l'), 'tanggal' => $item->tanggal->format('d M Y'), 'tanggal_iso' => $item->tanggal->format('Y-m-d'),
                'jam' => substr($item->mulai, 0, 5), 'lokasi' => $item->lokasi ?: '-', 'metode' => $item->metode ?: '-', 'status' => ucfirst($item->status), 'konselor' => auth()->user()->name,
            ];
        })->values()->all();
        return view('konselor.jadwal', [
            'jadwal' => $jadwal,
            'statistik' => ['hari_ini' => $records->filter(fn ($item) => $item->tanggal->isToday())->count(), 'minggu_ini' => $records->filter(fn ($item) => $item->tanggal->isCurrentWeek())->count(), 'bulan_ini' => $records->filter(fn ($item) => $item->tanggal->isCurrentMonth())->count(), 'selesai' => $semuaRecords->where('status', 'selesai')->count()],
            'bulanTahun' => now()->translatedFormat('F Y'), 'tanggalHariIni' => now()->translatedFormat('l, d F Y'), 'kalenderTahunAwal' => now()->year, 'kalenderBulanAwal' => now()->month,
        ]);

        // Legacy demo data below is retained for reference during migration.
        $jadwal = [
            [
                'kode'         => 'SLK-2025-000101',
                'nama'         => 'Andi Setiawan',
                'nim'          => '2210631170001',
                'prodi'        => 'Teknik Informatika',
                'fakultas'     => 'FT',
                'kategori'     => 'Kesehatan Mental',
                'hari'         => 'Selasa',
                'tanggal'      => '12 Agu 2025',
                'tanggal_iso'  => '2025-08-12',
                'jam'          => '09.00',
                'lokasi'       => 'Ruang A',
                'metode'       => 'Tatap Muka',
                'status'       => 'Menunggu',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
            [
                'kode'         => 'SLK-2025-000098',
                'nama'         => 'Budi Santoso',
                'nim'          => '2210631170002',
                'prodi'        => 'Manajemen',
                'fakultas'     => 'FE',
                'kategori'     => 'Karier',
                'hari'         => 'Selasa',
                'tanggal'      => '12 Agu 2025',
                'tanggal_iso'  => '2025-08-12',
                'jam'          => '13.00',
                'lokasi'       => 'Ruang B',
                'metode'       => 'Tatap Muka',
                'status'       => 'Menunggu',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
            [
                'kode'         => 'SLK-2025-000095',
                'nama'         => 'Siti Nurhaliza',
                'nim'          => '2210631170003',
                'prodi'        => 'Psikologi',
                'fakultas'     => 'FIP',
                'kategori'     => 'Pribadi',
                'hari'         => 'Kamis',
                'tanggal'      => '14 Agu 2025',
                'tanggal_iso'  => '2025-08-14',
                'jam'          => '10.00',
                'lokasi'       => 'Ruang C',
                'metode'       => 'Tatap Muka',
                'status'       => 'Terjadwal',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
            [
                'kode'         => 'SLK-2025-000092',
                'nama'         => 'Rizky Pratama',
                'nim'          => '2210631170004',
                'prodi'        => 'Teknik Sipil',
                'fakultas'     => 'FT',
                'kategori'     => 'Sosial',
                'hari'         => 'Jumat',
                'tanggal'      => '15 Agu 2025',
                'tanggal_iso'  => '2025-08-15',
                'jam'          => '09.00',
                'lokasi'       => 'Ruang A',
                'metode'       => 'Tatap Muka',
                'status'       => 'Terjadwal',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
            [
                'kode'         => 'SLK-2025-000090',
                'nama'         => 'Maya Sari',
                'nim'          => '2210631170005',
                'prodi'        => 'Akuntansi',
                'fakultas'     => 'FE',
                'kategori'     => 'Disabilitas',
                'hari'         => 'Senin',
                'tanggal'      => '18 Agu 2025',
                'tanggal_iso'  => '2025-08-18',
                'jam'          => '11.00',
                'lokasi'       => 'Ruang B',
                'metode'       => 'Tatap Muka',
                'status'       => 'Terjadwal',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
            [
                'kode'         => 'SLK-2025-000087',
                'nama'         => 'Dewi Rahmawati',
                'nim'          => '2210631170006',
                'prodi'        => 'Ilmu Komunikasi',
                'fakultas'     => 'FIKOM',
                'kategori'     => 'Akademik',
                'hari'         => 'Rabu',
                'tanggal'      => '20 Agu 2025',
                'tanggal_iso'  => '2025-08-20',
                'jam'          => '14.00',
                'lokasi'       => 'Ruang A',
                'metode'       => 'Tatap Muka',
                'status'       => 'Terjadwal',
                'konselor'     => 'Dr. Sari Dewi, M.Psi',
            ],
        ];

        // Ringkasan statistik (dummy, nanti dihitung dari query asli)
        $statistik = [
            'hari_ini'   => 2,
            'minggu_ini' => 5,
            'bulan_ini'  => 23,
        ];

        $bulanTahun     = 'Agustus 2025';
        $tanggalHariIni = 'Rabu, 23 September 2026';

        // Bulan/tahun yang pertama kali ditampilkan di tampilan Kalender
        $kalenderTahunAwal = 2025;
        $kalenderBulanAwal = 8; // 1 = Januari ... 12 = Desember

        return view('konselor.jadwal', compact(
            'jadwal',
            'statistik',
            'bulanTahun',
            'tanggalHariIni',
            'kalenderTahunAwal',
            'kalenderBulanAwal'
        ));
    }
}
