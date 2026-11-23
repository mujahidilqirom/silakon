<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'peran' => ['nullable', 'in:admin,konselor,koordinator'],
        ]);

        $remember = $request->boolean('remember');
        unset($credentials['peran']);
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->is_active || ($request->filled('peran') && $request->input('peran') !== $user->role) || ! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai, atau akun tidak aktif.'])->withInput();
        }

        $request->session()->regenerate();
        // Jangan memakai redirect()->intended() di sini. URL sebelumnya bisa
        // berasal dari portal role lain (misalnya membuka /admin lalu login
        // sebagai koordinator), sehingga berakhir 403/Forbidden. Portal
        // tujuan harus selalu mengikuti role akun yang berhasil login.
        return redirect(match ($user->role) {
            'admin' => route('admin.dashboard'),
            'konselor' => route('konselor.dashboard'),
            'koordinator' => route('koordinator.dashboard'),
            default => route('login'),
        });
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
