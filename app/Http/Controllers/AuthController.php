<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->redirectByRole(Auth::user()->role));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $identifier = trim($request->email);
        $attemptKey = sprintf('security:login-failures:%s:%s', mb_strtolower($identifier), $request->ip());

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
        } else {
            $user = User::where('username', $identifier)
                ->orWhere('employee_code', $identifier)
                ->first();
        }

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $request->password], $remember)) {
            Cache::forget($attemptKey);
            $request->session()->regenerate();

            return redirect()->intended($this->redirectByRole(Auth::user()->role));
        }

        $attempts = Cache::increment($attemptKey);
        Cache::put($attemptKey, $attempts, now()->addMinutes(30));

        Log::warning('Failed login attempt detected.', [
            'identifier' => $identifier,
            'ip' => $request->ip(),
            'attempts' => $attempts,
            'resolved_user_id' => $user?->id,
        ]);

        if ($attempts >= 5) {
            Log::alert('Multiple failed login attempts detected.', [
                'identifier' => $identifier,
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'resolved_user_id' => $user?->id,
            ]);
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'manager' => route('manager.dashboard'),
            default => route('employee.dashboard'),
        };
    }
}
