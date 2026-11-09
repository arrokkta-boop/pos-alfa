<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isKasir()) {
                return redirect()->route('kasir.pos');
            } elseif ($user->isGudang()) {
                return redirect()->route('gudang.index');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi Administrator.',
            ])->withInput($request->only('username'));
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            /** @var User $authenticatedUser */
            $authenticatedUser = Auth::user();

            if ($authenticatedUser->isKasir()) {
                return redirect()->intended(route('kasir.pos'))->with('success', 'Selamat datang, ' . $authenticatedUser->name . '!');
            } elseif ($authenticatedUser->isGudang()) {
                return redirect()->intended(route('gudang.index'))->with('success', 'Selamat datang, ' . $authenticatedUser->name . '!');
            } elseif ($authenticatedUser->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang, ' . $authenticatedUser->name . '!');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'Username atau password tidak sesuai.',
        ])->withInput($request->only('username'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
