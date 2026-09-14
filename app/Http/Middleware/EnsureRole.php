<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'login' => 'Tài khoản không hoạt động hoặc phiên đăng nhập đã hết hạn.',
            ]);
        }

        abort_unless(in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
