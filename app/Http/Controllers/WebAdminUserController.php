<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WebAdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($userQuery) => $userQuery
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%')
                ->orWhere('student_id', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function create(): View
    {
        return view('admin.users.edit', ['user' => new User([
            'role' => 'staff',
            'wallet_balance' => 0,
            'is_active' => true,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return redirect()->route('admin.users.edit', $user)->with('status', 'Đã tạo tài khoản mới.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($request->user()->is($user) && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'Quản trị viên không thể tự hạ quyền hoặc khóa tài khoản của mình.']);
        }

        if (filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (! $data['is_active']) {
            $data['api_token'] = null;
        }

        $user->update($data);

        return back()->with('status', 'Đã cập nhật tài khoản.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $id = $user?->id ?? 'NULL';

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$id}"],
            'student_id' => ['nullable', 'string', 'max:30', "unique:users,student_id,{$id}"],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:customer,staff,admin'],
            'wallet_balance' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'is_active' => ['required', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
