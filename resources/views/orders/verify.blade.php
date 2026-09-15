@extends('layouts.app')
@section('title', 'Xác minh phiếu nhận món · FoodGo')
@section('content')
@php($valid = in_array($order->status, ['paid', 'preparing', 'ready'], true))
<section class="pickup-verification {{ $valid ? 'valid' : 'inactive' }}"><span class="material-symbols-outlined">{{ $valid ? 'verified' : 'info' }}</span><div class="eyebrow">Xác minh phiếu FoodGo</div><h1>{{ $order->pickup_code ?? 'A-'.str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</h1><p>{{ $valid ? 'Phiếu hợp lệ. Đối chiếu trạng thái đơn trước khi giao món.' : 'Phiếu này hiện không còn hiệu lực để nhận món.' }}</p><dl><div><dt>Mã đơn</dt><dd>#FG-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</dd></div><div><dt>Khung giờ nhận</dt><dd>{{ $order->pickup_slot }}</dd></div><div><dt>Trạng thái</dt><dd>{{ $order->status }}</dd></div></dl></section>
@endsection
