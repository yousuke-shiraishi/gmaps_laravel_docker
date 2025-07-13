<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GmapController;
use App\Http\Controllers\UserController;

// ──────────────────────────────────────
// 認証不要ルート
// ──────────────────────────────────────
// ログイン
Route::post('/login',   [AuthController::class, 'login']);
// 新規ユーザー登録 (store) はここで明示的に定義
Route::post('/users',   [UserController::class, 'store']);

// ──────────────────────────────────────
// 認証済みユーザー専用ルート
// ──────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    // トークンリフレッシュ／ログアウト
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout',  [AuthController::class, 'logout']);

    // 認証ユーザー情報取得
    Route::get('/user', function () {
        return auth()->user();
    });

    // ユーザー管理 (index, show, update, destroy)
    Route::apiResource('users', UserController::class)
         ->except(['store']);

    // Gmap 非公開系
    Route::get('gmaps/private-search', [GmapController::class, 'privateSearch']);
    Route::apiResource('gmaps', GmapController::class)
         ->only(['store', 'destroy']);
});

// ──────────────────────────────────────
// 認証不要：公開系 Gmap、ヘルスチェック
// ──────────────────────────────────────
Route::get('gmaps',               [GmapController::class, 'index']);
Route::get('gmaps/public-search', [GmapController::class, 'publicSearch']);
Route::get('/health', fn() => response()->json(['status' => 'OK']));
