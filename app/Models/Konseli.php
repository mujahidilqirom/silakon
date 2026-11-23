<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konseli extends Model
{
    protected $table = 'konseli';

    protected $fillable = [
        'jenis_konseli', 'nama', 'email', 'nim', 'telepon', 'data', 'terakhir_mendaftar_at',
    ];

    protected $casts = [
        'data' => 'array',
        'terakhir_mendaftar_at' => 'datetime',
    ];

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }
}
