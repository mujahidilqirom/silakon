<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\JadwalKonseling;
use App\Models\KetersediaanKonselor;
use App\Models\PenugasanKonseling;
use App\Models\ProgramKonselingKelompok;
use App\Models\PesertaKonselingKelompok;
use App\Models\SelfHelp;
use App\Models\Konseli;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\SimplePdf;
use Carbon\Carbon;
use App\Support\TelegramNotifier;
use App\Services\PrioritasKonselingService;
use Illuminate\Support\Facades\Mail;
use App\Support\InteractiveMail;

class AdminPendaftaranController extends Controller
{
    private function audit(string $action, string $detail = ''): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'detail' => $detail,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * GET /admin/pendaftaran
     * Data pendaftaran masih dummy.
     */
    public function index(Request $request)
    {
        $statusAktif = $request->query('status', 'semua');
        // Sinkronkan nilai screening lama agar tabel selalu memakai rumus WHO-5
        // + aging SAW terbaru, bukan nilai yang tersimpan dari perhitungan lama.
        Pengajuan::query()->whereNotNull('who5_skor')->get()->each(function (Pengajuan $item) {
            $nilai = app(PrioritasKonselingService::class)->untuk($item);
            if ((float) $item->saw_nilai !== (float) $nilai['saw_nilai'] || $item->prioritas !== $nilai['prioritas']) {
                $item->update(['saw_nilai' => $nilai['saw_nilai'], 'prioritas' => $nilai['prioritas']]);
            }
        });
        // Pengajuan baru diurutkan berdasarkan rekomendasi SAW. Nilai null
        // (data lama sebelum WHO-5) tetap ditampilkan di bagian bawah.
        $query = Pengajuan::with('konseli')
            ->orderByRaw('saw_nilai IS NULL')
            ->orderByDesc('saw_nilai')
            ->orderBy('created_at');
        if ($statusAktif !== 'semua') {
            $query->where('status', $this->mapStatusFilter($statusAktif));
        }
        $items = $query->paginate(15)->withQueryString();
        $rows = $items->getCollection()->map(fn (Pengajuan $item) => $this->legacyRow($item))->values();

        return view('admin.pendaftaran', [
            'menuAktif' => 'pendaftaran',
            'filterStatus' => [
                ['key' => 'semua', 'label' => 'Semua'],
                ['key' => 'menunggu_pemeriksaan', 'label' => 'Menunggu Pemeriksaan'],
                ['key' => 'diterima', 'label' => 'Diterima'],
                ['key' => 'terjadwal', 'label' => 'Terjadwal'],
                ['key' => 'sesi_lanjutan', 'label' => 'Menunggu Sesi Lanjutan'],
                ['key' => 'selesai', 'label' => 'Selesai'],
                ['key' => 'ditolak', 'label' => 'Ditolak'],
            ],
            'filterAktif' => $statusAktif,
            'pengajuan' => $rows,
            'totalData' => $items->total(),
            'halamanAktif' => $items->currentPage(),
            'totalHalaman' => $items->lastPage(),
        ]);

        // Legacy demo data below is retained for reference during migration.
        $filterStatus = [
            ['key' => 'semua',        'label' => 'Semua'],
            ['key' => 'menunggu',     'label' => 'Menunggu Verifikasi'],
            ['key' => 'diverifikasi', 'label' => 'Diverifikasi'],
            ['key' => 'terjadwal',    'label' => 'Terjadwal'],
            ['key' => 'selesai',      'label' => 'Selesai'],
            ['key' => 'ditolak',      'label' => 'Ditolak'],
        ];

        $pengajuan = $this->daftarPengajuan();

        $statusAktif = $request->query('status', 'semua');

        return view('admin.pendaftaran', [
            'menuAktif'     => 'pendaftaran',
            'filterStatus'  => $filterStatus,
            'filterAktif'   => $statusAktif,
            'pengajuan'     => $pengajuan->values(),
            'totalData'     => $pengajuan->count(),
            'halamanAktif'  => (int) $request->query('page', 1),
            'totalHalaman'  => 1,
        ]);
    }

    public function eksporPendaftaran(Request $request)
    {
        $rows=Pengajuan::with('konseli')->when($request->query('status'),fn($q,$v)=>$q->where('status',$this->mapStatusFilter($v)))->latest()->get();
        $html='<meta charset="utf-8"><style>body{font-family:Arial;font-size:12px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #bbb;padding:6px;text-align:left}th{background:#e8efe8}</style><h2>Pengajuan Konseling SILAKON</h2><table><tr><th>Kode</th><th>Nama</th><th>Jenis Konseli</th><th>Status</th><th>Tanggal</th></tr>';
        $lines=['PENGAJUAN KONSELING SILAKON','','Kode | Nama | Jenis Konseli | Status | Tanggal'];
        foreach($rows as $p){$r=[$p->kode_pengajuan,$p->konseli?->nama ?? '-',ucwords(str_replace('_',' ',$p->konseli?->jenis_konseli ?? '-')),$p->status,optional($p->created_at)->format('d-m-Y H:i')];$html.='<tr>'.collect($r)->map(fn($v)=>'<td>'.e($v).'</td>')->implode('').'</tr>';$lines[]=implode(' | ',$r);}$html.='</table>';
        if($request->query('jenis')==='pdf')return response(SimplePdf::make($lines),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="pengajuan-silakon.pdf"']);
        return response($html,200,['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8','Content-Disposition'=>'attachment; filename="pengajuan-silakon.xls"']);
    }

    public function eksporSemuaTiket(Request $request)
    {
        $query = Pengajuan::latest();
        $semua = $request->boolean('semua');
        $labelPeriode = 'Semua periode';
        if (! $semua) {
            $dari = Carbon::create((int) $request->query('dari_tahun', now()->year), (int) $request->query('dari_bulan', now()->month), 1)->startOfMonth();
            $sampai = Carbon::create((int) $request->query('sampai_tahun', now()->year), (int) $request->query('sampai_bulan', now()->month), 1)->endOfMonth();
            if ($dari->gt($sampai)) [$dari, $sampai] = [$sampai, $dari];
            $query->whereBetween('created_at', [$dari, $sampai]);
            $labelPeriode = $dari->translatedFormat('F Y').' – '.$sampai->translatedFormat('F Y');
        }
        $rows = $query->get();
        // Susun field umum terlebih dahulu. Field khusus (wali, unit kerja,
        // hubungan, dan sejenisnya) diletakkan setelah field umum.
        $normalized = $rows->map(function ($p) {
            $d = (array) ($p->data ?? []); $jenis = $d['jenis_konseli'] ?? $p->konseli?->jenis_konseli;
            $pick = fn (array $keys, $fallback = '-') => collect($keys)->map(fn ($key) => data_get($d, $key))->first(fn ($value) => $value !== null && $value !== '') ?? $fallback;
            $nama = match ($jenis) {
                'mahasiswa' => $pick(['mahasiswa_nama', 'nama']), 'alumni' => $pick(['alumni_nama', 'nama']), 'tenaga_kependidikan' => $pick(['tk_nama', 'nama']),
                'wali_mahasiswa' => ($d['wali_subjek_konseling'] ?? 'wali') === 'mahasiswa' ? $pick(['wali_mahasiswa_nama']) : $pick(['wali_nama']), default => $pick(['nama'], $p->konseli?->nama ?: '-'),
            };
            $fakultas = $pick(match ($jenis) { 'mahasiswa' => ['mahasiswa_fakultas','fakultas'], 'alumni' => ['alumni_fakultas','fakultas'], 'wali_mahasiswa' => ['wali_mahasiswa_fakultas','fakultas'], default => ['fakultas'] });
            $prodi = $pick(match ($jenis) { 'mahasiswa' => ['mahasiswa_prodi','prodi'], 'alumni' => ['alumni_prodi','prodi'], 'wali_mahasiswa' => ['wali_mahasiswa_prodi','prodi'], default => ['prodi'] });
            $telepon = $pick(match ($jenis) { 'mahasiswa' => ['mahasiswa_telepon','telepon'], 'alumni' => ['alumni_telepon','telepon'], 'tenaga_kependidikan' => ['tk_telepon','telepon'], 'wali_mahasiswa' => ($d['wali_subjek_konseling'] ?? 'wali') === 'mahasiswa' ? ['wali_mahasiswa_telepon','wali_telepon'] : ['wali_telepon','telepon'], default => ['telepon'] });
            $common = ['Kode Pengajuan'=>$p->kode_pengajuan, 'Nama'=>$nama, 'Email'=>$p->email ?: '-', 'NIM'=>$p->nim ?: '-', 'Telepon'=>$telepon, 'Jenis Konseli'=>ucwords(str_replace('_',' ', $jenis ?: '-')), 'Fakultas'=>$fakultas, 'Prodi'=>$prodi, 'Status'=>str_replace('_',' ',ucfirst($p->status)), 'Skor WHO-5'=>$p->who5_skor !== null ? $p->who5_skor.'/100' : '-', 'Nilai SAW'=>$p->saw_nilai !== null ? number_format((float) $p->saw_nilai, 4) : '-', 'Prioritas'=>$p->prioritas ?: '-', 'Tanggal Pengajuan'=>optional($p->created_at)->format('d-m-Y H:i') ?: '-'];
            $extra = collect($d)->except(['jenis_konseli','verification_code','consent','_token','deskripsi_masalah','who5_jawaban'])->reject(fn ($v, $key) => preg_match('/^(mahasiswa|alumni|tk|wali|dosen)_(nama|email|nim|telepon|fakultas|prodi)$/', $key))->mapWithKeys(function ($value, $key) { if (is_array($value)) $value = implode(', ', array_filter($value)); return [ucwords(str_replace('_',' ', $key)) => ($value === null || $value === '' ? '-' : (string) $value)]; })->all();
            return ['common'=>$common, 'extra'=>$extra];
        });
        $headers = ['Kode Pengajuan','Nama','Email','NIM','Telepon','Jenis Konseli','Fakultas','Prodi','Status','Skor WHO-5','Nilai SAW','Prioritas','Tanggal Pengajuan'];
        $extraHeaders = $normalized->flatMap(fn ($row) => array_keys($row['extra']))->unique()->values()->all();
        $headers = array_merge($headers, $extraHeaders);
        $html = '<meta charset="utf-8"><style>body{font-family:Arial;font-size:11px}h2{color:#284c34}p{color:#4b5a51}table{border-collapse:collapse;width:100%}th,td{border:1px solid #b8c5ba;padding:6px;text-align:left;vertical-align:top;white-space:pre-wrap}th{background:#e6f0e7}</style><h2>Ekspor Lengkap Pengajuan Konseling SILAKON</h2><p>Periode: '.e($labelPeriode).'</p><table><tr>'.collect($headers)->map(fn ($h) => '<th>'.e($h).'</th>')->implode('').'</tr>';
        $lines = ['EKSPOR LENGKAP PENGAJUAN KONSELING SILAKON', 'Periode: '.$labelPeriode, '', implode(' | ', $headers)];
        foreach ($normalized as $row) {
            $all = array_merge($row['common'], $row['extra']);
            $values = array_map(fn ($header) => $all[$header] ?? '-', $headers);
            $html .= '<tr>'.collect($values)->map(fn ($v) => '<td>'.e($v).'</td>')->implode('').'</tr>';
            $lines[] = implode(' | ', $values);
        }
        $html .= '</table>';
        $this->audit('Ekspor Semua Pengajuan', 'Mengekspor seluruh pengajuan sebagai '.strtolower($request->query('jenis','excel')).'.');
        if ($request->query('jenis') === 'pdf') return response(SimplePdf::make($lines), 200, ['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="semua-pengajuan-silakon.pdf"']);
        return response($html, 200, ['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8','Content-Disposition'=>'attachment; filename="semua-pengajuan-silakon.xls"']);
    }

    public function eksporDataKonseli(Request $request)
    {
        $rows = Konseli::with('pengajuan')->withCount(['pengajuan','pengajuan as tiket_aktif_count' => fn ($q) => $q->aktif()])->latest('terakhir_mendaftar_at')->get();
        $html = '<meta charset="utf-8"><style>body{font-family:Arial;font-size:12px}h2{color:#284c34}table{border-collapse:collapse;width:100%}th,td{border:1px solid #b8c5ba;padding:7px;text-align:left;vertical-align:top}th{background:#e6f0e7}</style><h2>Data Konseli SILAKON</h2><table><tr><th>Nama</th><th>Jenis Konseli</th><th>Email</th><th>NIM</th><th>Telepon</th><th>Total Pengajuan</th><th>Pengajuan Aktif</th><th>Terakhir Daftar</th></tr>';
        $lines = ['DATA KONSELI SILAKON', '', 'Nama | Jenis | Email | NIM | Telepon | Total Pengajuan | Aktif | Terakhir Daftar'];
        foreach ($rows as $k) {
            $values = [$k->nama, ucwords(str_replace('_',' ', $k->jenis_konseli)), $k->email, $k->nim ?: '-', $k->telepon ?: '-', $k->pengajuan_count, $k->tiket_aktif_count, optional($k->terakhir_mendaftar_at)->format('d-m-Y H:i') ?: '-'];
            $html .= '<tr>'.collect($values)->map(fn ($v) => '<td>'.e($v).'</td>')->implode('').'</tr>'; $lines[] = implode(' | ', $values);
        }
        $html .= '</table>';
        $this->audit('Ekspor Data Konseli', 'Mengekspor data konseli sebagai '.strtolower($request->query('jenis','excel')).'.');
        if ($request->query('jenis') === 'pdf') return response(SimplePdf::make($lines), 200, ['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="data-konseli-silakon.pdf"']);
        return response($html, 200, ['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8','Content-Disposition'=>'attachment; filename="data-konseli-silakon.xls"']);
    }

    /**
     * GET /admin/pendaftaran/{kode}
     * Halaman detail satu pengajuan. Tombol "Verifikasi" hanya muncul bila
     * status = menunggu (diatur di view). Data masih dummy.
     */
    public function show($kode)
    {
        $record = Pengajuan::with([
            'konseli.pengajuan.jadwal.konselor',
            'konseli.pengajuan.penugasan.konselor',
            'riwayatStatus',
        ])->where('kode_pengajuan', $kode)->firstOrFail();
        $row = $this->legacyRow($record);
        $riwayatKonseling = $record->konseli?->pengajuan
            ->sortByDesc('created_at')
            ->map(function ($item) use ($record) {
                $jadwal = $item->jadwal->sortByDesc(fn ($j) => $j->tanggal?->timestamp ?? 0)->first();
                $penugasan = $item->penugasan->sortByDesc('created_at')->first();
                $konselor = $jadwal?->konselor?->name ?? $penugasan?->konselor?->name;
                return [
                    'kode' => $item->kode_pengajuan,
                    'konselor' => $konselor ?: 'Belum ditentukan',
                    'tanggal' => $jadwal?->tanggal?->translatedFormat('d M Y') ?? $item->created_at?->translatedFormat('d M Y'),
                    'jam' => $jadwal?->mulai ? substr($jadwal->mulai, 0, 5) . ' WIB' : null,
                    'status' => $this->statusLabel($item->status),
                    'is_current' => $item->id === $record->id,
                ];
            })->values() ?? collect();
        return view('admin.pendaftaran-detail', [
            'menuAktif' => 'pendaftaran',
            'pengajuan' => $row,
            'detail' => $this->legacyDetail($record),
            'identitas' => $this->legacyIdentitas($record),
            'riwayatKonseling' => $riwayatKonseling,
            'verifikasi' => $record->terverifikasi_at ? ['waktu' => $record->terverifikasi_at->format('d M Y, H:i'), 'oleh' => optional($record->pemeriksa)->name] : null,
            'tahapan' => [
                ['key' => 'menunggu_pemeriksaan', 'label' => 'Menunggu pemeriksaan'],
                ['key' => 'diterima', 'label' => 'Diterima'],
                ['key' => 'terjadwal', 'label' => 'Terjadwal'],
                ['key' => 'selesai', 'label' => 'Selesai'],
            ],
        ]);

        // Legacy demo data below is retained for reference during migration.
        $pengajuan = $this->daftarPengajuan()->firstWhere('kode', $kode);

        abort_if(! $pengajuan, 404);

        $detail = $this->detailPengajuan($pengajuan);

        return view('admin.pendaftaran-detail', [
            'menuAktif'  => 'pendaftaran',
            'pengajuan'  => $pengajuan,
            'detail'     => $detail,
            'identitas'  => $this->identitasKonseli($detail),
            'verifikasi' => $pengajuan['diverifikasi'] ?? ($detail['diverifikasi'] ?? null),
            'tahapan'    => [
                ['key' => 'menunggu',     'label' => 'Menunggu verifikasi'],
                ['key' => 'diverifikasi', 'label' => 'Diverifikasi'],
                ['key' => 'terjadwal',    'label' => 'Terjadwal'],
                ['key' => 'selesai',      'label' => 'Selesai'],
            ],
        ]);
    }

    /**
     * POST /admin/pendaftaran/{kode}/verifikasi
     * Memverifikasi pengajuan berstatus "menunggu". Karena database masih dummy,
     * hasilnya disimpan sementara di session. Nanti diganti mis.:
     *   $pengajuan->update(['status' => 'diverifikasi', 'diverifikasi_at' => now(), 'diverifikasi_oleh' => $request->user()->id]);
     * Input opsional "kembali=daftar" -> setelah selesai kembali ke daftar pendaftaran masuk,
     * selain itu kembali ke halaman detail.
     */
    public function verifikasi(Request $request, $kode)
    {
        $record = Pengajuan::with('konseli')->where('kode_pengajuan', $kode)->firstOrFail();
        $kembali = $request->input('kembali') === 'daftar'
            ? redirect()->route('admin.pendaftaran')
            : redirect()->route('admin.pendaftaran.show', $kode);

        if ($record->status !== Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN) {
            return $kembali->with('galat', "Pengajuan {$kode} tidak sedang menunggu pemeriksaan.");
        }

        $decision = $request->input('keputusan', 'diterima');
        $status = $decision === 'ditolak' ? Pengajuan::STATUS_DITOLAK : Pengajuan::STATUS_DITERIMA;
        $request->validate(['alasan_penolakan' => [$status === Pengajuan::STATUS_DITOLAK ? 'required' : 'nullable', 'string', 'max:1000']]);
        $old = $record->status;
        $record->update([
            'status' => $status,
            'diperiksa_oleh' => $request->user()->id,
            'diperiksa_at' => now(),
            'alasan_penolakan' => $request->input('alasan_penolakan'),
        ]);
        $record->riwayatStatus()->create([
            'status_lama' => $old,
            'status_baru' => $status,
            'diubah_oleh' => $request->user()->id,
            'keterangan' => $status === Pengajuan::STATUS_DITOLAK ? $request->input('alasan_penolakan') : 'Pengajuan diterima oleh Admin.',
        ]);
        $this->audit('Ubah Status Pengajuan', 'Pengajuan '.$kode.' diubah dari '.$old.' menjadi '.$status.($status === Pengajuan::STATUS_DITOLAK ? '. Alasan: '.$request->input('alasan_penolakan') : '.'));
        if ($record->email) {
            try {
                $ditolak = $status === Pengajuan::STATUS_DITOLAK;
                InteractiveMail::send($record->email, $ditolak ? 'Pengajuan Konseling Belum Dapat Diproses' : 'Pengajuan Konseling Diterima', [
                    'greeting' => 'Halo, '.($record->konseli?->nama ?: 'Konseli'),
                    'message' => $ditolak
                        ? 'Saat ini pengajuan Anda belum dapat dilanjutkan. Silakan membaca alasan yang diberikan dan mengajukan kembali apabila sudah siap.'
                        : 'Pengajuan Anda telah diterima. Selanjutnya, waktu yang tepat akan dipersiapkan agar Anda dapat berbicara dengan nyaman dan aman.',
                    'status' => $ditolak ? 'Belum dapat diproses' : 'Diterima',
                    'details' => array_filter(['Kode pengajuan' => $record->kode_pengajuan, 'Alasan' => $ditolak ? ($record->alasan_penolakan ?: '-') : null]),
                    'actionUrl' => route('cek-status.hasil', ['kode' => $record->kode_pengajuan, 'email' => $record->email]),
                    'actionLabel' => 'Lihat Status Pengajuan',
                    'quote' => $ditolak ? 'Penolakan hari ini bukan berarti perjalanan Anda harus berhenti.' : 'Langkah Anda telah diterima. Terima kasih telah mempercayakan cerita Anda.',
                ]);
            } catch (\Throwable $e) { report($e); }
        }

        return $kembali->with('sukses', "Pengajuan {$kode} berhasil diperbarui.");

        // Legacy session-backed verification below is retained for reference.
        $pengajuan = $this->daftarPengajuan()->firstWhere('kode', $kode);

        abort_if(! $pengajuan, 404);

        $kembali = $request->input('kembali') === 'daftar'
            ? redirect()->route('admin.pendaftaran')
            : redirect()->route('admin.pendaftaran.show', $kode);

        if ($pengajuan['tone'] !== 'menunggu') {
            return $kembali->with('galat', 'Pengajuan ' . $kode . ' tidak berstatus menunggu verifikasi, jadi tidak bisa diverifikasi lagi.');
        }

        $bulan    = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sekarang = now();

        $diverifikasi = session('pengajuan_diverifikasi', []);

        $diverifikasi[$kode] = [
            'waktu' => $sekarang->format('j') . ' ' . $bulan[(int) $sekarang->format('n')] . ' ' . $sekarang->format('Y') . ', ' . $sekarang->format('H.i'),
            'oleh'  => optional($request->user())->name ?? 'Admin',
        ];

        session(['pengajuan_diverifikasi' => $diverifikasi]);

        return $kembali->with('sukses', 'Pengajuan ' . $kode . ' atas nama ' . $pengajuan['nama'] . ' berhasil diverifikasi.');
    }

    /**
     * GET /admin/penjadwalan
     * Daftar seluruh pengajuan dari database beserta ringkasan hasil sesi.
     */
    public function penjadwalan()
    {
        $pengajuan = Pengajuan::with(['konseli', 'jadwal' => fn ($q) => $q->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])])
            ->whereIn('status', ['diterima', 'ditugaskan', 'menunggu_jadwal', 'terjadwal', 'sesi_lanjutan'])
            ->orderByRaw('saw_nilai IS NULL')->orderByDesc('saw_nilai')->orderBy('created_at')->get();
        $jadwalRecords = JadwalKonseling::with(['pengajuan.konseli', 'konselor'])->whereIn('status', ['terjadwal', 'berlangsung'])->latest('tanggal')->get();
        $konselor = \App\Models\User::where('role', 'konselor')->where('is_active', true)->orderBy('name')->get();
        $kategoriLabel = static function ($value, string $fallback = 'Konseling individual'): string {
            if (is_array($value)) return collect($value)->flatten()->filter(fn ($v) => is_scalar($v) && trim((string) $v) !== '')->map(fn ($v) => trim((string) $v))->implode(', ') ?: $fallback;
            return trim((string) $value) !== '' ? (string) $value : $fallback;
        };
        $antrianLanjutan = JadwalKonseling::with(['pengajuan.konseli', 'konselor', 'catatanKonseling'])
            ->where('status', 'selesai')
            ->whereHas('pengajuan', fn ($q) => $q->where('status', 'sesi_lanjutan'))
            ->whereHas('catatanKonseling', fn ($q) => $q->where(function ($q) {
                $q->where('hasil', 'Perlu Sesi Lanjutan')->orWhere('tindak_lanjut', 'Perlu Sesi Lanjutan');
            }))
            ->latest('tanggal')->get()->map(function ($j) use ($kategoriLabel) {
                // Nomor sesi berikutnya dihitung dari seluruh sesi selesai
                // pada tiket yang sama, bukan dari jumlah pengajuan konseli.
                $jumlahSesiSelesai = JadwalKonseling::where('pengajuan_id', $j->pengajuan_id)
                    ->where('status', 'selesai')->count();
                return [
                    'pengajuan_id' => $j->pengajuan_id,
                    'konseli' => $j->pengajuan?->konseli?->nama ?? 'Konseli',
                    'konselor' => $j->konselor?->name ?? '-',
            'kategori' => ucfirst((string) ($j->pengajuan?->prioritas ?: data_get($j->pengajuan?->data, 'prioritas', 'Belum dihitung'))),
                    'sesi' => 'Sesi '.($jumlahSesiSelesai + 1),
                    'tanggal_terakhir' => optional($j->tanggal)->format('d M Y'),
                ];
            })->values()->all();
        return view('admin.penjadwalan', [
            'menuAktif' => 'penjadwalan',
            'bulanTampil' => now()->translatedFormat('F Y'),
            'antrianLanjutan' => $antrianLanjutan,
            'jadwal' => $jadwalRecords->map(fn (JadwalKonseling $item) => [
                'id' => $item->id, 'pengajuan_id' => $item->pengajuan_id, 'konseli' => $item->pengajuan->konseli->nama, 'konselor' => $item->konselor->name,
                'tanggal' => $item->tanggal->format('d M Y'), 'tanggal_iso' => $item->tanggal->format('Y-m-d'),
            'waktu' => substr($item->mulai, 0, 5), 'lokasi' => $item->lokasi, 'kategori' => ucfirst((string) ($item->pengajuan->prioritas ?: data_get($item->pengajuan->data, 'prioritas', 'Belum dihitung'))),
                'status' => ucfirst($item->status),
            ])->values()->all(),
            'konseliTerverifikasi' => $pengajuan->filter(fn (Pengajuan $item) => $item->jadwal->whereIn('status', ['terjadwal', 'berlangsung'])->isEmpty())->map(fn (Pengajuan $item) => [
                'id' => $item->id, 'kode' => $item->kode_pengajuan, 'nama' => $item->konseli->nama, 'nim' => $item->nim ?: $item->email,
                'prodi' => data_get($item->data, 'mahasiswa_jurusan', data_get($item->data, 'alumni_jurusan', '-')),
            'kategori' => ucfirst((string) ($item->prioritas ?: data_get($item->data, 'prioritas', 'Belum dihitung'))), 'tanggal_verifikasi' => optional($item->diperiksa_at)->format('d M Y'),
                'who5_skor' => $item->who5_skor, 'saw_nilai' => $item->saw_nilai, 'prioritas' => $item->prioritas,
                'hari' => data_get($item->data, 'preferensi_hari', '-'), 'jam' => data_get($item->data, 'preferensi_waktu', '-'), 'mode' => data_get($item->data, 'metode_layanan', '-'),
            ])->values()->all(),
            'daftarKonselor' => $konselor->pluck('name')->values()->all(),
            'konselorIds' => $konselor->mapWithKeys(fn ($item) => [$item->name => $item->id])->all(),
            'ketersediaanKonselor' => KetersediaanKonselor::with('konselor')->where('status', 'tersedia')->whereDate('tanggal', '>=', today())->orderBy('tanggal')->get()->map(fn ($item) => [
                'id' => $item->id, 'konselor' => $item->konselor->name, 'tanggal' => $item->tanggal->format('Y-m-d'), 'mulai' => substr($item->mulai, 0, 5), 'selesai' => substr($item->selesai, 0, 5), 'catatan' => $item->catatan,
            ])->values()->all(),
        ]);

        // Legacy demo data below is retained for reference during migration.
        $antrianLanjutan = [
            [
                'konseli' => 'Andi Setiawan',
                'konselor' => 'Dr. Sari Dewi',
                'kategori' => 'Kesehatan Mental',
                'sesi' => 'Sesi ke-2',
                'tanggal_terakhir' => '5 Agu 2025',
            ],
            [
                'konseli' => 'Mega Lestari',
                'konselor' => 'Dr. Rina Kusumawati',
                'kategori' => 'Akademik',
                'sesi' => 'Sesi ke-3',
                'tanggal_terakhir' => '3 Agu 2025',
            ],
        ];

        $jadwal = [
            [
                'id' => 1,
                'konseli' => 'Andi Setiawan',
                'konselor' => 'Dr. Sari Dewi',
                'tanggal' => '12 Agu 2025',
                'tanggal_iso' => '2025-08-12',
                'waktu' => '09.00',
                'lokasi' => 'Ruang A',
                'kategori' => 'Kesehatan Mental',
                'status' => 'Terjadwal',
            ],
            [
                'id' => 2,
                'konseli' => 'Dewi Rahmawati',
                'konselor' => 'Dr. Rina Kusumawati',
                'tanggal' => '12 Agu 2025',
                'tanggal_iso' => '2025-08-12',
                'waktu' => '10.00',
                'lokasi' => 'Ruang B',
                'kategori' => 'Akademik',
                'status' => 'Terjadwal',
            ],
            [
                'id' => 3,
                'konseli' => 'Budi Santoso',
                'konselor' => 'Dr. Hendra Pratama',
                'tanggal' => '13 Agu 2025',
                'tanggal_iso' => '2025-08-13',
                'waktu' => '13.00',
                'lokasi' => 'Ruang A',
                'kategori' => 'Karier',
                'status' => 'Terjadwal',
            ],
            [
                'id' => 4,
                'konseli' => 'Siti Nurhaliza',
                'konselor' => 'Drs. Agus Santoso',
                'tanggal' => '14 Agu 2025',
                'tanggal_iso' => '2025-08-14',
                'waktu' => '09.00',
                'lokasi' => 'Ruang C',
                'kategori' => 'Pribadi',
                'status' => 'Terjadwal',
            ],
            [
                'id' => 5,
                'konseli' => 'Rizky Pratama',
                'konselor' => 'Dr. Sari Dewi',
                'tanggal' => '14 Agu 2025',
                'tanggal_iso' => '2025-08-14',
                'waktu' => '11.00',
                'lokasi' => 'Ruang A',
                'kategori' => 'Sosial',
                'status' => 'Terjadwal',
            ],
        ];

        // Konseli yang sudah terverifikasi dan BELUM memiliki jadwal
        $konseliTerverifikasi = [
            [
                'id' => 1,
                'nama' => 'Dewi Rahmawati',
                'nim' => '2101001001',
                'prodi' => 'Psikologi',
                'kategori' => 'Akademik',
                'tanggal_verifikasi' => '10 Agu 2025',
                'hari' => 'Senin',
                'jam' => '09.00 - 11.00',
                'mode' => 'Offline',
            ],
            [
                'id' => 2,
                'nama' => 'Fajar Nugroho',
                'nim' => '2101001002',
                'prodi' => 'Teknik Informatika',
                'kategori' => 'Karier',
                'tanggal_verifikasi' => '10 Agu 2025',
                'hari' => 'Selasa',
                'jam' => '10.00 - 12.00',
                'mode' => 'Online',
            ],
            [
                'id' => 3,
                'nama' => 'Nadia Permatasari',
                'nim' => '2101001003',
                'prodi' => 'Manajemen',
                'kategori' => 'Pribadi',
                'tanggal_verifikasi' => '11 Agu 2025',
                'hari' => 'Rabu',
                'jam' => '13.00 - 15.00',
                'mode' => 'Offline',
            ],
            [
                'id' => 4,
                'nama' => 'Raka Aditya',
                'nim' => '2101001004',
                'prodi' => 'Ilmu Komunikasi',
                'kategori' => 'Sosial',
                'tanggal_verifikasi' => '11 Agu 2025',
                'hari' => 'Kamis',
                'jam' => '09.00 - 11.00',
                'mode' => 'Online',
            ],
            [
                'id' => 5,
                'nama' => 'Putri Maharani',
                'nim' => '2101001005',
                'prodi' => 'Pendidikan',
                'kategori' => 'Kesehatan Mental',
                'tanggal_verifikasi' => '12 Agu 2025',
                'hari' => 'Jumat',
                'jam' => '14.00 - 16.00',
                'mode' => 'Offline',
            ],
        ];

        return view('admin.penjadwalan', [
            'menuAktif' => 'penjadwalan',
            'bulanTampil' => 'Agustus 2025',
            'antrianLanjutan' => $antrianLanjutan,
            'jadwal' => $jadwal,
            'konseliTerverifikasi' => $konseliTerverifikasi,
            'daftarKonselor' => $this->daftarKonselor(),
            'ketersediaanKonselor' => $this->dummyKetersediaanKonselor(),
        ]);
    }

    public function simpanKetersediaan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'konselor_id' => ['required', 'exists:users,id'], 'tanggal' => ['required', 'date'],
            'mulai' => ['required', 'date_format:H:i'], 'selesai' => ['required', 'date_format:H:i', 'after:mulai'], 'catatan' => ['nullable', 'string', 'max:255'],
        ]);
        $user = \App\Models\User::findOrFail($data['konselor_id']);
        abort_if($user->role !== 'konselor', 422, 'Pengguna yang dipilih bukan konselor.');
        $slot = KetersediaanKonselor::create($data);
        $this->audit('Tambah Ketersediaan', 'Menambahkan ketersediaan konselor untuk '.$data['tanggal'].' '.$data['mulai'].'-'.$data['selesai'].'.');
        return response()->json(['message' => 'Ketersediaan konselor berhasil disimpan.', 'id' => $slot->id]);
    }

    public function perbaruiKetersediaan(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'konselor_id' => ['required', 'exists:users,id'], 'tanggal' => ['required', 'date'],
            'mulai' => ['required', 'date_format:H:i'], 'selesai' => ['required', 'date_format:H:i', 'after:mulai'], 'catatan' => ['nullable', 'string', 'max:255'],
        ]);
        $slot = KetersediaanKonselor::findOrFail($id);
        $slot->update($data);
        $this->audit('Ubah Ketersediaan', 'Mengubah ketersediaan konselor #'.$id.' menjadi '.$data['tanggal'].' '.$data['mulai'].'-'.$data['selesai'].'.');
        return response()->json(['message' => 'Ketersediaan konselor berhasil diperbarui.', 'id' => $slot->id]);
    }

    public function hapusKetersediaan(int $id): JsonResponse
    {
        KetersediaanKonselor::findOrFail($id)->delete();
        $this->audit('Hapus Ketersediaan', 'Menghapus ketersediaan konselor #'.$id.'.');
        return response()->json(['message' => 'Ketersediaan konselor berhasil dihapus.']);
    }

    public function batalkanJadwal(int $id): JsonResponse
    {
        $jadwal = JadwalKonseling::with(['pengajuan.konseli', 'konselor'])->findOrFail($id);
        $pengajuan = $jadwal->pengajuan;
        $penugasanId = $jadwal->penugasan_id;
        DB::transaction(function () use ($jadwal, $pengajuan, $penugasanId) {
            if ($pengajuan) {
                $old = $pengajuan->status;
                $next = 'diterima';
                $pengajuan->update(['status' => $next]);
                $pengajuan->riwayatStatus()->create(['status_lama'=>$old,'status_baru'=>$next,'diubah_oleh'=>auth()->id(),'keterangan'=>'Jadwal dihapus; pengajuan dikembalikan ke tahap sebelumnya.']);
            }
            // Jadwal administratif yang dibatalkan dihapus dari daftar jadwal.
            // Riwayat perubahan status pengajuan tetap disimpan.
            $jadwal->delete();
            if ($penugasanId) PenugasanKonseling::whereKey($penugasanId)->delete();
        });
        $d = $pengajuan?->data ?? [];
        if ($jadwal->konselor) {
            app(TelegramNotifier::class)->sendTo($jadwal->konselor,
                "ℹ️ Perubahan jadwal SILAKON\n\nJadwal untuk kode ".($pengajuan?->kode_pengajuan ?: '-')." telah dibatalkan/dihapus oleh Admin. Pengajuan dikembalikan ke tahap sebelumnya."
            );
        }
        if ($pengajuan?->email) {
            try {
                InteractiveMail::send($pengajuan->email, 'Perubahan Jadwal Konseling', [
                    'greeting' => 'Halo, '.($pengajuan->konseli?->nama ?: 'Konseli'),
                    'message' => 'Jadwal konseling sebelumnya telah dibatalkan oleh Admin. Pengajuan Anda dikembalikan ke tahap sebelumnya dan dapat dijadwalkan kembali.',
                    'status' => 'Menunggu penjadwalan ulang',
                    'details' => ['Kode pengajuan' => $pengajuan->kode_pengajuan],
                    'actionUrl' => route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $pengajuan->email]),
                    'actionLabel' => 'Lihat Status Pengajuan',
                    'quote' => 'Perubahan jadwal tidak mengubah kesempatan Anda untuk mendapatkan pendampingan.',
                ]);
            } catch (\Throwable $e) { report($e); }
        }
        $this->audit('Hapus Jadwal', 'Menghapus jadwal #'.$id.' untuk pengajuan '.($pengajuan?->kode_pengajuan ?? '-').'.');
        return response()->json([
            'message' => 'Jadwal dihapus dan pengajuan dikembalikan ke tahap sebelumnya.',
            'pengajuan' => $pengajuan ? [
                'id' => $pengajuan->id,
                'nama' => $pengajuan->konseli?->nama ?? 'Konseli',
                'nim' => $pengajuan->nim ?: $pengajuan->email,
                'prodi' => data_get($d, 'mahasiswa_jurusan', data_get($d, 'alumni_jurusan', '-')),
                'kategori' => data_get($d, 'lingkup_hambatan', 'Individual'),
                'hari' => data_get($d, 'preferensi_hari', '-'),
                'jam' => data_get($d, 'preferensi_waktu', '-'),
                'mode' => data_get($d, 'metode_layanan', '-'),
            ] : null,
        ]);
    }

    public function tandaiTidakHadir(int $id): JsonResponse
    {
        $jadwal = JadwalKonseling::with('pengajuan')->findOrFail($id);
        $jadwal->update(['status' => 'tidak_hadir']);
        \App\Models\CatatanKonseling::updateOrCreate(['jadwal_id'=>$jadwal->id], ['konselor_id'=>$jadwal->konselor_id,'kehadiran'=>'tidak_hadir','dicatat_at'=>now()]);
        if ($jadwal->pengajuan && $jadwal->pengajuan->status !== 'dibatalkan') {
            $old = $jadwal->pengajuan->status;
            $jadwal->pengajuan->update(['status' => 'dibatalkan']);
            $jadwal->pengajuan->riwayatStatus()->create(['status_lama' => $old, 'status_baru' => 'dibatalkan', 'diubah_oleh' => auth()->id(), 'keterangan' => 'Konseli tidak hadir; jadwal dibatalkan.']);
        }
        if ($jadwal->pengajuan?->email) {
            try {
                InteractiveMail::send($jadwal->pengajuan->email, 'Informasi Sesi Konseling', [
                    'greeting' => 'Halo, '.($jadwal->pengajuan->konseli?->nama ?: 'Konseli'),
                    'message' => 'Sesi konseling pada jadwal tersebut tercatat tidak dihadiri sehingga statusnya dibatalkan. Silakan hubungi Admin apabila ingin mendapatkan arahan berikutnya.',
                    'status' => 'Sesi dibatalkan',
                    'details' => ['Kode pengajuan' => $jadwal->pengajuan->kode_pengajuan],
                    'actionUrl' => route('cek-status.hasil', ['kode' => $jadwal->pengajuan->kode_pengajuan, 'email' => $jadwal->pengajuan->email]),
                    'actionLabel' => 'Lihat Status Pengajuan',
                ]);
            } catch (\Throwable $e) { report($e); }
        }
        $this->audit('Tandai Tidak Hadir', 'Menandai konseli tidak hadir pada jadwal #'.$id.'.');
        return response()->json(['message' => 'Konseli ditandai tidak hadir.']);
    }

    public function simpanJadwal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pengajuan_id' => ['required', 'exists:pengajuan,id'], 'konselor_id' => ['required', 'exists:users,id'],
            'tanggal' => ['required', 'date'], 'mulai' => ['required', 'date_format:H:i'], 'selesai' => ['required', 'date_format:H:i', 'after:mulai'],
            'metode' => ['nullable', 'string', 'max:40'], 'lokasi' => ['nullable', 'string', 'max:150'], 'catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $pengajuan = Pengajuan::findOrFail($data['pengajuan_id']);
        $konselor = \App\Models\User::where('id', $data['konselor_id'])->where('role', 'konselor')->where('is_active', true)->firstOrFail();
        $jadwalLama = JadwalKonseling::where('pengajuan_id', $pengajuan->id)->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])->first();
        $bentrok = JadwalKonseling::where('konselor_id', $konselor->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->when($jadwalLama, fn ($q) => $q->where('id', '!=', $jadwalLama->id))
            ->where('mulai', '<', $data['selesai'])
            ->where('selesai', '>', $data['mulai'])
            ->exists();
        abort_if($bentrok, 422, 'Konselor sudah memiliki jadwal pada waktu tersebut.');
        $jadwal = DB::transaction(function () use ($data, $pengajuan, $konselor, $request) {
            $penugasan = PenugasanKonseling::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id, 'konselor_id' => $konselor->id],
                ['ditugaskan_oleh' => $request->user()->id, 'status' => 'aktif']
            );
            $jadwal = $jadwalLama = JadwalKonseling::where('pengajuan_id', $pengajuan->id)
                ->whereNotIn('status', ['selesai', 'dibatalkan', 'tidak_hadir'])->first();
            if ($jadwalLama) {
                $jadwalLama->update([...$data, 'penugasan_id' => $penugasan->id, 'status' => 'terjadwal']);
                $jadwal = $jadwalLama->fresh();
            } else {
                $jadwal = JadwalKonseling::create([...$data, 'penugasan_id' => $penugasan->id, 'status' => 'terjadwal']);
            }
            // Slot yang dipakai jadwal dihapus dari ketersediaan persisten.
            KetersediaanKonselor::where('konselor_id', $konselor->id)
                ->whereDate('tanggal', $data['tanggal'])
                ->where('status', 'tersedia')
                ->where('mulai', '<', $data['selesai'])
                ->where('selesai', '>', $data['mulai'])
                ->delete();
            $old = $pengajuan->status;
            $pengajuan->update(['status' => 'terjadwal']);
            if ($old !== 'terjadwal') {
                $pengajuan->riwayatStatus()->create(['status_lama' => $old, 'status_baru' => 'terjadwal', 'diubah_oleh' => $request->user()->id, 'keterangan' => 'Konselor dan jadwal konseling ditetapkan oleh Admin.']);
            }
            return $jadwal;
        });
        $isReschedule = (bool) $jadwalLama;
        $this->audit($isReschedule ? 'Ubah Jadwal Konseling' : 'Buat Jadwal Konseling', ($isReschedule ? 'Mengubah' : 'Menetapkan').' jadwal untuk pengajuan '.$pengajuan->kode_pengajuan.' pada '.$data['tanggal'].' '.$data['mulai'].'-'.$data['selesai'].' bersama '.$konselor->name.'.');
        app(TelegramNotifier::class)->sendTo($konselor,
            ($isReschedule ? "🔄 Reschedule jadwal konseling SILAKON\n\n" : "📅 Jadwal konseling SILAKON\n\n").
            "Kode: {$pengajuan->kode_pengajuan}\n".
            "Konseli: ".$this->maskNama($pengajuan->konseli?->nama ?: 'Konseli')."\n".
            "Tanggal: ".Carbon::parse($data['tanggal'])->translatedFormat('d F Y')."\n".
            "Waktu: {$data['mulai']}–{$data['selesai']}\n".
            "Lokasi: ".($data['lokasi'] ?: $data['metode'] ?: '-')."\n\n".
            "Jadwal dapat berubah sesuai pembaruan Admin."
        );
        if ($pengajuan->email) {
            try {
                InteractiveMail::send($pengajuan->email, $isReschedule ? 'Perubahan Jadwal Konseling Anda' : 'Jadwal Konseling Anda Telah Disiapkan', [
                    'greeting' => 'Halo, '.($pengajuan->konseli?->nama ?: 'Konseli'),
                    'message' => $isReschedule ? 'Jadwal konseling Anda telah diperbarui oleh Admin. Mohon gunakan waktu dan lokasi terbaru di bawah ini.' : 'Waktu untuk berbicara telah disiapkan. Datanglah dengan tenang—tidak perlu menyiapkan kata-kata yang sempurna. Cukup hadir dan ceritakan apa yang Anda rasakan.',
                    'status' => 'Terjadwal',
                    'details' => ['Kode pengajuan' => $pengajuan->kode_pengajuan, 'Tanggal' => Carbon::parse($data['tanggal'])->translatedFormat('d F Y'), 'Waktu' => $data['mulai'].'–'.$data['selesai'].' WIB', 'Lokasi' => ($data['lokasi'] ?: $data['metode'] ?: '-')],
                    'actionUrl' => route('cek-status.hasil', ['kode' => $pengajuan->kode_pengajuan, 'email' => $pengajuan->email]),
                    'actionLabel' => 'Lihat Detail Jadwal',
                    'quote' => 'Anda tidak perlu membawa kata-kata yang sempurna. Hadir apa adanya sudah cukup.',
                ]);
            } catch (\Throwable $e) { report($e); }
        }
        return response()->json(['message' => 'Jadwal konseling berhasil disimpan.', 'id' => $jadwal->id]);
    }

    private function waktuSudahLewat(string $tanggal, string $mulai): bool
    {
        return \Carbon\Carbon::parse($tanggal . ' ' . $mulai, config('app.timezone'))->isPast();
    }

    private function maskNama(string $nama): string
    {
        return collect(preg_split('/\s+/', trim($nama)))->filter()->map(function ($kata) {
            $kata = (string) $kata;
            return mb_strtoupper(mb_substr($kata, 0, 1)).str_repeat('*', max(3, min(6, mb_strlen($kata) - 1)));
        })->implode(' ') ?: 'K***';
    }

    /**
     * Daftar nama konselor untuk dropdown (modal jadwal & modal ketersediaan).
     * Nanti diganti mis. Konselor::orderBy('nama')->pluck('nama')->all().
     */
    private function daftarKonselor(): array
    {
        return [
            'Dr. Sari Dewi',
            'Dr. Rina Kusumawati',
            'Dr. Hendra Pratama',
            'Drs. Agus Santoso',
            'Dr. Bayu Nugroho',
            'Dr. Maya Indah',
        ];
    }

    /**
     * Ketersediaan konselor (dummy). Tanggal dibuat relatif dari hari ini
     * supaya selalu tampil sebagai jadwal mendatang.
     * Nanti diganti query Eloquent lalu di-map ke bentuk di bawah.
     *
     * 'tanggal' : Y-m-d    'mulai' / 'selesai' : HH:MM (24 jam)
     */
    private function dummyKetersediaanKonselor(): array
    {
        $hariKerja = fn (int $n) => now()->addWeekdays($n)->format('Y-m-d');

        return [
            ['id' => 1, 'konselor' => 'Dr. Sari Dewi',       'tanggal' => $hariKerja(1), 'mulai' => '09:00', 'selesai' => '12:00', 'catatan' => 'Tatap muka & online'],
            ['id' => 2, 'konselor' => 'Dr. Sari Dewi',       'tanggal' => $hariKerja(3), 'mulai' => '13:00', 'selesai' => '16:00', 'catatan' => null],
            ['id' => 3, 'konselor' => 'Dr. Rina Kusumawati', 'tanggal' => $hariKerja(2), 'mulai' => '10:00', 'selesai' => '14:00', 'catatan' => 'Hanya online'],
            ['id' => 4, 'konselor' => 'Dr. Hendra Pratama',  'tanggal' => $hariKerja(2), 'mulai' => '08:00', 'selesai' => '11:00', 'catatan' => null],
            ['id' => 5, 'konselor' => 'Drs. Agus Santoso',   'tanggal' => $hariKerja(4), 'mulai' => '09:00', 'selesai' => '11:00', 'catatan' => 'Ruang C'],
        ];
    }

    /**
     * GET /admin/kalender
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent
     * (mis. Jadwal::with(['konseli', 'konselor'])->whereBetween('tanggal', [...])).
     */
    public function kalender(Request $request)
    {
        $users = \App\Models\User::where('role','konselor')->where('is_active',true)->orderBy('name')->get();
        $konselor = $users->values()->map(fn ($u, $i) => ['id'=>(string)$u->id,'nama'=>$u->name,'tone'=>['hijau','biru','ungu','kuning','merah'][$i % 5]])->all();
        $jadwal = JadwalKonseling::with('pengajuan.konseli','konselor')->when($request->query('bulan'), fn ($q) => $q->whereBetween('tanggal', [$request->query('bulan').'-01', date('Y-m-t', strtotime($request->query('bulan').'-01'))]))->orderBy('tanggal')->orderBy('mulai')->get()->map(fn ($j) => ['id'=>$j->id,'tanggal'=>$j->tanggal?->format('Y-m-d'),'waktu'=>str_replace(':','.',$j->mulai),'konseli'=>$j->pengajuan?->konseli?->nama ?? '-','konselor_id'=>(string)$j->konselor_id,'ruang'=>$j->lokasi ?: ($j->metode === 'daring' ? 'Online' : '-')])->all();
        return view('admin.kalender', ['menuAktif'=>'kalender','konselor'=>$konselor,'ruang'=>['Ruang Konseling LPPP Unnes','LAB BK FIPP','Zoom'],'jadwal'=>$jadwal,'bulanAwal'=>$request->query('bulan', now()->format('Y-m')),'tanggalTerpilih'=>$request->query('tanggal', now()->format('Y-m-d'))]);

        // Warna ("tone") tiap konselor dipakai di kalender & panel detail.
        // Pilihan tone: hijau | biru | ungu | kuning | merah
        $konselor = [
            ['id' => 'sari',   'nama' => 'Dr. Sari Dewi',       'tone' => 'hijau'],
            ['id' => 'rina',   'nama' => 'Dr. Rina Kusumawati', 'tone' => 'biru'],
            ['id' => 'hendra', 'nama' => 'Dr. Hendra Pratama',  'tone' => 'ungu'],
            ['id' => 'agus',   'nama' => 'Drs. Agus Santoso',   'tone' => 'kuning'],
            ['id' => 'bayu',   'nama' => 'Dr. Bayu Nugroho',    'tone' => 'merah'],
        ];

        $ruang = ['Ruang Konseling LPPP Unnes', 'LAB BK FIPP', 'Zoom'];

        // 'tanggal' format ISO (Y-m-d), 'waktu' format 'HH.MM'.
        // 'ruang' = 'Online' untuk sesi daring.
        $jadwal = [
            ['id' => 1,  'tanggal' => '2026-09-03', 'waktu' => '09.00', 'konseli' => 'Andi Setiawan',   'konselor_id' => 'sari',   'ruang' => 'Ruang B'],
            ['id' => 2,  'tanggal' => '2026-09-03', 'waktu' => '11.00', 'konseli' => 'Budi Laksono',    'konselor_id' => 'rina',   'ruang' => 'Ruang C'],
            ['id' => 3,  'tanggal' => '2026-09-05', 'waktu' => '10.00', 'konseli' => 'Citra Dewi',      'konselor_id' => 'hendra', 'ruang' => 'Ruang A'],
            ['id' => 4,  'tanggal' => '2026-09-08', 'waktu' => '09.00', 'konseli' => 'Dewi Rahmawati',  'konselor_id' => 'agus',   'ruang' => 'Ruang B'],
            ['id' => 5,  'tanggal' => '2026-09-08', 'waktu' => '13.00', 'konseli' => 'Eko Prasetyo',    'konselor_id' => 'sari',   'ruang' => 'Online'],
            ['id' => 6,  'tanggal' => '2026-09-10', 'waktu' => '10.00', 'konseli' => 'Fani Susanti',    'konselor_id' => 'rina',   'ruang' => 'Ruang C'],
            // Dua sesi ini sengaja bentrok (Ruang A, 12 Sep, 09.00) untuk contoh banner konflik.
            ['id' => 7,  'tanggal' => '2026-09-12', 'waktu' => '09.00', 'konseli' => 'Gilang Ramadhan', 'konselor_id' => 'bayu',   'ruang' => 'Ruang A'],
            ['id' => 8,  'tanggal' => '2026-09-12', 'waktu' => '09.00', 'konseli' => 'Hana Pertiwi',    'konselor_id' => 'hendra', 'ruang' => 'Ruang A'],
            ['id' => 9,  'tanggal' => '2026-09-15', 'waktu' => '11.00', 'konseli' => 'Ivan Setiawan',   'konselor_id' => 'sari',   'ruang' => 'Ruang B'],
            ['id' => 10, 'tanggal' => '2026-09-17', 'waktu' => '13.00', 'konseli' => 'Julia Maulida',   'konselor_id' => 'agus',   'ruang' => 'Online'],
            ['id' => 11, 'tanggal' => '2026-09-19', 'waktu' => '09.00', 'konseli' => 'Kukuh Wibowo',    'konselor_id' => 'rina',   'ruang' => 'Ruang C'],
            ['id' => 12, 'tanggal' => '2026-09-22', 'waktu' => '10.00', 'konseli' => 'Laras Ayu',       'konselor_id' => 'hendra', 'ruang' => 'Ruang A'],
        ];

        return view('admin.kalender', [
            'menuAktif'       => 'kalender',
            'konselor'        => $konselor,
            'ruang'           => $ruang,
            'jadwal'          => $jadwal,
            // Bulan yang tampil pertama kali (format Y-m) dan tanggal yang langsung terpilih.
            // Karena data dummy hanya ada di September 2026, nilainya dikunci di sini.
            'bulanAwal'       => $request->query('bulan', '2026-09'),
            'tanggalTerpilih' => $request->query('tanggal', '2026-09-17'),
        ]);
    }

    /**
     * GET /admin/kasus-mendesak
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent.
     * 'status': 'mendesak' (belum ditangani) | 'ditangani'.
     * 'whatsapp': nomor format internasional tanpa '+' (dipakai untuk https://wa.me/{nomor}).
     * 'waktu': nanti bisa diganti $tiket->created_at->diffForHumans().
     */
    public function kasusMendesak()
    {
        $kasus = Pengajuan::with('konseli')->get()->filter(fn ($p) => in_array(data_get($p->data, 'kondisi_darurat'), ['ya','mendesak','darurat'], true))->map(function ($p) {
            $d = $p->data ?: []; $ditangani = data_get($d, 'darurat_status') === 'ditangani';
            $telepon = preg_replace('/\D+/', '', (string) ($p->konseli?->telepon ?? ''));
         $kategori = ucfirst((string) ($p->prioritas ?: data_get($d, 'prioritas', 'Belum dihitung')));
        return ['kode'=>$p->kode_pengajuan,'nama'=>$p->konseli?->nama ?? '-','peran'=>ucfirst($p->konseli?->jenis_konseli ?? 'Konseli'),'kategori'=>(string) $kategori,'waktu'=>$p->created_at?->diffForHumans(),'whatsapp'=>str_starts_with($telepon,'0') ? '62'.substr($telepon,1) : $telepon,'status'=>$ditangani?'ditangani':'mendesak','hasil'=>data_get($d,'darurat_hasil'),'catatan'=>data_get($d,'darurat_catatan')];
        })->values()->all();
        return view('admin.kasus-mendesak', ['menuAktif'=>'kasus-mendesak','kasus'=>$kasus]);

        $kasus = [
            [
                'kode'      => 'LP3-2026-000089',
                'nama'      => 'Rani Kusuma',
                'peran'     => 'Mahasiswa',
                'kategori'  => 'Kesehatan Mental',
                'waktu'     => '15 menit lalu',
                'whatsapp'  => '6281200000089',
                'status'    => 'mendesak',
            ],
            [
                'kode'      => 'LP3-2026-000076',
                'nama'      => 'Doni Prasetyo',
                'peran'     => 'Mahasiswa',
                'kategori'  => 'Pribadi',
                'waktu'     => '2 jam lalu',
                'whatsapp'  => '6281200000076',
                'status'    => 'mendesak',
            ],
            [
                'kode'      => 'LP3-2026-000061',
                'nama'      => 'Sari Wulandari',
                'peran'     => 'Alumni',
                'kategori'  => 'Kesehatan Mental',
                'waktu'     => 'Kemarin',
                'whatsapp'  => '6281200000061',
                'status'    => 'ditangani',
            ],
        ];

        return view('admin.kasus-mendesak', [
            'menuAktif' => 'kasus-mendesak',
            'kasus'     => $kasus,
        ]);
    }

    public function tandaiKasusMendesak(Request $request, string $kode)
    {
        $data = $request->validate(['hasil'=>'required|string|max:50','catatan'=>'nullable|string|max:2000']);
        $p = Pengajuan::where('kode_pengajuan',$kode)->firstOrFail(); $json = $p->data ?: [];
        $json['darurat_status'] = 'ditangani'; $json['darurat_hasil'] = $data['hasil']; $json['darurat_catatan'] = $data['catatan'] ?? null; $json['darurat_ditangani_at'] = now()->toIso8601String();
        $p->update(['data'=>$json]);
        app(TelegramNotifier::class)->roles(['admin', 'koordinator'],
            "🚨 Pengajuan mendesak SILAKON\n\n".
            "Kode: {$p->kode_pengajuan}\n".
            "Konseli: ".($p->konseli?->nama ?: 'Konseli')."\n".
            "Kategori: ".(data_get($json, 'kategori_masalah') ?: '—')."\n\n".
            "Silakan segera periksa pengajuan pada dashboard."
        );
        $this->audit('Tangani Kasus Mendesak', 'Menandai pengajuan '.$kode.' sebagai sudah ditangani.');
        return response()->json(['message'=>'Kasus mendesak ditandai sudah ditangani.']);
    }

    /**
     * GET /admin/semua-tiket
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent.
     * 'status_tone': hijau | kuning | biru | abu
     * 'catatan': 'sudah' | 'belum'
     * 'survei' : 'sudah' | 'belum' | null (null = belum relevan, tampil "-")
     * 'konselor': null jika belum ada konselor yang ditugaskan.
     */
    public function semuaTiket()
    {
        $rows = Pengajuan::with(['konseli','jadwal.konselor','jadwal.catatanKonseling','jadwal.survei','penugasan.konselor'])->latest()->get();
        $tiket = $rows->map(function ($p) {
            // Urutkan sesi berdasarkan tanggal, jam mulai, lalu id agar hasil
            // yang ditampilkan selalu berasal dari sesi paling terakhir.
            $jadwal = $p->jadwal->sortByDesc(function ($s) {
                $tanggal = optional($s->tanggal)->format('Y-m-d') ?: '0000-00-00';
                return $tanggal . ' ' . ((string) $s->mulai) . ' ' . str_pad((string) $s->id, 12, '0', STR_PAD_LEFT);
            });
            $j = $jadwal->first(fn ($s) => ! in_array($s->status, ['dibatalkan', 'tidak_hadir'])) ?: $jadwal->first();
            $sesiTerakhir = $jadwal->first();
            $cat = $sesiTerakhir?->catatanKonseling;
            $survey = $j?->survei;
            $data = (array) ($p->data ?? []);
            $jenis = $data['jenis_konseli'] ?? $p->konseli?->jenis_konseli;
            // Identitas yang ditampilkan adalah snapshot saat tiket dibuat,
            // bukan nama profil terbaru yang dapat berubah pada pendaftaran berikutnya.
            $nama = match ($jenis) {
                'mahasiswa' => data_get($data, 'mahasiswa_nama'),
                'alumni' => data_get($data, 'alumni_nama'),
                'tenaga_kependidikan' => data_get($data, 'tk_nama'),
                'wali_mahasiswa' => data_get($data, 'wali_subjek_konseling') === 'mahasiswa'
                    ? data_get($data, 'wali_mahasiswa_nama')
                    : data_get($data, 'wali_nama'),
                default => null,
            } ?: $p->konseli?->nama ?: '-';
            $hasil = $cat?->hasil ?: $cat?->tindak_lanjut;
            $hasil = $hasil ? str_replace('Tuntas — Tidak Perlu Tindak Lanjut', 'Tuntas', $hasil) : null;
            $konselor = $j?->konselor?->name ?: $p->penugasan->sortByDesc('created_at')->first()?->konselor?->name;
            return ['kode'=>$p->kode_pengajuan,'konseli'=>$nama,'konselor'=>$konselor,'tanggal_pengajuan'=>$p->created_at?->translatedFormat('d M Y') ?? '-','tanggal_iso'=>$p->created_at?->format('Y-m-d'),'status'=>str_replace('_',' ',ucfirst($p->status)),'status_tone'=>in_array($p->status,['selesai'])?'hijau':(in_array($p->status,['ditolak','dibatalkan'])?'abu':'kuning'),'catatan'=>$cat?'sudah':'belum','hasil'=>$hasil ?: null,'survei'=>$survey?->status==='terisi'?'sudah':($survey?'belum':null)];
        });
        $menungguTindakan = $tiket->filter(fn ($t) => in_array(strtolower($t['status']), [
            'menunggu pemeriksaan',
            'diterima',
            'terjadwal',
        ], true))->count();
        $statistik = [['label'=>'Total Tiket','nilai'=>$tiket->count(),'tone'=>'hijau'],['label'=>'Menunggu Tindakan','nilai'=>$menungguTindakan,'tone'=>'kuning'],['label'=>'Selesai','nilai'=>$rows->where('status','selesai')->count(),'tone'=>'terang'],['label'=>'Dibatalkan','nilai'=>$rows->whereIn('status',['dibatalkan','ditolak'])->count(),'tone'=>'merah']];
        return view('admin.semua-tiket', ['menuAktif'=>'semua-tiket','statistik'=>$statistik,'tiket'=>$tiket->values(),'opsiStatus'=>$tiket->pluck('status')->unique()->values(),'opsiKonselor'=>$tiket->pluck('konselor')->filter()->unique()->values(),'totalData'=>$tiket->count(),'halamanAktif'=>1,'totalHalaman'=>1]);

        $tiket = collect([
            ['kode' => 'TKT-001', 'konseli' => 'Andi Setiawan',   'konselor' => 'Dr. Sari Dewi',       'kategori' => 'Kesehatan Mental', 'tingkat' => 'S1', 'status' => 'Selesai',                     'status_tone' => 'hijau',  'catatan' => 'sudah', 'survei' => 'sudah'],
            ['kode' => 'TKT-002', 'konseli' => 'Dewi Rahmawati',  'konselor' => 'Dr. Rina Kusumawati', 'kategori' => 'Akademik',         'tingkat' => 'S1', 'status' => 'Menunggu Survei',            'status_tone' => 'kuning', 'catatan' => 'sudah', 'survei' => 'belum'],
            ['kode' => 'TKT-003', 'konseli' => 'Budi Santoso',    'konselor' => 'Dr. Hendra Pratama',  'kategori' => 'Karier',           'tingkat' => 'S2', 'status' => 'Terjadwal',           'status_tone' => 'biru',   'catatan' => 'belum', 'survei' => null],
            ['kode' => 'TKT-004', 'konseli' => 'Siti Nurhaliza',  'konselor' => 'Drs. Agus Santoso',   'kategori' => 'Pribadi',          'tingkat' => 'S1', 'status' => 'Jadwal Disepakati',          'status_tone' => 'biru',   'catatan' => 'belum', 'survei' => null],
            ['kode' => 'TKT-005', 'konseli' => 'Rizky Pratama',   'konselor' => 'Dr. Sari Dewi',       'kategori' => 'Sosial',           'tingkat' => 'D3', 'status' => 'Diterima',       'status_tone' => 'kuning', 'catatan' => 'belum', 'survei' => null],
            ['kode' => 'TKT-006', 'konseli' => 'Mega Lestari',    'konselor' => null,                  'kategori' => 'Asmara',           'tingkat' => 'S1', 'status' => 'Diterima', 'status_tone' => 'kuning', 'catatan' => 'belum', 'survei' => null],
            ['kode' => 'TKT-007', 'konseli' => 'Fajar Nugroho',   'konselor' => 'Dr. Maya Indah',      'kategori' => 'Keluarga',         'tingkat' => 'S1', 'status' => 'Pendaftaran Diterima',       'status_tone' => 'hijau',  'catatan' => 'belum', 'survei' => null],
            ['kode' => 'TKT-008', 'konseli' => 'Anisa Putri',     'konselor' => 'Dr. Rina Kusumawati', 'kategori' => 'Akademik',         'tingkat' => 'S1', 'status' => 'Dibatalkan',                 'status_tone' => 'abu',    'catatan' => 'belum', 'survei' => null],
        ]);

        $statistik = [
            ['label' => 'Total Tiket',       'nilai' => $tiket->count(),                                                        'tone' => 'hijau'],
            ['label' => 'Menunggu Tindakan', 'nilai' => $tiket->filter(fn ($t) => str_starts_with($t['status'], 'Menunggu'))->count(), 'tone' => 'kuning'],
            ['label' => 'Selesai',           'nilai' => $tiket->where('status', 'Selesai')->count(),                            'tone' => 'terang'],
            ['label' => 'Dibatalkan',        'nilai' => $tiket->where('status', 'Dibatalkan')->count(),                         'tone' => 'merah'],
        ];

        return view('admin.semua-tiket', [
            'menuAktif'     => 'semua-tiket',
            'statistik'     => $statistik,
            'tiket'         => $tiket->values(),
            'opsiStatus'    => $tiket->pluck('status')->unique()->values(),
            'opsiKonselor'  => $tiket->pluck('konselor')->filter()->unique()->values(),
            'opsiKategori'  => $tiket->pluck('kategori')->unique()->sort()->values(),
            'totalData'     => $tiket->count(),
            'halamanAktif'  => 1,
            'totalHalaman'  => 1,
        ]);
    }

    /**
     * GET /admin/data-konseli
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent
     * (mis. Konseli::withCount(['tiket', 'tiketAktif'])->latest('terakhir_daftar')->paginate()).
     *
     * 'status_key'  : 'terverifikasi' | 'belum_terverifikasi' | 'perlu_dikonfirmasi'
     *                 dipakai untuk tab "Perlu Dikonfirmasi" (filter di browser).
     * 'status_tone' : hijau | kuning | merah — mengikuti status_key di atas.
     * 'jenis'       : peran konseli, mis. Mahasiswa | Dosen | Alumni.
     * 'tiket_aktif' : jumlah tiket yang masih berjalan (0 = tampil "-").
     */
    public function dataKonseli()
    {
        $rows = Konseli::with(['pengajuan' => fn ($q) => $q->latest()])->withCount(['pengajuan','pengajuan as tiket_aktif_count' => fn ($q) => $q->aktif()])->latest('terakhir_mendaftar_at')->get();
        $konseli = $rows->map(function ($k) {
            $terakhir = $k->pengajuan->first();
            $detail = ['Identitas Konseli'=>['Nama Lengkap'=>$k->nama,'Jenis Konseli'=>ucfirst(str_replace('_',' ',$k->jenis_konseli)),'Email'=>$k->email,'NIM'=>$k->nim ?: '-', 'Nomor Telepon'=>$k->telepon ?: '-']];
            foreach ($k->pengajuan as $pengajuan) {
                $detail['Pengajuan ' . ($pengajuan->kode_pengajuan ?: $pengajuan->id)] = [
                    'Status' => str_replace('_',' ',ucfirst($pengajuan->status ?? '-')),
                    'Tanggal Pengajuan' => $pengajuan->created_at?->format('d M Y, H:i') ?? '-',
                    ...collect($pengajuan->data ?? [])->mapWithKeys(fn ($v,$key) => [ucwords(str_replace('_',' ', $key)) => is_array($v) ? implode(', ', $v) : ($v === null || $v === '' ? '-' : (string)$v)])->all(),
                ];
            }
            return ['id'=>$k->id,'nama'=>$k->nama,'jenis'=>ucfirst(str_replace('_',' ',$k->jenis_konseli)),'email'=>$k->email,'total_tiket'=>$k->pengajuan_count,'tiket_aktif'=>$k->tiket_aktif_count,'terakhir_daftar'=>$k->terakhir_mendaftar_at?->format('d M Y'),
                'detail'=>$detail,
                'edit'=>['id'=>$k->id,'nama'=>$k->nama,'email'=>$k->email,'nim'=>$k->nim,'telepon'=>$k->telepon,'data'=>$k->data ?? []]];
        });
        return view('admin.data-konseli', ['menuAktif'=>'data-konseli','konseli'=>$konseli->values(),'duplikatProfil'=>collect(),'totalPerluKonfirmasi'=>0,'totalData'=>$konseli->count(),'halamanAktif'=>1,'totalHalaman'=>1]);

        $konseli = collect([
            ['nama' => 'Andi Setiawan',        'jenis' => 'Mahasiswa', 'email' => 'andi.s@students.unnes.ac.id',  'total_tiket' => 3, 'tiket_aktif' => 1, 'terakhir_daftar' => '12 Agu 2025', 'status' => 'Terverifikasi',        'status_key' => 'terverifikasi',        'status_tone' => 'hijau'],
            ['nama' => 'Dewi Rahmawati',       'jenis' => 'Mahasiswa', 'email' => 'dewi.r@students.unnes.ac.id',  'total_tiket' => 2, 'tiket_aktif' => 1, 'terakhir_daftar' => '10 Agu 2025', 'status' => 'Terverifikasi',        'status_key' => 'terverifikasi',        'status_tone' => 'hijau'],
            ['nama' => 'Budi Santoso',         'jenis' => 'Dosen',     'email' => 'budi.s@mail.unnes.ac.id',      'total_tiket' => 1, 'tiket_aktif' => 0, 'terakhir_daftar' => '5 Agu 2025',  'status' => 'Belum Terverifikasi',  'status_key' => 'belum_terverifikasi',  'status_tone' => 'kuning'],
            ['nama' => 'Siti Nurhaliza',       'jenis' => 'Mahasiswa', 'email' => 'siti.n@students.unnes.ac.id',  'total_tiket' => 1, 'tiket_aktif' => 1, 'terakhir_daftar' => '8 Agu 2025',  'status' => 'Terverifikasi',        'status_key' => 'terverifikasi',        'status_tone' => 'hijau'],
            ['nama' => 'Andi Setiawan (Baru)', 'jenis' => 'Mahasiswa', 'email' => 'andis@gmail.com',              'total_tiket' => 1, 'tiket_aktif' => 0, 'terakhir_daftar' => '14 Agu 2025', 'status' => 'Perlu Dikonfirmasi',   'status_key' => 'perlu_dikonfirmasi',   'status_tone' => 'merah'],
            ['nama' => 'Rizky Pratama',        'jenis' => 'Mahasiswa', 'email' => 'rizky.p@students.unnes.ac.id', 'total_tiket' => 2, 'tiket_aktif' => 1, 'terakhir_daftar' => '14 Agu 2025', 'status' => 'Belum Terverifikasi',  'status_key' => 'belum_terverifikasi',  'status_tone' => 'kuning'],
        ]);

        // Pasangan profil yang terdeteksi sistem sebagai kemungkinan orang yang sama.
        // Nanti bisa diganti hasil deteksi kemiripan nama/email dari query Eloquent.
        $duplikatProfil = collect([
            [
                'utama' => [
                    'nama' => 'Andi Setiawan', 'email' => 'andi.s@students.unnes.ac.id',
                    'jenis' => 'Mahasiswa', 'total_tiket' => 3, 'terakhir_daftar' => '12 Agu 2025',
                ],
                'kandidat' => [
                    'nama' => 'Andi Setiawan (Baru)', 'email' => 'andis@gmail.com',
                    'jenis' => 'Mahasiswa', 'total_tiket' => 1, 'terakhir_daftar' => '14 Agu 2025',
                ],
            ],
        ]);

        return view('admin.data-konseli', [
            'menuAktif'             => 'data-konseli',
            'konseli'               => $konseli->values(),
            'duplikatProfil'        => $duplikatProfil->values(),
            'totalPerluKonfirmasi'  => $duplikatProfil->count(),
            'totalData'             => $konseli->count(),
            'halamanAktif'          => 1,
            'totalHalaman'          => 1,
        ]);
    }

    public function perbaruiKonseli(Request $request, int $id)
    {
        $konseli = Konseli::findOrFail($id);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'nim' => ['nullable', 'string', 'max:50'],
            'telepon' => ['nullable', 'string', 'max:40'],
            'data_json' => ['nullable', 'string'],
        ]);

        $data = (array) ($konseli->data ?? []);
        if (! empty($validated['data_json'])) {
            $tambahan = json_decode($validated['data_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($tambahan)) {
                return back()->withErrors(['data_json' => 'Data tambahan tidak valid.'])->withInput();
            }
            // Metadata riwayat hanya dikelola sistem dan tidak boleh dihapus
            // ketika Admin menyunting data profil.
            unset($tambahan['_riwayat_pengajuan']);
            $data = array_merge($data, $tambahan);
        }

        $konseli->update([
            'nama' => trim($validated['nama']),
            'email' => strtolower(trim($validated['email'])),
            'nim' => $validated['nim'] !== null ? trim($validated['nim']) : null,
            'telepon' => $validated['telepon'] !== null ? trim($validated['telepon']) : null,
            'data' => $data,
        ]);

        $this->audit('Perbarui data konseli', 'Memperbarui profil ' . $konseli->nama);
        return redirect()->route('admin.data-konseli')->with('sukses', 'Data konseli berhasil diperbarui.');
    }

    /**
     * GET /admin/konseling-kelompok
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent
     * (mis. ProgramKelompok::withCount('peserta')->latest()->get()).
     *
     * 'status'        : label status pendaftaran program yang tampil di badge.
     * 'status_tone'   : hijau | kuning | abu — mengikuti status di atas.
     * 'progress_tone' : hijau (kuota masih longgar) | merah (kuota hampir/sudah penuh, >=85%).
     */
    public function konselingKelompok()
    {
        $program = ProgramKonselingKelompok::withCount('peserta')->latest()->get()->map(fn ($p) => $this->mapProgramDb($p));
        return view('admin.konseling-kelompok', ['menuAktif' => 'konseling-kelompok', 'program' => $program, 'totalAktif' => $program->where('status', '!=', 'Selesai')->count()]);
        $program = $this->daftarProgramKelompok();

        return view('admin.konseling-kelompok', [
            'menuAktif'  => 'konseling-kelompok',
            'program'    => $program->values(),
            'totalAktif' => $program->count(),
        ]);
    }

    /**
     * POST /admin/konseling-kelompok
     * Menyimpan program baru dari form modal "Buat Program".
     * Karena database masih dummy, data disimpan sementara di session
     * (akan hilang saat session di-clear / logout tergantung driver session).
     * Nanti tinggal diganti: ProgramKelompok::create($data); lalu hapus
     * penyimpanan session di bawah ini.
     */
    public function simpanKonselingKelompok(Request $request)
    {
        $data = $request->validate([
            'judul'       => 'required|string|max:150',
            'tanggal'     => 'required|date',
            'waktu'       => 'required|date_format:H:i',
            'lokasi'      => 'required|string|max:150',
            'fasilitator' => 'required|string|max:150',
            'kuota'       => 'required|integer|min:1',
            'tautan_grup' => 'nullable|url|max:500',
        ]);

        ProgramKonselingKelompok::create(['judul' => $data['judul'], 'tanggal' => $data['tanggal'], 'waktu' => $data['waktu'], 'lokasi' => $data['lokasi'], 'fasilitator' => $data['fasilitator'], 'kuota' => $data['kuota'], 'tautan_grup' => $data['tautan_grup'] ?? null, 'status' => 'Pendaftaran Dibuka', 'tags' => []]);
        $this->audit('Buat Program Kelompok', 'Membuat program konseling kelompok "'.$data['judul'].'".');
        return redirect()->route('admin.konseling-kelompok')->with('sukses', 'Program "'.$data['judul'].'" berhasil dibuat.');

        $tambahan = session('konseling_kelompok_tambahan', []);

        $tambahan[] = [
            'id'          => 'baru-' . (count($tambahan) + 1),
            'judul'       => $data['judul'],
            'status'      => 'Pendaftaran Dibuka',
            'status_tone' => 'hijau',
            'tanggal'     => $data['tanggal'],
            'waktu'       => $data['waktu'],
            'lokasi'      => $data['lokasi'],
            'fasilitator' => $data['fasilitator'],
            'kuota'       => (int) $data['kuota'],
            'terdaftar'   => 0,
        ];

        session(['konseling_kelompok_tambahan' => $tambahan]);

        return redirect()
            ->route('admin.konseling-kelompok')
            ->with('sukses', 'Program "' . $data['judul'] . '" berhasil dibuat.');
    }

    public function ubahKonselingKelompok(Request $request, int $id)
    {
        $data = $request->validate(['judul'=>'required|string|max:150','tanggal'=>'required|date','waktu'=>'required|date_format:H:i','lokasi'=>'required|string|max:150','fasilitator'=>'required|string|max:150','kuota'=>'required|integer|min:1','tautan_grup'=>'nullable|url|max:500','status'=>'required|in:Pendaftaran Dibuka,Draft,Selesai']);
        $p = ProgramKonselingKelompok::findOrFail($id);
        if ($p->status === 'Selesai') return back()->with('gagal', 'Program yang sudah selesai hanya dapat melihat peserta atau dihapus.');
        $p->update($data);
        $this->audit('Ubah Program Kelompok', 'Memperbarui program "'.$p->judul.'".');
        return back()->with('sukses','Program berhasil diperbarui.');
    }

    public function hapusKonselingKelompok(int $id)
    {
        $p = ProgramKonselingKelompok::findOrFail($id); $judul = $p->judul; $p->delete();
        $this->audit('Hapus Program Kelompok', 'Menghapus program "'.$judul.'".');
        return back()->with('sukses','Program berhasil dihapus.');
    }

    /**
     * GET /admin/konseling-kelompok/{id}/peserta
     * Daftar peserta satu program konseling kelompok (tombol "Lihat Peserta").
     * Frontend + dummy data. Nanti diganti mis.
     * $program->peserta()->with('mahasiswa')->latest()->get().
     *
     * 'status'      : Terdaftar | Hadir | Tidak Hadir | Dibatalkan
     * 'status_tone' : hijau | biru | merah | kuning — mengikuti status di atas.
     */
    public function pesertaKonselingKelompok($id)
    {
        $p = ProgramKonselingKelompok::withCount('peserta')->findOrFail($id);
        $program = $this->mapProgramDb($p);
        $peserta = $p->peserta()->latest()->get()->map(fn ($x) => ['id'=>$x->id, 'nama' => $x->nama, 'nim' => $x->nim, 'email'=>$x->email, 'wa'=>$x->wa, 'fakultas' => $x->fakultas, 'prodi' => $x->prodi, 'semester' => $x->semester, 'tgl_daftar' => $x->created_at?->format('d M Y'), 'status' => $x->status, 'status_tone' => 'hijau']);
        return view('admin.konseling-kelompok-peserta', ['menuAktif' => 'konseling-kelompok', 'program' => $program, 'peserta' => $peserta, 'kuota' => $p->kuota, 'terdaftar' => $peserta->count(), 'sisa' => max($p->kuota - $peserta->count(), 0)]);

        $program = $this->daftarProgramKelompok()
            ->first(fn ($p) => (string) $p['id'] === (string) $id);

        abort_if(! $program, 404);

        $peserta = collect($this->dummyPesertaKelompok()[$program['id']] ?? []);

        $terdaftar = $peserta->count();

        return view('admin.konseling-kelompok-peserta', [
            'menuAktif' => 'konseling-kelompok',
            'program'   => $program,
            'peserta'   => $peserta->values(),
            'kuota'     => $program['kuota'],
            'terdaftar' => $terdaftar,
            'sisa'      => max($program['kuota'] - $terdaftar, 0),
        ]);
    }

    /**
     * Daftar program konseling kelompok (dummy + tambahan dari session),
     * lengkap dengan field turunan sisa / pct / progress_tone.
     * Dipakai bersama oleh konselingKelompok() dan pesertaKonselingKelompok().
     */
    private function daftarProgramKelompok()
    {
        // Program yang ditambahkan lewat form "Buat Program" (lihat simpanKonselingKelompok()).
        // Disimpan sementara di session karena database masih dummy — nanti tinggal
        // diganti ProgramKelompok::latest()->get() dan baris ->concat($tambahan) dihapus.
        $tambahan = collect(session('konseling_kelompok_tambahan', []));

        $program = collect([
            [
                'id'          => 1,
                'judul'       => 'Manajemen Stres Akademik',
                'status'      => 'Pendaftaran Dibuka',
                'status_tone' => 'hijau',
                'tanggal'     => '15 Agu 2025',
                'waktu'       => '09.00 – 12.00',
                'lokasi'      => 'Ruang Seminar LPPP',
                'fasilitator' => 'Dr. Sari Dewi, M.Psi',
                'kuota'       => 20,
                'terdaftar'   => 14,
            ],
            [
                'id'          => 2,
                'judul'       => 'Perencanaan Karier Pasca Kampus',
                'status'      => 'Pendaftaran Dibuka',
                'status_tone' => 'hijau',
                'tanggal'     => '22 Agu 2025',
                'waktu'       => '13.00 – 16.00',
                'lokasi'      => 'Aula LPPP',
                'fasilitator' => 'Dr. Rina Kusumawati, M.Pd',
                'kuota'       => 30,
                'terdaftar'   => 27,
            ],
            [
                'id'          => 3,
                'judul'       => 'Kecemasan Sosial & Kepercayaan Diri',
                'status'      => 'Pendaftaran Dibuka',
                'status_tone' => 'hijau',
                'tanggal'     => '5 Sep 2025',
                'waktu'       => '09.00 – 12.00',
                'lokasi'      => 'Ruang Konseling',
                'fasilitator' => 'Drs. Agus Santoso, M.Si',
                'kuota'       => 15,
                'terdaftar'   => 8,
            ],
            [
                'id'          => 4,
                'judul'       => 'Mindfulness & Kesehatan Mental',
                'status'      => 'Pendaftaran Dibuka',
                'status_tone' => 'hijau',
                'tanggal'     => '12 Sep 2025',
                'waktu'       => '15.00 – 17.00',
                'lokasi'      => 'Taman Kampus',
                'fasilitator' => 'Dr. Sari Dewi, M.Psi',
                'kuota'       => 25,
                'terdaftar'   => 3,
            ],
        ])
            ->concat($tambahan)
            ->map(function ($p) {
                $p['sisa']          = $p['kuota'] - $p['terdaftar'];
                $p['pct']           = $p['kuota'] > 0 ? (int) round(($p['terdaftar'] / $p['kuota']) * 100) : 0;
                $p['progress_tone'] = $p['pct'] >= 85 ? 'merah' : 'hijau';

                return $p;
            });

        return $program->values();
    }

    /**
     * Peserta dummy per program, key = id program.
     * Program yang belum punya data (mis. program baru) tampil dengan daftar kosong.
     */
    private function dummyPesertaKelompok(): array
    {
        return [
            1 => [
                ['kode' => 'KK-2026-00001', 'nama' => 'Budi Santoso', 'nim' => '1234567890', 'fakultas' => 'FIP',   'prodi' => 'Bimbingan Konseling', 'semester' => 6, 'tgl_daftar' => '5 Agu 2026', 'status' => 'Terdaftar',   'status_tone' => 'hijau'],
                ['kode' => 'KK-2026-00002', 'nama' => 'Siti Rahayu',   'nim' => '2345678901', 'fakultas' => 'FBS',   'prodi' => 'Sastra Indonesia',    'semester' => 4, 'tgl_daftar' => '5 Agu 2026', 'status' => 'Terdaftar',   'status_tone' => 'hijau'],
                ['kode' => 'KK-2026-00003', 'nama' => 'Andi Wijaya',   'nim' => '3456789012', 'fakultas' => 'FMIPA', 'prodi' => 'Matematika',          'semester' => 8, 'tgl_daftar' => '6 Agu 2026', 'status' => 'Hadir',       'status_tone' => 'biru'],
                ['kode' => 'KK-2026-00004', 'nama' => 'Dewi Pratama',  'nim' => '4567890123', 'fakultas' => 'FE',    'prodi' => 'Manajemen',           'semester' => 2, 'tgl_daftar' => '6 Agu 2026', 'status' => 'Tidak Hadir', 'status_tone' => 'merah'],
                ['kode' => 'KK-2026-00005', 'nama' => 'Rizky Maulana', 'nim' => '5678901234', 'fakultas' => 'FT',    'prodi' => 'Teknik Informatika', 'semester' => 6, 'tgl_daftar' => '7 Agu 2026', 'status' => 'Dibatalkan',  'status_tone' => 'kuning'],
            ],
        ];
    }

    /**
     * GET /admin/self-help
     * Frontend + dummy data. Nanti array tinggal diganti query Eloquent
     * (mis. KontenSelfHelp::latest('tanggal')->get()).
     *
     * 'tipe'   : Artikel | Video | Worksheet  -> tipe_tone: biru | merah | kuning
     * 'status' : Dipublikasi | Draft          -> status_tone: hijau | kuning
     */
    public function selfHelp()
    {
        $konten = SelfHelp::with('author')->where('tipe', 'Modul')->latest()->get()->map(fn ($m) => $this->mapSelfHelpDb($m));
        $statistik = collect(['Modul'])->map(fn ($tipe) => ['tipe' => $tipe, 'nilai' => $konten->where('tipe', $tipe)->count()]);
        return view('admin.self-help', ['menuAktif' => 'self-help', 'konten' => $konten, 'statistik' => $statistik, 'totalData' => $konten->count(), 'opsiTipe' => ['Modul'], 'opsiTopik' => ['Kesehatan Mental','Akademik','Karier','Pribadi','Sosial']]);
        $konten = $this->daftarSelfHelp();

        // Ringkasan per tipe (kartu di atas tabel), dihitung dari data konten.
        $statistik = collect(['Artikel', 'Video', 'Worksheet'])->map(fn ($tipe) => [
            'tipe'  => $tipe,
            'nilai' => $konten->where('tipe', $tipe)->count(),
        ]);

        return view('admin.self-help', [
            'menuAktif'  => 'self-help',
            'konten'     => $konten,
            'statistik'  => $statistik,
            'totalData'  => $konten->count(),
            'opsiTipe'   => ['Artikel', 'Video', 'Worksheet', 'Modul'],
            'opsiTopik'  => ['Kesehatan Mental', 'Akademik', 'Karier', 'Pribadi', 'Sosial'],
        ]);
    }

    /**
     * POST /admin/self-help
     * Menyimpan konten baru dari modal "Tambah Konten". Isian berbeda per tipe:
     *  - Artikel   : judul, isi (teks panjang), thumbnail (opsional)
     *  - Video     : judul, link YouTube, keterangan
     *  - Worksheet : judul, file PDF, daftar pertanyaan + halaman PDF-nya
     * Tanggal diisi otomatis (hari ini), tidak diinput manual.
     *
     * Database masih dummy: metadata disimpan di session, sedangkan file upload
     * disimpan sungguhan di storage/app/public/self-help/... (jalankan
     * `php artisan storage:link` agar bisa diakses lewat Storage::url()).
     * Nanti diganti KontenSelfHelp::create($data) dan hapus penyimpanan session.
     */
    public function simpanSelfHelp(Request $request)
    {
        $data = $request->validate([
            'judul'                => 'required|string|max:150',
            'tipe'                 => 'required|in:Modul',
            'topik'                => 'required|string|max:80',
            'status'               => 'required|in:Dipublikasi,Draft',

            // Artikel
            'isi'                  => 'required_if:tipe,Artikel|nullable|string',
            'thumbnail'            => 'nullable|image|max:2048',

            // Video
            'link_youtube'         => ['required_if:tipe,Video', 'nullable', 'url', 'regex:/^https?:\/\/(www\.|m\.)?(youtube\.com|youtu\.be)\/.+$/i'],
            'keterangan'           => 'required_if:tipe,Video|nullable|string',

            // Worksheet
            'file_pdf'             => 'required_if:tipe,Worksheet|nullable|file|mimes:pdf|max:10240',
            'file_pdf_blok'        => 'sometimes|array',
            'file_pdf_blok.*'      => 'file|mimes:pdf|max:10240',
            'pertanyaan'           => 'required_if:tipe,Worksheet|nullable|array|min:1',
            'pertanyaan.*.teks'    => 'required|string|max:1000',
            'pertanyaan.*.halaman' => 'required|integer|min:1',
            'jumlah_halaman'       => 'nullable|integer|min:1',
            'blok_json'            => 'required_if:tipe,Modul|nullable|json',
        ], [
            'required'          => ':attribute wajib diisi.',
            'required_if'       => ':attribute wajib diisi.',
            'url'               => ':attribute harus berupa tautan lengkap, diawali https://',
            'regex'             => 'Tautan harus dari YouTube (youtube.com atau youtu.be).',
            'image'             => 'Thumbnail harus berupa gambar (JPG/PNG).',
            'mimes'             => 'File harus berformat PDF.',
            'integer'           => ':attribute harus berupa angka.',
            'min.numeric'       => ':attribute minimal :min.',
            'min.array'         => 'Tambahkan minimal satu pertanyaan.',
            'thumbnail.max'     => 'Ukuran thumbnail maksimal 2 MB.',
            'file_pdf.max'      => 'Ukuran PDF maksimal 10 MB.',
            'thumbnail.uploaded' => 'Upload thumbnail gagal, ukuran file melebihi batas server.',
            'file_pdf.uploaded'  => 'Upload PDF gagal, ukuran file melebihi batas server.',
        ], [
            'judul'                => 'Judul',
            'topik'                => 'Topik',
            'isi'                  => 'Isi artikel',
            'link_youtube'         => 'Link YouTube',
            'keterangan'           => 'Keterangan',
            'file_pdf'             => 'File PDF',
            'pertanyaan'           => 'Pertanyaan',
            'pertanyaan.*.teks'    => 'Pertanyaan',
            'pertanyaan.*.halaman' => 'Halaman',
        ]);

        // Bagian yang khusus per tipe.
        if ($data['tipe'] === 'Artikel') {
            $detail = [
                'isi'       => $data['isi'],
                'thumbnail' => $request->hasFile('thumbnail')
                    ? $request->file('thumbnail')->store('self-help/thumbnail', 'public')
                    : null,
            ];
        } elseif ($data['tipe'] === 'Video') {
            $detail = [
                'link_youtube' => $data['link_youtube'],
                'keterangan'   => $data['keterangan'],
            ];
        } elseif ($data['tipe'] === 'Worksheet') {
            $detail = [
                'file_pdf'   => $request->file('file_pdf')->store('self-help/worksheet', 'public'),
                'pertanyaan' => collect($data['pertanyaan'])
                    ->map(fn ($q) => ['teks' => trim($q['teks']), 'halaman' => (int) $q['halaman']])
                    ->values()
                    ->all(),
                'jumlah_halaman' => $this->hitungHalamanPdf($detail['file_pdf']) ?: ($data['jumlah_halaman'] ?? null),
            ];
        } else {
            $blok = json_decode($data['blok_json'], true);
            if (!is_array($blok) || count($blok) < 1) {
                return back()->withInput()->withErrors(['blok_json' => 'Tambahkan minimal satu blok materi.']);
            }
            $detail = ['blok' => $this->prosesBlokPdfModul($blok, $request)];
        }

        SelfHelp::create(['judul' => $data['judul'], 'tipe' => $data['tipe'], 'topik' => $data['topik'], 'status' => $data['status'], 'author_id' => auth()->id(), 'detail' => $detail, 'thumbnail' => $detail['thumbnail'] ?? null, 'file_pdf' => $detail['file_pdf'] ?? null]);
        $this->audit('Buat Self Help', 'Membuat materi '.$data['tipe'].' "'.$data['judul'].'".');
        return redirect()->route('admin.self-help')->with('sukses', 'Konten "'.$data['judul'].'" berhasil ditambahkan.');

        // Tanggal otomatis = hari ini. Format "24 Sep 2026".
        $bulan   = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sekarang = now();

        $tambahan = session('self_help_tambahan', []);

        $tambahan[] = [
            'id'      => 'baru-' . (count($tambahan) + 1),
            'judul'   => $data['judul'],
            'tipe'    => $data['tipe'],
            'topik'   => $data['topik'],
            'tanggal' => $sekarang->format('j') . ' ' . $bulan[(int) $sekarang->format('n')] . ' ' . $sekarang->format('Y'),
            'status'  => $data['status'],
            'detail'  => $detail,
        ];

        session(['self_help_tambahan' => $tambahan]);

        return redirect()
            ->route('admin.self-help')
            ->with('sukses', 'Konten "' . $data['judul'] . '" berhasil ditambahkan.');
    }

    private function mapProgramDb(ProgramKonselingKelompok $p): array
    {
        $terdaftar = (int) ($p->peserta_count ?? $p->peserta()->count());
        $pct = $p->kuota > 0 ? min((int) round($terdaftar / $p->kuota * 100), 100) : 0;
        $status = $p->status;
        try { if ($p->tanggal && $this->parseTanggal($p->tanggal)?->isPast()) $status = 'Selesai'; } catch (\Throwable $e) { }
        if ($status !== $p->status && $status === 'Selesai') $p->update(['status' => $status]);
        return ['id' => $p->id, 'judul' => $p->judul, 'status' => $status, 'status_tone' => $status === 'Selesai' ? 'abu' : ($pct >= 90 ? 'kuning' : 'hijau'), 'tanggal' => $p->tanggal, 'waktu' => $p->waktu, 'lokasi' => $p->lokasi, 'fasilitator' => $p->fasilitator, 'tautan_grup' => $p->tautan_grup, 'kuota' => $p->kuota, 'terdaftar' => $terdaftar, 'sisa' => max($p->kuota - $terdaftar, 0), 'pct' => $pct, 'progress_tone' => $pct >= 85 ? 'merah' : 'hijau'];
    }

    private function hitungHalamanPdf(?string $path): ?int
    {
        if (!$path || !Storage::disk('public')->exists($path)) return null;
        $contents = @file_get_contents(Storage::disk('public')->path($path));
        if (!$contents) return null;
        $count = preg_match_all('/\/Type\s*\/Page\b/', $contents, $matches);
        return $count > 0 ? $count : null;
    }

    private function parseTanggal(?string $tanggal): ?Carbon
    {
        if (!$tanggal) return null;
        $id = ['Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April','Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August','September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December','Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','Jun'=>'Jun','Jul'=>'Jul','Agu'=>'Aug','Sep'=>'Sep','Okt'=>'Oct','Nov'=>'Nov','Des'=>'Dec'];
        return Carbon::parse(str_ireplace(array_keys($id), array_values($id), $tanggal));
    }

    /**
     * Memperbarui materi self-help yang sudah tersimpan di database.
     * File lama dipertahankan bila tidak ada file pengganti.
     */
    public function updateSelfHelp(Request $request, int $id)
    {
        $data = $request->validate([
            'judul'                => 'required|string|max:150',
            'tipe'                 => 'required|in:Modul',
            'topik'                => 'required|string|max:80',
            'status'               => 'required|in:Dipublikasi,Draft',
            'isi'                  => 'required_if:tipe,Artikel|nullable|string',
            'thumbnail'            => 'nullable|image|max:2048',
            'link_youtube'         => ['required_if:tipe,Video', 'nullable', 'url', 'regex:/^https?:\\/\\/(www\\.|m\\.)?(youtube\\.com|youtu\\.be)\\/.+$/i'],
            'keterangan'           => 'required_if:tipe,Video|nullable|string',
            'file_pdf'             => 'nullable|file|mimes:pdf|max:10240',
            'file_pdf_blok'        => 'sometimes|array',
            'file_pdf_blok.*'      => 'file|mimes:pdf|max:10240',
            'pertanyaan'           => 'required_if:tipe,Worksheet|nullable|array|min:1',
            'pertanyaan.*.teks'    => 'required|string|max:1000',
            'pertanyaan.*.halaman' => 'required|integer|min:1',
            'jumlah_halaman'       => 'nullable|integer|min:1',
            'blok_json'            => 'required_if:tipe,Modul|nullable|json',
        ]);
        if (($data['tipe'] ?? null) === 'Worksheet' && !empty($data['jumlah_halaman'])) {
            foreach (($data['pertanyaan'] ?? []) as $i => $q) {
                if ((int) $q['halaman'] > (int) $data['jumlah_halaman']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "pertanyaan.$i.halaman" => 'Halaman pertanyaan tidak boleh melebihi jumlah halaman PDF (' . $data['jumlah_halaman'] . ').',
                    ]);
                }
            }
        }

        $materi = SelfHelp::findOrFail($id);
        $lama = $materi->detail ?? [];
        $detail = [];

        if ($data['tipe'] === 'Artikel') {
            $detail['isi'] = $data['isi'] ?? null;
            $lamaThumb = $materi->thumbnail ?: ($lama['thumbnail'] ?? null);
            $detail['thumbnail'] = $lamaThumb;
            $this->hapusFileSelfHelp($materi->file_pdf ?: ($lama['file_pdf'] ?? null));
            if ($request->hasFile('thumbnail')) {
                $this->hapusFileSelfHelp($lamaThumb);
                $detail['thumbnail'] = $request->file('thumbnail')->store('self-help/thumbnail', 'public');
            }
        } elseif ($data['tipe'] === 'Video') {
            $detail = ['link_youtube' => $data['link_youtube'] ?? null, 'keterangan' => $data['keterangan'] ?? null];
            $this->hapusFileSelfHelp($materi->thumbnail);
            $this->hapusFileSelfHelp($materi->file_pdf);
        } elseif ($data['tipe'] === 'Worksheet') {
            $lamaPdf = $materi->file_pdf ?: ($lama['file_pdf'] ?? null);
            $detail['file_pdf'] = $lamaPdf;
            if ($request->hasFile('file_pdf')) {
                $this->hapusFileSelfHelp($lamaPdf);
                $detail['file_pdf'] = $request->file('file_pdf')->store('self-help/worksheet', 'public');
            }
            $detail['pertanyaan'] = collect($data['pertanyaan'] ?? ($lama['pertanyaan'] ?? []))
                ->map(fn ($q) => ['teks' => trim($q['teks']), 'halaman' => (int) $q['halaman']])->values()->all();
            $detail['jumlah_halaman'] = $data['jumlah_halaman'] ?? ($lama['jumlah_halaman'] ?? null);
            $this->hapusFileSelfHelp($materi->thumbnail);
        } else {
            $blok = json_decode($data['blok_json'], true);
            if (!is_array($blok) || count($blok) < 1) {
                return back()->withInput()->withErrors(['blok_json' => 'Tambahkan minimal satu blok materi.']);
            }
            $detail = ['blok' => $this->prosesBlokPdfModul($blok, $request, $lama['blok'] ?? [])];
            $this->hapusFileSelfHelp($materi->thumbnail);
        }

        $materi->update([
            'judul' => $data['judul'], 'tipe' => $data['tipe'], 'topik' => $data['topik'],
            'status' => $data['status'], 'detail' => $detail,
            'thumbnail' => $detail['thumbnail'] ?? null, 'file_pdf' => $detail['file_pdf'] ?? null,
        ]);
        $this->audit('Ubah Self Help', 'Mengubah materi self-help "'.$data['judul'].'".');

        return redirect()->route('admin.self-help')->with('sukses', 'Konten "'.$data['judul'].'" berhasil diperbarui.');
    }

    /** Menghapus materi beserta file upload yang terkait. */
    public function hapusSelfHelp(int $id)
    {
        $materi = SelfHelp::findOrFail($id);
        $judul = $materi->judul;
        $this->hapusFileSelfHelp($materi->thumbnail);
        $this->hapusFileSelfHelp($materi->file_pdf ?: data_get($materi->detail, 'file_pdf'));
        $materi->delete();
        $this->audit('Hapus Self Help', 'Menghapus materi self-help "'.$judul.'".');

        return redirect()->route('admin.self-help')->with('sukses', 'Konten self-help berhasil dihapus.');
    }

    private function hapusFileSelfHelp(?string $path): void
    {
        if ($path && !preg_match('/^https?:\\/\\//i', $path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }

    private function prosesBlokPdfModul(array $blocks, Request $request, array $oldBlocks = []): array
    {
        $oldById = collect($oldBlocks)->filter('is_array')->keyBy(fn ($b) => (string) ($b['file_id'] ?? ''));
        $files = $request->file('file_pdf_blok', []);
        $kept = [];
        $result = [];
        foreach (array_values($blocks) as $raw) {
            $block = is_array($raw) ? $raw : [];
            if (($block['tipe'] ?? '') !== 'pdf') { $result[] = $block; continue; }
            $fileId = (string) ($block['file_id'] ?? Str::uuid());
            $block['file_id'] = $fileId;
            $previous = $oldById->get($fileId, []);
            $upload = is_array($files) ? ($files[$fileId] ?? null) : null;
            if ($upload) {
                $path = $upload->store('self-help/modul', 'public');
                $block['file_pdf'] = $path;
                $block['nama_file'] = $upload->getClientOriginalName();
                $block['jumlah_halaman'] = $this->hitungHalamanPdf($path) ?: (int) ($block['jumlah_halaman'] ?? 1);
                if (!empty($previous['file_pdf']) && $previous['file_pdf'] !== $path) $this->hapusFileSelfHelp($previous['file_pdf']);
            } else {
                $block['file_pdf'] = $block['file_pdf'] ?? ($previous['file_pdf'] ?? null);
                $block['nama_file'] = $block['nama_file'] ?? ($previous['nama_file'] ?? null);
                $block['jumlah_halaman'] = $block['jumlah_halaman'] ?? ($previous['jumlah_halaman'] ?? null);
            }
            if (!empty($block['file_pdf'])) $kept[] = $block['file_pdf'];
            $result[] = $block;
        }
        foreach ($oldBlocks as $old) {
            $oldPath = is_array($old) ? ($old['file_pdf'] ?? null) : null;
            if ($oldPath && !in_array($oldPath, $kept, true)) $this->hapusFileSelfHelp($oldPath);
        }
        return $result;
    }

    private function mapSelfHelpDb(SelfHelp $m): array
    {
        $detail = $m->detail ?? [];
        return [
            'id' => $m->id, 'judul' => $m->judul, 'tipe' => $m->tipe, 'topik' => $m->topik,
            'tanggal' => $m->created_at?->format('d M Y'), 'status' => $m->status,
            'tipe_tone' => $m->tipe === 'Video' ? 'merah' : ($m->tipe === 'Worksheet' ? 'kuning' : ($m->tipe === 'Modul' ? 'hijau' : 'biru')),
            'status_tone' => $m->status === 'Dipublikasi' ? 'hijau' : 'kuning',
            'isi' => $detail['isi'] ?? '', 'link_youtube' => $detail['link_youtube'] ?? '',
            'keterangan' => $detail['keterangan'] ?? '', 'pertanyaan' => $detail['pertanyaan'] ?? [],
            'jumlah_halaman' => $detail['jumlah_halaman'] ?? null,
            'blok' => $detail['blok'] ?? [],
            'thumbnail' => $m->thumbnail, 'file_pdf' => $m->file_pdf ?: ($detail['file_pdf'] ?? null),
        ];
    }

    /**
     * Daftar konten self help (dummy + tambahan dari session), lengkap dengan
     * tone badge tipe & status. Konten baru tampil paling atas.
     */
    private function daftarSelfHelp()
    {
        $tambahan = collect(session('self_help_tambahan', []))->reverse();

        $tipeTone   = ['Artikel' => 'biru', 'Video' => 'merah', 'Worksheet' => 'kuning'];
        $statusTone = ['Dipublikasi' => 'hijau', 'Draft' => 'kuning'];

        return $tambahan
            ->concat([
                ['id' => 1, 'judul' => 'Mengatasi Kecemasan Ujian',        'tipe' => 'Artikel',   'topik' => 'Kesehatan Mental', 'tanggal' => '5 Agu 2025',  'status' => 'Dipublikasi'],
                ['id' => 2, 'judul' => 'Teknik Pomodoro untuk Belajar Efektif', 'tipe' => 'Video', 'topik' => 'Akademik',        'tanggal' => '3 Agu 2025',  'status' => 'Dipublikasi'],
                ['id' => 3, 'judul' => 'Menavigasi Dunia Kerja',           'tipe' => 'Artikel',   'topik' => 'Karier',           'tanggal' => '1 Agu 2025',  'status' => 'Dipublikasi'],
                ['id' => 4, 'judul' => 'Membangun Kepercayaan Diri',       'tipe' => 'Worksheet', 'topik' => 'Pribadi',          'tanggal' => '29 Jul 2025', 'status' => 'Draft'],
                ['id' => 5, 'judul' => 'Jurnal Mindfulness Harian',        'tipe' => 'Worksheet', 'topik' => 'Kesehatan Mental', 'tanggal' => '27 Jul 2025', 'status' => 'Dipublikasi'],
            ])
            ->map(function ($k) use ($tipeTone, $statusTone) {
                $k['tipe_tone']   = $tipeTone[$k['tipe']] ?? 'biru';
                $k['status_tone'] = $statusTone[$k['status']] ?? 'kuning';

                return $k;
            })
            ->values();
    }

    /**
     * Daftar semua pengajuan (dummy). Pengajuan yang sudah diverifikasi lewat
     * tombol Verifikasi (disimpan di session) otomatis berstatus "Diverifikasi".
     * Dipakai bersama oleh index(), show(), dan verifikasi().
     * Nanti diganti mis. Pengajuan::orderBy('created_at')->get().
     */
    private function daftarPengajuan()
    {
        $diverifikasi = session('pengajuan_diverifikasi', []);

        return collect([
            ['kode' => 'SLK-2025-08-4527', 'nama' => 'Fikri Ramadhan',  'no' => '2201123456', 'kategori' => 'Akademik',          'tanggal' => '9 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4526', 'nama' => 'Nabila Putri',    'no' => '2201123457', 'kategori' => 'Kesehatan Mental',  'tanggal' => '8 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4525', 'nama' => 'Galih Prakoso',   'no' => '2201123458', 'kategori' => 'Karier',            'tanggal' => '8 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4524', 'nama' => 'Rahma Aulia',     'no' => '2201123459', 'kategori' => 'Pribadi',           'tanggal' => '7 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4523', 'nama' => 'Yoga Pramudya',   'no' => '2201123460', 'kategori' => 'Sosial',            'tanggal' => '6 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4522', 'nama' => 'Citra Anggraini', 'no' => '2201123461', 'kategori' => 'Penyelesaian Studi', 'tanggal' => '6 Agu 2025', 'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4521', 'nama' => 'Andi Setiawan',   'no' => '1234567890', 'kategori' => 'Kesehatan Mental',  'tanggal' => '5 Agu 2025',  'status' => 'Menunggu Verifikasi', 'tone' => 'menunggu'],
            ['kode' => 'SLK-2025-08-4520', 'nama' => 'Dewi Rahmawati',  'no' => '0987654321', 'kategori' => 'Akademik',           'tanggal' => '4 Agu 2025',  'status' => 'Diverifikasi',        'tone' => 'diverifikasi'],
            ['kode' => 'SLK-2025-08-4519', 'nama' => 'Budi Santoso',    'no' => '1122334455', 'kategori' => 'Karier',             'tanggal' => '3 Agu 2025',  'status' => 'Terjadwal',           'tone' => 'terjadwal'],
            ['kode' => 'SLK-2025-08-4518', 'nama' => 'Siti Nurhaliza',  'no' => '5544332211', 'kategori' => 'Pribadi',            'tanggal' => '2 Agu 2025',  'status' => 'Diterima', 'tone' => 'diverifikasi'],
            ['kode' => 'SLK-2025-08-4517', 'nama' => 'Rizky Pratama',   'no' => '9988776655', 'kategori' => 'Sosial',             'tanggal' => '1 Agu 2025',  'status' => 'Selesai',             'tone' => 'selesai'],
            ['kode' => 'SLK-2025-08-4516', 'nama' => 'Maya Sari',       'no' => '6677889900', 'kategori' => 'Disabilitas',        'tanggal' => '31 Jul 2025', 'status' => 'Selesai',             'tone' => 'selesai'],
            ['kode' => 'SLK-2025-08-4515', 'nama' => 'Bagas Wicaksono', 'no' => '1357924680', 'kategori' => 'Penyelesaian Studi', 'tanggal' => '30 Jul 2025', 'status' => 'Ditolak',             'tone' => 'ditolak'],
            ['kode' => 'SLK-2025-08-4514', 'nama' => 'Linda Puspita',   'no' => '2468013579', 'kategori' => 'Akademik',           'tanggal' => '29 Jul 2025', 'status' => 'Selesai',             'tone' => 'selesai'],
        ])->map(function ($p) use ($diverifikasi) {
            if (isset($diverifikasi[$p['kode']])) {
                $p['status']       = 'Diverifikasi';
                $p['tone']         = 'diverifikasi';
                $p['diverifikasi'] = $diverifikasi[$p['kode']];
            }

            return $p;
        })->values();
    }

    /**
     * Detail lengkap satu pengajuan = isi formulir pengajuan konseling (create.blade.php).
     * Nama key mengikuti formulir tanpa prefiks jenis konseli
     * (mahasiswa_nama -> nama, alumni_telepon -> telepon, dst).
     *
     * 'jenis_konseli' : mahasiswa | alumni | tenaga_kependidikan | wali_mahasiswa
     * 'metode'        : tatap_muka | daring
     * 'hari'          : senin..jumat        'waktu' : pagi | siang | sore
     * 'kerentanan'    : rendah | sedang | tinggi   'konselor' : profesional | tutor_sebaya
     * 'darurat'       : ya | tidak
     *
     * Data selain yang ditulis eksplisit di dummyDetailPengajuan() diisi nilai bawaan di bawah.
     */
    private function detailPengajuan(array $p): array
    {
        $dasar = [
            'jenis_konseli' => 'mahasiswa',
            'nama'          => $p['nama'],
            'jenis_kelamin' => 'Perempuan',
            'nim'           => $p['no'],
            'fakultas'      => 'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)',
            'prodi'         => 'Bimbingan dan Konseling (S1)',
            'tempat_lahir'  => 'Semarang',
            'tanggal_lahir' => '12 Mei 2004',
            'alamat_kos'    => 'Jl. Sekaran Raya No. 12, Gunungpati, Kota Semarang',
            'alamat_rumah'  => 'Jl. Merdeka No. 45, Ungaran, Kabupaten Semarang',
            'tempat_bekerja' => null,
            'telepon'       => '081234567890',
            'email'         => strtolower(explode(' ', $p['nama'])[0]) . '@students.unnes.ac.id',
            'kontak_darurat' => [
                ['nama' => 'Sutrisno', 'hubungan' => 'Bapak', 'nomor' => '081311112222'],
                ['nama' => 'Lestari',  'hubungan' => 'Ibu',   'nomor' => '081322223333'],
            ],
            'metode'        => 'daring',
            'hari'          => 'senin',
            'waktu'         => 'pagi',
            'hambatan'      => ['Stres kuliah', 'Manajemen Waktu'],
            'kerentanan'    => 'sedang',
            'konselor'      => 'profesional',
            'darurat'       => 'tidak',
            'uraian'        => "Dalam beberapa minggu terakhir saya merasa kewalahan dengan tuntutan kuliah dan sulit membagi waktu.\nSaya sudah mencoba membuat jadwal belajar sendiri, tetapi sering tidak konsisten dan akhirnya menumpuk. Saya ingin berdiskusi tentang cara mengatur prioritas.",
            'email_terverifikasi' => true,
            'persetujuan'         => true,
        ];

        return array_replace($dasar, $this->dummyDetailPengajuan()[$p['kode']] ?? []);
    }

    /**
     * Isian detail yang berbeda dari nilai bawaan, key = kode pengajuan.
     */
    private function dummyDetailPengajuan(): array
    {
        return [
            'SLK-2025-08-4527' => [
                'jenis_kelamin' => 'Laki-laki',
                'fakultas' => 'Fakultas Teknik (FT)', 'prodi' => 'Teknik Sipil (S1)',
                'tempat_lahir' => 'Tegal', 'tanggal_lahir' => '9 Januari 2003',
                'telepon' => '081234560027', 'email' => 'fikri.ramadhan@students.unnes.ac.id',
                'hambatan' => ['Stres kuliah', 'Motivasi', 'Manajemen Waktu'],
                'konselor' => 'tutor_sebaya', 'hari' => 'rabu', 'waktu' => 'siang',
                'uraian' => "Nilai saya turun dua semester berturut-turut dan saya mulai kehilangan semangat kuliah.\nSaya merasa tertinggal dari teman-teman dan bingung harus mulai dari mana untuk memperbaikinya.",
            ],
            'SLK-2025-08-4526' => [
                'fakultas' => 'Fakultas Bahasa dan Seni (FBS)', 'prodi' => 'Sastra Inggris (S1)',
                'tempat_lahir' => 'Pati', 'tanggal_lahir' => '17 Agustus 2004',
                'telepon' => '081234560026', 'email' => 'nabila.putri@students.unnes.ac.id',
                'hambatan' => ['Depresi', 'Putus asa', 'Kehilangan'],
                'kerentanan' => 'tinggi', 'darurat' => 'ya', 'metode' => 'tatap_muka', 'hari' => 'senin', 'waktu' => 'pagi',
                'uraian' => "Sejak ibu saya meninggal tiga bulan lalu, saya merasa sangat kosong dan sulit tidur.\nSaya jadi sering tidak masuk kuliah dan menjauh dari teman-teman. Saya butuh seseorang untuk diajak bicara secepatnya.",
            ],
            'SLK-2025-08-4525' => [
                'jenis_konseli' => 'alumni', 'jenis_kelamin' => 'Laki-laki',
                'fakultas' => 'Fakultas Teknik (FT)', 'prodi' => 'Teknik Elektro (S1)', 'tahun_angkatan' => 2019,
                'tempat_lahir' => 'Magelang', 'tanggal_lahir' => '2 Oktober 2000',
                'tempat_bekerja' => 'PT Nusantara Teknologi, Semarang',
                'telepon' => '081234560025', 'email' => 'galih.prakoso@gmail.com',
                'hambatan' => ['Karier', 'Hambatan di masa transisi'],
                'kerentanan' => 'rendah', 'hari' => 'kamis', 'waktu' => 'sore',
                'uraian' => "Saya sudah satu tahun bekerja tetapi merasa pekerjaan ini kurang sesuai dengan minat saya.\nSaya ingin berdiskusi tentang arah karier dan kemungkinan pindah bidang.",
            ],
            'SLK-2025-08-4524' => [
                'jenis_konseli' => 'tenaga_kependidikan',
                'tempat_lahir' => 'Pekalongan', 'tanggal_lahir' => '21 Juli 1988',
                'unit_kerja' => 'Fakultas Ekonomika dan Bisnis',
                'alamat' => 'Jl. Banaran Raya No. 8, Gunungpati, Kota Semarang',
                'telepon' => '081234560024', 'email' => 'rahma.aulia@mail.unnes.ac.id',
                'kontak_darurat' => [['nama' => 'Hendra Wijaya', 'hubungan' => 'Suami', 'nomor' => '081355556666']],
                'hambatan' => ['Pengasuhan', 'Kehilangan'],
                'kerentanan' => 'sedang', 'metode' => 'tatap_muka', 'hari' => 'jumat', 'waktu' => 'siang',
                'uraian' => "Setelah kehilangan ayah, saya kesulitan membagi energi antara pekerjaan dan mengurus anak.\nSaya ingin ada ruang untuk bercerita dan menemukan cara menjalaninya dengan lebih tenang.",
            ],
            'SLK-2025-08-4523' => [
                'jenis_konseli' => 'wali_mahasiswa', 'hubungan' => 'Ayah',
                'mahasiswa_nama' => 'Dimas Pramudya', 'mahasiswa_nim' => '2201987654',
                'telepon' => '081234560023', 'email' => 'yoga.pramudya@gmail.com',
                'kontak_darurat' => [],
                'hambatan' => ['Pengasuhan', 'Tekanan Keluarga'],
                'kerentanan' => 'rendah', 'konselor' => 'profesional', 'metode' => 'tatap_muka', 'hari' => 'kamis', 'waktu' => 'sore',
                'uraian' => "Anak saya jadi lebih pendiam sejak pindah kos dan jarang menghubungi keluarga.\nSaya ingin tahu bagaimana cara mendampinginya tanpa terkesan menekan.",
            ],
            'SLK-2025-08-4522' => [
                'fakultas' => 'Fakultas Ekonomika dan Bisnis (FEB)', 'prodi' => 'Akuntansi (S1)',
                'tempat_lahir' => 'Demak', 'tanggal_lahir' => '5 Februari 2002',
                'telepon' => '081234560022', 'email' => 'citra.anggraini@students.unnes.ac.id',
                'hambatan' => ['Hambatan di masa transisi', 'Manajemen Waktu', 'Motivasi'],
                'hari' => 'selasa', 'waktu' => 'pagi',
                'uraian' => "Skripsi saya sudah berjalan lebih dari satu tahun dan belum selesai juga.\nSaya merasa tertekan karena teman seangkatan sudah lulus, dan mulai ragu apakah bisa menyelesaikannya.",
            ],
            'SLK-2025-08-4521' => [
                'jenis_kelamin' => 'Laki-laki',
                'fakultas' => 'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)', 'prodi' => 'Teknik Informatika (S1)',
                'tempat_lahir' => 'Kendal', 'tanggal_lahir' => '3 Maret 2003',
                'telepon' => '081234567801', 'email' => 'andi.s@students.unnes.ac.id',
                'hambatan' => ['Kecemasan/panik', 'Stres kuliah', 'Kesepian'],
                'hari' => 'selasa', 'waktu' => 'siang',
                'uraian' => "Setiap menjelang ujian saya merasa sangat cemas, jantung berdebar, dan sulit konsentrasi.\nSaya juga merasa sendirian karena jarang bercerita kepada teman. Saya ingin belajar mengelola kecemasan ini.",
            ],
            'SLK-2025-08-4520' => [
                'diverifikasi' => ['waktu' => '4 Agu 2025, 14.20', 'oleh' => 'Admin LP3'],
            ],
            'SLK-2025-08-4519' => ['jenis_kelamin' => 'Laki-laki'],
            'SLK-2025-08-4517' => ['jenis_kelamin' => 'Laki-laki'],
            'SLK-2025-08-4515' => ['jenis_kelamin' => 'Laki-laki'],
        ];
    }

    /**
     * Susun daftar isian identitas sesuai jenis konseli, mengikuti urutan & label
     * di formulir pengajuan. 'penuh' = isian melebar 2 kolom, 'tipe' = telepon|email
     * (dipakai view untuk membuat tautan WhatsApp / mailto).
     */
    private function identitasKonseli(array $d): array
    {
        $f = fn ($label, $key, $penuh = false, $tipe = null) => [
            'label' => $label,
            'nilai' => $d[$key] ?? null,
            'penuh' => $penuh,
            'tipe'  => $tipe,
        ];

        return match ($d['jenis_konseli']) {
            'wali_mahasiswa' => array_values(array_filter([
                $f('Yang membutuhkan konseling', 'subjek_konseling'),
                $f('Nama yang membutuhkan konseling', 'nama', true),
                $f('Hubungan dengan mahasiswa', 'hubungan'),
                $f('Nama mahasiswa yang diwakili', 'mahasiswa_nama'),
                $f('NIM mahasiswa yang diwakili', 'mahasiswa_nim'),
                $f('Nama wali', 'wali_nama'),
                $f('Nomor telepon wali', 'wali_telepon', false, 'telepon'),
                $f('Nomor WhatsApp mahasiswa', 'mahasiswa_telepon', false, 'telepon'),
                $f('Email wali', 'email', false, 'email'),
            ], fn ($field) => $field['label'] !== 'Nomor WhatsApp mahasiswa' || ($d['subjek_konseling'] ?? '') === 'Mahasiswa yang diwakili')),
            'tenaga_kependidikan' => [
                $f('Nama', 'nama', true),
                $f('Jenis kelamin', 'jenis_kelamin'),
                $f('Unit kerja', 'unit_kerja'),
                $f('Tempat lahir', 'tempat_lahir'),
                $f('Tanggal lahir', 'tanggal_lahir'),
                $f('Nomor telepon', 'telepon', false, 'telepon'),
                $f('Email', 'email', true, 'email'),
                $f('Alamat', 'alamat', true),
            ],
            'alumni' => [
                $f('Nama', 'nama', true),
                $f('Jenis kelamin', 'jenis_kelamin'),
                $f('NIM', 'nim'),
                $f('Fakultas', 'fakultas', true),
                $f('Program studi', 'prodi'),
                $f('Tahun angkatan', 'tahun_angkatan'),
                $f('Tempat lahir', 'tempat_lahir'),
                $f('Tanggal lahir', 'tanggal_lahir'),
                $f('Nomor telepon', 'telepon', false, 'telepon'),
                $f('Email', 'email', false, 'email'),
                $f('Alamat kos', 'alamat_kos', true),
                $f('Alamat rumah', 'alamat_rumah', true),
                $f('Tempat bekerja', 'tempat_bekerja', true),
            ],
            default => [
                $f('Nama', 'nama', true),
                $f('Jenis kelamin', 'jenis_kelamin'),
                $f('NIM', 'nim'),
                $f('Fakultas', 'fakultas', true),
                $f('Program studi', 'prodi', true),
                $f('Tempat lahir', 'tempat_lahir'),
                $f('Tanggal lahir', 'tanggal_lahir'),
                $f('Nomor telepon', 'telepon', false, 'telepon'),
                $f('Email UNNES', 'email', false, 'email'),
                $f('Alamat kos', 'alamat_kos', true),
                $f('Alamat rumah', 'alamat_rumah', true),
                $f('Tempat bekerja', 'tempat_bekerja', true),
            ],
        };
    }

    private function mapStatusFilter(string $status): string
    {
        return match ($status) {
            'menunggu', 'menunggu_pemeriksaan' => Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN,
            'diverifikasi', 'diterima' => Pengajuan::STATUS_DITERIMA,
            default => $status,
        };
    }

    private function legacyRow(Pengajuan $record): array
    {
        $prioritas = app(PrioritasKonselingService::class)->untuk($record);
        $tone = match ($record->status) {
            Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN => 'menunggu',
            Pengajuan::STATUS_DITOLAK, Pengajuan::STATUS_DIBATALKAN => 'ditolak',
            'selesai' => 'selesai',
            default => 'diverifikasi',
        };
        $data = (array) ($record->data ?? []);
        $namaTampil = $record->konseli->nama;
        if ($record->konseli->jenis_konseli === 'wali_mahasiswa') {
            $namaTampil = ($data['wali_subjek_konseling'] ?? 'wali') === 'mahasiswa'
                ? ($data['wali_mahasiswa_nama'] ?? $namaTampil)
                : ($data['wali_nama'] ?? $namaTampil);
        }
        $teleponTampil = $record->konseli->telepon;
        if ($record->konseli->jenis_konseli === 'wali_mahasiswa') {
            $teleponTampil = ($data['wali_subjek_konseling'] ?? 'wali') === 'mahasiswa'
                ? ($data['wali_mahasiswa_telepon'] ?? $data['wali_telepon'] ?? $teleponTampil)
                : ($data['wali_telepon'] ?? $teleponTampil);
        }
        return [
            'kode' => $record->kode_pengajuan,
            'nama' => $namaTampil,
            'jenis' => match ($record->konseli->jenis_konseli) {
                'mahasiswa' => 'Mahasiswa',
                'alumni' => 'Alumni',
                'tenaga_kependidikan' => 'Dosen / Tendik',
                'wali_mahasiswa' => 'Wali Mahasiswa',
                default => ucfirst(str_replace('_', ' ', $record->konseli->jenis_konseli)),
            },
            'no' => $record->nim ?: $record->email,
            'telepon' => $teleponTampil,
            'kategori' => collect((array) data_get($record->data, 'lingkup_hambatan', []))->implode(', ') ?: 'Konseling individual',
            'tanggal' => optional($record->created_at)->format('d M Y'),
            'tanggal_lengkap' => optional($record->created_at)->format('d M Y, H:i:s'),
            'created_at' => optional($record->created_at)->toIso8601String(),
            'status' => $this->statusLabel($record->status),
            'tone' => $tone,
            'who5_skor' => $prioritas['who5_skor'],
            'saw_nilai' => $prioritas['saw_nilai'],
            'prioritas' => $prioritas['prioritas'],
            'diverifikasi' => $record->diperiksa_at ? ['waktu' => $record->diperiksa_at->format('d M Y, H:i'), 'oleh' => optional($record->pemeriksa)->name] : null,
        ];
    }

    private function legacyDetail(Pengajuan $record): array
    {
        $d = $record->data ?: [];
        $type = $record->konseli->jenis_konseli;
        $prefix = match ($type) { 'mahasiswa' => 'mahasiswa', 'alumni' => 'alumni', 'tenaga_kependidikan' => 'tk', default => 'wali' };
        $waliMahasiswa = $type === 'wali_mahasiswa' && ($d['wali_subjek_konseling'] ?? 'wali') === 'mahasiswa';
        $kontakUtama = $waliMahasiswa ? ($d['wali_mahasiswa_telepon'] ?? $record->konseli->telepon) : $record->konseli->telepon;
        return [
            'jenis_konseli' => $type,
            'metode' => $d['metode_layanan'] ?? '',
            'prioritas' => $record->prioritas ?: 'Belum dihitung',
            'darurat' => $d['kondisi_darurat'] ?? 'tidak',
            'darurat_digunakan' => array_key_exists('kondisi_darurat', $d),
            'email' => $record->email,
            'telepon' => $kontakUtama,
            'subjek_konseling' => $type === 'wali_mahasiswa' ? ($d['wali_subjek_konseling'] ?? 'wali') : null,
            'hari' => $d['preferensi_hari'] ?? '',
            'waktu' => $d['preferensi_waktu'] ?? '',
            'konselor' => $d['pilihan_konselor'] ?? '',
            'hambatan' => $d['lingkup_hambatan'] ?? [],
            // Uraian tidak dikirim ke antarmuka Admin; aksesnya dibatasi untuk
            // konselor yang menangani sesuai kewenangan layanan.
            'uraian' => null,
            'persetujuan' => true,
            'email_terverifikasi' => (bool) $record->terverifikasi_at,
            'kontak_darurat' => array_values(array_filter([
                ['nama' => $d[$prefix . '_darurat_1_nama'] ?? null, 'hubungan' => $d[$prefix . '_darurat_1_hubungan'] ?? null, 'nomor' => $d[$prefix . '_darurat_1_nomor'] ?? null],
                ['nama' => $d[$prefix . '_darurat_2_nama'] ?? null, 'hubungan' => $d[$prefix . '_darurat_2_hubungan'] ?? null, 'nomor' => $d[$prefix . '_darurat_2_nomor'] ?? null],
            ], fn ($item) => $item['nama'] || $item['nomor'])),
            'alasan_penolakan' => $record->alasan_penolakan,
            'who5_skor' => $record->who5_skor,
            'saw_nilai' => $record->saw_nilai,
            'prioritas' => $record->prioritas ?: 'Belum dihitung',
        ];
    }

    private function legacyIdentitas(Pengajuan $record): array
    {
        $d = $this->legacyDetail($record);
        $raw = $record->data ?: [];
        if ($record->konseli->jenis_konseli === 'wali_mahasiswa') {
            $subjek = $raw['wali_subjek_konseling'] ?? 'wali';
            $d['yang_dikonseling'] = $subjek === 'mahasiswa' ? 'Mahasiswa yang diwakili' : 'Wali mahasiswa';
            $d['subjek_konseling'] = $d['yang_dikonseling'];
            $d['nama'] = $subjek === 'mahasiswa' ? ($raw['wali_mahasiswa_nama'] ?? $record->konseli->nama) : ($raw['wali_nama'] ?? $record->konseli->nama);
            $d['telepon'] = $subjek === 'mahasiswa' ? ($raw['wali_mahasiswa_telepon'] ?? $raw['wali_telepon'] ?? $record->konseli->telepon) : ($raw['wali_telepon'] ?? $record->konseli->telepon);
        } else {
            $d['nama'] = $record->konseli->nama;
        }
        $d['nim'] = $record->nim;
        $d['jenis_kelamin'] = $raw['mahasiswa_jenis_kelamin'] ?? $raw['alumni_jenis_kelamin'] ?? $raw['tk_jenis_kelamin'] ?? null;
        $d['fakultas'] = $raw['mahasiswa_fakultas'] ?? $raw['alumni_fakultas'] ?? null;
        $d['prodi'] = $raw['mahasiswa_jurusan'] ?? $raw['alumni_jurusan'] ?? null;
        $d['tempat_lahir'] = $raw['mahasiswa_tempat_lahir'] ?? $raw['alumni_tempat_lahir'] ?? $raw['tk_tempat_lahir'] ?? null;
        $d['tanggal_lahir'] = $raw['mahasiswa_tanggal_lahir'] ?? $raw['alumni_tanggal_lahir'] ?? $raw['tk_tanggal_lahir'] ?? null;
        $d['alamat_kos'] = $raw['mahasiswa_alamat_kos'] ?? $raw['alumni_alamat_kos'] ?? null;
        $d['alamat_rumah'] = $raw['mahasiswa_alamat_rumah'] ?? $raw['alumni_alamat_rumah'] ?? $raw['tk_alamat'] ?? null;
        $d['tempat_bekerja'] = $raw['mahasiswa_tempat_bekerja'] ?? $raw['alumni_tempat_bekerja'] ?? null;
        $d['unit_kerja'] = $raw['tk_unit_kerja'] ?? null;
        $d['tahun_angkatan'] = $raw['alumni_tahun_angkatan'] ?? null;
        $d['hubungan'] = $raw['wali_hubungan'] ?? null;
        $d['mahasiswa_nama'] = $raw['wali_mahasiswa_nama'] ?? null;
        $d['mahasiswa_nim'] = $raw['wali_mahasiswa_nim'] ?? null;
        if ($record->konseli->jenis_konseli === 'wali_mahasiswa') {
            $d['wali_nama'] = $raw['wali_nama'] ?? null;
            $d['wali_telepon'] = $raw['wali_telepon'] ?? null;
            $d['mahasiswa_telepon'] = $raw['wali_mahasiswa_telepon'] ?? null;
        }
        return $this->identitasKonseli($d);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN => 'Menunggu Pemeriksaan',
            'diterima' => 'Diterima', 'ditugaskan' => 'Diterima', 'menunggu_jadwal' => 'Diterima',
            'terjadwal' => 'Terjadwal', 'sesi_lanjutan' => 'Menunggu Penjadwalan Sesi Lanjutan', 'berlangsung' => 'Terjadwal', 'selesai' => 'Selesai',
            'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
