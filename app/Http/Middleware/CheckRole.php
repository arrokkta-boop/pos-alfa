<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Allowed role names or IDs
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Ensure user is logged in
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // 2. Ensure account is active
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi Administrator.');
        }

        // 3. Resolve user role name
        $userRoleName = strtolower($user->role?->role_name ?? '');

        // 4. Normalize allowed roles (support 'UKasir', 'kasir', '1', etc.)
        $allowed = array_map(function ($r) {
            $cleaned = strtolower(trim($r));
            return str_starts_with($cleaned, 'u') && strlen($cleaned) > 1 ? substr($cleaned, 1) : $cleaned;
        }, $roles);

        $currentNormalized = str_starts_with($userRoleName, 'u') && strlen($userRoleName) > 1 
            ? substr($userRoleName, 1) 
            : $userRoleName;

        if (! in_array($currentNormalized, $allowed, true) && ! in_array($userRoleName, array_map('strtolower', $roles), true)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
