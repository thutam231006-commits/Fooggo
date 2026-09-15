@extends('layouts.app')
@section('title', 'Điều phối Bếp KDS · FoodGo')
@section('content')
@php
    $lanes = [
        'paid' => ['title' => 'Chờ tiếp nhận', 'subtitle' => 'Đơn đã thanh toán', 'icon' => 'new_releases', 'action' => 'Nhận đơn'],
        'preparing' => ['title' => 'Đang chế biến', 'subtitle' => 'Bếp đang thực hiện', 'icon' => 'skillet', 'action' => 'Đánh dấu sẵn sàng'],
        'ready' => ['title' => 'Sẵn sàng giao', 'subtitle' => 'Chờ khách tại quầy', 'icon' => 'task_alt', 'action' => 'Xác nhận đã giao'],
    ];
@endphp
<div class="kitchen-shell">
    <aside class="kitchen-sidebar">
        <a class="kitchen-brand" href="{{ route('staff.dashboard') }}"><span class="material-symbols-outlined">restaurant</span><span><strong>FoodGo</strong><small>Kitchen Display</small></span></a>
        <div class="kitchen-live"><i></i><span><strong>Hệ thống trực tuyến</strong><small>Cập nhật theo dữ liệu đơn</small></span></div>
        <nav aria-label="Điều hướng vận hành"><a href="#overview"><span class="material-symbols-outlined">space_dashboard</span>Tổng quan ca</a><a class="active" href="#order-board"><span class="material-symbols-outlined">view_kanban</span>Bảng điều phối</a><a href="{{ route('home') }}"><span class="material-symbols-outlined">restaurant_menu</span>Thực đơn đang bán</a><a href="#stock-watch"><span class="material-symbols-outlined">inventory_2</span>Cảnh báo tồn kho</a><a href="#completed-orders"><span class="material-symbols-outlined">history</span>Đơn đã giao</a></nav>
        <div class="kitchen-shift"><span>Ca vận hành</span><strong>{{ now()->hour < 14 ? 'Ca trưa' : 'Ca chiều' }}</strong><small>{{ now()->format('d/m/Y') }}</small><div><i style="width:{{ min(100, $activeOrderCount * 12) }}%"></i></div><p>{{ $activeOrderCount }} đơn đang hoạt động</p></div>
    </aside>

    <div class="kitchen-workspace">
        <header class="kitchen-topbar"><div><span class="material-symbols-outlined">storefront</span><span><strong>Điều phối bếp & quầy nhận món</strong><small>Tất cả quầy · Căn tin FoodGo</small></span></div><div class="kitchen-clock"><span class="material-symbols-outlined">schedule</span><strong>{{ now()->format('H:i') }}</strong><small>Cập nhật lúc tải trang</small></div><div class="kitchen-user"><span>{{ mb_substr(auth()->user()->name,0,1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role === 'admin' ? 'Quản trị giám sát' : 'Nhân viên vận hành' }}</small></div></div></header>

        @if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif

        <section class="kitchen-overview" id="overview">
            <div class="kitchen-section-head"><div><span class="eyebrow">Tổng quan ca hiện tại</span><h1>Bảng điều phối đơn hàng</h1><p>Theo dõi đơn theo đúng thứ tự ưu tiên và khung giờ khách nhận.</p></div><label class="auto-refresh"><input type="checkbox" data-auto-refresh><span><b>Tự làm mới</b><small>Mỗi 30 giây</small></span></label></div>
            <div class="kitchen-kpis"><article class="waiting"><span class="material-symbols-outlined">payments</span><div><small>Chờ thanh toán</small><strong>{{ $pendingPaymentCount }}</strong><em>Chưa vào hàng đợi bếp</em></div></article><article class="accepted"><span class="material-symbols-outlined">notifications_active</span><div><small>Chờ tiếp nhận</small><strong>{{ $waitingCount }}</strong><em>Cần nhân viên xử lý</em></div></article><article class="cooking"><span class="material-symbols-outlined">skillet</span><div><small>Đang chế biến</small><strong>{{ $preparingCount }}</strong><em>{{ $averagePreparationMinutes ? 'TB '.$averagePreparationMinutes.' phút' : 'Chưa đủ dữ liệu thời gian' }}</em></div></article><article class="ready"><span class="material-symbols-outlined">room_service</span><div><small>Sẵn sàng giao</small><strong>{{ $readyCount }}</strong><em>Đang chờ khách nhận</em></div></article></div>
        </section>

        <section class="kitchen-tools">
            <form method="GET" action="{{ route('staff.dashboard') }}"><label><span class="material-symbols-outlined">search</span><input name="search" value="{{ request('search') }}" placeholder="Tìm mã nhận món, khách hàng hoặc tên món"></label><select name="status" aria-label="Lọc trạng thái"><option value="">Tất cả trạng thái</option><option value="pending_payment" @selected(request('status')==='pending_payment')>Chờ thanh toán</option><option value="paid" @selected(request('status')==='paid')>Chờ tiếp nhận</option><option value="preparing" @selected(request('status')==='preparing')>Đang chế biến</option><option value="ready" @selected(request('status')==='ready')>Sẵn sàng giao</option></select><button class="button" type="submit">Áp dụng</button></form><a class="kitchen-refresh" href="{{ route('staff.dashboard') }}"><span class="material-symbols-outlined">refresh</span>Làm mới dữ liệu</a>
        </section>

        @if($orders->where('status','pending_payment')->isNotEmpty())<section class="payment-waiting"><span class="material-symbols-outlined">hourglass_top</span><strong>Chờ khách thanh toán</strong><div>@foreach($orders->where('status','pending_payment') as $order)<span>#{{ $order->pickup_code ?? $order->id }} · {{ $order->user->name }} · {{ $order->items->pluck('food.name')->join(', ') }}</span>@endforeach</div><small>Chưa thể xử lý</small></section>@endif

        <section class="kitchen-board" id="order-board">
            @foreach($lanes as $status => $lane)
                <section class="kitchen-lane lane-{{ $status }}"><header><div><span class="material-symbols-outlined">{{ $lane['icon'] }}</span><span><strong>{{ $loop->iteration }}. {{ $lane['title'] }}</strong><small>{{ $lane['subtitle'] }}</small></span></div><b>{{ $orders->where('status',$status)->count() }}</b></header><div class="kitchen-order-list">
                    @forelse($orders->where('status',$status) as $order)
                        @php($minutesUntilPickup = (int) ceil(now()->diffInMinutes(\Illuminate\Support\Carbon::parse($order->pickup_slot), false)))
                        <article class="kitchen-order-card {{ $minutesUntilPickup <= 10 ? 'urgent' : '' }}">
                            <div class="order-card-head"><div><small>Mã nhận món</small><strong>#{{ $order->pickup_code ?? 'A-'.str_pad($order->id,3,'0',STR_PAD_LEFT) }}</strong></div><span class="{{ $minutesUntilPickup <= 10 ? 'urgent' : '' }}"><small>Giờ nhận</small><b>{{ \Illuminate\Support\Carbon::parse($order->pickup_slot)->format('H:i') }}</b></span></div>
                            <div class="order-customer"><span class="material-symbols-outlined">person</span><div><strong>{{ $order->user->name }}</strong><small>#FG-{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }} · {{ $order->fulfillment_type === 'takeaway' ? 'Mang đi' : 'Ăn tại căn tin' }}</small></div></div>
                            <div class="order-food-list">@foreach($order->items as $item)<div><b>{{ $item->quantity }}</b><div><strong>{{ $item->food->name }}</strong>@if($item->optionLabels())<small>{{ implode(' · ',$item->optionLabels()) }}</small>@endif</div></div>@endforeach</div>
                            <div class="order-card-foot"><span><span class="material-symbols-outlined">schedule</span>{{ $minutesUntilPickup > 0 ? 'Còn '.$minutesUntilPickup.' phút' : 'Đã đến giờ nhận' }}</span><strong>{{ number_format($order->total,0,',','.') }}đ</strong></div>
                            @if($status==='paid')<div class="order-actions"><form method="POST" action="{{ route('staff.orders.status',$order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="preparing"><button class="primary" type="submit"><span class="material-symbols-outlined">skillet</span>Nhận đơn & chế biến</button></form><details><summary aria-label="Từ chối đơn"><span class="material-symbols-outlined">more_horiz</span></summary><form method="POST" action="{{ route('staff.orders.status',$order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><label>Lý do từ chối<input name="rejection_reason" maxlength="1000" placeholder="Nhập lý do từ chối" required></label><button type="submit">Xác nhận từ chối</button></form></details></div>
                            @elseif($status==='preparing')<form method="POST" action="{{ route('staff.orders.status',$order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ready"><button class="lane-action cooking" type="submit"><span class="material-symbols-outlined">room_service</span>Đánh dấu sẵn sàng</button></form>
                            @else<form method="POST" action="{{ route('staff.orders.status',$order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="lane-action ready" type="submit"><span class="material-symbols-outlined">verified</span>Xác nhận đã giao</button></form>@endif
                        </article>
                    @empty<div class="lane-empty"><span class="material-symbols-outlined">check_circle</span><strong>Không có đơn</strong><p>{{ request()->filled('search') || request()->filled('status') ? 'Không có kết quả phù hợp bộ lọc.' : 'Các đơn mới sẽ xuất hiện tại đây.' }}</p></div>@endforelse
                </div></section>
            @endforeach
        </section>

        <section class="kitchen-insights" id="shift-summary">
            <article id="completed-orders"><div class="insight-head"><div><span class="material-symbols-outlined">history</span><span><strong>Đơn đã giao trong ca</strong><small>{{ $completedTodayCount }} đơn · {{ $completedTodayPortions }} suất</small></span></div><span>{{ now()->format('d/m/Y') }}</span></div><div class="recent-orders">@forelse($recentCompletedOrders as $order)<div><b>#{{ $order->pickup_code ?? $order->id }}</b><span>{{ $order->user->name }}</span><small>{{ $order->completed_at?->format('H:i') ?? $order->updated_at->format('H:i') }}</small><em>{{ $order->items->sum('quantity') }} suất</em></div>@empty<div class="insight-empty">Chưa có đơn hoàn tất trong ca hôm nay.</div>@endforelse</div></article>
            <article id="stock-watch"><div class="insight-head"><div><span class="material-symbols-outlined">inventory_2</span><span><strong>Theo dõi tồn kho thấp</strong><small>Chỉ hiển thị món còn tối đa 10 phần</small></span></div><a href="{{ route('home') }}">Xem thực đơn</a></div><div class="stock-watch-list">@forelse($lowStockFoods as $food)<div><span class="material-symbols-outlined">restaurant</span><p><strong>{{ $food->name }}</strong><small>{{ $food->category }}</small></p><b>Còn {{ $food->stock }}</b></div>@empty<div class="insight-empty"><span class="material-symbols-outlined">verified</span>Tồn kho các món đang ổn định.</div>@endforelse</div></article>
        </section>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{const toggle=document.querySelector('[data-auto-refresh]');if(!toggle)return;toggle.checked=localStorage.getItem('foodgo-kds-refresh')==='on';let timer;const sync=()=>{clearInterval(timer);localStorage.setItem('foodgo-kds-refresh',toggle.checked?'on':'off');if(toggle.checked)timer=setInterval(()=>location.reload(),30000)};toggle.addEventListener('change',sync);sync()});</script>
@endsection
