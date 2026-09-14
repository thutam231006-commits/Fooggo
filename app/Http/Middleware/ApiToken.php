<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiToken
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $token = $request->bearerToken();
        $user = $token
            ? User::where('api_token', hash('sha256', $token))->where('is_active', true)->first()
            : null;

        if (! $user && $token) {
            $user = User::where('api_token', $token)->where('is_active', true)->first();
            $user?->update(['api_token' => hash('sha256', $token)]);
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        } if ($roles && ! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        } auth()->setUser($user);

        return $next($request);
    }
}
