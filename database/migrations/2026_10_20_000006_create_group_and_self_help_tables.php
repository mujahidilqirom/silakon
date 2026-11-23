<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_konseling_kelompok', function (Blueprint $table) {
            $table->id();
            $table->string('judul'); $table->string('subjudul')->nullable(); $table->text('deskripsi')->nullable();
            $table->json('tags')->nullable(); $table->string('tone', 20)->default('blue');
            $table->string('tanggal', 60); $table->string('waktu', 60); $table->string('lokasi');
            $table->string('fasilitator'); $table->string('tautan_grup')->nullable();
            $table->unsignedInteger('kuota')->default(1); $table->string('status', 30)->default('Pendaftaran Dibuka');
            $table->timestamps();
        });

        Schema::create('peserta_konseling_kelompok', function (Blueprint $table) {
            $table->id(); $table->foreignId('program_id')->constrained('program_konseling_kelompok')->cascadeOnDelete();
            $table->string('kode', 30)->unique(); $table->string('nama'); $table->string('nim', 30);
            $table->string('semester', 30); $table->string('fakultas'); $table->string('prodi');
            $table->string('email'); $table->string('wa', 30); $table->string('status', 30)->default('Terdaftar');
            $table->timestamps(); $table->index(['program_id', 'nim']);
        });

        Schema::create('self_help', function (Blueprint $table) {
            $table->id(); $table->string('judul'); $table->string('tipe', 20); $table->string('topik', 80);
            $table->string('status', 20)->default('Draft'); $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('detail')->nullable(); $table->string('thumbnail')->nullable(); $table->string('file_pdf')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_help'); Schema::dropIfExists('peserta_konseling_kelompok'); Schema::dropIfExists('program_konseling_kelompok');
    }
};
