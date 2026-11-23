<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MasterLokasiRuang extends Model { protected $table='master_lokasi_ruang'; protected $fillable=['nama','deskripsi','aktif']; protected $casts=['aktif'=>'boolean']; }
