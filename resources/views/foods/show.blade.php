@extends('layouts.app')
@section('title', $food->name.' · FoodGo')
@section('content')
@php
    $riceTypes = \App\Services\FoodCustomizationService::RICE_TYPES;
    $extras = \App\Services\FoodCustomizationService::EXTRAS;
@endphp
<div class="product-breadcrumb"><a href="{{ route('home') }}">Thực đơn trưa</a><span>/</span><strong>{{ $food->category }} · {{ $food->name }}</strong></div>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
@if($food->stock === 0)<div class="alert error-alert"><strong>Hết sản phẩm.</strong> Món ăn này hiện không thể thêm vào giỏ hàng.</div>@endif
<form class="product-customizer" method="POST" action="{{ route('cart.items.store') }}" data-customization-form>
    @csrf
    <input type="hidden" name="food_id" value="{{ $food->id }}">
    <section class="product-visual">
        <div class="product-photo">@if($food->displayImageUrl())<img src="{{ $food->displayImageUrl() }}" alt="{{ $food->name }}">@else<div class="food-image food-placeholder">{{ $food->displayEmoji() }}</div>@endif<span class="photo-badge">Ảnh chụp món thật</span></div>
        <div class="product-facts"><div><span class="material-symbols-outlined">local_fire_department</span><small>Năng lượng</small><strong>620 kcal</strong></div><div><span class="material-symbols-outlined">restaurant</span><small>Trạng thái</small><strong>Món nóng hổi</strong></div><div><span class="material-symbols-outlined">storefront</span><small>Quầy chế biến</small><strong>Quầy 1</strong></div></div>
        <div class="chef-note"><span class="material-symbols-outlined">lightbulb</span><p><strong>Gợi ý từ Bếp trưởng</strong><small>Ăn kèm trứng ốp la và canh rong biển giúp suất ăn cân bằng hơn.</small></p></div>
    </section>
    <section class="product-options">
        <div class="product-heading"><div><span class="eyebrow">Món được yêu thích</span><h1>{{ $food->name }}</h1><p>{{ $food->description }}</p></div><span class="stock">Còn {{ $food->stock }} phần</span></div>
        <div class="product-base-price"><strong>{{ number_format($food->price,0,',','.') }}đ</strong><small>Giá món cơ bản</small></div>
        <fieldset><legend><b>1</b> Chọn loại cơm <span>Bắt buộc chọn</span></legend><div class="option-grid three">@foreach($riceTypes as $key=>$option)<label class="option-card"><input type="radio" name="rice_type" value="{{ $key }}" data-price="{{ $option['price'] }}" @checked($loop->first)><strong>{{ $option['label'] }}</strong><small>{{ $option['price'] ? '+'.number_format($option['price'],0,',','.').'đ' : 'Mặc định · +0đ' }}</small></label>@endforeach</div></fieldset>
        <fieldset><legend><b>2</b> Topping & Món ăn kèm <span>Chọn nhiều món</span></legend><div class="option-grid two">@foreach($extras as $key=>$option)<label class="option-check"><input type="checkbox" name="extras[]" value="{{ $key }}" data-price="{{ $option['price'] }}"><span class="material-symbols-outlined">check_box_outline_blank</span><strong>{{ $option['label'] }}</strong><small>+{{ number_format($option['price'],0,',','.') }}đ</small></label>@endforeach</div></fieldset>
        <fieldset><legend><b>3</b> Khẩu vị & Nước chấm</legend><div class="taste-grid"><label>Nước chấm<select name="sauce"><option value="default">Chua ngọt đậm đà</option><option value="spicy">Cay nhiều</option><option value="mild">Vị nhẹ</option></select></label><label>Độ cay<select name="spice_level"><option value="none">Không cay</option><option value="medium">Cay vừa</option><option value="hot">Cay nhiều</option></select></label></div></fieldset>
        <fieldset><legend><b>4</b> Ghi chú đặc biệt cho Quầy Bếp <span>Không tính phí</span></legend><textarea name="note" maxlength="300" rows="3" placeholder="Ví dụ: không hành, ít nước mắm, canh để riêng...">{{ old('note') }}</textarea></fieldset>
    </section>
    <div class="product-order-bar"><div class="quantity-stepper"><button type="button" data-quantity-minus aria-label="Giảm số lượng">−</button><input name="quantity" value="{{ old('quantity',1) }}" min="1" max="{{ $food->stock }}" readonly><button type="button" data-quantity-plus aria-label="Tăng số lượng">+</button></div><div><small>Tổng cộng tạm tính</small><strong data-total-price data-base-price="{{ (float)$food->price }}">{{ number_format($food->price,0,',','.') }}đ</strong></div>@if($food->stock > 0)@auth @if(auth()->user()->role === 'customer')<button class="button" type="submit"><span class="material-symbols-outlined">shopping_bag</span>Thêm vào giỏ hàng</button>@else<span class="muted">Chỉ tài khoản khách hàng được đặt món.</span>@endif @else<a class="button" href="{{ route('login') }}">Đăng nhập để đặt món</a>@endauth @else<button class="button" type="button" disabled>Hết hàng</button>@endif</div>
</form>
<section class="product-promises"><div><span class="material-symbols-outlined">groups</span><p><strong>Không cần xếp hàng</strong><small>Theo dõi khi món sẵn sàng trên phiếu nhận.</small></p></div><div><span class="material-symbols-outlined">restaurant</span><p><strong>Vệ sinh từ bếp</strong><small>Quy trình chế biến minh bạch.</small></p></div><div><span class="material-symbols-outlined">account_balance_wallet</span><p><strong>Thanh toán Ví FoodGo</strong><small>Tự động trừ và hoàn tiền.</small></p></div></section>
<div class="section-heading"><h2>Đánh giá gần đây</h2>@if($food->reviews->isNotEmpty())<span class="rating-stars">★ {{ number_format($food->reviews->avg('rating'),1) }}/5</span>@endif</div><div class="review-list">@forelse($food->reviews as $review)<article class="review-item"><div class="actions" style="justify-content:space-between"><strong>{{ $review->user->name }}</strong><span class="rating-stars">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</span></div>@if($review->content)<p>{{ $review->content }}</p>@endif</article>@empty<div class="panel muted">Món ăn chưa có đánh giá.</div>@endforelse</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-customization-form]');
    if (!form) return;
    const quantity = form.querySelector('[name="quantity"]');
    const total = form.querySelector('[data-total-price]');
    const updateTotal = () => {
        let unit = Number(total.dataset.basePrice);
        form.querySelectorAll('[data-price]:checked').forEach(input => unit += Number(input.dataset.price));
        total.textContent = new Intl.NumberFormat('vi-VN').format(unit * Number(quantity.value)) + 'đ';
    };
    form.addEventListener('change', updateTotal);
    form.querySelector('[data-quantity-minus]').addEventListener('click', () => { quantity.value = Math.max(1, Number(quantity.value) - 1); updateTotal(); });
    form.querySelector('[data-quantity-plus]').addEventListener('click', () => { quantity.value = Math.min(Number(quantity.max), Number(quantity.value) + 1); updateTotal(); });
});
</script>
@endsection
