<nav class="admin-nav" aria-label="Quản trị FoodGo">
    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="material-symbols-outlined">dashboard</span>Tổng quan Vận hành</a>
    <a class="{{ request()->routeIs('admin.foods.*') ? 'active' : '' }}" href="{{ route('admin.foods.index') }}"><span class="material-symbols-outlined">restaurant_menu</span>Quầy & Thực đơn</a>
    <a href="{{ route('staff.dashboard') }}"><span class="material-symbols-outlined">skillet</span>Điều phối Bếp (KDS)</a>
    <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><span class="material-symbols-outlined">analytics</span>Báo cáo Cao điểm</a>
    <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="material-symbols-outlined">group</span>Hệ thống & Nhân sự</a>
</nav>
