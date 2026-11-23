<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use App\Support\TelegramNotifier;
use App\Models\ActivityLog;

class TelegramController extends Controller
{
    public function link(): RedirectResponse
    {
        $token = Str::random(40);
        auth()->user()->update(['telegram_link_token' => $token]);
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Hubungkan Telegram',
            'detail' => 'Memulai proses pengaitan akun Telegram.',
            'ip_address' => request()->ip(),
        ]);
        $username = app(TelegramNotifier::class)->botUsername();
        if (! $username) return back()->withErrors(['telegram' => 'TELEGRAM_BOT_USERNAME belum diisi di .env.']);
        return redirect()->away('https://t.me/'.$username.'?start='.$token);
    }

    public function unlink(): RedirectResponse
    {
        auth()->user()->update(['telegram_chat_id' => null, 'telegram_link_token' => null, 'telegram_connected_at' => null]);
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Putuskan Telegram',
            'detail' => 'Memutuskan akun Telegram dari SILAKON.',
            'ip_address' => request()->ip(),
        ]);
        return back()->with('sukses', 'Telegram berhasil diputuskan.');
    }
}
