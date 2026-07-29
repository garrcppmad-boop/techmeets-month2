<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PostController;

Route::get('/items', [ItemController::class, 'index']);       // 一覧
Route::get('/items/{id}', [ItemController::class, 'show']);   // 1件取得
Route::post('/items', [ItemController::class, 'store']);      // 新規作成

Route::get('/posts', [PostController::class, 'index']);       // 投稿一覧
Route::post('/posts', [PostController::class, 'store']);      // 投稿の新規作成