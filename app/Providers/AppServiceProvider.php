<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        // Timeout sesi dapat diubah Koordinator melalui Pengaturan Sistem.
        // Fallback ke .env jika tabel/settings belum tersedia.
        try {
            $menit = (int) (Setting::where('key', 'sesi_timeout_menit')->value('value') ?? config('session.lifetime', 120));
            config(['session.lifetime' => max(5, min(120, $menit))]);
        } catch (\Throwable) {
            // Saat instalasi/migrasi awal tabel settings mungkin belum tersedia.
        }
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(8)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('otp', fn (Request $request) => Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('public-submit', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('status-check', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('password.email', fn (Request $request) => Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('password.reset', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
