@extends('layouts.app')
@section('title', 'Quản lý món ăn · FoodGo')
@section('content')
@include('admin.partials.nav')
<div class="management-head"><div><div class="eyebrow">Quản trị thực đơn</div><h1>Quản lý món ăn</h1></div><a class="button" href="{{ route('admin.foods.create') }}">Thêm món</a></div>
@if(session('status'))<div class="alert">{{ session('status') }}</div>@endif
<form class="filter-bar" method="GET"><input name="search" value="{{ request('search') }}" placeholder="Tìm tên hoặc danh mục"><select name="status"><option value="">Tất cả trạng thái</option><option value="available" @selected(request('status') === 'available')>Đang bán</option><option value="unavailable" @selected(request('status') === 'unavailable')>Ngừng bán</option></select><button class="button secondary" type="submit">Lọc</button></form>
<div class="table-wrap"><table><thead><tr><th>Món ăn</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($foods as $food)<tr><td><div class="cart-food">@if($food->displayImageUrl())<img src="{{ $food->displayImageUrl() }}" alt="">@else<span class="cart-food-placeholder">{{ $food->displayEmoji() }}</span>@endif<span>{{ $food->name }}</span></div></td><td>{{ $food->category }}</td><td>{{ number_format($food->price, 0, ',', '.') }} đ</td><td>{{ $food->stock }}</td><td><span class="badge">{{ $food->is_available ? 'Đang bán' : 'Ngừng bán' }}</span></td><td><div class="actions"><a class="button secondary" href="{{ route('admin.foods.edit', $food) }}">Sửa</a>@if($food->is_available)<form method="POST" action="{{ route('admin.foods.destroy', $food) }}">@csrf @method('DELETE')<button class="button danger" type="submit">Ngừng bán</button></form>@endif</div></td></tr>
@empty<tr><td colspan="6" class="muted">Không có món ăn phù hợp.</td></tr>@endforelse
</tbody></table></div>
{{ $foods->links('partials.pagination') }}
@endsection
