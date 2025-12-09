<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSecuritySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'password_min_length' => [
                'required',
                'integer',
                'min:6',
                'max:20',
                function ($attribute, $value, $fail) {
                    // Regla de negocio: si require_special=true, min_length >= 10
                    if ($this->boolean('password_require_special') && $value < 10) {
                        $fail('La longitud mínima debe ser al menos 10 caracteres si se requieren caracteres especiales.');
                    }
                }
            ],
            'password_require_uppercase' => 'nullable|boolean',
            'password_require_numbers' => 'nullable|boolean',
            'password_require_special' => 'nullable|boolean',
            'password_expiration_days' => 'required|integer|min:0|max:365',
        ];
    }

    public function messages(): array
    {
        return [
            'password_min_length.required' => 'La longitud mínima es requerida',
            'password_min_length.integer' => 'Debe ser un número entero',
            'password_min_length.min' => 'Mínimo 6 caracteres',
            'password_min_length.max' => 'Máximo 20 caracteres',
            
            'password_expiration_days.required' => 'Los días de expiración son requeridos',
            'password_expiration_days.integer' => 'Debe ser un número entero',
            'password_expiration_days.min' => 'No puede ser negativo',
            'password_expiration_days.max' => 'Máximo 365 días',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'password_require_uppercase' => $this->boolean('password_require_uppercase'),
            'password_require_numbers' => $this->boolean('password_require_numbers'),
            'password_require_special' => $this->boolean('password_require_special'),
        ]);
    }
}
