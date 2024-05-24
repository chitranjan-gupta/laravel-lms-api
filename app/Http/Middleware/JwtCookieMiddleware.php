<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JwtCookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie("access_token")) {
            if ($token = $request->cookie("access_token")) {
                try {
                    //Log::info("Setting cookie");
                    $request->headers->set('Authorization', 'Bearer '.$token);
                } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                    return response("Unauthorized", 401);
                }
            }
        }
        return $next($request);
    }
}
