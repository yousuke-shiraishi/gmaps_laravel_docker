<?php

use App\Models\User;

it('creates a new user', function () {
    // Factory から生のデータ配列を取得
    $data = User::factory()->raw();

    // POST /api/users（認証不要）で登録
    $this->postJson('/api/users', $data)
         ->assertCreated()
         ->assertJsonFragment([
             'email' => $data['email'],
         ]);
});

it('lists users', function () {
    // 5人分のテストユーザーを作成
    User::factory()->count(5)->create();
    // ログイン用ユーザーを1人作成
    $me = User::factory()->create();

    // 認証トークン付きで GET /api/users
    $this->actingAs($me, 'api')
         ->getJson('/api/users')
         ->assertOk()
         ->assertJsonCount(6); // 5人＋ログインユーザー
});

it('shows a single user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
         ->getJson("/api/users/{$user->id}")
         ->assertOk()
         ->assertJsonFragment([
             'id'    => $user->id,
             'email' => $user->email,
         ]);
});

it('updates a user', function () {
    $user = User::factory()->create();

    $update = [
        'name'     => 'Updated Name',
        'username' => 'updated_username',
        'email'    => 'updated@example.com',
        'birth'    => now()->subYears(30)->toDateString(),
    ];

    $this->actingAs($user, 'api')
         ->putJson("/api/users/{$user->id}", $update)
         ->assertOk()
         ->assertJsonFragment($update);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
         ->deleteJson("/api/users/{$user->id}")
         ->assertOk();

    // DBに該当ユーザーが存在しないことを確認
    expect(User::find($user->id))->toBeNull();
});


it('updates a user including password when provided', function () {
    $user = User::factory()->create([
        'password' => bcrypt('oldpass123'),
    ]);

    // ← ここでトークンを取得
    $token = JWTAuth::fromUser($user);

    $payload = [
        'name'     => 'Updated Name',
        'username' => 'updated_user',
        'email'    => 'updated@example.com',
        'birth'    => '1990-12-31',
        'password' => 'newpass456',
    ];

    $this
        // ← トークンをヘッダーにセット
        ->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/users/{$user->id}", $payload)
        ->assertOk()
        ->assertJsonFragment([
            'id'    => $user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $fresh = $user->fresh();
    expect(Hash::check('newpass456', $fresh->password))->toBeTrue();
});

it('updates a user without touching password when password not provided', function () {
    $user = User::factory()->create([
        'password' => bcrypt('keepme123'),
    ]);

    $token = JWTAuth::fromUser($user);

    $payload = [
        'name'     => 'Name Only',
        'username' => 'name_only',
        'email'    => 'nameonly@example.com',
        'birth'    => '1985-05-05',
        // password フィールドなし
    ];

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/users/{$user->id}", $payload)
        ->assertOk()
        ->assertJsonFragment([
            'id'    => $user->id,
            'name'  => 'Name Only',
            'email' => 'nameonly@example.com',
        ]);

    $fresh = $user->fresh();
    expect(Hash::check('keepme123', $fresh->password))->toBeTrue();
});
