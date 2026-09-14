@extends('layouts.app')
@section('title', 'Thực đơn · FoodGo')
@section('page')
<section class="menu-hero"><div class="menu-hero-inner"><div class="eyebrow">Căn tin FoodGo</div><h1>Thực đơn FoodGo</h1><p class="lead">Món ngon trong ngày, kiểm tra tồn kho tức thời và nhận đúng khung giờ bạn chọn.</p></div></section>
<div class="wrap menu-content">
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<div class="toolbar">
    <a class="pill {{ request('category') ? '' : 'active' }}" href="{{ route('home') }}">Tất cả</a>
    @foreach($categories as $category)<a class="pill {{ request('category') === $category ? 'active' : '' }}" href="{{ route('home', ['category' => $category]) }}">{{ $category }}</a>@endforeach
</div>
<div class="grid">
@forelse($foods as $food)
    <article class="card">
        <a class="card-link" href="{{ route('foods.show', $food) }}">@if($food->displayImageUrl())<img class="food-image" src="{{ $food->displayImageUrl() }}" alt="{{ $food->name }}" loading="lazy">@else<div class="food-image food-placeholder" role="img" aria-label="{{ $food->category ?: 'Món ăn' }}">{{ $food->displayEmoji() }}</div>@endif<div class="card-body"><div class="category">{{ $food->category ?: 'Món ăn' }}</div><div class="name">{{ $food->name }}</div><div class="desc">{{ Str::limit($food->description, 70) }}</div><div class="bottom"><span class="price">{{ number_format($food->price, 0, ',', '.') }} đ</span><span class="stock">{{ $food->stock > 0 ? 'Còn '.$food->stock : 'Hết hàng' }}</span></div></div></a>
        <div class="card-body" style="padding-top:0">
            @auth
                @if(auth()->user()->role === 'customer' && $food->stock > 0)
                    <form class="card-actions" method="POST" action="{{ route('cart.items.store') }}">
                        @csrf
                        <input type="hidden" name="food_id" value="{{ $food->id }}">
                        <input aria-label="Số lượng {{ $food->name }}" type="number" name="quantity" value="1" min="1" max="{{ $food->stock }}" required>
                        <button class="button" type="submit">Thêm vào giỏ</button>
                    </form>
                @elseif(auth()->user()->role !== 'customer')
                    <span class="muted" style="font-size:12px">Chỉ tài khoản khách hàng được đặt món.</span>
                @endif
            @else
                <a class="button full" style="margin-top:0" href="{{ route('login') }}">Đăng nhập để đặt món</a>
            @endauth
        </div>
    </article>
@empty <p>Chưa có món ăn đang bán.</p> @endforelse
</div>
{{ $foods->links('partials.pagination') }}
</div>
@endsection
