<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Service;
use App\Models\ViewedServiceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ServiceController extends Controller
{
    const DEFAULT_SIZE = 5;
    const CACHE_TTL = 30;
    const RELATION_TABLES = ['user', 'category', 'location', 'price'];
    const RELATION_TABLE_DETAILS = ['userFavorite', 'benefit', 'comment' => ['user']];
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }

    public function getKeyAll($size, $cursor)
    {
        return "service:all:{$size}:{$cursor}";
    }

    private function updateFirstPageCache(): void
    {
        $services = Service::with(self::RELATION_TABLES)
            ->orderBy('id', 'desc')
            ->cursorPaginate(self::DEFAULT_SIZE);

        $key = $this->getKeyAll(self::DEFAULT_SIZE, null);
        Redis::set($key, json_encode($services));
        Redis::expire($key, self::CACHE_TTL);
        Redis::sadd('service:all:keys', $key);
        Redis::expire('service:all:keys', self::CACHE_TTL);
    }

    public function getAll(Request $request)
    {
        $size = $request->query('size', self::DEFAULT_SIZE);
        $cursor = $request->query('cursor', null);
        $key = $this->getKeyAll($size, $cursor);

        $services = Redis::get($key);
        if ($services) {
            $services = json_decode($services, true);
        } else {
            $services = Service::with(self::RELATION_TABLES)
                ->orderBy('id', 'desc')
                ->cursorPaginate($size, ['*'], 'cursor', $cursor);
            Redis::set($key, json_encode($services));
            Redis::expire($key, self::CACHE_TTL);
            Redis::sadd('service:all:keys', $key);
            Redis::expire('service:all:keys', self::CACHE_TTL);
        }

        return response()->json($services, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getById(Request $request, string $id)
    {
        $service = Service::findOrFail($id)->load(array_merge(self::RELATION_TABLES, self::RELATION_TABLE_DETAILS));
        ViewedServiceLog::updateOrCreate([
            'service_id' => $service->id,
            'user_id' => auth()->guard()->user()->id,
        ], [
            'updated_at' => now()
        ]);

        $suggestedServices = Service::where('id', '!=', $service->id)
            ->where(function ($query) use ($service) {
                $query->where('category_id', $service->category_id)
                    ->orWhere('location_id', $service->location_id);
            })
            ->limit(5)
            ->pluck('id');

        $service->suggested_services = $suggestedServices;

        return response()->json($service);
    }

    public function create(Request $request)
    {
        $user = auth()->guard()->user();
        $validated = $request->validate([
            'service_name' => ['required', 'max:50'],
            'service_description' => ['required', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_name' => ['required', 'max:100']
        ]);
        $category = Category::findOrFail($validated['category_id']);
        return DB::transaction(function () use ($validated, $user, $category) {
            $service = new Service;
            $service->service_name = $validated['service_name'];
            $service->service_description = $validated['service_description'];
            $location = Location::create(['location_name' => $validated['location_name']]);
            $service->location()->associate($location);
            $service->user()->associate($user);
            $service->category()->associate($category);
            $service->save();

            // Load relations
            $service->load(self::RELATION_TABLES);

            // Cache single service
            $key = "service:{$service->id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);

            // Update first page cache since we have a new service
            $this->updateFirstPageCache();

            return response()->json($service);
        });
    }

    public function update(Request $request, string $id)
    {
        $user = auth()->guard()->user();
        $validated = $request->validate([
            'service_name' => ['required', 'max:50'],
            'service_description' => ['required', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_name' => ['required', 'max:100'],
        ]);

        $category = Category::findOrFail($validated['category_id']);
        return DB::transaction(function () use ($validated, $user, $category, $id) {
            $service = Service::findOrFail($id);
            $service->service_name = $validated['service_name'];
            $service->service_description = $validated['service_description'];
            $location = Location::create(['location_name' => $validated['location_name']]);
            $service->location()->associate($location);
            $service->user()->associate($user);
            $service->category()->associate($category);
            $service->save();

            // Load relations
            $service->load(self::RELATION_TABLES);

            // Update single service cache
            $key = "service:{$id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);

            // Update first page cache since we updated a service
            $this->updateFirstPageCache();

            return response()->json($service);
        });
    }

    public function delete(Request $request, string $id, ?bool $force)
    {
        $service = Service::findOrFail($id);

        DB::transaction(function () use ($service, $id, $force) {
            if ($force) {
                $service->forceDelete();
            } else {
                $service->delete();
            }

            // Delete single service cache
            Redis::del("service:{$id}");

            // Update first page cache since we deleted a service
            $this->updateFirstPageCache();
        });

        return response()->json(['message' => 'success']);
    }

    public function restore(Request $request, string $id)
    {
        $service = Service::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($service, $id) {
            $service->restore();

            // Load relations and update single service cache
            $service->load(self::RELATION_TABLES);
            Redis::set("service:{$id}", json_encode($service));
            Redis::expire("service:{$id}", self::CACHE_TTL);

            // Update first page cache since we restored a service
            $this->updateFirstPageCache();
        });

        return response()->json(['message' => 'success']);
    }

    public function restoreAll(Request $request)
    {
        DB::transaction(function () {
            $services = Service::onlyTrashed()->get();
            Service::onlyTrashed()->restore();

            // Update cache for all restored services
            foreach ($services as $service) {
                $service->load(self::RELATION_TABLES);
                Redis::set("service:{$service->id}", json_encode($service));
                Redis::expire("service:{$service->id}", self::CACHE_TTL);
            }

            // Update first page cache since we restored services
            $this->updateFirstPageCache();
        });

        return response()->json(['message' => 'success']);
    }
}
