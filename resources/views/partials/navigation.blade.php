<nav class="nav-links" aria-label="Điều hướng chính">
    <a class="nav-link" href="{{ route('home') }}">Thực đơn</a>
    @auth
        @if(auth()->user()->role === 'customer')
            <a class="nav-link" href="{{ route('cart.show') }}">Giỏ hàng ({{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }})</a>
        @endif
        <a class="nav-link" href="{{ route(auth()->user()->role.'.dashboard') }}">Trang của tôi</a>
        <span class="role-badge">{{ ['customer' => 'KHÁCH HÀNG', 'staff' => 'NHÂN VIÊN', 'admin' => 'QUẢN TRỊ'][auth()->user()->role] }}</span>
        <form class="inline-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button secondary nav-command" type="submit">Đăng xuất</button>
        </form>
    @else
        <a class="button secondary nav-command" href="{{ route('login') }}">Đăng nhập</a>
        <a class="button nav-command" href="{{ route('register') }}">Đăng ký</a>
    @endauth
</nav>
