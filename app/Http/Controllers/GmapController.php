<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGmapRequest;
use App\Http\Requests\PublicSearchRequest;
use App\Http\Requests\PrivateSearchRequest;
use App\Models\Gmap;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index','publicSearch']);
    }

    /**
     * 公開中のGmap一覧を取得
     *
     * @group Gmap
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "title": "My Place",
     *     "latitude": 35.6895,
     *     "longitude": 139.6917,
     *     "magic_word": "",
     *     "created_at": "2025-07-09T00:00:00.000000Z"
     *   }
     * ]
     */
    public function index()
    {
        $gmaps = Gmap::query()
            // magic_word が '' または NULL
            ->where(function($q) {
                $q->where('magic_word', '')
                ->orWhereNull('magic_word');
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($gmaps);
    }

    /**
     * Gmap を登録（画像付き）
     *
     * @group Gmap
     * @authenticated
     *
     * @bodyParam title string required タイトル Example: My Home
     * @bodyParam comment string コメント Example: 東京の拠点
     * @bodyParam latitude float required 緯度 Example: 35.6895
     * @bodyParam longitude float required 経度 Example: 139.6917
     * @bodyParam picture file 画像ファイル（任意）
     * @bodyParam magic_word string 合言葉（任意） Example: secretword
     *
     * @response 201 {
     *   "id": 1,
     *   "title": "My Home",
     *   "latitude": 35.6895,
     *   "longitude": 139.6917,
     *   "user_id": 1,
     *   "picture_path": "gmaps/photo.jpg"
     * }
     */
    public function store(StoreGmapRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('gmaps', 'public');
            $validated['picture_path'] = $path;
        }

        $gmap = new Gmap($validated);
        $gmap->user_id = auth()->id();
        $gmap->save();

        return response()->json($gmap, 201);
    }

    /**
     * Gmap を削除（オーナーのみ許可）
     *
     * @group Gmap
     * @authenticated
     *
     * @urlParam gmap int required Gmap ID Example: 1
     *
     * @response 200 {
     *   "message": "削除されました"
     * }
     *
     * @response 403 {
     *   "message": "権限がありません"
     * }
     */
    public function destroy(Gmap $gmap)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => '認証エラー'], 401);
        }

        Log::debug('GmapController@destroy', [
            'gmap_user_id' => $gmap->user_id,
            'token_user_id'=> $user->id,
        ]);

        if ($gmap->user_id != $user->id) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        $gmap->delete();

        return response()->json(['message' => '削除されました'], 200);
    }

    /**
     * 公開検索（ユーザー名＋誕生日）
     *
     * @group Gmap
     *
     * @bodyParam username string required ユーザー名 Example: yosuke
     * @bodyParam birth string required 生年月日（YYYY-MM-DD） Example: 1980-01-01
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "title": "Tokyo Tower",
     *     "magic_word": ""
     *   }
     * ]
     */
public function publicSearch(PublicSearchRequest $request)
{
    $validated = $request->validated();

    $gmaps = Gmap::whereHas('user', function ($query) use ($validated) {
            $query->where('username', $validated['username'])
                  ->where('birth',    $validated['birth']);
        })
        ->where(function($q) {
            $q->where('magic_word', '')
              ->orWhereNull('magic_word');
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($gmaps, 200);
}


    /**
     * 非公開検索（email＋合言葉）
     *
     * @group Gmap
     * @authenticated
     *
     * @bodyParam email string required メールアドレス Example: yosuke@example.com
     * @bodyParam magic_word string required 合言葉 Example: secretword
     *
     * @response 200 [
     *   {
     *     "id": 2,
     *     "title": "秘密の場所",
     *     "magic_word": "5ebe2294ecd0e0f08eab7690d2a6ee69"
     *   }
     * ]
     */
    public function privateSearch(PrivateSearchRequest $request)
    {
        $email = $request->validated()['email'];
        $magicWord = $request->validated()['magic_word'];
        $magicWordHash = md5($magicWord);

        $gmaps = Gmap::whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->where('magic_word', $magicWordHash)
            ->where('magic_word', '!=', '')
            ->get();

        return response()->json($gmaps);
    }
}
