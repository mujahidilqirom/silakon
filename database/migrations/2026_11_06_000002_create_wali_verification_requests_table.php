<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_wali_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('token', 80)->unique();
            $table->string('email_mahasiswa');
            $table->string('email_wali');
            $table->json('data');
            $table->timestamp('berlaku_sampai');
            $table->timestamp('diselesaikan_at')->nullable();
            $table->timestamps();
            $table->index(['email_mahasiswa', 'diselesaikan_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_wali_pengajuan');
    }
};
