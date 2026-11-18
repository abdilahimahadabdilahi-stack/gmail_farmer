<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = $request->user()->withdrawals()->latest()->paginate(10);

        return view('dashboard.withdrawals', compact('withdrawals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.30'],
            'payment_method' => ['required', Rule::in(['Zaad', 'Edahab', 'EVC Plus'])],
            'phone_number' => ['required', 'string', 'regex:/^\d{9,12}$/'],
        ]);

        $prefixes = ['Zaad' => '063', 'Edahab' => '065', 'EVC Plus' => '061'];
        $requiredPrefix = $prefixes[$validated['payment_method']];

        if (!str_starts_with($validated['phone_number'], $requiredPrefix)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone_number' => "{$validated['payment_method']} requires a phone number starting with {$requiredPrefix}."
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $user = $request->user()->lockForUpdate()->first();
            $amount = (float) $validated['amount'];

            if ((float) $user->active_balance < $amount) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => 'Insufficient active balance.'
                ]);
            }

            $user->decrement('active_balance', $amount);
            $user->withdrawals()->create([
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'phone_number' => $validated['phone_number'],
                'status' => 'pending',
            ]);
        });

        return back()->with('success', 'Withdrawal request submitted.');
    }

    public function edit(Request $request, Withdrawal $withdrawal)
    {
        abort_unless($withdrawal->user_id === $request->user()->id, 403);
        abort_unless($withdrawal->status === 'pending', 409, 'Only pending withdrawals can be edited.');

        return view('dashboard.withdrawal-edit', compact('withdrawal'));
    }

    public function update(Request $request, Withdrawal $withdrawal)
    {
        abort_unless($withdrawal->user_id === $request->user()->id, 403);
        abort_unless($withdrawal->status === 'pending', 409, 'Only pending withdrawals can be updated.');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.30'],
            'payment_method' => ['required', Rule::in(['Zaad', 'Edahab', 'EVC Plus'])],
            'phone_number' => ['required', 'string', 'regex:/^\d{9,12}$/'],
        ]);

        $prefixes = ['Zaad' => '063', 'Edahab' => '065', 'EVC Plus' => '061'];
        if (!str_starts_with($validated['phone_number'], $prefixes[$validated['payment_method']])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone_number' => "{$validated['payment_method']} requires a phone number starting with {$prefixes[$validated['payment_method']]}."
            ]);
        }

        DB::transaction(function () use ($request, $withdrawal, $validated) {
            $user = $request->user()->lockForUpdate()->first();
            $oldAmount = (float) $withdrawal->amount;
            $newAmount = (float) $validated['amount'];
            $difference = $newAmount - $oldAmount;

            if ($difference > 0 && (float) $user->active_balance < $difference) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => 'Insufficient active balance.'
                ]);
            }

            if ($difference > 0) {
                $user->decrement('active_balance', $difference);
            } elseif ($difference < 0) {
                $user->increment('active_balance', abs($difference));
            }

            $withdrawal->update($validated);
        });

        return redirect()->route('withdrawals.index')->with('success', 'Withdrawal updated.');
    }

    public function destroy(Request $request, Withdrawal $withdrawal)
    {
        abort_unless($withdrawal->user_id === $request->user()->id, 403);
        abort_unless($withdrawal->status === 'pending', 409, 'Paid or rejected withdrawals cannot be deleted.');

        DB::transaction(function () use ($request, $withdrawal) {
            $user = $request->user()->lockForUpdate()->first();
            $user->increment('active_balance', $withdrawal->amount);
            $withdrawal->delete();
        });

        return redirect()->route('withdrawals.index')->with('success', 'Pending withdrawal cancelled and balance restored.');
    }
}
