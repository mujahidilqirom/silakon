<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) return response()->json(['message' => 'Unauthenticated.'], 401);
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            $this->logDenied($request, $user, 'Akun tidak aktif mencoba mengakses area internal.');
            auth()->logout();
            if ($request->expectsJson()) return response()->json(['message' => 'Akun tidak aktif.'], 403);
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda tidak aktif.']);
        }

        if (! in_array($user->role, $roles, true)) {
            $this->logDenied($request, $user, 'Role '.$user->role.' mencoba mengakses area yang membutuhkan role: '.implode(', ', $roles).'.');
            if ($request->expectsJson()) return response()->json(['message' => 'Forbidden.'], 403);
            $dashboard = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'konselor' => route('konselor.dashboard'),
                'koordinator' => route('koordinator.dashboard'),
                default => route('login'),
            };
            return redirect($dashboard)->with('role_redirect', 'Anda diarahkan ke dashboard sesuai peran akun Anda.');
        }

        return $next($request);
    }

    private function logDenied(Request $request, $user, string $detail): void
    {
        try {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Akses Ditolak',
                'detail' => $detail.' URL: '.$request->method().' /'.trim($request->path(), '/').'.',
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
