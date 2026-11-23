<?php

namespace App\Http\Controllers;

use App\Models\SurveiKonseling;
use Illuminate\Http\Request;

/**
 * Survei untuk konseli (halaman publik, diakses lewat link bertoken).
 * Semua data masih DUMMY — nanti ganti dengan lookup token ke database.
 */
class SurveiController extends Controller
{
    public function tampil(string $token)
    {
        $survei = SurveiKonseling::with('jadwal.pengajuan.konseli', 'jadwal.konselor')
            ->where('token', $token)->firstOrFail();
        $surveiSudahDiisi = $survei->status === 'terisi';

        $pengajuan = $survei->jadwal->pengajuan;
        $konseliModel = $pengajuan->konseli;
        $data = $konseliModel->data ?? [];
        $jenis = ucfirst((string) ($konseliModel->jenis_konseli ?: ($data['jenis_konseli'] ?? 'Konseli')));
        $urutanJadwal = $pengajuan->jadwal()->orderBy('tanggal')->orderBy('mulai')->orderBy('id')->pluck('id')->values()->all();
        $nomorSesi = (($posisi = array_search($survei->jadwal_id, $urutanJadwal, true)) === false) ? 1 : $posisi + 1;
        $konseli = [
            'nama' => $konseliModel->nama,
            'email' => $konseliModel->email,
            'jenis' => $jenis,
            'prodi_pekerjaan' => $data['mahasiswa_jurusan'] ?? $data['program_studi'] ?? $data['prodi'] ?? $data['alumni_jurusan'] ?? $data['pekerjaan'] ?? $data['pekerjaan_bidang'] ?? '-',
            'konselor' => $survei->jadwal->konselor?->name ?? 'Konselor',
            'sesi' => $nomorSesi,
            'bersama' => 'Konselor',
        ];

        return view('survei.isi', compact('konseli', 'token', 'surveiSudahDiisi'));

        // TODO: cari survei berdasarkan $token, abort(404) kalau tidak ada / sudah diisi.
        // Data diri di bawah nanti diambil dari relasi tiket -> konseli & konselor.
        $konseli = [
            'nama'            => 'Aulia Rahmawati',
            'email'           => 'aulia.rahmawati@students.unnes.ac.id',
            'jenis'           => 'Mahasiswa', // Mahasiswa => label "Program Studi", Alumni => label "Pekerjaan"
            'prodi_pekerjaan' => 'Pendidikan Bimbingan dan Konseling',
            'konselor'        => 'Dr. Sri Wahyuni, M.Pd., Kons.',
            'sesi'            => 2,
            'bersama'         => 'Konselor Profesional', // atau 'Tutor Sebaya'
        ];

        return view('survei.isi', compact('konseli', 'token'));
    }

    public function kirim(Request $request, string $token)
    {
        $wajib = 'Kolom ini wajib diisi.';

        $request->validate([
            'perasaan'             => 'required|in:harapan,keruh,lain',
            'perasaan_lain'        => 'required_if:perasaan,lain|nullable|string|max:500',
            'membantu'             => 'required|in:ya,tidak',
            'manfaat'              => 'required|string|max:2000',
            'kekurangan_kelebihan' => 'required|string|max:2000',
            'saran'                => 'required|string|max:2000',
            'sikap'                => 'required|in:memfasilitasi,menghakimi,lain',
            'sikap_lain'           => 'required_if:sikap,lain|nullable|string|max:500',
        ], [
            'required'    => $wajib,
            'in'          => 'Pilihan tidak valid.',
            'required_if' => 'Tulis jawaban Anda pada kolom "Yang lain".',
            'max'         => 'Jawaban terlalu panjang.',
        ]);

        $survei = SurveiKonseling::where('token', $token)->firstOrFail();
        if ($survei->status === 'terisi') {
            return redirect()->route('survei.tampil', $token)->with('survei_selesai', true);
        }
        $survei->update([
            'jawaban' => $request->only(['perasaan', 'perasaan_lain', 'membantu', 'manfaat', 'kekurangan_kelebihan', 'saran', 'sikap', 'sikap_lain']),
            'status' => 'terisi',
            'diisi_at' => now(),
        ]);

        return redirect()
            ->route('survei.tampil', $token)
            ->with('survei_selesai', true);
    }
}
