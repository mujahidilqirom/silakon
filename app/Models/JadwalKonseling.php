<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JadwalKonseling extends Model
{
    protected $table = 'jadwal_konseling';
    protected $fillable = ['pengajuan_id', 'penugasan_id', 'konselor_id', 'tanggal', 'mulai', 'selesai', 'metode', 'lokasi', 'status', 'catatan', 'telegram_reminder_sent_at', 'email_reminder_sent_at'];
    protected $casts = ['tanggal' => 'date', 'telegram_reminder_sent_at' => 'datetime', 'email_reminder_sent_at' => 'datetime'];
    public function pengajuan(): BelongsTo { return $this->belongsTo(Pengajuan::class); }
    public function konselor(): BelongsTo { return $this->belongsTo(User::class, 'konselor_id'); }
    public function penugasan(): BelongsTo { return $this->belongsTo(PenugasanKonseling::class, 'penugasan_id'); }
    public function catatanKonseling(): HasOne { return $this->hasOne(CatatanKonseling::class, 'jadwal_id'); }
    public function survei(): HasOne { return $this->hasOne(SurveiKonseling::class, 'jadwal_id'); }
}
