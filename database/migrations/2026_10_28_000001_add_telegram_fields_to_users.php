<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id', 80)->nullable()->after('phone');
            $table->string('telegram_link_token', 80)->nullable()->unique()->after('telegram_chat_id');
            $table->timestamp('telegram_connected_at')->nullable()->after('telegram_link_token');
        });
        Schema::table('jadwal_konseling', function (Blueprint $table) {
            $table->timestamp('telegram_reminder_sent_at')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_link_token']);
            $table->dropColumn(['telegram_chat_id', 'telegram_link_token', 'telegram_connected_at']);
        });
        Schema::table('jadwal_konseling', function (Blueprint $table) {
            $table->dropColumn('telegram_reminder_sent_at');
        });
    }
};
