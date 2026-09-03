<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'glucose_target_min' => ['nullable', 'integer', 'between:40,300'],
            'glucose_target_max' => ['nullable', 'integer', 'between:40,400', 'gte:glucose_target_min'],
            'height_cm' => ['nullable', 'numeric', 'between:50,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:2,500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'doctor_name' => ['nullable', 'string', 'max:255'],
            'medical_notes' => ['nullable', 'string', 'max:5000'],
            'health_goals' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
