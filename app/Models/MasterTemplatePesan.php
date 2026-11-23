<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MasterTemplatePesan extends Model { protected $table='master_template_pesan'; protected $fillable=['kode','nama','kanal','isi','aktif']; protected $casts=['aktif'=>'boolean']; }
