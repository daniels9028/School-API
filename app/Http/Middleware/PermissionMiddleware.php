<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = JWTAuth::user();

        if (! $user || ! $user->can($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
}
