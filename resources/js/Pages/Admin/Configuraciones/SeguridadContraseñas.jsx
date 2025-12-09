import { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import useSystemColors from '@/hooks/useSystemColors';
import useSystemSettings from '@/hooks/useSystemSettings';
import { 
    ArrowLeft, 
    Shield, 
    AlertTriangle,
    CheckCircle,
    RotateCcw,
    Save,
    Settings,
    Loader
} from 'lucide-react';

export default function SeguridadContraseñas() {
    const { colors } = useSystemColors();
    const { systemSettings, updateSetting, isSyncing, syncError, clearSyncError } = useSystemSettings();
    const [successMessage, setSuccessMessage] = useState('');
    const [validationErrors, setValidationErrors] = useState({});
    
    // Estado local sincronizado con systemSettings
    const [formData, setFormData] = useState({
        password_min_length: systemSettings?.password_min_length || 8,
        password_require_uppercase: systemSettings?.password_require_uppercase !== false,
        password_require_numbers: systemSettings?.password_require_numbers !== false,
        password_require_special: systemSettings?.password_require_special || false,
        password_expiration_days: systemSettings?.password_expiration_days || 90,
    });

    // Sincronizar cuando cambian los systemSettings
    useEffect(() => {
        setFormData({
            password_min_length: systemSettings?.password_min_length || 8,
            password_require_uppercase: systemSettings?.password_require_uppercase !== false,
            password_require_numbers: systemSettings?.password_require_numbers !== false,
            password_require_special: systemSettings?.password_require_special || false,
            password_expiration_days: systemSettings?.password_expiration_days || 90,
        });
    }, [systemSettings]);

    /**
     * Validaciones frontend completas
     */
    const validateForm = () => {
        const errors = {};

        // Validar longitud mínima
        if (formData.password_min_length < 6) {
            errors.password_min_length = 'Mínimo 6 caracteres requeridos';
        }
        if (formData.password_min_length > 20) {
            errors.password_min_length = 'Máximo 20 caracteres permitidos';
        }

        // Regla de negocio: si requiere especiales, mínimo 10 caracteres
        if (formData.password_require_special && formData.password_min_length < 10) {
            errors.password_min_length = 'La longitud mínima debe ser al menos 10 caracteres si se requieren caracteres especiales';
        }

        // Validar días de expiración
        if (formData.password_expiration_days < 0) {
            errors.password_expiration_days = 'No puede ser negativo';
        }
        if (formData.password_expiration_days > 365) {
            errors.password_expiration_days = 'Máximo 365 días permitidos';
        }

        return errors;
    };

    /**
     * Manejar cambios en inputs
     */
    const handleInputChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));
        // Limpiar error de este campo cuando el usuario lo cambia
        if (validationErrors[field]) {
            setValidationErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors[field];
                return newErrors;
            });
        }
        clearSyncError();
    };

    /**
     * Guardar configuraciones de seguridad
     */
    const handleSaveSecuritySettings = async (e) => {
        e.preventDefault();
        
        // Validar antes de enviar
        const errors = validateForm();
        if (Object.keys(errors).length > 0) {
            setValidationErrors(errors);
            return;
        }

        // Enviar al backend
        const result = await updateSetting(
            'security',
            formData,
            route('configuraciones.seguridad')
        );

        if (result.success) {
            setSuccessMessage('✅ Políticas de seguridad guardadas exitosamente');
            setValidationErrors({});
            setTimeout(() => setSuccessMessage(''), 3000);
        } else {
            // Mostrar errores del servidor si existen
            if (result.errors) {
                setValidationErrors(result.errors);
            }
        }
    };

    /**
     * Restablecer a valores por defecto
     */
    const handleResetToDefaults = async () => {
        if (!confirm('¿Estás seguro de restablecer todas las configuraciones de seguridad a los valores por defecto?')) {
            return;
        }

        const defaultValues = {
            password_min_length: 8,
            password_require_uppercase: true,
            password_require_numbers: true,
            password_require_special: false,
            password_expiration_days: 90,
        };

        setFormData(defaultValues);

        const result = await updateSetting(
            'security',
            defaultValues,
            route('configuraciones.seguridad')
        );

        if (result.success) {
            setSuccessMessage('✅ Configuraciones restablecidas a valores por defecto');
            setValidationErrors({});
            setTimeout(() => setSuccessMessage(''), 3000);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title="Seguridad y Contraseñas - SENA" />
            
            {/* Header */}
            <div className="bg-white shadow-sm border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center py-6">
                        <div className="flex items-center">
                            <Link 
                                href={route('admin.configuraciones')}
                                className="mr-4 p-2 text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <ArrowLeft size={20} />
                            </Link>
                            <div className="flex items-center">
                                <Shield className={`mr-3 text-green-600`} size={24} />
                                <div>
                                    <h1 className="text-3xl font-bold text-gray-900">
                                        Seguridad y Contraseñas
                                    </h1>
                                    <p className="text-gray-600 mt-1">
                                        Configura las políticas de seguridad del sistema
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Link
                            href={route('admin.configuraciones')}
                            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                        >
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            {/* Mensajes de estado */}
            {successMessage && (
                <div className="bg-green-50 border-b border-green-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div className="flex items-center">
                            <CheckCircle className="text-green-600 mr-3" size={20} />
                            <p className="text-green-700 font-medium">{successMessage}</p>
                        </div>
                    </div>
                </div>
            )}

            {syncError && (
                <div className="bg-red-50 border-b border-red-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div className="flex items-start">
                            <AlertTriangle className="text-red-600 mr-3 mt-0.5" size={20} />
                            <div className="flex-1">
                                <p className="text-red-700 font-medium">Error al guardar</p>
                                <p className="text-red-600 text-sm mt-1">{syncError}</p>
                            </div>
                            <button
                                onClick={clearSyncError}
                                className="text-red-600 hover:text-red-800 font-bold ml-4"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Contenido Principal */}
            <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <form onSubmit={handleSaveSecuritySettings} className="space-y-8">
                    {/* Políticas de Contraseña */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <Shield className="mr-2 text-blue-600" size={20} />
                            Políticas de Contraseña
                        </h3>
                        
                        <div className="space-y-6">
                            {/* Longitud Mínima */}
                            <div className="flex items-end justify-between">
                                <div className="flex-1">
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Longitud Mínima de Contraseña
                                    </label>
                                    <input
                                        type="number"
                                        min="6"
                                        max="20"
                                        value={formData.password_min_length}
                                        onChange={(e) => handleInputChange('password_min_length', parseInt(e.target.value))}
                                        className={`w-24 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
                                            validationErrors.password_min_length 
                                                ? 'border-red-500 bg-red-50' 
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {validationErrors.password_min_length && (
                                        <p className="text-red-600 text-sm mt-1 flex items-center">
                                            <AlertTriangle size={14} className="mr-1" />
                                            {validationErrors.password_min_length}
                                        </p>
                                    )}
                                </div>
                                <div className="text-sm text-gray-500 ml-4">
                                    Actual: {formData.password_min_length} caracteres
                                </div>
                            </div>

                            <hr className="border-gray-200" />

                            {/* Requisitos de Caracteres */}
                            <div>
                                <h4 className="text-base font-medium text-gray-900 mb-4">
                                    Requisitos Especiales
                                </h4>
                                
                                <div className="space-y-3">
                                    <label className="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            checked={formData.password_require_uppercase}
                                            onChange={(e) => handleInputChange('password_require_uppercase', e.target.checked)}
                                            className="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        />
                                        <span className="ml-3 text-sm font-medium text-gray-700">
                                            Requerir mayúsculas y minúsculas (Ej: AaBbCc)
                                        </span>
                                    </label>
                                    
                                    <label className="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            checked={formData.password_require_numbers}
                                            onChange={(e) => handleInputChange('password_require_numbers', e.target.checked)}
                                            className="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        />
                                        <span className="ml-3 text-sm font-medium text-gray-700">
                                            Requerir números (Ej: 123)
                                        </span>
                                    </label>
                                    
                                    <label className="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            checked={formData.password_require_special}
                                            onChange={(e) => handleInputChange('password_require_special', e.target.checked)}
                                            className="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        />
                                        <span className="ml-3 text-sm font-medium text-gray-700">
                                            Requerir caracteres especiales (Ej: !@#$%^&*)
                                        </span>
                                    </label>
                                </div>

                                {/* Resumen de requisitos activos */}
                                <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p className="text-sm font-medium text-blue-900 mb-2">Requisitos actuales:</p>
                                    <ul className="text-xs text-blue-800 space-y-1">
                                        <li>✓ Mínimo {formData.password_min_length} caracteres</li>
                                        {formData.password_require_uppercase && <li>✓ Mayúsculas y minúsculas</li>}
                                        {formData.password_require_numbers && <li>✓ Números</li>}
                                        {formData.password_require_special && <li>✓ Caracteres especiales</li>}
                                    </ul>
                                </div>
                            </div>

                            <hr className="border-gray-200" />

                            {/* Expiración de Contraseña */}
                            <div className="flex items-end justify-between">
                                <div className="flex-1">
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Expiración de Contraseña
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="365"
                                        value={formData.password_expiration_days}
                                        onChange={(e) => handleInputChange('password_expiration_days', parseInt(e.target.value))}
                                        className={`w-24 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
                                            validationErrors.password_expiration_days 
                                                ? 'border-red-500 bg-red-50' 
                                                : 'border-gray-300'
                                        }`}
                                    />
                                    {validationErrors.password_expiration_days && (
                                        <p className="text-red-600 text-sm mt-1 flex items-center">
                                            <AlertTriangle size={14} className="mr-1" />
                                            {validationErrors.password_expiration_days}
                                        </p>
                                    )}
                                    <p className="text-xs text-gray-500 mt-2">
                                        Días hasta que la contraseña expire (0 = sin expiración)
                                    </p>
                                </div>
                                <div className="text-sm text-gray-500 ml-4">
                                    {formData.password_expiration_days === 0 
                                        ? 'Sin expiración' 
                                        : `${formData.password_expiration_days} días`}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Botones de Acción */}
                    <div className="flex justify-between items-center">
                        <div className="flex space-x-3">
                            <button
                                type="submit"
                                disabled={isSyncing}
                                className="flex items-center px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {isSyncing ? (
                                    <>
                                        <Loader className="animate-spin mr-2" size={16} />
                                        Guardando...
                                    </>
                                ) : (
                                    <>
                                        <Save size={16} className="mr-2" />
                                        Guardar Cambios
                                    </>
                                )}
                            </button>
                            
                            <Link
                                href={route('admin.configuraciones')}
                                className="flex items-center px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </Link>
                        </div>

                        <button
                            type="button"
                            onClick={handleResetToDefaults}
                            disabled={isSyncing}
                            className="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-md transition-colors disabled:opacity-50"
                        >
                            <RotateCcw size={16} className="mr-2" />
                            Restablecer Defectos
                        </button>
                    </div>

                    {/* Recomendaciones */}
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h4 className="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                            <Settings className="mr-2" size={20} />
                            💡 Recomendaciones de Seguridad
                        </h4>
                        <ul className="space-y-2 text-sm text-blue-800">
                            <li>✓ Longitud mínima recomendada: 8-12 caracteres</li>
                            <li>✓ Activar mayúsculas y minúsculas para mayor seguridad</li>
                            <li>✓ Incluir números es fundamental para prevenir ataques</li>
                            <li>✓ Caracteres especiales: solo si el nivel de seguridad lo requiere</li>
                            <li>✓ Expiración: 90 días es un estándar seguro</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    );
}