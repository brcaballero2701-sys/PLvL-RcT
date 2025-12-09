<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class ValidateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?? 'NULL'),
            'password' => $userId ? 'nullable|' . Rules\Password::defaults() : 'required|' . Rules\Password::defaults(),
            'role' => 'required|in:admin,guardia,user',
            'codigo_guardia' => 'nullable|string|max:20|unique:users,codigo_guardia,' . ($userId ?? 'NULL'),
            'ubicacion_asignada' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'El correo electrónico ya está registrado',
            'email.max' => 'El correo no puede exceder 255 caracteres',
            
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            
            'role.required' => 'El rol es requerido',
            'role.in' => 'El rol seleccionado es inválido',
            
            'codigo_guardia.max' => 'El código de guardia no puede exceder 20 caracteres',
            'codigo_guardia.unique' => 'El código de guardia ya está registrado',
            
            'ubicacion_asignada.max' => 'La ubicación no puede exceder 255 caracteres',
        ];
    }

    protected function prepareForValidation(): void
    {
        // No hacer nada específico, pero dejar el hook disponible
    }
}
