@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="mx-auto max-w-md border border-white/10 bg-slate-900/60 p-6"><h1 class="text-2xl font-bold">Welcome back</h1><form class="mt-6 space-y-4" method="POST" action="{{ route('login') }}">@csrf
<label class="block text-sm">Email<input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Password<input type="password" name="password" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember">Remember me</label>
<button class="w-full rounded-md bg-emerald-400 py-3 font-bold text-slate-950">Log in</button></form></div>
@endsection
