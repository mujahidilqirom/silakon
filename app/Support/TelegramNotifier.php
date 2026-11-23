<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function botUsername(): ?string
    {
        if (filled(config('telegram.username'))) return ltrim((string) config('telegram.username'), '@');
        $token = config('telegram.token');
        if (! $token) return null;
        try {
            $result = Http::timeout(8)->get(config('telegram.api').'/bot'.$token.'/getMe')->json();
            return data_get($result, 'result.username');
        } catch (\Throwable) { return null; }
    }

    public function sendTo(User $user, string $text): bool
    {
        if (! $user->telegram_chat_id) return false;
        return $this->send($user->telegram_chat_id, $text);
    }

    public function send(string|int $chatId, string $text): bool
    {
        $token = config('telegram.token');
        if (! $token || ! $chatId) return false;
        try {
            $response = Http::timeout(10)->post(config('telegram.api').'/bot'.$token.'/sendMessage', [
                'chat_id' => (string) $chatId,
                'text' => $text,
                'disable_web_page_preview' => true,
            ]);
            if (! $response->successful()) Log::warning('Telegram gagal mengirim pesan', ['status' => $response->status()]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram tidak dapat dihubungi', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function roles(array $roles, string $text): int
    {
        $sent = 0;
        User::query()->whereIn('role', $roles)->where('is_active', true)->whereNotNull('telegram_chat_id')->get()->each(function (User $user) use ($text, &$sent) {
            if ($this->sendTo($user, $text)) $sent++;
        });
        return $sent;
    }
}
