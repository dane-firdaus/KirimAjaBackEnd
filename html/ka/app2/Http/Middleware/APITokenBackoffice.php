<?php

namespace App\Http\Middleware;

use Closure;

class APITokenBackoffice
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
        if (auth('backoffice-users')->user() == null) {
            return response()->json(['status'=> 40], 401);
        }
        return $next($request);
    }
}
