@extends('layouts.app')
@section('title', 'Edit task')
@section('content')
<div class="mx-auto max-w-xl border border-white/10 bg-slate-900/60 p-6"><h1 class="text-2xl font-bold">Edit task</h1><form class="mt-6 space-y-4" method="POST" action="{{ route('admin.tasks.update', $task) }}">@csrf @method('PUT')<label class="block text-sm">Proof URL<input type="url" name="proof_url" value="{{ old('proof_url', $task->proof_url) }}" required class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2"></label><label class="block text-sm">Rejection reason<textarea name="rejection_reason" class="mt-2 w-full rounded-md border border-white/10 bg-slate-950 px-3 py-2" rows="4">{{ old('rejection_reason', $task->rejection_reason) }}</textarea></label><button class="rounded-md bg-emerald-400 px-5 py-2.5 font-bold text-slate-950">Save changes</button></form></div>
@endsection
