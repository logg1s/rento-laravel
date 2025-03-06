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
    const KEY_LIST_EMAILS = 'list:emails';

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
            $canRegister = !boolval($isExist);
            return response()->json(['message' => $canRegister], $canRegister ? 200 : 400);
        }
        $isExist = User::where('email', $request->email)->exists();
        if ($isExist) {
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
            'role' => [Rule::enum(RoleEnum::class)],
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
            $user->channelNotification()->attach($role->id);
            $user->userSetting()->create(['is_notification' => true]);

            $token = auth()->guard()->login($user);

            return $this->respondWithToken($token, $user);
        });
    }

    public function loginWithGoogle(Request $request)
    {
        $validate = $request->validate(
            [
                'email' => 'required|email',
                'name' => 'required|max:50',
                'image_url' => 'required|url',
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
                $role = Role::findOrFail(RoleEnum::USER);
                $user->role()->attach($role);
                $user->channelNotification()->attach($role->id);
                $user->userSetting()->create(['is_notification' => true]);
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

    public function logout()
    {
        $user = auth()->guard()->user();
        $user->update(['expo_token' => null]);
        auth()->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(Request $request)
    {
        $token = auth()->guard()->refresh();
        return $this->respondWithToken($token);
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
