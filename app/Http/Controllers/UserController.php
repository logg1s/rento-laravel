<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\Image;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\ViewedServiceLog;
use App\Utils\DirtyLog;
use DB;
use Illuminate\Http\Request;
use Password;
use Storage;
use Illuminate\Validation\Rule;
use App\Enums\RoleEnum;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.status');
    }
    const LOAD_RELATION = [
        'channelNotification'
    ];
    public function me()
    {
        $data = auth()->user()->load(array_merge(self::LOAD_RELATION));
        return Response::json($data);
    }

    public function changeSetting(Request $request): JsonResponse
    {
        $validate = $request->validate(['is_notification' => 'required|boolean']);
        $user = auth()->guard()->user();
        $user->userSetting()->updateOrCreate([], $validate);
        return Response::json($user->load('userSetting'));
    }

    public function getOrder(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $orders = $user->order()->with([
            'service' => function ($query) {
                $query->with('user');
            },
            'cancelBy',
        ])->orderBy('created_at', 'desc')->cursorPaginate(perPage: 5);
        return Response::json($orders);
    }


    public function getById(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        return Response::json($user->load(array_merge(self::LOAD_RELATION, [
            'service' => function ($query) {
                $query->with('comment', 'category', 'location', 'price', 'userFavorite', 'benefit', 'user');
            },
            'location' => function ($query) {
                $query->with('province');
            },
        ])));
    }

    public function updateStatusOrder(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $validate = $request->validate([
            'status' => 'required|integer|between:0,3',
        ]);
        $order->update([
            'status' => $validate['status'],
            'cancel_by' => $validate['status'] == StatusEnum::CANCELLED->value ? auth()->guard()->user()->id : null,
        ]);

        OrderController::sendUpdateOrderMessageToProvider($order->service, $order->user, $order);
        return Response::json(['message' => 'Order updated successfully', 'order' => $order]);
    }


    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'max:50'],
            'phone_number' => ['nullable', 'regex:/[0-9]{10,}/'],
            'address' => ['nullable', 'max: 255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'real_location_name' => ['nullable', 'max: 255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'role' => ['nullable', Rule::enum(RoleEnum::class)],
        ]);
        $user = auth()->guard()->user();

        return DB::transaction(function () use ($validated, $user) {
            if (!empty($validated['role'])) {
                $role = Role::findOrFail($validated['role']);
                $user->role()->sync($role);
            }


            $userData = array_filter([
                'name' => $validated['name'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);


            if (isset($validated['real_location_name']) || isset($validated['lat']) || isset($validated['lng']) || isset($validated['province_id'])) {

                $locationData = [];

                if (isset($validated['address'])) {
                    $locationData['location_name'] = $validated['address'];
                }

                if (isset($validated['real_location_name'])) {
                    $locationData['real_location_name'] = $validated['real_location_name'];
                }

                if (isset($validated['lng'])) {
                    $locationData['lng'] = $validated['lng'];
                }

                if (isset($validated['lat'])) {
                    $locationData['lat'] = $validated['lat'];
                }

                if (isset($validated['province_id'])) {
                    $locationData['province_id'] = $validated['province_id'];
                }

                if (!empty($locationData)) {

                    $location = Location::create($locationData);
                    $userData['location_id'] = $location->id;
                }
            }

            $user->update($userData);
            return Response::json($user);
        });
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_password' => ['required', 'max:50', Password::min(8)],
            'new_password' => ['required', 'max:50', Password::min(8)],
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->guard()->user();

            if (
                !auth()->guard()->validate([
                    'email' => $user->email,
                    'password' => $validated['old_password']
                ])
            ) {
                return Response::json([
                    'message' => 'Old password is incorrect'
                ], 400);
            }


            $user->password = bcrypt($validated['new_password']);
            $user->save();


            return Response::json($user);
        });
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:100000']
        ]);

        return DB::transaction(function () use ($request) {
            $user = auth()->guard()->user();

            if ($user->image_id) {
                $oldImage = Image::find($user->image_id);
                if ($oldImage) {
                    $user->image()->dissociate();
                    $user->save();

                    if ($oldImage->user()->count() === 0) {
                        $relativePath = str_replace('/storage/', '', $oldImage->path);
                        Storage::disk('public')->delete($relativePath);
                        $oldImage->delete();
                    }
                }
            }


            $filename = $request->file('avatar')->hashName();
            $path = $request->file('avatar')->storeAs('avatars', $filename, "public");
            $image = Image::create(['path' => Storage::url($path)]);
            $user->image()->associate($image);
            $user->save();

            return Response::json([
                'avatar' => $user->image,
                'message' => 'Avatar updated successfully'
            ]);
        });
    }
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'mimes:jpeg,png,jpg,gif', 'max:1000000']
        ]);

        return DB::transaction(function () use ($request) {

            $filename = $request->file('image')->hashName();
            $path = $request->file('image')->storeAs('images', $filename, "public");
            $image = Image::create(['path' => Storage::url($path)]);

            return Response::json([
                'path' => Storage::url($path),
                'message' => 'Image updated successfully'
            ]);
        });
    }
    public function deleteViewedServiceByServiceId(Request $request, string $id)
    {
        $user = auth()->guard()->user();
        $user->viewedServiceLog()->where('service_id', $id)->delete();
        return Response::json(['message' => 'Viewed service deleted successfully']);
    }
    public function deleteAllViewedService(Request $request): JsonResponse
    {
        $user = auth()->guard()->user();
        $user->viewedServiceLog()->delete();
        return Response::json(['message' => 'All viewed services deleted successfully']);
    }
    public function deleteImage(Request $request): JsonResponse
    {
        $request->validate([
            'imagePath' => ['required', 'string']
        ]);


        $imagePath = $request->input('imagePath') ?: $request->query('imagePath');


        if (empty($imagePath)) {
            return Response::json([
                'message' => 'Image path is required'
            ], 400);
        }


        if (strpos($imagePath, '/storage/') === 0) {
            $relativePath = substr($imagePath, 9);
        } else {
            $relativePath = $imagePath;
        }


        if (Storage::disk('public')->exists($relativePath)) {
            try {

                Storage::disk('public')->delete($relativePath);

                return Response::json([
                    'message' => 'Image deleted successfully'
                ]);
            } catch (\Exception $e) {
                return Response::json([
                    'message' => 'Failed to delete image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return Response::json([
            'message' => 'Image not found'
        ], 404);
    }
}

