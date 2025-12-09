import { usePage, router } from '@inertiajs/react';
import { useState, useCallback } from 'react';

/**
 * Hook robusto para sincronización de configuraciones del sistema
 * Maneja estado local, props de Inertia y persistencia en backend
 */
export const useSystemSettings = () => {
    const { props } = usePage();
    const systemSettings = props.systemSettings || props.auth?.systemSettings || {};
    const [syncError, setSyncError] = useState(null);
    const [isSyncing, setSyncing] = useState(false);

    /**
     * Actualizar una configuración específica en el backend
     * @param {string} section - Sección (seguridad, notificaciones, etc)
     * @param {object} data - Datos a guardar
     * @param {string} endpoint - Ruta del backend
     * @returns {Promise<object>}
     */
    const updateSetting = useCallback(async (section, data, endpoint) => {
        try {
            setSyncing(true);
            setSyncError(null);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                throw new Error('Token CSRF no encontrado');
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Error desconocido' }));
                throw new Error(errorData.message || `Error ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                setSyncError(null);
                return {
                    success: true,
                    data: result.data,
                    message: result.message
                };
            } else {
                const errorMsg = result.message || 'Error al guardar configuración';
                setSyncError(errorMsg);
                throw new Error(errorMsg);
            }

        } catch (error) {
            const errorMsg = error.message || 'Error de conexión';
            setSyncError(errorMsg);
            console.error(`Error en updateSetting (${section}):`, error);
            
            return {
                success: false,
                error: errorMsg,
                message: errorMsg
            };
        } finally {
            setSyncing(false);
        }
    }, []);

    /**
     * Actualizar múltiples configuraciones de una sección
     */
    const updateSection = useCallback(async (section, formData, endpoint) => {
        return updateSetting(section, formData, endpoint);
    }, [updateSetting]);

    /**
     * Obtener una configuración específica con fallback
     */
    const getSetting = useCallback((key, defaultValue = null) => {
        return systemSettings[key] !== undefined ? systemSettings[key] : defaultValue;
    }, [systemSettings]);

    /**
     * Verificar si una configuración está habilitada
     */
    const isSectionEnabled = useCallback((section) => {
        const sectionKey = `${section}_enabled`;
        return systemSettings[sectionKey] !== false;
    }, [systemSettings]);

    /**
     * Limpiar errores de sincronización
     */
    const clearSyncError = useCallback(() => {
        setSyncError(null);
    }, []);

    return {
        // Configuraciones del sistema (read-only, source of truth)
        systemSettings,
        
        // Información del logo
        logoPath: systemSettings?.logo_path || '/images/sena-logo.png',
        systemName: systemSettings?.system_name || 'Sistema SENA',
        
        // Color principal
        primaryColor: systemSettings?.primary_color || 'green',
        colorScheme: systemSettings?.color_scheme || 'green-600',
        
        // Idioma y tema
        locale: systemSettings?.locale || systemSettings?.language || 'es',
        theme: systemSettings?.theme || 'light',
        
        // Configuraciones de seguridad
        securitySettings: {
            password_min_length: systemSettings?.password_min_length || 8,
            password_require_uppercase: systemSettings?.password_require_uppercase !== false,
            password_require_numbers: systemSettings?.password_require_numbers !== false,
            password_require_special: systemSettings?.password_require_special || false,
            password_expiration_days: systemSettings?.password_expiration_days || 90,
            max_login_attempts: systemSettings?.max_login_attempts || 5,
        },

        // Configuraciones de notificaciones
        notificationSettings: {
            color_success: systemSettings?.notification_color_success || 'green',
            color_error: systemSettings?.notification_color_error || 'red',
            color_warning: systemSettings?.notification_color_warning || 'yellow',
            color_info: systemSettings?.notification_color_info || 'blue',
            duration: systemSettings?.notification_duration || 5,
            position: systemSettings?.notification_position || 'top-right',
            sound: systemSettings?.notification_sound || false,
            animation: systemSettings?.notification_animation || 'slide',
        },

        // Horarios
        schedules: {
            manana_inicio: systemSettings?.manana_inicio || '06:00',
            manana_fin: systemSettings?.manana_fin || '11:45',
            tarde_inicio: systemSettings?.tarde_inicio || '12:00',
            tarde_fin: systemSettings?.tarde_fin || '17:45',
            noche_inicio: systemSettings?.noche_inicio || '18:00',
            noche_fin: systemSettings?.noche_fin || '21:45',
        },

        // Estados de sincronización
        isSyncing,
        syncError,
        
        // Métodos públicos
        updateSetting,        // Actualizar una sección completa
        updateSection,        // Alias para updateSetting
        getSetting,          // Obtener valor con fallback
        isSectionEnabled,    // Verificar si sección está habilitada
        clearSyncError,      // Limpiar errores

        // Utilidades
        hasCustomLogo: () => !!(systemSettings?.logo_path && systemSettings.logo_path !== '/images/sena-logo.png'),
        
        // Para refresh manual si es necesario
        refresh: () => router.visit(route('admin.configuraciones'), { 
            preserveState: true,
            preserveScroll: true 
        })
    };
};

export default useSystemSettings;