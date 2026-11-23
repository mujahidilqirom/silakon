<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survei_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->unique()->constrained('jadwal_konseling')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('belum_dikirim');
            $table->json('jawaban')->nullable();
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamp('diisi_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_konseling');
    }
};
