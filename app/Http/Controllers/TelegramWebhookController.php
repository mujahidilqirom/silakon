<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TelegramNotifier;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramNotifier $telegram)
    {
        $secret = (string) config('telegram.webhook_secret');
        if ($secret && ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            abort(403);
        }

        $message = $request->input('message');
        $text = trim((string) data_get($message, 'text', ''));
        $chatId = data_get($message, 'chat.id');
        if ($chatId && str_starts_with($text, '/start')) {
            $linkToken = trim((string) preg_replace('/^\/start\s*/', '', $text));
            $user = $linkToken ? User::where('telegram_link_token', $linkToken)->first() : null;
            if ($user) {
                $user->update(['telegram_chat_id' => (string) $chatId, 'telegram_link_token' => null, 'telegram_connected_at' => now()]);
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Telegram Terhubung',
                    'detail' => 'Akun Telegram berhasil ditautkan ke SILAKON.',
                    'ip_address' => $request->ip(),
                ]);
                $telegram->send($chatId, "Telegram berhasil terhubung ke akun SILAKON {$user->name}. Anda akan menerima notifikasi sesuai peran.");
            } else {
                $telegram->send($chatId, 'Gunakan tombol Hubungkan Telegram dari profil SILAKON untuk mengaitkan akun ini.');
            }
        }
        return response()->json(['ok' => true]);
    }
}
