<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class APITokenJWT
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
            return response()->json(['status'=> 401], 401);
        }
    }
}
