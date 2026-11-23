<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlucosePhotoAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
