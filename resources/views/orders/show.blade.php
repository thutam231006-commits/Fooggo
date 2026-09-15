@extends('layouts.app')
@section('title', 'Đơn hàng #'.$order->id.' · FoodGo')
@section('content')
@php $statusLabels = ['pending_payment' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán - chờ căn tin xác nhận', 'preparing' => 'Đang chuẩn bị', 'ready' => 'Sẵn sàng nhận', 'completed' => 'Đã hoàn tất', 'rejected' => 'Căn tin từ chối', 'cancelled' => 'Khách hàng hủy']; @endphp
<div class="eyebrow">Đơn hàng #{{ $order->id }}</div>
<h1 style="font-size:38px">{{ $statusLabels[$order->status] ?? $order->status }}</h1>
@if($order->status === 'rejected' && $order->rejection_reason)<div class="alert error-alert"><strong>Lý do từ chối:</strong> {{ $order->rejection_reason }}</div>@endif
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<div class="order-summary">
    <section class="panel"><h2>Chi tiết món</h2><div class="table-wrap" style="border:0"><table style="min-width:0"><thead><tr><th>Món</th><th>SL</th><th>Thành tiền</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->food->name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->subtotal, 0, ',', '.') }} đ</td></tr>@endforeach</tbody></table></div></section>
    <aside class="panel"><div class="field"><span class="muted">Thời gian nhận</span><strong>{{ $order->pickup_slot }}</strong></div><div class="field"><span class="muted">Tổng thanh toán</span><strong style="font-size:24px">{{ number_format($order->total, 0, ',', '.') }} đ</strong></div><div class="field"><span class="muted">Số dư Ví FoodGo</span><strong>{{ number_format(auth()->user()->wallet_balance, 0, ',', '.') }} đ</strong></div>
    @if($order->status === 'pending_payment' && $order->payment_expires_at)<div class="field"><span class="muted">Giữ món đến</span><strong>{{ $order->payment_expires_at->format('d/m/Y H:i') }}</strong></div>@endif
    @if($order->status === 'pending_payment')<form method="POST" action="{{ route('web.orders.pay', $order) }}">@csrf<button class="button full" type="submit">Thanh toán bằng Ví FoodGo</button></form><form method="POST" action="{{ route('web.orders.cancel', $order) }}">@csrf @method('DELETE')<button class="button danger full" type="submit">Hủy đơn hàng</button></form>@elseif($order->payment)<div class="alert" style="margin-top:18px">Mã giao dịch: {{ $order->payment->transaction_code }}</div>@endif
    </aside>
</div>
@if($order->status === 'completed')
<div class="section-heading"><h2>Đánh giá món ăn</h2><span class="muted">Mỗi món được đánh giá một lần trong đơn này</span></div>
<div class="review-grid">
@foreach($order->items as $item)
    @php $existingReview = $reviewsByFood->get($item->food_id); @endphp
    <section class="review-item"><div><strong>{{ $item->food->name }}</strong><div class="muted">{{ $item->quantity }} phần</div></div>
    @if($existingReview)<div class="rating-stars" aria-label="{{ $existingReview->rating }} trên 5 sao">{{ str_repeat('★', $existingReview->rating) }}{{ str_repeat('☆', 5 - $existingReview->rating) }}</div>@if($existingReview->content)<p>{{ $existingReview->content }}</p>@endif
    @else<form method="POST" action="{{ route('web.reviews.store', [$order, $item->food]) }}">@csrf<div class="review-form"><select name="rating" aria-label="Số sao cho {{ $item->food->name }}" required><option value="">Chọn số sao</option>@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} sao</option>@endfor</select><input name="content" maxlength="1000" placeholder="Nhận xét về món ăn"><button class="button" type="submit">Gửi đánh giá</button></div></form>@endif
    </section>
@endforeach
</div>
@endif
<p style="margin-top:20px"><a class="back" href="{{ route('customer.dashboard') }}">← Xem tất cả đơn hàng</a></p>
@endsection
