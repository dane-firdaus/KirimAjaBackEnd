<?php

namespace App\Http\Middleware;

use App\DashboardUserToken;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class APITokenJWTDashboard
{
    protected $authorizer;

    public function __construct(JWTAuth $jwtAuth)
    {
        $this->authorizer = $jwtAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if($this->authorizer::parseToken()->authenticate()) {
                return $next($request);
            }
            return response()->json(['status'=> 401, 'message'=> ''], 401);
        } catch(JWTException $e) {
            if (!$request->header('X-KIRIMAJA-TOKEN')) {
                return response()->json(['message' => '?'], 401);
            }
    
            $token = DashboardUserToken::where('token', $request->header('X-KIRIMAJA-TOKEN'))->first();
            if (is_null($token)) {
                return response()->json(['message' => 'invalid'], 401);
            } else {
                return $next($request);
            }
            
            return response()->json(['status'=> 401], 401);
        }
    }
}
