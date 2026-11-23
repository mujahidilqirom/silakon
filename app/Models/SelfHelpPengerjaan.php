<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SelfHelpPengerjaan extends Model { protected $table='self_help_pengerjaan'; protected $fillable=['self_help_id','konseli_id','nama','nim','fakultas','prodi','email','jawaban','selesai_at']; protected $casts=['jawaban'=>'array','selesai_at'=>'datetime']; public function materi(){return $this->belongsTo(SelfHelp::class,'self_help_id');} public function konseli(){return $this->belongsTo(Konseli::class);} }
