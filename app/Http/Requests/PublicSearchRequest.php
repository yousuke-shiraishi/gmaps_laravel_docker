<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'birth' => 'required|date',
        ];
    }
}
