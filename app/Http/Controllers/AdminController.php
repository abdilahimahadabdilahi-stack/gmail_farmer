<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'active_users' => User::where('role', 'user')->where('active_balance', '>', 0)->count(),
            'total_paid_out' => Withdrawal::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function showTasks()
    {
        $tasks = Task::where('status', 'pending')->with('user')->latest()->paginate(10);
        return view('admin.tasks', compact('tasks'));
    }

    public function approveTask($id)
    {
        DB::transaction(function () use ($id) {
            $task = Task::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($task->status !== 'pending', 409, 'Task has already been reviewed.');

            $task->update(['status' => 'approved', 'rejection_reason' => null]);
            $task->user()->lockForUpdate()->first()->increment('active_balance', 0.25);

            $submitter = $task->user()->lockForUpdate()->first();
            if ($submitter->referred_by) {
                $submitter->referrer()->lockForUpdate()->first()?->increment('active_balance', 0.025);
            }
        });

        return back()->with('success', 'Task approved and rewards credited.');
    }

    public function rejectTask(Request $request, $id)
    {
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'min:5', 'max:1000']]);

        DB::transaction(function () use ($validated, $id) {
            $task = Task::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($task->status !== 'pending', 409, 'Task has already been reviewed.');
            $task->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);
        });

        return back()->with('success', 'Task rejected.');
    }

    public function showWithdrawals()
    {
        $withdrawals = Withdrawal::where('status', 'pending')->with('user')->latest()->paginate(10);
        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function markAsPaid($id)
    {
        $withdrawal = Withdrawal::whereKey($id)->firstOrFail();
        abort_if($withdrawal->status !== 'pending', 409, 'Withdrawal has already been reviewed.');
        $withdrawal->update(['status' => 'paid']);

        return back()->with('success', 'Withdrawal marked as paid.');
    }

    public function showUsers()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.user-edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:30', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', Rule::in(['user', 'admin'])],
            'active_balance' => ['required', 'numeric', 'min:0'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = collect($validated)->except('password')->toArray();
        $data['username'] = '@' . ltrim($data['username'], '@');
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function destroyUser(User $user)
    {
        abort_if($user->id === auth()->id(), 409, 'You cannot delete your own account.');
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }

    public function editTask(Task $task)
    {
        return view('admin.task-edit', compact('task'));
    }

    public function updateTask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'proof_url' => ['required', 'url', 'max:2048', 'unique:tasks,proof_url,' . $task->id],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks')->with('success', 'Task updated.');
    }

    public function destroyTask(Task $task)
    {
        DB::transaction(function () use ($task) {
            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            if ($task->status === 'approved') {
                $submitter = $task->user()->lockForUpdate()->first();
                $submitter->decrement('active_balance', 0.25);
                if ($submitter->referred_by) {
                    $submitter->referrer()->lockForUpdate()->first()?->decrement('active_balance', 0.025);
                }
            }

            $task->delete();
        });

        return redirect()->route('admin.tasks')->with('success', 'Task deleted.');
    }

    public function editWithdrawal(Withdrawal $withdrawal)
    {
        return view('admin.withdrawal-edit', compact('withdrawal'));
    }

    public function updateWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.30'],
            'payment_method' => ['required', Rule::in(['Zaad', 'Edahab', 'EVC Plus'])],
            'phone_number' => ['required', 'string', 'regex:/^\d{9,12}$/'],
            'status' => ['required', Rule::in(['pending', 'paid', 'rejected'])],
        ]);

        $prefixes = ['Zaad' => '063', 'Edahab' => '065', 'EVC Plus' => '061'];
        if (!str_starts_with($validated['phone_number'], $prefixes[$validated['payment_method']])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone_number' => "{$validated['payment_method']} requires a phone number starting with {$prefixes[$validated['payment_method']]}."
            ]);
        }

        DB::transaction(function () use ($withdrawal, $validated) {
            $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $user = $locked->user()->lockForUpdate()->first();
            $oldStatus = $locked->status;
            $newStatus = $validated['status'];

            if ($oldStatus === 'pending' && $newStatus === 'rejected') {
                $user->increment('active_balance', $locked->amount);
            } elseif ($oldStatus === 'rejected' && in_array($newStatus, ['pending', 'paid'], true)) {
                if ((float) $user->active_balance < (float) $validated['amount']) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Insufficient balance to restore this withdrawal.']);
                }
                $user->decrement('active_balance', $validated['amount']);
            } elseif ($oldStatus === 'pending' && $newStatus === 'pending') {
                $difference = (float) $validated['amount'] - (float) $locked->amount;
                if ($difference > 0) {
                    if ((float) $user->active_balance < $difference) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Insufficient active balance.']);
                    }
                    $user->decrement('active_balance', $difference);
                } elseif ($difference < 0) {
                    $user->increment('active_balance', abs($difference));
                }
            }

            $locked->update($validated);
        });

        return redirect()->route('admin.withdrawals')->with('success', 'Withdrawal updated.');
    }

    public function destroyWithdrawal(Withdrawal $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'pending') {
                $locked->user()->lockForUpdate()->first()->increment('active_balance', $locked->amount);
            }
            $locked->delete();
        });

        return redirect()->route('admin.withdrawals')->with('success', 'Withdrawal deleted.');
    }
}
