<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Storage;

class AuthController extends Controller
{
    const KEY_LIST_EMAILS = "list:emails";
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh', 'checkEmail', 'loginWithGoogle']]);
    }

    public function validateToken(Request $request)
    {
        return response()->json(['valid' => auth()->guard()->check()]);
    }

    public function checkEmail(Request $request)
    {
        $emails = Redis::smembers(self::KEY_LIST_EMAILS);
        if (!empty($emails)) {
            $isExist = Redis::sismember(self::KEY_LIST_EMAILS, $request->email);
            error_log("doc tu redis");
            $canRegister = !boolval($isExist);
            return response()->json(['message' => $canRegister], $canRegister ? 200 : 400);
        }
        $isExist = User::where('email', $request->email)->exists();
        if ($isExist) {
            error_log("doc tu db");
            Redis::sadd(self::KEY_LIST_EMAILS, $request->email);
            return response()->json(['message' => false], 400);
        }
        Redis::srem(self::KEY_LIST_EMAILS, $request->email);
        return response()->json(['message' => true]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['email', 'unique:users', 'max:100'],
            'password' => ['required', 'max:50', Password::min(8)],
            'phone_number' => ['required', 'min:4'],
            'address' => ['max: 255'],
            'role' => [Rule::enum(RoleEnum::class)]
        ]);

        $role = Role::findOrFail($validated['role']);

        return DB::transaction(function () use ($validated, $role) {
            $user = new User;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = bcrypt($validated['password']);
            $user->phone_number = $validated['phone_number'];
            $user->address = $validated['address'];

            $user->save();
            $user->role()->attach($role);

            $token = auth()->guard()->login($user);

            return $this->respondWithToken($token, $user);
        });
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

    public function loginWithGoogle(Request $request)
    {
        $validate = $request->validate(
            [
                'email' => 'required|email',
                'name' => 'required|max:50',
                'image_url' => 'required|url'
            ]
        );

        return DB::transaction(function () use ($request, $validate) {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $salt = config('jwt.secret');
                $password = hash('sha256', $validate['email'] . $salt);
                $user = User::create([
                    'name' => $validate['name'],
                    'email' => $validate['email'],
                    'is_oauth' => true,
                    'password' => bcrypt(substr($password, 0, 8)),
                ]);

                $image = Image::create(['path' => $validate['image_url']]);
                $user->image()->associate($image);
                $user->save();
            }
            $token = auth()->guard()->login($user);
            return $this->respondWithToken($token);
        });
    }

    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->guard()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->guard()->user());
    }

    public function logout()
    {
        auth()->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->guard()->refresh());
    }

    protected function respondWithToken($token, $info = null)
    {
        return response()->json(array_filter([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard()->factory()->getTTL() * 60,
            'info' => $info
        ]));
    }
}
