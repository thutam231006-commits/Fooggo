<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FoodGo')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" rel="stylesheet">
    <style>
        :root { --green:#087f5b; --role:#087f5b; --role-soft:#e9f8f2; --ink:#17201d; --muted:#6b7772; --line:#e6ebe8; --bg:#f7faf8; }
        body.role-staff { --role:#a35400; --role-soft:#fff4e5; } body.role-admin { --role:#2457a6; --role-soft:#edf4ff; }
        * { box-sizing:border-box; } body { margin:0; font-family:Inter,Segoe UI,Arial,sans-serif; color:var(--ink); background:var(--bg); }
        a { color:inherit; text-decoration:none; } .wrap { width:min(1120px, calc(100% - 32px)); margin:auto; }
        header { background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:2; }
        .nav { min-height:68px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .brand { font-size:22px; font-weight:800; color:var(--role); } .nav-links { display:flex; align-items:center; gap:10px; } .nav-link { color:var(--muted); font-size:14px; padding:9px 4px; } .nav-link:hover { color:var(--role); } .nav-command { min-height:36px; padding:0 12px; font-size:13px; } .role-badge { color:var(--role); background:var(--role-soft); padding:6px 9px; border-radius:5px; font-size:12px; font-weight:800; }
        main { padding:42px 0 64px; } .eyebrow { color:var(--green); font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        h1 { font-size:clamp(30px,5vw,52px); line-height:1.05; max-width:650px; margin:10px 0 14px; letter-spacing:-1.2px; } .lead { color:var(--muted); max-width:620px; line-height:1.6; }
        .toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin:30px 0 20px; } .pill { border:1px solid var(--line); background:#fff; padding:9px 14px; border-radius:6px; font-size:14px; } .pill.active { color:#fff; background:var(--green); border-color:var(--green); }
        .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; } .card { background:#fff; border:1px solid var(--line); border-radius:8px; overflow:hidden; transition:transform .15s, box-shadow .15s; } .card:hover { transform:translateY(-2px); box-shadow:0 8px 24px #12331d12; } .card-link { display:block; } .card-actions { display:grid; grid-template-columns:1fr auto; gap:8px; margin-top:14px; } .card-actions .button { min-height:38px; padding:0 12px; font-size:13px; }
        .food-image { height:150px; background:linear-gradient(135deg,#d8f3e9,#f9dfb7); display:grid; place-items:center; font-size:48px; } .card-body { padding:16px; } .category { color:var(--green); font-size:12px; font-weight:700; } .name { font-size:17px; font-weight:750; margin:7px 0; } .desc { color:var(--muted); font-size:13px; line-height:1.45; min-height:38px; } .bottom { display:flex; justify-content:space-between; align-items:center; margin-top:14px; } .price { font-weight:800; } .stock { font-size:12px; color:var(--muted); } .detail { color:var(--green); font-size:13px; font-weight:700; }
        .pagination { margin-top:28px; display:flex; gap:8px; } .pagination a,.pagination span { background:#fff; border:1px solid var(--line); padding:8px 12px; border-radius:5px; font-size:13px; } .pagination .current { background:var(--green); color:#fff; }
        .detail-page { max-width:720px; background:#fff; padding:30px; border:1px solid var(--line); border-radius:8px; } .back { color:var(--green); font-size:14px; }
        .auth-shell { min-height:calc(100vh - 200px); display:grid; place-items:center; } .auth-box { width:min(460px,100%); background:#fff; border:1px solid var(--line); border-radius:8px; padding:28px; } .auth-box h1 { font-size:28px; margin:0 0 8px; } .field { display:grid; gap:7px; margin-top:16px; } label { font-size:13px; font-weight:700; } input,select,textarea { width:100%; border:1px solid #ccd5d0; border-radius:6px; padding:11px 12px; font:inherit; background:#fff; } input:focus { outline:2px solid #b9e7d7; border-color:var(--green); } .button { display:inline-flex; justify-content:center; align-items:center; min-height:42px; border:0; border-radius:6px; padding:0 16px; background:var(--green); color:#fff; font-weight:700; cursor:pointer; } .button.full { width:100%; margin-top:20px; } .button.secondary { background:#fff; color:var(--ink); border:1px solid var(--line); } .button.danger { background:#fff; color:#b42318; border:1px solid #f0c7c3; } .error { color:#b42318; font-size:13px; margin-top:5px; } .alert { padding:12px 14px; border-radius:6px; background:#e9f8f2; color:#086747; margin-bottom:16px; } .alert.error-alert { background:#fff0ee; color:#9f1c13; } .muted { color:var(--muted); }
        .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin:24px 0 30px; } .stat { border:1px solid var(--line); background:#fff; padding:18px; border-radius:8px; } .stat strong { display:block; font-size:26px; margin-top:6px; } .table-wrap { overflow-x:auto; border:1px solid var(--line); background:#fff; border-radius:8px; } table { width:100%; border-collapse:collapse; min-width:700px; } th,td { padding:13px 14px; border-bottom:1px solid var(--line); text-align:left; font-size:14px; } th { color:var(--muted); font-size:12px; text-transform:uppercase; } .badge { display:inline-block; padding:5px 8px; border-radius:5px; background:#edf3f0; font-size:12px; font-weight:700; } .inline-form { display:inline; }
        .actions { display:flex; flex-wrap:wrap; gap:10px; align-items:center; } .quantity-input { width:84px; } .checkout { margin-top:24px; display:grid; grid-template-columns:minmax(220px,360px) auto; gap:12px; align-items:end; } .order-summary { display:grid; grid-template-columns:1.4fr .8fr; gap:24px; } .panel { background:#fff; border:1px solid var(--line); border-radius:8px; padding:22px; }
        .role-header { border-left:5px solid var(--role); padding:4px 0 4px 18px; margin-bottom:24px; } .role-header .eyebrow { color:var(--role); } .role-header h1 { font-size:36px; margin:6px 0 8px; } .quick-actions { display:flex; flex-wrap:wrap; gap:10px; margin:0 0 28px; } .quick-actions .button { background:var(--role); } .stats.four { grid-template-columns:repeat(4,1fr); } .section-heading { display:flex; justify-content:space-between; align-items:center; gap:16px; margin:28px 0 12px; } .section-heading h2 { margin:0; font-size:20px; } .staff-queue { border-top:3px solid var(--role); } .admin-summary { border-top:3px solid var(--role); }
        footer { border-top:1px solid var(--line); padding:24px 0; color:var(--muted); font-size:13px; }
        @media(max-width:900px){.grid{grid-template-columns:repeat(3,1fr)}.stats.four{grid-template-columns:repeat(2,1fr)}} @media(max-width:650px){.grid{grid-template-columns:repeat(2,1fr)} h1{font-size:36px}.stats,.stats.four,.order-summary{grid-template-columns:1fr}.checkout{grid-template-columns:1fr}.nav{align-items:flex-start;padding:16px 0}.nav-links{flex-wrap:wrap;justify-content:flex-end}} @media(max-width:440px){.grid{grid-template-columns:1fr}.wrap{width:min(100% - 24px,1120px)}}
    </style>
    <link rel="stylesheet" href="{{ asset('css/foodgo.css') }}">
</head>
@php($operationsPage = request()->routeIs('staff.dashboard', 'admin.dashboard'))
<body class="@auth role-{{ auth()->user()->role }} @endauth {{ $operationsPage ? 'operations-page' : '' }}">
@unless($operationsPage)<header><div class="wrap nav"><a class="brand" href="{{ route('home') }}"><span class="material-symbols-outlined">restaurant</span><span>FoodGo</span></a>@include('partials.navigation')</div></header>@endunless
@if($operationsPage)
    <main class="operations-main"><form class="operations-logout" method="POST" action="{{ route('logout') }}">@csrf<button type="submit" title="Đăng xuất" aria-label="Đăng xuất"><span class="material-symbols-outlined">logout</span></button></form>@yield('content')</main>
@elseif(View::hasSection('page'))
    <main>@yield('page')</main>
@else
    <main><div class="wrap">@yield('content')</div></main>
@endif
@unless($operationsPage)<footer><div class="wrap">FoodGo · Đặt món căn tin nhanh chóng</div></footer>@endunless
</body>
</html>
