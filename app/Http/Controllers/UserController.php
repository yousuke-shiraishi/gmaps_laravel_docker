<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['store']);
    }

    /**
     * ユーザー一覧を取得
     *
     * @group User Management
     * @authenticated
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "name": "Yosuke Shiraishi",
     *     "email": "yosuke@example.com"
     *   }
     * ]
     */
    public function index()
    {
        return response()->json(User::all());
    }

    /**
     * ユーザー登録
     *
     * @group User Management
     *
     * @bodyParam name string required 名前 Example: Yosuke Shiraishi
     * @bodyParam username string required ユーザー名（ログインに使用） Example: yosuke
     * @bodyParam email string required メールアドレス Example: yosuke@example.com
     * @bodyParam password string required パスワード（8文字以上） Example: password123
     * @bodyParam birth string required 生年月日（YYYY-MM-DD） Example: 1980-01-01
     *
     * @response 201 {
     *   "message": "Userの生成が成功",
     *   "token": "jwt.token.here",
     *   "user": {
     *     "id": 1,
     *     "name": "Yosuke Shiraishi",
     *     "username": "yosuke",
     *     "email": "yosuke@example.com",
     *     "birth": "1980-01-01"
     *   }
     * }
     */
    public function store(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'birth'    => $data['birth'],
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Userの生成が成功',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'birth'    => $user->birth,
            ],
        ], 201);
    }

    /**
     * 指定したユーザー情報を取得
     *
     * @group User Management
     * @authenticated
     *
     * @urlParam id int required ユーザーID Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Yosuke Shiraishi",
     *   "email": "yosuke@example.com"
     * }
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * ユーザー情報を更新
     *
     * @group User Management
     * @authenticated
     *
     * @urlParam id int required ユーザーID Example: 1
     * @bodyParam name string 名前 Example: Yosuke Updated
     * @bodyParam username string ユーザー名 Example: yosuke-updated
     * @bodyParam email string メールアドレス Example: yosuke-updated@example.com
     * @bodyParam password string 新しいパスワード Example: newpass123
     * @bodyParam birth string 生年月日（YYYY-MM-DD） Example: 1981-01-01
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Yosuke Updated",
     *   "email": "yosuke-updated@example.com"
     * }
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $updates = $request->only(['name','username','email','birth']);
        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->input('password'));
        }

        $user->update($updates);

        return response()->json($user);
    }

    /**
     * ユーザーを削除
     *
     * @group User Management
     * @authenticated
     *
     * @urlParam id int required ユーザーID Example: 1
     *
     * @response 200 {
     *   "message": "User削除"
     * }
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User削除']);
    }
}
