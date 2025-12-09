import { useState, useCallback } from 'react';
import {
  scheduleRules,
  notificationRules,
  securityRules,
  userRules,
  customizationRules,
  backupRules,
  validationUtils
} from '@/utils/validationRules';

/**
 * Hook personalizado para manejo de validaciones en formularios
 * Integra validación frontend con manejo de errores y estado
 */
export const useFormValidation = (initialData = {}, validationType = 'custom') => {
  const [formData, setFormData] = useState(initialData);
  const [errors, setErrors] = useState({});
  const [isValidating, setIsValidating] = useState(false);
  const [isDirty, setIsDirty] = useState(false);

  /**
   * Manejar cambios en inputs
   */
  const handleChange = useCallback((field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));

    // Marcar como modificado
    setIsDirty(true);

    // Limpiar error del campo si existe
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  }, [errors]);

  /**
   * Validar un campo específico
   */
  const validateField = useCallback((field, value) => {
    let error = null;

    switch (validationType) {
      case 'schedule':
        if (field.includes('_inicio') || field.includes('_fin')) {
          error = scheduleRules.validateScheduleTime(value);
        }
        break;

      case 'notification':
        if (field === 'duration') {
          error = notificationRules.validateDuration(value);
        } else if (field === 'position') {
          error = notificationRules.validatePosition(value);
        } else if (field === 'animation') {
          error = notificationRules.validateAnimation(value);
        } else if (field.includes('color')) {
          error = notificationRules.validateColor(value);
        }
        break;

      case 'security':
        if (field === 'password_min_length') {
          error = securityRules.validatePasswordMinLength(value);
        } else if (field === 'password_expiration_days') {
          error = securityRules.validateExpirationDays(value);
        }
        break;

      case 'user':
        if (field === 'name') {
          error = userRules.validateName(value);
        } else if (field === 'email') {
          error = userRules.validateEmail(value);
        } else if (field === 'password') {
          error = userRules.validatePassword(value);
        } else if (field === 'role') {
          error = userRules.validateRole(value);
        } else if (field === 'codigo_guardia') {
          error = userRules.validateCodigoGuardia(value);
        } else if (field === 'ubicacion_asignada') {
          error = userRules.validateUbicacion(value);
        }
        break;

      case 'customization':
        if (field === 'primary_color') {
          error = customizationRules.validatePrimaryColor(value);
        } else if (field === 'language') {
          error = customizationRules.validateLanguage(value);
        }
        break;

      case 'backup':
        if (field === 'file') {
          error = backupRules.validateBackupFile(value);
        }
        break;

      default:
        break;
    }

    return error;
  }, [validationType]);

  /**
   * Validar todos los campos
   */
  const validate = useCallback(async () => {
    setIsValidating(true);
    const newErrors = {};

    try {
      switch (validationType) {
        case 'schedule':
          const scheduleErrors = scheduleRules.validateAllSchedules(formData);
          if (scheduleErrors) Object.assign(newErrors, scheduleErrors);
          break;

        case 'notification':
          const notificationErrors = notificationRules.validateNotifications(formData);
          if (notificationErrors) Object.assign(newErrors, notificationErrors);
          break;

        case 'security':
          const securityErrors = securityRules.validateSecuritySettings(formData);
          if (securityErrors) Object.assign(newErrors, securityErrors);
          break;

        case 'user':
          const userErrors = userRules.validateUser(formData);
          if (userErrors) Object.assign(newErrors, userErrors);
          break;

        case 'customization':
          const customizationErrors = customizationRules.validateCustomization(formData);
          if (customizationErrors) Object.assign(newErrors, customizationErrors);
          break;

        default:
          break;
      }

      setErrors(newErrors);
      return Object.keys(newErrors).length === 0;
    } finally {
      setIsValidating(false);
    }
  }, [formData, validationType]);

  /**
   * Resetear formulario
   */
  const reset = useCallback(() => {
    setFormData(initialData);
    setErrors({});
    setIsDirty(false);
  }, [initialData]);

  /**
   * Obtener error de un campo
   */
  const getFieldError = useCallback((field) => {
    if (Array.isArray(errors[field])) {
      return errors[field][0];
    }
    return errors[field] || null;
  }, [errors]);

  /**
   * Verificar si campo tiene error
   */
  const hasFieldError = useCallback((field) => {
    return !!errors[field];
  }, [errors]);

  /**
   * Establecer error manualmente
   */
  const setFieldError = useCallback((field, error) => {
    setErrors(prev => ({
      ...prev,
      [field]: error
    }));
  }, []);

  /**
   * Limpiar errores
   */
  const clearErrors = useCallback(() => {
    setErrors({});
  }, []);

  return {
    formData,
    setFormData,
    errors,
    setErrors,
    isValidating,
    isDirty,
    
    // Métodos
    handleChange,
    validateField,
    validate,
    reset,
    getFieldError,
    hasFieldError,
    setFieldError,
    clearErrors,
    
    // Utilidades
    hasErrors: validationUtils.hasErrors(errors),
    isValid: Object.keys(errors).length === 0
  };
};

export default useFormValidation;
