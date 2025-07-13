<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 権限チェックを行うならここで
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'birth' => 'required|date',
        ];
    }
    /**
     * @return array<string, \Knuckles\Scribe\Extracting\ParamHelpers\BodyParameter>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'ユーザーの表示名',
                'example' => 'Yosuke Shiraishi',
            ],
            'username' => [
                'description' => 'ログインや公開検索に使用する名前',
                'example' => 'yosuke_dev',
            ],
            'email' => [
                'description' => 'メールアドレス',
                'example' => 'example@example.com',
            ],
            'password' => [
                'description' => 'パスワード',
                'example' => 'password123',
            ],
            'birth' => [
                'description' => '生年月日 (YYYY-MM-DD)',
                'example' => '1980-01-01',
            ],
        ];
    }

}
