<?php

namespace App\Http\Middleware;

use App\DashboardUserToken;
use Closure;

class APIDashboardUser
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
        if (!$request->header('X-KIRIMAJA-TOKEN')) {
            return response()->json(['message' => '?'], 401);
        }

        $token = DashboardUserToken::where('token', $request->header('X-KIRIMAJA-TOKEN'))->first();
        if (is_null($token)) {
            return response()->json(['message' => 'invalid'], 401);
        }
        
        return $next($request);
    }
}
