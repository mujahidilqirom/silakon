<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konseli', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_konseli', 40);
            $table->string('nama');
            $table->string('email');
            $table->string('nim')->nullable();
            $table->string('telepon', 40)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('terakhir_mendaftar_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'nim']);
        });

        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konseli_id')->constrained('konseli')->cascadeOnDelete();
            $table->string('kode_pengajuan', 30)->unique();
            $table->string('jenis_layanan', 40)->default('individual');
            $table->string('status', 40)->index();
            $table->string('email');
            $table->string('nim')->nullable();
            $table->json('data');
            $table->timestamp('terverifikasi_at')->nullable();
            $table->foreignId('diperiksa_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diperiksa_at')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'status']);
        });

        Schema::create('riwayat_status_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->string('status_lama', 40)->nullable();
            $table->string('status_baru', 40);
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index(['pengajuan_id', 'created_at']);
        });

        Schema::create('kode_verifikasi_email', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('kode_hash');
            $table->unsignedTinyInteger('percobaan')->default(0);
            $table->unsignedTinyInteger('jumlah_kirim')->default(1);
            $table->timestamp('berlaku_sampai')->nullable();
            $table->timestamp('dikirim_terakhir_at')->nullable();
            $table->timestamp('terverifikasi_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'terverifikasi_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_verifikasi_email');
        Schema::dropIfExists('riwayat_status_pengajuan');
        Schema::dropIfExists('pengajuan');
        Schema::dropIfExists('konseli');
    }
};
