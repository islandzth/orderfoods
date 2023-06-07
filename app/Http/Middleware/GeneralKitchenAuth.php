<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class GeneralKitchenAuth
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
        if ($role !== User::GENERAL_KITCHEN_ROLE) {
            Auth::logout();
            return redirect('/login');
        }
        return $next($request);
    }
}
