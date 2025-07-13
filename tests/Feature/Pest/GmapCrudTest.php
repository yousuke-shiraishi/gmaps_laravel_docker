<?php

use App\Models\User;
use App\Models\Gmap;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;







it('creates a new gmap location with an image and stores it', function () {

    $me  = User::factory()->create();
    $token = JWTAuth::fromUser($me);

    $file = UploadedFile::fake()->image('pin.jpg');

    $data = Gmap::factory()->raw(['magic_word' => null]);

    $payload = array_merge($data, ['picture' => $file]);

    $response = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/gmaps', $payload)
        ->assertCreated()
        ->assertJsonStructure([
            'id', 'title', 'comment', 'latitude', 'longitude', 'picture_url', 'created_at'
        ]);

    Storage::disk('public')
        ->assertExists('gmaps/' . $file->hashName());

    $this->assertDatabaseHas('gmaps', [
        'id'      => $response->json('id'),
        'user_id' => $me->id,
    ]);
});


it('only allows the owner to delete a gmap object', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    $gmap = Gmap::factory()->create(['user_id' => $me->id]);

    $tokenOther = JWTAuth::fromUser($other);
    $this
        ->withHeader('Authorization', "Bearer {$tokenOther}")
        ->deleteJson("/api/gmaps/{$gmap->id}")
        ->assertStatus(403);

    $tokenOwner = JWTAuth::fromUser($me);
    $this
        ->withHeader('Authorization', "Bearer {$tokenOwner}")
        ->deleteJson("/api/gmaps/{$gmap->id}")
        ->assertOk()
        ->assertJson(['message' => '削除されました']);

    $this->assertDatabaseMissing('gmaps', ['id' => $gmap->id]);
});


it('lists only public gmaps in index', function () {
    Gmap::factory()->count(3)->create(['magic_word' => null]);
    Gmap::factory()->count(2)->create(['magic_word' => 'secret']);

    $this
        ->getJson('/api/gmaps')
        ->assertOk()
        ->assertJsonCount(3);
});


it('public search by username and birth', function () {
    $me = User::factory()->create();
    $publicGmap = Gmap::factory()->create([
        'user_id'    => $me->id,
        'magic_word' => null,
    ]);
    Gmap::factory()->create([
        'user_id'    => $me->id,
        'magic_word' => '秘密の言葉',
    ]);

    $this
        ->getJson('/api/gmaps/public-search?username='. $me->username .'&birth='. $me->birth)
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $publicGmap->id]);
});


it('private search with valid magic_word and email', function () {
    $me  = User::factory()->create();
    $token = JWTAuth::fromUser($me);

    Gmap::factory()->create([
        'user_id'    => $me->id,
        'magic_word' => null,
    ]);
    $secret  = '秘密の言葉';
    $private = Gmap::factory()->create(['user_id' => $me->id]);
    $private->magic_word = $secret;
    $private->save();

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/gmaps/private-search?email='. $me->email .'&magic_word='. $secret)
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $private->id]);
});



it('fails to delete a gmap when not authenticated', function () {
    $gmap = Gmap::factory()->create();

    $this
        // これで auth:api などのミドルウェアをスキップ
        ->withoutMiddleware()
        ->deleteJson("/api/gmaps/{$gmap->id}")
        ->assertStatus(401)
        // コントローラの catch が返すメッセージ
        ->assertExactJson(['message' => '認証エラー']);
});

it('returns 403 when deleting a gmap owned by someone else', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $gmap  = Gmap::factory()->create(['user_id' => $owner->id]);
    $token = JWTAuth::fromUser($other);

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/gmaps/{$gmap->id}")
        ->assertForbidden()
        ->assertJson(['message' => '権限がありません']);
});

