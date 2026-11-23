<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusPengajuan extends Model
{
    protected $table = 'riwayat_status_pengajuan';

    protected $fillable = ['pengajuan_id', 'status_lama', 'status_baru', 'diubah_oleh', 'keterangan'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
