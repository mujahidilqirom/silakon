<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('singkatan', 20)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('master_program_studi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->constrained('master_fakultas')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jenjang', 20);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->index(['fakultas_id', 'jenjang']);
        });
        Schema::create('master_kategori_masalah', function (Blueprint $table) {
            $table->id(); $table->string('nama')->unique(); $table->boolean('aktif')->default(true); $table->timestamps();
        });
        Schema::create('master_lokasi_ruang', function (Blueprint $table) {
            $table->id(); $table->string('nama')->unique(); $table->text('deskripsi')->nullable(); $table->boolean('aktif')->default(true); $table->timestamps();
        });
        Schema::create('master_template_pesan', function (Blueprint $table) {
            $table->id(); $table->string('kode')->unique(); $table->string('nama'); $table->string('kanal', 30); $table->text('isi'); $table->boolean('aktif')->default(true); $table->timestamps();
        });
        Schema::create('konselor_preferences', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('telegram_terhubung')->default(false); $table->string('telegram_username')->nullable(); $table->json('notifikasi')->nullable(); $table->timestamps();
        });
        Schema::create('self_help_pengerjaan', function (Blueprint $table) {
            $table->id(); $table->foreignId('self_help_id')->constrained('self_help')->cascadeOnDelete();
            $table->foreignId('konseli_id')->nullable()->constrained('konseli')->nullOnDelete();
            $table->string('email')->nullable(); $table->json('jawaban')->nullable(); $table->timestamp('selesai_at')->nullable(); $table->timestamps();
            $table->index(['self_help_id', 'selesai_at']);
        });

        $catalog = config('silakon.katalog_fakultas_prodi', []);
        foreach ($catalog as $nama => $levels) {
            preg_match('/\(([^)]+)\)$/', $nama, $match);
            $fakultasId = DB::table('master_fakultas')->insertGetId(['nama' => $nama, 'singkatan' => $match[1] ?? null, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($levels as $jenjang => $programs) {
                foreach ($programs as $program) DB::table('master_program_studi')->insert(['fakultas_id' => $fakultasId, 'nama' => $program, 'jenjang' => $jenjang, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        foreach (['Motivasi','Stres kuliah','Depresi','Harga diri/kepercayaan diri','Pengekspresian diri/perasaan','Kehilangan','Kecemasan/panik','Trauma','Pengasuhan','Home Sickness','Putus asa','Manajemen Keuangan','Konflik dengan pasangan','Tekanan Keluarga','Manajemen Waktu','Spiritualitas/Keberagamaan','Hambatan di masa transisi','Kesepian','Konflik interpersonal','Karier','Yang lain'] as $nama) DB::table('master_kategori_masalah')->insert(['nama'=>$nama,'created_at'=>now(),'updated_at'=>now()]);
        foreach ([['Ruang Konseling LPPP Unnes','Ruang layanan konseling LPPP Unnes'],['LAB BK FIPP','Laboratorium Bimbingan Konseling FIPP'],['Zoom','Sesi konseling daring melalui Zoom']] as [$nama,$deskripsi]) DB::table('master_lokasi_ruang')->insert(['nama'=>$nama,'deskripsi'=>$deskripsi,'created_at'=>now(),'updated_at'=>now()]);
        foreach ([['pengajuan-diterima','Pengajuan Diterima','Email','Yth. {{nama_konseli}}, pengajuan konseling {{kode_tiket}} telah diterima.'],['jadwal-ditetapkan','Jadwal Ditetapkan','Email','Jadwal konseling {{kode_tiket}} bersama {{nama_konselor}} pada {{tanggal}}, pukul {{waktu}} di {{lokasi}}.'],['pengingat-h1','Pengingat H-1','WhatsApp','Pengingat sesi konseling {{kode_tiket}} besok pukul {{waktu}} di {{lokasi}}.']] as [$kode,$nama,$kanal,$isi]) DB::table('master_template_pesan')->insert(compact('kode','nama','kanal','isi') + ['created_at'=>now(),'updated_at'=>now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('self_help_pengerjaan'); Schema::dropIfExists('konselor_preferences'); Schema::dropIfExists('master_template_pesan'); Schema::dropIfExists('master_lokasi_ruang'); Schema::dropIfExists('master_kategori_masalah'); Schema::dropIfExists('master_program_studi'); Schema::dropIfExists('master_fakultas');
    }
};
