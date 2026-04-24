<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->filled('role'))   $query->role($request->role);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where(fn($q) =>
            $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")
        );

        return UserResource::collection($query->paginate($request->input('per_page', 20)));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([...$request->validated(), 'password' => Hash::make($request->password)]);
        $user->assignRole($request->role);
        return $this->success(new UserResource($user->load('roles')), 'Tạo tài khoản thành công', 201);
    }

    public function show(User $user)
    {
        return $this->success(new UserResource($user->load('roles')));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $user->update($data);
        if ($request->filled('role')) $user->syncRoles([$request->role]);
        return $this->success(new UserResource($user->fresh('roles')), 'Cập nhật thành công');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->success(null, 'Xóa tài khoản thành công');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,teacher,student']);
        $user->syncRoles([$request->role]);
        return $this->success(new UserResource($user->load('roles')), 'Phân quyền thành công');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return $this->success(new UserResource($user->fresh()), 'Cập nhật trạng thái thành công');
    }
}
