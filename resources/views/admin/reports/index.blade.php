@extends('layouts.app')
@section('title', 'Báo cáo kinh doanh · FoodGo')
@section('content')
@include('admin.partials.nav')
<div class="management-head"><div><div class="eyebrow">Phân tích hoạt động</div><h1>Báo cáo kinh doanh</h1></div></div>
@if($errors->any())<div class="alert error-alert">{{ $errors->first() }}</div>@endif
<form class="filter-bar" method="GET"><div><label for="from">Từ ngày</label><input id="from" type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}"></div><div><label for="to">Đến ngày</label><input id="to" type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}"></div><button class="button" type="submit">Xem báo cáo</button></form>
<div class="stats"><div class="stat admin-summary"><span class="muted">Đơn hoàn tất</span><strong>{{ $orderCount }}</strong></div><div class="stat admin-summary"><span class="muted">Tổng doanh thu</span><strong>{{ number_format($revenue, 0, ',', '.') }} đ</strong></div><div class="stat admin-summary"><span class="muted">Giá trị trung bình</span><strong>{{ number_format($averageOrder, 0, ',', '.') }} đ</strong></div></div>
<div class="section-heading"><h2>Món bán chạy</h2><span class="muted">{{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}</span></div>
<div class="table-wrap"><table><thead><tr><th>Xếp hạng</th><th>Món ăn</th><th>Danh mục</th><th>Số lượng bán</th><th>Doanh thu</th></tr></thead><tbody>
@forelse($bestSellers as $index => $item)<tr><td><strong>#{{ $index + 1 }}</strong></td><td>{{ $item->food->name }}</td><td>{{ $item->food->category }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->revenue, 0, ',', '.') }} đ</td></tr>
@empty<tr><td colspan="5" class="muted">Chưa có dữ liệu hoàn tất trong khoảng thời gian này.</td></tr>@endforelse
</tbody></table></div>
@endsection
