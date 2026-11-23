<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('self_help_pengerjaan', function (Blueprint $table) {
            $table->string('nama', 150)->nullable()->after('self_help_id');
            $table->string('nim', 40)->nullable()->after('nama');
            $table->string('fakultas', 150)->nullable()->after('nim');
            $table->string('prodi', 150)->nullable()->after('fakultas');
        });
    }

    public function down(): void
    {
        Schema::table('self_help_pengerjaan', function (Blueprint $table) {
            $table->dropColumn(['nama', 'nim', 'fakultas', 'prodi']);
        });
    }
};
