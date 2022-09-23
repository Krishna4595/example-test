<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\RestExceptionHandlerTrait;
use JWTAuth;

class SuperAdmin
{
    use RestExceptionHandlerTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::user();
        if ($user->id == 1) {
            return $next($request);
        }
        return $this->invalidUser();
    }
}
