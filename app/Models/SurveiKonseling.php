<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveiKonseling extends Model
{
    protected $table = 'survei_konseling';
    protected $fillable = ['jadwal_id', 'token', 'status', 'jawaban', 'dikirim_at', 'diisi_at'];
    protected $casts = ['jawaban' => 'array', 'dikirim_at' => 'datetime', 'diisi_at' => 'datetime'];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalKonseling::class, 'jadwal_id');
    }
}
