@extends('layouts.app')
@section('title', 'Pending tasks')
@section('content')
<h1 class="text-3xl font-bold">Pending tasks</h1>

<div class="mt-6 overflow-x-auto rounded-lg border border-white/10 bg-slate-900/70">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-white/10 bg-white/[.02] text-slate-300">
            <tr>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Details</th>
                <th class="px-4 py-3">Reward</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/10 text-slate-200">
            @forelse ($tasks as $task)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $task->account_email }}</td>
                    <td class="px-4 py-3">{{ $task->account_code }}</td>
                    <td class="px-4 py-3 text-emerald-400">${{ number_format($task->reward_amount, 2) }}</td>
                    <td class="px-4 py-3 capitalize">{{ $task->status }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-start gap-2">
                            <form method="POST" action="{{ route('admin.tasks.approve', $task) }}">
                                @csrf
                                <button class="rounded-md bg-emerald-400 px-3 py-2 text-xs font-bold text-slate-950">Approve</button>
                            </form>
                            <a href="{{ route('admin.tasks.edit', $task) }}" class="rounded-md border border-white/10 px-3 py-2 text-xs text-sky-300">Edit</a>
                            <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-md border border-red-400/20 px-3 py-2 text-xs text-red-300">Delete</button>
                            </form>
                            <form method="POST" action="{{ route('admin.tasks.reject', $task) }}" class="flex min-w-56 gap-2">
                                @csrf
                                <input
                                    type="text"
                                    name="rejection_reason"
                                    required
                                    minlength="5"
                                    maxlength="1000"
                                    placeholder="Rejection reason"
                                    aria-label="Rejection reason"
                                    class="min-w-0 flex-1 rounded-md border border-white/10 bg-slate-950 px-3 py-2 text-xs"
                                >
                                <button class="rounded-md border border-amber-400/20 px-3 py-2 text-xs text-amber-300">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-400">No pending tasks.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $tasks->links() }}</div>
@endsection
