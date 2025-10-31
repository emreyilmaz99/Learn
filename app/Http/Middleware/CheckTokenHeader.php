<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenHeader
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            throw new AuthenticationException('Token sağlanmadı');
        }

        $token = PersonalAccessToken::findToken($bearer);

        if (! $token) {
            throw new AuthenticationException('Token geçersiz');
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            throw new AuthenticationException('Token süresi dolmuş');
        }

        return $next($request);
    }
}
