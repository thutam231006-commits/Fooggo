@extends('layouts.app')
@section('title', 'Phiên làm việc hết hạn · FoodGo')
@section('content')
<div class="auth-shell"><section class="auth-box"><h1>Phiên làm việc đã hết hạn</h1><p class="muted">Vui lòng đăng nhập lại để tiếp tục thao tác.</p><a class="button full" href="{{ route('login') }}">Về trang đăng nhập</a></section></div>
@endsection
