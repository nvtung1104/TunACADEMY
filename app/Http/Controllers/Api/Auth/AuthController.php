<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->input('role', 'student'));

        return $this->success([
            'user'  => new UserResource($user),
            'token' => $user->createToken('api')->plainTextToken,
        ], 'Đăng ký thành công', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Email hoặc mật khẩu không đúng', 401);
        }
        if ($user->status !== 'active') {
            return $this->error('Tài khoản đã bị tạm khóa', 403);
        }

        $user->update(['last_login_at' => now()]);

        return $this->success([
            'user'  => new UserResource($user->load('roles')),
            'token' => $user->createToken('api')->plainTextToken,
        ], 'Đăng nhập thành công');
    }

    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()->load('roles')));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Đăng xuất thành công');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'phone'          => 'sometimes|nullable|string|max:20',
            'date_of_birth'  => 'sometimes|nullable|date',
            'gender'         => 'sometimes|nullable|in:male,female,other',
            'frame'          => 'sometimes|nullable|in:none,male,female',
            'address'        => 'sometimes|nullable|string',
            'qualification'  => 'sometimes|nullable|string|max:100',
            'parent_name'    => 'sometimes|nullable|string|max:255',
            'parent_phone'   => 'sometimes|nullable|string|max:20',
            'parent_address' => 'sometimes|nullable|string',
        ]);
        $request->user()->update($data);
        return $this->success(new UserResource($request->user()->fresh()->load('roles')), 'Cập nhật thành công');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        $request->user()->update(['avatar' => $path]);
        return $this->success(['avatar_url' => asset("storage/{$path}")], 'Cập nhật ảnh đại diện thành công');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return $this->error('Mật khẩu hiện tại không đúng', 422);
        }
        $request->user()->update(['password' => Hash::make($request->password), 'must_change_password' => false]);
        return $this->success(null, 'Đổi mật khẩu thành công');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        Password::sendResetLink($request->only('email'));
        return $this->success(null, 'Email đặt lại mật khẩu đã được gửi');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            fn($user, $password) => $user->update(['password' => Hash::make($password), 'must_change_password' => false])
        );
        return $status === Password::PASSWORD_RESET
            ? $this->success(null, 'Mật khẩu đã được đặt lại thành công')
            : $this->error('Token không hợp lệ hoặc đã hết hạn', 400);
    }
}
