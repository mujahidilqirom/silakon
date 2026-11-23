<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->unsignedTinyInteger('who5_skor')->nullable()->after('nim');
            $table->decimal('saw_nilai', 8, 4)->nullable()->after('who5_skor');
            $table->string('prioritas', 20)->nullable()->after('saw_nilai');
            $table->index(['prioritas', 'saw_nilai']);
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropIndex(['prioritas', 'saw_nilai']);
            $table->dropColumn(['who5_skor', 'saw_nilai', 'prioritas']);
        });
    }
};
