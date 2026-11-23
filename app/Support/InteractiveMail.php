<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;

/**
 * Pengirim email HTML yang konsisten untuk seluruh komunikasi kepada konseli.
 * Seluruh konfigurasi SMTP tetap mengikuti mailer Laravel di .env.
 */
final class InteractiveMail
{
    public static function send(string $to, string $subject, array $data = []): void
    {
        Mail::send('emails.interaktif', array_merge([
            'greeting' => 'Halo,',
            'message' => '',
            'status' => null,
            'details' => [],
            'actionUrl' => null,
            'actionLabel' => null,
            'secondaryUrl' => null,
            'secondaryLabel' => null,
            'quote' => null,
        ], $data), function ($mail) use ($to, $subject) {
            $mail->to($to)->subject($subject);
        });
    }
}
