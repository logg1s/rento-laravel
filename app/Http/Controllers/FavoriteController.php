<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Service;
class FavoriteController extends Controller
{

    const RELATION_TABLES = ['user', 'category', 'location', 'price', 'comment'];

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getFavorites(): JsonResponse
    {
        $user = Auth::user();
        $favorites = $user->serviceFavorite()
            ->with(self::RELATION_TABLES)
            ->where('favorite.user_id', $user->id)
            ->orderBy('id', 'desc')->get();
        return Response::json($favorites);
    }
    // get list favorite: return only array of service_id: [...]
    public function getListFavorite(): JsonResponse
    {
        $user = Auth::user();
        $favorites = $user->serviceFavorite()
            ->orderBy('id', 'desc')->get();
        return Response::json(['service_ids' => $favorites->pluck('id')]);
    }
    public function toggleFavorite(Request $request, string $serviceId): JsonResponse
    {
        $validate = $request->validate(['action' => 'required|boolean']);
        $isLiked = $validate['action'];

        $user = auth()->guard()->user();
        $service = Service::findOrFail($serviceId);
        error_log($isLiked);
        if ($isLiked) {
            $user->serviceFavorite()->attach($service);
        } else {
            $user->serviceFavorite()->detach($service);
        }

        return Response::json(['message' => "Success " . $isLiked ? 'liked' : 'disliked']);
    }
}
