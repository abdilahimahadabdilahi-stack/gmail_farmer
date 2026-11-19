<div class="mx-auto max-w-7xl px-4 py-10">
    @if (session('success'))
        <div class="mb-6 rounded-md border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-md border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-300">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[360px_1fr]">
        <!-- Foomka Enter gmail and password -->
        <div class="rounded-lg border border-white/10 bg-slate-900/70 p-6">
            <h2 class="text-xl font-bold text-white">Enter gmail and password</h2>
            <p class="mt-1 text-sm text-slate-400">Enter the newly created Gmail address and password to submit for review.</p>

            <form action="{{ route('user.tasks.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="gmail" class="block text-sm font-medium text-slate-300">Gmail</label>
                    <input
                        type="email"
                        id="gmail"
                        name="gmail"
                        value="{{ old('gmail') }}"
                        placeholder="example@gmail.com"
                        required
                        class="mt-1 w-full rounded-md border border-white/15 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-400 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required
                        class="mt-1 w-full rounded-md border border-white/15 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-400 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-emerald-300"
                >
                    Submit for review
                </button>
            </form>
        </div>

        <!-- Shaxda Submitted tasks -->
        <div>
            <h2 class="text-2xl font-bold text-white">Submitted tasks</h2>
            <div class="mt-4 overflow-x-auto rounded-lg border border-white/10 bg-slate-900/70">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/[.02] text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Gmail</th>
                            <th class="px-4 py-3">Reward</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-slate-200">
                        @forelse ($tasks as $task)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $task->gmail }}</td>
                                <td class="px-4 py-3 text-emerald-400">${{ number_format($task->reward ?? 0.25, 2) }}</td>
                                <td class="px-4 py-3 capitalize">{{ $task->status }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $task->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400">No tasks submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>