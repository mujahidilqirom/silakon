<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\JadwalKonseling;
use App\Models\Konseli;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    private function perubahanPersen(int $sekarang, int $sebelumnya): int
    {
        if ($sebelumnya === 0) return $sekarang === 0 ? 0 : 100;
        return (int) round((($sekarang - $sebelumnya) / $sebelumnya) * 100);
    }

    public function index()
    {
        $statistik = [
            [
                'nilai' => '0',
                'label' => 'Total Pengajuan',
                'sub' => '0 bulan ini',
                'tone' => 'blue',
                'icon' => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>'
            ],
            [
                'nilai' => '0',
                'label' => 'Menunggu Verifikasi',
                'sub' => '0 perlu diperiksa',
                'tone' => 'yellow',
                'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'
            ],
            [
                'nilai' => '0',
                'label' => 'Menunggu Jadwal',
                'sub' => 'Perlu tindakan',
                'tone' => 'purple',
                'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'
            ],
            [
                'nilai' => '0',
                'label' => 'Konseling Hari Ini',
                'sub' => '0 selesai',
                'tone' => 'green',
                'icon' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5L16 9.5"/>'
            ],
        ];

        $tren = [];
        $trenMaks = 1;

        $kategori = [];

        $pengajuanTerbaru = [];

        $aksiCepat = [
            ['label' => 'Verifikasi Pengajuan', 'jumlah' => 0, 'tone' => 'green',  'icon' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5L16 9.5"/>'],
            ['label' => 'Atur Jadwal',          'jumlah' => 0, 'tone' => 'blue',   'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
            ['label' => 'Kelola Antrian',       'jumlah' => 0, 'tone' => 'purple', 'icon' => '<path d="M4 9h16M4 15h16M10 3 8 21M16 3l-2 18"/>'],
            ['label' => 'Lihat Laporan',        'jumlah' => null, 'tone' => 'yellow', 'icon' => '<path d="M18 20V10M12 20V4M6 20v-6"/>'],
        ];

        $totalPengajuan = Pengajuan::count();
        // Hanya kasus mendesak yang belum ditandai selesai ditangani
        // ditampilkan sebagai peringatan aktif di dashboard. Pengajuan tetap
        // berada di Pengajuan Masuk sampai Admin memverifikasi status utamanya.
        $pengajuanDarurat = Pengajuan::with('konseli')->latest()->get()->filter(function ($item) {
            return in_array(data_get($item->data, 'kondisi_darurat'), ['ya', 'mendesak', 'darurat'], true)
                && data_get($item->data, 'darurat_status') !== 'ditangani';
        });
        $kasusMendesakJumlah = $pengajuanDarurat->count();
        $daruratTerbaru = $pengajuanDarurat->first();
        $kasusMendesakTerbaru = [
            'nomor' => $daruratTerbaru?->kode_pengajuan ?? '-',
            'nama' => $daruratTerbaru?->konseli?->nama ?? '-',
            'peran' => ucfirst($daruratTerbaru?->konseli?->jenis_konseli ?? 'Konseli'),
            'waktu' => $daruratTerbaru?->created_at?->diffForHumans() ?? '-',
        ];
        $awalBulan = now()->startOfMonth();
        $awalBulanLalu = now()->subMonth()->startOfMonth();
        $akhirBulanLalu = now()->startOfMonth()->subSecond();
        $jumlahBulanIni = Pengajuan::whereBetween('created_at', [$awalBulan, now()])->count();
        $jumlahBulanLalu = Pengajuan::whereBetween('created_at', [$awalBulanLalu, $akhirBulanLalu])->count();
        $perubahanBulan = $this->perubahanPersen($jumlahBulanIni, $jumlahBulanLalu);
        $menunggu = Pengajuan::where('status', Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN)->count();
        $menungguJadwal = Pengajuan::whereIn('status', ['diterima', 'ditugaskan', 'menunggu_jadwal', 'sesi_lanjutan'])->count();
        $antrian = Pengajuan::whereIn('status', ['menunggu_pemeriksaan', 'diterima', 'ditugaskan', 'menunggu_jadwal', 'sesi_lanjutan'])->count();
        $statistik[0]['nilai'] = (string) $totalPengajuan;
        $statistik[0]['sub'] = ($jumlahBulanIni > 0 ? '+' : '') . $jumlahBulanIni . ' bulan ini';
        $statistik[1]['nilai'] = (string) $menunggu;
        $statistik[1]['sub'] = $menunggu . ' perlu diperiksa';
        $statistik[2]['nilai'] = (string) $menungguJadwal;
        // Kartu "Konseling Hari Ini" hanya menghitung sesi yang benar-benar
        // sudah dijadwalkan dan masih aktif. Jadwal sesi sebelumnya yang sudah
        // selesai tidak boleh membuat sesi lanjutan terlihat sudah terjadwal.
        $hariIni = JadwalKonseling::whereDate('tanggal', today())
            ->whereIn('status', ['terjadwal', 'berlangsung'])
            ->get();
        $selesaiHariIni = JadwalKonseling::whereDate('tanggal', today())
            ->where('status', 'selesai')->count();
        $statistik[3]['nilai'] = (string) $hariIni->count();
        $statistik[3]['sub'] = $selesaiHariIni . ' selesai';
        $tren=collect(range(5,0))->map(function($i){$d=now()->startOfMonth()->subMonths($i);return ['bulan'=>$d->translatedFormat('M'),'nilai'=>Pengajuan::whereYear('created_at',$d->year)->whereMonth('created_at',$d->month)->count()];})->all();
        $trenMaks=max(1,max(array_column($tren,'nilai')));
        $kategoriBulanIni = Pengajuan::whereBetween('created_at', [$awalBulan, now()])->get();
        $countsKategori=[];
        foreach($kategoriBulanIni as $item){foreach((array)data_get($item->data,'lingkup_hambatan',[]) as $nama){$countsKategori[$nama]=($countsKategori[$nama]??0)+1;}}
        $totalKategori=max(1,array_sum($countsKategori));
        $warnaKategori=['#3B82F6','#EF4444','#F59E0B','#8B5CF6','#86A878','#B6BDB4'];
        $warnaIndex = 0;
        $groups=collect($countsKategori)->sortDesc()->map(function($n,$nama) use($totalKategori,$warnaKategori,&$warnaIndex){$warna=$warnaKategori[$warnaIndex % count($warnaKategori)];$warnaIndex++;return ['nama'=>$nama,'pct'=>round($n/$totalKategori*100),'warna'=>$warna];});
        $kategori=$groups->values()->all();
        $trenPerubahan = $perubahanBulan;
        $aksiCepat[0]['jumlah'] = $menunggu;
        $aksiCepat[1]['jumlah'] = $menungguJadwal;
        $aksiCepat[2]['jumlah'] = $antrian;
        $pengajuanTerbaru = Pengajuan::with('konseli')->latest()->limit(5)->get()->map(fn (Pengajuan $item) => [
            'kode' => $item->kode_pengajuan,
            'nama' => $item->konseli?->nama ?? data_get($item->data, 'mahasiswa_nama', data_get($item->data, 'alumni_nama', '-')),
            'nomor' => $item->nim ?: $item->email,
            'kategori' => collect((array) data_get($item->data, 'lingkup_hambatan', []))->implode(', ') ?: 'Konseling individual',
            'waktu' => $item->created_at?->diffForHumans(),
            'status' => str_replace('_', ' ', ucfirst($item->status)),
            'tone' => $item->status === Pengajuan::STATUS_MENUNGGU_PEMERIKSAAN ? 'yellow' : 'blue',
        ])->all();

        return view('admin.dashboard', compact(
            'statistik',
            'tren',
            'trenMaks',
            'trenPerubahan',
            'kategori',
            'pengajuanTerbaru',
            'aksiCepat',
            'kasusMendesakJumlah',
            'kasusMendesakTerbaru'
        ));
    }

    /**
     * GET /admin/profil
     * Halaman "Profil Saya" (dummy). Nanti array tinggal diganti data dari
     * auth()->user() dan tabel pengaturan.
     *
     * 'jamOperasional' : 'tutup' => true dipakai untuk hari libur (badge "Tutup").
     */
    public function profil()
    {
        $user=auth()->user();
        $profil = [
            'nama'    => $user?->name ?? 'Admin SILAKON',
            'email'   => $user?->email ?? 'admin@lppp.unnes.ac.id',
            'telepon' => $user?->phone ?? '-',
            'jabatan' => 'Admin Layanan Konseling',
        ];

        $prefix = 'admin_' . ($user?->id ?? 0) . '_';
        $setting = fn (string $key, string $fallback) => Setting::where('key', $prefix . $key)->value('value') ?? $fallback;
        $notifikasi = [
            ['key' => 'email',    'judul' => 'Notifikasi Email',    'deskripsi' => 'Terima email untuk setiap pengajuan baru',                 'aktif' => $setting('notif_email', '1') === '1'],
            ['key' => 'whatsapp', 'judul' => 'Notifikasi WhatsApp', 'deskripsi' => 'Notifikasi melalui WhatsApp Business',                     'aktif' => $setting('notif_whatsapp', '1') === '1'],
            ['key' => 'antrian',  'judul' => 'Antrian Otomatis',    'deskripsi' => 'Otomatis masukkan pengajuan ke antrian setelah diverifikasi', 'aktif' => $setting('notif_antrian', '0') === '1'],
        ];

        $jamOperasional = [
            ['key' => 'senin-kamis', 'hari' => 'Senin – Kamis', 'tutup' => false, 'buka' => $setting('jam_senin-kamis_buka', '08:00'), 'tutup_jam' => $setting('jam_senin-kamis_tutup', '16:00')],
            ['key' => 'jumat',       'hari' => 'Jumat',         'tutup' => false, 'buka' => $setting('jam_jumat_buka', '08:00'), 'tutup_jam' => $setting('jam_jumat_tutup', '11:00')],
            ['key' => 'sabtu',       'hari' => 'Sabtu',         'tutup' => true],
            ['key' => 'minggu',      'hari' => 'Minggu',        'tutup' => true],
        ];

        $infoSistem = [
            ['label' => 'Versi Sistem',        'nilai' => 'SILAKON v1.0.0'],
            ['label' => 'Institusi',           'nilai' => 'LPPP UNNES'],
            ['label' => 'Total Konseli',       'nilai' => Konseli::count()],
            ['label' => 'Terakhir Diperbarui', 'nilai' => now()->translatedFormat('d F Y')],
        ];

        return view('admin.profil', [
            'menuAktif'      => 'profil',
            'profil'         => $profil,
            'notifikasi'     => $notifikasi,
            'jamOperasional' => $jamOperasional,
            'infoSistem'     => $infoSistem,
        ]);
    }

    public function profilUpdate(Request $request)
    {
        $data=$request->validate(['nama'=>'required|string|max:150','email'=>'required|email|max:150','telepon'=>'nullable|string|max:30']);
        $request->user()->update(['name'=>$data['nama'],'email'=>$data['email'],'phone'=>$data['telepon']]);
        return back()->with('sukses','Profil berhasil diperbarui.');
    }

    public function profilPassword(Request $request)
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (!Hash::check($data['password_lama'], $request->user()->password)) {
            return back()->withErrors(['password_lama' => 'Password saat ini salah.']);
        }
        $request->user()->update(['password' => Hash::make($data['password_baru'])]);
        return back()->with('sukses', 'Password berhasil diubah.');
    }

    public function profilPreferensi(Request $request)
    {
        $prefix = 'admin_' . $request->user()->id . '_';
        foreach (['email', 'whatsapp', 'antrian'] as $key) {
            Setting::updateOrCreate(['key' => $prefix . 'notif_' . $key], ['value' => $request->boolean('notif_' . $key) ? '1' : '0']);
        }
        return back()->with('sukses', 'Pengaturan notifikasi berhasil disimpan.');
    }

    public function profilJam(Request $request)
    {
        $data = $request->validate([
            'jam_senin-kamis_buka' => ['required', 'date_format:H:i'],
            'jam_senin-kamis_tutup' => ['required', 'date_format:H:i', 'after:jam_senin-kamis_buka'],
            'jam_jumat_buka' => ['required', 'date_format:H:i'],
            'jam_jumat_tutup' => ['required', 'date_format:H:i', 'after:jam_jumat_buka'],
        ]);
        $prefix = 'admin_' . $request->user()->id . '_';
        foreach ($data as $key => $value) Setting::updateOrCreate(['key' => $prefix . $key], ['value' => $value]);
        return back()->with('sukses', 'Jam operasional berhasil disimpan.');
    }
}
