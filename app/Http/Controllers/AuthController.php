<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'student_id' => ['nullable', 'string', 'max:30', 'unique:users,student_id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data + ['role' => 'customer']);
        $token = $this->issueToken($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required_without:email', 'string', 'max:255'],
            'email' => ['required_without:login', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $identifier = $data['login'] ?? $data['email'];
        $user = User::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('email', $identifier)->orWhere('student_id', $identifier))
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 422);
        }

        $token = $this->issueToken($user);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(Request $r)
    {
        $r->user()->update(['api_token' => null]);

        return ['message' => 'Đã đăng xuất.'];
    }

    private function issueToken(User $user): string
    {
        $token = Str::random(60);
        $user->update(['api_token' => hash('sha256', $token)]);

        return $token;
    }
}
