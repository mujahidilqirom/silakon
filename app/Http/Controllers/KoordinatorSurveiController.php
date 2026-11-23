<?php

namespace App\Http\Controllers;

use App\Models\JadwalKonseling;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Hasil Survei untuk Koordinator: rekap lengkap + daftar jawaban per konseli,
 * dengan filter rentang tanggal dan ekspor (PDF cetak & Excel/CSV).
 * Halaman ini HANYA MELIHAT hasil (generate/kirim link ada di sisi Admin).
 * Data DUMMY — nanti ganti dengan query Eloquent (tiket selesai + relasi jawaban survei).
 */
class KoordinatorSurveiController extends Controller
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

    private const BULAN_ID = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

    public function index(Request $request)
    {
        $filter = $this->ambilFilter($request);
        $data   = $this->siapkanData($filter);

        return view('koordinator.survei', array_merge($filter, $data, [
            'menuAktif'    => 'survei',
            'opsiKonselor' => User::where('role', 'konselor')->where('is_active', true)->orderBy('name')->pluck('name')->all(),
        ]));
    }

    /** Ekspor Excel (.xlsx asli, 4 sheet) — file langsung diunduh, mengikuti filter yang aktif. */
    public function ekspor(Request $request)
    {
        $filter = $this->ambilFilter($request);
        $data   = $this->siapkanData($filter);
        $f      = $filter;

        $sheets = [
            'Ringkasan' => [
                ['Hasil Survei Layanan Konseling'],
                ['Periode', $data['periodeLabel']],
                ['Konselor', $f['konselorFilter'] === 'semua' ? 'Semua Konselor' : $f['konselorFilter']],
                ['Jenis Konseli', $f['jenisFilter'] === 'semua' ? 'Semua Jenis' : $f['jenisFilter']],
                [''],
                ['Metrik', 'Nilai'],
                ['Total Sesi Selesai', $data['ringkasan']['total_selesai']],
                ['Survei Terisi', $data['ringkasan']['total_mengisi']],
                ['Tingkat Respon (%)', $data['ringkasan']['persen_respon']],
                ['Terbantu (%)', $data['ringkasan']['persen_puas']],
                ['Sikap Memfasilitasi (%)', $data['ringkasan']['sikap_baik']],
            ],

            'Distribusi Jawaban' => array_merge(
                [['Pertanyaan', 'Pilihan Jawaban', 'Jumlah', 'Persen (%)']],
                collect($data['distribusi'])->flatMap(fn ($d) => collect($d['opsi'])->map(fn ($o) => [
                    $d['judul'], $o['label'], $o['jumlah'], $o['persen'],
                ]))->all()
            ),

            'Per Konselor' => array_merge(
                [['Konselor', 'Survei Masuk', 'Terbantu (%)', 'Sikap Memfasilitasi (%)']],
                collect($data['perKonselor'])->map(fn ($p) => [$p['nama'], $p['jumlah'], $p['persen_puas'], $p['persen_baik']])->all()
            ),

            'Detail Jawaban' => array_merge(
                [['Nama Konseli', 'Jenis', 'Konselor', 'Sesi', 'Tanggal Diisi', 'Status', 'Perasaan Pasca Konseling', 'Membantu?', 'Manfaat', 'Kekurangan/Kelebihan', 'Saran', 'Sikap Konselor']],
                collect($data['responden'])->map(fn ($r) => [
                    $r['nama'], $r['jenis'], $r['konselor'], 'Ke-' . $r['sesi'], $r['diisi'], $r['puas'] ? 'Terbantu' : 'Belum terbantu',
                    $r['jawabanFlat']['perasaan'], $r['jawabanFlat']['membantu'], $r['jawabanFlat']['manfaat'],
                    $r['jawabanFlat']['kekurangan_kelebihan'], $r['jawabanFlat']['saran'], $r['jawabanFlat']['sikap'],
                ])->all()
            ),
        ];

        $isi  = $this->buatXlsx($sheets);
        $nama = 'hasil-survei-' . now()->format('Ymd-His') . '.xlsx';

        return response($isi, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
            'Content-Length'      => strlen($isi),
        ]);
    }

    /** Ekspor PDF — halaman cetak polos, dibuka tab baru lalu disimpan sebagai PDF lewat dialog cetak browser. */
    public function cetak(Request $request)
    {
        $filter = $this->ambilFilter($request);
        $data   = $this->siapkanData($filter);

        return view('koordinator.survei-cetak', array_merge($filter, $data));
    }

    private function ambilFilter(Request $request): array
    {
        return [
            'dari'           => $request->query('dari', ''),
            'sampai'         => $request->query('sampai', ''),
            'konselorFilter' => $request->query('konselor', 'semua'),
            'jenisFilter'    => $request->query('jenis', 'semua'),
        ];
    }

    private function siapkanData(array $filter): array
    {
        ['dari' => $dari, 'sampai' => $sampai, 'konselorFilter' => $konselor, 'jenisFilter' => $jenis] = $filter;

        $records = JadwalKonseling::with(['pengajuan.konseli', 'konselor', 'survei'])
            ->where('status', 'selesai')
            ->when($dari !== '', fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai !== '', fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->get()
            ->map(function ($jadwal) {
                $p = $jadwal->pengajuan; $k = $p?->konseli; $s = $jadwal->survei;
                $data = $k?->data ?? [];
                $urutan = $p?->jadwal()->orderBy('tanggal')->orderBy('mulai')->orderBy('id')->pluck('id')->values()->all() ?? [];
                $posisi = array_search($jadwal->id, $urutan, true);
                return [
                    'id' => $jadwal->id, 'nama' => $k?->nama ?? '-',
                    'jenis' => ucfirst($k?->jenis_konseli ?? 'Konseli'),
                    'konselor' => $jadwal->konselor?->name ?? '-',
                    'sesi' => $posisi === false ? 1 : $posisi + 1,
                    'sesi_selesai' => optional($jadwal->tanggal)->format('Y-m-d'),
                    'diisi' => $s?->diisi_at?->format('Y-m-d'), 'jawaban' => $s?->jawaban,
                    'prodi' => $data['program_studi'] ?? null,
                ];
            })
            ->when($konselor !== 'semua', fn ($c) => $c->where('konselor', $konselor))
            ->when($jenis !== 'semua', fn ($c) => $c->where('jenis', $jenis))
            ->values();
        $terisi = $records->filter(fn ($k) => is_array($k['jawaban']))->values();
        $totalSelesai = $records->count(); $n = $terisi->count();
        return [
            'periodeLabel' => $this->periodeLabel($dari, $sampai),
            'ringkasan' => [
                'total_selesai' => $totalSelesai, 'total_mengisi' => $n,
                'persen_respon' => $totalSelesai ? round($n / $totalSelesai * 100) : 0,
                'membantu_ya' => $terisi->where('jawaban.membantu', 'ya')->count(),
                'persen_puas' => $n ? round($terisi->where('jawaban.membantu', 'ya')->count() / $n * 100) : 0,
                'sikap_baik' => $n ? round($terisi->where('jawaban.sikap', 'memfasilitasi')->count() / $n * 100) : 0,
            ],
            'distribusi' => $this->distribusi($terisi), 'perKonselor' => $this->perKonselor($terisi),
            'responden' => $terisi->map(fn ($k) => [
                'nama' => $k['nama'], 'jenis' => $k['jenis'], 'konselor' => $k['konselor'], 'sesi' => $k['sesi'],
                'diisi' => $this->formatTanggal($k['diisi']), 'puas' => $k['jawaban']['membantu'] === 'ya',
                'jawabanFlat' => $this->jawabanFlat($k['jawaban']), 'detail' => $this->detail($k['jawaban']),
            ])->values()->all(),
        ];

        // TODO: pindahkan seluruh penyaringan ini ke query Eloquent (whereBetween, where konselor/jenis).
        $semua = collect($this->dummy())
            ->when($dari !== '', fn ($c) => $c->filter(fn ($k) => $k['sesi_selesai'] >= $dari))
            ->when($sampai !== '', fn ($c) => $c->filter(fn ($k) => $k['sesi_selesai'] <= $sampai))
            ->when($konselor !== 'semua', fn ($c) => $c->where('konselor', $konselor))
            ->when($jenis !== 'semua', fn ($c) => $c->where('jenis', $jenis))
            ->values();

        $terisi       = $semua->filter(fn ($k) => $k['jawaban'] !== null)->values();
        $totalSelesai = $semua->count();
        $n            = $terisi->count();

        return [
            'periodeLabel' => $this->periodeLabel($dari, $sampai),

            'ringkasan' => [
                'total_selesai' => $totalSelesai,
                'total_mengisi' => $n,
                'persen_respon' => $totalSelesai ? round($n / $totalSelesai * 100) : 0,
                'membantu_ya'   => $terisi->where('jawaban.membantu', 'ya')->count(),
                'persen_puas'   => $n ? round($terisi->where('jawaban.membantu', 'ya')->count() / $n * 100) : 0,
                'sikap_baik'    => $n ? round($terisi->where('jawaban.sikap', 'memfasilitasi')->count() / $n * 100) : 0,
            ],

            'distribusi'  => $this->distribusi($terisi),
            'perKonselor' => $this->perKonselor($terisi),
            'responden'   => $terisi->map(fn ($k) => [
                'nama'        => $k['nama'],
                'jenis'       => $k['jenis'],
                'konselor'    => $k['konselor'],
                'sesi'        => $k['sesi'],
                'diisi'       => $this->formatTanggal($k['diisi']),
                'puas'        => $k['jawaban']['membantu'] === 'ya',
                'jawabanFlat' => $this->jawabanFlat($k['jawaban']),
                'detail'      => $this->detail($k['jawaban']),
            ])->values()->all(),
        ];
    }

    private function periodeLabel(string $dari, string $sampai): string
    {
        if ($dari === '' && $sampai === '') return 'Semua periode';
        if ($dari !== '' && $sampai !== '') return $this->formatTanggal($dari) . ' – ' . $this->formatTanggal($sampai);
        if ($dari !== '') return 'Sejak ' . $this->formatTanggal($dari);

        return 'Sampai ' . $this->formatTanggal($sampai);
    }

    /** Format tanggal 'Y-m-d' menjadi '22 Sep 2026' tanpa bergantung locale server. */
    private function formatTanggal(?string $ymd): ?string
    {
        if (!$ymd) return null;

        [$y, $m, $d] = explode('-', $ymd);

        return ((int) $d) . ' ' . self::BULAN_ID[(int) $m] . ' ' . $y;
    }

    /** Rekap jumlah & persen tiap pilihan untuk pertanyaan bertipe pilihan. */
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

    /** Rekap tingkat kepuasan & sikap positif per konselor, untuk tabel performa. */
    private function perKonselor($terisi): array
    {
        return $terisi->groupBy('konselor')->map(function ($grup, $nama) {
            $n = $grup->count();

            return [
                'nama'        => $nama,
                'jumlah'      => $n,
                'persen_puas' => $n ? round($grup->where('jawaban.membantu', 'ya')->count() / $n * 100) : 0,
                'persen_baik' => $n ? round($grup->where('jawaban.sikap', 'memfasilitasi')->count() / $n * 100) : 0,
            ];
        })->sortByDesc('persen_puas')->values()->all();
    }

    /** Jawaban mentah -> teks siap tampil (pilihan "lain" otomatis digabung isiannya). Dipakai modal, tabel cetak, & Excel. */
    private function jawabanFlat(array $j): array
    {
        $pilih = fn ($nm) => $j[$nm] === 'lain'
            ? 'Yang lain: ' . $j[$nm . '_lain']
            : self::OPSI[$nm][$j[$nm]];

        return [
            'perasaan'             => $pilih('perasaan'),
            'membantu'             => $pilih('membantu'),
            'manfaat'              => $j['manfaat'],
            'kekurangan_kelebihan' => $j['kekurangan_kelebihan'],
            'saran'                => $j['saran'],
            'sikap'                => $pilih('sikap'),
        ];
    }

    /** Pasangan pertanyaan-jawaban untuk modal detail. */
    private function detail(array $j): array
    {
        $flat = $this->jawabanFlat($j);

        return [
            ['tanya' => self::TANYA['perasaan'],             'jawab' => $flat['perasaan']],
            ['tanya' => self::TANYA['membantu'],             'jawab' => $flat['membantu']],
            ['tanya' => self::TANYA['manfaat'],              'jawab' => $flat['manfaat']],
            ['tanya' => self::TANYA['kekurangan_kelebihan'], 'jawab' => $flat['kekurangan_kelebihan']],
            ['tanya' => self::TANYA['saran'],                'jawab' => $flat['saran']],
            ['tanya' => self::TANYA['sikap'],                'jawab' => $flat['sikap']],
        ];
    }

    /** 'sesi_selesai' = tanggal sesi konseling selesai (dipakai untuk filter rentang tanggal). */
    private function dummy(): array
    {
        return [
            ['id' => 1, 'nama' => 'Aulia Rahmawati', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 2, 'sesi_selesai' => '2026-09-24', 'diisi' => null, 'jawaban' => null],
            ['id' => 2, 'nama' => 'Bagas Prasetyo', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'sesi_selesai' => '2026-09-23', 'diisi' => null, 'jawaban' => null],
            ['id' => 3, 'nama' => 'Citra Lestari', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 3, 'sesi_selesai' => '2026-09-21', 'diisi' => '2026-09-22', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Saya jadi lebih tenang dalam mengambil keputusan karier dan punya langkah yang jelas.',
                'kekurangan_kelebihan' => 'Kelebihannya konselor sabar mendengarkan. Kekurangannya jadwal terbatas.',
                'saran' => 'Tambah slot jadwal di sore hari untuk alumni yang bekerja.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 4, 'nama' => 'Dimas Aryanto', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 1, 'sesi_selesai' => '2026-09-19', 'diisi' => '2026-09-20', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Jadi lebih paham cara mengatur waktu antara kuliah dan organisasi.',
                'kekurangan_kelebihan' => 'Tidak ada kekurangan berarti, sesi berjalan nyaman.',
                'saran' => 'Sudah cukup baik, dipertahankan saja.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 5, 'nama' => 'Eka Putri Anggraini', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'sesi_selesai' => '2026-09-17', 'diisi' => null, 'jawaban' => null],
            ['id' => 6, 'nama' => 'Fajar Nugroho', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 2, 'sesi_selesai' => '2026-09-15', 'diisi' => '2026-09-16', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Bisa cerita tanpa dihakimi, jadi lebih lega.',
                'kekurangan_kelebihan' => 'Ruangannya nyaman. Kurangnya pengingat jadwal sebelum sesi.',
                'saran' => 'Kirim pengingat lewat WhatsApp sehari sebelum sesi.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 7, 'nama' => 'Gita Maharani', 'jenis' => 'Alumni', 'konselor' => 'Drs. Ahmad Fauzi, M.Pd.', 'sesi' => 1, 'sesi_selesai' => '2026-09-12', 'diisi' => '2026-09-13', 'jawaban' => [
                'perasaan' => 'lain', 'perasaan_lain' => 'Masih bingung, tapi sudah punya gambaran langkah berikutnya.', 'membantu' => 'ya',
                'manfaat' => 'Mendapat sudut pandang baru soal pekerjaan.',
                'kekurangan_kelebihan' => 'Satu sesi terasa kurang untuk membahas semuanya.',
                'saran' => 'Durasi sesi bisa ditambah.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
            ['id' => 8, 'nama' => 'Hana Salsabila', 'jenis' => 'Mahasiswa', 'konselor' => 'Tutor Sebaya', 'sesi' => 1, 'sesi_selesai' => '2026-09-10', 'diisi' => '2026-09-11', 'jawaban' => [
                'perasaan' => 'keruh', 'perasaan_lain' => null, 'membantu' => 'tidak',
                'manfaat' => 'Belum terasa manfaatnya.',
                'kekurangan_kelebihan' => 'Merasa kurang didengarkan karena waktu sesi cepat habis.',
                'saran' => 'Beri lebih banyak waktu bagi konseli untuk bercerita.',
                'sikap' => 'lain', 'sikap_lain' => 'Cukup ramah, tetapi terkesan terburu-buru.',
            ]],
            ['id' => 9, 'nama' => 'Indra Kusuma', 'jenis' => 'Mahasiswa', 'konselor' => 'Dr. Sri Wahyuni, M.Pd., Kons.', 'sesi' => 1, 'sesi_selesai' => '2026-09-08', 'diisi' => '2026-09-09', 'jawaban' => [
                'perasaan' => 'harapan', 'perasaan_lain' => null, 'membantu' => 'ya',
                'manfaat' => 'Lebih paham cara mengelola kecemasan sebelum ujian.',
                'kekurangan_kelebihan' => 'Semuanya baik, tidak ada kekurangan berarti.',
                'saran' => 'Dipertahankan saja kualitasnya.',
                'sikap' => 'memfasilitasi', 'sikap_lain' => null,
            ]],
        ];
    }

    // =========================================================================
    // XLSX WRITER NATIVE — tidak butuh package composer tambahan (mis. PhpSpreadsheet).
    // Cukup ekstensi 'zip' PHP yang bawaan. Baris pertama tiap sheet otomatis ditebalkan.
    // =========================================================================

    /** @param array<string, array<int, array<int, mixed>>> $sheets nama sheet => baris-baris (baris pertama = header) */
    private function buatXlsx(array $sheets): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);

        $nama = array_keys($sheets);
        $n    = count($nama);

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes($n));
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook($nama));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels($n));
        $zip->addFromString('xl/styles.xml', $this->xlsxStyles());

        $i = 1;
        foreach ($sheets as $baris) {
            $zip->addFromString("xl/worksheets/sheet{$i}.xml", $this->xlsxSheet($baris));
            $i++;
        }

        $zip->close();
        $isi = file_get_contents($tmp);
        unlink($tmp);

        return $isi;
    }

    private function xlsxContentTypes(int $n): string
    {
        $override = '';
        for ($i = 1; $i <= $n; $i++) {
            $override .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $override . '</Types>';
    }

    private function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbook(array $namaSheet): string
    {
        $xml = '';
        foreach ($namaSheet as $i => $nm) {
            $id  = $i + 1;
            $nm  = htmlspecialchars($nm, ENT_QUOTES, 'UTF-8');
            $xml .= '<sheet name="' . $nm . '" sheetId="' . $id . '" r:id="rId' . $id . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $xml . '</sheets></workbook>';
    }

    private function xlsxWorkbookRels(int $n): string
    {
        $xml = '';
        for ($i = 1; $i <= $n; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $xml .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $xml . '</Relationships>';
    }

    /** Style minimal: index 0 = normal, index 1 = tebal (dipakai baris header/judul). */
    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function xlsxSheet(array $baris): string
    {
        $lebarKolom = [];
        foreach ($baris as $row) {
            foreach ($row as $ci => $val) {
                $panjang = mb_strlen((string) $val);
                if (!isset($lebarKolom[$ci]) || $panjang > $lebarKolom[$ci]) {
                    $lebarKolom[$ci] = $panjang;
                }
            }
        }

        $cols = '<cols>';
        foreach ($lebarKolom as $ci => $panjang) {
            $lebar = min(max($panjang + 2, 10), 60);
            $cols .= '<col min="' . ($ci + 1) . '" max="' . ($ci + 1) . '" width="' . $lebar . '" customWidth="1"/>';
        }
        $cols .= '</cols>';

        $sheetData = '<sheetData>';
        foreach ($baris as $ri => $row) {
            $r = $ri + 1;
            $sheetData .= '<row r="' . $r . '">';
            foreach ($row as $ci => $val) {
                $ref   = $this->xlsxKolomHuruf($ci) . $r;
                $style = $ri === 0 ? ' s="1"' : '';

                if (is_numeric($val) && $val !== '') {
                    $sheetData .= '<c r="' . $ref . '"' . $style . '><v>' . $val . '</v></c>';
                } else {
                    $teks = htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
                    $sheetData .= '<c r="' . $ref . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">' . $teks . '</t></is></c>';
                }
            }
            $sheetData .= '</row>';
        }
        $sheetData .= '</sheetData>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $cols . $sheetData . '</worksheet>';
    }

    /** Indeks kolom 0-based -> huruf kolom Excel (0=A, 25=Z, 26=AA, ...). */
    private function xlsxKolomHuruf(int $index): string
    {
        $index++;
        $huruf = '';
        while ($index > 0) {
            $sisa  = ($index - 1) % 26;
            $huruf = chr(65 + $sisa) . $huruf;
            $index = intdiv($index - 1, 26);
        }

        return $huruf;
    }
}
