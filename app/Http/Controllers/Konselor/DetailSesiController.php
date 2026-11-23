<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Models\JadwalKonseling;
use App\Helpers\KonseliDisplay;
use Illuminate\Http\Request;

/**
 * SILAKON — Detail Sesi Konseling (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/DetailSesiController.php
 *
 * Halaman ini dituju dari tombol "Mulai" di halaman Jadwal Saya
 * (baik dari tabel List maupun panel detail Kalender), lewat
 * route konselor.detail-sesi.show dengan parameter {kode}.
 *
 * Data masih statis (dummy) dan disesuaikan dengan isi form pengajuan.
 * Nanti tinggal diganti dengan query ke model Penugasan/Kasus berdasarkan $kode,
 * struktur array dipertahankan supaya view tidak perlu diubah.
 */
class DetailSesiController extends Controller
{
    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
            'diterima' => 'Diterima',
            'terjadwal' => 'Terjadwal',
            'berlangsung' => 'Berlangsung',
            'selesai' => 'Selesai',
            'sesi_lanjutan' => 'Menunggu Sesi Lanjutan',
            'ditolak' => 'Ditolak',
            'dibatalkan', 'tidak_hadir' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    public function show(Request $request, $kode)
    {
        $jadwal = JadwalKonseling::with(['pengajuan.konseli', 'catatanKonseling'])
            ->whereHas('pengajuan', fn ($query) => $query->where('kode_pengajuan', $kode))
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->where('konselor_id', auth()->id())
            ->when($request->filled('jadwal_id'), fn ($query) => $query->whereKey((int) $request->query('jadwal_id')))
            ->orderByRaw("CASE WHEN status IN ('terjadwal', 'berlangsung') THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->firstOrFail();
        $pengajuan = $jadwal->pengajuan;
        $data = $pengajuan->data ?: [];
        $konseli = $pengajuan->konseli;
        $type = $konseli->jenis_konseli;
        $display = KonseliDisplay::from($pengajuan);
        $prefix = match ($type) { 'mahasiswa' => 'mahasiswa', 'alumni' => 'alumni', 'tenaga_kependidikan' => 'tk', default => 'wali' };
        $identity = $display['nim'] !== '-' ? $display['nim'] : ($data[$prefix . '_unit_kerja'] ?? $pengajuan->email);
        if ($type === 'wali_mahasiswa') {
            $identity = ($display['subjek'] === 'Mahasiswa' ? 'Mahasiswa' : 'Wali Mahasiswa') . ' · ' . ($display['nim'] !== '-' ? $display['nim'] : 'Tanpa NIM');
        }
        $item = [
            'nama' => $display['nama'], 'jenis_konseli' => $display['jenis'], 'identitas' => $identity,
            'telepon' => $display['telepon'], 'email' => $display['email'],
            'subjek_konseling' => $display['subjek'], 'wali_nama' => $display['wali_nama'],
            'wali_telepon' => $display['wali_telepon'], 'mahasiswa_nama' => $display['mahasiswa_nama'],
            'mahasiswa_nim' => $display['mahasiswa_nim'], 'mahasiswa_telepon' => $display['mahasiswa_telepon'],
            'prodi' => $display['prodi'], 'fakultas' => $display['fakultas'],
            'kontak_darurat' => !empty($data[$prefix . '_darurat_1_nama']) ? ['nama' => $data[$prefix . '_darurat_1_nama'], 'hubungan' => $data[$prefix . '_darurat_1_hubungan'] ?? '-', 'nomor' => $data[$prefix . '_darurat_1_nomor'] ?? '-'] : null,
            'tanggal' => $jadwal->tanggal->translatedFormat('l, d F Y'), 'jam' => substr($jadwal->mulai, 0, 5),
            'metode' => $jadwal->metode ?: ($data['metode_layanan'] ?? '-'), 'lokasi' => $jadwal->lokasi ?: '-', 'konselor' => auth()->user()->name, 'status' => ucfirst($jadwal->status),
            'catatan' => $jadwal->catatanKonseling?->ringkasan ?? '', 'hasil' => $jadwal->catatanKonseling?->hasil ?? '',
        ];
        $masalah = [
            'darurat' => ($data['kondisi_darurat'] ?? 'tidak') === 'ya',
            'prioritas' => $jadwal->pengajuan?->prioritas ?: 'Belum dihitung',
            'who5_skor' => $jadwal->pengajuan?->who5_skor, 'saw_nilai' => $jadwal->pengajuan?->saw_nilai,
            'pilihan_konselor' => $data['pilihan_konselor'] ?? '-',
            'lingkup' => $data['lingkup_hambatan'] ?? [], 'uraian' => $data['deskripsi_masalah'] ?? '-',
        ];
        // Riwayat dikelompokkan berdasarkan profil konseli, tetapi nomor
        // pengajuan tetap dipertahankan. Jadi pengajuan baru dengan NIM/email
        // sama tampil sebagai tiket baru, sedangkan beberapa jadwal pada satu
        // tiket tetap diberi label Sesi 1, Sesi 2, dan seterusnya.
        $riwayat = collect();
        $semuaPengajuan = $konseli->pengajuan()
            ->with(['jadwal.konselor', 'jadwal.catatanKonseling', 'penugasan.konselor'])
            ->oldest('created_at')->get();
        foreach ($semuaPengajuan as $pRiwayat) {
            $dRiwayat = (array) ($pRiwayat->data ?? []);
            $jadwalRiwayat = $pRiwayat->jadwal->sortBy(fn ($j) => $j->tanggal?->timestamp ?? $j->created_at?->timestamp ?? 0)->values();
            if ($jadwalRiwayat->isEmpty()) {
                $penugasan = $pRiwayat->penugasan->sortByDesc('created_at')->first();
                $riwayat->push([
                    'kode' => $pRiwayat->kode_pengajuan,
                    'tanggal' => $pRiwayat->created_at?->translatedFormat('d M Y') ?? '-',
                    'kategori' => data_get($dRiwayat, 'tingkat_kerentanan', 'Konseling Individual'),
                    'status' => $this->statusLabel($pRiwayat->status),
                    'konselor' => $penugasan?->konselor?->name ?? '-',
                    'sesi' => null,
                    'uraian' => data_get($dRiwayat, 'deskripsi_masalah', '-'),
                    'catatan' => null,
                    'hasil' => null,
                ]);
                continue;
            }
            foreach ($jadwalRiwayat as $index => $jRiwayat) {
                $catatan = $jRiwayat->catatanKonseling;
                $riwayat->push([
                    'kode' => $pRiwayat->kode_pengajuan,
                    'tanggal' => $jRiwayat->tanggal?->translatedFormat('d M Y') ?? $pRiwayat->created_at?->translatedFormat('d M Y'),
                    'kategori' => data_get($dRiwayat, 'tingkat_kerentanan', 'Konseling Individual'),
                    'status' => $this->statusLabel($jRiwayat->status),
                    'konselor' => $jRiwayat->konselor?->name ?? '-',
                    'sesi' => $index + 1,
                    'uraian' => data_get($dRiwayat, 'deskripsi_masalah', '-'),
                    'catatan' => $catatan?->ringkasan,
                    'hasil' => $catatan?->hasil,
                ]);
            }
        }
        return view('konselor.detail-sesi', [
            'kode' => $kode, 'item' => $item, 'masalah' => $masalah,
            'tahapan' => ['Menunggu Pemeriksaan', 'Diterima', 'Terjadwal', 'Selesai'],
            'langkahAktif' => match (strtolower((string) $jadwal->status)) {
                'selesai' => 4,
                'terjadwal', 'berlangsung' => 3,
                'diterima', 'ditugaskan', 'menunggu_jadwal' => 2,
                default => 1,
            }, 'riwayat' => $riwayat->values()->all(),
            'opsiHasilKonseling' => ['Tuntas — Tidak Perlu Tindak Lanjut', 'Perlu Sesi Lanjutan', 'Disarankan pindah prodi', 'Disarankan untuk rujuk ke pihak lain', 'Konseli Tidak Hadir'],
            'tanggalHariIni' => now()->translatedFormat('l, d F Y'),
        ]);

        // Legacy demo data below is retained for reference during migration.
        // ============ Data pemohon + jadwal per kode ============
        // identitas: teks satu baris di bawah nama
        //   mahasiswa/alumni -> "NIM · Fakultas · Prodi"
        //   dosen/tendik     -> unit kerja
        //   wali             -> "Wali dari {nama} ({nim})"
        $daftarKonseli = [
            'SLK-2025-000101' => [
                'nama'           => 'Andi Setiawan',
                'jenis_konseli'  => 'Mahasiswa',
                'identitas'      => '2210631170001 · FT · Teknik Informatika (S1)',
                'telepon'        => '081234567801',
                'email'          => 'andi.setiawan@students.unnes.ac.id',
                'kontak_darurat' => ['nama' => 'Slamet Setiawan', 'hubungan' => 'Bapak', 'nomor' => '081200000001'],
                'tanggal'        => 'Selasa, 12 Agustus 2025',
                'jam'            => '09.00',
                'metode'         => 'Tatap Muka',
                'lokasi'         => 'Ruang A',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
            'SLK-2025-000098' => [
                'nama'           => 'Budi Santoso',
                'jenis_konseli'  => 'Mahasiswa',
                'identitas'      => '2210631170002 · FEB · Manajemen (S1)',
                'telepon'        => '081234567802',
                'email'          => 'budi.santoso@students.unnes.ac.id',
                'kontak_darurat' => ['nama' => 'Sri Wahyuni', 'hubungan' => 'Ibu', 'nomor' => '081200000002'],
                'tanggal'        => 'Selasa, 12 Agustus 2025',
                'jam'            => '13.00',
                'metode'         => 'Tatap Muka',
                'lokasi'         => 'Ruang B',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
            'SLK-2025-000095' => [
                'nama'           => 'Siti Nurhaliza',
                'jenis_konseli'  => 'Mahasiswa',
                'identitas'      => '2210631170003 · FIPP · Psikologi (S1)',
                'telepon'        => '081234567803',
                'email'          => 'siti.nurhaliza@students.unnes.ac.id',
                'kontak_darurat' => ['nama' => 'Nurhadi', 'hubungan' => 'Bapak', 'nomor' => '081200000003'],
                'tanggal'        => 'Kamis, 14 Agustus 2025',
                'jam'            => '10.00',
                'metode'         => 'Daring',
                'lokasi'         => 'Video call',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
            'SLK-2025-000092' => [
                'nama'           => 'Rizky Pratama',
                'jenis_konseli'  => 'Alumni',
                'identitas'      => '2010631170004 · FT · Teknik Sipil (S1) · Angkatan 2020',
                'telepon'        => '081234567804',
                'email'          => 'rizky.pratama@gmail.com',
                'kontak_darurat' => ['nama' => 'Dewi Pratama', 'hubungan' => 'Kakak', 'nomor' => '081200000004'],
                'tanggal'        => 'Jumat, 15 Agustus 2025',
                'jam'            => '09.00',
                'metode'         => 'Tatap Muka',
                'lokasi'         => 'Ruang A',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
            'SLK-2025-000090' => [
                'nama'           => 'Maya Sari',
                'jenis_konseli'  => 'Mahasiswa',
                'identitas'      => '2210631170005 · FEB · Akuntansi (S1)',
                'telepon'        => '081234567805',
                'email'          => 'maya.sari@students.unnes.ac.id',
                'kontak_darurat' => ['nama' => 'Hartini', 'hubungan' => 'Ibu', 'nomor' => '081200000005'],
                'tanggal'        => 'Senin, 18 Agustus 2025',
                'jam'            => '11.00',
                'metode'         => 'Tatap Muka',
                'lokasi'         => 'Ruang B',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
            'SLK-2025-000087' => [
                'nama'           => 'Dewi Rahmawati',
                'jenis_konseli'  => 'Wali Mahasiswa',
                'identitas'      => 'Wali dari Fajar Ramadhan (2210631170006)',
                'telepon'        => '081234567806',
                'email'          => 'dewi.rahmawati@gmail.com',
                'kontak_darurat' => null, // wali tidak mengisi kontak darurat
                'tanggal'        => 'Rabu, 20 Agustus 2025',
                'jam'            => '14.00',
                'metode'         => 'Daring',
                'lokasi'         => 'Video call',
                'konselor'       => 'Dr. Sari Dewi, M.Psi',
                'status'         => 'Terjadwal',
            ],
        ];

        // ============ Permasalahan per kode (dari step 2 form pengajuan) ============
        // tingkat: Ringan / Sedang / Berat
        $daftarMasalah = [
            'SLK-2025-000101' => [
                'darurat'          => true,
                'tingkat'          => 'Berat',
                'pilihan_konselor' => 'Profesional',
                'lingkup'          => ['Stres kuliah', 'Kecemasan/panik', 'Putus asa'],
                'uraian'           => "Sejak sekitar dua bulan terakhir saya sering panik tiba-tiba, terutama menjelang ujian. Jantung berdebar, sulit tidur, dan saya jadi malas masuk kelas.\n\nSaya sudah mencoba mengatur jadwal belajar dan olahraga ringan, tetapi belum membantu. Akhir-akhir ini saya merasa tidak sanggup melanjutkan kuliah.",
            ],
            'SLK-2025-000098' => [
                'darurat'          => false,
                'tingkat'          => 'Sedang',
                'pilihan_konselor' => 'Profesional',
                'lingkup'          => ['Karier', 'Motivasi'],
                'uraian'           => "Saya bingung menentukan arah karier setelah lulus. Orang tua ingin saya menjadi PNS, sedangkan saya tertarik berwirausaha. Saya jadi kurang termotivasi mengerjakan skripsi.",
            ],
            'SLK-2025-000095' => [
                'darurat'          => false,
                'tingkat'          => 'Ringan',
                'pilihan_konselor' => 'Tutor sebaya',
                'lingkup'          => ['Konflik interpersonal', 'Kesepian'],
                'uraian'           => "Saya merasa kurang cocok dengan teman satu kos dan akhir-akhir ini sering menyendiri. Ingin belajar cara berkomunikasi yang lebih baik.",
            ],
            'SLK-2025-000092' => [
                'darurat'          => false,
                'tingkat'          => 'Sedang',
                'pilihan_konselor' => 'Profesional',
                'lingkup'          => ['Karier', 'Hambatan di masa transisi', 'Manajemen Keuangan'],
                'uraian'           => "Setelah lulus saya belum mendapat pekerjaan tetap dan merasa tertekan dengan tuntutan keluarga. Saya butuh bantuan menata langkah dan mengelola keuangan sementara.",
            ],
            'SLK-2025-000090' => [
                'darurat'          => false,
                'tingkat'          => 'Sedang',
                'pilihan_konselor' => 'Profesional',
                'lingkup'          => ['Harga diri/kepercayaan diri', 'Pengekspresian diri/perasaan'],
                'uraian'           => "Saya sulit percaya diri saat berbicara di depan kelas dan sering memendam perasaan. Hal ini mulai mengganggu nilai presentasi saya.",
            ],
            'SLK-2025-000087' => [
                'darurat'          => false,
                'tingkat'          => 'Ringan',
                'pilihan_konselor' => 'Profesional',
                'lingkup'          => ['Pengasuhan', 'Tekanan Keluarga'],
                'uraian'           => "Anak saya (mahasiswa semester 5) belakangan menjadi tertutup dan nilainya menurun. Saya ingin berkonsultasi bagaimana cara mendampinginya tanpa menekan.",
            ],
        ];

        // Fallback ke data pertama kalau kode tidak ditemukan (mis. diakses langsung)
        $kunci   = array_key_exists($kode, $daftarKonseli) ? $kode : array_key_first($daftarKonseli);
        $item    = $daftarKonseli[$kunci];
        $masalah = $daftarMasalah[$kunci];

        // ============ Alur pengajuan (tahapan tetap) ============
        $tahapan = ['Menunggu Pemeriksaan', 'Diterima', 'Terjadwal', 'Selesai'];

        // Status "Menunggu" konfirmasi konselor -> masih di tahap Penjadwalan (4)
        // Status "Terjadwal" -> sudah masuk tahap Terjadwal (5), menunggu sesi & catatan
        $langkahAktif = $item['status'] === 'Terjadwal' ? 5 : 4;

        // ============ Riwayat konseling sebelumnya (dummy) ============
        $riwayat = [
            [
                'kode'      => 'SLK-2024-000891',
                'tanggal'   => '15 Jan 2024',
                'kategori'  => 'Akademik',
                'status'    => 'Selesai',
                'konselor'  => 'Dr. Ahmad Fauzi',
            ],
        ];

        // ============ Opsi hasil konseling ============
        $opsiHasilKonseling = [
            'Tuntas — Tidak Perlu Tindak Lanjut',
            'Perlu Sesi Lanjutan',
            'Disarankan pindah prodi',
            'Disarankan untuk rujuk ke pihak lain',
            'Konseli Tidak Hadir',
        ];

        $tanggalHariIni = 'Rabu, 23 September 2026';

        return view('konselor.detail-sesi', compact(
            'kode',
            'item',
            'masalah',
            'tahapan',
            'langkahAktif',
            'riwayat',
            'opsiHasilKonseling',
            'tanggalHariIni'
        ));
    }
}
