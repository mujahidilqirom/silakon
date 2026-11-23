<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

/** Mencatat percobaan masuk ke area internal yang ditolak middleware auth. */
class LogUnauthorizedAccess
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $path = trim($request->path(), '/');
        $internal = str_starts_with($path, 'admin')
            || str_starts_with($path, 'konselor')
            || str_starts_with($path, 'koordinator');
        $location = (string) $response->headers->get('Location', '');
        $redirectLogin = $response->isRedirection() && str_contains($location, '/login');

        $accessDenied = in_array($response->getStatusCode(), [401, 403], true) || $redirectLogin;
        if ($internal && $accessDenied && ! $request->user()) {
            try {
                ActivityLog::create([
                    'user_id' => null,
                    'action' => 'Akses Ditolak',
                    'detail' => 'Percobaan akses tanpa login ke '.$request->method().' /'.$path.'.',
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $response;
    }
}
