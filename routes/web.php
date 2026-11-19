<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard.index', [
            'holdBalance' => $user->hold_balance,
            'recentTasks' => $user->tasks()->latest()->take(5)->get(),
            'recentWithdrawals' => $user->withdrawals()->latest()->take(5)->get(),
        ]);
    })->name('dashboard');

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
        Route::get('/{withdrawal}/edit', [WithdrawalController::class, 'edit'])->name('edit');
        Route::put('/{withdrawal}', [WithdrawalController::class, 'update'])->name('update');
        Route::delete('/{withdrawal}', [WithdrawalController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::get('/tasks', [AdminController::class, 'showTasks'])->name('tasks');
    Route::post('/tasks/{id}/approve', [AdminController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{id}/reject', [AdminController::class, 'rejectTask'])->name('tasks.reject');
    Route::get('/tasks/{task}/edit', [AdminController::class, 'editTask'])->name('tasks.edit');
    Route::put('/tasks/{task}', [AdminController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{task}', [AdminController::class, 'destroyTask'])->name('tasks.destroy');

    Route::get('/withdrawals', [AdminController::class, 'showWithdrawals'])->name('withdrawals');
    Route::post('/withdrawals/{id}/paid', [AdminController::class, 'markAsPaid'])->name('withdrawals.paid');
    Route::get('/withdrawals/{withdrawal}/edit', [WithdrawalController::class, 'editWithdrawal'])->name('withdrawals.edit');
    Route::put('/withdrawals/{withdrawal}', [WithdrawalController::class, 'updateWithdrawal'])->name('withdrawals.update');
    Route::delete('/withdrawals/{withdrawal}', [WithdrawalController::class, 'destroyWithdrawal'])->name('withdrawals.destroy');

    Route::get('/users', [AdminController::class, 'showUsers'])->name('users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
});