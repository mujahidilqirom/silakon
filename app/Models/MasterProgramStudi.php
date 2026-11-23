<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MasterProgramStudi extends Model { protected $table='master_program_studi'; protected $fillable=['fakultas_id','nama','jenjang','aktif']; protected $casts=['aktif'=>'boolean']; public function fakultas(){return $this->belongsTo(MasterFakultas::class,'fakultas_id');} }
