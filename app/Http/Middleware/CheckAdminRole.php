<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah pengguna terautentikasi dan memiliki peran admin
        if ($request->user() && $request->user()->role !== 'ADMIN') {
            // Arahkan pengguna ke dashboard jika bukan admin
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

