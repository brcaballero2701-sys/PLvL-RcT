<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSchedulesRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta solicitud
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     */
    public function rules(): array
    {
        return [
            'manana_inicio' => 'required|date_format:H:i|before:manana_fin',
            'manana_fin' => 'required|date_format:H:i|after:manana_inicio',
            'tarde_inicio' => 'required|date_format:H:i|before:tarde_fin',
            'tarde_fin' => 'required|date_format:H:i|after:tarde_inicio',
            'noche_inicio' => 'required|date_format:H:i|before:noche_fin',
            'noche_fin' => 'required|date_format:H:i|after:noche_inicio',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function messages(): array
    {
        return [
            'manana_inicio.required' => 'La hora de inicio de la mañana es requerida',
            'manana_inicio.date_format' => 'La hora de inicio debe estar en formato HH:mm',
            'manana_inicio.before' => 'La hora de inicio debe ser menor a la hora de fin',
            'manana_fin.required' => 'La hora de fin de la mañana es requerida',
            'manana_fin.date_format' => 'La hora de fin debe estar en formato HH:mm',
            'manana_fin.after' => 'La hora de fin debe ser mayor a la hora de inicio',
            
            'tarde_inicio.required' => 'La hora de inicio de la tarde es requerida',
            'tarde_inicio.date_format' => 'La hora de inicio debe estar en formato HH:mm',
            'tarde_inicio.before' => 'La hora de inicio debe ser menor a la hora de fin',
            'tarde_fin.required' => 'La hora de fin de la tarde es requerida',
            'tarde_fin.date_format' => 'La hora de fin debe estar en formato HH:mm',
            'tarde_fin.after' => 'La hora de fin debe ser mayor a la hora de inicio',
            
            'noche_inicio.required' => 'La hora de inicio de la noche es requerida',
            'noche_inicio.date_format' => 'La hora de inicio debe estar en formato HH:mm',
            'noche_inicio.before' => 'La hora de inicio debe ser menor a la hora de fin',
            'noche_fin.required' => 'La hora de fin de la noche es requerida',
            'noche_fin.date_format' => 'La hora de fin debe estar en formato HH:mm',
            'noche_fin.after' => 'La hora de fin debe ser mayor a la hora de inicio',
        ];
    }

    /**
     * Preparar los datos para validación
     */
    protected function prepareForValidation(): void
    {
        // Convertir datos si es necesario
        $this->merge([
            'manana_inicio' => $this->manana_inicio,
            'manana_fin' => $this->manana_fin,
            'tarde_inicio' => $this->tarde_inicio,
            'tarde_fin' => $this->tarde_fin,
            'noche_inicio' => $this->noche_inicio,
            'noche_fin' => $this->noche_fin,
        ]);
    }
}
