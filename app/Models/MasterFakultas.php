<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MasterFakultas extends Model { protected $table='master_fakultas'; protected $fillable=['nama','singkatan','aktif']; protected $casts=['aktif'=>'boolean']; public function prodi(){return $this->hasMany(MasterProgramStudi::class,'fakultas_id');} }
