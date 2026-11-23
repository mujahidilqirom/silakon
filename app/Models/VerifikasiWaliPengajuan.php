<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiWaliPengajuan extends Model
{
    protected $table = 'verifikasi_wali_pengajuan';

    protected $fillable = ['token', 'email_mahasiswa', 'email_wali', 'data', 'berlaku_sampai', 'diselesaikan_at'];

    protected $casts = ['data' => 'array', 'berlaku_sampai' => 'datetime', 'diselesaikan_at' => 'datetime'];
}
