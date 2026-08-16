<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateCsrfCookie
{
    public function handle(Request $request, Closure $next)
    {
        $cookieToken = $request->cookie('XSRF-TOKEN');
        $headerToken = $request->header('X-XSRF-TOKEN');

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
            if (!$cookieToken || !$headerToken || !hash_equals((string) $cookieToken, (string) $headerToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token tidak valid.',
                ], 419);
            }
        }

        return $next($request);
    }
}
