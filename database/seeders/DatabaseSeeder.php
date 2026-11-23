<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProgramKonselingKelompok;
use App\Models\SelfHelp;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin SILAKON', 'email' => 'admin@lppp.unnes.ac.id', 'role' => 'admin'],
            ['name' => 'Konselor SILAKON', 'email' => 'konselor@lppp.unnes.ac.id', 'role' => 'konselor'],
            ['name' => 'Koordinator SILAKON', 'email' => 'koordinator@lppp.unnes.ac.id', 'role' => 'koordinator'],
        ] as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [...$account, 'password' => Hash::make('password'), 'is_active' => true]
            );
        }

        foreach ([
            ['judul' => 'Manajemen Stres Akademik', 'deskripsi' => 'Strategi mengelola tekanan akademik, tugas, dan ujian.', 'tags' => ['Kesehatan Mental', 'Akademik'], 'tone' => 'blue', 'tanggal' => '20 Okt 2026', 'waktu' => '09.00 – 12.00 WIB', 'lokasi' => 'Ruang Seminar LPPP', 'fasilitator' => 'Konselor SILAKON', 'kuota' => 20],
            ['judul' => 'Perencanaan Karier Pasca Kampus', 'deskripsi' => 'Menyusun langkah karier dan kesiapan memasuki dunia kerja.', 'tags' => ['Karier', 'Pengembangan Diri'], 'tone' => 'green', 'tanggal' => '27 Okt 2026', 'waktu' => '13.00 – 16.00 WIB', 'lokasi' => 'Aula LPPP', 'fasilitator' => 'Konselor SILAKON', 'kuota' => 30],
            ['judul' => 'Kecemasan Sosial dan Kepercayaan Diri', 'deskripsi' => 'Ruang aman untuk melatih komunikasi dan kepercayaan diri.', 'tags' => ['Sosial', 'Pribadi'], 'tone' => 'purple', 'tanggal' => '3 Nov 2026', 'waktu' => '09.00 – 12.00 WIB', 'lokasi' => 'Ruang Konseling LPPP', 'fasilitator' => 'Konselor SILAKON', 'kuota' => 15],
        ] as $program) ProgramKonselingKelompok::updateOrCreate(['judul' => $program['judul']], [...$program, 'status' => 'Pendaftaran Dibuka']);

        $admin = User::where('role', 'admin')->first();
        foreach ([
            ['judul' => 'Mengatasi Kecemasan Ujian', 'tipe' => 'Artikel', 'topik' => 'Kesehatan Mental', 'detail' => ['isi' => 'Kenali gejala kecemasan ujian dan lakukan persiapan bertahap untuk mengelolanya.', 'excerpt' => 'Panduan praktis untuk menghadapi kecemasan ujian.']],
            ['judul' => 'Teknik Pomodoro untuk Belajar Efektif', 'tipe' => 'Video', 'topik' => 'Akademik', 'detail' => ['link_youtube' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'keterangan' => 'Teknik belajar fokus dengan jeda teratur.']],
            ['judul' => 'Jurnal Mindfulness Harian', 'tipe' => 'Worksheet', 'topik' => 'Kesehatan Mental', 'detail' => ['instruction' => 'Jawab pertanyaan berikut dengan jujur.', 'questions' => [['id' => 'q1', 'label' => 'Bagaimana perasaanmu hari ini?'], ['id' => 'q2', 'label' => 'Apa satu hal yang membuatmu bersyukur?']]]],
        ] as $materi) SelfHelp::updateOrCreate(['judul' => $materi['judul']], [...$materi, 'status' => 'Dipublikasi', 'author_id' => $admin?->id]);

    }
}
