<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MasterKategoriMasalah extends Model { protected $table='master_kategori_masalah'; protected $fillable=['nama','aktif']; protected $casts=['aktif'=>'boolean']; }
