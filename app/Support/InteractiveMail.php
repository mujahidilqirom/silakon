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
        $payload = array_merge([
            'greeting' => 'Halo,',
            'message' => '',
            'status' => null,
            'details' => [],
            'actionUrl' => null,
            'actionLabel' => null,
            'secondaryUrl' => null,
            'secondaryLabel' => null,
            'quote' => null,
        ], $data);
        // Illuminate Mail menyuntikkan variabel $message berisi objek MailMessage
        // ke view. Jangan memakai nama itu untuk isi pesan aplikasi.
        $payload['bodyMessage'] = (string) ($payload['message'] ?? '');
        unset($payload['message']);
        Mail::send('emails.interaktif', $payload, function ($mail) use ($to, $subject) {
            $mail->to($to)->subject($subject);
        });
    }
}
