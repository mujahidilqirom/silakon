<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Models\JadwalKonseling;
use App\Helpers\KonseliDisplay;

/**
 * SILAKON — Catatan Konseling (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/CatatanKonselingController.php
 *
 * Beda dengan versi Koordinator (yang melihat SEMUA konseli & catatan):
 * Konselor hanya melihat konseli yang PERNAH atau SEDANG ia tangani.
 * Begitu satu konseli lolos filter itu, SELURUH riwayat sesinya tetap
 * ditampilkan apa adanya — termasuk sesi yang ditangani konselor lain
 * (mis. Sesi 1 oleh konselor A, Sesi 2 oleh konselor yang login) —
 * karena ini murni riwayat, bukan hasil pengalihan/transfer kasus.
 *
 * Data masih statis (dummy) dan sengaja disamakan dengan sumber data
 * di KoordinatorController@catatan supaya konsisten. Nanti tinggal
 * diganti dengan query ke model Konseli/Sesi yang sama, tinggal
 * ditambah ->whereHas('sesi', fn ($q) => $q->where('konselor_id', Auth::id())).
 */
class CatatanKonselingController extends Controller
{
    public function index(): View
    {
        $jadwal = JadwalKonseling::with(['pengajuan.konseli','konselor','catatanKonseling'])->where('konselor_id', auth()->id())->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])->get();
        $catatan = $jadwal->groupBy(fn ($j) => $j->pengajuan?->konseli_id)->map(function ($items) {
            $p = $items->first()->pengajuan; $display = KonseliDisplay::from($p);
            return [
                'nim' => $display['nim'], 'nama' => $display['nama'], 'peran' => $display['jenis'],
                'prodi' => $display['prodi'], 'fakultas' => $display['fakultas'],
                'subjek_konseling' => $display['subjek'], 'wali_nama' => $display['wali_nama'],
                'mahasiswa_nama' => $display['mahasiswa_nama'], 'telepon' => $display['telepon'],
                'sesi' => $items->map(fn ($s) => [
                    'tgl' => $s->tanggal?->translatedFormat('d M Y'), 'konselor' => $s->konselor?->name ?? '-',
                    'kategori' => collect((array) data_get($s->pengajuan?->data, 'lingkup_hambatan', []))->flatten()->filter()->implode(', ') ?: 'Konseling Individual',
                    'kode' => $s->pengajuan?->kode_pengajuan, 'prioritas' => $s->pengajuan?->prioritas ?: 'Belum dihitung',
                    'catatan' => $s->catatanKonseling?->ringkasan,
                ])->values()->all(),
            ];
        })->values()->all();
        $konselorAktif = auth()->user()->name; $tanggalHariIni = now()->translatedFormat('l, d F Y');
        return view('konselor.catatan-konseling', compact('catatan','konselorAktif','tanggalHariIni'));

        // TODO: ganti dengan nama konselor yang sedang login, mis. Auth::user()->name
        $konselorAktif = 'Dr. Sari Dewi, M.Psi';

        // Sumber data sama dengan halaman Koordinator (KoordinatorController@catatan).
        // Satu baris = satu konseli. Tiap sesi punya konselor, kategori, dan kode tiket sendiri.
        $semuaCatatan = [
            [
                'nim'    => '2201416012',
                'nama'   => 'Rani Kusuma',
                'peran'  => 'Mahasiswa',
                'prodi'  => 'Bimbingan dan Konseling',
                'sesi'   => [
                    [
                        'tgl' => '4 Sep 2026', 'konselor' => 'Dr. Rina Kusumawati, M.Pd',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'tingkat' => 'Tinggi',
                        'catatan' => "Konseli datang dengan keluhan sulit tidur dan cemas berlebihan sejak dua minggu terakhir. Konseli merasa kewalahan dengan tuntutan akademik dan masalah keluarga.\nDilakukan asesmen awal dan penjelasan tentang proses konseling. Konseli bersedia melanjutkan sesi berikutnya.",
                    ],
                    [
                        'tgl' => '11 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'tingkat' => 'Sedang',
                        'catatan' => "Konseling dilanjutkan oleh konselor lain atas penugasan koordinator. Konseli mulai terbuka menceritakan pemicu kecemasannya, terutama saat menghadapi ujian.\nDiajarkan teknik pernapasan dan grounding. Konseli diminta mencatat pola tidurnya selama seminggu.",
                    ],
                    [
                        'tgl' => '18 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'tingkat' => 'Rendah',
                        'catatan' => "Kondisi membaik. Pola tidur mulai teratur dan frekuensi cemas menurun. Konseli sudah bisa menerapkan teknik pernapasan secara mandiri.\nDisepakati sesi tindak lanjut satu bulan lagi bila diperlukan.",
                    ],
                ],
            ],
            [
                'nim'    => '2101416088',
                'nama'   => 'Doni Prasetyo',
                'peran'  => 'Mahasiswa',
                'prodi'  => 'Teknik Informatika',
                'sesi'   => [
                    [
                        'tgl' => '8 Sep 2026', 'konselor' => 'Drs. Agus Santoso, M.Si',
                        'kategori' => 'Pribadi', 'kode' => 'LP3-2026-000076', 'tingkat' => 'Sedang',
                        'catatan' => "Konseli merasa kurang percaya diri dalam pergaulan dan sulit menyampaikan pendapat. Digali riwayat pengalaman yang membentuk pola tersebut.",
                    ],
                    [
                        'tgl' => '15 Sep 2026', 'konselor' => 'Drs. Agus Santoso, M.Si',
                        'kategori' => 'Pribadi', 'kode' => 'LP3-2026-000076', 'tingkat' => 'Rendah',
                        'catatan' => "Latihan asertif melalui bermain peran. Konseli mampu menyampaikan pendapat dengan lebih tenang. Diberi tugas praktik di lingkungan kelas.",
                    ],
                ],
            ],
            [
                'nim'    => '2301416034',
                'nama'   => 'Sari Wulandari',
                'peran'  => 'Mahasiswa',
                'prodi'  => 'Manajemen',
                'sesi'   => [
                    [
                        'tgl' => '3 Sep 2026', 'konselor' => 'Dr. Hendra Pratama, M.Psi',
                        'kategori' => 'Akademik', 'kode' => 'LP3-2026-000061', 'tingkat' => 'Sedang',
                        'catatan' => "Konseli mengalami penurunan IPK dan sulit mengatur waktu belajar. Diidentifikasi kebiasaan menunda dan beban organisasi yang terlalu padat.",
                    ],
                    [
                        'tgl' => '10 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Akademik', 'kode' => 'LP3-2026-000061', 'tingkat' => 'Rendah',
                        'catatan' => "Disusun jadwal belajar mingguan bersama konseli dan dibahas prioritas kegiatan organisasi. Konseli berkomitmen mengurangi satu kepengurusan semester ini.",
                    ],
                ],
            ],
            [
                'nim'    => '2001416121',
                'nama'   => 'Budi Santoso',
                'peran'  => 'Mahasiswa',
                'prodi'  => 'Akuntansi',
                'sesi'   => [
                    [
                        'tgl' => '5 Sep 2026', 'konselor' => 'Dr. Rina Kusumawati, M.Pd',
                        'kategori' => 'Karier', 'kode' => 'LP3-2026-000054', 'tingkat' => 'Rendah',
                        'catatan' => null,
                    ],
                ],
            ],
        ];

        // Konselor hanya melihat konseli yang punya minimal satu sesi dengan dirinya —
        // baik sesi itu sesi pertama, sesi lanjutan, atau sesi yang lebih baru dari
        // konselor lain sebelumnya. Setelah lolos filter, seluruh riwayat sesinya
        // (termasuk milik konselor lain) tetap ikut ditampilkan sebagai konteks.
        $catatan = collect($semuaCatatan)
            ->filter(fn ($c) => collect($c['sesi'])->contains(
                fn ($s) => $s['konselor'] === $konselorAktif
            ))
            ->values()
            ->all();

        $tanggalHariIni = 'Rabu, 23 September 2026';

        return view('konselor.catatan-konseling', compact('catatan', 'konselorAktif', 'tanggalHariIni'));
    }
}
