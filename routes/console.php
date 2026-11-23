<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Support\TelegramNotifier;
use App\Models\JadwalKonseling;
use App\Models\ActivityLog;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:poll {--once : Verwerk één keer en stop}', function (TelegramNotifier $telegram) {
    $token = config('telegram.token');
    if (! $token) { $this->error('TELEGRAM_BOT_TOKEN belum diisi.'); return 1; }
    $offset = (int) cache('telegram.update_offset', 0);
    do {
        // Long polling Telegram dapat menahan koneksi hingga nilai `timeout`.
        // Beri batas HTTP lebih longgar agar koneksi tidak diputus sebelum respons selesai.
        $response = Http::timeout(60)->connectTimeout(15)->get(config('telegram.api').'/bot'.$token.'/getUpdates', ['timeout' => 30, 'offset' => $offset]);
        if (! $response->successful()) { $this->error('Telegram API gagal: HTTP '.$response->status()); return 1; }
        foreach ($response->json('result', []) as $update) {
            $offset = max($offset, ((int) ($update['update_id'] ?? 0)) + 1);
            $message = $update['message'] ?? null;
            $text = trim((string) ($message['text'] ?? ''));
            $chatId = data_get($message, 'chat.id');
            if (! $chatId || ! str_starts_with($text, '/start')) continue;
            $linkToken = trim((string) preg_replace('/^\/start\s*/', '', $text));
            $user = $linkToken ? User::where('telegram_link_token', $linkToken)->first() : null;
            if ($user) {
                $user->update(['telegram_chat_id' => (string) $chatId, 'telegram_link_token' => null, 'telegram_connected_at' => now()]);
                ActivityLog::create(['user_id'=>$user->id,'action'=>'Telegram Terhubung','detail'=>'Akun Telegram berhasil ditautkan ke SILAKON.','ip_address'=>null]);
                $telegram->send($chatId, "Telegram berhasil terhubung ke akun SILAKON {$user->name}. Anda akan menerima notifikasi sesuai peran.");
            } else {
                $telegram->send($chatId, 'Gunakan tombol Hubungkan Telegram dari profil SILAKON untuk mengaitkan akun ini.');
            }
        }
        cache(['telegram.update_offset' => $offset], now()->addDay());
        if ($this->option('once')) break;
    } while (true);
    return 0;
})->purpose('Menghubungkan chat pribadi Telegram dan memproses /start');

Artisan::command('telegram:reminders', function (TelegramNotifier $telegram) {
    $jadwal = JadwalKonseling::with(['konselor', 'pengajuan.konseli'])
        ->whereDate('tanggal', today())->where('status', 'terjadwal')->whereNull('telegram_reminder_sent_at')->get();
    foreach ($jadwal as $item) {
        if ($item->konselor?->telegram_chat_id) $telegram->sendTo($item->konselor,
            "⏰ Pengingat konseling SILAKON hari ini\n\n".
            "Kode: {$item->pengajuan?->kode_pengajuan}\n".
            "Konseli: ".($item->pengajuan?->konseli?->nama ?: 'Konseli')."\n".
            "Waktu: ".substr((string) $item->mulai, 0, 5)."–".substr((string) $item->selesai, 0, 5)."\n".
            "Lokasi: ".($item->lokasi ?: $item->metode ?: '-'));
        if ($item->pengajuan?->email && ! $item->email_reminder_sent_at) {
            try { \App\Support\InteractiveMail::send($item->pengajuan->email, 'Pengingat Konseling Hari Ini', [
                'greeting' => 'Halo, '.($item->pengajuan?->konseli?->nama ?: 'Konseli'),
                'message' => 'Hari ini adalah waktu yang telah disiapkan untuk Anda. Hadirlah dengan tenang dan berikan ruang untuk menceritakan apa yang Anda rasakan.',
                'status' => 'Jadwal hari ini',
                'details' => ['Kode pengajuan' => $item->pengajuan?->kode_pengajuan, 'Waktu' => substr((string) $item->mulai, 0, 5).'–'.substr((string) $item->selesai, 0, 5).' WIB', 'Lokasi' => ($item->lokasi ?: $item->metode ?: '-')],
                'actionUrl' => route('cek-status.hasil', ['kode' => $item->pengajuan->kode_pengajuan, 'email' => $item->pengajuan->email]),
                'actionLabel' => 'Lihat Detail Jadwal',
                'quote' => 'Tidak perlu menyiapkan kata-kata yang sempurna. Cukup hadir apa adanya.',
            ]); } catch (\Throwable $e) { report($e); }
            $item->update(['email_reminder_sent_at' => now()]);
        }
        if ($item->konselor?->telegram_chat_id) $item->update(['telegram_reminder_sent_at' => now()]);
    }
    $this->info('Pengingat diproses: '.$jadwal->count());
})->purpose('Mengirim pengingat Telegram untuk jadwal hari ini');

Artisan::command('telegram:webhook:set {url : URL HTTPS publik webhook}', function (string $url) {
    if (! str_starts_with(strtolower($url), 'https://')) { $this->error('URL webhook harus menggunakan HTTPS.'); return 1; }
    $token = config('telegram.token');
    $secret = config('telegram.webhook_secret');
    $payload = ['url' => rtrim($url, '/') . '/telegram/webhook'];
    if ($secret) $payload['secret_token'] = $secret;
    $response = Http::timeout(15)->post(config('telegram.api').'/bot'.$token.'/setWebhook', $payload);
    $response->successful() ? $this->info('Webhook Telegram berhasil didaftarkan.') : $this->error('Webhook gagal: '.$response->body());
    return $response->successful() ? 0 : 1;
})->purpose('Mendaftarkan webhook Telegram pada domain HTTPS');

Artisan::command('telegram:webhook:delete', function () {
    $token = config('telegram.token');
    $response = Http::timeout(15)->post(config('telegram.api').'/bot'.$token.'/deleteWebhook');
    $response->successful() ? $this->info('Webhook dihapus; polling dapat digunakan kembali.') : $this->error('Webhook gagal dihapus: '.$response->body());
    return $response->successful() ? 0 : 1;
})->purpose('Menghapus webhook Telegram');
