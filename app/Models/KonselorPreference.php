<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KonselorPreference extends Model { protected $table='konselor_preferences'; protected $fillable=['user_id','telegram_terhubung','telegram_username','notifikasi']; protected $casts=['telegram_terhubung'=>'boolean','notifikasi'=>'array']; public function user(){return $this->belongsTo(User::class);} }
