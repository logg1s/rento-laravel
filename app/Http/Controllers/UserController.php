<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\Image;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\ViewedServiceLog;
use DB;
use Illuminate\Http\Request;
use Password;
use Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    const LOAD_RELATION = [
        'notification',
        'channelNotification'
    ];
    public function me()
    {
        return response()->json(auth()->guard()->user()->load(array_merge(self::LOAD_RELATION, [
            'service' => function ($query) {
                $query->with('comment', 'category', 'location', 'price', 'userFavorite', 'benefit', 'user');
            },
            'viewedServiceLog' => function ($query) {
                $query->orderBy('id', 'desc');
            },
            'serviceFavorite' => function ($query) {
                return $query->select('service_id')->get()->pluck('service_id')->toArray();
            },
        ])));
    }

    public function changeSetting(Request $request)
    {
        $validate = $request->validate(['is_notification' => 'required|boolean']);
        $user = auth()->guard()->user();
        $user->userSetting()->updateOrCreate([], $validate);
        return response()->json($user->load('userSetting'));
    }

    public function getOrder(Request $request)
    {
        $user = auth()->guard()->user();
        $orders = $user->order()->with([
            'service' => function ($query) {
                $query->with('comment', 'category', 'location', 'price', 'userFavorite', 'benefit', 'user');
            },
            'cancelBy',
        ])->get();
        return response()->json($orders);
    }


    public function getById(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->load(array_merge(self::LOAD_RELATION, [
            'service' => function ($query) {
                $query->with('comment', 'category', 'location', 'price', 'userFavorite', 'benefit', 'user');
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
            'cancel_by' => $validate['status'] == StatusEnum::CANCELLED ? auth()->guard()->user()->id : null,
        ]);
        return response()->json(['message' => 'Order updated successfully', 'order' => $order]);
    }


    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'max:50'],
            'phone_number' => ['nullable', 'regex:/[0-9]{10,}/'],
            'address' => ['nullable', 'max: 255'],
        ]);

        return DB::transaction(function () use ($validated) {
            $user = auth()->guard()->user();
            $user->update($validated);
            return response()->json($user);
        });
    }

    public function updatePassword(Request $request)
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
                return response()->json([
                    'message' => 'Old password is incorrect'
                ], 400);
            }

            // Update password mới
            $user->password = bcrypt($validated['new_password']);
            $user->save();

            // Refresh token
            return response()->json($user);
        });
    }

    public function uploadAvatar(Request $request)
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

            // Save new image
            $filename = $request->file('avatar')->hashName();
            $path = $request->file('avatar')->storeAs('avatars', $filename, "public");
            $image = Image::create(['path' => Storage::url($path)]);
            $user->image()->associate($image);
            $user->save();

            return response()->json([
                'avatar' => $user->image,
                'message' => 'Avatar updated successfully'
            ]);
        });
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:100000']
        ]);

        return DB::transaction(function () use ($request) {

            $filename = $request->file('image')->hashName();
            $path = $request->file('image')->storeAs('images', $filename, "public");
            $image = Image::create(['path' => Storage::url($path)]);

            return response()->json([
                'path' => Storage::url($path),
                'message' => 'Image updated successfully'
            ]);
        });
    }
}
