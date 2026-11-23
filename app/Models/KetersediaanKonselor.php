<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetersediaanKonselor extends Model
{
    protected $table = 'ketersediaan_konselor';
    protected $fillable = ['konselor_id', 'tanggal', 'mulai', 'selesai', 'catatan', 'status'];
    protected $casts = ['tanggal' => 'date'];
    public function konselor(): BelongsTo { return $this->belongsTo(User::class, 'konselor_id'); }
}
