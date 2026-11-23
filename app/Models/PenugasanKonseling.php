<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenugasanKonseling extends Model
{
    protected $table = 'penugasan_konseling';
    protected $fillable = ['pengajuan_id', 'konselor_id', 'ditugaskan_oleh', 'status', 'catatan'];
    public function pengajuan(): BelongsTo { return $this->belongsTo(Pengajuan::class); }
    public function konselor(): BelongsTo { return $this->belongsTo(User::class, 'konselor_id'); }
}
