<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class IsAdmin extends Middleware
{
        /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if($request->__authenticatedUser->user_type == config('app.ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == config('app.SUPERADMIN_ROLE_ID'))
        {
            $response = $next($request);
            return response($response);
        }

        throw new AuthorizationException('You are not authorized to view this page.');
    }
}
