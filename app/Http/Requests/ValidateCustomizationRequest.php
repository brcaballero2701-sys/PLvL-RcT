<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCustomizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'primary_color' => 'required|string|in:green,blue,indigo,purple,red,orange,yellow,teal,cyan,gray,slate,stone',
            'language' => 'required|in:es,en,fr,pt',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color.required' => 'El color primario es requerido',
            'primary_color.in' => 'El color seleccionado es inválido',
            
            'language.required' => 'El idioma es requerido',
            'language.in' => 'El idioma seleccionado es inválido. Opciones: es, en, fr, pt',
            
            'logo.image' => 'El archivo debe ser una imagen',
            'logo.mimes' => 'Formatos permitidos: JPEG, PNG, JPG, SVG, WebP',
            'logo.max' => 'El archivo no puede exceder 2MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Validar que al menos uno de los campos esté presente
        if (!$this->filled('primary_color') && !$this->filled('language') && !$this->hasFile('logo')) {
            $this->merge([]);
        }
    }
}
