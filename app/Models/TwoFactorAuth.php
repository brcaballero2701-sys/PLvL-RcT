<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuth extends Model
{
    protected $fillable = [
        'user_id',
        'enabled',
        'method',
        'secret',
        'backup_codes',
        'confirmed_at',
        'last_used_at',
        'failed_attempts',
        'locked_until',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'backup_codes' => 'array',
        'confirmed_at' => 'datetime',
        'last_used_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si 2FA está habilitado y confirmado
     */
    public function isActive(): bool
    {
        return $this->enabled && $this->confirmed_at !== null;
    }

    /**
     * Verificar si la cuenta está bloqueada por intentos fallidos
     */
    public function isLocked(): bool
    {
        return $this->locked_until && now() < $this->locked_until;
    }

    /**
     * Generar códigos de respaldo (backup codes)
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        
        $this->backup_codes = $codes;
        $this->save();
        
        return $codes;
    }

    /**
     * Verificar si un código de respaldo es válido
     */
    public function verifyBackupCode(string $code): bool
    {
        if (!$this->backup_codes) {
            return false;
        }

        $code = strtoupper($code);
        $key = array_search($code, $this->backup_codes);

        if ($key !== false) {
            unset($this->backup_codes[$key]);
            $this->backup_codes = array_values($this->backup_codes);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Incrementar intentos fallidos
     */
    public function incrementFailedAttempts(): void
    {
        $this->failed_attempts++;

        if ($this->failed_attempts >= 5) {
            // Bloquear por 15 minutos después de 5 intentos fallidos
            $this->locked_until = now()->addMinutes(15);
        }

        $this->save();
    }

    /**
     * Resetear intentos fallidos
     */
    public function resetFailedAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->last_used_at = now();
        $this->save();
    }
}
