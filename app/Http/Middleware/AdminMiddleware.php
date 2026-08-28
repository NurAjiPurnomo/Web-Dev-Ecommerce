<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan masuk ke Akun Admin terlebih dahulu.');
        }

        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        
        if (!$user->is_admin) {
            \Illuminate\Support\Facades\Auth::guard('admin')->logout();
            return redirect()->route('home')->with('error', 'Akses ditolak. Halaman ini hanya untuk Administrator.');
        }

        return $next($request);
    }
}
