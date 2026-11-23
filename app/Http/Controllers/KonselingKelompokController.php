<?php

namespace App\Http\Controllers;

use App\Models\PesertaKonselingKelompok;
use App\Models\ProgramKonselingKelompok;
use App\Models\MasterFakultas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KonselingKelompokController extends Controller
{
    /** Katalog lama untuk kompatibilitas; halaman publik membaca tabel program. */
    private function programs(): array
    {
        return [
            1 => [
                'id' => 1, 'tone' => 'blue',
                'tags' => ['Kesehatan Mental', 'Akademik'],
                'title' => 'Manajemen Stres Akademik',
                'subtitle' => 'Strategi Efektif Mengelola Tekanan Akademik',
                'desc' => 'Program konseling kelompok yang membahas teknik-teknik efektif untuk mengelola stres akibat tekanan akademik, deadline tugas, dan ujian.',
                'date' => '15 Agustus 2025', 'time' => '09.00 – 12.00 WIB',
                'place' => 'Ruang Seminar LPPP, Gedung A Lt. 3', 'counselor' => 'Dr. Sari Dewi, M.Psi',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM01',
                'filled' => 14, 'quota' => 20,
            ],
            2 => [
                'id' => 2, 'tone' => 'green',
                'tags' => ['Karier', 'Pengembangan Diri'],
                'title' => 'Perencanaan Karier Pasca Kampus',
                'subtitle' => 'Menyiapkan Langkah Setelah Lulus Kuliah',
                'desc' => 'Sesi interaktif untuk membantu mahasiswa semester akhir dan alumni merencanakan langkah karier, membangun CV profesional, dan mempersiapkan wawancara kerja.',
                'date' => '22 Agustus 2025', 'time' => '13.00 – 16.00 WIB',
                'place' => 'Aula LPPP, Lantai 1', 'counselor' => 'Dr. Rina Kusumawati, M.Pd',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM02',
                'filled' => 27, 'quota' => 30,
            ],
            3 => [
                'id' => 3, 'tone' => 'purple',
                'tags' => ['Sosial', 'Pribadi'],
                'title' => 'Kecemasan Sosial & Kepercayaan Diri',
                'subtitle' => 'Membangun Rasa Percaya Diri di Lingkungan Sosial',
                'desc' => 'Program untuk membantu peserta membangun kepercayaan diri, mengatasi kecemasan sosial, dan mengembangkan keterampilan komunikasi yang efektif.',
                'date' => '5 September 2025', 'time' => '09.00 – 12.00 WIB',
                'place' => 'Ruang Konseling LPPP', 'counselor' => 'Drs. Agus Santoso, M.Si',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM03',
                'filled' => 8, 'quota' => 15,
            ],
            4 => [
                'id' => 4, 'tone' => 'yellow',
                'tags' => ['Kesehatan Mental', 'Mindfulness'],
                'title' => 'Mindfulness & Kesehatan Mental',
                'subtitle' => 'Latihan Relaksasi untuk Keseimbangan Hidup',
                'desc' => 'Sesi praktis mindfulness dan teknik relaksasi untuk meningkatkan kesejahteraan mental dan keseimbangan hidup mahasiswa.',
                'date' => '12 September 2025', 'time' => '15.00 – 17.00 WIB',
                'place' => 'Taman Kampus UNNES', 'counselor' => 'Dr. Sari Dewi, M.Psi',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM04',
                'filled' => 3, 'quota' => 25,
            ],
            5 => [
                'id' => 5, 'tone' => 'gray',
                'tags' => ['Sosial', 'Akademik'],
                'title' => 'Adaptasi Mahasiswa Baru',
                'subtitle' => 'Beradaptasi dengan Kehidupan Kampus',
                'desc' => 'Program khusus untuk mahasiswa baru dalam beradaptasi dengan kehidupan kampus, membangun jejaring sosial, dan menetapkan tujuan akademik.',
                'date' => '19 September 2025', 'time' => '08.00 – 11.00 WIB',
                'place' => 'Aula LPPP, Lantai 1', 'counselor' => 'Dra. Wulandari, M.Pd',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM05',
                'filled' => 22, 'quota' => 40,
            ],
            6 => [
                'id' => 6, 'tone' => 'pink',
                'tags' => ['Disabilitas', 'Inklusif'],
                'title' => 'Konseling Kelompok untuk Disabilitas',
                'subtitle' => 'Ruang Aman dan Inklusif untuk Semua',
                'desc' => 'Sesi konseling kelompok khusus untuk mahasiswa berkebutuhan khusus, membahas tantangan dan strategi sukses di lingkungan akademik.',
                'date' => '26 September 2025', 'time' => '10.00 – 12.00 WIB',
                'place' => 'Ruang Aksesibel LPPP', 'counselor' => 'Dra. Wulandari, M.Pd',
                'wa_group' => 'https://chat.whatsapp.com/DUMMYKK2026PROGRAM06',
                'filled' => 5, 'quota' => 12,
            ],
        ];
    }

    private function findProgram(int $id): array
    {
        $program = ProgramKonselingKelompok::withCount('peserta')->findOrFail($id);
        return $this->mapProgram($program);
    }

    private function mapProgram(ProgramKonselingKelompok $p): array
    {
        $status = $p->status;
        try { if ($p->tanggal && $this->parseTanggal($p->tanggal)?->isPast()) $status = 'Selesai'; } catch (\Throwable $e) { }
        return ['id' => $p->id, 'tone' => $p->tone, 'tags' => $p->tags ?: [], 'title' => $p->judul, 'subtitle' => $p->subjudul,
            'desc' => $p->deskripsi, 'date' => $p->tanggal, 'time' => $p->waktu, 'place' => $p->lokasi, 'counselor' => $p->fasilitator,
            'wa_group' => $p->tautan_grup, 'filled' => $p->peserta_count, 'quota' => $p->kuota, 'status' => $status];
    }

    private function parseTanggal(?string $tanggal): ?Carbon
    {
        if (!$tanggal) return null;
        $id = ['Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April','Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August','September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December','Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','Jun'=>'Jun','Jul'=>'Jul','Agu'=>'Aug','Sep'=>'Sep','Okt'=>'Oct','Nov'=>'Nov','Des'=>'Dec'];
        return Carbon::parse(str_ireplace(array_keys($id), array_values($id), $tanggal));
    }

    /** Halaman daftar program (blade yang sudah ada). */
    public function index()
    {
        $programs = ProgramKonselingKelompok::withCount('peserta')->latest()->get()->map(fn ($p) => $this->mapProgram($p))->filter(fn ($p) => $p['status'] !== 'Selesai')->values()->all();
        return view('konseling-kelompok', compact('programs'));
    }

    /** Step 1 — formulir data pendaftar. */
    public function create(int $id)
    {
        $katalog = MasterFakultas::with(['prodi' => fn ($q) => $q->where('aktif', true)->orderBy('jenjang')->orderBy('nama')])->where('aktif', true)->orderBy('nama')->get()->mapWithKeys(fn ($f) => [$f->nama => $f->prodi->groupBy('jenjang')->map->pluck('nama')->all()])->all();
        return view('konseling-kelompok.daftar', [
            'program' => $this->findProgram($id),
            'katalog' => $katalog,
        ]);
    }

    /** Step 2 — konfirmasi data (belum tersimpan ke database). */
    public function konfirmasi(Request $request, int $id)
    {
        $program = $this->findProgram($id);
        abort_if($program['filled'] >= $program['quota'] || $program['status'] === 'Selesai', 422, 'Program sudah penuh atau ditutup.');

        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'nim'      => ['nullable', 'string', 'max:30'],
            'semester' => ['required', 'string'],
            'fakultas' => ['required', 'string'],
            'prodi'    => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150'],
            'wa'       => ['required', 'string', 'regex:/^08[0-9]{8,12}$/'],
        ], [
            'wa.regex'         => 'Nomor WhatsApp harus diawali 08 dan berupa angka.',
        ]);

        // disimpan sementara di session, bukan database
        $data['program_id'] = $id;
        $request->session()->put('pendaftaran_kk', $data);

        return view('konseling-kelompok.konfirmasi', [
            'program' => $program,
            'data'    => $data,
        ]);
    }

    /** Step 3 — "kirim" pendaftaran (masih dummy). */
    public function store(Request $request, int $id)
    {
        $request->validate([
            'setuju' => ['accepted'],
        ], [
            'setuju.accepted' => 'Centang pernyataan kebenaran data terlebih dahulu.',
        ]);

        $data = $request->session()->get('pendaftaran_kk');

        if (! $data) {
            return redirect()->to('/konseling-kelompok/' . $id . '/daftar');
        }

        $program = ProgramKonselingKelompok::withCount('peserta')->findOrFail($id);
        abort_if($program->peserta_count >= $program->kuota, 422, 'Kuota program sudah penuh.');
        PesertaKonselingKelompok::create(['program_id' => $id, 'nama' => $data['nama'], 'nim' => $data['nim'], 'semester' => $data['semester'], 'fakultas' => $data['fakultas'], 'prodi' => $data['prodi'], 'email' => $data['email'], 'wa' => $data['wa']]);
        $data['tanggal'] = date('j') . ' ' . [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ][(int) date('n')] . ' ' . date('Y');

        $request->session()->forget('pendaftaran_kk');
        $request->session()->put('pendaftaran_kk_hasil', $data);

        return redirect()->to('/konseling-kelompok/' . $id . '/berhasil');
    }

    /** Step 4 — halaman berhasil. */
    public function berhasil(Request $request, int $id)
    {
        $data = $request->session()->get('pendaftaran_kk_hasil');

        if (! $data) {
            return redirect()->to('/konseling-kelompok/' . $id . '/daftar');
        }

        return view('konseling-kelompok.berhasil', [
            'program' => $this->findProgram($id),
            'data'    => $data,
        ]);
    }
}
