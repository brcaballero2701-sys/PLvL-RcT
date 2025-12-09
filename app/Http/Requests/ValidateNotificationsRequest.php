<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'color_success' => 'required|in:green,blue,purple',
            'color_error' => 'required|in:red,orange',
            'color_warning' => 'required|in:yellow,orange',
            'color_info' => 'required|in:blue,gray',
            'duration' => 'required|integer|in:0,3,5,8',
            'position' => 'required|in:top-right,top-left,bottom-right,bottom-left',
            'sound' => 'nullable|boolean',
            'animation' => 'required|in:slide,fade,zoom',
        ];
    }

    public function messages(): array
    {
        return [
            'color_success.required' => 'El color de éxito es requerido',
            'color_success.in' => 'Color de éxito inválido',
            'color_error.required' => 'El color de error es requerido',
            'color_error.in' => 'Color de error inválido',
            'color_warning.required' => 'El color de advertencia es requerido',
            'color_warning.in' => 'Color de advertencia inválido',
            'color_info.required' => 'El color de información es requerido',
            'color_info.in' => 'Color de información inválido',
            'duration.required' => 'La duración es requerida',
            'duration.in' => 'Duración inválida. Opciones: 0, 3, 5, 8 segundos',
            'position.required' => 'La posición es requerida',
            'position.in' => 'Posición inválida',
            'animation.required' => 'La animación es requerida',
            'animation.in' => 'Animación inválida',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sound' => $this->boolean('sound'),
        ]);
    }
}
