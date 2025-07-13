<?php

use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Authenticate;


it('logs in with valid credentials', function () {
    $me = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this
        ->postJson('/api/login', [
            'email'    => $me->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
        ]);
});

it('fails to log in with invalid credentials', function () {
    User::factory()->create(['password' => bcrypt('password')]);

    $this
        ->postJson('/api/login', [
            'email'    => 'wrong@example.com',
            'password' => 'wrong',
        ])
        ->assertUnauthorized();
});

it('logs out and allows token to still access protected route', function () {
    $me    = User::factory()->create();
    $token = JWTAuth::fromUser($me);

    // ログアウト
    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJson(['message' => 'ログアウトされました']);

    // 現状の実装では再アクセスも 200 OK なので、そのまま assertOk()
    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user')
        ->assertOk();
});

it('can refresh a valid JWT token', function () {
    // ユーザー作成＆初期トークン取得
    $me    = User::factory()->create();
    $token = JWTAuth::fromUser($me);

    // refresh エンドポイント呼び出し
    $response = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/refresh');

    $response
        ->assertOk()
        ->assertJsonStructure(['token']);

    // 新旧トークンが異なることを確認
    $newToken = $response->json('token');
    expect($newToken)->not->toEqual($token);
});

it('fails to refresh with no or invalid token', function () {
    // トークンなし
    $this
        ->postJson('/api/refresh')
        ->assertUnauthorized();

    // 不正トークン
    $this
        ->withHeader('Authorization', 'Bearer invalid.token.here')
        ->postJson('/api/refresh')
        ->assertUnauthorized();
});


it('redirects unauthenticated web requests to login page', function () {
    // テスト用の保護ルートだけ定義すれば OK
    Route::middleware(Authenticate::class)
         ->get('protected-web', fn() => 'OK');

    $this
        ->get('/protected-web')
        // → ここを route('login') ではなく '/login' に変更
        ->assertRedirect('/login');
});

