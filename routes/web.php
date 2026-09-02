<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\ReplyController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('threads.index'));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ブログ（未ログインでも閲覧可）
Route::resource('posts', PostController::class)->only(['index', 'show']);

// 掲示板（未ログインでも閲覧可）
Route::resource('threads', ThreadController::class)->only(['index', 'show']);

// ログイン必須
Route::middleware('auth')->group(function () {
    // ブログ
    Route::resource('posts', PostController::class)->except(['index', 'show']);

    // 掲示板
    Route::resource('threads', ThreadController::class)->only(['create', 'store', 'destroy']);
    Route::post('threads/{thread}/replies', [ReplyController::class, 'store'])->name('threads.replies.store');
    Route::delete('threads/{thread}/replies/{reply}', [ReplyController::class, 'destroy'])->name('threads.replies.destroy');

    // タスク管理（全操作がログイン必須）
    Route::resource('tasks', TaskController::class);

    // Stripe Checkout での商品購入（ログイン必須）
    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::post('checkout/{product}', [CheckoutController::class, 'checkout'])->name('checkout');

    // プロフィール
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

