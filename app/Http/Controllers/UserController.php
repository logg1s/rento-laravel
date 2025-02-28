<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Request;
use Password;
use Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function me()
    {
        return response()->json(auth()->guard()->user());
    }

    public function getById(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->load([
            'service' => function ($query) {
                $query->with('comment', 'category', 'location', 'price', 'userFavorite', 'benefit', 'user');
            }
        ]));
    }


    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'max:50'],
            'phone_number' => ['nullable', 'min:4'],
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
}