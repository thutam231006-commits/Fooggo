@extends('layouts.app')
@section('title', 'Trang quản trị · FoodGo')
@section('content')
@php $statusLabels = ['pending_payment' => 'Chờ thanh toán', 'paid' => 'Chờ xác nhận', 'preparing' => 'Đang chuẩn bị', 'ready' => 'Sẵn sàng nhận', 'completed' => 'Hoàn tất', 'rejected' => 'Bị từ chối', 'cancelled' => 'Đã hủy']; @endphp
@include('admin.partials.nav')
<section class="role-header">
    <div class="eyebrow">Khu vực quản trị hệ thống</div>
    <h1>Tổng quan FoodGo</h1>
    <p class="lead">Theo dõi tài khoản, thực đơn, đơn hàng và doanh thu toàn hệ thống.</p>
</section>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
<div class="stats four">
    <div class="stat admin-summary"><span class="muted">Tổng tài khoản</span><strong>{{ $userCount }}</strong><span class="muted">{{ $customerCount }} khách · {{ $staffCount }} nhân viên</span></div>
    <div class="stat admin-summary"><span class="muted">Tổng món ăn</span><strong>{{ $foodCount }}</strong><span class="muted">{{ $unavailableFoodCount }} hết/ngừng bán</span></div>
    <div class="stat admin-summary"><span class="muted">Đơn hoàn tất</span><strong>{{ $completedOrderCount }}</strong></div>
    <div class="stat admin-summary"><span class="muted">Doanh thu ghi nhận</span><strong>{{ number_format($revenue, 0, ',', '.') }} đ</strong></div>
</div>
<div class="quick-actions"><a class="button" href="{{ route('admin.foods.index') }}">Quản lý món ăn</a><a class="button secondary" href="{{ route('admin.users.index') }}">Quản lý tài khoản</a><a class="button secondary" href="{{ route('admin.reports.index') }}">Xem báo cáo</a></div>
<div class="section-heading"><h2>Đơn hàng toàn hệ thống</h2><span class="role-badge">QUẢN TRỊ</span></div>
<div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Thời gian đặt</th><th>Nhận lúc</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>#{{ $order->id }}</td><td>{{ $order->user->name }}<div class="muted">{{ $order->user->email }}</div></td><td>{{ $order->ordered_at?->format('d/m/Y H:i') }}</td><td>{{ $order->pickup_slot }}</td><td>{{ number_format($order->total, 0, ',', '.') }} đ</td><td><span class="badge">{{ $statusLabels[$order->status] ?? $order->status }}</span></td></tr>
@empty<tr><td colspan="6" class="muted">Hệ thống chưa có đơn hàng.</td></tr>@endforelse
</tbody></table></div>
@endsection
