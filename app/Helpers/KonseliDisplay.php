<?php

namespace App\Helpers;

use App\Models\Pengajuan;

/**
 * Menentukan identitas yang ditampilkan kepada konselor dari snapshot pengajuan.
 * Wali mahasiswa dapat mengajukan untuk dirinya sendiri atau untuk mahasiswa.
 */
final class KonseliDisplay
{
    public static function from(Pengajuan $pengajuan): array
    {
        $data = (array) ($pengajuan->data ?? []);
        $konseli = $pengajuan->konseli;
        $jenis = (string) ($konseli?->jenis_konseli ?? '');
        $wali = $jenis === 'wali_mahasiswa';
        $subjek = $wali ? (string) ($data['wali_subjek_konseling'] ?? 'wali') : null;
        $untukMahasiswa = $wali && $subjek === 'mahasiswa';

        $namaWali = $data['wali_nama'] ?? ($konseli?->nama ?? '-');
        $teleponWali = $data['wali_telepon'] ?? ($konseli?->telepon ?? '-');
        $namaMahasiswa = $data['wali_mahasiswa_nama'] ?? '-';
        $teleponMahasiswa = $data['wali_mahasiswa_telepon'] ?? '-';
        $nama = $untukMahasiswa ? $namaMahasiswa : ($wali ? $namaWali : ($konseli?->nama ?? '-'));
        $telepon = $untukMahasiswa ? $teleponMahasiswa : ($wali ? $teleponWali : ($konseli?->telepon ?? '-'));
        $nim = $untukMahasiswa
            ? ($data['wali_mahasiswa_nim'] ?? '-')
            : ($pengajuan->nim ?: ($data['mahasiswa_nim'] ?? '-'));
        $prodi = $untukMahasiswa
            ? ($data['wali_mahasiswa_prodi'] ?? '-')
            : ($data['mahasiswa_jurusan'] ?? $data['alumni_jurusan'] ?? data_get($konseli?->data, 'program_studi', '-'));
        $fakultas = $untukMahasiswa
            ? ($data['wali_mahasiswa_fakultas'] ?? '-')
            : ($data['mahasiswa_fakultas'] ?? $data['alumni_fakultas'] ?? '-');

        $jenisLabel = $jenis === 'wali_mahasiswa' ? 'Wali Mahasiswa' : ucfirst(str_replace('_', ' ', $jenis ?: 'konseli'));
        return [
            'nama' => $nama ?: '-',
            'telepon' => $telepon ?: '-',
            'nim' => $nim ?: '-',
            'prodi' => $prodi ?: '-',
            'fakultas' => $fakultas ?: '-',
            'jenis' => $jenisLabel,
            'subjek' => $wali ? ($untukMahasiswa ? 'Mahasiswa' : 'Wali Mahasiswa') : null,
            'wali_nama' => $wali ? $namaWali : null,
            'wali_telepon' => $wali ? $teleponWali : null,
            'mahasiswa_nama' => $wali ? $namaMahasiswa : null,
            'mahasiswa_nim' => $wali ? ($data['wali_mahasiswa_nim'] ?? '-') : null,
            'mahasiswa_telepon' => $wali ? $teleponMahasiswa : null,
            'email' => $pengajuan->email ?: ($konseli?->email ?? '-'),
        ];
    }
}
