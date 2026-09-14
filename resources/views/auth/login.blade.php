@extends('layouts.app')
@section('title', 'Đăng nhập · FoodGo')
@section('content')
<div class="auth-shell">
    <section class="auth-box">
        <h1>Đăng nhập</h1>
        <p class="muted">Dùng email hoặc mã sinh viên của bạn.</p>
        @if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="field"><label for="login">Email hoặc mã sinh viên</label><input id="login" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>@error('login')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field"><label for="password">Mật khẩu</label><input id="password" type="password" name="password" autocomplete="current-password" required>@error('password')<div class="error">{{ $message }}</div>@enderror</div>
            <div class="field" style="display:flex;grid-template-columns:auto 1fr;align-items:center"><input id="remember" type="checkbox" name="remember" value="1" style="width:auto"><label for="remember">Ghi nhớ đăng nhập</label></div>
            <button class="button full" type="submit">Đăng nhập</button>
        </form>
        <p class="muted" style="text-align:center;margin:18px 0 0">Chưa có tài khoản? <a class="back" href="{{ route('register') }}">Đăng ký</a></p>
    </section>
</div>
@endsection
