<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PesertaKonselingKelompok extends Model
{
    protected $table = 'peserta_konseling_kelompok';
    protected $fillable = ['program_id','nama','nim','semester','fakultas','prodi','email','wa','status'];
    public function program(): BelongsTo { return $this->belongsTo(ProgramKonselingKelompok::class, 'program_id'); }
}
