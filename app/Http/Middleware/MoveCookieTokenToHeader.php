<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MoveCookieTokenToHeader
{
   public function handle(Request $request, Closure $next)
    {
         logger('BRIDGE BEFORE', ['cookie' => $request->cookie('access_token'), 'all_cookies' => $request->cookies->all()]);

        if (!$request->hasHeader('Authorization') && $request->cookie('access_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('access_token'));
        }

        logger('BRIDGE AFTER', ['auth_header' => $request->header('Authorization')]);

        return $next($request);
    }
}