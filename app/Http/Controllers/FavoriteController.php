<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFavoriteUpdate;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class FavoriteController extends Controller
{

    const RELATION_TABLES = ['user', 'category', 'location', 'price', 'comment'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getFavorites()
    {
        $user = Auth::user();
            $favorites = $user->serviceFavorite()
                ->with(self::RELATION_TABLES)
                ->where('favorite.user_id', $user->id)
                ->orderBy('id', 'desc')->get();

        return response()->json($favorites);
    }

    public function toggleFavorite(Request $request, string $serviceId)
    {
        $validate = $request->validate(['action' => 'required|boolean']);
        $isLiked = $validate['action'];

        $user = auth()->guard()->user();
        $service = Service::findOrFail($serviceId);
        error_log($isLiked);
        if($isLiked){
            $user->serviceFavorite()->attach($service);
        } else {
            $user->serviceFavorite()->detach($service);
        }

        return response()->json(['message' => "Success " . $isLiked ? 'liked' : 'disliked']);
    }
}
