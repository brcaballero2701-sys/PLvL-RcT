<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthController extends Controller
{
    /**
     * Mostrar la configuración de 2FA del usuario
     */
    public function show()
    {
        $twoFactor = auth()->user()->twoFactorAuth;
        
        return response()->json([
            'enabled' => $twoFactor?->enabled ?? false,
            'method' => $twoFactor?->method ?? 'email',
            'confirmed' => $twoFactor?->confirmed_at !== null,
            'backup_codes_remaining' => count($twoFactor?->backup_codes ?? []),
        ]);
    }

    /**
     * Habilitar 2FA por correo electrónico
     */
    public function enableEmail(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        
        // Crear o actualizar registro de 2FA
        $twoFactor = TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->id],
            [
                'enabled' => true,
                'method' => 'email',
                'backup_codes' => null,
            ]
        );

        // Generar códigos de respaldo
        $backupCodes = $twoFactor->generateBackupCodes();

        // Registrar en auditoría
        AuditLog::logAction(
            'security_change',
            'User',
            $user->id,
            null,
            ['2fa_method' => 'email', 'enabled' => true],
            'Usuario habilitó autenticación de dos factores por correo'
        );

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores habilitada',
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * Enviar código de verificación por correo
     */
    public function sendVerificationCode(Request $request)
    {
        $user = auth()->user();
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Guardar código en caché por 10 minutos
        cache()->put("2fa_code_{$user->id}", [
            'code' => $code,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ], 10 * 60);

        // Enviar correo (aquí iría Mail::send)
        // Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        AuditLog::logAction(
            '2fa_code_sent',
            'User',
            $user->id,
            null,
            null,
            'Código de 2FA enviado al correo'
        );

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación enviado a tu correo',
        ]);
    }

    /**
     * Verificar código de 2FA
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $user = auth()->user();
        $twoFactor = $user->twoFactorAuth;

        // Verificar si la cuenta está bloqueada
        if ($twoFactor && $twoFactor->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta bloqueada temporalmente. Intenta más tarde.',
            ], 429);
        }

        $cachedData = cache()->get("2fa_code_{$user->id}");

        if (!$cachedData || $cachedData['code'] !== $request->code) {
            $twoFactor?->incrementFailedAttempts();
            
            AuditLog::logAction(
                '2fa_verification_failed',
                'User',
                $user->id,
                null,
                null,
                'Intento fallido de verificación 2FA',
                'failed'
            );

            return response()->json([
                'success' => false,
                'message' => 'Código inválido',
            ], 401);
        }

        // Código válido - confirmar 2FA
        $twoFactor->resetFailedAttempts();
        $twoFactor->update(['confirmed_at' => now()]);
        
        cache()->forget("2fa_code_{$user->id}");

        AuditLog::logAction(
            '2fa_verified',
            'User',
            $user->id,
            null,
            ['method' => $twoFactor->method],
            '2FA verificado correctamente'
        );

        return response()->json([
            'success' => true,
            'message' => '¡Verificación exitosa!',
        ]);
    }

    /**
     * Deshabilitar 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        $twoFactor = $user->twoFactorAuth;

        if ($twoFactor) {
            $twoFactor->update([
                'enabled' => false,
                'confirmed_at' => null,
                'backup_codes' => null,
            ]);

            AuditLog::logAction(
                'security_change',
                'User',
                $user->id,
                ['2fa_enabled' => true],
                ['2fa_enabled' => false],
                'Autenticación de dos factores deshabilitada'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores deshabilitada',
        ]);
    }

    /**
     * Regenerar códigos de respaldo
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $twoFactor = auth()->user()->twoFactorAuth;
        
        if (!$twoFactor || !$twoFactor->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Primero debes habilitar 2FA',
            ], 400);
        }

        $backupCodes = $twoFactor->generateBackupCodes();

        AuditLog::logAction(
            'backup_codes_regenerated',
            'User',
            auth()->id(),
            null,
            null,
            'Códigos de respaldo regenerados'
        );

        return response()->json([
            'success' => true,
            'backup_codes' => $backupCodes,
        ]);
    }
}
