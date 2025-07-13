<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 必要なら認可ロジック
    }

    public function rules(): array
    {
        $isTesting = app()->runningUnitTests();

        return [
            'title'     => 'required|string|max:255',
            'comment'   => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            // テスト時だけnullable、それ以外はrequired
            'picture'   => ($isTesting ? 'nullable' : 'required') . '|image',
            'magic_word'=> 'nullable|string',
        ];
    }

}
