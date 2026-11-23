<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Models\JadwalKonseling;
use App\Models\PenugasanKonseling;
use App\Models\CatatanKonseling;
use App\Models\SurveiKonseling;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Support\InteractiveMail;

/**
 * SILAKON — Kasus Saya (Konselor)
 * Simpan sebagai: app/Http/Controllers/Konselor/KasusController.php
 *
 * Data masih statis (dummy). Nanti tinggal diganti dengan query ke
 * model Kasus/Konseli (mis. Kasus::where('konselor_id', ...)->with('riwayatSesi')...),
 * struktur array dipertahankan supaya view tidak perlu diubah.
 */
class KasusController extends Controller
{
    public function index()
    {
        // Pendampingan berasal dari penugasan, bukan hanya dari jadwal.
        // Dengan begitu pengajuan yang sudah ditugaskan tetapi belum dijadwalkan
        // tetap terlihat sebagai pendampingan aktif.
        $penugasan = PenugasanKonseling::with('pengajuan.konseli.pengajuan')
            ->where('konselor_id', auth()->id())
            ->whereNotIn('status', ['dibatalkan'])
            ->get();
        $jadwal = JadwalKonseling::with(['pengajuan.konseli', 'catatanKonseling', 'survei', 'konselor'])
            ->where('konselor_id', auth()->id())
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->orderByDesc('tanggal')->get();

        $groups = [];
        foreach ($penugasan as $assignment) {
            $p = $assignment->pengajuan;
            if (! $p || in_array($p->status, ['ditolak', 'dibatalkan'], true)) continue;
            $groups[$p->id] = ['pengajuan' => $p, 'penugasan_status' => $assignment->status, 'jadwal' => collect()];
        }
        foreach ($jadwal as $item) {
            $p = $item->pengajuan;
            if (! $p || in_array($p->status, ['ditolak', 'dibatalkan'], true)) continue;
            $groups[$p->id] ??= ['pengajuan' => $p, 'penugasan_status' => null, 'jadwal' => collect()];
            $groups[$p->id]['jadwal']->push($item);
        }

        // Riwayat memakai profil konseli sebagai penghubung. Setiap tiket
        // tetap dibedakan dengan nomor pengajuannya; sesi lanjutan pada tiket
        // yang sama diberi nomor Sesi 1, Sesi 2, dan seterusnya.
        $riwayatPerKonseli = [];
        $riwayatDibuat = [];
        foreach ($groups as $group) {
            $pAktif = $group['pengajuan'];
            if (!$pAktif->konseli_id || isset($riwayatDibuat[$pAktif->konseli_id])) continue;
            $riwayatDibuat[$pAktif->konseli_id] = true;
            $semuaPengajuan = $pAktif->konseli?->pengajuan()
                ->with(['jadwal.konselor', 'jadwal.catatanKonseling', 'penugasan.konselor'])
                ->oldest('created_at')->get() ?? collect();
            foreach ($semuaPengajuan as $p) {
                $d = (array) ($p->data ?? []);
                $jadwalPengajuan = $p->jadwal->sortBy(fn ($j) => $j->tanggal?->timestamp ?? $j->created_at?->timestamp ?? 0)->values();
                if ($jadwalPengajuan->isEmpty()) {
                    $penugasan = $p->penugasan->sortByDesc('created_at')->first();
                    $riwayatPerKonseli[$p->konseli_id][] = [
                        'tanggal_raw' => $p->created_at,
                        'tanggal' => $p->created_at?->translatedFormat('d M Y') ?? '-',
                        'konselor' => $penugasan?->konselor?->name ?? '-',
            'kategori' => collect((array) data_get($d, 'lingkup_hambatan', []))->flatten()->filter()->implode(', ') ?: 'Konseling Individual',
                        'pengajuan' => $p->kode_pengajuan,
                        'sesi' => null,
                        'status' => ucfirst(str_replace('_', ' ', $p->status)),
                        'uraian' => data_get($d, 'deskripsi_masalah', '-'),
                        'catatan' => null,
                        'hasil' => null,
                    ];
                    continue;
                }
                foreach ($jadwalPengajuan as $index => $item) {
                    $catatan = $item->catatanKonseling;
                    $riwayatPerKonseli[$p->konseli_id][] = [
                        'tanggal_raw' => $item->tanggal,
                        'tanggal' => $item->tanggal?->translatedFormat('d M Y') ?? '-',
                        'konselor' => $item->konselor?->name ?? '-',
            'kategori' => collect((array) data_get($d, 'lingkup_hambatan', []))->flatten()->filter()->implode(', ') ?: 'Konseling Individual',
                        'pengajuan' => $p->kode_pengajuan,
                        'sesi' => $index + 1,
                        'status' => ucfirst(str_replace('_', ' ', $item->status)),
                        'uraian' => data_get($d, 'deskripsi_masalah', '-'),
                        'catatan' => $catatan?->ringkasan,
                        'hasil' => $catatan?->hasil,
                    ];
                }
            }
        }
        foreach ($riwayatPerKonseli as $konseliId => $riwayat) {
            usort($riwayat, fn ($a, $b) => ($a['tanggal_raw']?->timestamp ?? 0) <=> ($b['tanggal_raw']?->timestamp ?? 0));
            $riwayatPerKonseli[$konseliId] = collect($riwayat)->map(function ($item) {
                unset($item['tanggal_raw']);
                return $item;
            })->values()->all();
        }

        $kasus = collect($groups)->map(function ($group) use ($riwayatPerKonseli) {
            $p = $group['pengajuan'];
            $k = $p->konseli;
            $d = (array) ($p->data ?? []);
            $profilAwal = $k?->pengajuan?->sortBy('created_at')->first();
            $identitas = (array) ($profilAwal?->data ?? $d);
            $namaProfil = data_get($identitas, 'mahasiswa_nama', data_get($identitas, 'alumni_nama', data_get($identitas, 'tk_nama', data_get($identitas, 'wali_nama', $k?->nama ?? '-'))));
            $nimProfil = data_get($identitas, 'mahasiswa_nim', data_get($identitas, 'alumni_nim', data_get($identitas, 'wali_mahasiswa_nim', $k?->nim ?? ($p->nim ?? '-'))));
            if ($p->konseli?->jenis_konseli === 'wali_mahasiswa') {
                $subjek = data_get($d, 'wali_subjek_konseling', 'wali');
                $namaProfil = $subjek === 'mahasiswa' ? data_get($d, 'wali_mahasiswa_nama', '-') : data_get($d, 'wali_nama', '-');
                $nimProfil = $subjek === 'mahasiswa' ? data_get($d, 'wali_mahasiswa_nim', '-') : '-';
            }
            $items = $group['jadwal']->sortBy('tanggal')->values();
            $latest = $items->last();
            $nomorWa = $k?->telepon ?? data_get($d, 'mahasiswa_telepon', data_get($d, 'telepon', ''));
            if ($p->konseli?->jenis_konseli === 'wali_mahasiswa') {
                $nomorWa = data_get($d, 'wali_subjek_konseling') === 'mahasiswa'
                    ? data_get($d, 'wali_mahasiswa_telepon', '')
                    : data_get($d, 'wali_telepon', '');
            }
            $sesi = $items->values()->map(function ($j, $index) use ($p, $nomorWa) {
                $survei = $j->survei;
                return [
                    'id' => $j->id,
                    'nomor' => $index + 1,
                    'tanggal' => $j->tanggal?->translatedFormat('d M Y') ?? '-',
                    'jam' => $j->mulai ? substr($j->mulai, 0, 5) : '-',
                    'status' => ucfirst(str_replace('_', ' ', $j->status)),
                    'catatan' => $j->catatanKonseling?->ringkasan,
                    'hasil' => $j->catatanKonseling?->hasil,
                    'survey_status' => $survei?->status,
                    'survey_url' => $survei?->token ? route('survei.tampil', $survei->token) : null,
                    'survey_wa' => $nomorWa,
                    'boleh_survey' => $j->status === 'selesai' && $survei?->status !== 'terisi',
                ];
            })->all();
            $selesai = $p->status === 'selesai' || $group['penugasan_status'] === 'selesai' || ($items->isNotEmpty() && $items->every(fn ($j) => $j->status === 'selesai'));
        $kategori = data_get($d, 'kategori_masalah', data_get($d, 'lingkup_hambatan', 'Konseling Individual'));
            if (is_array($kategori)) $kategori = implode(', ', $kategori);
            return [
                'kode' => $p->kode_pengajuan ?? '-',
                // Nama, NIM, dan kontak diambil dari profil konseli utama yang
                // sama untuk semua pengajuan, sedangkan data sesi tetap milik tiket ini.
                'nama' => $namaProfil,
                'nim' => $nimProfil,
                'prodi' => $p->konseli?->jenis_konseli === 'wali_mahasiswa'
                    ? data_get($d, 'wali_mahasiswa_prodi', '-')
                    : data_get($identitas, 'mahasiswa_jurusan', data_get($identitas, 'alumni_jurusan', data_get($identitas, 'prodi', '-'))),
                'kategori' => $kategori,
                'status' => $selesai ? 'Selesai' : 'Aktif',
                'jumlah_sesi' => $items->count(),
                'ada_jadwal' => $items->isNotEmpty(),
                'jadwal_id' => $latest?->id,
                'sesi' => $sesi,
                'terakhir' => $latest?->tanggal?->translatedFormat('d M Y') ?? $p->created_at?->translatedFormat('d M Y'),
                'konselor_sebelumnya' => null,
                'catatan' => $items->filter(fn ($j) => filled($j->catatanKonseling?->ringkasan))->sortByDesc('tanggal')->first()?->catatanKonseling?->ringkasan,
                'riwayat' => $riwayatPerKonseli[$p->konseli_id] ?? [],
                'survey_terkirim' => $items->contains(fn ($j) => in_array($j->survei?->status, ['dikirim', 'terisi'], true)),
                'survey_sudah_diisi' => $items->isNotEmpty() && $items->every(fn ($j) => $j->status !== 'selesai' || $j->survei?->status === 'terisi'),
                'survey_tersedia' => $items->contains(fn ($j) => $j->status === 'selesai' && $j->survei?->status !== 'terisi'),
                'survey_wa' => $nomorWa,
                'survey_url' => $latest?->survei?->token ? route('survei.tampil', $latest->survei->token) : null,
            ];
        })->sortByDesc('terakhir')->values()->all();
        return view('konselor.kasus', ['kasus'=>$kasus,'tanggalHariIni'=>now()->translatedFormat('l, d F Y')]);

        $kasus = [
            [
                'kode'                => 'KS-2025-0011',
                'nama'                => 'Andi Setiawan',
                'nim'                 => '1234567890',
                'prodi'               => 'Teknik Informatika',
                'kategori'            => 'Akademik',
                'status'              => 'Aktif',
                'jumlah_sesi'         => 3,
                'terakhir'            => '10 Sep 2026',
                'konselor_sebelumnya' => null,
                'catatan'             => "Konseli menunjukkan progres yang baik dalam manajemen waktu belajar. Masih perlu pendampingan terkait kecemasan menjelang ujian.",
                'riwayat'             => [
                    ['sesi' => 1, 'tanggal' => '20 Agu 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Akademik'],
                    ['sesi' => 2, 'tanggal' => '02 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Akademik'],
                    ['sesi' => 3, 'tanggal' => '10 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Akademik'],
                ],
                'survey_terkirim'     => false,
            ],
            [
                'kode'                => 'KS-2025-0009',
                'nama'                => 'Dewi Rahmawati',
                'nim'                 => '0987654321',
                'prodi'               => 'Ilmu Komunikasi',
                'kategori'            => 'Kesehatan Mental',
                'status'              => 'Aktif',
                'jumlah_sesi'         => 5,
                'terakhir'            => '2 Sep 2026',
                'konselor_sebelumnya' => 'Dr. Sari Dewi',
                'catatan'             => "Sesi lanjutan pemulihan dari tekanan akademik. Konseli mulai menerapkan teknik relaksasi yang diajarkan. Perlu dipantau setiap minggu.",
                'riwayat'             => [
                    ['sesi' => 1, 'tanggal' => '05 Jul 2026', 'konselor' => 'Dr. Budi Hartono, M.Psi', 'kategori' => 'Kesehatan Mental'],
                    ['sesi' => 2, 'tanggal' => '19 Jul 2026', 'konselor' => 'Dr. Budi Hartono, M.Psi', 'kategori' => 'Kesehatan Mental'],
                    ['sesi' => 3, 'tanggal' => '02 Agu 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Kesehatan Mental'],
                    ['sesi' => 4, 'tanggal' => '19 Agu 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Kesehatan Mental'],
                    ['sesi' => 5, 'tanggal' => '02 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Kesehatan Mental'],
                ],
                'survey_terkirim'     => true,
            ],
            [
                'kode'                => 'KS-2025-0004',
                'nama'                => 'Rizky Pratama',
                'nim'                 => '2210631170004',
                'prodi'               => 'Teknik Sipil',
                'kategori'            => 'Sosial',
                'status'              => 'Selesai',
                'jumlah_sesi'         => 2,
                'terakhir'            => '15 Agu 2025',
                'konselor_sebelumnya' => null,
                'catatan'             => "Konseli sempat mengalami kesulitan adaptasi dengan lingkungan sosial di kampus. Setelah 2 sesi, konseli sudah lebih percaya diri berinteraksi dengan teman sekelas. Kasus ditutup dengan rekomendasi tindak lanjut mandiri.",
                'riwayat'             => [
                    ['sesi' => 1, 'tanggal' => '01 Agu 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Sosial'],
                    ['sesi' => 2, 'tanggal' => '15 Agu 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Sosial'],
                ],
                'survey_terkirim'     => true,
            ],
            [
                'kode'                => 'KS-2025-0002',
                'nama'                => 'Maya Sari',
                'nim'                 => '2210631170005',
                'prodi'               => 'Akuntansi',
                'kategori'            => 'Disabilitas',
                'status'              => 'Selesai',
                'jumlah_sesi'         => 4,
                'terakhir'            => '20 Agu 2025',
                'konselor_sebelumnya' => null,
                'catatan'             => "Pendampingan terkait aksesibilitas fasilitas kuliah telah selesai dilakukan. Koordinasi dengan pihak fakultas sudah berjalan dan kebutuhan konseli terpenuhi. Kasus ditutup.",
                'riwayat'             => [
                    ['sesi' => 1, 'tanggal' => '25 Jul 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Disabilitas'],
                    ['sesi' => 2, 'tanggal' => '01 Agu 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Disabilitas'],
                    ['sesi' => 3, 'tanggal' => '08 Agu 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Disabilitas'],
                    ['sesi' => 4, 'tanggal' => '20 Agu 2025', 'konselor' => 'Dr. Sari Dewi, M.Psi', 'kategori' => 'Disabilitas'],
                ],
                'survey_terkirim'     => false,
            ],
        ];

        $tanggalHariIni = 'Rabu, 23 September 2026';

        return view('konselor.kasus', compact('kasus', 'tanggalHariIni'));
    }

    /**
     * Simpan catatan konseling untuk sebuah kasus (masih dummy).
     * Nanti diarahkan lewat route POST, mis:
     * Route::post('/konselor/kasus/{kode}/catatan', [KasusController::class, 'simpanCatatan'])
     *     ->name('konselor.kasus.catatan.simpan');
     */
    public function simpanCatatan(Request $request, string $kode)
    {
        $request->validate([
            'catatan' => 'required|string',
            'jadwal_id' => 'nullable|integer',
        ]);

        $jadwal = JadwalKonseling::where('konselor_id', auth()->id())
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->whereHas('pengajuan', fn ($query) => $query->where('kode_pengajuan', $kode))
            ->when($request->filled('jadwal_id'), fn ($query) => $query->whereKey((int) $request->input('jadwal_id')))
            ->latest('tanggal')->firstOrFail();
        if (! in_array($jadwal->status, ['terjadwal', 'berlangsung', 'selesai'], true)) {
            abort(422, 'Sesi belum berada pada status yang dapat dicatat.');
        }
        if ($jadwal->status === 'selesai') {
            abort(422, 'Sesi ini sudah selesai dan catatan tidak dapat diubah lagi.');
        }
        if ($request->boolean('selesai') && (! trim((string) $request->input('catatan')) || ! trim((string) $request->input('hasil')))) {
            abort(422, 'Catatan konseling dan hasil konseling wajib diisi sebelum sesi diselesaikan.');
        }
        DB::transaction(function () use ($request, $jadwal) {
            $hasil = trim((string) $request->input('hasil', ''));
            $hasilLanjutan = $hasil === 'Perlu Sesi Lanjutan';
            $hasilTidakHadir = $hasil === 'Konseli Tidak Hadir' || $request->input('kehadiran') === 'tidak_hadir';
            CatatanKonseling::updateOrCreate(
                ['jadwal_id' => $jadwal->id],
                ['konselor_id' => auth()->id(), 'kehadiran' => $hasilTidakHadir ? 'tidak_hadir' : $request->input('kehadiran', 'hadir'), 'hasil' => $hasil ?: null, 'ringkasan' => $request->input('catatan'), 'tindak_lanjut' => $hasilLanjutan ? 'Perlu Sesi Lanjutan' : $request->input('tindak_lanjut'), 'dicatat_at' => now()]
            );
            if ($request->boolean('selesai')) {
                $jadwalStatus = $hasilTidakHadir ? 'tidak_hadir' : 'selesai';
                $pengajuanStatus = $hasilTidakHadir ? 'dibatalkan' : ($hasilLanjutan ? 'sesi_lanjutan' : 'selesai');
                $jadwal->update(['status' => $jadwalStatus]);
                $jadwal->pengajuan->update(['status' => $pengajuanStatus]);
                $label = $hasilTidakHadir ? 'Konseli tidak hadir; jadwal dibatalkan.' : ($hasilLanjutan ? 'Hasil sesi memerlukan penjadwalan sesi lanjutan dengan tiket yang sama.' : 'Konselor menyimpan hasil sesi konseling.');
                $jadwal->pengajuan->riwayatStatus()->create(['status_lama' => 'berlangsung', 'status_baru' => $pengajuanStatus, 'diubah_oleh' => auth()->id(), 'keterangan' => $label]);
                if (! $hasilTidakHadir) {
                    SurveiKonseling::firstOrCreate(
                        ['jadwal_id' => $jadwal->id],
                        ['token' => Str::random(40), 'status' => 'belum_dikirim']
                    );
                }
            }
        });
        if ($request->boolean('selesai') && $jadwal->pengajuan?->email && $jadwal->status === 'selesai') {
            $catatan = $jadwal->catatanKonseling()->first();
            $survei = $jadwal->survei()->first();
            $link = $survei?->token ? route('survei.tampil', $survei->token) : route('cek-status.hasil', ['kode' => $jadwal->pengajuan->kode_pengajuan, 'email' => $jadwal->pengajuan->email]);
            try {
                InteractiveMail::send($jadwal->pengajuan->email, 'Terima Kasih Telah Mengikuti Konseling', [
                    'greeting' => 'Halo, '.($jadwal->pengajuan?->konseli?->nama ?: 'Konseli'),
                    'message' => 'Terima kasih telah membuka ruang untuk diri sendiri. Tidak semua hal harus selesai hari ini, tetapi setiap langkah tetap berarti. Berikut ringkasan informasi dari sesi Anda.',
                    'status' => 'Sesi selesai',
                    'details' => ['Kode pengajuan' => $jadwal->pengajuan?->kode_pengajuan, 'Hasil konseling' => $catatan?->hasil ?: '-', 'Catatan sesi' => $catatan?->ringkasan ?: '-'],
                    'actionUrl' => $link,
                    'actionLabel' => 'Isi Survei Konseling',
                    'quote' => 'Semoga perjalanan Anda terasa sedikit lebih ringan setelah ini.',
                ]);
            } catch (\Throwable $e) { report($e); }
        }
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $request->boolean('selesai') ? 'Selesaikan Konseling' : 'Simpan Catatan Konseling',
            'detail' => 'Memperbarui catatan untuk pengajuan '.($jadwal->pengajuan?->kode_pengajuan ?? $kode).'.',
            'ip_address' => $request->ip(),
        ]);
        if ($request->expectsJson()) return response()->json(['message' => 'Catatan berhasil disimpan.']);

        return back()->with('sukses', 'Catatan berhasil disimpan.');
    }

    public function tandaiDarurat(Request $request, string $kode)
    {
        $jadwal = JadwalKonseling::with('pengajuan')->where('konselor_id', auth()->id())
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->whereHas('pengajuan', fn ($query) => $query->where('kode_pengajuan', $kode))
            ->latest('tanggal')->firstOrFail();
        $pengajuan = $jadwal->pengajuan;
        $data = (array) ($pengajuan->data ?? []);
        $data['kondisi_darurat'] = 'ya';
        $data['darurat_status'] = 'mendesak';
        $data['darurat_ditandai_oleh'] = auth()->id();
        $data['darurat_ditandai_at'] = now()->toIso8601String();
        $pengajuan->update(['data' => $data]);
        ActivityLog::create([
            'user_id' => auth()->id(), 'action' => 'Tandai Pengajuan Mendesak',
            'detail' => 'Menandai pengajuan '.$pengajuan->kode_pengajuan.' sebagai pengajuan mendesak.',
            'ip_address' => $request->ip(),
        ]);
        if ($request->expectsJson()) return response()->json(['message' => 'Pengajuan berhasil ditandai sebagai mendesak.']);
        return back()->with('sukses', 'Pengajuan berhasil ditandai sebagai mendesak.');
    }

    /**
     * Kirim/generate tautan survey pengalaman konseling untuk konseli (masih dummy).
     * Nanti diarahkan lewat route POST, mis:
     * Route::post('/konselor/kasus/{kode}/survey', [KasusController::class, 'kirimSurvey'])
     *     ->name('konselor.kasus.survey.kirim');
     */
    public function kirimSurvey(Request $request, string $kode)
    {
        $jadwal = JadwalKonseling::with('survei')->where('konselor_id', auth()->id())
            ->where('status', 'selesai')
            ->whereHas('pengajuan', fn ($q) => $q->where('kode_pengajuan', $kode))
            ->when($request->filled('jadwal_id'), fn ($q) => $q->whereKey((int) $request->input('jadwal_id')))
            ->latest('tanggal')->firstOrFail();
        $survei = $jadwal->survei ?: $jadwal->survei()->create(['token' => Str::random(40), 'status' => 'belum_dikirim']);
        if ($survei->status !== 'terisi') {
            $survei->update(['status' => 'dikirim', 'dikirim_at' => now()]);
        }
        $payload = ['token' => $survei->token, 'url' => route('survei.tampil', $survei->token), 'state' => $survei->status];
        if (request()->expectsJson()) return response()->json($payload);
        return back()->with('sukses', 'Tautan survey berhasil dibuat.');
    }
}
