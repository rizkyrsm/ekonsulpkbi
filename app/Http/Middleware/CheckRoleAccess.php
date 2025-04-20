<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role, $roles)) {
            session()->flash('toasts', [
                ...session('toasts', []),
                ['type' => 'warning', 'message' => 'Kamu tidak punya akses ke menu ini!'],
            ]);

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
