<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penugasan_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('konselor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('ditugaskan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['pengajuan_id', 'konselor_id']);
        });

        Schema::create('ketersediaan_konselor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konselor_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('mulai');
            $table->time('selesai');
            $table->string('catatan')->nullable();
            $table->string('status', 30)->default('tersedia');
            $table->timestamps();
            $table->index(['konselor_id', 'tanggal']);
        });

        Schema::create('jadwal_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('penugasan_id')->nullable()->constrained('penugasan_konseling')->nullOnDelete();
            $table->foreignId('konselor_id')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('mulai');
            $table->time('selesai');
            $table->string('metode', 40)->nullable();
            $table->string('lokasi')->nullable();
            $table->string('status', 30)->default('terjadwal');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['konselor_id', 'tanggal']);
        });

        Schema::create('catatan_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->unique()->constrained('jadwal_konseling')->cascadeOnDelete();
            $table->foreignId('konselor_id')->constrained('users')->restrictOnDelete();
            $table->string('kehadiran', 30)->default('hadir');
            $table->string('hasil', 100)->nullable();
            $table->text('ringkasan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamp('dicatat_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_konseling');
        Schema::dropIfExists('jadwal_konseling');
        Schema::dropIfExists('ketersediaan_konselor');
        Schema::dropIfExists('penugasan_konseling');
    }
};
