<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [BudgetController::class, 'index'])->name('dashboard');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::patch('/budgets/{id}/status', [BudgetController::class, 'updateStatus'])->name('budgets.updateStatus');
});

require __DIR__ . '/auth.php';
