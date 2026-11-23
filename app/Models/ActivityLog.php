<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'detail', 'ip_address'];
    public function user() { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::created(function () {
            // Simpan log operasional selama satu tahun. Pembersihan dijalankan
            // paling banyak sekali sehari agar tabel tidak terus membesar.
            if (Cache::add('activity_logs_pruned_at', true, now()->addDay())) {
                static::where('created_at', '<', now()->subYear())->delete();
            }
        });
    }
}
