<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProgramKonselingKelompok extends Model
{
    protected $table = 'program_konseling_kelompok';
    protected $fillable = ['judul','subjudul','deskripsi','tags','tone','tanggal','waktu','lokasi','fasilitator','tautan_grup','kuota','status'];
    protected $casts = ['tags' => 'array'];
    public function peserta(): HasMany { return $this->hasMany(PesertaKonselingKelompok::class, 'program_id'); }
}
