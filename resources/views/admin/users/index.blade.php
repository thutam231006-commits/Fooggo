@extends('layouts.app')
@section('title', 'Quản lý tài khoản · FoodGo')
@section('content')
@include('admin.partials.nav')
<div class="management-head"><div><div class="eyebrow">Phân quyền hệ thống</div><h1>Quản lý tài khoản</h1></div><a class="button" href="{{ route('admin.users.create') }}">Tạo tài khoản</a></div>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
<form class="filter-bar" method="GET"><input name="search" value="{{ request('search') }}" placeholder="Tên, email hoặc mã sinh viên"><select name="role"><option value="">Tất cả vai trò</option><option value="customer" @selected(request('role') === 'customer')>Khách hàng</option><option value="staff" @selected(request('role') === 'staff')>Nhân viên</option><option value="admin" @selected(request('role') === 'admin')>Quản trị</option></select><button class="button secondary" type="submit">Lọc</button></form>
<div class="table-wrap"><table><thead><tr><th>Người dùng</th><th>Mã sinh viên</th><th>Điện thoại</th><th>Vai trò</th><th>Số dư ví</th><th>Trạng thái</th><th></th></tr></thead><tbody>
@forelse($users as $user)<tr><td><strong>{{ $user->name }}</strong><div class="muted">{{ $user->email }}</div></td><td>{{ $user->student_id ?: '—' }}</td><td>{{ $user->phone ?: '—' }}</td><td><span class="badge">{{ ['customer' => 'Khách hàng', 'staff' => 'Nhân viên', 'admin' => 'Quản trị'][$user->role] }}</span></td><td>{{ number_format($user->wallet_balance, 0, ',', '.') }} đ</td><td>{{ $user->is_active ? 'Hoạt động' : 'Đã khóa' }}</td><td><a class="button secondary" href="{{ route('admin.users.edit', $user) }}">Chỉnh sửa</a></td></tr>
@empty<tr><td colspan="7" class="muted">Không có tài khoản phù hợp.</td></tr>@endforelse
</tbody></table></div>
{{ $users->links('partials.pagination') }}
@endsection
