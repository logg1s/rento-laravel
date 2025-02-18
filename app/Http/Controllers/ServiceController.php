<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAll(Request $request)
    {
        $size = $request->query('size', 10);
        $services = Service::withTrashed(['user', 'category', 'location', 'price'])->orderBy('id')->cursorPaginate($size);
        return response()->json($services);
    }

    public function getById(Request $request, string $id)
    {
        return response()->json(Service::findOrFail($id));
    }

    public function create(Request $request)
    {
        $user = auth()->guard()->user();
        $validated = $request->validate(['service_name' => ['required', 'max:50'],
            'service_description' => ['required', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_name' => ['required', 'max:100']]);
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
            return response()->json($service);
        });
    }

    public function updateSevice(Request $request, string $id)
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
            return response()->json($service);
        });
    }

    public function delete(Request $request, string $id, ?bool $force)
    {
        $service = Service::findOrFail($id);
        if ($force) {
            $service->forceDelete();
        } else
            $service->delete();
        return response()->json(['message' => 'success']);
    }

    public function restore(Request $request, string $id)
    {
        $service = Service::withTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'success']);
    }

    public function restoreAll(Request $request)
    {
        $service = Service::onlyTrashed()->restore();
        return response()->json(['message' => 'success']);
    }
}
