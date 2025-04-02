<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Location;
use App\Models\Service;
use App\Models\ViewedServiceLog;
use App\Utils\DirtyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Storage;
class ServiceController extends Controller
{
    const DEFAULT_SIZE = 5;
    const CACHE_TTL = 30;
    const RELATION_TABLES = ['user', 'category', 'location', 'price'];
    const RELATION_TABLE_DETAILS = ['userFavorite', 'benefit'];
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
        $services = Service::with(array_merge(self::RELATION_TABLES, ['image']))
            ->orderBy('id', 'desc')
            ->cursorPaginate(self::DEFAULT_SIZE);

        $key = $this->getKeyAll(self::DEFAULT_SIZE, null);
        Redis::set($key, json_encode($services));
        Redis::expire($key, self::CACHE_TTL);
        Redis::sadd('service:all:keys', $key);
        Redis::expire('service:all:keys', self::CACHE_TTL);
    }

    public function getAll(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $services = Service::with(array_merge(self::RELATION_TABLES, ['image']))
            ->orderBy('id', 'desc')
            ->cursorPaginate(5);

        return Response::json($services, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getMyServices(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $query = Service::with(array_merge(self::RELATION_TABLES, ['image']))
            ->where('user_id', $user->id);


        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                    ->orWhere('service_description', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('id', 'desc')
            ->cursorPaginate($request->query('per_page', 5));

        return Response::json($services, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getById(Request $request, string $id): JsonResponse
    {
        $service = Service::findOrFail($id)->load(array_merge(self::RELATION_TABLES, self::RELATION_TABLE_DETAILS, ['image']));
        ViewedServiceLog::updateOrCreate([
            'service_id' => $service->id,
            'user_id' => auth()->guard()->user()->id,
        ], [
            'updated_at' => now()
        ]);

        $service->view_count = ViewedServiceLog::where('service_id', $service->id)->count();
        $service->order_count = $service->order()->count();
        $service->ordered_by_me = $service->order()->where('user_id', auth()->guard()->user()->id)->where('status', StatusEnum::SUCCESS->value)->exists();
        $suggestedServices = Service::where('id', '!=', $service->id)
            ->where(function ($query) use ($service) {
                $query->where('category_id', $service->category_id)
                    ->orWhere('location_id', $service->location_id);
            })
            ->limit(5)
            ->pluck('id');

        $service->suggested_services = $suggestedServices;


        return Response::json($service, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Lấy chi tiết dịch vụ cho nhà cung cấp
     */
    public function getProviderServiceById(Request $request, string $id): JsonResponse
    {
        $user = auth()->guard()->user();
        $service = Service::findOrFail($id);

        if ($service->user_id !== $user->id) {
            return Response::json(['message' => 'Unauthorized'], 403);
        }

        $service->load(array_merge(self::RELATION_TABLES, self::RELATION_TABLE_DETAILS, [
            "image"
        ]));

        $service->view_count = ViewedServiceLog::where('service_id', $service->id)->count();
        $service->order_count = $service->order()->count();

        return Response::json($service);
    }

    public function create(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $validated = $request->validate([
            'service_name' => ['required', 'max:50'],
            'service_description' => ['required', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_name' => ['required', 'max:100'],
            'lng' => ['nullable', 'numeric'],
            'lat' => ['nullable', 'numeric'],
            'real_location_name' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required']
        ]);
        $category = Category::findOrFail($validated['category_id']);
        return DB::transaction(function () use ($validated, $user, $category, $request) {
            $service = new Service;
            $service->service_name = $validated['service_name'];
            $service->service_description = $validated['service_description'];

            $locationData = [
                'location_name' => $validated['location_name']
            ];

            if (isset($validated['lng'])) {
                $locationData['lng'] = $validated['lng'];
            }

            if (isset($validated['lat'])) {
                $locationData['lat'] = $validated['lat'];
            }

            if (isset($validated['real_location_name'])) {
                $locationData['real_location_name'] = $validated['real_location_name'];
            }

            if (isset($validated['province_id'])) {
                $locationData['province_id'] = $validated['province_id'];
            }

            if (isset($validated['address'])) {
                $locationData['address'] = $validated['address'];
            }

            $location = Location::create($locationData);
            $service->location()->associate($location);
            $service->user()->associate($user);
            $service->category()->associate($category);
            $service->save();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('services', 'public');
                    $image = Image::create(['path' => Storage::url($path)]);
                    $service->image()->attach($image->id);
                }
            }

            $service->load(array_merge(self::RELATION_TABLES, ['image']));

            $key = "service:{$service->id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);


            $this->updateFirstPageCache();

            return Response::json($service);
        });
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->guard()->user();

        $validated = $request->validate([
            'service_name' => ['required', 'max:50'],
            'service_description' => ['required', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_name' => ['required', 'max:100'],
            'lng' => ['nullable', 'numeric'],
            'lat' => ['nullable', 'numeric'],
            'real_location_name' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'images' => ['array', 'nullable'],
            'images.*' => ['nullable'],
            'kept_image_ids' => ['array', 'nullable'],
            'kept_image_ids.*' => ['nullable', 'exists:images,id'],
            'remove_all_images' => ['boolean']
        ]);

        $category = Category::findOrFail($validated['category_id']);
        return DB::transaction(function () use ($validated, $user, $category, $id, $request) {
            $service = Service::findOrFail($id);


            if ($service->user_id !== $user->id) {
                return Response::json(['message' => 'Unauthorized'], 403);
            }

            $service->service_name = $validated['service_name'];
            $service->service_description = $validated['service_description'];


            $locationData = [
                'location_name' => $validated['location_name']
            ];


            if (isset($validated['lng'])) {
                $locationData['lng'] = $validated['lng'];
            }

            if (isset($validated['lat'])) {
                $locationData['lat'] = $validated['lat'];
            }

            if (isset($validated['real_location_name'])) {
                $locationData['real_location_name'] = $validated['real_location_name'];
            }

            if (isset($validated['province_id'])) {
                $locationData['province_id'] = $validated['province_id'];
            }

            if (isset($validated['address'])) {
                $locationData['address'] = $validated['address'];
            }


            if ($service->location_id) {
                $location = Location::find($service->location_id);
                if ($location) {
                    $location->update($locationData);
                } else {

                    $location = Location::create($locationData);
                    $service->location()->associate($location);
                }
            } else {

                $location = Location::create($locationData);
                $service->location()->associate($location);
            }

            $service->category()->associate($category);
            $service->save();


            if ($request->has('kept_image_ids')) {
                $keptIds = $request->input('kept_image_ids', []);
                $currentImages = $service->image()->pluck('image_id')->toArray();


                $imagesToDetach = array_diff($currentImages, $keptIds);


                foreach ($imagesToDetach as $imageId) {
                    $service->image()->detach($imageId);
                }
            } elseif ($request->has('remove_all_images') && $request->input('remove_all_images') == 1) {

                \Log::info('Xóa tất cả hình ảnh của dịch vụ #' . $service->id);
                $service->image()->detach();
            }


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('services', 'public');
                    $image = Image::create(['path' => '/storage/' . $path]);
                    $service->image()->attach($image->id);
                }
            }


            $service->load(array_merge(self::RELATION_TABLES, ['image']));


            $key = "service:{$id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);


            $this->updateFirstPageCache();

            return Response::json($service);
        });
    }

    public function delete(Request $request, string $id, ?int $force): JsonResponse
    {
        $service = Service::findOrFail($id);

        DB::transaction(function () use ($service, $id, $force) {
            if ($force) {
                $service->forceDelete();
            } else {
                $service->delete();
            }
        });

        return Response::json(['message' => 'success']);
    }

    public function restore(Request $request, string $id): JsonResponse
    {
        $service = Service::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($service, $id) {
            $service->restore();


            $service->load(self::RELATION_TABLES);
            Redis::set("service:{$id}", json_encode($service));
            Redis::expire("service:{$id}", self::CACHE_TTL);


            $this->updateFirstPageCache();
        });

        return Response::json(['message' => 'success']);
    }

    public function restoreAll(Request $request): JsonResponse
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

            // Update first page cache
            $this->updateFirstPageCache();
        });

        return Response::json(['message' => 'success']);
    }

    public function addServicePrice(Request $request, string $serviceId): JsonResponse
    {
        $user = auth()->guard()->user();
        $service = Service::findOrFail($serviceId);


        if ($service->user_id !== $user->id) {
            return Response::json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'price_name' => ['required', 'string', 'max:100'],
            'price_value' => ['required', 'numeric', 'min:0']
        ]);

        return DB::transaction(function () use ($service, $validated) {
            $price = $service->price()->create([
                'price_name' => $validated['price_name'],
                'price_value' => $validated['price_value']
            ]);


            $service->load(array_merge(self::RELATION_TABLES, ['image']));


            $key = "service:{$service->id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);

            return Response::json($price);
        });
    }


    public function updateServicePrice(Request $request, string $serviceId, string $priceId): JsonResponse
    {
        $user = auth()->guard()->user();
        $service = Service::findOrFail($serviceId);


        if ($service->user_id !== $user->id) {
            return Response::json(['message' => 'Unauthorized'], 403);
        }

        $price = $service->price()->findOrFail($priceId);

        $validated = $request->validate([
            'price_name' => ['required', 'string', 'max:100'],
            'price_value' => ['required', 'numeric', 'min:0']
        ]);

        return DB::transaction(function () use ($price, $service, $validated) {
            $price->update([
                'price_name' => $validated['price_name'],
                'price_value' => $validated['price_value']
            ]);


            $service->load(array_merge(self::RELATION_TABLES, ['image']));


            $key = "service:{$service->id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);

            return Response::json($price);
        });
    }

    /**
     * Xóa gói giá cho dịch vụ
     */
    public function deleteServicePrice(Request $request, string $serviceId, string $priceId): JsonResponse
    {
        $user = auth()->guard()->user();
        $service = Service::findOrFail($serviceId);


        if ($service->user_id !== $user->id) {
            return Response::json(['message' => 'Unauthorized'], 403);
        }

        $price = $service->price()->findOrFail($priceId);

        return DB::transaction(function () use ($price, $service) {
            $price->delete();


            $service->load(array_merge(self::RELATION_TABLES, ['image']));


            $key = "service:{$service->id}";
            Redis::set($key, json_encode($service));
            Redis::expire($key, self::CACHE_TTL);

            return Response::json(['message' => 'success']);
        });
    }

    /**
     * Get counts of services by category
     */
    public function getCategoryCounts(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();


        $query = Service::where('user_id', $user->id);


        $counts = $query->select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->get()
            ->pluck('count', 'category_id')
            ->toArray();

        return Response::json($counts);
    }

    /**
     * Lấy danh sách dịch vụ gần vị trí hiện tại của người dùng
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyServices(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $radius = $request->input('radius', 10);
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $provinceId = $request->input('province_id');
        $perPage = $request->input('per_page', 15);

        if (!$lat || !$lng) {

            if (!$provinceId) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Vui lòng cung cấp vị trí hoặc tỉnh/thành của bạn'
                ], 400);
            }

            $query = Service::with(array_merge(self::RELATION_TABLES, ['image']))
                ->whereHas('location', function ($query) use ($provinceId) {
                    $query->where('province_id', $provinceId);
                })
                ->orderBy('id', 'desc');

            $services = $query->paginate($perPage);


            $services->getCollection()->transform(function ($service) use ($user) {
                $service->is_liked = $service->userFavorite->contains('id', $user->id);
                return $service;
            });

            return Response::json([
                'status' => 'success',
                'data' => $services
            ]);
        }

        $haversineSQL = "
            (
                6371 * 2 * ASIN(
                    SQRT(
                        POWER(SIN((RADIANS(locations.lat) - RADIANS(?)) / 2), 2) +
                        COS(RADIANS(?)) * COS(RADIANS(locations.lat)) * POWER(SIN((RADIANS(locations.lng) - RADIANS(?)) / 2), 2)
                    )
                )
            )";

        $query = Service::with(array_merge(self::RELATION_TABLES, ['image']))
            ->join('locations', 'services.location_id', '=', 'locations.id')
            ->select('services.*')
            ->selectRaw("$haversineSQL AS distance", [$lat, $lat, $lng])
            ->whereRaw("$haversineSQL < ?", [$lat, $lat, $lng, $radius])
            ->whereNotNull('locations.lat')
            ->whereNotNull('locations.lng')
            ->orderBy('distance', 'asc');

        $services = $query->paginate($perPage);


        $services->getCollection()->transform(function ($service) use ($user) {
            $service->is_liked = $service->userFavorite->contains('id', $user->id);
            return $service;
        });

        return Response::json([
            'status' => 'success',
            'data' => $services
        ]);
    }

    public function getViewedService(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $viewedServices = Service::with(array_merge(self::RELATION_TABLES, ['image']))
            ->whereHas('viewedServiceLog', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('id', 'desc')
            ->cursorPaginate(perPage: 5);
        return Response::json($viewedServices);
    }

}
