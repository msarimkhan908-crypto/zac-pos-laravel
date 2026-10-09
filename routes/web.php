<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS — cashier and admin
    Route::get('/pos', function () {
        return view('pos.index');
    })->middleware('role:admin,cashier')->name('pos.index');

    // Orders — all roles
    Route::get('/orders', function () {
        return view('orders.index');
    })->name('orders.index');

    // Admin-only sections
    Route::middleware('role:admin')->group(function () {
        Route::get('/products', function () {
            return view('products.index');
        })->name('products.index');

        Route::get('/products/create', function () {
            return view('products.form');
        })->name('products.create');

        Route::get('/products/{productId}/edit', function (string $productId) {
            return view('products.form', compact('productId'));
        })->name('products.edit');

        Route::post('/products/upload-image', [ProductImageController::class, 'store'])->name('products.upload-image');

        Route::get('/inventory', function () {
            return view('inventory.index');
        })->name('inventory.index');

        Route::get('/receipt-designer', function () {
            return view('receipt-designer.index');
        })->name('receipt-designer.index');

        Route::get('/reports', function () {
            return view('reports.index');
        })->name('reports.index');

        Route::get('/employees', function () {
            return view('employees.index');
        })->name('employees.index');

        Route::get('/settings', function () {
            return view('settings.index');
        })->name('settings.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dark mode toggle — stored per user
Route::post('/dark-mode', function () {
    $user = auth()->user();
    $user->dark_mode = ! $user->dark_mode;
    $user->save();
    return back();
})->middleware('auth')->name('dark-mode.toggle');

require __DIR__.'/auth.php';
