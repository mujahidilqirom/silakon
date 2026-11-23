<?php

namespace App\Http\Controllers;

use App\Models\JadwalKonseling;
use App\Models\SurveiKonseling;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Halaman admin survei: tab "Kirim Survei" (generate/kirim link) dan tab "Hasil Survei".
 * Data DUMMY. Token hasil generate disimpan di session supaya setelah reload
 * tombolnya sudah berubah jadi "Kirim Link". Nanti ganti dengan kolom/tabel di database.
 */
class AdminSurveiController extends Controller
{
    private const TANYA = [
        'perasaan'             => 'Perasaan, pemahaman dan tindakan pasca konseling',
        'membantu'             => 'Layanan konseling membantu permasalahan yang dialami',
        'manfaat'              => 'Manfaat yang didapatkan setelah konseling',
        'kekurangan_kelebihan' => 'Kekurangan atau kelebihan setelah konseling',
        'saran'                => 'Harapan/saran untuk perbaikan layanan',
        'sikap'                => 'Sikap konselor dalam melayani',
    ];

    private const OPSI = [
        'perasaan' => ['harapan' => 'Memberi harapan/pencerahan', 'keruh' => 'Semakin memperkeruh kondisi/permasalahan', 'lain' => 'Yang lain'],
        'membantu' => ['ya' => 'Ya', 'tidak' => 'Tidak'],
        'sikap'    => ['memfasilitasi' => 'Memfasilitasi', 'menghakimi' => 'Lebih banyak memberikan penilaian (judgment) dan berlaku buruk', 'lain' => 'Yang lain'],
    ];

    public function index()
    {
        $records = JadwalKonseling::with(['pengajuan.konseli', 'konselor', 'survei'])
            ->where('status', 'selesai')->latest('tanggal')->get();
        $konseli = $records->map(function ($jadwal) {
            $p = $jadwal->pengajuan; $k = $p?->konseli; $s = $jadwal->survei;
            $jawaban = $s?->jawaban;
            $token = $s?->token;
            $urutan = $p?->jadwal()->orderBy('tanggal')->orderBy('mulai')->orderBy('id')->pluck('id')->values()->all() ?? [];
            $posisi = array_search($jadwal->id, $urutan, true);
            return [
                'id' => $jadwal->id, 'nama' => $k?->nama ?? '-', 'email' => $k?->email ?? $p?->email ?? '-',
                'jenis' => ucfirst($k?->jenis_konseli ?? 'Konseli'), 'wa' => $k?->telepon ?? '',
                'konselor' => $jadwal->konselor?->name ?? '-', 'sesi' => $posisi === false ? 1 : $posisi + 1,
                'selesai' => optional($jadwal->tanggal)->format('d M Y'), 'token' => $token,
                'url' => $token ? route('survei.tampil', $token) : null,
                'state' => $s?->status === 'terisi' ? 'terisi' : ($token && $s?->status === 'dikirim' ? 'dibuat' : 'belum'),
                'jawaban' => $jawaban, 'diisi' => $s?->diisi_at?->format('d M Y'),
            ];
        })->values()->all();
        $terisi = array_values(array_filter($konseli, fn ($k) => $k['jawaban']));
        $total = count($konseli);
        return view('admin.survei', [
            'menuAktif' => 'survei', 'konseli' => $konseli, 'totalSelesai' => $total,
            'ringkasan' => ['total' => $total, 'terisi' => count($terisi), 'persen' => $total ? round(count($terisi) / $total * 100) : 0, 'link_dibuat' => count(array_filter($konseli, fn ($k) => $k['state'] !== 'belum'))],
            'distribusi' => $this->distribusi($terisi),
            'responden' => array_map(fn ($k) => ['nama' => $k['nama'], 'jenis' => $k['jenis'], 'konselor' => $k['konselor'], 'diisi' => $k['diisi'], 'detail' => $this->detail($k['jawaban'])], $terisi),
        ]);

        $tokenSesi = session('survei_token', []);

        // TODO: query tiket status 'selesai' + relasi jawaban survei (Eloquent), bukan array dummy.
        $konseli = collect($this->dummy())->map(function ($k) use ($tokenSesi) {
            $token = $tokenSesi[$k['id']] ?? $k['token'];

            $k['url']   = $token ? route('survei.tampil', $token) : null;
            $k['state'] = $k['jawaban'] ? 'terisi' : ($token ? 'dibuat' : 'belum');

            return $k;
        })->all();

        $terisi = array_values(array_filter($konseli, fn ($k) => $k['jawaban']));
        $total  = count($konseli);

        return view('admin.survei', [
            'menuAktif'    => 'survei', // cocok dengan 'key' => 'survei' di sidebar
            'konseli'      => $konseli,
            'totalSelesai' => $total,
            'ringkasan'    => [
                'total'       => $total,
                'terisi'      => count($terisi),
                'persen'      => $total ? round(count($terisi) / $total * 100) : 0,
                'link_dibuat' => count(array_filter($konseli, fn ($k) => $k['state'] !== 'belum')),
            ],
            'distribusi'   => $this->distribusi($terisi),
            'responden'    => array_map(fn ($k) => [
                'nama'     => $k['nama'],
                'jenis'    => $k['jenis'],
                'konselor' => $k['konselor'],
                'diisi'    => $k['diisi'],
                'detail'   => $this->detail($k['jawaban']),
            ], $terisi),
        ]);
    }

    public function generate(Request $request, int $id)
    {
        $jadwal = JadwalKonseling::with('survei')->where('status', 'selesai')->findOrFail($id);
        $survei = $jadwal->survei ?: $jadwal->survei()->create(['token' => Str::random(40), 'status' => 'belum_dikirim']);
        if ($survei->status === 'terisi') {
            return response()->json(['token' => $survei->token, 'url' => route('survei.tampil', $survei->token), 'state' => 'terisi']);
        }
        $survei->update(['status' => 'dikirim', 'dikirim_at' => now()]);
        return response()->json(['token' => $survei->token, 'url' => route('survei.tampil', $survei->token)]);

        abort_unless(collect($this->dummy())->contains('id', $id), 404);

        // Jangan buat token baru kalau sudah pernah digenerate.
        $token = session("survei_token.$id") ?? Str::random(32);
        session()->put("survei_token.$id", $token);

        return response()->json([
            'token' => $token,
            'url'   => route('survei.tampil', $token),
        ]);
    }

    /** Rekap jumlah & persen tiap pilihan untuk pertanyaan bertipe pilihan. */
    private function distribusi(array $terisi): array
    {
        $n   = count($terisi);
        $out = [];

        foreach (['perasaan', 'membantu', 'sikap'] as $nm) {
            $opsi = [];
            foreach (self::OPSI[$nm] as $val => $label) {
                $jml    = count(array_filter($terisi, fn ($k) => $k['jawaban'][$nm] === $val));
                $opsi[] = ['label' => $label, 'jumlah' => $jml, 'persen' => $n ? round($jml / $n * 100) : 0];
            }
            $out[] = ['judul' => self::TANYA[$nm], 'total' => $n, 'opsi' => $opsi];
        }

        return $out;
    }

    /** Pasangan pertanyaan-jawaban untuk modal "Lihat jawaban". */
    private function detail(array $j): array
    {
        $pilih = fn ($nm) => $j[$nm] === 'lain'
            ? 'Yang lain: ' . $j[$nm . '_lain']
            : self::OPSI[$nm][$j[$nm]];

        return [
            ['tanya' => self::TANYA['perasaan'],             'jawab' => $pilih('perasaan')],
            ['tanya' => self::TANYA['membantu'],             'jawab' => $pilih('membantu')],
            ['tanya' => self::TANYA['manfaat'],              'jawab' => $j['manfaat']],
            ['tanya' => self::TANYA['kekurangan_kelebihan'], 'jawab' => $j['kekurangan_kelebihan']],
            ['tanya' => self::TANYA['saran'],                'jawab' => $j['saran']],
            ['tanya' => self::TANYA['sikap'],                'jawab' => $pilih('sikap')],
        ];
    }

    private function dummy(): array
    {
        return [
            ['id' => 1, 'nama' => 'Aulia Rahmawati', 'email' => 'aulia.rahmawati@students.unnes.ac.id', 'jenis' => 'Mahasiswa', 'wa' => '081234567801', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 2, 'selesai' => '24 Sep 2026', 'token' => null, 'jawaban' => null, 'diisi' => null],
            ['id' => 2, 'nama' => 'Bagas Prasetyo', 'email' => 'bagas.prasetyo@students.unnes.ac.id', 'jenis' => 'Mahasiswa', 'wa' => '085712345602', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'selesai' => '23 Sep 2026', 'token' => 'a8Kx2QmZ9vTb4LpR7sWc1YdE6nHf3JuG', 'jawaban' => null, 'diisi' => null],
            ['id' => 3, 'nama' => 'Citra Lestari', 'email' => 'citra.lestari@gmail.com', 'jenis' => 'Alumni', 'wa' => '+62 812-3456-7803', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 3, 'selesai' => '21 Sep 2026', 'token' => 'Zp3Nw8RtY5cKf2VxQ7mBh9LdS4eJu6Ga', 'diisi' => '22 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Saya jadi lebih tenang dalam mengambil keputusan karier dan punya langkah yang jelas.',
                'kekurangan_kelebihan' => 'Kelebihannya konselor sabar mendengarkan. Kekurangannya jadwal terbatas.',
                'saran' => 'Tambah slot jadwal di sore hari untuk alumni yang bekerja.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 4, 'nama' => 'Dimas Aryanto', 'email' => 'dimas.aryanto@students.unnes.ac.id', 'jenis' => 'Mahasiswa', 'wa' => '', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 1, 'selesai' => '19 Sep 2026', 'token' => null, 'jawaban' => null, 'diisi' => null],
            ['id' => 5, 'nama' => 'Eka Putri Anggraini', 'email' => 'eka.putri@gmail.com', 'jenis' => 'Alumni', 'wa' => '087812345605', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'selesai' => '17 Sep 2026', 'token' => null, 'jawaban' => null, 'diisi' => null],
            ['id' => 6, 'nama' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@students.unnes.ac.id', 'jenis' => 'Mahasiswa', 'wa' => '081234567806', 'konselor' => 'Tutor Sebaya', 'sesi' => 2, 'selesai' => '15 Sep 2026', 'token' => 'Qm7Lc2XvB9nTz4RkY6sHw1DfE8pJa3Ug', 'diisi' => '16 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Bisa cerita tanpa dihakimi, jadi lebih lega.',
                'kekurangan_kelebihan' => 'Ruangannya nyaman. Kurangnya pengingat jadwal sebelum sesi.',
                'saran' => 'Kirim pengingat lewat WhatsApp sehari sebelum sesi.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 7, 'nama' => 'Gita Maharani', 'email' => 'gita.maharani@gmail.com', 'jenis' => 'Alumni', 'wa' => '085712345607', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'selesai' => '12 Sep 2026', 'token' => 'Hv5Rn8CwK2yPd7XtL3mZb9QfS4eJa6Ug', 'diisi' => '13 Sep 2026', 'jawaban' => [
                'perasaan' => 'lain', 'perasaan_lain' => 'Masih bingung, tapi sudah punya gambaran langkah berikutnya.', 'membantu' => 'ya',
                'manfaat' => 'Mendapat sudut pandang baru soal pekerjaan.',
                'kekurangan_kelebihan' => 'Satu sesi terasa kurang untuk membahas semuanya.',
                'saran' => 'Durasi sesi bisa ditambah.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 8, 'nama' => 'Hana Salsabila', 'email' => 'hana.salsabila@students.unnes.ac.id', 'jenis' => 'Mahasiswa', 'wa' => '087812345608', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'selesai' => '10 Sep 2026', 'token' => 'Wt4Yb7NcJ2xKd9RvL5mZq8PfS3eHa6Ug', 'diisi' => '11 Sep 2026', 'jawaban' => [
                'perasaan' => 'keruh', 'perasaan_lain' => null, 'membantu' => 'tidak',
                'manfaat' => 'Belum terasa manfaatnya.',
                'kekurangan_kelebihan' => 'Merasa kurang didengarkan karena waktu sesi cepat habis.',
                'saran' => 'Beri lebih banyak waktu bagi konseli untuk bercerita.',
                'sikap' => 'lain', 'sikap_lain' => 'Cukup ramah, tetapi terkesan terburu-buru.',
            ]],
        ];
    }
}
