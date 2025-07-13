<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrivateSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'magic_word' => 'required|string',
        ];
    }
}
