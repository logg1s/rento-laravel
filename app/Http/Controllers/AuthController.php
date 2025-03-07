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

class AuthController extends Controller
{
    const OTP_EXPIRY_MINUTES = 5;
    const RESEND_OTP_DELAY_MINUTES = 1;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh', 'checkEmail', 'loginWithGoogle', 'verifyCode', 'resendVerificationCode']]);
        $this->middleware('check.status', ['except' => ['login', 'register', 'refresh', 'checkEmail', 'loginWithGoogle', 'verifyCode', 'resendVerificationCode']]);
    }

    public function validateToken(Request $request)
    {
        return response()->json(['valid' => auth()->guard()->check()]);
    }

    public function checkEmail(Request $request)
    {
        $isExist = User::where('email', $request->email)->exists();
        if ($isExist) {
            return response()->json(['message' => false], 400);
        }
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
            $user->status = UserStatusEnum::PENDING->value;
            $user->save();
            $user->role()->attach($role);
            $user->channelNotification()->attach($role->id);
            $user->userSetting()->create(['is_notification' => true]);

            // Gửi mã xác thực sau khi đăng ký
            $this->sendVerificationCode($user->email);

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
                    'status' => UserStatusEnum::ACTIVE->value,
                ]);

                $image = Image::create(['path' => $validate['image_url']]);
                $user->image()->associate($image);
                $user->save();
                $role = Role::findOrFail(RoleEnum::USER->value);
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

        return $this->respondWithToken($token, auth()->guard()->user());
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

    /**
     * Gửi mã xác thực đến email
     */
    public function sendVerificationCode(string $email)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES)
            ]
        );

        // Gửi email trong background
        SendVerificationEmail::dispatch($email, $code);
    }

    /**
     * Xác thực mã code
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $verification = EmailVerification::where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json([
                'message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        $verification->delete();

        // Cập nhật trạng thái người dùng nếu đang ở trạng thái PENDING
        $user = User::where('email', $validated['email'])->first();
        if ($user && $user->status === UserStatusEnum::PENDING->value) {
            $user->status = UserStatusEnum::ACTIVE->value;
            $user->save();
        }

        return response()->json([
            'message' => 'Xác thực thành công'
        ]);
    }

    /**
     * Gửi lại mã xác thực
     */
    public function resendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);

        // Kiểm tra xem email có tồn tại trong hệ thống không
        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return response()->json([
                'message' => 'Email không tồn tại trong hệ thống'
            ], 400);
        }

        // Kiểm tra trạng thái user

        if ($user->status != UserStatusEnum::PENDING->value) {
            return response()->json([
                'message' => 'Tài khoản đã được xác thực'
            ], 400);
        }

        // Kiểm tra thời gian gửi lại mã
        $lastVerification = EmailVerification::where('email', $validated['email'])
            ->where('created_at', '>', now()->subMinutes(self::RESEND_OTP_DELAY_MINUTES))
            ->first();

        if ($lastVerification) {
            $remainingTime = now()->diffInSeconds($lastVerification->created_at->addMinutes(self::RESEND_OTP_DELAY_MINUTES));
            return response()->json([
                'message' => 'Vui lòng đợi ' . ceil($remainingTime / 60) . ' giây nữa để gửi lại mã'
            ], 429);
        }

        // Gửi mã mới
        $this->sendVerificationCode($validated['email']);

        return response()->json([
            'message' => 'Mã xác thực mới đã được gửi đến email của bạn'
        ]);
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
