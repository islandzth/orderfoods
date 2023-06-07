<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Support\Facades\Auth;
class ExportAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        $user = \Auth::user();
        $role = $user->role;
        if ($role !== User::EXPORT_ROLE) {
            Auth::logout();
            return redirect('/login');
        }
        return $next($request);
    }
}
