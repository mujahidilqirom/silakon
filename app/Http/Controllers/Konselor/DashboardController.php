<?php

namespace App\Http\Controllers\Konselor;

use App\Http\Controllers\Controller;
use App\Models\JadwalKonseling;

/**
 * SILAKON — Dashboard Konselor
 * Simpan sebagai: app/Http/Controllers/Konselor/DashboardController.php
 */
class DashboardController extends Controller
{
    public function index()
    {
        $records = JadwalKonseling::with('pengajuan.konseli')
            ->where('konselor_id', auth()->id())
            ->whereNotIn('status', ['dibatalkan', 'tidak_hadir'])
            ->orderBy('tanggal')
            ->orderBy('mulai')
            ->get();

        // Sesi hari ini
        // Sesi selesai tetap masuk statistik total, tetapi bukan lagi sesi
        // aktif yang tampil sebagai "hari ini" untuk dimulai.
        $hari = $records->filter(fn ($x) => $x->tanggal?->isToday() && in_array($x->status, ['terjadwal', 'berlangsung'], true));

        // Sesi mendatang: besok sampai 7 hari ke depan
        $batas = now()->addDays(7)->endOfDay();
        $mendatang = $records->filter(
            fn ($x) => $x->tanggal && $x->tanggal->isFuture() && $x->tanggal->lte($batas)
                && in_array($x->status, ['terjadwal', 'berlangsung'], true)
        );

        $selesai = $records->where('status', 'selesai')->count();
        $kategoriLabel = static function ($value): string {
            if (is_array($value)) return collect($value)->flatten()->filter(fn ($v) => is_scalar($v) && trim((string) $v) !== '')->map(fn ($v) => trim((string) $v))->implode(', ') ?: 'Konseling Individual';
            return trim((string) $value) !== '' ? (string) $value : 'Konseling Individual';
        };

        $statistik = [
            [
                'label' => 'Sesi Hari Ini',
                'nilai' => $hari->count(),
                'sub'   => $hari->where('status', 'selesai')->count() . ' selesai',
                'nada'  => 'hijau',
                'icon'  => '<rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/>',
            ],
            [
                'label' => 'Sesi Mendatang',
                'nilai' => $mendatang->count(),
                'sub'   => '7 hari ke depan',
                'nada'  => 'biru',
                'icon'  => '<path d="M7 2.5h10M7 21.5h10"/><path d="M17 2.5v4.2c0 2.8-5 3.9-5 5.3s5 2.5 5 5.3v4.2M7 2.5v4.2c0 2.8 5 3.9 5 5.3s-5 2.5-5 5.3v4.2"/>',
            ],
            [
                'label' => 'Total Selesai',
                'nilai' => $selesai,
                'sub'   => 'seluruh sesi',
                'nada'  => 'hijau',
                'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.3l2.4 2.4 4.6-5"/>',
            ],
        ];

        $jadwalHariIni = $hari->map(fn ($x) => [
            'kode'     => $x->pengajuan?->kode_pengajuan,
            'jadwal_id'=> $x->id,
            'jam'      => substr($x->mulai, 0, 5),
            'konseli'  => $x->pengajuan?->konseli?->nama ?? '-',
            'kategori' => $kategoriLabel(data_get($x->pengajuan?->data, 'lingkup_hambatan', 'Konseling Individual')),
            'ruang'    => $x->lokasi ?? '-',
            'status'   => ucfirst($x->status),
            'nada'     => $x->status === 'selesai' ? 'selesai' : 'tunggu',
        ])->values()->all();

        $jadwalMendatang = $mendatang->take(5)->map(fn ($x) => [
            'tanggal'  => $x->tanggal->format('d'),
            'bulan'    => $x->tanggal->translatedFormat('M'),
            'konseli'  => $x->pengajuan?->konseli?->nama ?? '-',
            'kategori' => $kategoriLabel(data_get($x->pengajuan?->data, 'lingkup_hambatan', 'Konseling Individual')),
            'jam'      => substr($x->mulai, 0, 5),
        ])->values()->all();

        $tanggalHariIni = now()->translatedFormat('l, d F Y');
        $tanggalJadwal  = $tanggalHariIni;

        return view('konselor.dashboard', compact(
            'statistik',
            'jadwalHariIni',
            'jadwalMendatang',
            'tanggalHariIni',
            'tanggalJadwal'
        ));
    }
}
