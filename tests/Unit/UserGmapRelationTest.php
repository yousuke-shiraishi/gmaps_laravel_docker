<?php
use App\Models\User;
use App\Models\Gmap;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 今回はメールアドレスとマジックワードでGmapオブジェクトを取ってこれるのでhasManyは必要ないが保守する時に楽になる
it('defines a hasMany relation to Gmap for User', function () {
    $user = new User();

    // リレーションオブジェクトを取得
    $relation = $user->gmaps();

    // HasMany であることをアサート
    expect($relation)->toBeInstanceOf(HasMany::class);
});

it('can retrieve related gmaps for a user', function () {
    $user = User::factory()->create();

    Gmap::factory()->count(2)->create([
        'user_id' => $user->id,
    ]);

    expect($user->gmaps()->get())->toHaveCount(2)
                                 ->each->toBeInstanceOf(Gmap::class);

    expect($user->gmaps)->toHaveCount(2);
});
