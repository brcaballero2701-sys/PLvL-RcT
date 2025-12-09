/**
 * Reglas de Validación Reutilizables - Sistema SENA
 * Validaciones frontend para todas las secciones de Configuraciones
 */

// ========== VALIDACIONES DE HORARIOS ==========
export const scheduleRules = {
  validateScheduleTime: (time) => {
    if (!time) return 'La hora es requerida';
    const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
    if (!timeRegex.test(time)) return 'Formato de hora inválido (HH:mm)';
    return null;
  },

  validateScheduleRange: (inicio, fin) => {
    if (!inicio || !fin) return 'Ambas horas son requeridas';
    const [inicioHour, inicioMin] = inicio.split(':').map(Number);
    const [finHour, finMin] = fin.split(':').map(Number);
    const inicioTotal = inicioHour * 60 + inicioMin;
    const finTotal = finHour * 60 + finMin;
    
    if (inicioTotal >= finTotal) {
      return 'La hora de inicio debe ser menor a la hora de fin';
    }
    return null;
  },

  validateAllSchedules: (schedules) => {
    const errors = {};
    
    // Validar cada turno
    const turns = [
      { prefix: 'manana', name: 'Mañana' },
      { prefix: 'tarde', name: 'Tarde' },
      { prefix: 'noche', name: 'Noche' }
    ];

    turns.forEach(turn => {
      const inicio = schedules[`${turn.prefix}_inicio`];
      const fin = schedules[`${turn.prefix}_fin`];
      
      const timeError = scheduleRules.validateScheduleTime(inicio);
      if (timeError) errors[`${turn.prefix}_inicio`] = timeError;
      
      const rangeError = scheduleRules.validateScheduleRange(inicio, fin);
      if (rangeError) errors[`${turn.prefix}_fin`] = rangeError;
    });

    return Object.keys(errors).length > 0 ? errors : null;
  }
};

// ========== VALIDACIONES DE NOTIFICACIONES ==========
export const notificationRules = {
  validColors: ['green', 'blue', 'purple', 'red', 'orange', 'yellow', 'gray'],
  validPositions: ['top-right', 'top-left', 'bottom-right', 'bottom-left'],
  validAnimations: ['slide', 'fade', 'zoom'],
  validDurations: [0, 3, 5, 8],

  validateColor: (color) => {
    if (!color) return 'El color es requerido';
    if (!notificationRules.validColors.includes(color)) {
      return `Color inválido. Opciones válidas: ${notificationRules.validColors.join(', ')}`;
    }
    return null;
  },

  validatePosition: (position) => {
    if (!position) return 'La posición es requerida';
    if (!notificationRules.validPositions.includes(position)) {
      return `Posición inválida. Opciones válidas: ${notificationRules.validPositions.join(', ')}`;
    }
    return null;
  },

  validateDuration: (duration) => {
    if (duration === undefined || duration === null) return 'La duración es requerida';
    if (!notificationRules.validDurations.includes(parseInt(duration))) {
      return `Duración inválida. Opciones válidas: ${notificationRules.validDurations.join(', ')}`;
    }
    return null;
  },

  validateAnimation: (animation) => {
    if (!animation) return 'La animación es requerida';
    if (!notificationRules.validAnimations.includes(animation)) {
      return `Animación inválida. Opciones válidas: ${notificationRules.validAnimations.join(', ')}`;
    }
    return null;
  },

  validateNotifications: (config) => {
    const errors = {};

    const colorError = notificationRules.validateColor(config.color_success);
    if (colorError) errors.color_success = colorError;

    const positionError = notificationRules.validatePosition(config.position);
    if (positionError) errors.position = positionError;

    const durationError = notificationRules.validateDuration(config.duration);
    if (durationError) errors.duration = durationError;

    const animationError = notificationRules.validateAnimation(config.animation);
    if (animationError) errors.animation = animationError;

    return Object.keys(errors).length > 0 ? errors : null;
  }
};

// ========== VALIDACIONES DE SEGURIDAD ==========
export const securityRules = {
  validatePasswordMinLength: (length) => {
    if (length === undefined || length === null) return 'La longitud mínima es requerida';
    const num = parseInt(length);
    if (isNaN(num)) return 'Debe ser un número';
    if (num < 6) return 'Mínimo 6 caracteres';
    if (num > 20) return 'Máximo 20 caracteres';
    return null;
  },

  validatePasswordMinWithSpecial: (minLength, requireSpecial) => {
    if (requireSpecial && parseInt(minLength) < 10) {
      return 'Si se requieren caracteres especiales, mínimo 10 caracteres';
    }
    return null;
  },

  validateExpirationDays: (days) => {
    if (days === undefined || days === null) return 'Los días de expiración son requeridos';
    const num = parseInt(days);
    if (isNaN(num)) return 'Debe ser un número';
    if (num < 0) return 'No puede ser negativo';
    if (num > 365) return 'Máximo 365 días';
    return null;
  },

  validateSecuritySettings: (settings) => {
    const errors = {};

    const minLengthError = securityRules.validatePasswordMinLength(settings.password_min_length);
    if (minLengthError) errors.password_min_length = minLengthError;

    const specialError = securityRules.validatePasswordMinWithSpecial(
      settings.password_min_length,
      settings.password_require_special
    );
    if (specialError) errors.password_min_length = specialError;

    const expirationError = securityRules.validateExpirationDays(settings.password_expiration_days);
    if (expirationError) errors.password_expiration_days = expirationError;

    return Object.keys(errors).length > 0 ? errors : null;
  }
};

// ========== VALIDACIONES DE USUARIOS ==========
export const userRules = {
  validateName: (name) => {
    if (!name || !name.trim()) return 'El nombre es requerido';
    if (name.length < 3) return 'Mínimo 3 caracteres';
    if (name.length > 255) return 'Máximo 255 caracteres';
    return null;
  },

  validateEmail: (email) => {
    if (!email) return 'El correo es requerido';
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) return 'Correo inválido';
    if (email.length > 255) return 'Máximo 255 caracteres';
    return null;
  },

  validatePassword: (password) => {
    if (!password) return 'La contraseña es requerida';
    if (password.length < 8) return 'Mínimo 8 caracteres';
    if (!/[A-Z]/.test(password)) return 'Debe contener mayúsculas';
    if (!/[a-z]/.test(password)) return 'Debe contener minúsculas';
    if (!/\d/.test(password)) return 'Debe contener números';
    return null;
  },

  validateRole: (role) => {
    const validRoles = ['admin', 'guardia', 'user'];
    if (!role) return 'El rol es requerido';
    if (!validRoles.includes(role)) {
      return `Rol inválido. Opciones válidas: ${validRoles.join(', ')}`;
    }
    return null;
  },

  validateCodigoGuardia: (codigo) => {
    if (codigo && codigo.length > 20) return 'Máximo 20 caracteres';
    return null;
  },

  validateUbicacion: (ubicacion) => {
    if (ubicacion && ubicacion.length > 255) return 'Máximo 255 caracteres';
    return null;
  },

  validateUser: (user) => {
    const errors = {};

    const nameError = userRules.validateName(user.name);
    if (nameError) errors.name = nameError;

    const emailError = userRules.validateEmail(user.email);
    if (emailError) errors.email = emailError;

    if (user.password) {
      const passwordError = userRules.validatePassword(user.password);
      if (passwordError) errors.password = passwordError;
    }

    const roleError = userRules.validateRole(user.role);
    if (roleError) errors.role = roleError;

    const codigoError = userRules.validateCodigoGuardia(user.codigo_guardia);
    if (codigoError) errors.codigo_guardia = codigoError;

    const ubicacionError = userRules.validateUbicacion(user.ubicacion_asignada);
    if (ubicacionError) errors.ubicacion_asignada = ubicacionError;

    return Object.keys(errors).length > 0 ? errors : null;
  }
};

// ========== VALIDACIONES DE PERSONALIZACIÓN ==========
export const customizationRules = {
  validColors: ['green', 'blue', 'indigo', 'purple', 'red', 'orange', 'yellow', 'teal', 'cyan', 'gray', 'slate', 'stone'],
  validLanguages: ['es', 'en', 'fr', 'pt'],

  validatePrimaryColor: (color) => {
    if (!color) return 'El color es requerido';
    if (!customizationRules.validColors.includes(color)) {
      return `Color inválido. Opciones válidas: ${customizationRules.validColors.join(', ')}`;
    }
    return null;
  },

  validateLanguage: (language) => {
    if (!language) return 'El idioma es requerido';
    if (!customizationRules.validLanguages.includes(language)) {
      return `Idioma inválido. Opciones válidas: ${customizationRules.validLanguages.join(', ')}`;
    }
    return null;
  },

  validateLogo: (file) => {
    if (!file) return 'El archivo es requerido';
    
    const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      return 'Tipo de archivo inválido. Formatos válidos: JPEG, PNG, SVG, WebP';
    }

    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
      return `Archivo muy grande. Máximo ${maxSize / (1024 * 1024)}MB`;
    }

    return null;
  },

  validateCustomization: (data) => {
    const errors = {};

    const colorError = customizationRules.validatePrimaryColor(data.primary_color);
    if (colorError) errors.primary_color = colorError;

    const languageError = customizationRules.validateLanguage(data.language);
    if (languageError) errors.language = languageError;

    return Object.keys(errors).length > 0 ? errors : null;
  }
};

// ========== VALIDACIONES DE RESPALDO ==========
export const backupRules = {
  validateBackupFile: (file) => {
    if (!file) return 'El archivo es requerido';

    const validExtensions = ['.sql', '.zip', '.tar', '.gz'];
    const fileName = file.name.toLowerCase();
    const hasValidExtension = validExtensions.some(ext => fileName.endsWith(ext));

    if (!hasValidExtension) {
      return `Extensión inválida. Formatos válidos: ${validExtensions.join(', ')}`;
    }

    const maxSize = 500 * 1024 * 1024; // 500MB
    if (file.size > maxSize) {
      return `Archivo muy grande. Máximo ${maxSize / (1024 * 1024)}MB`;
    }

    return null;
  }
};

// ========== UTILIDADES GENERALES ==========
export const validationUtils = {
  /**
   * Validar todos los datos de configuración
   */
  validateAllConfig: (config) => {
    const allErrors = {};

    // Validar horarios
    if (config.schedules) {
      const scheduleErrors = scheduleRules.validateAllSchedules(config.schedules);
      if (scheduleErrors) Object.assign(allErrors, scheduleErrors);
    }

    // Validar notificaciones
    if (config.notifications) {
      const notificationErrors = notificationRules.validateNotifications(config.notifications);
      if (notificationErrors) Object.assign(allErrors, notificationErrors);
    }

    // Validar seguridad
    if (config.security) {
      const securityErrors = securityRules.validateSecuritySettings(config.security);
      if (securityErrors) Object.assign(allErrors, securityErrors);
    }

    // Validar usuarios
    if (config.user) {
      const userErrors = userRules.validateUser(config.user);
      if (userErrors) Object.assign(allErrors, userErrors);
    }

    return Object.keys(allErrors).length > 0 ? allErrors : null;
  },

  /**
   * Limpiar errores de un campo específico
   */
  clearFieldError: (errors, fieldName) => {
    const newErrors = { ...errors };
    delete newErrors[fieldName];
    return Object.keys(newErrors).length > 0 ? newErrors : null;
  },

  /**
   * Verificar si hay errores
   */
  hasErrors: (errors) => {
    return errors && Object.keys(errors).length > 0;
  },

  /**
   * Obtener primer error
   */
  getFirstError: (errors, fieldName) => {
    if (Array.isArray(errors[fieldName])) {
      return errors[fieldName][0];
    }
    return errors[fieldName];
  }
};

export default validationUtils;
