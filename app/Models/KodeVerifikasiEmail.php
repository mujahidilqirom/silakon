<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeVerifikasiEmail extends Model
{
    protected $table = 'kode_verifikasi_email';

    protected $fillable = [
        'email', 'kode_hash', 'percobaan', 'jumlah_kirim', 'berlaku_sampai', 'dikirim_terakhir_at', 'terverifikasi_at',
    ];

    protected $casts = [
        'berlaku_sampai' => 'datetime',
        'dikirim_terakhir_at' => 'datetime',
        'terverifikasi_at' => 'datetime',
    ];
}
