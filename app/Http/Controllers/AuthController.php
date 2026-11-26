<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

    public function googleRedirect()
    {
        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function googleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = strtolower(trim((string) $googleUser->getEmail()));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->withErrors(['email' => 'Login Google gagal atau dibatalkan. Silakan coba lagi.']);
        }

        if ($email === '') {
            return redirect()->route('login')->withErrors(['email' => 'Google tidak mengembalikan alamat email yang valid.']);
        }

        $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Email Google belum terdaftar sebagai akun SILAKON.']);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

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

    public function forgotForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotSend(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Tautan reset password telah dikirim ke email Anda.')
            : back()->withErrors(['email' => $this->passwordMessage($status)]);
    }

    public function resetForm(Request $request)
    {
        return view('auth.reset-password', ['token' => $request->route('token'), 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan masuk.')
            : back()->withErrors(['email' => $this->passwordMessage($status)])->withInput($request->only('email'));
    }

    private function passwordMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email tersebut belum terdaftar sebagai akun SILAKON.',
            Password::RESET_THROTTLED => 'Permintaan reset terlalu sering. Silakan coba lagi beberapa saat.',
            Password::INVALID_TOKEN => 'Tautan reset password tidak valid atau sudah kedaluwarsa.',
            default => 'Permintaan reset password tidak dapat diproses. Silakan coba lagi.',
        };
    }
}
