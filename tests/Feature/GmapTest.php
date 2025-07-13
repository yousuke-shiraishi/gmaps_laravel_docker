<?php

use App\Models\User;


it('creates a new gmap location', function () {
    // テスト用ユーザーをFactoryで作成
    $user = User::factory()->create();

    // APIに対して認証ユーザーでPOSTリクエスト
    $this->actingAs($user, 'api')
         ->postJson('/api/gmaps', [
             'title'     => 'Test Location',
             'comment'   => 'Nice place',
             'latitude'  => 35.0,
             'longitude' => 139.0,
         ])
         // 201 Created が返ってくることを検証
         ->assertCreated()
         // レスポンスJSONに"title"フィールドが含まれていることを検証
         ->assertJsonFragment(['title' => 'Test Location']);
});
