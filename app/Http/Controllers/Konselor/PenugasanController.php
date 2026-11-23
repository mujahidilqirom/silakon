<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Models\PenugasanKonseling;

/**
 * SILAKON — Penugasan Baru (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/PenugasanController.php
 *
 * Data masih statis (dummy). Nanti tinggal diganti dengan query ke
 * model Penugasan/Kasus (mis. Penugasan::where('konselor_id', ...)),
 * struktur array dipertahankan supaya view tidak perlu diubah.
 */
class PenugasanController extends Controller
{
    public function index()
    {
        $penugasan = PenugasanKonseling::with(['pengajuan.konseli', 'pengajuan.jadwal'])
            ->where('konselor_id', auth()->id())->where('status', 'aktif')->latest()->get()
            ->map(fn ($item) => [
                'kode' => $item->pengajuan->kode_pengajuan,
                'nama' => $item->pengajuan->konseli->nama,
                'peran' => ucfirst(str_replace('_', ' ', $item->pengajuan->konseli->jenis_konseli)),
                'nim' => $item->pengajuan->nim ?: $item->pengajuan->email,
                'kategori' => collect((array) data_get($item->pengajuan->data, 'lingkup_hambatan', []))->flatten()->filter()->implode(', ') ?: 'Konseling individual',
                'prioritas' => $item->pengajuan->prioritas ?: 'Belum dihitung',
                'mode' => data_get($item->pengajuan->data, 'metode_layanan', 'Belum ditentukan'),
                'tanggal_konseling' => optional($item->pengajuan->jadwal->first()?->tanggal)->translatedFormat('l, d M Y') ?? 'Belum dijadwalkan',
                'jam_konseling' => $item->pengajuan->jadwal->first()?->mulai ? substr($item->pengajuan->jadwal->first()->mulai, 0, 5) : '-',
                'tempat_konseling' => $item->pengajuan->jadwal->first()?->lokasi ?? '-',
            ])->values()->all();
        return view('konselor.penugasan', ['penugasan' => $penugasan, 'tanggalHariIni' => now()->translatedFormat('l, d F Y')]);

        // Legacy demo data below is retained for reference during migration.
        $penugasan = [
            [
                'kode'               => 'LP3-2026-000101',
                'nama'               => 'Andi Setiawan',
                'peran'              => 'Mahasiswa',
                'nim'                => '1234567890',
                'kategori'           => 'Akademik',
                'tingkat'            => 'Sedang',
                'mode'               => 'Tatap Muka',
                'tanggal_konseling'  => 'Selasa, 22 Sep 2026',
                'jam_konseling'      => '09.00',
                'tempat_konseling'   => 'Ruang A',
            ],
            [
                'kode'               => 'LP3-2026-000098',
                'nama'               => 'Putri Lestari',
                'peran'              => 'Mahasiswa',
                'nim'                => '2345678901',
                'kategori'           => 'Kesehatan Mental',
                'tingkat'            => 'Tinggi',
                'mode'               => 'Online',
                'tanggal_konseling'  => 'Selasa, 22 Sep 2026',
                'jam_konseling'      => '13.00',
                'tempat_konseling'   => 'Google Meet',
            ],
            [
                'kode'               => 'LP3-2026-000095',
                'nama'               => 'Budi Santoso',
                'peran'              => 'Mahasiswa',
                'nim'                => '3456789012',
                'kategori'           => 'Karier',
                'tingkat'            => 'Rendah',
                'mode'               => 'Tatap Muka',
                'tanggal_konseling'  => 'Rabu, 23 Sep 2026',
                'jam_konseling'      => '10.30',
                'tempat_konseling'   => 'Ruang B',
            ],
            [
                'kode'               => 'LP3-2026-000092',
                'nama'               => 'Siti Nurhaliza',
                'peran'              => 'Mahasiswa',
                'nim'                => '4567890123',
                'kategori'           => 'Pribadi',
                'tingkat'            => 'Sedang',
                'mode'               => 'Online',
                'tanggal_konseling'  => 'Kamis, 24 Sep 2026',
                'jam_konseling'      => '09.00',
                'tempat_konseling'   => 'Zoom Meeting',
            ],
            [
                'kode'               => 'LP3-2026-000090',
                'nama'               => 'Rizky Pratama',
                'peran'              => 'Mahasiswa',
                'nim'                => '5678901234',
                'kategori'           => 'Sosial',
                'tingkat'            => 'Tinggi',
                'mode'               => 'Tatap Muka',
                'tanggal_konseling'  => 'Kamis, 24 Sep 2026',
                'jam_konseling'      => '14.00',
                'tempat_konseling'   => 'Ruang A',
            ],
            [
                'kode'               => 'LP3-2026-000087',
                'nama'               => 'Maya Sari',
                'peran'              => 'Mahasiswa',
                'nim'                => '6789012345',
                'kategori'           => 'Disabilitas',
                'tingkat'            => 'Sedang',
                'mode'               => 'Online',
                'tanggal_konseling'  => 'Jumat, 25 Sep 2026',
                'jam_konseling'      => '11.00',
                'tempat_konseling'   => 'Google Meet',
            ],
        ];

        $tanggalHariIni = 'Senin, 21 September 2026';

        return view('konselor.penugasan', compact('penugasan', 'tanggalHariIni'));
    }
}
