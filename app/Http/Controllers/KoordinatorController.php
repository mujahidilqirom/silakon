<?php

// Simpan sebagai: app/Http/Controllers/KoordinatorController.php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\ProgramKonselingKelompok;
use App\Models\SelfHelp;
use App\Models\SelfHelpPengerjaan;
use App\Models\Pengajuan;
use App\Models\JadwalKonseling;
use App\Models\Konseli;
use App\Models\CatatanKonseling;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Support\SimplePdf;

class KoordinatorController extends Controller
{
    private function audit(string $action, string $detail = ''): void
    {
        ActivityLog::create(['user_id'=>auth()->id(), 'action'=>$action, 'detail'=>$detail, 'ip_address'=>request()->ip()]);
    }

    public function tandaiKasusMendesak(Request $request, string $kode): RedirectResponse
    {
        $data=$request->validate(['hasil'=>'required|string|max:50','catatan'=>'nullable|string|max:1000']);
        $p=Pengajuan::where('kode_pengajuan',$kode)->firstOrFail(); $payload=$p->data ?? [];
        $payload['darurat_status']='ditangani'; $payload['darurat_hasil']=$data['hasil']; $payload['darurat_catatan']=$data['catatan'] ?? null; $payload['darurat_ditangani_at']=now()->toIso8601String();
        $p->update(['data'=>$payload]); $this->audit('Tandai Kasus Mendesak','Menandai '.$kode.' sebagai ditangani');
        return back()->with('sukses','Kasus mendesak berhasil ditandai sebagai ditangani.');
    }
    public function dashboard(Request $request): View
    {
        $dariInput = trim((string) $request->query('dari', ''));
        $sampaiInput = trim((string) $request->query('sampai', ''));
        $distribusiMode = $request->query('distribusi', 'konseli') === 'pengajuan' ? 'pengajuan' : 'konseli';
        $semuaPeriode = $dariInput === '' && $sampaiInput === '';
        $dari = $sampai = null;
        if (! $semuaPeriode) {
            try {
                $dari = Carbon::parse($dariInput !== '' ? $dariInput : $sampaiInput)->startOfDay();
                $sampai = Carbon::parse($sampaiInput !== '' ? $sampaiInput : $dariInput)->endOfDay();
                if ($dari->gt($sampai)) {
                    [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
                }
            } catch (\Throwable) {
                $dariInput = $sampaiInput = '';
                $semuaPeriode = true;
            }
        }
        $tahun = (int) ($dari?->year ?: now()->year);
        $semester = 'Rentang tanggal';
        $mulaiPeriode = $semuaPeriode ? null : $dari;
        $akhirPeriode = $semuaPeriode ? null : $sampai;
        $iconUsers = '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>';

        // Kartu statistik
        $statistik = [
            ['nilai' => 134, 'label' => 'Konseli', 'sub' => 'Total terdaftar', 'tone' => 'green', 'icon' => $iconUsers],
            ['nilai' => 187, 'label' => 'Tiket', 'sub' => '12 aktif bulan ini', 'tone' => 'blue', 'icon' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>'],
            ['nilai' => 203, 'label' => 'Sesi', 'sub' => 'Total sesi terlaksana', 'tone' => 'purple', 'icon' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>'],
            ['nilai' => 58, 'label' => 'Peserta Kelompok', 'sub' => '3 kegiatan aktif', 'tone' => 'yellow', 'icon' => $iconUsers],
            ['nilai' => 91, 'label' => 'Pengerjaan Self Help', 'sub' => 'Modul dikerjakan', 'tone' => 'pink', 'icon' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>'],
        ];

        // Tren bulanan (jumlah asli; tinggi batang dihitung di view)
        $tren = [
            ['bulan' => 'Apr', 'konseli' => 18, 'sesi' => 26],
            ['bulan' => 'Mei', 'konseli' => 20, 'sesi' => 30],
            ['bulan' => 'Jun', 'konseli' => 22, 'sesi' => 34],
            ['bulan' => 'Jul', 'konseli' => 24, 'sesi' => 36],
            ['bulan' => 'Agu', 'konseli' => 26, 'sesi' => 38],
            ['bulan' => 'Sep', 'konseli' => 24, 'sesi' => 39],
        ];

        $darurat = [
            ['nilai' => 7,     'label' => 'Total Darurat',        'warna' => '#F59E0B'],
            ['nilai' => 5,     'label' => 'Sudah Ditangani',      'warna' => '#6E8F60'],
            ['nilai' => 2,     'label' => 'Belum Ditangani',      'warna' => '#EF4444'],
            ['nilai' => '1.4 jam', 'label' => 'Rata-rata Penanganan', 'warna' => '#1B2432'],
        ];

        // Distribusi: [label, jumlah, persen, warna(opsional)]
        $distribusi = [
            'Jenis Konseli' => [['Mahasiswa', 89, 66], ['Alumni', 21, 16], ['Pegawai/Staf', 15, 11], ['Wali Mahasiswa', 9, 7]],
            'Jenis Kelamin' => [['Perempuan', 81, 60], ['Laki-laki', 53, 40]],
            'Mode Konseling' => [['Tatap Muka', 121, 65], ['Online', 66, 35]],
            'Tingkat Masalah' => [['Rendah', 48, 26], ['Sedang', 91, 49, 'warn'], ['Tinggi', 48, 26, 'bad']],
            'Kategori Masalah' => [['Kesehatan Mental', 52, 28], ['Akademik', 41, 22], ['Pribadi', 35, 19], ['Asmara', 28, 15], ['Karier', 18, 10], ['Keluarga', 11, 6]],
            'Fakultas' => [['FIPP', 34, 25], ['FIS', 28, 21], ['FT', 22, 16], ['FMIPA', 20, 15], ['FE', 18, 13], ['Lainnya', 12, 9]],
        ];

        $waktuTunggu = [
            'nilai' => '3.2',
            'ket' => 'hari (daftar → sesi pertama)',
            'rincian' => [['Daftar → Penugasan', '0.8 hari'], ['Penugasan → Jadwal', '1.4 hari'], ['Jadwal → Sesi', '1.0 hari']],
        ];

        $tidakHadir = [
            'nilai' => '8.4%',
            'ket' => 'dari total sesi terjadwal',
            'rincian' => [['Total tidak hadir', '17 sesi'], ['Dijadwalkan ulang', '11 sesi'], ['Tiket ditutup', '6 tiket']],
        ];

        $tiketMacet = [
            ['kode' => 'LP3-2026-000043', 'ket' => 'Menunggu Catatan Konselor · 8 hari'],
            ['kode' => 'LP3-2026-000031', 'ket' => 'Menunggu Survei · 15 hari'],
            ['kode' => 'LP3-2026-000019', 'ket' => 'Diterima · 6 hari'],
        ];

        $konselor = [
            ['nama' => 'Dr. Sari Dewi, M.Psi',      'aktif' => 0, 'selesai' => 0],
            ['nama' => 'Drs. Agus Santoso, M.Si',   'aktif' => 0, 'selesai' => 0],
            ['nama' => 'Dr. Rina Kusumawati, M.Pd', 'aktif' => 0, 'selesai' => 0],
            ['nama' => 'Dr. Hendra Pratama, M.Psi', 'aktif' => 0, 'selesai' => 0],
            ['nama' => 'Dra. Fatimah Zahra, M.Pd',  'aktif' => 0, 'selesai' => 0],
        ];

        $allPengajuan=Pengajuan::with('konseli')
            ->when(! $semuaPeriode, fn($q) => $q->whereBetween('created_at', [$mulaiPeriode, $akhirPeriode]))
            ->get();
        // Distribusi demografi dihitung per konseli unik, bukan per pengajuan.
        // Satu konseli dapat memiliki beberapa tiket sehingga memakai seluruh
        // pengajuan akan membuat jumlah grafik melebihi kartu total konseli.
        $demografi = $allPengajuan->sortByDesc('created_at')->unique('konseli_id')->values();
        $distributionSource = $distribusiMode === 'pengajuan' ? $allPengajuan : $demografi;
        $value = static function ($p, array $keys, ?string $fallback = null): array {
            $data = $p->data ?? [];
            $jenis = (string) data_get($data, 'jenis_konseli', $p->konseli?->jenis_konseli);
            if (in_array('__jenis_kelamin_subjek', $keys, true)) {
                $key = match ($jenis) {
                    'mahasiswa' => 'mahasiswa_jenis_kelamin',
                    'alumni' => 'alumni_jenis_kelamin',
                    'tenaga_kependidikan' => 'tk_jenis_kelamin',
                    'wali_mahasiswa' => data_get($data, 'wali_subjek_konseling') === 'mahasiswa' ? 'wali_mahasiswa_jenis_kelamin' : 'wali_jenis_kelamin',
                    default => 'jenis_kelamin',
                };
                $candidate = data_get($data, $key);
                return is_string($candidate) && trim($candidate) !== '' ? [trim($candidate)] : ($fallback === null ? [] : [$fallback]);
            }
            if (in_array('__fakultas_subjek', $keys, true)) {
                $key = match ($jenis) {
                    'mahasiswa' => 'mahasiswa_fakultas',
                    'alumni' => 'alumni_fakultas',
                    'tenaga_kependidikan' => 'tk_unit_kerja',
                    'wali_mahasiswa' => data_get($data, 'wali_subjek_konseling') === 'mahasiswa' ? 'wali_mahasiswa_fakultas' : null,
                    default => 'fakultas',
                };
                $candidate = $key ? data_get($data, $key) : null;
                return is_string($candidate) && trim($candidate) !== '' ? [trim($candidate)] : ($fallback === null ? [] : [$fallback]);
            }
            foreach ($keys as $key) {
                $candidate = data_get($p, $key, data_get($data, $key));
                if (is_array($candidate)) {
                    $candidate = array_values(array_filter($candidate));
                    if ($candidate) return $candidate;
                } elseif (is_string($candidate) && trim($candidate) !== '') return [trim($candidate)];
            }
            return $fallback === null ? [] : [$fallback];
        };
        $normalisasiFakultas = static function (string $label): string {
            $kunci = strtolower(trim(preg_replace('/\s+/', ' ', $label)));
            return match ($kunci) {
                'fakultas teknik', 'ft' => 'Fakultas Teknik (FT)',
                'fakultas bahasa dan seni', 'fbs' => 'Fakultas Bahasa dan Seni (FBS)',
                'fakultas matematika & ilmu pengetahuan alam', 'fmipa' => 'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)',
                'fakultas ilmu pendidikan dan psikologi', 'fipp' => 'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)',
                default => $label,
            };
        };
        $makeDistribution = function (array $keys, ?string $fallback = null) use ($distributionSource, $value, $normalisasiFakultas): array {
            $groups = collect();
            foreach ($distributionSource as $p) foreach ($value($p, $keys, $fallback) as $label) {
                if (in_array('tk_unit_kerja', $keys, true) || in_array('__fakultas_subjek', $keys, true)) $label = $normalisasiFakultas((string) $label);
                $groups[$label] = ($groups[$label] ?? 0) + 1;
            }
            $groups = $groups->sortDesc(); $total = max(1, $distributionSource->count());
            return $groups->map(fn($n, $label) => [$label, $n, round($n / $total * 100)])->values()->all();
        };
        $ringkasKategori = function (array $baris) use ($distributionSource): array {
            $baris = collect($baris);
            if ($baris->count() <= 4) return $baris->values()->all();
            $utama = $baris->take(4)->values();
            $jumlahLainnya = $baris->skip(4)->sum(fn ($item) => (int) ($item[1] ?? 0));
            $total = max(1, $distributionSource->count());
            $utama->push(['Lainnya', $jumlahLainnya, round($jumlahLainnya / $total * 100)]);
            return $utama->all();
        };
        $kategoriMasalahLengkap = $makeDistribution(['lingkup_hambatan','kategori_masalah']);
        $distribusi=[
            'Jenis Konseli'=>$makeDistribution(['konseli.jenis_konseli','jenis_konseli']),
            'Jenis Kelamin'=>$makeDistribution(['__jenis_kelamin_subjek']),
            'Mode Konseling'=>$makeDistribution(['metode_layanan','mode_konseling']),
            'Prioritas SAW'=>$distributionSource->groupBy(fn($p) => $p->prioritas ?: 'Belum dihitung')->map(fn($g, $label) => [$label, $g->count(), round($g->count() / max(1, $distributionSource->count()) * 100)])->sortByDesc(1)->values()->all(),
            'Kategori Masalah'=>$ringkasKategori($kategoriMasalahLengkap),
            'Fakultas'=>$makeDistribution(['__fakultas_subjek']),
        ];
        $jadwalDashboard=JadwalKonseling::with('catatanKonseling')
            ->when(! $semuaPeriode, fn($q) => $q->whereBetween('created_at', [$mulaiPeriode, $akhirPeriode]))
            ->get();
        $jadwalAktifUntukTunggu = $jadwalDashboard->whereNotIn('status', ['dibatalkan', 'tidak_hadir']);
        $durasiWait=$allPengajuan->map(function($p) use($jadwalAktifUntukTunggu){$j=$jadwalAktifUntukTunggu->where('pengajuan_id',$p->id)->sortBy('tanggal')->first(); return $j ? max(0,$p->created_at->diffInDays($j->tanggal)) : null;})->filter();
        $avgWait=$durasiWait->count() ? round($durasiWait->avg(),1) : 0;
        $totalPengajuanPeriode = $allPengajuan->count();
        $totalDianalisis = $durasiWait->count();
        $belumMemilikiJadwal = max(0, $totalPengajuanPeriode - $totalDianalisis);
        $waktuTunggu=['nilai'=>(string)$avgWait,'ket'=>'hari (pengajuan → sesi pertama)','rincian'=>[['Pengajuan → Sesi',$avgWait.' hari'],['Total pengajuan dianalisis',$totalDianalisis],['Belum memiliki jadwal',$belumMemilikiJadwal]]];
        $scheduled=$jadwalDashboard->count(); $absent=$jadwalDashboard->filter(fn($j)=>in_array(strtolower((string)($j->catatanKonseling?->kehadiran ?? '')),['tidak_hadir','tidak hadir','absen']))->count();
        $tidakHadir=['nilai'=>$scheduled?round($absent/$scheduled*100,1).'%' :'0%','ket'=>'dari total sesi terjadwal','rincian'=>[['Total sesi',$scheduled],['Tidak hadir',$absent],['Hadir',max(0,$scheduled-$absent)]]];
        $bulanMulai = $semuaPeriode ? now()->startOfMonth()->subMonths(5) : $mulaiPeriode->copy()->startOfMonth();
        $jumlahBulan = $semuaPeriode ? 6 : max(1, (($akhirPeriode->year * 12 + $akhirPeriode->month) - ($mulaiPeriode->year * 12 + $mulaiPeriode->month)) + 1);
        $tren=collect(range(0, $jumlahBulan - 1))->map(function($i) use($bulanMulai, $allPengajuan, $jadwalDashboard){
            $d=$bulanMulai->copy()->addMonths($i);
            $konseli=$allPengajuan->filter(fn($p)=>$p->created_at && $p->created_at->isSameMonth($d))->pluck('konseli_id')->filter()->unique()->count();
            $sesi=$jadwalDashboard->filter(fn($j)=>$j->created_at && $j->created_at->isSameMonth($d) && $j->status === 'selesai')->count();
            return ['bulan'=>$d->translatedFormat('M'),'konseli'=>$konseli,'sesi'=>$sesi];
        })->all();
        $daruratItems=$allPengajuan->filter(fn($p)=>in_array($p->data['kondisi_darurat'] ?? '',['ya','mendesak','darurat'])); $handled=$daruratItems->filter(fn($p)=>($p->data['darurat_status'] ?? '')==='ditangani')->count();
        $darurat=[['nilai'=>$daruratItems->count(),'label'=>'Total Darurat','warna'=>'#F59E0B'],['nilai'=>$handled,'label'=>'Sudah Ditangani','warna'=>'#6E8F60'],['nilai'=>$daruratItems->count()-$handled,'label'=>'Belum Ditangani','warna'=>'#EF4444'],['nilai'=>'-','label'=>'Rata-rata Penanganan','warna'=>'#1B2432']];
        $tiketMacet=$allPengajuan->whereNotIn('status',['selesai','ditolak','dibatalkan'])->sortBy('created_at')->take(3)->map(fn($p)=>['kode'=>$p->kode_pengajuan,'ket'=>'Status '.str_replace('_',' ',$p->status).' · '.optional($p->created_at)->diffForHumans()])->values()->all();
        $konselor=User::where('role','konselor')->where('is_active',true)->get()->map(fn($u)=>['nama'=>$u->name,'aktif'=>JadwalKonseling::where('konselor_id',$u->id)->whereNotIn('status',['selesai','dibatalkan'])->count(),'selesai'=>JadwalKonseling::where('konselor_id',$u->id)->where('status','selesai')->count()])->all();

        $programDalamPeriode = ProgramKonselingKelompok::withCount('peserta')
            ->when(! $semuaPeriode, fn($q) => $q->whereBetween('created_at', [$mulaiPeriode, $akhirPeriode]))
            ->get();
        $hasilDalamPeriode = SelfHelpPengerjaan::query()
            ->when(! $semuaPeriode, fn($q) => $q->whereBetween('created_at', [$mulaiPeriode, $akhirPeriode]))
            ->count();
        $statistik[0]['nilai'] = $allPengajuan->pluck('konseli_id')->filter()->unique()->count();
        $statistik[1]['nilai'] = $allPengajuan->count();
        $statistik[2]['nilai'] = $jadwalDashboard->where('status', 'selesai')->count();
        $statistik[3]['nilai'] = $programDalamPeriode->count();
        $statistik[1]['sub'] = $semuaPeriode ? 'seluruh periode' : $mulaiPeriode->translatedFormat('d M Y').' – '.$akhirPeriode->translatedFormat('d M Y');
        $programAktif = $programDalamPeriode->map(fn ($p) => $this->mapProgramDb($p))->where('status', '!=', 'Selesai')->count();
        $statistik[3]['sub'] = $programAktif.' kegiatan aktif';
        $statistik[4]['nilai'] = $hasilDalamPeriode;
        $statistik[4]['sub'] = $semuaPeriode ? 'seluruh periode' : $mulaiPeriode->translatedFormat('d M Y').' – '.$akhirPeriode->translatedFormat('d M Y');
        return view('koordinator.dashboard', compact(
            'statistik', 'tren', 'darurat', 'distribusi', 'distribusiMode', 'kategoriMasalahLengkap',
            'waktuTunggu', 'tidakHadir', 'tiketMacet', 'konselor',
            'tahun', 'semester', 'semuaPeriode', 'dariInput', 'sampaiInput'
        ));
    }

    public function eksporDashboard(Request $request)
    {
        $dariInput = trim((string) $request->query('dari', ''));
        $sampaiInput = trim((string) $request->query('sampai', ''));
        $distribusiMode = $request->query('distribusi', 'konseli') === 'pengajuan' ? 'pengajuan' : 'konseli';
        $semua = $dariInput === '' && $sampaiInput === '';
        $dari = $sampai = null;
        if (! $semua) {
            try {
                $dari = Carbon::parse($dariInput !== '' ? $dariInput : $sampaiInput)->startOfDay();
                $sampai = Carbon::parse($sampaiInput !== '' ? $sampaiInput : $dariInput)->endOfDay();
                if ($dari->gt($sampai)) [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
            } catch (\Throwable) { $semua = true; }
        }
        $pengajuan = Pengajuan::with('konseli')->when(! $semua, fn ($q) => $q->whereBetween('created_at', [$dari, $sampai]))->latest()->get();
        $jadwal = JadwalKonseling::with('catatanKonseling')->when(! $semua, fn ($q) => $q->whereBetween('created_at', [$dari, $sampai]))->get();
        $aktif = $jadwal->whereNotIn('status', ['dibatalkan', 'tidak_hadir']);
        $selesai = $jadwal->where('status', 'selesai')->count();
        $menunggu = max(0, $pengajuan->count() - $pengajuan->filter(fn ($p) => $aktif->where('pengajuan_id', $p->id)->isNotEmpty())->count());
        $wait = $pengajuan->map(function ($p) use ($aktif) { $j = $aktif->where('pengajuan_id', $p->id)->sortBy('tanggal')->first(); return $j ? max(0, $p->created_at->diffInDays($j->tanggal)) : null; })->filter();
        $ringkasan = [
            ['label' => 'Total pengajuan', 'nilai' => $pengajuan->count()],
            ['label' => 'Konseli unik', 'nilai' => $pengajuan->pluck('konseli_id')->filter()->unique()->count()],
            ['label' => 'Sesi selesai', 'nilai' => $selesai],
            ['label' => 'Belum terjadwal', 'nilai' => $menunggu],
            ['label' => 'Waktu tunggu rata-rata', 'nilai' => ($wait->count() ? round($wait->avg(), 1) : 0).' hari'],
        ];
        $periode = $semua ? 'Seluruh periode' : $dari->translatedFormat('d F Y').' – '.$sampai->translatedFormat('d F Y');
        $bulanMulai = $semua ? now()->startOfMonth()->subMonths(5) : $dari->copy()->startOfMonth();
        $bulanAkhir = $semua ? now()->startOfMonth() : $sampai->copy()->startOfMonth();
        $tren = collect();
        for ($m = $bulanMulai->copy(); $m->lte($bulanAkhir); $m->addMonth()) {
            $tren->push(['bulan' => $m->translatedFormat('M Y'), 'pengajuan' => $pengajuan->filter(fn ($p) => $p->created_at?->isSameMonth($m))->count(), 'sesi' => $jadwal->filter(fn ($j) => $j->created_at?->isSameMonth($m) && $j->status === 'selesai')->count()]);
        }
        $hitung = function (callable $fn) use ($pengajuan) {
            return $pengajuan->map($fn)
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->map(function ($v) {
                    if (is_array($v)) $v = implode(', ', array_filter(array_map(fn ($x) => is_scalar($x) ? (string) $x : '', $v)));
                    return trim((string) $v);
                })
                ->filter()
                ->countBy()->sortDesc();
        };
        $jenis = $hitung(fn ($p) => ucwords(str_replace('_', ' ', (string) ($p->data['jenis_konseli'] ?? $p->konseli?->jenis_konseli ?? 'Belum diisi'))));
        $prioritas = $hitung(fn ($p) => ucfirst((string) ($p->prioritas ?: 'Belum dihitung')));
        $mode = $hitung(fn ($p) => ucfirst((string) ($p->data['metode_layanan'] ?? $p->data['mode_konseling'] ?? 'Belum diisi')));
        $kategori = $hitung(fn ($p) => $p->data['kategori_masalah'] ?? $p->data['lingkup_hambatan'] ?? null);
        $gender = $hitung(function ($p) {
            $d = $p->data ?? []; $jenisKonseli = $d['jenis_konseli'] ?? $p->konseli?->jenis_konseli;
            return $jenisKonseli === 'wali_mahasiswa' ? (($d['wali_subjek_konseling'] ?? '') === 'mahasiswa' ? ($d['wali_mahasiswa_jenis_kelamin'] ?? null) : ($d['wali_jenis_kelamin'] ?? null)) : ($d['jenis_kelamin'] ?? $p->konseli?->jenis_kelamin);
        });
        $normalisasiFakultasEkspor = static function ($label): string {
            $k = strtolower(trim(preg_replace('/\s+/', ' ', (string) $label)));
            return match ($k) {
                'fakultas teknik', 'ft' => 'Fakultas Teknik (FT)',
                'fakultas bahasa dan seni', 'fbs' => 'Fakultas Bahasa dan Seni (FBS)',
                'fakultas matematika & ilmu pengetahuan alam', 'fmipa' => 'Fakultas Matematika & Ilmu Pengetahuan Alam (FMIPA)',
                'fakultas ilmu pendidikan dan psikologi', 'fipp' => 'Fakultas Ilmu Pendidikan dan Psikologi (FIPP)',
                default => (string) $label,
            };
        };
        $fakultas = $hitung(fn ($p) => $normalisasiFakultasEkspor($p->data['fakultas'] ?? $p->data['mahasiswa_fakultas'] ?? $p->data['wali_mahasiswa_fakultas'] ?? $p->data['tk_unit_kerja'] ?? null));
        $distribusi = collect(['Jenis Konseli' => $jenis, 'Jenis Kelamin' => $gender, 'Fakultas/Unit' => $fakultas, 'Mode Konseling' => $mode, 'Kategori Masalah' => $kategori, 'Prioritas SAW' => $prioritas])->mapWithKeys(function ($baris, $judul) use ($pengajuan) {
            $total = max(1, $pengajuan->count());
            return [$judul => $baris->map(fn ($jumlah, $label) => [(string) $label, (int) $jumlah, round($jumlah / $total * 100, 1)])->values()->all()];
        })->all();
        $statistik = [
            ['nilai' => $pengajuan->pluck('konseli_id')->filter()->unique()->count(), 'label' => 'Konseli', 'sub' => $periode, 'tone' => 'green', 'icon' => ''],
            ['nilai' => $pengajuan->count(), 'label' => 'Pengajuan', 'sub' => $periode, 'tone' => 'blue', 'icon' => ''],
            ['nilai' => $selesai, 'label' => 'Sesi selesai', 'sub' => $periode, 'tone' => 'purple', 'icon' => ''],
        ];
        $tren = $tren->map(fn ($t) => $t + ['konseli' => $t['pengajuan']])->all();
        $waktuTunggu = ['nilai' => ($wait->count() ? round($wait->avg(), 1) : 0), 'ket' => 'hari (pengajuan → sesi pertama)', 'rincian' => [['Total pengajuan', $pengajuan->count()], ['Sudah terjadwal', $pengajuan->count() - $menunggu], ['Belum terjadwal', $menunggu]]];
        $tidakHadir = ['nilai' => '0%', 'ket' => 'dari total sesi terjadwal', 'rincian' => []];
        $tiketMacet = [];
        $darurat = [];
        $konselor = [];
        $data = compact('periode', 'ringkasan', 'tren', 'distribusi');
        if ($request->query('jenis') === 'excel') {
            $html = view('koordinator.dashboard-ekspor-excel', $data)->render();
            return response($html, 200, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="dashboard-koordinator.xls"']);
        }
        if ($request->query('jenis') === 'pdf') {
            $lines = ['DASHBOARD ANALISIS KOORDINATOR', 'Periode: '.$periode, ''];
            foreach ($ringkasan as $r) $lines[] = $r['label'].': '.$r['nilai'];
            $lines[] = ''; $lines[] = 'TREN PENGAJUAN DAN SESI SELESAI';
            foreach ($tren as $t) $lines[] = $t['bulan'].' | Pengajuan: '.$t['pengajuan'].' '.str_repeat('#', min(40, (int) $t['pengajuan'])).' | Sesi selesai: '.$t['sesi'];
            $lines[] = ''; $lines[] = 'DISTRIBUSI JENIS KONSELI';
            foreach ($distribusi as $nama => $baris) { $lines[] = ''; $lines[] = strtoupper($nama); foreach ($baris as $row) { $label = $row[0] ?? ''; $jumlah = (int) ($row[1] ?? 0); $lines[] = $label.': '.$jumlah.' '.str_repeat('#', min(40, $jumlah)); } }
            // SimplePdf memakai font Helvetica/WinAnsi; sanitasi karakter non-ASCII agar PDF selalu valid dibuka.
            $lines = array_map(fn ($line) => preg_replace('/[^\x20-\x7E]/', '?', (string) $line), $lines);
            $pdf = SimplePdf::make($lines);
            return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="dashboard-koordinator.pdf"', 'Content-Length' => strlen($pdf)]);
        }
            return view('koordinator.dashboard-ekspor', compact('periode', 'statistik', 'tren', 'darurat', 'distribusi', 'waktuTunggu', 'tidakHadir', 'tiketMacet', 'konselor', 'distribusiMode'));
    }

    public function kasus(): View
    {
        $kasus = Pengajuan::with('konseli')->whereIn('data->kondisi_darurat', ['ya','mendesak','darurat'])->latest()->get()->map(function ($p) {
            $d = $p->data ?? []; return ['nama'=>$p->konseli?->nama ?? ($d['nama'] ?? 'Konseli'), 'kode'=>$p->kode_pengajuan,
                'peran'=>ucwords(str_replace('_',' ', $p->konseli?->jenis_konseli ?? ($d['jenis_konseli'] ?? 'Konseli'))),
                'kategori'=>$d['kategori_masalah'] ?? ($d['kategori'] ?? 'Konseling'), 'waktu'=>optional($p->created_at)->diffForHumans(),
                'whatsapp'=>$p->konseli?->telepon ?? ($d['telepon'] ?? ''), 'status'=>($d['darurat_status'] ?? '') === 'ditangani' ? 'ditangani' : 'mendesak'];
        })->values()->all();
        return view('koordinator.kasus', compact('kasus'));
        // Data dummy — nanti ganti dengan query tiket berstatus darurat.
        // status: 'mendesak' (belum ditangani) | 'ditangani'
        $kasus = [
            ['nama' => 'Nadia Putri Ramadhani', 'kode' => 'LP3-2026-000052', 'peran' => 'Mahasiswa', 'kategori' => 'Kesehatan Mental', 'waktu' => '25 menit lalu', 'whatsapp' => '6281234567801', 'status' => 'mendesak'],
            ['nama' => 'Rizky Aditya',          'kode' => 'LP3-2026-000049', 'peran' => 'Mahasiswa', 'kategori' => 'Pribadi',          'waktu' => '2 jam lalu',     'whatsapp' => '6281234567802', 'status' => 'mendesak'],
            ['nama' => 'Salsabila Nur Aini',    'kode' => 'LP3-2026-000047', 'peran' => 'Mahasiswa', 'kategori' => 'Kesehatan Mental', 'waktu' => 'Kemarin, 16.40', 'whatsapp' => '6281234567803', 'status' => 'ditangani'],
            ['nama' => 'Bambang Hartono',       'kode' => 'LP3-2026-000041', 'peran' => 'Pegawai/Staf', 'kategori' => 'Keluarga',      'waktu' => 'Kemarin, 09.15', 'whatsapp' => '6281234567804', 'status' => 'ditangani'],
            ['nama' => 'Dimas Prasetyo',        'kode' => 'LP3-2026-000038', 'peran' => 'Mahasiswa', 'kategori' => 'Asmara',           'waktu' => '2 hari lalu',    'whatsapp' => '6281234567805', 'status' => 'ditangani'],
            ['nama' => 'Anisa Rahmawati',       'kode' => 'LP3-2026-000034', 'peran' => 'Alumni',    'kategori' => 'Karier',           'waktu' => '3 hari lalu',    'whatsapp' => '6281234567806', 'status' => 'ditangani'],
            ['nama' => 'Fajar Nugroho',         'kode' => 'LP3-2026-000029', 'peran' => 'Mahasiswa', 'kategori' => 'Akademik',         'waktu' => '4 hari lalu',    'whatsapp' => '6281234567807', 'status' => 'ditangani'],
        ];

        return view('koordinator.kasus', compact('kasus'));
    }
    public function catatan(): View
    {
        // Koordinator hanya melihat ringkasan/statistik pengajuan. Isi catatan
        // konseling tetap menjadi data rahasia yang hanya dapat diakses konselor.
        $rows = Pengajuan::with(['konseli', 'jadwal.konselor', 'jadwal.catatanKonseling', 'jadwal.survei', 'penugasan.konselor'])
            ->latest()->get();

        $pengajuan = $rows->map(function ($p) {
            $data = (array) ($p->data ?? []);
            $jadwal = $p->jadwal->sortByDesc(function ($j) {
                return (optional($j->tanggal)->format('Y-m-d') ?: '0000-00-00') . ' ' .
                    (string) $j->mulai . ' ' . str_pad((string) $j->id, 12, '0', STR_PAD_LEFT);
            });
            $aktif = $jadwal->first(fn ($j) => ! in_array($j->status, ['dibatalkan', 'tidak_hadir'], true));
            $sesi = $jadwal->first();
            $nama = match ($data['jenis_konseli'] ?? $p->konseli?->jenis_konseli) {
                'mahasiswa' => data_get($data, 'mahasiswa_nama'),
                'alumni' => data_get($data, 'alumni_nama'),
                'tenaga_kependidikan' => data_get($data, 'tk_nama'),
                'wali_mahasiswa' => data_get($data, 'wali_subjek_konseling') === 'mahasiswa'
                    ? data_get($data, 'wali_mahasiswa_nama') : data_get($data, 'wali_nama'),
                default => null,
            } ?: $p->konseli?->nama ?: '-';
            $catatan = $sesi?->catatanKonseling;
            $hasil = $catatan?->hasil ?: $catatan?->tindak_lanjut;
            $hasil = $hasil ? str_replace('Tuntas — Tidak Perlu Tindak Lanjut', 'Tuntas', $hasil) : null;
            $status = str_replace('_', ' ', ucfirst((string) $p->status));
            return [
                'kode' => $p->kode_pengajuan,
                'konseli' => $nama,
                'konselor' => $aktif?->konselor?->name ?: $p->penugasan->sortByDesc('created_at')->first()?->konselor?->name,
                'tanggal' => $p->created_at?->translatedFormat('d M Y') ?: '-',
                'status' => $status,
                'status_tone' => $p->status === 'selesai' ? 'hijau' : (in_array($p->status, ['ditolak', 'dibatalkan'], true) ? 'abu' : 'kuning'),
                // Hanya metadata, bukan isi catatan.
                'catatan' => $catatan ? 'sudah' : 'belum',
                'hasil' => $hasil,
                'survei' => $aktif?->survei?->status === 'terisi' ? 'sudah' : ($aktif?->survei ? 'belum' : null),
            ];
        })->values();

        $statistik = [
            ['label' => 'Total Pengajuan', 'nilai' => $pengajuan->count(), 'tone' => 'hijau'],
            ['label' => 'Menunggu Tindakan', 'nilai' => $pengajuan->filter(fn ($p) => in_array(strtolower($p['status']), ['menunggu pemeriksaan', 'diterima', 'terjadwal'], true))->count(), 'tone' => 'kuning'],
            ['label' => 'Selesai', 'nilai' => $rows->where('status', 'selesai')->count(), 'tone' => 'terang'],
            ['label' => 'Ditolak/Dibatalkan', 'nilai' => $rows->whereIn('status', ['ditolak', 'dibatalkan'])->count(), 'tone' => 'merah'],
        ];
        return view('koordinator.ringkasan-pengajuan', [
            'pengajuan' => $pengajuan,
            'statistik' => $statistik,
            'opsiStatus' => $pengajuan->pluck('status')->unique()->values(),
            'opsiKonselor' => $pengajuan->pluck('konselor')->filter()->unique()->values(),
        ]);
        // Data dummy — nanti ganti dengan query konseli beserta sesi & catatannya.
        // Satu baris = satu konseli. Tiap sesi punya konselor, kategori, dan kode tiket sendiri.
        // 'catatan' => null berarti konselor belum mengisi catatan untuk sesi itu.
        $catatan = [
            [
                'nim'  => '2201416012',
                'nama' => 'Rani Kusuma',
                'sesi' => [
                    [
                        'tgl' => '4 Sep 2026', 'konselor' => 'Dr. Rina Kusumawati, M.Pd',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'darurat' => true,
                        'catatan' => "Konseli datang dengan keluhan sulit tidur dan cemas berlebihan sejak dua minggu terakhir. Konseli merasa kewalahan dengan tuntutan akademik dan masalah keluarga.\nDilakukan asesmen awal dan penjelasan tentang proses konseling. Konseli bersedia melanjutkan sesi berikutnya.",
                    ],
                    [
                        'tgl' => '11 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'darurat' => true,
                        'catatan' => "Konseling dilanjutkan oleh konselor lain atas penugasan koordinator. Konseli mulai terbuka menceritakan pemicu kecemasannya, terutama saat menghadapi ujian.\nDiajarkan teknik pernapasan dan grounding. Konseli diminta mencatat pola tidurnya selama seminggu.",
                    ],
                    [
                        'tgl' => '18 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Kesehatan Mental', 'kode' => 'LP3-2026-000089', 'darurat' => false,
                        'catatan' => "Kondisi membaik. Pola tidur mulai teratur dan frekuensi cemas menurun. Konseli sudah bisa menerapkan teknik pernapasan secara mandiri.\nDisepakati sesi tindak lanjut satu bulan lagi bila diperlukan.",
                    ],
                ],
            ],
            [
                'nim'  => '2101416088',
                'nama' => 'Doni Prasetyo',
                'sesi' => [
                    [
                        'tgl' => '8 Sep 2026', 'konselor' => 'Drs. Agus Santoso, M.Si',
                        'kategori' => 'Pribadi', 'kode' => 'LP3-2026-000076', 'darurat' => false,
                        'catatan' => "Konseli merasa kurang percaya diri dalam pergaulan dan sulit menyampaikan pendapat. Digali riwayat pengalaman yang membentuk pola tersebut.",
                    ],
                    [
                        'tgl' => '15 Sep 2026', 'konselor' => 'Drs. Agus Santoso, M.Si',
                        'kategori' => 'Pribadi', 'kode' => 'LP3-2026-000076', 'darurat' => false,
                        'catatan' => "Latihan asertif melalui bermain peran. Konseli mampu menyampaikan pendapat dengan lebih tenang. Diberi tugas praktik di lingkungan kelas.",
                    ],
                ],
            ],
            [
                'nim'  => '2301416034',
                'nama' => 'Sari Wulandari',
                'sesi' => [
                    [
                        'tgl' => '3 Sep 2026', 'konselor' => 'Dr. Hendra Pratama, M.Psi',
                        'kategori' => 'Akademik', 'kode' => 'LP3-2026-000061', 'darurat' => false,
                        'catatan' => "Konseli mengalami penurunan IPK dan sulit mengatur waktu belajar. Diidentifikasi kebiasaan menunda dan beban organisasi yang terlalu padat.",
                    ],
                    [
                        'tgl' => '10 Sep 2026', 'konselor' => 'Dr. Sari Dewi, M.Psi',
                        'kategori' => 'Akademik', 'kode' => 'LP3-2026-000061', 'darurat' => false,
                        'catatan' => "Disusun jadwal belajar mingguan bersama konseli dan dibahas prioritas kegiatan organisasi. Konseli berkomitmen mengurangi satu kepengurusan semester ini.",
                    ],
                ],
            ],
            [
                'nim'  => '2001416121',
                'nama' => 'Budi Santoso',
                'sesi' => [
                    [
                        'tgl' => '5 Sep 2026', 'konselor' => 'Dr. Rina Kusumawati, M.Pd',
                        'kategori' => 'Karier', 'kode' => 'LP3-2026-000054', 'darurat' => false,
                        'catatan' => null,
                    ],
                ],
            ],
        ];

        return view('koordinator.catatan-konseling', compact('catatan'));
    }
        public function kelompok(): View
    {
        $program = ProgramKonselingKelompok::withCount('peserta')->latest()->get()->map(fn ($p) => $this->mapProgramDb($p))->values()->all();
        $totalAktif = collect($program)->where('status', '!=', 'Selesai')->count();
        return view('koordinator.konseling-kelompok', compact('program', 'totalAktif'));
        $program = $this->daftarProgramKelompok();
 
        // Program yang belum berstatus "Selesai" dihitung aktif
        $totalAktif = collect($program)->where('status', '!=', 'Selesai')->count();
 
        return view('koordinator.konseling-kelompok', compact('program', 'totalAktif'));
    }
 
    public function simpanKelompok(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul'       => ['required', 'string', 'max:150'],
            'tanggal'     => ['required', 'date'],
            'waktu'       => ['required', 'date_format:H:i'],
            'lokasi'      => ['required', 'string', 'max:150'],
            'fasilitator' => ['required', 'string', 'max:150'],
            'kuota'       => ['required', 'integer', 'min:1', 'max:500'],
            'tautan_grup' => ['nullable', 'url', 'max:500'],
        ]);
 
        ProgramKonselingKelompok::create([...$data, 'tags' => [], 'status' => 'Pendaftaran Dibuka']);
        $this->audit('Buat Program Kelompok', 'Membuat program konseling kelompok "'.$data['judul'].'".');
        return redirect()->route('koordinator.kelompok')->with('sukses', 'Program "'.$data['judul'].'" berhasil dibuat.');

        $program = $this->daftarProgramKelompok();
        $idBaru  = (int) collect($program)->max('id') + 1;
 
        $program[] = $this->susunProgramKelompok(
            $idBaru, $data['judul'], $data['tanggal'], $data['waktu'],
            $data['lokasi'], $data['fasilitator'], (int) $data['kuota'], 0
        );
 
        session(['koordinator.program_kelompok' => $program]);
 
        return redirect()
            ->route('koordinator.kelompok')
            ->with('sukses', 'Program "' . $data['judul'] . '" berhasil dibuat.');
    }

    public function ubahKelompok(Request $request, int $id): RedirectResponse
    {
        $data=$request->validate(['judul'=>'required|string|max:150','tanggal'=>'required|date','waktu'=>'required|date_format:H:i','lokasi'=>'required|string|max:150','fasilitator'=>'required|string|max:150','kuota'=>'required|integer|min:1','tautan_grup'=>'nullable|url|max:500','status'=>'required|in:Pendaftaran Dibuka,Draft,Selesai']);
        $p=ProgramKonselingKelompok::findOrFail($id);
        if ($p->status === 'Selesai') return back()->with('gagal', 'Program yang sudah selesai hanya dapat melihat peserta atau dihapus.');
        $p->update($data); $this->audit('Ubah Program Kelompok','Memperbarui program "'.$p->judul.'".');
        return back()->with('sukses','Program berhasil diperbarui.');
    }

    public function hapusKelompok(int $id): RedirectResponse
    {
        $p=ProgramKonselingKelompok::findOrFail($id); $judul=$p->judul; $p->delete(); $this->audit('Hapus Program Kelompok','Menghapus program "'.$judul.'".');
        return back()->with('sukses','Program berhasil dihapus.');
    }
 
    public function pesertaKelompok(int $id): View
    {
        $p = ProgramKonselingKelompok::withCount('peserta')->findOrFail($id);
        $program = $this->mapProgramDb($p); $peserta = $p->peserta()->latest()->get()->map(fn ($x) => ['id'=>$x->id,'nama'=>$x->nama,'nim'=>$x->nim,'email'=>$x->email,'wa'=>$x->wa,'fakultas'=>$x->fakultas,'prodi'=>$x->prodi,'semester'=>$x->semester,'tgl_daftar'=>$x->created_at?->format('d M Y'),'status'=>$x->status,'status_tone'=>'hijau']);
        $kuota = $p->kuota; $terdaftar = $peserta->count(); $sisa = max($kuota - $terdaftar, 0);
        return view('koordinator.konseling-kelompok-peserta', compact('program','peserta','kuota','terdaftar','sisa'));
        $program = collect($this->daftarProgramKelompok())->firstWhere('id', $id);
        abort_if($program === null, 404);
 
        $peserta   = collect($this->pesertaKelompokDummy($program));
        $kuota     = $program['kuota'];
        $terdaftar = $program['terdaftar'];
        $sisa      = $program['sisa'];
 
        return view('koordinator.konseling-kelompok-peserta', compact('program', 'peserta', 'kuota', 'terdaftar', 'sisa'));
    }
 
    /** Program dari session; kalau belum ada, pakai data awal. */
    private function daftarProgramKelompok(): array
    {
        return session('koordinator.program_kelompok', $this->programKelompokAwal());
    }
 
    private function programKelompokAwal(): array
    {
        return [
            $this->susunProgramKelompok(1, 'Manajemen Waktu Efektif',               '20 Okt 2026', '09.00 – 12.00', 'Ruang Seminar LPPP', 'Dr. Sari Dewi, M.Psi',      20, 14),
            $this->susunProgramKelompok(2, 'Mengelola Kecemasan Menghadapi Ujian',  '27 Okt 2026', '13.00 – 15.30', 'Ruang Konseling 2',  'Dr. Hendra Pratama, M.Psi', 15, 14),
            $this->susunProgramKelompok(3, 'Public Speaking dan Percaya Diri',      '3 Nov 2026',  '09.00 – 11.30', 'Aula LPPP',          'Dra. Fatimah Zahra, M.Pd',  25, 18),
            $this->susunProgramKelompok(4, 'Mengelola Stres Akhir Semester',        '10 Sep 2026', '13.00 – 15.00', 'Ruang Seminar LPPP', 'Drs. Agus Santoso, M.Si',   12, 12, true),
        ];
    }
 
    /** Hitung sisa kuota, persentase, dan label status dari data dasar program. */
    private function susunProgramKelompok(
        int $id, string $judul, string $tanggal, string $waktu, string $lokasi,
        string $fasilitator, int $kuota, int $terdaftar, bool $selesai = false
    ): array {
        $sisa = max($kuota - $terdaftar, 0);
        $pct  = $kuota > 0 ? (int) round($terdaftar / $kuota * 100) : 0;
 
        if ($selesai) {
            [$status, $tone] = ['Selesai', 'abu'];
        } elseif ($pct >= 100) {
            [$status, $tone] = ['Penuh', 'kuning'];
        } elseif ($pct >= 90) {
            [$status, $tone] = ['Hampir Penuh', 'kuning'];
        } else {
            [$status, $tone] = ['Pendaftaran Dibuka', 'hijau'];
        }
 
        return [
            'id'            => $id,
            'judul'         => $judul,
            'status'        => $status,
            'status_tone'   => $tone,
            'tanggal'       => $tanggal,
            'waktu'         => $waktu,
            'lokasi'        => $lokasi,
            'fasilitator'   => $fasilitator,
            'kuota'         => $kuota,
            'terdaftar'     => $terdaftar,
            'sisa'          => $sisa,
            'pct'           => min($pct, 100),
            'progress_tone' => (! $selesai && $pct >= 90) ? 'merah' : 'hijau',
        ];
    }
 
    /** Peserta dummy; jumlahnya mengikuti kolom "terdaftar" pada program. */
    private function pesertaKelompokDummy(array $program): array
    {
        $pool = [
            ['Aulia Rahma',       '2201416005', 'FIPP',  'Bimbingan dan Konseling',        5],
            ['Bagas Setiawan',    '2201416019', 'FT',    'Teknik Informatika',             5],
            ['Citra Lestari',     '2301416027', 'FE',    'Manajemen',                      3],
            ['Dewi Anggraini',    '2101416043', 'FIS',   'Pendidikan Sosiologi',           7],
            ['Eko Prabowo',       '2301416051', 'FMIPA', 'Biologi',                        3],
            ['Farah Nabila',      '2201416062', 'FIPP',  'PGSD',                           5],
            ['Galih Permana',     '2101416070', 'FT',    'Teknik Sipil',                   7],
            ['Hana Safitri',      '2401416008', 'FIS',   'Ilmu Sejarah',                   1],
            ['Irfan Maulana',     '2301416085', 'FE',    'Akuntansi',                      3],
            ['Jihan Kamila',      '2201416091', 'FMIPA', 'Matematika',                     5],
            ['Kevin Alfarizi',    '2401416014', 'FT',    'Pendidikan Teknik Mesin',        1],
            ['Laila Zulfa',       '2101416102', 'FIPP',  'Pendidikan Guru PAUD',           7],
            ['Mario Ardiansyah',  '2301416113', 'FIS',   'Pendidikan Pancasila dan Kewarganegaraan', 3],
            ['Nisa Azzahra',      '2201416120', 'FE',    'Pendidikan Ekonomi',             5],
            ['Oki Firmansyah',    '2101416131', 'FMIPA', 'Fisika',                         7],
            ['Putri Amelia',      '2401416027', 'FIPP',  'Bimbingan dan Konseling',        1],
            ['Rendra Wijaya',     '2301416139', 'FT',    'Teknik Elektro',                 3],
            ['Salma Khairunnisa', '2201416147', 'FIS',   'Pendidikan Geografi',            5],
        ];
 
        // Tanggal pendaftaran terakhir per program [bulan, hari] — semuanya 2026
        $tglAkhir = [1 => [9, 22], 2 => [9, 20], 3 => [9, 24], 4 => [9, 5]];
        [$bln, $hari] = $tglAkhir[$program['id']] ?? [9, 24];
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
 
        $selesai = $program['status'] === 'Selesai';
        $jumlah  = min($program['terdaftar'], count($pool));
        $hasil   = [];
 
        for ($i = 0; $i < $jumlah; $i++) {
            [$nama, $nim, $fakultas, $prodi, $smt] = $pool[$i];
 
            // mktime menormalkan hari negatif ke bulan sebelumnya
            $ts  = mktime(0, 0, 0, $bln, $hari - intdiv($i, 3), 2026);
            $tgl = date('j', $ts) . ' ' . $namaBulan[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
 
            if ($selesai) {
                [$status, $tone] = (($i + 1) % 6 === 0) ? ['Tidak Hadir', 'merah'] : ['Hadir', 'biru'];
            } else {
                [$status, $tone] = (($i + 1) % 5 === 0) ? ['Menunggu Konfirmasi', 'kuning'] : ['Terdaftar', 'hijau'];
            }
 
            $hasil[] = [
                'kode'        => sprintf('KLP-2026-%d%02d', $program['id'], $i + 1),
                'nama'        => $nama,
                'nim'         => $nim,
                'fakultas'    => $fakultas,
                'prodi'       => $prodi,
                'semester'    => $smt,
                'tgl_daftar'  => $tgl,
                'status'      => $status,
                'status_tone' => $tone,
            ];
        }
 
        return $hasil;
    }
        /** Kunci session tempat menyimpan dummy "database" materi self help. */
    private const SESSION_KEY_MATERI = 'koordinator_materi_selfhelp';
 
    public function selfhelp(): View
    {
        $materi = SelfHelp::with('author')->where('tipe', 'Modul')->latest()->get()->map(fn ($m) => ['id'=>$m->id,'title'=>$m->judul,'type'=>strtolower($m->tipe),'topic'=>$m->topik,'status'=>$m->status,'author'=>$m->author?->name,'date'=>$m->created_at?->translatedFormat('d M Y'),'isi'=>$m->detail['isi'] ?? '','link_youtube'=>$m->detail['link_youtube'] ?? '','keterangan'=>$m->detail['keterangan'] ?? '','pertanyaan'=>$m->detail['pertanyaan'] ?? [],'blok'=>$m->detail['blok'] ?? [],'file_pdf'=>$m->file_pdf ?: ($m->detail['file_pdf'] ?? null),'jumlah_halaman'=>$m->detail['jumlah_halaman'] ?? null])->values()->all();
        $hasil = SelfHelpPengerjaan::with('materi')->whereHas('materi', fn ($q) => $q->where('tipe', 'Modul'))->latest()->get()->map(function ($h) {
            $pertanyaan = $this->pertanyaanSelfHelp($h->materi);
            $jawaban = collect($h->jawaban ?? [])->values()->map(function ($jawab, $i) use ($pertanyaan) {
                $q = $pertanyaan[$i] ?? [];
                return ['pertanyaan' => $q['teks'] ?? $q['label'] ?? ('Pertanyaan ' . ($i + 1)), 'jawaban' => $jawab];
            })->all();
            $waktu = $h->selesai_at ?: $h->created_at;
            return ['id'=>$h->id,'nama'=>$h->nama ?? '','nim'=>$h->nim ?? '','fakultas'=>$h->fakultas ?? '-','prodi'=>$h->prodi ?? '-',
                'worksheet_judul'=>$h->materi?->judul ?? '-', 'tanggal'=>$waktu?->translatedFormat('d F Y H:i'),
                'minggu_ini'=>$waktu?->isSameWeek(now()) ?? false, 'jawaban'=>$jawaban];
        })->values()->all();
        $statistikMateri = [
            ['nilai' => count($materi), 'label' => 'Total Modul', 'sub' => 'materi terpadu', 'tone' => 'green',
             'icon' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>'],
            ['nilai' => collect($materi)->where('type', 'modul')->count(), 'label' => 'Modul', 'sub' => 'materi terpadu', 'tone' => 'blue',
             'icon' => '<path d="M4 5a2 2 0 0 1 2-2h14v17H6a2 2 0 0 0-2 2V5Z"/><path d="M4 20a2 2 0 0 1 2-2h14"/>'],
        ];

        $terpopuler = collect($hasil)->countBy('worksheet_judul')->sortDesc();

        $statistikHasil = [
            ['nilai' => count($hasil), 'label' => 'Total Pengerjaan', 'sub' => 'seluruh modul', 'tone' => 'green',
             'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
            ['nilai' => collect($hasil)->where('minggu_ini', true)->count(), 'label' => 'Pengerjaan Minggu Ini',
             'sub' => now()->startOfWeek()->translatedFormat('d M') . ' – ' . now()->endOfWeek()->translatedFormat('d M Y'), 'tone' => 'blue',
             'icon' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 2v4"/><path d="M16 2v4"/>'],
            ['nilai' => ($terpopuler->first() ?? 0) . 'x', 'label' => 'Modul Terpopuler', 'sub' => $terpopuler->keys()->first() ?? 'Belum ada data', 'tone' => 'pink',
             'icon' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>'],
        ];
        $worksheetList = collect($materi)->where('type','modul')->pluck('title')->values();
        return view('koordinator.self-help', compact('materi','hasil','statistikMateri','statistikHasil','worksheetList'));
    }
 
    /**
     * Simpan materi baru (dipanggil dari modal "Tambah Materi").
     */
    private function pertanyaanSelfHelp(?SelfHelp $materi): array
    {
        if (!$materi) return [];
        $detail = $materi->detail ?? [];
        $lama = $detail['pertanyaan'] ?? $detail['questions'] ?? [];
        if (is_array($lama) && count($lama)) return array_values($lama);
        return collect($detail['blok'] ?? [])->filter(fn ($b) => is_array($b) && ($b['tipe'] ?? '') === 'soal')
            ->flatMap(function ($b) {
                return collect(preg_split('/\r?\n+/', trim((string) ($b['pertanyaan'] ?? $b['isi'] ?? ''))))
                    ->filter(fn ($teks) => trim($teks) !== '')
                    ->map(fn ($teks) => ['teks' => trim($teks)]);
            })->values()->all();
    }

    public function simpanMateriSelfHelp(Request $request): RedirectResponse
    {
        $data = $this->validasiMateri($request);
        if ($data['type'] === 'worksheet' && ! $request->hasFile('file_pdf')) {
            return back()->withErrors(['file_pdf' => 'File PDF wajib diunggah untuk worksheet.'])->withInput();
        }
        $detail = ['isi'=>$data['isi'] ?? null,'link_youtube'=>$data['link_youtube'] ?? null,'keterangan'=>$data['keterangan'] ?? null];
        if ($data['type'] === 'worksheet') {
            $detail['file_pdf'] = $request->file('file_pdf')->store('self-help/worksheet', 'public');
            $detail['pertanyaan'] = collect($data['pertanyaan'])->map(fn ($q) => ['teks'=>trim($q['teks']), 'halaman'=>(int) $q['halaman']])->values()->all();
            $detail['jumlah_halaman'] = $this->hitungHalamanPdf($detail['file_pdf']) ?: ($data['jumlah_halaman'] ?? null);
        } elseif ($data['type'] === 'modul') {
            $detail = ['blok' => $this->prosesBlokPdfModul(json_decode($data['blok_json'], true) ?: [], $request)];
        }
        SelfHelp::create(['judul'=>$data['title'],'tipe'=>ucfirst($data['type']),'topik'=>$data['topic'],'status'=>$data['status'],'author_id'=>auth()->id(),'detail'=>$detail,'file_pdf'=>$detail['file_pdf'] ?? null]);
        $this->audit('Buat Self Help', 'Membuat materi '.$data['type'].' "'.$data['title'].'".');
        return back()->with('sukses', 'Materi "'.$data['title'].'" berhasil ditambahkan.');
 
        $materi   = $this->ambilMateriSelfHelp();
        $idBaru   = count($materi) ? max(array_column($materi, 'id')) + 1 : 1;
        $materi[] = array_merge($data, [
            'id'   => $idBaru,
            'date' => now()->translatedFormat('j M Y'),
        ]);
 
        $this->simpanMateriSelfHelpKeSession($materi);
 
        return back()->with('sukses', 'Materi "' . $data['title'] . '" berhasil ditambahkan.');
    }
 
    /**
     * Perbarui materi yang sudah ada (dipanggil dari modal "Edit Materi").
     */
    public function updateMateriSelfHelp(Request $request, int $id): RedirectResponse
    {
        $data   = $this->validasiMateri($request);
        $materiDb = SelfHelp::findOrFail($id);
        $lamaDetail = $materiDb->detail ?? [];
        $detail = ['isi'=>$data['isi'] ?? null,'link_youtube'=>$data['link_youtube'] ?? null,'keterangan'=>$data['keterangan'] ?? null];
        if ($data['type'] === 'worksheet') {
            $detail['file_pdf'] = $materiDb->file_pdf ?: ($lamaDetail['file_pdf'] ?? null);
            if ($request->hasFile('file_pdf')) {
                if ($detail['file_pdf'] && !preg_match('/^https?:\\/\\//i', $detail['file_pdf'])) Storage::disk('public')->delete($detail['file_pdf']);
                $detail['file_pdf'] = $request->file('file_pdf')->store('self-help/worksheet', 'public');
            }
            $detail['pertanyaan'] = collect($data['pertanyaan'] ?? ($lamaDetail['pertanyaan'] ?? []))->map(fn ($q) => ['teks'=>trim($q['teks']), 'halaman'=>(int) $q['halaman']])->values()->all();
            $detail['jumlah_halaman'] = $data['jumlah_halaman'] ?? ($lamaDetail['jumlah_halaman'] ?? null);
        } elseif ($data['type'] === 'modul') {
            $detail = ['blok' => $this->prosesBlokPdfModul(json_decode($data['blok_json'], true) ?: [], $request, $lamaDetail['blok'] ?? [])];
        } else {
            if ($materiDb->file_pdf && !preg_match('/^https?:\\/\\//i', $materiDb->file_pdf)) Storage::disk('public')->delete($materiDb->file_pdf);
        }
        $materiDb->update(['judul'=>$data['title'],'tipe'=>ucfirst($data['type']),'topik'=>$data['topic'],'status'=>$data['status'],'detail'=>$detail,'file_pdf'=>$detail['file_pdf'] ?? $materiDb->file_pdf]);
        $this->audit('Ubah Self Help', 'Mengubah materi self-help "'.$data['title'].'".');
        return back()->with('sukses', 'Materi "'.$data['title'].'" berhasil diperbarui.');
        $materi = $this->ambilMateriSelfHelp();
 
        $ditemukan = false;
        foreach ($materi as $i => $m) {
            if ((int) $m['id'] === $id) {
                $materi[$i] = array_merge($m, $data);
                $ditemukan  = true;
                break;
            }
        }
 
        if (! $ditemukan) {
            return back()->with('gagal', 'Materi tidak ditemukan.');
        }
 
        $this->simpanMateriSelfHelpKeSession($materi);
 
        return back()->with('sukses', 'Materi "' . $data['title'] . '" berhasil diperbarui.');
    }
 
    /**
     * Hapus materi.
     */
    public function hapusMateriSelfHelp(int $id): RedirectResponse
    {
        $materiDb = SelfHelp::findOrFail($id);
        $judul = $materiDb->judul;
        foreach ([$materiDb->file_pdf, $materiDb->thumbnail, data_get($materiDb->detail, 'file_pdf')] as $path) {
            if ($path && !preg_match('/^https?:\\/\\//i', $path)) Storage::disk('public')->delete($path);
        }
        $materiDb->delete();
        $this->audit('Hapus Self Help', 'Menghapus materi self-help "'.$judul.'".');
        return back()->with('sukses', 'Materi berhasil dihapus.');
        $materi = $this->ambilMateriSelfHelp();
        $materi = array_values(array_filter($materi, fn ($m) => (int) $m['id'] !== $id));
 
        $this->simpanMateriSelfHelpKeSession($materi);
 
        return back()->with('sukses', 'Materi berhasil dihapus.');
    }

    private function mapProgramDb(ProgramKonselingKelompok $p): array
    {
        $n = $p->peserta_count ?? $p->peserta()->count(); $pct = $p->kuota ? min((int) round($n / $p->kuota * 100), 100) : 0;
        $status = $p->status;
        try { if ($p->tanggal && $this->parseTanggal($p->tanggal)?->isPast()) $status = 'Selesai'; } catch (\Throwable $e) { /* tanggal lama tetap memakai status tersimpan */ }
        if ($status !== $p->status && $status === 'Selesai') $p->update(['status' => $status]);
        return ['id'=>$p->id,'judul'=>$p->judul,'status'=>$status,'status_tone'=>$status==='Selesai'?'abu':($pct>=90?'kuning':'hijau'),'tanggal'=>$p->tanggal,'waktu'=>$p->waktu,'lokasi'=>$p->lokasi,'fasilitator'=>$p->fasilitator,'tautan_grup'=>$p->tautan_grup,'kuota'=>$p->kuota,'terdaftar'=>$n,'sisa'=>max($p->kuota-$n,0),'pct'=>$pct,'progress_tone'=>$pct>=90?'merah':'hijau'];
    }

    private function hitungHalamanPdf(?string $path): ?int
    {
        if (!$path || !Storage::disk('public')->exists($path)) return null;
        $contents = @file_get_contents(Storage::disk('public')->path($path));
        if (!$contents) return null;
        $count = preg_match_all('/\/Type\s*\/Page\b/', $contents, $matches);
        return $count > 0 ? $count : null;
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
                if (!empty($previous['file_pdf']) && $previous['file_pdf'] !== $path && !preg_match('/^https?:\\/\\//i', $previous['file_pdf'])) Storage::disk('public')->delete($previous['file_pdf']);
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
            if ($oldPath && !in_array($oldPath, $kept, true) && !preg_match('/^https?:\\/\\//i', $oldPath)) Storage::disk('public')->delete($oldPath);
        }
        return $result;
    }

    private function parseTanggal(?string $tanggal): ?Carbon
    {
        if (!$tanggal) return null;
        $id = ['Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April','Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August','September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December','Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','Jun'=>'Jun','Jul'=>'Jul','Agu'=>'Aug','Sep'=>'Sep','Okt'=>'Oct','Nov'=>'Nov','Des'=>'Dec'];
        return Carbon::parse(str_ireplace(array_keys($id), array_values($id), $tanggal));
    }
 
    /**
     * Validasi form tambah/edit materi. Field yang wajib diisi menyesuaikan
     * jenis materi yang dipilih (artikel / video / worksheet).
     */
    private function validasiMateri(Request $request): array
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:modul',
            'topic'        => 'required|string|max:100',
            'status'       => 'required|in:Dipublikasi,Draft',
            'isi'          => 'required_if:type,artikel|nullable|string',
            'link_youtube' => 'required_if:type,video|nullable|url',
            'keterangan'   => 'required_if:type,video|nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'file_pdf_blok' => 'sometimes|array',
            'file_pdf_blok.*' => 'file|mimes:pdf|max:10240',
            'pertanyaan' => 'required_if:type,worksheet|nullable|array|min:1',
            'pertanyaan.*.teks' => 'required|string|max:1000',
            'pertanyaan.*.halaman' => 'required|integer|min:1',
            'jumlah_halaman' => 'nullable|integer|min:1',
            'blok_json' => 'required_if:type,modul|nullable|json',
        ]);
        if (($data['type'] ?? null) === 'worksheet' && !empty($data['jumlah_halaman'])) {
            foreach (($data['pertanyaan'] ?? []) as $i => $q) {
                if ((int) $q['halaman'] > (int) $data['jumlah_halaman']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "pertanyaan.$i.halaman" => 'Halaman pertanyaan tidak boleh melebihi jumlah halaman PDF (' . $data['jumlah_halaman'] . ').',
                    ]);
                }
            }
        }
        return $data;
    }
 
    /**
     * Ambil daftar materi dari session, isi dengan data dummy default kalau
     * session masih kosong (pertama kali dibuka).
     */
    private function ambilMateriSelfHelp(): array
    {
        if (! session()->has(self::SESSION_KEY_MATERI)) {
            session([self::SESSION_KEY_MATERI => $this->materiSelfHelpDummy()]);
        }
 
        return session(self::SESSION_KEY_MATERI);
    }
 
    private function simpanMateriSelfHelpKeSession(array $materi): void
    {
        session([self::SESSION_KEY_MATERI => $materi]);
    }
 
    /**
     * Data dummy materi self help — nanti ganti dengan query tabel materi.
     * ID di sini harus sama dengan ID pada halaman publik /self-help/{id}
     * (resources/views/self-help.blade.php & self-help-detail.blade.php).
     *
     * Kolom 'author' tidak lagi ditampilkan di tabel koordinator (diganti
     * 'status'), tapi tetap disimpan kalau masih dipakai halaman publik.
     */
    private function materiSelfHelpDummy(): array
    {
        return [
            ['id' => 1, 'type' => 'artikel',   'topic' => 'Kesehatan Mental', 'author' => 'Dr. Sari Dewi',        'status' => 'Dipublikasi', 'date' => '3 Sep 2026',  'title' => 'Mengatasi Kecemasan Ujian: Panduan Praktis untuk Mahasiswa'],
            ['id' => 2, 'type' => 'video',     'topic' => 'Akademik',         'author' => 'Dr. Rina Kusumawati',  'status' => 'Dipublikasi', 'date' => '28 Agu 2026', 'title' => 'Teknik Pomodoro: Belajar Efektif dengan Manajemen Waktu'],
            ['id' => 3, 'type' => 'worksheet', 'topic' => 'Kesehatan Mental', 'author' => 'Dr. Sari Dewi',        'status' => 'Dipublikasi', 'date' => '20 Agu 2026', 'title' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri'],
            ['id' => 4, 'type' => 'artikel',   'topic' => 'Pribadi',          'author' => 'Drs. Agus Santoso',    'status' => 'Draft',       'date' => '15 Agu 2026', 'title' => 'Membangun Kepercayaan Diri: Panduan Pengembangan Diri'],
            ['id' => 5, 'type' => 'video',     'topic' => 'Kesehatan Mental', 'author' => 'Dr. Sari Dewi',        'status' => 'Dipublikasi', 'date' => '10 Agu 2026', 'title' => 'Teknik Pernapasan untuk Mengelola Stres Seketika'],
            ['id' => 6, 'type' => 'worksheet', 'topic' => 'Karier',           'author' => 'Dr. Rina Kusumawati',  'status' => 'Dipublikasi', 'date' => '5 Agu 2026',  'title' => 'Refleksi Karier: Menemukan Arah Setelah Lulus'],
            ['id' => 7, 'type' => 'artikel',   'topic' => 'Sosial',           'author' => 'Drs. Agus Santoso',    'status' => 'Draft',       'date' => '29 Jul 2026', 'title' => 'Seni Berkomunikasi Asertif di Lingkungan Kampus'],
            ['id' => 8, 'type' => 'video',     'topic' => 'Karier',           'author' => 'Dr. Rina Kusumawati',  'status' => 'Dipublikasi', 'date' => '20 Jul 2026', 'title' => 'Panduan Menulis CV dan Surat Lamaran yang Memikat'],
            ['id' => 9, 'type' => 'worksheet', 'topic' => 'Akademik',         'author' => 'Prof. Bambang Widodo', 'status' => 'Dipublikasi', 'date' => '12 Jul 2026', 'title' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu'],
        ];
    }
 
    /**
     * Data dummy hasil pengerjaan worksheet mahasiswa — nanti ganti dengan
     * query tabel hasil worksheet (relasi ke mahasiswa & materi worksheet).
     * (Tidak diubah — bagian ini tetap seperti sebelumnya.)
     */
    private function hasilWorksheetDummy(): array
    {
        return [
            [
                'nama' => 'Aulia Rahma', 'nim' => '2201416005', 'fakultas' => 'FIPP', 'prodi' => 'Bimbingan dan Konseling',
                'worksheet_id' => 3, 'worksheet_judul' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri',
                'tanggal' => '24 Sep 2026, 09.10', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Bagaimana perasaanmu hari ini? Coba gambarkan dengan 3 kata.', 'jawaban' => 'Lelah, tenang, lega.'],
                    ['pertanyaan' => 'Apa satu hal yang membuatmu bersyukur hari ini?', 'jawaban' => 'Bersyukur akhirnya presentasi kelompok selesai dengan baik.'],
                    ['pertanyaan' => 'Momen apa yang terasa paling menantang hari ini, dan bagaimana kamu meresponsnya?', 'jawaban' => 'Deadline laporan praktikum yang mepet; aku coba cicil dari pagi supaya tidak begadang.'],
                    ['pertanyaan' => 'Apa yang sedang kamu pikirkan atau khawatirkan saat ini?', 'jawaban' => 'Masih memikirkan nilai UTS minggu depan.'],
                    ['pertanyaan' => 'Satu langkah kecil apa yang bisa kamu lakukan besok untuk merawat dirimu sendiri?', 'jawaban' => 'Tidur lebih awal dan menyiapkan bahan belajar dari malam ini.'],
                ],
            ],
            [
                'nama' => 'Bagas Setiawan', 'nim' => '2201416019', 'fakultas' => 'FT', 'prodi' => 'Teknik Informatika',
                'worksheet_id' => 9, 'worksheet_judul' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu',
                'tanggal' => '24 Sep 2026, 08.45', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Apa 3 prioritas utamamu minggu ini?', 'jawaban' => 'Menyelesaikan proyek pemrograman, belajar untuk kuis, dan ikut rapat UKM.'],
                    ['pertanyaan' => 'Tugas atau deadline apa saja yang perlu diselesaikan minggu ini?', 'jawaban' => 'Deadline tugas basis data hari Jumat.'],
                    ['pertanyaan' => 'Kapan waktu terbaikmu untuk fokus belajar (pagi, siang, malam)? Mengapa?', 'jawaban' => 'Malam hari, karena kos lebih tenang dan aku lebih fokus.'],
                    ['pertanyaan' => 'Apa yang biasanya membuat rencanamu berantakan, dan bagaimana mengantisipasinya?', 'jawaban' => 'Sering kebablasan main game; aku pasang timer supaya tidak kebablasan.'],
                    ['pertanyaan' => 'Kapan kamu akan menjadwalkan waktu istirahat/me-time minggu ini?', 'jawaban' => 'Sabtu sore untuk main futsal bareng teman kos.'],
                ],
            ],
            [
                'nama' => 'Citra Lestari', 'nim' => '2301416027', 'fakultas' => 'FE', 'prodi' => 'Manajemen',
                'worksheet_id' => 6, 'worksheet_judul' => 'Refleksi Karier: Menemukan Arah Setelah Lulus',
                'tanggal' => '23 Sep 2026, 20.12', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Aktivitas apa yang membuatmu merasa paling bersemangat selama kuliah?', 'jawaban' => 'Menyusun laporan keuangan dan analisis data penjualan saat magang.'],
                    ['pertanyaan' => 'Sebutkan 3 kekuatan atau keahlian yang paling kamu banggakan.', 'jawaban' => 'Teliti, komunikatif, dan cepat belajar hal baru.'],
                    ['pertanyaan' => 'Bidang pekerjaan atau industri apa yang selama ini menarik perhatianmu?', 'jawaban' => 'Bidang keuangan perusahaan dan konsultan bisnis.'],
                    ['pertanyaan' => 'Apa kekhawatiran terbesarmu soal dunia kerja setelah lulus?', 'jawaban' => 'Takut kalah bersaing dengan lulusan dari kampus lain yang lebih dikenal.'],
                    ['pertanyaan' => 'Siapa sosok (role model) yang jalur kariernya ingin kamu pelajari, dan mengapa?', 'jawaban' => 'CFO startup lokal yang sering aku ikuti kontennya di LinkedIn.'],
                    ['pertanyaan' => 'Satu langkah konkret apa yang bisa kamu ambil bulan ini untuk mendekati tujuan kariermu?', 'jawaban' => 'Ikut sertifikasi dasar akuntansi bulan ini.'],
                ],
            ],
            [
                'nama' => 'Dewi Anggraini', 'nim' => '2101416043', 'fakultas' => 'FIS', 'prodi' => 'Pendidikan Sosiologi',
                'worksheet_id' => 3, 'worksheet_judul' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri',
                'tanggal' => '23 Sep 2026, 16.30', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Bagaimana perasaanmu hari ini? Coba gambarkan dengan 3 kata.', 'jawaban' => 'Cemas, bingung, berharap.'],
                    ['pertanyaan' => 'Apa satu hal yang membuatmu bersyukur hari ini?', 'jawaban' => 'Bersyukur masih bisa video call dengan keluarga di rumah.'],
                    ['pertanyaan' => 'Momen apa yang terasa paling menantang hari ini, dan bagaimana kamu meresponsnya?', 'jawaban' => 'Rapat organisasi yang molor sampai malam; aku coba tetap fokus dan tidak terbawa emosi.'],
                    ['pertanyaan' => 'Apa yang sedang kamu pikirkan atau khawatirkan saat ini?', 'jawaban' => 'Memikirkan tugas akhir yang belum menemukan topik.'],
                    ['pertanyaan' => 'Satu langkah kecil apa yang bisa kamu lakukan besok untuk merawat dirimu sendiri?', 'jawaban' => 'Meluangkan 15 menit untuk jalan santai sore hari.'],
                ],
            ],
            [
                'nama' => 'Eko Prabowo', 'nim' => '2301416051', 'fakultas' => 'FMIPA', 'prodi' => 'Biologi',
                'worksheet_id' => 9, 'worksheet_judul' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu',
                'tanggal' => '22 Sep 2026, 21.05', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Apa 3 prioritas utamamu minggu ini?', 'jawaban' => 'Praktikum lab, revisi laporan, dan belajar untuk responsi.'],
                    ['pertanyaan' => 'Tugas atau deadline apa saja yang perlu diselesaikan minggu ini?', 'jawaban' => 'Laporan praktikum biologi molekuler harus dikumpul Rabu.'],
                    ['pertanyaan' => 'Kapan waktu terbaikmu untuk fokus belajar (pagi, siang, malam)? Mengapa?', 'jawaban' => 'Pagi hari sebelum kelas, karena pikiran masih segar.'],
                    ['pertanyaan' => 'Apa yang biasanya membuat rencanamu berantakan, dan bagaimana mengantisipasinya?', 'jawaban' => 'Sering menunda karena capek habis praktikum; aku coba cicil laporan langsung setelah lab selesai.'],
                    ['pertanyaan' => 'Kapan kamu akan menjadwalkan waktu istirahat/me-time minggu ini?', 'jawaban' => 'Jumat malam untuk nonton film santai.'],
                ],
            ],
            [
                'nama' => 'Farah Nabila', 'nim' => '2201416062', 'fakultas' => 'FIPP', 'prodi' => 'PGSD',
                'worksheet_id' => 3, 'worksheet_judul' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri',
                'tanggal' => '21 Sep 2026, 07.55', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Bagaimana perasaanmu hari ini? Coba gambarkan dengan 3 kata.', 'jawaban' => 'Semangat, sedikit lelah, optimis.'],
                    ['pertanyaan' => 'Apa satu hal yang membuatmu bersyukur hari ini?', 'jawaban' => 'Bersyukur dosen memberi perpanjangan waktu pengumpulan tugas.'],
                    ['pertanyaan' => 'Momen apa yang terasa paling menantang hari ini, dan bagaimana kamu meresponsnya?', 'jawaban' => 'Kesulitan memahami materi statistik; aku bertanya ke teman sekelas.'],
                    ['pertanyaan' => 'Apa yang sedang kamu pikirkan atau khawatirkan saat ini?', 'jawaban' => 'Khawatir soal jadwal PPL yang bertabrakan dengan organisasi.'],
                    ['pertanyaan' => 'Satu langkah kecil apa yang bisa kamu lakukan besok untuk merawat dirimu sendiri?', 'jawaban' => 'Membuat daftar prioritas tugas minggu ini.'],
                ],
            ],
            [
                'nama' => 'Galih Permana', 'nim' => '2101416070', 'fakultas' => 'FT', 'prodi' => 'Teknik Sipil',
                'worksheet_id' => 6, 'worksheet_judul' => 'Refleksi Karier: Menemukan Arah Setelah Lulus',
                'tanggal' => '14 Sep 2026, 13.20', 'minggu_ini' => false,
                'jawaban' => [
                    ['pertanyaan' => 'Aktivitas apa yang membuatmu merasa paling bersemangat selama kuliah?', 'jawaban' => 'Merancang dan menghitung struktur bangunan untuk tugas studio.'],
                    ['pertanyaan' => 'Sebutkan 3 kekuatan atau keahlian yang paling kamu banggakan.', 'jawaban' => 'Detail, sabar, dan suka memecahkan masalah teknis.'],
                    ['pertanyaan' => 'Bidang pekerjaan atau industri apa yang selama ini menarik perhatianmu?', 'jawaban' => 'Konsultan struktur dan manajemen proyek konstruksi.'],
                    ['pertanyaan' => 'Apa kekhawatiran terbesarmu soal dunia kerja setelah lulus?', 'jawaban' => 'Khawatir belum punya pengalaman kerja lapangan yang cukup.'],
                    ['pertanyaan' => 'Siapa sosok (role model) yang jalur kariernya ingin kamu pelajari, dan mengapa?', 'jawaban' => 'Senior alumni yang sekarang jadi project manager di kontraktor BUMN.'],
                    ['pertanyaan' => 'Satu langkah konkret apa yang bisa kamu ambil bulan ini untuk mendekati tujuan kariermu?', 'jawaban' => 'Melamar magang di kontraktor lokal bulan ini.'],
                ],
            ],
            [
                'nama' => 'Hana Safitri', 'nim' => '2401416008', 'fakultas' => 'FIS', 'prodi' => 'Ilmu Sejarah',
                'worksheet_id' => 9, 'worksheet_judul' => 'Worksheet Manajemen Waktu: Rencanakan Minggumu',
                'tanggal' => '12 Sep 2026, 11.40', 'minggu_ini' => false,
                'jawaban' => [
                    ['pertanyaan' => 'Apa 3 prioritas utamamu minggu ini?', 'jawaban' => 'Membaca referensi skripsi, menyusun outline, dan bimbingan dosen.'],
                    ['pertanyaan' => 'Tugas atau deadline apa saja yang perlu diselesaikan minggu ini?', 'jawaban' => 'Draf bab 2 skripsi harus dikirim ke dosen pembimbing.'],
                    ['pertanyaan' => 'Kapan waktu terbaikmu untuk fokus belajar (pagi, siang, malam)? Mengapa?', 'jawaban' => 'Siang hari setelah makan siang, saat kos sepi.'],
                    ['pertanyaan' => 'Apa yang biasanya membuat rencanamu berantakan, dan bagaimana mengantisipasinya?', 'jawaban' => 'Sering terdistraksi media sosial; aku coba simpan HP di ruang lain saat menulis.'],
                    ['pertanyaan' => 'Kapan kamu akan menjadwalkan waktu istirahat/me-time minggu ini?', 'jawaban' => 'Minggu pagi untuk olahraga ringan dan me-time.'],
                ],
            ],
            [
                'nama' => 'Irfan Maulana', 'nim' => '2301416085', 'fakultas' => 'FE', 'prodi' => 'Akuntansi',
                'worksheet_id' => 3, 'worksheet_judul' => 'Jurnal Mindfulness Harian: Lembar Kerja Refleksi Diri',
                'tanggal' => '18 Sep 2026, 15.00', 'minggu_ini' => true,
                'jawaban' => [
                    ['pertanyaan' => 'Bagaimana perasaanmu hari ini? Coba gambarkan dengan 3 kata.', 'jawaban' => 'Tenang, fokus, sedikit capek.'],
                    ['pertanyaan' => 'Apa satu hal yang membuatmu bersyukur hari ini?', 'jawaban' => 'Bersyukur bisa menyelesaikan revisi proposal tepat waktu.'],
                    ['pertanyaan' => 'Momen apa yang terasa paling menantang hari ini, dan bagaimana kamu meresponsnya?', 'jawaban' => 'Rasa kurang percaya diri saat presentasi; aku tarik napas dalam sebelum mulai bicara.'],
                    ['pertanyaan' => 'Apa yang sedang kamu pikirkan atau khawatirkan saat ini?', 'jawaban' => 'Memikirkan biaya kuliah semester depan.'],
                    ['pertanyaan' => 'Satu langkah kecil apa yang bisa kamu lakukan besok untuk merawat dirimu sendiri?', 'jawaban' => 'Mencoba journaling sebelum tidur untuk melepas pikiran.'],
                ],
            ],
        ];
    }
       
    public function laporan(Request $request)
{
    $tahun    = (int) $request->query('tahun', now()->year);
    $bulan    = (int) $request->query('bulan', 1);
    $semuaPeriode = $request->boolean('semua');
    $dariBulan = (int) $request->query('dari_bulan', 1);
    $dariTahun = (int) $request->query('dari_tahun', $tahun);
    $sampaiBulan = (int) $request->query('sampai_bulan', 12);
    $sampaiTahun = (int) $request->query('sampai_tahun', $tahun);
 
    $q = Pengajuan::with('konseli');
    if (! $semuaPeriode) {
        $mulai = Carbon::create($dariTahun, $dariBulan, 1)->startOfMonth();
        $akhir = Carbon::create($sampaiTahun, $sampaiBulan, 1)->endOfMonth();
        if ($mulai->gt($akhir)) [$mulai, $akhir] = [$akhir, $mulai];
        $q->whereBetween('created_at', [$mulai, $akhir]);
    }
    $items=$q->get();
    $jadwal=JadwalKonseling::with(['konselor','pengajuan','survei'])
        ->when(! $semuaPeriode, fn($query) => $query->whereBetween('created_at', [$mulai, $akhir]))
        ->get();
    $total=$items->count(); $selesai=$items->where('status','selesai')->count();
    $durasiTunggu=$jadwal->filter(fn($j)=>$j->pengajuan?->created_at && $j->tanggal)->map(fn($j)=>max(0,$j->pengajuan->created_at->diffInDays($j->tanggal)));
    $ringkasan=['total_pengajuan'=>$total,'sesi_selesai'=>$jadwal->where('status','selesai')->count(),'tingkat_selesai'=>$total?round($selesai/$total*100):0,'rata_tunggu'=>$durasiTunggu->count()?round($durasiTunggu->avg(),1):0,'jumlah_survei'=>$jadwal->filter(fn($j)=>$j->survei)->count()];
    $trenBulanan=collect(range(1,12))->map(function($m) use ($items, $tahun) {
        $bulan = $items->filter(fn($p) => $p->created_at && (int)$p->created_at->year === $tahun && (int)$p->created_at->month === $m);
        return ['label'=>Carbon::create($tahun, $m, 1)->translatedFormat('M'),'diproses'=>$bulan->whereNotIn('status',['selesai'])->count(),'selesai'=>$bulan->where('status','selesai')->count()];
    })->all();
    $kategoriKonseling = collect();
    foreach ($items as $p) {
        $kategori = data_get($p->data ?? [], 'lingkup_hambatan', data_get($p->data ?? [], 'kategori_masalah', 'Lainnya'));
        foreach ((array) $kategori as $label) {
            $label = trim((string) $label) ?: 'Lainnya';
            $kategoriKonseling[$label] = ($kategoriKonseling[$label] ?? 0) + 1;
        }
    }
    $kategoriKonseling = $kategoriKonseling->sortDesc()->map(fn($jumlah,$label)=>['label'=>$label,'jumlah'=>$jumlah,'persen'=>$total?round($jumlah/$total*100):0])->values();
    if ($kategoriKonseling->count() > 6) {
        $lainnya = $kategoriKonseling->skip(6)->sum('jumlah');
        $kategoriKonseling = $kategoriKonseling->take(6)->push(['label'=>'Lainnya','jumlah'=>$lainnya,'persen'=>$total?round($lainnya/$total*100):0]);
    }
    $kategoriKonseling = $kategoriKonseling->values()->all();
    $performaKonselor=$jadwal->groupBy('konselor_id')->map(fn($g)=>['nama'=>$g->first()->konselor?->name ?? 'Konselor','sesi'=>$g->count(),'selesai'=>$g->where('status','selesai')->count()])->values()->all();
    $jenisKonseli=$items->groupBy(fn($p)=>ucwords(str_replace('_',' ', $p->konseli?->jenis_konseli ?? 'Lainnya')))->map(fn($g,$label)=>['label'=>$label,'persen'=>$total?round($g->count()/$total*100):0,'bg'=>'#eef1ee','warna'=>'#5c6a62'])->values()->all();
    $daftarKonselor = User::where('role', 'konselor')->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    return view('koordinator.laporan', [
        'menuAktif'  => 'laporan',
        'tahun'      => $tahun,
        'bulan'      => $bulan,
        'semuaPeriode' => $semuaPeriode,
        'dariBulan' => $dariBulan, 'dariTahun' => $dariTahun,
        'sampaiBulan' => $sampaiBulan, 'sampaiTahun' => $sampaiTahun,
        'opsiTahun'  => range(now()->year, now()->year - 4),
        'opsiBulan'  => [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ],
 
        // Kartu statistik atas
        'ringkasan' => $ringkasan,
 
        // Chart batang "Tren Pengajuan Bulanan"
        'trenBulanan' => $trenBulanan,
 
        // Daftar kategori konseling + progress bar
        'kategoriKonseling' => $kategoriKonseling,
 
        // Tabel performa konselor
        'performaKonselor' => $performaKonselor,
 
        // Daftar jenis konseli + persen
        'jenisKonseli' => $jenisKonseli,
        'daftarKonselor' => $daftarKonselor,
    ]);
}
 
    /**
     * GET /koordinator/laporan/ekspor?jenis=pdf|excel
     * Placeholder unduh laporan. Nanti ganti isinya dengan generator PDF
     * (mis. barryvdh/laravel-dompdf) atau Excel (mis. maatwebsite/excel)
     * sesuai filter tahun/bulan/semester yang dikirim dari halaman laporan.
     */
    public function eksporKonselor(Request $request)
    {
        $ids = collect((array) $request->query('konselor', []))->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->values();
        $semua = $request->boolean('semua');
        $tahun = (int) $request->query('tahun', now()->year);
        $dariBulan = (int) $request->query('dari_bulan', 1);
        $dariTahun = (int) $request->query('dari_tahun', $tahun);
        $sampaiBulan = (int) $request->query('sampai_bulan', 12);
        $sampaiTahun = (int) $request->query('sampai_tahun', $tahun);
        $mulai = Carbon::create($dariTahun, $dariBulan, 1)->startOfMonth();
        $akhir = Carbon::create($sampaiTahun, $sampaiBulan, 1)->endOfMonth();
        if ($mulai->gt($akhir)) [$mulai, $akhir] = [$akhir, $mulai];
        $jadwal = JadwalKonseling::with(['konselor', 'pengajuan.konseli', 'pengajuan'])
            ->where('status', 'selesai')
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('konselor_id', $ids->all()))
            ->when(! $semua, fn ($q) => $q->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()]))
            ->get();
        $rows = $jadwal->map(function ($j) {
            $p = $j->pengajuan; $d = $p?->data ?? []; $jenis = $d['jenis_konseli'] ?? $p?->konseli?->jenis_konseli;
            $nama = match ($jenis) {
                'mahasiswa' => data_get($d, 'mahasiswa_nama'), 'alumni' => data_get($d, 'alumni_nama'),
                'tenaga_kependidikan' => data_get($d, 'tk_nama'),
                'wali_mahasiswa' => data_get($d, 'wali_subjek_konseling') === 'mahasiswa' ? data_get($d, 'wali_mahasiswa_nama') : data_get($d, 'wali_nama'),
                default => null,
            } ?: $p?->konseli?->nama ?: '-';
            $sebelumnya = JadwalKonseling::where('pengajuan_id', $j->pengajuan_id)->where('status', 'selesai')->where(function ($q) use ($j) { $q->where('tanggal', '<', $j->tanggal)->orWhere(function ($q) use ($j) { $q->where('tanggal', $j->tanggal)->where('id', '<=', $j->id); }); })->count();
            return ['nama_konselor' => $j->konselor?->name ?? '-', 'tanggal' => optional($j->tanggal)->format('d-m-Y').' '.($j->mulai ?: ''), 'nama_konseli' => $nama, 'sesi' => 'Sesi ke-'.$sebelumnya, '_sort_nama' => strtolower((string) ($j->konselor?->name ?? '')), '_sort_tanggal' => (string) $j->tanggal.' '.$j->mulai];
        })->sortBy([['_sort_nama', 'asc'], ['_sort_tanggal', 'asc']])->values();
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial;font-size:12px}h2{color:#315d3a}table{border-collapse:collapse;width:100%}th,td{border:1px solid #aeb9b0;padding:7px}th{background:#dcebdc}</style></head><body><h2>Rekap Sesi Konselor</h2><p>Periode: '.e($semua ? 'Semua periode' : $mulai->translatedFormat('d F Y').' - '.$akhir->translatedFormat('d F Y')).'</p><table><tr><th>No</th><th>Nama Konselor</th><th>Tanggal Sesi</th><th>Nama Konseli</th><th>Sesi</th></tr>';
        foreach ($rows as $i => $r) $html .= '<tr><td>'.($i + 1).'</td><td>'.e($r['nama_konselor']).'</td><td>'.e($r['tanggal']).'</td><td>'.e($r['nama_konseli']).'</td><td>'.e($r['sesi']).'</td></tr>';
        $html .= '</table></body></html>';
        return response($html, 200, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="rekap-sesi-konselor.xls"']);
    }

    public function eksporLaporan(Request $request)
    {
        $jenis    = $request->query('jenis', 'pdf'); // 'pdf' | 'excel'
        $semua    = $request->boolean('semua');
        $tahun    = (int) $request->query('tahun', now()->year);
        $bulan    = (int) $request->query('bulan', now()->month);
        $dariBulan = (int) $request->query('dari_bulan', $bulan);
        $dariTahun = (int) $request->query('dari_tahun', $tahun);
        $sampaiBulan = (int) $request->query('sampai_bulan', $bulan);
        $sampaiTahun = (int) $request->query('sampai_tahun', $tahun);
        $semester = $request->query('semester');

        $query = Pengajuan::with(['konseli','jadwal.konselor','jadwal.catatanKonseling'])->latest();
        $labelPeriode = 'Semua periode';
        if (! $semua) {
            $mulai = Carbon::create($dariTahun, $dariBulan, 1)->startOfMonth();
            $akhir = Carbon::create($sampaiTahun, $sampaiBulan, 1)->endOfMonth();
            if ($mulai->gt($akhir)) [$mulai, $akhir] = [$akhir, $mulai];
            $query->whereBetween('created_at', [$mulai, $akhir]);
            $labelPeriode = $mulai->translatedFormat('F Y').' – '.$akhir->translatedFormat('F Y');
        }
        $rows = $query->get();
        $title = 'Laporan SILAKON';
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;font-size:12px;color:#1b2432}h1{font-size:18px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #b9c2ba;padding:6px;text-align:left}th{background:#e8efe8}</style></head><body>';
        $html .= '<h1>'.e($title).'</h1><p>Periode: '.e($labelPeriode).'</p><table><thead><tr><th>Kode Pengajuan</th><th>Nama Konseli</th><th>Jenis Konseli</th><th>Kategori</th><th>Status</th><th>Hasil Sesi Terakhir</th><th>Tanggal Pengajuan</th></tr></thead><tbody>';
        foreach ($rows as $p) {
            $data = (array) ($p->data ?? []);
            $jenisKonseli = $data['jenis_konseli'] ?? $p->konseli?->jenis_konseli;
            $nama = match ($jenisKonseli) {
                'mahasiswa' => data_get($data, 'mahasiswa_nama'), 'alumni' => data_get($data, 'alumni_nama'),
                'tenaga_kependidikan' => data_get($data, 'tk_nama'),
                'wali_mahasiswa' => data_get($data, 'wali_subjek_konseling') === 'mahasiswa' ? data_get($data, 'wali_mahasiswa_nama') : data_get($data, 'wali_nama'),
                default => null,
            } ?: $p->konseli?->nama ?: '-';
            $sesi = $p->jadwal->sortByDesc(fn ($j) => ($j->tanggal?->format('Y-m-d') ?: '0000-00-00').' '.($j->mulai ?: '').' '.$j->id)->first();
            $hasil = $sesi?->catatanKonseling?->hasil ?: $sesi?->catatanKonseling?->tindak_lanjut ?: '-';
            $kategori = data_get($data, 'lingkup_hambatan', data_get($data, 'kategori_masalah', '-'));
            if (is_array($kategori)) $kategori = implode(', ', $kategori);
            $html .= '<tr><td>'.e($p->kode_pengajuan).'</td><td>'.e($nama).'</td><td>'.e(ucwords(str_replace('_',' ', $jenisKonseli ?: '-'))).'</td><td>'.e($kategori).'</td><td>'.e($p->status).'</td><td>'.e($hasil).'</td><td>'.e(optional($p->created_at)->format('d-m-Y H:i')).'</td></tr>';
        }
        $html .= '</tbody></table></body></html>';
        $this->audit('Ekspor Laporan','Ekspor laporan '.$labelPeriode.' sebagai '.strtolower($jenis));
        if ($jenis === 'excel') return response($html, 200, ['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8','Content-Disposition'=>'attachment; filename="laporan-silakon.xls"']);
        $lines = ['LAPORAN SILAKON', 'Periode: '.$labelPeriode, '', 'Kode | Nama Konseli | Jenis | Kategori | Status | Hasil | Tanggal'];
        foreach ($rows as $p) {
            $cat = data_get($p->data ?? [], 'lingkup_hambatan', data_get($p->data ?? [], 'kategori_masalah', '-'));
            if (is_array($cat)) $cat = implode(', ', $cat);
            $sesi = $p->jadwal->sortByDesc(fn ($j) => ($j->tanggal?->format('Y-m-d') ?: '0000-00-00').' '.($j->mulai ?: '').' '.$j->id)->first();
            $hasil = $sesi?->catatanKonseling?->hasil ?: $sesi?->catatanKonseling?->tindak_lanjut ?: '-';
            $lines[] = $p->kode_pengajuan.' | '.($p->konseli?->nama ?? '-').' | '.($p->konseli?->jenis_konseli ?? '-').' | '.$cat.' | '.$p->status.' | '.$hasil.' | '.optional($p->created_at)->format('d-m-Y H:i');
        }
        return response($this->buatPdfSederhana($lines), 200, ['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="laporan-silakon.pdf"']);
    }

    private function buatPdfSederhana(array $lines): string
    {
        $pages = array_chunk($lines, 48); $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = []; $objNo = 3;
        foreach ($pages as $pageLines) { $kids[] = $objNo.' 0 R'; $objNo += 3; }
        $objects[] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        foreach ($pages as $pageLines) {
            $pageObj = count($objects)+1; $contentObj = $pageObj+1;
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.($contentObj+1).' 0 R >> >> /Contents '.$contentObj.' 0 R >>';
            $stream = "BT /F1 9 Tf 40 810 Td 12 TL\n";
            foreach ($pageLines as $line) {
                $line = preg_replace('/\s+/', ' ', (string) $line);
                if ($line === '') { $stream .= "() Tj T*\n"; continue; }
                while (mb_strlen($line) > 100) {
                    $chunk = mb_substr($line, 0, 100); $line = mb_substr($line, 100);
                    $chunk = str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $chunk); $stream .= '('.$chunk.') Tj T*\n';
                }
                $line = str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $line); $stream .= '('.$line.') Tj T*\n';
            }
            $stream .= 'ET'; $objects[] = '<< /Length '.strlen($stream).' >>\nstream\n'.$stream.'\nendstream'; $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        }
        $pdf = "%PDF-1.4\n"; $offsets = [0]; foreach ($objects as $i=>$obj) { $offsets[] = strlen($pdf); $pdf .= ($i+1).' 0 obj\n'.$obj.'\nendobj\n'; }
        $xref = strlen($pdf); $pdf .= "xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n"; for ($i=1;$i<=count($objects);$i++) $pdf .= sprintf('%010d 00000 n \n',$offsets[$i]);
        return $pdf."trailer\n<< /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }
    
    /**
     * Kartu ringkasan laporan berbasis data pengajuan dan jadwal.
     */
    private function ringkasanLaporanDummy(int $tahun, int $bulan): array
    {
        return [
            'total_pengajuan'  => 87,
            'sesi_selesai'     => 63,
            'tingkat_selesai'  => 72,
            'rata_tunggu'      => 2.4,
            'jumlah_survei'    => 63,
        ];
    }
    
    /**
     * Data untuk chart batang "Tren Pengajuan Bulanan" (Diproses vs Selesai).
     */
    private function trenBulananDummy(int $tahun): array
    {
        return [
            ['label' => 'Jan', 'diproses' => 4,  'selesai' => 26],
            ['label' => 'Feb', 'diproses' => 5,  'selesai' => 28],
            ['label' => 'Mar', 'diproses' => 6,  'selesai' => 32],
            ['label' => 'Apr', 'diproses' => 4,  'selesai' => 24],
            ['label' => 'Mei', 'diproses' => 8,  'selesai' => 38],
            ['label' => 'Jun', 'diproses' => 7,  'selesai' => 34],
            ['label' => 'Jul', 'diproses' => 5,  'selesai' => 29],
            ['label' => 'Agu', 'diproses' => 15, 'selesai' => 45],
        ];
    }
    
    /**
     * Daftar kategori konseling beserta jumlah & persentase.
     */
    private function kategoriKonselingDummy(): array
    {
        return [
            ['label' => 'Kesehatan Mental', 'jumlah' => 32, 'persen' => 37],
            ['label' => 'Akademik',         'jumlah' => 21, 'persen' => 24],
            ['label' => 'Karier',           'jumlah' => 14, 'persen' => 16],
            ['label' => 'Sosial & Relasi',  'jumlah' => 11, 'persen' => 13],
            ['label' => 'Pribadi',          'jumlah' => 6,  'persen' => 7],
            ['label' => 'Lainnya',          'jumlah' => 3,  'persen' => 3],
        ];
    }
    
    /**
     * Tabel performa konselor: jumlah sesi dan sesi selesai.
     */
    private function performaKonselorDummy(): array
    {
        return [
            ['nama' => 'Dr. Sari Dewi, M.Psi',       'sesi' => 0, 'selesai' => 0],
            ['nama' => 'Drs. Agus Santoso, M.Si',    'sesi' => 0, 'selesai' => 0],
            ['nama' => 'Dr. Rina Kusumawati, M.Pd',  'sesi' => 0, 'selesai' => 0],
            ['nama' => 'Dra. Wulandari, M.Pd',       'sesi' => 0, 'selesai' => 0],
        ];
    }
    
    /**
     * Daftar jenis konseli (Mahasiswa/Alumni/Tendik/Wali) beserta persentase
     * dan warna badge (bg = latar, warna = teks + isi progress bar).
     */
    private function jenisKonseliDummy(): array
    {
        return [
            ['label' => 'Mahasiswa', 'persen' => 72, 'bg' => '#d9f5e3', 'warna' => '#1f7a44'],
            ['label' => 'Alumni',    'persen' => 14, 'bg' => '#eef1ee', 'warna' => '#5c6a62'],
            ['label' => 'Tendik',    'persen' => 9,  'bg' => '#dfeafc', 'warna' => '#2456a8'],
            ['label' => 'Wali',      'persen' => 5,  'bg' => '#eef1ee', 'warna' => '#8a978f'],
        ];
    }
    public function survei(): View { return view('koordinator.hasil-survei'); }

    /**
     * =========================================================
     *  MANAJEMEN PENGGUNA
     *  NOTE: sementara data disimpan di session (dummy storage)
     *  supaya tambah/edit/reset/toggle langsung terasa jalan
     *  tanpa perlu migrasi dulu. Kalau tabel `users` sudah siap,
     *  tinggal ganti $this->penggunaSemua()/session ini dengan
     *  query ke model User (lihat komentar TODO di tiap method).
     * =========================================================
     */
    private function penggunaSemua(): array
    {
        if (!session()->has('pengguna_dummy')) {
            session(['pengguna_dummy' => [
                [
                    'id' => 1, 'nama' => 'Dra. Edwindha Prafitra, M.Pd., Kons.',
                    'email' => 'edwindha@lppp.unnes.ac.id', 'wa' => '081234567890',
                    'role' => ['Admin', 'Konselor', 'Koordinator'],
                    'keterangan' => 'Konselor Tetap', 'status' => 'Aktif', 'tgl' => '2024-01-15',
                ],
                [
                    'id' => 2, 'nama' => 'Dr. Mulawarman, M.Pd',
                    'email' => 'mulawarman@lppp.unnes.ac.id', 'wa' => '082345678901',
                    'role' => ['Konselor'], 'keterangan' => 'Dosen Konselor', 'status' => 'Aktif', 'tgl' => '2024-02-01',
                ],
                [
                    'id' => 3, 'nama' => 'Drs. Suharso, M.Pd., Kons.',
                    'email' => 'suharso@lppp.unnes.ac.id', 'wa' => '083456789012',
                    'role' => ['Konselor'], 'keterangan' => 'Dosen Konselor', 'status' => 'Aktif', 'tgl' => '2024-02-15',
                ],
                [
                    'id' => 4, 'nama' => 'Dr. Awalya, M.Pd',
                    'email' => 'awalya@lppp.unnes.ac.id', 'wa' => '084567890123',
                    'role' => ['Konselor'], 'keterangan' => 'Magang PPK', 'status' => 'Nonaktif', 'tgl' => '2024-03-01',
                ],
                [
                    'id' => 5, 'nama' => 'Ahmad Fauzi, S.Kom.',
                    'email' => 'fauzi@lppp.unnes.ac.id', 'wa' => '085678901234',
                    'role' => ['Admin'], 'keterangan' => null, 'status' => 'Aktif', 'tgl' => '2024-01-10',
                ],
            ]]);
        }
        return session('pengguna_dummy');
    }

    private function penggunaSimpan(array $data): void
    {
        session(['pengguna_dummy' => $data]);
    }

    /** GET /koordinator/manajemen-pengguna */
    public function pengguna(): View
    {
        $pengguna = User::whereIn('role', ['admin', 'konselor', 'koordinator'])->latest()->get()->map(fn (User $user) => [
            'id' => $user->id,
            'nama' => $user->name,
            'email' => $user->email,
            'wa' => $user->phone ?? '-',
            'role' => [ucfirst($user->role)],
            'keterangan' => null,
            'status' => $user->is_active ? 'Aktif' : 'Nonaktif',
            'tgl' => optional($user->created_at)->format('Y-m-d'),
        ])->all();
        return view('koordinator.manajemen-pengguna', compact('pengguna'));

        // TODO ganti: $pengguna = User::orderByDesc('created_at')->get();
        return view('koordinator.manajemen-pengguna', [
            'pengguna' => $this->penggunaSemua(),
        ]);
    }

    /** POST /koordinator/manajemen-pengguna */
    public function penggunaStore(Request $request)
    {
        $data = $request->validate([
            'nama'   => 'required|string|max:150',
            'email'  => 'required|email|max:150',
            'wa'     => 'required|string|max:20',
            'role'   => 'required|array|min:1',
            'role.*' => 'in:Admin,Konselor,Koordinator',
            'status' => 'required|in:Aktif,Nonaktif',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $role = strtolower($data['role'][0]);
        $password = Str::random(10);
        $user = User::create([
            'name' => $data['nama'],
            'email' => $data['email'],
            'phone' => $data['wa'],
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => $data['status'] === 'Aktif',
        ]);
        try { Mail::raw("Akun SILAKON Anda telah dibuat. Email: {$user->email}\nPassword awal: {$password}", function ($message) use ($user) { $message->to($user->email)->subject('Akun SILAKON'); }); } catch (\Throwable $e) { report($e); }
        $this->audit('Tambah Akun','Menambahkan akun '.$user->email.' dengan peran '.$role);
        return back()->with('sukses', 'Akun berhasil dibuat dan password awal dikirim melalui email.');

        $semua = $this->penggunaSemua();
        $idBaru = empty($semua) ? 1 : (max(array_column($semua, 'id')) + 1);

        $semua[] = [
            'id' => $idBaru,
            'nama' => $data['nama'],
            'email' => $data['email'],
            'phone' => $data['wa'],
            'wa' => $data['wa'],
            'role' => $data['role'],
            'keterangan' => in_array('Konselor', $data['role']) ? ($data['keterangan'] ?? null) : null,
            'status' => $data['status'],
            'tgl' => now()->format('Y-m-d'),
        ];
        $this->penggunaSimpan($semua);

        // TODO ganti dengan pembuatan User::create([...]) + generate & kirim password awal via email/notifikasi.

        return back()->with('sukses', 'Akun "' . $data['nama'] . '" berhasil ditambahkan.');
    }

    /** PUT /koordinator/manajemen-pengguna/{id} */
    public function penggunaUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'nama'   => 'required|string|max:150',
            'email'  => 'required|email|max:150',
            'wa'     => 'required|string|max:20',
            'role'   => 'sometimes|array|min:1',
            'role.*' => 'in:Admin,Konselor,Koordinator',
            'status' => 'required|in:Aktif,Nonaktif',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $data['nama'],
            'email' => $data['email'],
            'phone' => $data['wa'],
            'is_active' => $data['status'] === 'Aktif',
        ]);
        if (!empty($data['role'][0])) $user->update(['role' => strtolower($data['role'][0])]);
        $this->audit('Ubah Pengguna','Memperbarui akun '.$user->email);
        if ($user->id === request()->user()->id && $user->role !== 'koordinator') {
            request()->session()->regenerate();
            return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'konselor.dashboard')
                ->with('sukses', 'Role akun berhasil diubah menjadi '.ucfirst($user->role).'.');
        }
        return back()->with('sukses', 'Akun berhasil diperbarui.');

        $semua = $this->penggunaSemua();
        foreach ($semua as &$p) {
            if ($p['id'] === $id) {
                $p['nama'] = $data['nama'];
                $p['email'] = $data['email'];
                $p['wa'] = $data['wa'];
                $p['role'] = $data['role'];
                $p['keterangan'] = in_array('Konselor', $data['role']) ? ($data['keterangan'] ?? null) : null;
                $p['status'] = $data['status'];
                break;
            }
        }
        unset($p);
        $this->penggunaSimpan($semua);

        // TODO ganti dengan User::findOrFail($id)->update([...]);

        return back()->with('sukses', 'Akun berhasil diperbarui.');
    }

    /** POST /koordinator/manajemen-pengguna/{id}/reset */
    public function penggunaReset(int $id)
    {
        $user = User::findOrFail($id);
        $password = Str::random(10);
        $user->update(['password' => Hash::make($password)]);
        $this->audit('Reset Password','Mereset password akun '.$user->email);
        try { Mail::raw("Password baru akun SILAKON Anda: {$password}", function ($message) use ($user) { $message->to($user->email)->subject('Reset Password SILAKON'); }); } catch (\Throwable $e) { report($e); }
        return back()->with('sukses', 'Password berhasil direset dan dikirim melalui email pengguna.');

        $semua = $this->penggunaSemua();
        $nama = collect($semua)->firstWhere('id', $id)['nama'] ?? 'pengguna';

        // TODO ganti dengan logika reset password sungguhan, misalnya:
        //   $user = User::findOrFail($id);
        //   $passwordBaru = Str::random(10);
        //   $user->update(['password' => Hash::make($passwordBaru)]);
        //   Notification::send($user, new PasswordDiresetNotification($passwordBaru));

        return back()->with('sukses', 'Password untuk "' . $nama . '" berhasil direset dan dikirim ke email pengguna.');
    }

    /** POST /koordinator/manajemen-pengguna/{id}/toggle */
    public function penggunaToggle(int $id)
    {
        $user = User::findOrFail($id);
        abort_if($user->id === request()->user()->id, 422, 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        $user->update(['is_active' => ! $user->is_active]);
        $this->audit('Ubah Status','Mengubah status akun '.$user->email);
        return back()->with('sukses', 'Status akun berhasil diubah.');

        $semua = $this->penggunaSemua();
        $namaStatus = '';
        foreach ($semua as &$p) {
            if ($p['id'] === $id) {
                $p['status'] = $p['status'] === 'Aktif' ? 'Nonaktif' : 'Aktif';
                $namaStatus = $p['status'];
                break;
            }
        }
        unset($p);
        $this->penggunaSimpan($semua);

        // TODO ganti dengan: User::findOrFail($id)->update(['status' => $statusBaru]);

        return back()->with('sukses', 'Status akun berhasil diubah menjadi "' . $namaStatus . '".');
    }

    public function penggunaDestroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) return back()->with('galat', 'Akun yang sedang digunakan tidak dapat dihapus.');
        $email = $user->email; $user->delete();
        $this->audit('Hapus Pengguna', 'Menghapus akun '.$email);
        return back()->with('sukses', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Sumber data dummy untuk Pengaturan Sistem.
     * TODO ganti: ambil dari tabel `pengaturan` (key-value) atau model Setting, mis.
     *   Pengaturan::pluck('value', 'key')->toArray();
     */
    private function pengaturanSemua(): array
    {
        if (!session()->has('pengaturan_dummy')) {
            session(['pengaturan_dummy' => [
                'wa_admin'          => '085179510121',
                'wa_darurat'        => '082134059075',
                'jam_layanan'       => 'Senin–Kamis, 08.00–16.00 WIB; Jumat, 08.00–15.00 WIB',
                'teks_privasi'      => "Data dan cerita yang kamu isi hanya dapat dilihat oleh konselor yang menangani dan koordinator LP3. Kerahasiaan informasimu dijaga sepenuhnya sesuai dengan kode etik profesi konselor.",
                'teks_darurat'      => "Jika kamu sedang dalam kondisi darurat atau membutuhkan pertolongan segera, hubungi: Hotline LP3 UNNES atau segera hubungi orang terdekat yang kamu percaya. Kamu tidak sendirian.",
                'sesi_timeout_menit' => 30,
            ]]);
        }
        return session('pengaturan_dummy');
    }

    private function pengaturanSimpan(array $data): void
    {
        session(['pengaturan_dummy' => $data]);
    }

    /** GET /koordinator/pengaturan */
    public function pengaturan(): View
    {
        $defaults=['wa_admin'=>'085179510121','wa_darurat'=>'082134059075','jam_layanan'=>'Senin–Kamis, 08.00–16.00 WIB; Jumat, 08.00–15.00 WIB','teks_privasi'=>'Data dan cerita yang kamu isi hanya dapat dilihat oleh pihak berwenang.','teks_darurat'=>'Jika darurat, hubungi pihak yang dipercaya.','sesi_timeout_menit'=>30,'saw_bobot_who5'=>70,'saw_bobot_aging'=>30,'saw_batas_tinggi'=>67,'saw_batas_sedang'=>34];
        $saved=Setting::pluck('value','key')->all(); $pengaturan=array_merge($defaults,$saved); $pengaturan['sesi_timeout_menit']=(int)$pengaturan['sesi_timeout_menit'];
        return view('koordinator.pengaturan', compact('pengaturan'));
    }

    /** PUT /koordinator/pengaturan */
    public function pengaturanUpdate(Request $request)
    {
        $data = $request->validate([
            'wa_admin'           => ['required', 'regex:/^08[0-9]{8,12}$/'],
            'wa_darurat'         => ['nullable', 'regex:/^08[0-9]{8,12}$/'],
            'jam_layanan'        => ['required', 'string', 'max:150'],
            'teks_privasi'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'teks_darurat'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'sesi_timeout_menit' => ['required', 'integer', 'min:5', 'max:120'],
            'saw_bobot_who5'     => ['required', 'integer', 'min:0', 'max:100'],
            'saw_bobot_aging'    => ['required', 'integer', 'min:0', 'max:100'],
            'saw_batas_tinggi'   => ['required', 'integer', 'min:1', 'max:100'],
            'saw_batas_sedang'   => ['required', 'integer', 'min:0', 'max:99'],
        ], [
            'wa_admin.regex'   => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
            'wa_darurat.regex' => 'Format nomor darurat tidak valid (contoh: 081234567890).',
        ]);
        if ((int) $data['saw_bobot_who5'] + (int) $data['saw_bobot_aging'] !== 100) {
            return back()->withErrors(['saw_bobot_who5' => 'Bobot WHO-5 dan waktu tunggu harus berjumlah 100%.'])->withInput();
        }
        if ((int) $data['saw_batas_sedang'] >= (int) $data['saw_batas_tinggi']) {
            return back()->withErrors(['saw_batas_sedang' => 'Batas Sedang harus lebih kecil dari batas Tinggi.'])->withInput();
        }

        foreach($data as $key=>$value) Setting::updateOrCreate(['key'=>$key],['value'=>(string)$value]);
        $this->audit('Ubah Pengaturan','Memperbarui pengaturan sistem');

        // TODO ganti dengan penyimpanan sungguhan, mis.:
        //   foreach ($data as $key => $value) {
        //       Pengaturan::updateOrCreate(['key' => $key], ['value' => $value]);
        //   }
        //   Cache::forget('pengaturan_sistem');

        return back()->with('sukses', 'Pengaturan sistem berhasil disimpan.');
    }
    /**
     * Sumber data dummy Log Aktivitas.
     * TODO ganti: ambil dari tabel `activity_logs` mis.
     *   ActivityLog::with('user')->latest('created_at')->get();
     */
    private function logAktivitasDummy(): array
    {
        return [
            ['waktu' => '2026-09-22 14:32:10', 'pengguna' => 'Edwindha Prafitra',     'role' => 'Koordinator', 'aksi' => 'Ekspor Laporan',  'detail' => 'Ekspor laporan rekap sesi Q3 2026 (PDF)',                         'ip' => '192.168.1.10'],
            ['waktu' => '2026-09-22 13:15:44', 'pengguna' => 'Edwindha Prafitra',     'role' => 'Admin',       'aksi' => 'Ubah Status',     'detail' => 'Ubah status tiket #TK-089 → Ditutup',                             'ip' => '192.168.1.10'],
            ['waktu' => '2026-09-22 09:48:05', 'pengguna' => 'Edwindha Prafitra',     'role' => 'Koordinator', 'aksi' => 'Ganti Peran',     'detail' => 'Beralih peran dari Koordinator ke Konselor',                      'ip' => '192.168.1.10'],
            ['waktu' => '2026-09-21 16:55:30', 'pengguna' => 'Ahmad Fauzi',           'role' => 'Admin',       'aksi' => 'Ubah Status',     'detail' => 'Ubah status akun Awalya → Nonaktif',                              'ip' => '192.168.1.25'],
            ['waktu' => '2026-09-20 15:05:47', 'pengguna' => 'Ahmad Fauzi',           'role' => 'Admin',       'aksi' => 'Tambah Akun',     'detail' => 'Menambahkan akun konselor baru: Dra. Fatimah Zahra, M.Pd',        'ip' => '192.168.1.25'],
            ['waktu' => '2026-09-20 08:40:03', 'pengguna' => 'Edwindha Prafitra',     'role' => 'Koordinator', 'aksi' => 'Reset Password',  'detail' => 'Reset password untuk akun Dr. Hendra Pratama, M.Psi',             'ip' => '192.168.1.10'],
            ['waktu' => '2026-09-19 19:12:58', 'pengguna' => 'Ahmad Fauzi',           'role' => 'Admin',       'aksi' => 'Login',           'detail' => 'Login berhasil ke portal koordinator',                            'ip' => '192.168.1.25'],
            ['waktu' => '2026-09-19 09:03:21', 'pengguna' => 'Dr. Rina Kusumawati, M.Pd', 'role' => 'Konselor', 'aksi' => 'Ekspor Data',    'detail' => 'Ekspor data survei kepuasan bulan Agustus',                       'ip' => '192.168.1.42'],
        ];
    }

    /**
     * Warna badge per jenis aksi: [bg, warna teks].
     */
    private function aksiBadge(string $aksi): array
    {
        return match ($aksi) {
            'Ekspor Laporan', 'Ekspor Data' => ['bg' => '#d9f5e3', 'warna' => '#1f7a44'],
            'Ubah Status', 'Ubah Status Pengajuan', 'Hapus Jadwal', 'Hapus Self Help', 'Hapus Ketersediaan' => ['bg' => '#fde3e3', 'warna' => '#c23636'],
            'Buat Jadwal Konseling', 'Tambah Ketersediaan', 'Buat Program Kelompok', 'Buat Self Help' => ['bg' => '#d9f5e3', 'warna' => '#1f7a44'],
            'Ubah Ketersediaan', 'Ubah Self Help', 'Tangani Kasus Mendesak', 'Tandai Tidak Hadir' => ['bg' => '#fdf1d0', 'warna' => '#a9720d'],
            'Simpan Catatan Konseling', 'Selesaikan Konseling' => ['bg' => '#dcf3f0', 'warna' => '#1a7d72'],
            'Ganti Peran'                   => ['bg' => '#fdf1d0', 'warna' => '#a9720d'],
            'Tambah Akun'                   => ['bg' => '#dcf3f0', 'warna' => '#1a7d72'],
            'Reset Password'                => ['bg' => '#ece3fb', 'warna' => '#6b3fc9'],
            default                         => ['bg' => '#eef1ee', 'warna' => '#5c6a62'],
        };
    }

    /** GET /koordinator/log-aktivitas */
    public function log(Request $request): View
    {
        $aksi     = $request->query('aksi', '');
        $pengguna = trim((string) $request->query('pengguna', ''));
        $dari     = $request->query('dari', '');
        $sampai   = $request->query('sampai', '');
        $halaman  = max(1, (int) $request->query('halaman', 1));
        $perHalaman = (int) $request->query('per_halaman', 10);
        if (! in_array($perHalaman, [10, 25, 50], true)) {
            $perHalaman = 10;
        }

        $opsiAksi = ['Ubah Status Pengajuan', 'Buat Jadwal Konseling', 'Hapus Jadwal', 'Tandai Tidak Hadir', 'Tambah Ketersediaan', 'Ubah Ketersediaan', 'Hapus Ketersediaan', 'Tangani Kasus Mendesak', 'Buat Program Kelompok', 'Buat Self Help', 'Ubah Self Help', 'Hapus Self Help', 'Simpan Catatan Konseling', 'Selesaikan Konseling', 'Ekspor Laporan', 'Ekspor Data', 'Ganti Peran', 'Tambah Akun', 'Ubah Pengguna', 'Reset Password', 'Ubah Status', 'Ubah Pengaturan', 'Ubah Profil', 'Ubah Password', 'Hubungkan Telegram', 'Telegram Terhubung', 'Putuskan Telegram', 'Akses Ditolak'];

        // Koordinator tidak lagi membuka isi catatan konseling. Sembunyikan
        // jejak legacy "Buka Catatan" agar tidak muncul di log maupun filter.
        $query=ActivityLog::with('user')->where('action', '!=', 'Buka Catatan')->latest();
        if($aksi!=='') $query->where('action',$aksi);
        if($pengguna!=='') $query->whereHas('user',fn($q)=>$q->where('name','like','%'.$pengguna.'%'));
        if($dari!=='') $query->whereDate('created_at','>=',$dari);
        if($sampai!=='') $query->whereDate('created_at','<=',$sampai);
        $paginator=$query->paginate($perHalaman)->withQueryString();
        $logs=$paginator->getCollection()->map(fn($l)=>['waktu'=>$l->created_at->toDateTimeString(),'pengguna'=>$l->user?->name ?? 'Sistem','role'=>ucfirst($l->user?->role ?? '-'),'aksi'=>$l->action,'detail'=>$l->detail,'ip'=>$l->ip_address,'badge'=>$this->aksiBadge($l->action)]);
        return view('koordinator.log-aktivitas',['logs'=>$logs,'opsiAksi'=>$opsiAksi,'perHalaman'=>$perHalaman,'filter'=>['aksi'=>$aksi,'pengguna'=>$pengguna,'dari'=>$dari,'sampai'=>$sampai],'halaman'=>$paginator->currentPage(),'totalHalaman'=>$paginator->lastPage(),'total'=>$paginator->total(),'awal'=>$paginator->firstItem()??0,'akhir'=>$paginator->lastItem()??0]);

        // Filter (di dunia nyata ini jadi query builder ->when(...) di database)
        $semua = collect($this->logAktivitasDummy())
            ->when($aksi !== '', fn($q) => $q->where('aksi', $aksi))
            ->when($pengguna !== '', fn($q) => $q->filter(fn($l) => str_contains(strtolower($l['pengguna']), strtolower($pengguna))))
            ->when($dari !== '', fn($q) => $q->filter(fn($l) => substr($l['waktu'], 0, 10) >= $dari))
            ->when($sampai !== '', fn($q) => $q->filter(fn($l) => substr($l['waktu'], 0, 10) <= $sampai))
            ->sortByDesc('waktu')
            ->values()
            ->map(fn($l) => $l + ['badge' => $this->aksiBadge($l['aksi'])]);

        $total = $semua->count();
        $totalHalaman = max(1, (int) ceil($total / $perHalaman));
        $halaman = min($halaman, $totalHalaman);

        $logs = $semua->slice(($halaman - 1) * $perHalaman, $perHalaman)->values();

        return view('koordinator.log-aktivitas', [
            'logs'        => $logs,
            'opsiAksi'    => $opsiAksi,
            'filter'      => ['aksi' => $aksi, 'pengguna' => $pengguna, 'dari' => $dari, 'sampai' => $sampai],
            'halaman'     => $halaman,
            'totalHalaman' => $totalHalaman,
            'total'       => $total,
            'awal'        => $total === 0 ? 0 : ($halaman - 1) * $perHalaman + 1,
            'akhir'       => min($halaman * $perHalaman, $total),
        ]);
    }

    /** GET /koordinator/log-aktivitas/ekspor */
    public function logEkspor(Request $request)
    {
        $logs=ActivityLog::with('user')->where('action', '!=', 'Buka Catatan')->when($request->query('aksi'),fn($q,$v)=>$q->where('action',$v))->when($request->query('dari'),fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($request->query('sampai'),fn($q,$v)=>$q->whereDate('created_at','<=',$v))->latest()->get();
        $jenis=$request->query('jenis','excel');
        $html='<meta charset="utf-8"><style>body{font-family:Arial;font-size:12px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #bbb;padding:5px;text-align:left}th{background:#e8efe8}</style><h2>Log Aktivitas SILAKON</h2><table><tr><th>Waktu</th><th>Pengguna</th><th>Role</th><th>Aksi</th><th>Detail</th><th>IP</th></tr>';
        $lines=['LOG AKTIVITAS SILAKON','','Waktu | Pengguna | Role | Aksi | Detail | IP'];
        foreach($logs as $l){$row=[$l->created_at,$l->user?->name ?? 'Sistem',ucfirst($l->user?->role ?? '-'),$l->action,$l->detail,$l->ip_address];$html.='<tr>'.collect($row)->map(fn($v)=>'<td>'.e($v).'</td>')->implode('').'</tr>';$lines[]=implode(' | ',$row);}
        $html.='</table>'; $this->audit('Ekspor Data','Mengekspor log aktivitas sebagai '.strtoupper($jenis).'.');
        if($jenis==='pdf') return response($this->buatPdfSederhana($lines),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="log-aktivitas.pdf"']);
        return response($html,200,['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8','Content-Disposition'=>'attachment; filename="log-aktivitas.xls"']);
    }
    /**
     * Sumber data dummy Profil.
     * TODO ganti: ambil dari Auth::user() / relasi profil pengguna, mis.
     *   $user = Auth::user();
     */
    private function profilSemua(): array
    {
        if (!session()->has('profil_dummy')) {
            session(['profil_dummy' => [
                'nama'    => 'Dra. Edwindha Prafitra, M.Pd., Kons.',
                'email'   => 'edwindha@lppp.unnes.ac.id',
                'wa'      => '081234567890',
                'jabatan' => 'Koordinator LP3',
                'inisial' => 'DE',
                'peran'       => ['Admin', 'Konselor', 'Koordinator'],
                'peran_aktif' => 'Koordinator',
            ]]);
        }
        return session('profil_dummy');
    }

    private function profilSimpan(array $data): void
    {
        session(['profil_dummy' => $data]);
    }

    /** Warna badge peran: [bg, warna teks]. */
    private function peranBadge(string $peran): array
    {
        return match ($peran) {
            'Admin'       => ['bg' => '#DEE9FB', 'warna' => '#3B6FD6'],
            'Konselor'    => ['bg' => '#E4EEDE', 'warna' => '#6E8F60'],
            'Koordinator' => ['bg' => '#FBEED9', 'warna' => '#DB9A2C'],
            default       => ['bg' => '#EEF1EE', 'warna' => '#5C6A62'],
        };
    }

    /** GET /koordinator/profil */
    public function profil(): View
    {
        $user=auth()->user();
        $profil=['nama'=>$user->name,'email'=>$user->email,'wa'=>$user->phone ?? '','jabatan'=>'Koordinator SILAKON','inisial'=>collect(explode(' ',preg_replace('/[.,]/','',$user->name)))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode(''),'peran'=>[ucfirst($user->role)],'peran_aktif'=>ucfirst($user->role)];

        return view('koordinator.profil', [
            'profil'      => $profil,
            'peranBadge'  => collect($profil['peran'])->mapWithKeys(fn($p) => [$p => $this->peranBadge($p)]),
            'aktifBadge'  => $this->peranBadge($profil['peran_aktif']),
        ]);
    }

    /** PUT /koordinator/profil — simpan perubahan Data Diri */
    public function profilUpdate(Request $request)
    {
        $data = $request->validate([
            'nama'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150'],
            'wa'      => ['required', 'regex:/^08[0-9]{8,12}$/'],
            'jabatan' => ['required', 'string', 'max:100'],
        ], [
            'wa.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
        ]);

        $user=auth()->user(); $user->update(['name'=>$data['nama'],'email'=>$data['email'],'phone'=>$data['wa']]); $this->audit('Ubah Profil','Memperbarui profil akun');
        return back()->with('sukses', 'Profil berhasil diperbarui.');

        $profil = $this->profilSemua();
        $data['inisial'] = collect(explode(' ', preg_replace('/[.,]/', '', $data['nama'])))
            ->filter(fn($p) => !in_array(strtolower($p), ['dr', 'drs', 'dra', 'm', 'psi', 'pd', 'si', 'kons']))
            ->map(fn($p) => mb_substr($p, 0, 1))
            ->take(2)->implode('');
        $data['peran'] = $profil['peran'];
        $data['peran_aktif'] = $profil['peran_aktif'];
        $this->profilSimpan($data);

        // TODO ganti dengan: Auth::user()->update([...]);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    /** PUT /koordinator/profil/password — ubah password */
    public function profilUbahPassword(Request $request)
    {
        $data = $request->validate([
            'password_saat_ini'             => ['required', 'string'],
            'password_baru'                 => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).+$/', 'confirmed'],
            'password_baru_confirmation'    => ['required'],
        ], [
            'password_baru.regex' => 'Password baru harus mengandung huruf, angka, dan simbol.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($data['password_saat_ini'], auth()->user()->password)) return back()->withErrors(['password_saat_ini'=>'Password saat ini salah.'])->with('gagalPassword',true);
        auth()->user()->update(['password'=>Hash::make($data['password_baru'])]); $this->audit('Ubah Password','Memperbarui password akun');
        return back()->with('sukses','Password berhasil diubah.');

        // TODO ganti dengan pengecekan sungguhan, mis. Hash::check($data['password_saat_ini'], Auth::user()->password)
        $passwordSaatIniDummy = session('profil_password_dummy', 'Password123!');
        if ($data['password_saat_ini'] !== $passwordSaatIniDummy) {
            return back()->withErrors(['password_saat_ini' => 'Password saat ini salah.'])->with('gagalPassword', true);
        }

        session(['profil_password_dummy' => $data['password_baru']]);

        // TODO ganti dengan: Auth::user()->update(['password' => Hash::make($data['password_baru'])]);

        return back()->with('sukses', 'Password berhasil diubah.');
    }
}
