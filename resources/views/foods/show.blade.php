@extends('layouts.app')
@section('title', $food->name.' · FoodGo')
@section('content')
<div class="detail-page">
    <a class="back" href="{{ route('home') }}">← Quay lại thực đơn</a>
    @if($food->displayImageUrl())<img class="food-image" src="{{ $food->displayImageUrl() }}" alt="{{ $food->name }}">@else<div class="food-image food-placeholder" role="img" aria-label="{{ $food->category ?: 'Món ăn' }}">{{ $food->displayEmoji() }}</div>@endif
    <div class="category">{{ $food->category ?: 'Món ăn' }}</div>
    <h1 style="font-size:36px">{{ $food->name }}</h1>
    <p class="lead">{{ $food->description }}</p>
    <div class="bottom"><span class="price">{{ number_format($food->price, 0, ',', '.') }} đ</span><span class="stock">Còn {{ $food->stock }} phần</span></div>
    @if($food->stock > 0)
        @auth
            @if(auth()->user()->role === 'customer')
                <form method="POST" action="{{ route('cart.items.store') }}" style="margin-top:22px">
                    @csrf
                    <input type="hidden" name="food_id" value="{{ $food->id }}">
                    <div class="actions"><label for="quantity">Số lượng</label><input class="quantity-input" id="quantity" type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $food->stock }}" required><button class="button" type="submit">Thêm vào giỏ</button></div>
                    @error('quantity')<div class="error">{{ $message }}</div>@enderror
                </form>
            @else
                <div class="alert error-alert" style="margin-top:22px">Vui lòng đăng nhập bằng tài khoản khách hàng để đặt món.</div>
            @endif
        @else
            <a class="button" style="margin-top:22px" href="{{ route('login') }}">Đăng nhập để đặt món</a>
        @endauth
    @else
        <div class="alert error-alert" style="margin-top:22px">Món ăn hiện đã hết hàng.</div>
    @endif
</div>
<div class="section-heading" style="max-width:900px"><h2>Đánh giá gần đây</h2>@if($food->reviews->isNotEmpty())<span class="rating-stars">★ {{ number_format($food->reviews->avg('rating'), 1) }}/5</span>@endif</div>
<div class="review-list" style="max-width:900px">
@forelse($food->reviews as $review)<article class="review-item"><div class="actions" style="justify-content:space-between"><strong>{{ $review->user->name }}</strong><span class="rating-stars" aria-label="{{ $review->rating }} trên 5 sao">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></div>@if($review->content)<p>{{ $review->content }}</p>@endif</article>
@empty<div class="panel muted">Món ăn chưa có đánh giá.</div>@endforelse
</div>
@endsection
