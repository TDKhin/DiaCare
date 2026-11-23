<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGlucoseReadingRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'value' => ['required', 'integer', 'between:20,600'],
            'context' => ['required', Rule::in(collect(config('glucose.time_slots'))->pluck('label')->all())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'activity_log_id' => ['nullable', 'integer', 'exists:activity_logs,id'],
        ];
    }
}
