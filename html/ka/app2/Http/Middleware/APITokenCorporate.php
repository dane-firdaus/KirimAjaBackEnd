<?php

namespace App\Http\Middleware;

use Closure;

class APITokenCorporate
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
        if (auth('corporate-api')->user() == null) {
            return response()->json(['status'=> 401], 401);
        }
        return $next($request);
    }
}
