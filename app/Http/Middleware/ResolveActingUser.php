<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActingUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->header('X-User-Id');

        if ($userId && is_numeric($userId)) {
            $user = User::query()->find((int) $userId);
            if ($user && $user->status === 'active') {
                $request->attributes->set('acting_user', $user);
            }
        }

        return $next($request);
    }
}
