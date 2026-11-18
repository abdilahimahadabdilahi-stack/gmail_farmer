@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="mx-auto max-w-md border border-white/10 bg-slate-900/60 p-6"><h1 class="text-2xl font-bold">Create account</h1><form class="mt-6 space-y-4" method="POST" action="{{ route('register') }}">@csrf
<label class="block text-sm">Name<input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Username<input name="username" value="{{ old('username') }}" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Email<input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Referral code<input name="referral_code" value="{{ old('referral_code', request('ref')) }}" class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Password<input type="password" name="password" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<label class="block text-sm">Confirm password<input type="password" name="password_confirmation" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label>
<button class="w-full rounded-md bg-emerald-400 py-3 font-bold text-slate-950">Create account</button></form></div>
@endsection
