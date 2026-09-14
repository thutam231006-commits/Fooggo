@extends('layouts.app')
@section('title', 'Đăng ký · FoodGo')
@section('content')
<div class="auth-shell">
    <section class="auth-box">
        <h1>Tạo tài khoản</h1>
        <p class="muted">Tài khoản mới được tạo với vai trò khách hàng.</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="field"><label for="name">Họ và tên</label><input id="name" name="name" value="{{ old('name') }}" required autofocus>@error('name')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required>@error('email')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="student_id">Mã sinh viên</label><input id="student_id" name="student_id" value="{{ old('student_id') }}">@error('student_id')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="phone">Số điện thoại</label><input id="phone" name="phone" value="{{ old('phone') }}">@error('phone')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="password">Mật khẩu</label><input id="password" type="password" name="password" minlength="8" required>@error('password')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="password_confirmation">Nhập lại mật khẩu</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" required></div>
            <button class="button full" type="submit">Đăng ký</button>
        </form>
        <p class="muted" style="text-align:center;margin:18px 0 0">Đã có tài khoản? <a class="back" href="{{ route('login') }}">Đăng nhập</a></p>
    </section>
</div>
@endsection
