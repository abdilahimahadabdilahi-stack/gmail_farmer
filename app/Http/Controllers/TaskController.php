<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->latest()->paginate(10);

        return view('dashboard.tasks', compact('tasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_email' => ['required', 'email', 'max:255'],
            'account_code' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->tasks()->create([
            'account_email' => $validated['account_email'],
            'account_code' => $validated['account_code'],
            'reward_amount' => 0.25,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Task details submitted for review.');
    }

    public function edit(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        abort_unless($task->status === 'pending', 409, 'Only pending tasks can be edited.');

        return view('dashboard.task-edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        abort_unless($task->status === 'pending', 409, 'Only pending tasks can be updated.');

        $validated = $request->validate([
            'proof_url' => ['required', 'url', 'max:2048', 'unique:tasks,proof_url,' . $task->id],
        ]);

        $task->update(['proof_url' => $validated['proof_url']]);

        return redirect()->route('tasks.index')->with('success', 'Task proof updated.');
    }

    public function destroy(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        abort_unless($task->status === 'pending', 409, 'Approved or rejected tasks cannot be deleted.');

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Pending task deleted.');
    }
}
