<nav class="nav-links" aria-label="Điều hướng chính">
    <a class="nav-link" href="{{ route('home') }}"><span class="material-symbols-outlined">restaurant_menu</span>Thực đơn hôm nay</a>
    @auth
        @if(auth()->user()->role === 'customer')
            <a class="nav-link" href="{{ route('cart.show') }}"><span class="material-symbols-outlined">shopping_bag</span>Giỏ hàng <b class="cart-count">{{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }}</b></a>
        @endif
        <a class="nav-link" href="{{ route(auth()->user()->role.'.dashboard') }}"><span class="material-symbols-outlined">dashboard</span>Trang của tôi</a>
        <span class="role-badge">{{ ['customer' => 'KHÁCH HÀNG', 'staff' => 'NHÂN VIÊN', 'admin' => 'QUẢN TRỊ'][auth()->user()->role] }}</span>
        <form class="inline-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button secondary nav-command" type="submit"><span class="material-symbols-outlined">logout</span>Đăng xuất</button>
        </form>
    @else
        <a class="button secondary nav-command" href="{{ route('login') }}">Đăng nhập</a>
        <a class="button nav-command" href="{{ route('register') }}">Đăng ký</a>
    @endauth
</nav>
