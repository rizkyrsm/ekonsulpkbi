<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->role !== 'ADMIN') {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

