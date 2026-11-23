<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    public const STATUS_MENUNGGU_PEMERIKSAAN = 'menunggu_pemeriksaan';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DIBATALKAN = 'dibatalkan';
    public const STATUS_SESI_LANJUTAN = 'sesi_lanjutan';

    protected $fillable = [
        'konseli_id', 'kode_pengajuan', 'jenis_layanan', 'status', 'email', 'nim', 'data',
        'who5_skor', 'saw_nilai', 'prioritas',
        'terverifikasi_at', 'diperiksa_oleh', 'diperiksa_at', 'alasan_penolakan', 'dibatalkan_at',
    ];

    protected $casts = [
        'data' => 'array',
        'who5_skor' => 'integer',
        'saw_nilai' => 'decimal:4',
        'terverifikasi_at' => 'datetime',
        'diperiksa_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
    ];

    public function konseli(): BelongsTo
    {
        return $this->belongsTo(Konseli::class);
    }

    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusPengajuan::class, 'pengajuan_id');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(PenugasanKonseling::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalKonseling::class);
    }

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DITOLAK, self::STATUS_DIBATALKAN, 'selesai']);
    }
}
