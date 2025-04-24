<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmail;
use App\Mail\VerificationEmail;
use App\Models\EmailVerification;
use App\Models\Image;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Utils\DirtyLog;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Storage;
use Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class AuthController extends Controller
{
    const OTP_EXPIRY_MINUTES = 5;
    const RESEND_OTP_DELAY_MINUTES = 1;

    public function __construct()
    {
        $this->middleware("auth:api", ["except" => ["login", "register", "refresh", "checkEmail", "loginWithGoogle", "verifyCode", "resendVerificationCode", "forgotPassword", "verifyForgotPassword"]]);
        $this->middleware("check.status", ["except" => ["login", "register", "refresh", "checkEmail", "loginWithGoogle", "verifyCode", "resendVerificationCode", "forgotPassword", "verifyForgotPassword"]]);
    }

    public function validateToken(Request $request): JsonResponse
    {
        return Response::json(["valid" => auth()->guard()->check()]);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $isExist = User::where("email", $request->email)->exists();
        if ($isExist) {
            return Response::json(["message" => false], 400);
        }
        return Response::json(["message" => true]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "name" => ["required", "max:50"],
            "email" => ["email", "unique:users", "max:100"],
            "password" => ["required", "max:50", Password::min(8)],
            "phone_number" => ["required", "min:4"],
            "address" => ["max: 255"],
            "lng" => ["nullable", "numeric"],
            "lat" => ["nullable", "numeric"],
            "real_address" => ["nullable", "max: 255"],
            "role" => [Rule::enum(RoleEnum::class)],
        ]);

        $role = Role::findOrFail($validated["role"]);

        return DB::transaction(function () use ($validated, $role) {
            $user = new User;
            $user->name = $validated["name"];
            $user->email = $validated["email"];
            $user->password = bcrypt($validated["password"]);
            $user->phone_number = $validated["phone_number"];
            $user->address = $validated["address"];
            $user->status = UserStatusEnum::PENDING->value;
            $user->save();
            $user->role()->attach($role);
            $user->channelNotification()->attach($role->id);
            $user->userSetting()->create(['is_notification' => true]);


            if (isset($validated["real_address"]) || isset($validated["lat"]) || isset($validated["lng"])) {

                $locationData = [];

                if (isset($validated["address"])) {
                    $locationData["location_name"] = $validated["address"];
                }

                if (isset($validated["real_address"])) {
                    $locationData["real_location_name"] = $validated["real_address"];
                }

                if (isset($validated["lng"])) {
                    $locationData["lng"] = $validated["lng"];
                }

                if (isset($validated["lat"])) {
                    $locationData["lat"] = $validated["lat"];
                }

                if (!empty($locationData)) {

                    \App\Models\Location::create($locationData);
                }
            }


            $this->sendVerificationCode($user->email);

            $token = auth()->guard()->login($user);

            return $this->respondWithToken($token, $user);
        });
    }

    public function loginWithGoogle(Request $request): JsonResponse
    {
        $validate = $request->validate(
            [
                "email" => "required|email",
                "name" => "required|max:50",
                "image_url" => "required|url",
            ]
        );

        return DB::transaction(function () use ($request, $validate) {
            $user = User::where("email", $request->email)->first();
            if (!$user) {
                $salt = config("jwt.secret");
                $password = hash("sha256", $validate["email"] . $salt);
                $user = User::create([
                    "name" => $validate["name"],
                    "email" => $validate["email"],
                    "is_oauth" => true,
                    "password" => bcrypt(substr($password, 0, 8)),
                    "status" => UserStatusEnum::ACTIVE->value,
                ]);

                $image = Image::create(["path" => $validate["image_url"]]);
                $user->image()->associate($image);
                $user->save();
                $role = Role::findOrFail(RoleEnum::USER->value);
                $user->role()->attach($role);
                $user->channelNotification()->attach($role->id);
                $user->userSetting()->create(["is_notification" => true]);
            }
            $token = auth()->guard()->login($user);
            return $this->respondWithToken($token);
        });
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = request(["email", "password"]);
        if (!$token = auth()->guard()->attempt($credentials)) {
            return Response::json(["error" => "Unauthorized"], 401);
        }

        return $this->respondWithToken($token, auth()->guard()->user());
    }

    public function logout()
    {
        $user = auth()->guard()->user();
        $user->update(['expo_token' => null]);
        auth()->guard()->logout();

        return Response::json(["message" => "Successfully logged out"]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = auth()->guard()->refresh();
        return $this->respondWithToken($token);
    }

    public function sendVerificationCode(string $email)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::updateOrCreate(
            ["email" => $email],
            [
                "code" => $code,
                "expires_at" => now()->addMinutes(self::OTP_EXPIRY_MINUTES)
            ]
        );


        SendVerificationEmail::dispatch($email, $code);
    }

    /**
     * Xác thực mã code
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $verification = EmailVerification::where('email', $validated['email'])
            ->where("code", $validated["code"])
            ->where("expires_at", ">", now())
            ->first();

        if (!$verification) {
            return Response::json([
                'message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        $verification->delete();


        $user = User::where('email', $validated['email'])->first();
        if ($user && $user->status === UserStatusEnum::PENDING->value) {
            $user->status = UserStatusEnum::ACTIVE->value;
            $user->save();
        }

        return Response::json([
            'message' => 'Xác thực thành công'
        ]);
    }

    /**
     * Gửi lại mã xác thực
     */
    public function resendVerificationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);


        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return Response::json([
                'message' => 'Email không tồn tại trong hệ thống'
            ], 400);
        }



        if ($user->status != UserStatusEnum::PENDING->value) {
            return Response::json([
                'message' => 'Tài khoản đã được xác thực'
            ], 400);
        }


        $lastVerification = EmailVerification::where('email', $validated['email'])
            ->where('created_at', '>', now()->subMinutes(self::RESEND_OTP_DELAY_MINUTES))
            ->first();

        if ($lastVerification) {
            $remainingTime = now()->diffInSeconds($lastVerification->created_at->addMinutes(self::RESEND_OTP_DELAY_MINUTES));
            return Response::json([
                'message' => 'Vui lòng đợi ' . ceil($remainingTime / 60) . ' giây nữa để gửi lại mã'
            ], 429);
        }


        $this->sendVerificationCode($validated['email']);

        return Response::json([
            'message' => 'Mã xác thực mới đã được gửi đến email của bạn'
        ]);
    }


    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => ["required", "email", "max:100"],
        ]);


        $user = User::where('email', $validated['email'])
            ->where("is_oauth", false)
            ->where("status", UserStatusEnum::ACTIVE->value)
            ->first();

        if (!$user) {
            return Response::json([
                "message" => "Email không tồn tại hoặc không thể thực hiện quên mật khẩu"
            ], 400);
        }


        $this->sendVerificationCode($validated['email']);

        return Response::json([
            "message" => "Mã xác thực đã được gửi đến email của bạn"
        ]);
    }


    public function verifyForgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => ["required", "email", "max:100"],
            "code" => ["required", "string", "size:6"],
            "new_password" => ["required", "max:50", Password::min(8)],
        ]);


        $user = User::where('email', $validated['email'])
            ->where("is_oauth", false)
            ->where("status", UserStatusEnum::ACTIVE->value)
            ->first();

        if (!$user) {
            return Response::json([
                "message" => "Email không tồn tại hoặc không thể thực hiện quên mật khẩu"
            ], 400);
        }


        $verification = EmailVerification::where('email', $validated['email'])
            ->where("code", $validated["code"])
            ->where("expires_at", ">", now())
            ->first();

        if (!$verification) {
            return Response::json([
                "message" => "Mã xác thực không hợp lệ hoặc đã hết hạn"
            ], 400);
        }

        $verification->delete();

        $user->password = bcrypt($validated['new_password']);
        $user->save();

        return Response::json([
            "message" => "Đặt lại mật khẩu thành công"
        ]);
    }

    protected function respondWithToken($token, $info = null)
    {
        return Response::json(array_filter([
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => auth()->guard()->factory()->getTTL() * 60,
            "info" => $info
        ]));
    }
}
