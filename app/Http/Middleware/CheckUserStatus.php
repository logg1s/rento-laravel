<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard()->user();

        if ($user) {
            if ($user->status === UserStatusEnum::BLOCKED->value) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa'
                ], 403);
            }

            if ($user->status === UserStatusEnum::PENDING->value) {
                return response()->json([
                    'message' => 'Tài khoản của bạn chưa được xác thực'
                ], 403);
            }
        }

        return $next($request);
    }
}