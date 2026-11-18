<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gmail Farmer Max')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,.12),transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,.10),transparent_30%)]"></div>
    <nav class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="font-bold tracking-tight">Gmail Farmer <span class="text-emerald-400">Max</span></a>
            @auth
                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white">Dashboard</a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.tasks') }}" class="text-slate-300 hover:text-white">Tasks</a>
                        <a href="{{ route('admin.withdrawals') }}" class="text-slate-300 hover:text-white">Withdrawals</a>
                        <a href="{{ route('admin.users') }}" class="text-slate-300 hover:text-white">Users</a>
                    @else
                        <a href="{{ route('tasks.index') }}" class="text-slate-300 hover:text-white">Tasks</a>
                        <a href="{{ route('withdrawals.index') }}" class="text-slate-300 hover:text-white">Withdrawals</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-md border border-white/10 px-3 py-1.5 hover:bg-white/5">Log out</button></form>
                </div>
            @else
                <div class="flex gap-2"><a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm">Log in</a><a href="{{ route('register') }}" class="rounded-md bg-emerald-400 px-3 py-2 text-sm font-semibold text-slate-950">Register</a></div>
            @endauth
        </div>
    </nav>
    <main class="mx-auto max-w-7xl px-4 py-8">
        @if(session('success'))<div class="mb-6 rounded-md border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mb-6 rounded-md border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</body>
</html>
