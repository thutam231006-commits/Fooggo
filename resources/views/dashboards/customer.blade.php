@extends('layouts.app')
@section('title', 'Trang khách hàng · FoodGo')
@section('content')
@php $statusLabels = ['pending_payment' => 'Chờ thanh toán', 'paid' => 'Chờ căn tin xác nhận', 'preparing' => 'Đang chuẩn bị', 'ready' => 'Sẵn sàng nhận', 'completed' => 'Đã hoàn tất', 'rejected' => 'Căn tin từ chối', 'cancelled' => 'Đã hủy', 'expired' => 'Hết hạn thanh toán']; @endphp
<section class="role-header">
    <div class="eyebrow">Khu vực khách hàng</div>
    <h1>Đơn hàng của {{ auth()->user()->name }}</h1>
    <p class="lead">Chọn món, thanh toán bằng Ví FoodGo và theo dõi thời gian nhận.</p>
</section>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<div class="stats four">
    <div class="stat"><span class="muted">Số dư Ví FoodGo</span><strong>{{ number_format(auth()->user()->wallet_balance, 0, ',', '.') }} đ</strong></div>
    <div class="stat"><span class="muted">Món trong giỏ</span><strong>{{ $cartQuantity }}</strong></div>
    <div class="stat"><span class="muted">Đơn của tôi</span><strong>{{ $orders->count() }}</strong></div>
    <div class="stat"><span class="muted">Món đang bán</span><strong>{{ $foodCount }}</strong></div>
</div>
<div class="quick-actions"><a class="button" href="{{ route('home') }}">Chọn món</a><a class="button secondary" href="{{ route('cart.show') }}">Mở giỏ hàng</a></div>
<div class="section-heading"><h2>Lịch sử đơn hàng</h2><span class="muted">Chỉ hiển thị đơn của tài khoản này</span></div>
<div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Món</th><th>Nhận lúc</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td><a class="back" href="{{ route('web.orders.show', $order) }}">#{{ $order->id }}</a></td><td>{{ $order->items->sum('quantity') }} phần</td><td>{{ $order->pickup_slot }}</td><td>{{ number_format($order->total, 0, ',', '.') }} đ</td><td><span class="badge">{{ $statusLabels[$order->status] ?? $order->status }}</span>@if($order->status === 'rejected' && $order->rejection_reason)<div class="error">{{ $order->rejection_reason }}</div>@endif</td><td>
@if(in_array($order->status, ['pending_payment', 'paid'], true))<div class="actions">@if($order->status === 'pending_payment')<form method="POST" action="{{ route('web.orders.pay', $order) }}">@csrf<input type="hidden" name="redirect_to" value="dashboard"><button class="button" type="submit">Thanh toán</button></form>@endif<form method="POST" action="{{ route('web.orders.cancel', $order) }}">@csrf @method('DELETE')<input type="hidden" name="redirect_to" value="dashboard"><button class="button danger" type="submit">Hủy đơn</button></form></div>@else<span class="muted">Xem chi tiết</span>@endif
</td></tr>
@empty<tr><td colspan="6" class="muted">Bạn chưa có đơn hàng. Hãy chọn món từ thực đơn.</td></tr>@endforelse
</tbody></table></div>
@endsection
