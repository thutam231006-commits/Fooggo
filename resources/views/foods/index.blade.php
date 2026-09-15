@extends('layouts.app')
@section('title', 'Thực đơn · FoodGo')
@section('page')
<section class="menu-hero">
    <div class="menu-hero-inner"><div class="eyebrow">Đang phục vụ trưa · Căn tin FoodGo</div><h1>Thực đơn nóng sốt hôm nay</h1><p class="lead">Khung giờ vàng: <strong>11:00 - 13:30</strong>. Đặt món trước, đến quầy đúng giờ và không cần xếp hàng.</p></div>
    <div class="hero-badges"><span class="hero-badge"><span class="material-symbols-outlined">schedule</span>5 - 8 phút</span><span class="hero-badge hero-badge-green"><span class="material-symbols-outlined">verified</span>Ưu tiên theo giờ</span></div>
</section>
<div class="wrap menu-content">
    @if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
    <div class="queue-overview" aria-label="Tình trạng quầy">
        <span class="queue-all"><span class="material-symbols-outlined">restaurant</span>Tất cả quầy <b>{{ $availableFoodCount }}</b></span>
        @foreach($queueStats as $queue)<a href="{{ route('home', ['category' => $queue->category]) }}"><span class="material-symbols-outlined">storefront</span><span><strong>{{ $queue->category }}</strong><small>{{ $queue->food_count }} món · {{ $queue->portions }} phần</small></span></a>@endforeach
    </div>
    <div class="menu-layout">
        <div class="menu-main">
            <section class="menu-controls">
                <form class="menu-search" method="GET" action="{{ route('home') }}"><span class="material-symbols-outlined">search</span><input name="search" value="{{ request('search') }}" placeholder="Tìm món ăn, đồ uống..."><button class="button" type="submit">Tìm món</button></form>
                <div class="toolbar-filters"><a class="pill {{ request('category') ? '' : 'active' }}" href="{{ route('home', request()->only('search')) }}">Tất cả món</a>@foreach($categories as $category)<a class="pill {{ request('category') === $category ? 'active' : '' }}" href="{{ route('home', array_filter(['category'=>$category,'search'=>request('search')])) }}">{{ $category }}</a>@endforeach</div>
            </section>
            <div class="menu-section-title"><div><span class="material-symbols-outlined">local_fire_department</span><h2>{{ request('search') ? 'Kết quả tìm kiếm' : 'Món nổi bật hôm nay' }}</h2></div><span>{{ $foods->total() }} món đang phục vụ</span></div>
            <div class="grid">
                @forelse($foods as $food)
                    <article class="card">
                        <a class="card-link" href="{{ route('foods.show', $food) }}">
                            <div class="food-photo">@if($food->displayImageUrl())<img class="food-image" src="{{ $food->displayImageUrl() }}" alt="{{ $food->name }}" loading="lazy">@else<div class="food-image food-placeholder">{{ $food->displayEmoji() }}</div>@endif<span class="counter-chip">Quầy {{ in_array($food->category,['Đồ uống']) ? '4' : '1' }}</span></div>
                            <div class="card-body"><div class="food-meta"><span>{{ $food->reviews_count ? '★ '.number_format($food->reviews_avg_rating,1) : 'Món mới' }}</span><span>{{ $food->category ?: 'Món ăn' }}</span></div><div class="name">{{ $food->name }}</div><div class="desc">{{ Str::limit($food->description, 66) }}</div><div class="bottom"><span class="price">{{ number_format($food->price,0,',','.') }}đ</span><span class="stock">Còn {{ $food->stock }}</span></div></div>
                        </a>
                        <div class="card-body card-footer-actions">
                            @auth
                                @if(auth()->user()->role === 'customer')<form class="card-actions" method="POST" action="{{ route('cart.items.store') }}">@csrf<input type="hidden" name="food_id" value="{{ $food->id }}"><input type="hidden" name="quantity" value="1"><input type="hidden" name="redirect_to" value="menu"><a class="customize-link" href="{{ route('foods.show',$food) }}">Tùy chọn</a><button class="button" type="submit" aria-label="Thêm vào giỏ"><span class="material-symbols-outlined">add</span><span class="sr-only">Thêm vào giỏ</span><span aria-hidden="true">Chọn</span></button></form>@endif
                            @else<a class="button full" href="{{ route('login') }}">Đăng nhập để chọn món</a>@endauth
                        </div>
                    </article>
                @empty<div class="menu-empty"><span class="material-symbols-outlined">search_off</span><h3>Không tìm thấy món phù hợp</h3><p>Hãy thử tên món hoặc danh mục khác.</p><a class="button secondary" href="{{ route('home') }}">Xem toàn bộ thực đơn</a></div>@endforelse
            </div>
            {{ $foods->links('partials.pagination') }}
            <section class="service-status"><div class="menu-section-title"><div><span class="material-symbols-outlined">monitoring</span><h2>Tình trạng phục vụ thực tế tại các quầy</h2></div><span>Cập nhật theo tồn kho hiện tại</span></div><div class="service-status-grid">@foreach($queueStats as $queue)<article><span>Quầy {{ $loop->iteration }}</span><strong>{{ $queue->category }}</strong><small>{{ $queue->portions > 40 ? 'Sẵn sàng phục vụ' : 'Số lượng có hạn' }}</small><b>{{ $queue->portions }} phần</b></article>@endforeach</div></section>
        </div>
        <aside class="cart-sidebar">
            <div class="cart-sidebar-head"><div><h2>Khay món của bạn</h2><span class="muted">{{ $cart?->items->sum('quantity') ?? 0 }} món đã chọn</span></div><span class="cart-count">{{ $cart?->items->sum('quantity') ?? 0 }}</span></div>
            @if($cart && $cart->items->isNotEmpty())
                <div class="cart-mini-list">@foreach($cart->items as $item)<div class="cart-mini-item">@if($item->food->displayImageUrl())<img src="{{ $item->food->displayImageUrl() }}" alt="">@endif<div><strong>{{ Str::limit($item->food->name,24) }}</strong><small>{{ number_format($item->effective_unit_price,0,',','.') }}đ · SL {{ $item->quantity }}</small></div><span>{{ number_format($item->quantity*$item->effective_unit_price,0,',','.') }}đ</span></div>@endforeach</div><div class="cart-total"><span>Tổng cộng</span><strong>{{ number_format($cart->items->sum(fn($i)=>$i->quantity*$i->effective_unit_price),0,',','.') }}đ</strong></div><a class="button full" href="{{ route('cart.show') }}">Chọn giờ nhận món <span class="material-symbols-outlined">arrow_forward</span></a><div class="safe-note"><span class="material-symbols-outlined">account_balance_wallet</span>Số dư: {{ number_format(auth()->user()->wallet_balance,0,',','.') }}đ</div>
            @elseif(auth()->check() && auth()->user()->role === 'customer')<div class="cart-empty"><span class="material-symbols-outlined">shopping_bag</span><p>Chưa có món nào trong khay.</p><small>Chọn món để bắt đầu đơn hàng.</small></div>
            @else<div class="cart-empty"><span class="material-symbols-outlined">person</span><p>Đăng nhập tài khoản khách hàng để đặt món.</p><a class="button" href="{{ route('login') }}">Đăng nhập</a></div>@endif
        </aside>
    </div>
</div>
@endsection
