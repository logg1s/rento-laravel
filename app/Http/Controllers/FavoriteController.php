<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFavoriteUpdate;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class FavoriteController extends Controller
{
    const CACHE_TTL = 30; // seconds
    const JOB_DELAY = 60; // seconds

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getFavorites()
    {
        $user = Auth::user();
        $redisKey = "user:{$user->id}:favorites";

        $favoriteIds = Redis::smembers($redisKey);


        if (!empty($favoriteIds)) {
            $favorites = Service::whereIn('id', $favoriteIds)
                ->get();

        } else {
            $favorites = $user->serviceFavorite()
                ->where('favorite.user_id', $user->id)
                ->get();

            if ($favorites->isNotEmpty()) {
                Redis::pipeline(function ($pipe) use ($redisKey, $favorites) {
                    foreach ($favorites as $favorite) {
                        $pipe->sadd($redisKey, $favorite->id);
                    }
                    $pipe->expire($redisKey, self::CACHE_TTL);
                });
            }
        }

        return response()->json($favorites);
    }

    public function toggleFavorite($serviceId)
    {
        $user = Auth::user();
        $redisKey = "user:{$user->id}:favorites";

        $isLiked = Redis::sismember($redisKey, $serviceId);

        // Dispatch job với delay để gom nhóm các thao tác
        ProcessFavoriteUpdate::dispatch([
            'user_id' => $user->id,
            'service_id' => $serviceId,
            'action' => $isLiked ? 'detach' : 'attach'
        ])->delay(Carbon::now()->addSeconds(self::JOB_DELAY));

        // Update Redis ngay lập tức
        if ($isLiked) {
            Redis::srem($redisKey, $serviceId);
            return response()->json(['message' => 'Đã bỏ thích dịch vụ'], 200);
        } else {
            Redis::sadd($redisKey, $serviceId);
            return response()->json(['message' => 'Đã thích dịch vụ'], 201);
        }
    }
}
