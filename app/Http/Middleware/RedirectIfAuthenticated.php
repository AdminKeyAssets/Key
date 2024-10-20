<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check() && request()->route()->getName() != 'admin.logout') {
            if (auth()->user()) {
                if (auth()->user()->getRolesNameAttribute() == 'administrator') {
                    return redirect()->route('admin.user.index');
                } elseif (auth()->user()->getRolesNameAttribute() == 'Sale Manager'||
                    auth()->user()->getRolesNameAttribute() == 'Sales Manager') {
                    return redirect()->route('lead.index');
                } else {
                    return redirect()->route('admin.investor.index');
                }
            }
            return redirect()->route('admin.user.index');
        }


        return $next($request);
    }
}
