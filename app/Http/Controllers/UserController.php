<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::query()->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))->latest()->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);

        return response()->json(User::create($data), 201);
    }

    public function show(User $user)
    {
        return $user->loadCount('orders', 'reviews');
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        if ($request->user()->is($user) && (($data['role'] ?? $user->role) !== 'admin' || ! ($data['is_active'] ?? $user->is_active))) {
            throw ValidationException::withMessages(['role' => 'Quản trị viên không thể tự hạ quyền hoặc khóa tài khoản của mình.']);
        }
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } $user->update($data);

        return $user;
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->is($user), 422, 'Quản trị viên không thể tự khóa tài khoản.');
        $user->update(['is_active' => false, 'api_token' => null]);

        return response()->json(['message' => 'Đã khóa tài khoản.']);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $id = $user?->id ?? 'NULL';

        return $request->validate([
            'name' => [$user ? 'sometimes' : 'required', 'string', 'max:120'],
            'email' => [$user ? 'sometimes' : 'required', 'email', "unique:users,email,{$id}"],
            'student_id' => ['nullable', 'string', 'max:30', "unique:users,student_id,{$id}"],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => [$user ? 'sometimes' : 'required', 'in:customer,staff,admin'],
            'wallet_balance' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => [$user ? 'sometimes' : 'required', 'string', 'min:8'],
        ]);
    }
}
