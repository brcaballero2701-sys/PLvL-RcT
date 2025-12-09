<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class ThemeController extends Controller
{
    /**
     * Obtener la configuración de tema actual del usuario
     */
    public function getCurrent()
    {
        $theme = auth()->user() 
            ? auth()->user()->theme_preference ?? 'light'
            : 'light';

        return response()->json([
            'theme' => $theme,
            'isDark' => $theme === 'dark',
        ]);
    }

    /**
     * Cambiar el tema del usuario (light/dark)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system',
        ]);

        if (auth()->check()) {
            auth()->user()->update([
                'theme_preference' => $request->theme,
            ]);

            // Registrar en auditoría
            \App\Models\AuditLog::logAction(
                'theme_changed',
                'User',
                auth()->id(),
                null,
                ['theme' => $request->theme],
                "Tema del sistema cambiado a: {$request->theme}"
            );
        }

        // Guardar en sesión también para usuarios no autenticados
        session(['theme_preference' => $request->theme]);

        return response()->json([
            'success' => true,
            'theme' => $request->theme,
            'message' => 'Tema actualizado correctamente',
        ]);
    }

    /**
     * Obtener la configuración de accesibilidad
     */
    public function getAccessibility()
    {
        $user = auth()->user();

        return response()->json([
            'high_contrast' => $user?->high_contrast ?? false,
            'reduce_motion' => $user?->reduce_motion ?? false,
            'font_size' => $user?->font_size ?? 'normal',
            'line_spacing' => $user?->line_spacing ?? 'normal',
        ]);
    }

    /**
     * Actualizar preferencias de accesibilidad
     */
    public function updateAccessibility(Request $request)
    {
        $request->validate([
            'high_contrast' => 'boolean',
            'reduce_motion' => 'boolean',
            'font_size' => 'in:small,normal,large,extra-large',
            'line_spacing' => 'in:tight,normal,loose,extra-loose',
        ]);

        if (auth()->check()) {
            auth()->user()->update([
                'high_contrast' => $request->boolean('high_contrast', false),
                'reduce_motion' => $request->boolean('reduce_motion', false),
                'font_size' => $request->font_size ?? 'normal',
                'line_spacing' => $request->line_spacing ?? 'normal',
            ]);

            \App\Models\AuditLog::logAction(
                'accessibility_changed',
                'User',
                auth()->id(),
                null,
                $request->all(),
                'Preferencias de accesibilidad actualizadas'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferencias de accesibilidad actualizadas',
        ]);
    }
}
