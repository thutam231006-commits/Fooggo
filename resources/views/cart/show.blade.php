@extends('layouts.app')
@section('title', 'Giỏ hàng · FoodGo')
@section('content')
<div class="eyebrow">Đơn hàng của bạn</div>
<h1 style="font-size:38px">Giỏ hàng</h1>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<div class="table-wrap"><table><thead><tr><th>Món ăn</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr></thead><tbody>
@forelse($cart->items as $item)
<tr><td><a class="cart-food" href="{{ route('foods.show', $item->food) }}">@if($item->food->displayImageUrl())<img src="{{ $item->food->displayImageUrl() }}" alt="">@else<span class="cart-food-placeholder">{{ $item->food->displayEmoji() }}</span>@endif<span>{{ $item->food->name }}</span></a></td><td>{{ number_format($item->food->price, 0, ',', '.') }} đ</td><td><form class="actions" method="POST" action="{{ route('cart.items.update', $item) }}">@csrf @method('PATCH')<input class="quantity-input" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->food->stock }}" required><button class="button secondary" type="submit">Cập nhật</button></form></td><td><strong>{{ number_format($item->quantity * $item->food->price, 0, ',', '.') }} đ</strong></td><td><form method="POST" action="{{ route('cart.items.destroy', $item) }}">@csrf @method('DELETE')<button class="button danger" type="submit">Xóa</button></form></td></tr>
@empty<tr><td colspan="5" class="muted">Giỏ hàng đang trống. Hãy chọn món trước khi đặt hàng.</td></tr>@endforelse
</tbody></table></div>
@if($cart->items->isNotEmpty())
<div class="bottom" style="font-size:20px"><strong>Tổng cộng</strong><strong>{{ number_format($total, 0, ',', '.') }} đ</strong></div>
<div class="quick-actions" style="margin-top:20px"><a class="button secondary" href="{{ route('home') }}">← Tiếp tục chọn món</a></div>
<form class="checkout" method="POST" action="{{ route('web.orders.store') }}">
    @csrf
    <div class="field"><label for="pickup_slot">Khung giờ nhận món</label><input id="pickup_slot" type="datetime-local" name="pickup_slot" value="{{ old('pickup_slot', now()->addHour()->format('Y-m-d\TH:i')) }}" min="{{ now()->addMinutes(15)->format('Y-m-d\TH:i') }}" required></div>
    <button class="button" type="submit">Đặt hàng</button>
</form>
@else
<div class="actions" style="margin-top:20px"><a class="button" href="{{ route('home') }}">Chọn món</a><button class="button secondary" type="button" disabled>Đặt hàng</button></div>
@endif
@endsection
