<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function validateToken(Request $request)
    {
        return response()->json(['valid' => auth()->guard()->check()]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['email:rfc,dns', 'unique:users', 'max:100'],
            'password' => ['required', 'max:50', Password::min(8)],
            'phone_number' => ['required', 'min:4'],
            'role' => [Rule::enum(RoleEnum::class)]
        ]);

        $role = Role::findOrFail($validated['role']);

        return DB::transaction(function () use ($validated, $role) {
            $user = new User;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = bcrypt($validated['password']);
            $user->phone_number = $validated['phone_number'];

            $user->save();
            $user->role()->attach($role);

            $token = auth()->guard()->login($user);

            return $this->respondWithToken($token, $user);
        });
    }

    public function login()
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
