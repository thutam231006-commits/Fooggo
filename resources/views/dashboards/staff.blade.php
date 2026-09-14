@extends('layouts.app')
@section('title', 'Trang nhân viên · FoodGo')
@section('content')
@php $statusLabels = ['pending_payment' => 'Chờ khách thanh toán', 'paid' => 'Chờ nhận đơn', 'preparing' => 'Đang chuẩn bị', 'ready' => 'Sẵn sàng giao']; @endphp
<section class="role-header">
    <div class="eyebrow">Khu vực nhân viên căn tin</div>
    <h1>Hàng đợi xử lý món</h1>
    <p class="lead">Theo dõi đơn mới ngay khi khách đặt; tiếp nhận và xử lý sau khi thanh toán thành công.</p>
</section>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<div class="stats four">
    <div class="stat"><span class="muted">Chờ khách thanh toán</span><strong>{{ $pendingPaymentCount }}</strong></div>
    <div class="stat"><span class="muted">Chờ nhận đơn</span><strong>{{ $waitingCount }}</strong></div>
    <div class="stat"><span class="muted">Đang chuẩn bị</span><strong>{{ $preparingCount }}</strong></div>
    <div class="stat"><span class="muted">Sẵn sàng giao</span><strong>{{ $readyCount }}</strong></div>
</div>
<div class="section-heading"><h2>Đơn hàng đang hoạt động</h2><span class="role-badge">VẬN HÀNH BẾP</span></div>
<div class="table-wrap staff-queue"><table><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Món</th><th>Nhận lúc</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td>#{{ $order->id }}</td><td>{{ $order->user->name }}<div class="muted">{{ $order->user->email }}</div></td><td>@foreach($order->items as $item)<div>{{ $item->quantity }} × {{ $item->food->name }}</div>@endforeach</td><td>{{ $order->pickup_slot }}</td><td>{{ number_format($order->total, 0, ',', '.') }} đ</td><td><span class="badge">{{ $statusLabels[$order->status] }}</span></td><td>
@if($order->status === 'pending_payment')<span class="muted">Chưa thể xử lý</span>
@elseif($order->status === 'paid')<div class="actions" style="align-items:flex-start"><form method="POST" action="{{ route('staff.orders.status', $order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="preparing"><button class="button" type="submit">Nhận đơn</button></form><form method="POST" action="{{ route('staff.orders.status', $order) }}" style="display:grid;gap:8px;min-width:230px">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><input aria-label="Lý do từ chối đơn #{{ $order->id }}" name="rejection_reason" maxlength="1000" placeholder="Nhập lý do từ chối" required><button class="button danger" type="submit">Từ chối đơn</button></form></div>
@elseif($order->status === 'preparing')<form method="POST" action="{{ route('staff.orders.status', $order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ready"><button class="button" type="submit">Sẵn sàng nhận</button></form>
@elseif($order->status === 'ready')<form method="POST" action="{{ route('staff.orders.status', $order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="button" type="submit">Hoàn tất</button></form>@endif
</td></tr>
@empty<tr><td colspan="7" class="muted">Hiện không có đơn hàng nào đang hoạt động.</td></tr>@endforelse
</tbody></table></div>
@endsection
