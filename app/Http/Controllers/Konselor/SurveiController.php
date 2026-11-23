<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalKonseling;

/**
 * Hasil Survei untuk Konselor: HANYA hasil survei dari konseli yang dia tangani sendiri.
 * Data DUMMY — nanti ganti dengan query Eloquent yang difilter where('konselor_id', Auth::id()).
 *
 * PENTING soal keamanan data: penyaringan "konseli milik konselor ini" harus dilakukan
 * di query database (WHERE konselor_id = ...), bukan difilter belakangan di Blade/JS,
 * supaya konselor tidak pernah menerima data konseli konselor lain lewat response.
 */
class SurveiController extends Controller
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

    public function index(Request $request)
    {
        $namaKonselor = Auth::user()->name;
        $semuaSelesai = JadwalKonseling::with(['pengajuan.konseli', 'survei'])->where('konselor_id', Auth::id())->where('status', 'selesai')->get();
        $milikSaya = $semuaSelesai->map(function ($j) use ($namaKonselor) {
            $urutan = $j->pengajuan?->jadwal()->orderBy('tanggal')->orderBy('mulai')->orderBy('id')->pluck('id')->values()->all() ?? [];
            $posisi = array_search($j->id, $urutan, true);
            return ['nama'=>$j->pengajuan?->konseli?->nama ?? '-', 'jenis'=>ucfirst($j->pengajuan?->konseli?->jenis_konseli ?? 'Konseli'), 'konselor'=>$namaKonselor, 'sesi'=>$posisi === false ? 1 : $posisi + 1, 'diisi'=>$j->survei?->diisi_at?->format('d M Y'), 'jawaban'=>$j->survei?->jawaban];
        })->filter(fn ($k) => is_array($k['jawaban']))->values();
        $kata = trim((string) $request->query('q')); $terisi = $milikSaya; $ditampilkan = $kata !== '' ? $terisi->filter(fn ($k) => str_contains(strtolower($k['nama']), strtolower($kata)))->values() : $terisi;
        $n = $terisi->count();
        return view('konselor.survei', ['namaKonselor'=>$namaKonselor,'kata'=>$kata,'ringkasan'=>['total_selesai'=>$semuaSelesai->count(),'total_mengisi'=>$n,'persen_respon'=>$semuaSelesai->count()?round($n/$semuaSelesai->count()*100):0,'persen_puas'=>$n?round($terisi->where('jawaban.membantu','ya')->count()/$n*100):0],'distribusi'=>$this->distribusi($terisi),'responden'=>$ditampilkan->map(fn ($k)=>['nama'=>$k['nama'],'jenis'=>$k['jenis'],'sesi'=>$k['sesi'],'diisi'=>$k['diisi'],'puas'=>$k['jawaban']['membantu']==='ya','detail'=>$this->detail($k['jawaban'])])->values()->all()]);

        // TODO: ganti dengan nama/ID konselor yang sedang login, mis. Auth::user()->nama_lengkap.
        // Sementara dihardcode ke salah satu konselor dummy supaya halaman ini bisa dipratinjau.
        $namaKonselor = Auth::user()->nama_lengkap ?? 'Dr. Sri Wahyuni, M.Pd., Kons.';

        // TODO: ganti dengan query: Konseli::where('konselor_id', Auth::id())->where('status', 'selesai')->with('jawabanSurvei')->get();
        $milikSaya = collect($this->dummy())->where('konselor', $namaKonselor)->values();

        $totalSelesai = $milikSaya->count();
        $terisi       = $milikSaya->filter(fn ($k) => $k['jawaban'] !== null)->values();
        $n            = $terisi->count();

        $kata = trim((string) $request->query('q'));
        $ditampilkan = $kata !== ''
            ? $terisi->filter(fn ($k) => str_contains(strtolower($k['nama']), strtolower($kata)))->values()
            : $terisi;

        return view('konselor.survei', [
            'namaKonselor' => $namaKonselor,
            'kata'         => $kata,

            'ringkasan' => [
                'total_selesai' => $totalSelesai,
                'total_mengisi' => $n,
                'persen_respon' => $totalSelesai ? round($n / $totalSelesai * 100) : 0,
                'persen_puas'   => $n ? round($terisi->where('jawaban.membantu', 'ya')->count() / $n * 100) : 0,
            ],

            'distribusi' => $this->distribusi($terisi),

            'responden' => $ditampilkan->map(fn ($k) => [
                'nama'   => $k['nama'],
                'jenis'  => $k['jenis'],
                'sesi'   => $k['sesi'],
                'diisi'  => $k['diisi'],
                'puas'   => $k['jawaban']['membantu'] === 'ya',
                'detail' => $this->detail($k['jawaban']),
            ])->values()->all(),
        ]);
    }

    /** Rekap jumlah & persen tiap pilihan, hanya dari konseli konselor ini. */
    private function distribusi($terisi): array
    {
        $n   = $terisi->count();
        $out = [];

        foreach (['perasaan', 'membantu', 'sikap'] as $nm) {
            $opsi = [];
            foreach (self::OPSI[$nm] as $val => $label) {
                $jml    = $terisi->where("jawaban.$nm", $val)->count();
                $opsi[] = ['label' => $label, 'jumlah' => $jml, 'persen' => $n ? round($jml / $n * 100) : 0];
            }
            $out[] = ['judul' => self::TANYA[$nm], 'total' => $n, 'opsi' => $opsi];
        }

        return $out;
    }

    /** Pasangan pertanyaan-jawaban untuk modal detail. */
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

    /** Dummy lintas-konselor. Nanti diganti tabel konseli + relasi jawaban survei di database. */
    private function dummy(): array
    {
        return [
            ['id' => 1, 'nama' => 'Aulia Rahmawati', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 2, 'diisi' => null, 'jawaban' => null],
            ['id' => 2, 'nama' => 'Bagas Prasetyo', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'diisi' => null, 'jawaban' => null],
            ['id' => 3, 'nama' => 'Citra Lestari', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 3, 'diisi' => '22 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Saya jadi lebih tenang dalam mengambil keputusan karier dan punya langkah yang jelas.',
                'kekurangan_kelebihan' => 'Kelebihannya konselor sabar mendengarkan. Kekurangannya jadwal terbatas.',
                'saran' => 'Tambah slot jadwal di sore hari untuk alumni yang bekerja.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 4, 'nama' => 'Dimas Aryanto', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 1, 'diisi' => '20 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Jadi lebih paham cara mengatur waktu antara kuliah dan organisasi.',
                'kekurangan_kelebihan' => 'Tidak ada kekurangan berarti, sesi berjalan nyaman.',
                'saran' => 'Sudah cukup baik, dipertahankan saja.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 5, 'nama' => 'Eka Putri Anggraini', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'diisi' => null, 'jawaban' => null],
            ['id' => 6, 'nama' => 'Fajar Nugroho', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 2, 'diisi' => '16 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Bisa cerita tanpa dihakimi, jadi lebih lega.',
                'kekurangan_kelebihan' => 'Ruangannya nyaman. Kurangnya pengingat jadwal sebelum sesi.',
                'saran' => 'Kirim pengingat lewat WhatsApp sehari sebelum sesi.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 7, 'nama' => 'Gita Maharani', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'diisi' => '13 Sep 2026', 'jawaban' => [
                'perasaan' => 'lain', 'perasaan_lain' => 'Masih bingung, tapi sudah punya gambaran langkah berikutnya.', 'membantu' => 'ya',
                'manfaat' => 'Mendapat sudut pandang baru soal pekerjaan.',
                'kekurangan_kelebihan' => 'Satu sesi terasa kurang untuk membahas semuanya.',
                'saran' => 'Durasi sesi bisa ditambah.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 8, 'nama' => 'Hana Salsabila', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'diisi' => '11 Sep 2026', 'jawaban' => [
                'perasaan' => 'keruh', 'perasaan_lain' => null, 'membantu' => 'tidak',
                'manfaat' => 'Belum terasa manfaatnya.',
                'kekurangan_kelebihan' => 'Merasa kurang didengarkan karena waktu sesi cepat habis.',
                'saran' => 'Beri lebih banyak waktu bagi konseli untuk bercerita.',
                'sikap' => 'lain', 'sikap_lain' => 'Cukup ramah, tetapi terkesan terburu-buru.',
            ]],
            ['id' => 9, 'nama' => 'Indra Kusuma', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 1, 'diisi' => '9 Sep 2026', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Lebih paham cara mengelola kecemasan sebelum ujian.',
                'kekurangan_kelebihan' => 'Semuanya baik, tidak ada kekurangan berarti.',
                'saran' => 'Dipertahankan saja kualitasnya.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
        ];
    }
}
