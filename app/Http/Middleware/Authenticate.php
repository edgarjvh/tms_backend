<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $jwt_tms = $request->cookie('jwt_tms');
        $authorization = 'Bearer ' . $jwt_tms;

        if($jwt_tms){
            $request->headers->set('Authorization', $authorization);
        }

        $this->authenticate($request, $guards);

        return $next($request);
    }
}
