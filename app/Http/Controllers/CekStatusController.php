<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CekStatusController extends Controller
{
    public function index()
    {
        return view('cek-status.index');
    }

    public function cari(Request $request)
    {
        $validated = $request->validate([
            'kode_pengajuan' => ['required', 'string', 'regex:/^SLK-\d{4}-\d{2}-\d{4}$/i'],
            'email' => ['required', 'email'],
        ], ['kode_pengajuan.regex' => 'Format kode pengajuan tidak valid. Contoh: SLK-2026-10-1234']);

        $exists = Pengajuan::where('kode_pengajuan', strtoupper($validated['kode_pengajuan']))
            ->where('email', strtolower($validated['email']))->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['kode_pengajuan' => 'Kode pengajuan dan email tidak ditemukan atau tidak cocok.']);
        }

        return redirect()->route('cek-status.hasil', ['kode' => strtoupper($validated['kode_pengajuan']), 'email' => strtolower($validated['email'])]);
    }

    public function hasil(Request $request, string $kode)
    {
        $email = strtolower((string) $request->query('email'));
        validator(['email' => $email], ['email' => ['required', 'email']])->validate();

        $data = Pengajuan::with(['konseli', 'riwayatStatus' => fn ($query) => $query->latest()])
            ->where('kode_pengajuan', strtoupper($kode))->where('email', $email)->first();

        if (! $data) {
            return redirect()->route('cek-status.index')->withErrors(['kode_pengajuan' => 'Kode pengajuan dan email tidak ditemukan atau tidak cocok.']);
        }

        $meta = $this->statusMeta($data->status);
        $pengajuan = [
            'kode_pengajuan' => $data->kode_pengajuan,
            'status' => $data->status,
            'status_label' => $meta['label'],
            'status_class' => $meta['class'],
            'nama' => $data->konseli->nama,
            'jenis_konseli' => $this->labelJenis($data->konseli->jenis_konseli),
            'kategori' => collect((array) data_get($data->data, 'lingkup_hambatan', []))->implode(', ') ?: 'Konseling individual',
            'tanggal_pengajuan' => optional($data->created_at)->translatedFormat('j F Y'),
            'alasan_penolakan' => $data->alasan_penolakan,
            'riwayat' => $data->riwayatStatus->sortBy('created_at')->reduce(function ($hasil, $item) {
                // Satu aksi penjadwalan dapat terpanggil ulang dari browser.
                // Jangan tampilkan status yang sama berturut-turut sebagai
                // banyak tahap pada timeline konseli.
                $terakhir = $hasil->last();
                $timelineSlug = match ($item->status_baru) {
                    'menunggu_verifikasi', 'menunggu_jadwal', 'ditugaskan' => 'diterima',
                    'berlangsung' => 'terjadwal',
                    default => $item->status_baru,
                };
                if ($terakhir && $terakhir['status_slug'] === $timelineSlug) {
                    return $hasil;
                }
                $meta = $this->statusMeta($item->status_baru);
                $hasil->push(['status_slug' => $timelineSlug, 'label' => $meta['label'], 'status' => 'selesai', 'waktu' => optional($item->created_at)->translatedFormat('j M Y, H.i'), 'keterangan' => $item->keterangan, 'detail' => null]);
                return $hasil;
            }, collect())->map(fn ($item) => collect($item)->except('status_slug')->all())->values()->all(),
        ];

        return view('cek-status.hasil', compact('pengajuan'));
    }

    protected function statusMeta(string $slug): array
    {
        return match ($slug) {
            'menunggu_verifikasi', 'menunggu_pemeriksaan' => ['label' => 'Menunggu Pemeriksaan', 'class' => 'st-yellow'],
            'diterima' => ['label' => 'Pengajuan Diterima', 'class' => 'st-blue'],
            'ditugaskan', 'menunggu_jadwal' => ['label' => 'Pengajuan Diterima', 'class' => 'st-blue'],
            'terjadwal' => ['label' => 'Terjadwal', 'class' => 'st-green'],
            'sesi_lanjutan' => ['label' => 'Menunggu Penjadwalan Sesi Lanjutan', 'class' => 'st-yellow'],
            'berlangsung' => ['label' => 'Terjadwal', 'class' => 'st-green'],
            'selesai' => ['label' => 'Selesai', 'class' => 'st-mint'],
            'ditolak', 'dibatalkan' => ['label' => ucfirst($slug), 'class' => 'st-red'],
            default => ['label' => ucfirst(str_replace('_', ' ', $slug)), 'class' => 'st-yellow'],
        };
    }

    private function labelJenis(string $jenis): string
    {
        return match ($jenis) {
            'mahasiswa' => 'Mahasiswa', 'alumni' => 'Alumni', 'wali_mahasiswa' => 'Wali Mahasiswa', 'tenaga_kependidikan' => 'Tenaga Kependidikan',
            default => ucfirst(str_replace('_', ' ', $jenis)),
        };
    }
}
