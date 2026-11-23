<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanKonseling extends Model
{
    protected $table = 'catatan_konseling';
    protected $fillable = ['jadwal_id', 'konselor_id', 'kehadiran', 'hasil', 'ringkasan', 'tindak_lanjut', 'dicatat_at'];
    protected $casts = ['dicatat_at' => 'datetime'];
    public function jadwal(): BelongsTo { return $this->belongsTo(JadwalKonseling::class, 'jadwal_id'); }
    public function konselor(): BelongsTo { return $this->belongsTo(User::class, 'konselor_id'); }
}
