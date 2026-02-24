<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user belum login, lanjutkan
        if (!$user) {
            return $next($request);
        }

        // Cek apakah password masih default
        if ($user->hasDefaultPassword()) {
            // Daftar route yang dizinkan saat password masih default
            $allowedRoutes = [
                'password.edit',
                'password.update',
                'password.update.custom', // Pastikan nama route ini benar di web.php
                'logout',
            ];

            // Jika route saat ini bukan yang diizinkan, redirect ke halaman ganti password
            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('password.edit')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti password default sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
