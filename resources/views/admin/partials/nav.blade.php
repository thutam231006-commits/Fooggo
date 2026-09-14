<nav class="admin-nav" aria-label="Quản trị FoodGo">
    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Tổng quan</a>
    <a class="{{ request()->routeIs('admin.foods.*') ? 'active' : '' }}" href="{{ route('admin.foods.index') }}">Món ăn</a>
    <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Tài khoản</a>
    <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Báo cáo</a>
</nav>
