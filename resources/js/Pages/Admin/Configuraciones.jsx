import { useState, useEffect } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { Clock, Users, MessageSquare, Image, Shield, Database, CheckCircle, Settings, Download, Upload, AlertTriangle, HardDrive, Calendar, User, Trash2 } from 'lucide-react';
import LogoDisplay from '@/Components/LogoDisplay';
import SidebarLayout from '@/Layouts/SidebarLayout';

export default function Configuraciones() {
    const { rolesStats, asistenciasStats, asistenciasRecientes, systemSettings, backupFiles } = usePage().props;

    const [activeSection, setActiveSection] = useState('horarios');
    const [activeSubSection, setActiveSubSection] = useState('configuracion');

    // Estado para mensajes flash - INICIALIZAR CON VALORES DEL BACKEND
    const [notificacionesConfig, setNotificacionesConfig] = useState({
        color_success: systemSettings?.notification_color_success || 'green',
        color_error: systemSettings?.notification_color_error || 'red',
        color_warning: systemSettings?.notification_color_warning || 'yellow',
        color_info: systemSettings?.notification_color_info || 'blue',
        duration: String(systemSettings?.notification_duration || '5'),
        position: systemSettings?.notification_position || 'top-right',
        sound: systemSettings?.notification_sound || false,
        animation: systemSettings?.notification_animation || 'slide'
    });

    const [notificacionesStatus, setNotificacionesStatus] = useState({ loading: false, message: '' });

    // Sincronizar estado cuando cambian los systemSettings
    useEffect(() => {
        if (systemSettings) {
            setNotificacionesConfig({
                color_success: systemSettings.notification_color_success || 'green',
                color_error: systemSettings.notification_color_error || 'red',
                color_warning: systemSettings.notification_color_warning || 'yellow',
                color_info: systemSettings.notification_color_info || 'blue',
                duration: String(systemSettings.notification_duration || '5'),
                position: systemSettings.notification_position || 'top-right',
                sound: systemSettings.notification_sound || false,
                animation: systemSettings.notification_animation || 'slide'
            });
        }
    }, [systemSettings]);

    // Estado para el upload de logo (simplificado)
    const [uploadStatus, setUploadStatus] = useState({ loading: false, message: '' });
    const [logoTimestamp, setLogoTimestamp] = useState(Date.now());
    const [currentLogoPath, setCurrentLogoPath] = useState(systemSettings?.logo_path || '/images/sena-logo.svg');

    // Estado para el esquema de colores
    const [selectedColor, setSelectedColor] = useState(systemSettings?.primary_color || 'green');
    const [colorSaveStatus, setColorSaveStatus] = useState({ loading: false, message: '' });
    const [previewColor, setPreviewColor] = useState(systemSettings?.primary_color || 'green');

    // Estado para políticas de seguridad CON VALIDACIONES
    const [securityConfig, setSecurityConfig] = useState({
        min_length: systemSettings?.password_min_length || '8',
        require_uppercase: systemSettings?.password_require_uppercase !== false,
        require_numbers: systemSettings?.password_require_numbers !== false,
        require_special: systemSettings?.password_require_special || false,
        expiration_days: systemSettings?.password_expiration_days || '90'
    });

    // Estado para errores de validación
    const [securityErrors, setSecurityErrors] = useState({});
    const [securityStatus, setSecurityStatus] = useState({ loading: false, message: '', type: 'info' });

    // Estado para respaldo y restauración
    const [backupStatus, setBackupStatus] = useState({ loading: false, message: '' });
    const [uploadFile, setUploadFile] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const [backupList, setBackupList] = useState(backupFiles || []);

    // Estado para modal de contraseña en restauración
    const [showPasswordModal, setShowPasswordModal] = useState(false);
    const [restorePassword, setRestorePassword] = useState('');
    const [restorePasswordError, setRestorePasswordError] = useState('');
    const [isRestoringWithPassword, setIsRestoringWithPassword] = useState(false);

    // Mapa de colores
    const colorMap = {
        green: { 50: '240, 253, 244', 100: '220, 252, 231', 200: '187, 247, 208', 300: '134, 239, 172', 400: '74, 222, 128', 500: '34, 197, 94', 600: '22, 163, 74', 700: '16, 185, 74', 800: '20, 83, 45', 900: '20, 83, 45' },
        blue: { 50: '239, 246, 255', 100: '219, 234, 254', 200: '191, 219, 254', 300: '147, 197, 253', 400: '96, 165, 250', 500: '59, 130, 246', 600: '37, 99, 235', 700: '29, 78, 216', 800: '30, 58, 138', 900: '30, 58, 138' },
        indigo: { 50: '238, 242, 255', 100: '224, 231, 255', 200: '199, 210, 254', 300: '165, 180, 252', 400: '129, 140, 248', 500: '99, 102, 241', 600: '79, 70, 229', 700: '67, 56, 202', 800: '55, 48, 163', 900: '55, 48, 163' },
        purple: { 50: '250, 245, 255', 100: '243, 232, 255', 200: '232, 204, 255', 300: '216, 180, 254', 400: '192, 132, 250', 500: '168, 85, 247', 600: '147, 51, 234', 700: '126, 34, 206', 800: '88, 28, 135', 900: '88, 28, 135' },
        red: { 50: '254, 242, 242', 100: '254, 226, 226', 200: '254, 202, 202', 300: '252, 165, 165', 400: '248, 113, 113', 500: '239, 68, 68', 600: '220, 38, 38', 700: '185, 28, 28', 800: '127, 29, 29', 900: '127, 29, 29' },
        orange: { 50: '255, 247, 237', 100: '255, 237, 213', 200: '254, 215, 170', 300: '253, 186, 116', 400: '251, 146, 60', 500: '249, 115, 22', 600: '234, 88, 12', 700: '194, 65, 12', 800: '124, 45, 18', 900: '124, 45, 18' },
        yellow: { 50: '254, 252, 232', 100: '254, 248, 204', 200: '254, 240, 138', 300: '253, 224, 71', 400: '250, 204, 21', 500: '234, 179, 8', 600: '202, 138, 4', 700: '161, 98, 7', 800: '113, 63, 18', 900: '113, 63, 18' },
        teal: { 50: '240, 253, 250', 100: '204, 251, 241', 200: '153, 246, 228', 300: '94, 234, 212', 400: '45, 212, 191', 500: '20, 184, 166', 600: '13, 148, 136', 700: '15, 118, 110', 800: '20, 83, 80', 900: '20, 83, 80' },
        cyan: { 50: '240, 249, 250', 100: '207, 250, 254', 200: '165, 243, 252', 300: '103, 232, 249', 400: '34, 211, 238', 500: '34, 211, 238', 600: '8, 145, 178', 700: '14, 116, 144', 800: '21, 94, 117', 900: '21, 94, 117' },
        gray: { 50: '249, 250, 251', 100: '243, 244, 246', 200: '229, 231, 235', 300: '209, 213, 219', 400: '156, 163, 175', 500: '107, 114, 128', 600: '75, 85, 99', 700: '55, 65, 81', 800: '31, 41, 55', 900: '17, 24, 39' },
        slate: { 50: '248, 250, 252', 100: '241, 245, 249', 200: '226, 232, 240', 300: '203, 213, 225', 400: '148, 163, 184', 500: '100, 116, 139', 600: '71, 85, 105', 700: '51, 65, 85', 800: '30, 41, 59', 900: '15, 23, 42' },
        stone: { 50: '250, 250, 249', 100: '245, 245, 244', 200: '231, 229, 228', 300: '214, 211, 209', 400: '168, 162, 158', 500: '120, 113, 108', 600: '87, 83, 82', 700: '68, 64, 60', 800: '41, 37, 36', 900: '28, 25, 23' }
    };

    // Datos de configuración de horarios
    const { data: horarios, setData: setHorarios, post: postHorarios, processing: processingHorarios } = useForm({
        manana_inicio: '06:00',
        manana_fin: '11:45',
        tarde_inicio: '12:00',
        tarde_fin: '17:45',
        noche_inicio: '18:00',
        noche_fin: '21:45',
        desde: '',
        hasta: ''
    });

    const guardarAsistencia = () => {
        alert('Horario actualizado con éxito.');
    };

    const cancelar = () => {
        window.location.reload();
    };

    // Función para aplicar color a la vista previa
    const applyColorPreview = (colorName) => {
        const color = colorMap[colorName] || colorMap.green;
        const previewElement = document.getElementById('color-preview-bar');
        if (previewElement) {
            previewElement.style.backgroundColor = `rgb(${color[600]})`;
        }
    };

    const handleColorPreview = (colorValue) => {
        setPreviewColor(colorValue);
        applyColorPreview(colorValue);
    };

    const handleColorChange = async (colorValue) => {
        setSelectedColor(colorValue);
        setColorSaveStatus({ loading: true, message: '⏳ Guardando color...' });

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.customization.update-color'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ primary_color: colorValue })
            });

            const result = await response.json();
            if (response.ok && result.success) {
                setColorSaveStatus({ loading: false, message: '✅ Color guardado exitosamente' });
                // Recargar la página automáticamente después de 1.5 segundos
                setTimeout(() => window.location.reload(), 1500);
            } else {
                setColorSaveStatus({ loading: false, message: `❌ ${result.message || 'Error al guardar'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setColorSaveStatus({ loading: false, message: '❌ Error al guardar el color: ' + error.message });
        }
    };

    const handleLogoUpload = async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setUploadStatus({ loading: true, message: '⏳ Subiendo logo...' });

        const formData = new FormData();
        formData.append('logo', file);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.customization.upload-logo'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            });

            const result = await response.json();
            if (response.ok && result.success) {
                setCurrentLogoPath(result.logo_path || `/images/logo.png`);
                setLogoTimestamp(Date.now());
                setUploadStatus({ loading: false, message: '✅ Logo subido exitosamente' });
                // Recargar la página automáticamente después de 1.5 segundos
                setTimeout(() => window.location.reload(), 1500);
            } else {
                setUploadStatus({ loading: false, message: `❌ ${result.message || 'Error al subir'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setUploadStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    const handleLogoReset = async () => {
        if (!confirm('¿Desea restablecer el logo predeterminado?')) return;
        setUploadStatus({ loading: true, message: '⏳ Restableciendo logo...' });

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.customization.reset'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                setCurrentLogoPath('/images/sena-logo.png');
                setLogoTimestamp(Date.now());
                setUploadStatus({ loading: false, message: '✅ Logo restablecido' });
                setTimeout(() => setUploadStatus({ loading: false, message: '' }), 2000);
            } else {
                setUploadStatus({ loading: false, message: `❌ ${result.message || 'Error al restablecer'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setUploadStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    useEffect(() => {
        applyColorPreview(previewColor);
    }, [previewColor]);

    // Handlers para Mensajes Flash
    const handleGuardarNotificaciones = async () => {
        setNotificacionesStatus({ loading: true, message: '⏳ Guardando configuración...' });
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.notificaciones'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    color_success: notificacionesConfig.color_success,
                    color_error: notificacionesConfig.color_error,
                    color_warning: notificacionesConfig.color_warning,
                    color_info: notificacionesConfig.color_info,
                    duration: parseInt(notificacionesConfig.duration),
                    position: notificacionesConfig.position,
                    sound: notificacionesConfig.sound || false,
                    animation: notificacionesConfig.animation
                })
            });

            const result = await response.json();
            if (response.ok && result.success) {
                setNotificacionesStatus({ loading: false, message: '✅ Configuración guardada exitosamente' });
                setTimeout(() => {
                    setNotificacionesStatus({ loading: false, message: '' });
                    showTestNotification('success');
                }, 1500);
            } else {
                setNotificacionesStatus({ loading: false, message: `❌ ${result.message || 'Error al guardar'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setNotificacionesStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    const handleRestablecerNotificaciones = async () => {
        if (!confirm('¿Está seguro de que desea restablecer la configuración de notificaciones a los valores por defecto?')) {
            return;
        }
        setNotificacionesStatus({ loading: true, message: '⏳ Restableciendo configuración...' });
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.notificaciones'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    color_success: 'green',
                    color_error: 'red',
                    color_warning: 'yellow',
                    color_info: 'blue',
                    duration: 5,
                    position: 'top-right',
                    sound: false,
                    animation: 'slide'
                })
            });

            const result = await response.json();
            if (response.ok && result.success) {
                setNotificacionesConfig({
                    color_success: 'green',
                    color_error: 'red',
                    color_warning: 'yellow',
                    color_info: 'blue',
                    duration: '5',
                    position: 'top-right',
                    sound: false,
                    animation: 'slide'
                });
                setNotificacionesStatus({ loading: false, message: '✅ Configuración restablecida a valores predeterminados' });
                setTimeout(() => setNotificacionesStatus({ loading: false, message: '' }), 2000);
            } else {
                setNotificacionesStatus({ loading: false, message: `❌ ${result.message || 'Error al restablecer'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setNotificacionesStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    // Handlers para otras secciones
    const handleGuardarRoles = () => {
        alert('✅ Cambios en roles guardados correctamente');
    };

    const handleCancelarRoles = () => {
        window.location.reload();
    };

    const handleGuardarPersonalizacion = () => {
        alert('✅ Personalización guardada correctamente');
    };

    const handleVistaPrevia = () => {
        alert('📋 Vista previa de personalización');
    };

    const handleGuardarSeguridad = async () => {
        // Validar antes de enviar
        const minLength = parseInt(securityConfig.min_length);
        
        if (securityConfig.require_special && minLength < 10) {
            setSecurityStatus({ 
                loading: false, 
                message: '❌ Si se requieren caracteres especiales, la longitud mínima debe ser al menos 10 caracteres',
                type: 'error'
            });
            return;
        }

        setSecurityStatus({ loading: true, message: '⏳ Guardando políticas de seguridad...', type: 'info' });
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const payload = {
                password_min_length: minLength,
                password_require_uppercase: Boolean(securityConfig.require_uppercase),
                password_require_numbers: Boolean(securityConfig.require_numbers),
                password_require_special: Boolean(securityConfig.require_special),
                password_expiration_days: parseInt(securityConfig.expiration_days)
            };

            console.log('Enviando payload:', payload);

            const response = await fetch(route('admin.configuraciones.seguridad'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            
            console.log('Response status:', response.status);
            console.log('Response body:', result);

            if (response.ok && result.success) {
                setSecurityStatus({ 
                    loading: false, 
                    message: '✅ Políticas de seguridad guardadas exitosamente',
                    type: 'success'
                });
                setTimeout(() => {
                    setSecurityStatus({ loading: false, message: '', type: 'info' });
                    showTestNotification('success');
                }, 1500);
            } else {
                const errorMsg = result.errors 
                    ? Object.values(result.errors).flat().join(', ')
                    : result.message || 'Error desconocido';
                    
                setSecurityStatus({ 
                    loading: false, 
                    message: `❌ Error: ${errorMsg}`,
                    type: 'error'
                });
            }
        } catch (error) {
            console.error('Error completo:', error);
            setSecurityStatus({ 
                loading: false, 
                message: `❌ Error de conexión: ${error.message}`,
                type: 'error'
            });
        }
    };

    const handleCrearRespaldo = async () => {
        if (!confirm('¿Crear un respaldo de la base de datos ahora?')) return;
        
        setBackupStatus({ loading: true, message: '⏳ Creando respaldo...' });
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.backup.generate'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                // Añadir el nuevo respaldo a la lista sin recargar
                const newBackup = {
                    name: result.backup_name || `backup_${new Date().getTime()}.sql`,
                    size: result.size || 0,
                    date: new Date().toISOString()
                };
                
                setBackupList([newBackup, ...backupList]);
                setBackupStatus({ loading: false, message: '✅ Respaldo creado exitosamente' });
                setTimeout(() => setBackupStatus({ loading: false, message: '' }), 3000);
            } else {
                setBackupStatus({ loading: false, message: `❌ Error: ${result.message || 'Error al crear respaldo'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setBackupStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    const handleDescargarRespaldo = async (filename) => {
        // Descargar usando blob para permitir elegir ubicación
        try {
            setBackupStatus({ loading: true, message: '⏳ Descargando respaldo...' });
            const response = await fetch(route('admin.backup.download', { filename: filename }), {
                method: 'GET',
            });

            if (!response.ok) {
                throw new Error('Error al descargar el respaldo');
            }

            const blob = await response.blob();
            
            // Crear un elemento link temporal para descargar
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            
            // Triggear descarga
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            setBackupStatus({ loading: false, message: '✅ Respaldo descargado correctamente' });
            setTimeout(() => setBackupStatus({ loading: false, message: '' }), 2000);
        } catch (error) {
            console.error('Error:', error);
            setBackupStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    const handleEliminarRespaldo = async (filename) => {
        if (!confirm('¿Está seguro de que desea eliminar este respaldo?')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.backup.delete', { filename: filename }), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                // Eliminar de la lista sin recargar
                setBackupList(backupList.filter(b => b.name !== filename));
                setBackupStatus({ loading: false, message: '✅ Respaldo eliminado exitosamente' });
                setTimeout(() => setBackupStatus({ loading: false, message: '' }), 2000);
            } else {
                setBackupStatus({ loading: false, message: `❌ ${result.message || 'Error al eliminar respaldo'}` });
            }
        } catch (error) {
            console.error('Error:', error);
            setBackupStatus({ loading: false, message: '❌ Error: ' + error.message });
        }
    };

    const handleGuardarAvanzada = async () => {
        const debug = document.querySelector('input[data-field="debug"]')?.checked || false;
        const audit = document.querySelector('input[data-field="audit"]')?.checked || true;
        const cache = document.querySelector('input[data-field="cache"]')?.checked || true;
        const maintenance = document.querySelector('input[data-field="maintenance"]')?.checked || false;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.avanzada'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    debug_mode: debug,
                    audit_log: audit,
                    cache_enabled: cache,
                    maintenance_mode: maintenance
                })
            });

            const result = await response.json();
            if (response.ok && result.success) {
                alert('✅ Configuración avanzada guardada correctamente');
            } else {
                alert(`❌ Error: ${result.message || 'Error al guardar'}`);
            }
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    };

    const handleLimpiarCache = async () => {
        if (!confirm('¿Está seguro de que desea limpiar el caché del sistema?')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.limpiar-cache'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                alert('✅ Caché limpiado exitosamente');
            } else {
                alert(`❌ Error: ${result.message || 'Error al limpiar caché'}`);
            }
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    };

    const handleRestablecerConfig = async () => {
        if (!confirm('¿Está COMPLETAMENTE seguro de restablecer todos los valores a predeterminados? Esta acción no se puede deshacer.')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.resetear-valores'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                alert('✅ Configuración restablecida a valores predeterminados');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert(`❌ Error: ${result.message || 'Error al restablecer'}`);
            }
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    };

    const handleEliminarDatos = async () => {
        if (!confirm('⚠️ ADVERTENCIA: Esto eliminará TODOS los datos del sistema. ¿Continuar?')) return;
        if (!confirm('⚠️ SEGUNDA CONFIRMACIÓN: Esta acción es IRREVERSIBLE. ¿Realmente desea continuar?')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('admin.configuraciones.eliminar-datos'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                alert('✅ Todos los datos han sido eliminados');
                setTimeout(() => window.location.href = route('login'), 1000);
            } else {
                alert(`❌ Error: ${result.message || 'Error al eliminar datos'}`);
            }
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    };

    // Función para confirmar restauración con contraseña
    const handleConfirmRestore = async () => {
        if (!uploadFile) {
            setRestorePasswordError('Por favor selecciona un archivo');
            return;
        }

        if (!restorePassword.trim()) {
            setRestorePasswordError('La contraseña es requerida');
            return;
        }

        setIsRestoringWithPassword(true);
        setRestorePasswordError('');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Paso 1: Subir el archivo
            const uploadFormData = new FormData();
            uploadFormData.append('backup_file', uploadFile);

            const uploadResponse = await fetch(route('admin.backup.upload'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: uploadFormData
            });

            const uploadResult = await uploadResponse.json();

            if (!uploadResponse.ok || !uploadResult.success) {
                setRestorePasswordError(uploadResult.message || 'Error al subir el archivo');
                setIsRestoringWithPassword(false);
                return;
            }

            const uploadedFilename = uploadResult.filename;

            // Paso 2: Restaurar el archivo con contraseña
            const restoreResponse = await fetch(route('admin.backup.restore'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    filename: uploadedFilename,
                    password: restorePassword
                })
            });

            const restoreResult = await restoreResponse.json();

            if (restoreResponse.ok && restoreResult.success) {
                setShowPasswordModal(false);
                setRestorePassword('');
                setUploadFile(null);
                alert('✅ Base de datos restaurada exitosamente');
                
                // Esperar un segundo y luego redirigir al login
                setTimeout(() => {
                    // Redirigir al login usando la URL del servidor
                    if (restoreResult.redirect) {
                        window.location.href = restoreResult.redirect;
                    } else {
                        // Fallback a la ruta de login
                        window.location.href = route('login');
                    }
                }, 1500);
            } else {
                setRestorePasswordError(restoreResult.message || 'Error al restaurar');
                setIsRestoringWithPassword(false);
            }
        } catch (error) {
            console.error('Error:', error);
            setRestorePasswordError('Error de conexión: ' + error.message);
            setIsRestoringWithPassword(false);
        }
    };

    // Función para limpiar error al escribir
    const handlePasswordChange = (e) => {
        setRestorePassword(e.target.value);
        if (restorePasswordError) {
            setRestorePasswordError(''); // Limpiar el error cuando el usuario comienza a escribir
        }
    };

    // Función para manejar el inicio de restauración
    const handleStartRestore = async () => {
        if (!uploadFile) {
            alert('Por favor selecciona un archivo');
            return;
        }
        if (!confirm('⚠️ ADVERTENCIA: Esto restaurará la base de datos. ¿Continuar?')) return;
        
        setShowPasswordModal(true);
    };

    // Función para mostrar notificaciones de prueba
    const showTestNotification = (type) => {
        const messages = {
            success: {
                title: '✅ Operación completada',
                message: 'Los cambios se guardaron correctamente'
            },
            error: {
                title: '❌ Error en la operación',
                message: 'No se pudo completar la acción'
            },
            warning: {
                title: '⚠️ Advertencia',
                message: 'Revisa los datos ingresados'
            },
            info: {
                title: 'ℹ️ Información',
                message: 'Nueva actualización disponible'
            }
        };

        const config = messages[type] || messages.info;
        
        // Crear elemento de notificación
        const notification = document.createElement('div');

        const dotColor = {
            success: `bg-${notificacionesConfig.color_success}-500`,
            error: `bg-${notificacionesConfig.color_error}-500`,
            warning: `bg-${notificacionesConfig.color_warning}-500`,
            info: `bg-${notificacionesConfig.color_info}-500`
        };

        notification.className = `fixed ${notificacionesConfig.position} p-4 rounded-lg border-2 shadow-lg z-50 animate-${notificacionesConfig.animation} max-w-sm`;
        notification.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 ${dotColor[type]} rounded-full"></div>
                <div>
                    <p class="font-medium">${config.title}</p>
                    <p class="text-sm">${config.message}</p>
                </div>
            </div>
        `;

        // Aplicar estilos dinámicamente
        const bgColors = {
            green: 'rgb(240, 253, 244)',
            red: 'rgb(254, 242, 242)',
            yellow: 'rgb(254, 252, 232)',
            blue: 'rgb(239, 246, 255)'
        };

        const borderColors = {
            green: 'rgb(220, 252, 231)',
            red: 'rgb(254, 226, 226)',
            yellow: 'rgb(254, 248, 204)',
            blue: 'rgb(219, 234, 254)'
        };

        const textColors = {
            green: 'rgb(20, 83, 45)',
            red: 'rgb(127, 29, 29)',
            yellow: 'rgb(113, 63, 18)',
            blue: 'rgb(30, 58, 138)'
        };

        const colorKey = {
            success: notificacionesConfig.color_success,
            error: notificacionesConfig.color_error,
            warning: notificacionesConfig.color_warning,
            info: notificacionesConfig.color_info
        }[type];

        notification.style.backgroundColor = bgColors[colorKey] || bgColors.blue;
        notification.style.borderColor = borderColors[colorKey] || borderColors.blue;
        notification.style.color = textColors[colorKey] || textColors.blue;

        document.body.appendChild(notification);

        // Eliminar automáticamente según duración
        const duration = parseInt(notificacionesConfig.duration) * 1000 || 5000;
        if (duration > 0) {
            setTimeout(() => notification.remove(), duration);
        }
    };

    const formatFileSize = (size) => {
        if (size < 1024) return `${size} B`;
        if (size < 1024 * 1024) return `${(size / 1024).toFixed(2)} KB`;
        if (size < 1024 * 1024 * 1024) return `${(size / (1024 * 1024)).toFixed(2)} MB`;
        return `${(size / (1024 * 1024 * 1024)).toFixed(2)} GB`;
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES');
    };

    return (
        <SidebarLayout
            title="Configuraciones del Sistema - SENA"
            header={
                <div>
                    <h1 className="text-3xl font-bold text-gray-800">Configuraciones del Sistema</h1>
                    <p className="text-gray-600 mt-1">Administra parámetros generales, roles y opciones avanzadas</p>
                </div>
            }
        >
            <div className="flex gap-0">
                {/* Sidebar de opciones - SECUNDARIO */}
                <aside className="w-64 bg-white border-r border-gray-200 p-4 overflow-y-auto shadow-lg">
                    <h3 className="text-gray-700 text-sm font-bold uppercase tracking-widest mb-6 px-3">Opciones de Configuración</h3>
                    <nav className="space-y-2">
                        <button
                            onClick={() => setActiveSection('horarios')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'horarios' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <Clock size={20} />
                            <span className="text-sm">Horarios y Asistencias</span>
                        </button>

                        <button
                            onClick={() => setActiveSection('usuarios')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'usuarios' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <Users size={20} />
                            <span className="text-sm">Usuarios y Roles</span>
                        </button>

                        <button
                            onClick={() => setActiveSection('mensajes')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'mensajes' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <MessageSquare size={20} />
                            <span className="text-sm">Mensajes flash / Notificaciones</span>
                        </button>

                        <button
                            onClick={() => setActiveSection('personalizacion')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'personalizacion' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <Image size={20} />
                            <span className="text-sm">Personalización del Sistema</span>
                        </button>

                        <button
                            onClick={() => setActiveSection('seguridad')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'seguridad' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <Shield size={20} />
                            <span className="text-sm">Seguridad y Contraseñas</span>
                        </button>

                        <button
                            onClick={() => setActiveSection('respaldo')}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                                activeSection === 'respaldo' 
                                    ? 'bg-green-700 text-white font-semibold shadow-lg border-l-4 border-green-700' 
                                    : 'text-gray-700 hover:bg-gray-100 border-l-4 border-transparent'
                            }`}
                        >
                            <Database size={20} />
                            <span className="text-sm">Respaldo y Restauración</span>
                        </button>
                    </nav>
                </aside>

                {/* Contenido principal */}
                <main className="flex-1 p-8 overflow-y-auto">
                    {/* Sección Horarios y Asistencias */}
                    {activeSection === 'horarios' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Horarios y Asistencias</h2>
                            <p className="text-gray-600 mb-8">Configuración de intervalos de trabajo y historial de asistencias</p>

                            {/* Pestañas internas para Horarios y Historial */}
                            <div className="mb-8">
                                <div className="border-b border-gray-200">
                                    <nav className="-mb-px flex space-x-8">
                                        <button
                                            onClick={() => setActiveSubSection('configuracion')}
                                            className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                                activeSubSection === 'configuracion' || !activeSubSection
                                                    ? 'border-green-500 text-green-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                            }`}
                                        >
                                            Configuración de Horarios
                                        </button>
                                        <button
                                            onClick={() => setActiveSubSection('historial')}
                                            className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                                activeSubSection === 'historial'
                                                    ? 'border-green-500 text-green-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                            }`}
                                        >
                                            Historial de Asistencias
                                        </button>
                                    </nav>
                                </div>
                            </div>

                            {/* Contenido de Configuración de Horarios */}
                            {(!activeSubSection || activeSubSection === 'configuracion') && (
                                <div className="space-y-6">
                                    {/* Horario Mañana */}
                                    <div className="flex items-center gap-4">
                                        <div className="w-48">
                                            <span className="text-lg font-medium text-gray-800">06:00AM-11:45AM</span>
                                            <span className="text-gray-600 ml-2">(mañana)</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="time"
                                                value={horarios.manana_fin}
                                                onChange={(e) => setHorarios('manana_fin', e.target.value)}
                                                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-400"
                                            />
                                            <span className="text-gray-600">PM</span>
                                        </div>
                                    </div>

                                    {/* Horario Tarde */}
                                    <div className="flex items-center gap-4">
                                        <div className="w-48">
                                            <span className="text-lg font-medium text-gray-800">12:00PM-05:45PM</span>
                                            <span className="text-gray-600 ml-2">(tarde)</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="time"
                                                value={horarios.tarde_fin}
                                                onChange={(e) => setHorarios('tarde_fin', e.target.value)}
                                                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-400"
                                            />
                                            <span className="text-gray-600">PM</span>
                                        </div>
                                    </div>

                                    {/* Horario Noche */}
                                    <div className="flex items-center gap-4">
                                        <div className="w-48">
                                            <span className="text-lg font-medium text-gray-800">06:00PM-09:45PM</span>
                                            <span className="text-gray-600 ml-2">(noche)</span>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <input
                                                type="text"
                                                placeholder="Desde"
                                                value={horarios.desde}
                                                onChange={(e) => setHorarios('desde', e.target.value)}
                                                className="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 placeholder-gray-400"
                                            />
                                            <input
                                                type="text"
                                                placeholder="Hasta"
                                                value={horarios.hasta}
                                                onChange={(e) => setHorarios('hasta', e.target.value)}
                                                className="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 placeholder-gray-400"
                                            />
                                        </div>
                                    </div>

                                    {/* Botón Agregar asistencia */}
                                    <div className="mt-8">
                                        <button
                                            onClick={guardarAsistencia}
                                            className="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg"
                                        >
                                            Agregar asistencia
                                        </button>
                                    </div>

                                    {/* Botones de acción */}
                                    <div className="flex gap-4 mt-8">
                                        <button
                                            onClick={guardarAsistencia}
                                            className="bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-lg"
                                        >
                                            Guardar
                                        </button>
                                        <button
                                            onClick={cancelar}
                                            className="bg-white border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 px-6 rounded-lg"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Contenido del Historial de Asistencias */}
                            {activeSubSection === 'historial' && (
                                <div>
                                    {/* Estadísticas rápidas */}
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                                        <div className="bg-white rounded-lg p-6 border border-gray-200">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-gray-600 mb-1">Total Asistencias Hoy</p>
                                                    <p className="text-2xl font-bold text-gray-900">{asistenciasStats?.total_hoy || 0}</p>
                                                </div>
                                                <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <CheckCircle className="w-6 h-6 text-green-600" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="bg-white rounded-lg p-6 border border-gray-200">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-gray-600 mb-1">Llegadas Puntuales</p>
                                                    <p className="text-2xl font-bold text-gray-900">{asistenciasStats?.puntuales || 0}</p>
                                                </div>
                                                <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <Clock className="w-6 h-6 text-blue-600" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="bg-white rounded-lg p-6 border border-gray-200">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-gray-600 mb-1">Llegadas Tarde</p>
                                                    <p className="text-2xl font-bold text-gray-900">{asistenciasStats?.tarde || 0}</p>
                                                </div>
                                                <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                    <Clock className="w-6 h-6 text-yellow-600" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="bg-white rounded-lg p-6 border border-gray-200">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm text-gray-600 mb-1">Ausencias</p>
                                                    <p className="text-2xl font-bold text-gray-900">{asistenciasStats?.ausencias || 0}</p>
                                                </div>
                                                <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                                    <Users className="w-6 h-6 text-red-600" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Tabla de asistencias recientes */}
                                    <div className="bg-white rounded-lg border border-gray-200">
                                        <div className="px-6 py-4 border-b border-gray-200">
                                            <div className="flex items-center justify-between">
                                                <h3 className="text-lg font-semibold text-gray-900">Asistencias Recientes</h3>
                                                <div className="flex items-center gap-3">
                                                    <input
                                                        type="date"
                                                        className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                                        defaultValue={new Date().toISOString().split('T')[0]}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="overflow-x-auto">
                                            {asistenciasRecientes && asistenciasRecientes.length > 0 ? (
                                                <table className="w-full">
                                                    <thead className="bg-gray-50">
                                                        <tr>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Instructor
                                                            </th>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Área
                                                            </th>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Fecha
                                                            </th>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Hora Entrada
                                                            </th>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Hora Salida
                                                            </th>
                                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Estado
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="bg-white divide-y divide-gray-200">
                                                        {asistenciasRecientes.map((asistencia, index) => (
                                                            <tr key={index} className="hover:bg-gray-50">
                                                                <td className="px-6 py-4 whitespace-nowrap">
                                                                    <div className="text-sm font-medium text-gray-900">
                                                                        {asistencia.instructor}
                                                                    </div>
                                                                </td>
                                                                <td className="px-6 py-4 whitespace-nowrap">
                                                                    <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                        {asistencia.area}
                                                                    </span>
                                                                </td>
                                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {asistencia.fecha}
                                                                </td>
                                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {asistencia.entrada}
                                                                </td>
                                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    {asistencia.salida}
                                                                </td>
                                                                <td className="px-6 py-4 whitespace-nowrap">
                                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                                        asistencia.estado === 'Puntual' ? 'bg-green-100 text-green-800' :
                                                                        asistencia.estado === 'Tarde' ? 'bg-yellow-100 text-yellow-800' :
                                                                        asistencia.estado === 'Presente' ? 'bg-blue-100 text-blue-800' :
                                                                        'bg-red-100 text-red-800'
                                                                    }`}>
                                                                        {asistencia.estado}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>
                                            ) : (
                                                <div className="text-center py-12">
                                                    <Clock className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                                    <h3 className="text-sm font-medium text-gray-900 mb-2">No hay registros de asistencias hoy</h3>
                                                    <p className="text-sm text-gray-500">
                                                        Las asistencias de hoy aparecerán aquí cuando los instructores registren su entrada.
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Sección Usuarios y Roles */}
                    {activeSection === 'usuarios' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Usuarios y Roles</h2>
                            <p className="text-gray-600 mb-8">Gestiona los usuarios del sistema y sus roles asignados</p>
                            
                            {/* Estadísticas de usuarios */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div className="bg-white rounded-lg p-6 border border-gray-200">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm text-gray-600 mb-1">Total Usuarios</p>
                                            <p className="text-2xl font-bold text-gray-900">{rolesStats?.total || 0}</p>
                                        </div>
                                        <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <Users className="w-6 h-6 text-blue-600" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="bg-white rounded-lg p-6 border border-gray-200">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm text-gray-600 mb-1">Administradores</p>
                                            <p className="text-2xl font-bold text-gray-900">{rolesStats?.admin || 0}</p>
                                        </div>
                                        <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <Shield className="w-6 h-6 text-green-600" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="bg-white rounded-lg p-6 border border-gray-200">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm text-gray-600 mb-1">Guardias</p>
                                            <p className="text-2xl font-bold text-gray-900">{rolesStats?.guardia || 0}</p>
                                        </div>
                                        <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <Shield className="w-6 h-6 text-yellow-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Gestión de roles */}
                            <div className="bg-white rounded-lg p-6 border border-gray-200">
                                <h3 className="text-lg font-semibold text-gray-800 mb-4">Roles del Sistema</h3>
                                
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                <Shield className="w-5 h-5 text-red-600" />
                                            </div>
                                            <div>
                                                <h4 className="font-semibold text-gray-900">Administrador</h4>
                                                <p className="text-sm text-gray-600">Acceso completo al sistema</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                                                {rolesStats?.admin || 0} usuarios
                                            </span>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                <Shield className="w-5 h-5 text-yellow-600" />
                                            </div>
                                            <div>
                                                <h4 className="font-semibold text-gray-900">Guardia</h4>
                                                <p className="text-sm text-gray-600">Registro de asistencias y seguridad</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                                {rolesStats?.guardia || 0} usuarios
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Sección Mensajes y Notificaciones */}
                    {activeSection === 'mensajes' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Mensajes flash / Notificaciones</h2>
                            <p className="text-gray-600 mb-8">Configura el comportamiento de las notificaciones del sistema</p>
                            
                            {/* Colores de notificación */}
                            <div className="bg-white rounded-lg p-6 border border-gray-200 mb-6">
                                <h3 className="text-lg font-semibold text-gray-800 mb-4">Colores de Notificaciones</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Notificaciones de Éxito
                                        </label>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-green-500 rounded border border-gray-300"></div>
                                            <select
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                value={notificacionesConfig.color_success}
                                                onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, color_success: e.target.value })}
                                            >
                                                <option value="green">Verde (por defecto)</option>
                                                <option value="blue">Azul</option>
                                                <option value="purple">Púrpura</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Notificaciones de Error
                                        </label>
                                        <div className="flex items-center gap-3">
                                            <select
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                value={notificacionesConfig.color_error}
                                                onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, color_error: e.target.value })}
                                            >
                                                <option value="red">Rojo (por defecto)</option>
                                                <option value="orange">Naranja</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Notificaciones de Advertencia
                                        </label>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-yellow-500 rounded border border-gray-300"></div>
                                            <select
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                value={notificacionesConfig.color_warning}
                                                onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, color_warning: e.target.value })}
                                            >
                                                <option value="yellow">Amarillo (por defecto)</option>
                                                <option value="orange">Naranja</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Notificaciones de Información
                                        </label>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-blue-500 rounded border border-gray-300"></div>
                                            <select
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                value={notificacionesConfig.color_info}
                                                onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, color_info: e.target.value })}
                                            >
                                                <option value="blue">Azul (por defecto)</option>
                                                <option value="gray">Gris</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Configuración de comportamiento */}
                            <div className="bg-white rounded-lg p-6 border border-gray-200 mb-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Sonido de notificación</label>
                                            <p className="text-xs text-gray-500">Reproducir un sonido al mostrar notificaciones</p>
                                        </div>
                                        <input
                                            type="checkbox"
                                            className="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                            checked={notificacionesConfig.sound}
                                            onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, sound: e.target.checked })}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Animación de entrada</label>
                                            <p className="text-xs text-gray-500">Efecto visual al mostrar notificaciones</p>
                                        </div>
                                        <select
                                            className="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            value={notificacionesConfig.animation}
                                            onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, animation: e.target.value })}
                                        >
                                            <option value="slide">Deslizar</option>
                                            <option value="fade">Desvanecer</option>
                                            <option value="bounce">Rebote</option>
                                            <option value="scale">Escala</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* Duración y posición */}
                            <div className="bg-white rounded-lg p-6 border border-gray-200 mb-6">
                                <h3 className="text-lg font-semibold text-gray-800 mb-4">Comportamiento</h3>
                                
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Duración (segundos)</label>
                                            <p className="text-xs text-gray-500">Tiempo que permanecerá visible la notificación</p>
                                        </div>
                                        <input
                                            type="number"
                                            min="1"
                                            max="30"
                                            value={notificacionesConfig.duration}
                                            onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, duration: e.target.value })}
                                            className="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Posición en pantalla</label>
                                            <p className="text-xs text-gray-500">Dónde aparecerá la notificación</p>
                                        </div>
                                        <select
                                            className="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            value={notificacionesConfig.position}
                                            onChange={(e) => setNotificacionesConfig({ ...notificacionesConfig, position: e.target.value })}
                                        >
                                            <option value="top-right">Superior Derecha</option>
                                            <option value="top-left">Superior Izquierda</option>
                                            <option value="top-center">Superior Centro</option>
                                            <option value="bottom-right">Inferior Derecha</option>
                                            <option value="bottom-left">Inferior Izquierda</option>
                                            <option value="bottom-center">Inferior Centro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* Botones de acción */}
                            <div className="flex gap-4">
                                <button
                                    onClick={handleGuardarNotificaciones}
                                    className="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg"
                                    disabled={notificacionesStatus.loading}
                                >
                                    {notificacionesStatus.loading ? 'Guardando...' : 'Guardar Cambios'}
                                </button>
                                <button
                                    onClick={handleRestablecerNotificaciones}
                                    className="bg-white border border-red-300 text-red-600 hover:bg-red-50 font-medium py-3 px-6 rounded-lg"
                                    disabled={notificacionesStatus.loading}
                                >
                                    Restablecer Valores
                                </button>
                            </div>

                            {/* Mensaje de estado */}
                            {notificacionesStatus.message && (
                                <div className="mt-4 text-sm text-gray-600">
                                    {notificacionesStatus.message}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Sección Personalización del Sistema */}
                    {activeSection === 'personalizacion' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Personalización del Sistema</h2>
                            <p className="text-gray-600 mb-8">Personaliza el logo, colores y apariencia del sistema</p>
                            
                            {/* Logo Institucional */}
                            <div className="bg-white rounded-lg p-8 border border-gray-200 mb-8">
                                <h3 className="text-xl font-semibold text-gray-800 mb-6">Logo Institucional</h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                                    {/* Zona de carga */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-4">Cargar Nuevo Logo</label>
                                        <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition-all"
                                            onDragOver={(e) => {
                                                e.preventDefault();
                                                e.currentTarget.classList.add('bg-blue-50', 'border-blue-400');
                                            }}
                                            onDragLeave={(e) => {
                                                e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');
                                            }}
                                            onDrop={(e) => {
                                                e.preventDefault();
                                                e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');
                                                const file = e.dataTransfer.files[0];
                                                if (file) handleLogoUpload({target: {files: [file]}});
                                            }}
                                        >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={handleLogoUpload}
                                                className="hidden"
                                                id="logo-upload"
                                            />
                                            <label htmlFor="logo-upload" className="cursor-pointer block">
                                                <Upload className="mx-auto h-12 w-12 text-gray-400 mb-3" />
                                                <p className="text-sm font-medium text-gray-900">Haz clic para cargar o arrastra una imagen</p>
                                                <p className="text-xs text-gray-500 mt-2">PNG, JPG, SVG (máx. 2MB)</p>
                                            </label>
                                        </div>
                                        <button
                                            onClick={handleLogoReset}
                                            className="w-full mt-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors"
                                        >
                                            Restablecer Logo Predeterminado
                                        </button>
                                        {uploadStatus.message && (
                                            <div className={`mt-4 p-3 rounded-lg text-sm ${
                                                uploadStatus.message.includes('✅') 
                                                    ? 'bg-green-50 text-green-700 border border-green-200'
                                                    : 'bg-red-50 text-red-700 border border-red-200'
                                            }`}>
                                                {uploadStatus.message}
                                            </div>
                                        )}
                                    </div>
                                    
                                    {/* Vista previa del logo */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-4">Vista Previa</label>
                                        <div className="w-full h-64 bg-gray-50 border-2 border-gray-200 rounded-lg flex items-center justify-center shadow-sm">
                                            <img 
                                                src={`${currentLogoPath}?t=${logoTimestamp}`}
                                                alt="Logo actual"
                                                className="max-h-full max-w-full object-contain"
                                                onError={() => {
                                                    // Fallback si la imagen no carga
                                                }}
                                            />
                                        </div>
                                        <p className="text-xs text-gray-500 mt-4 text-center">El logo se mostrará en la esquina superior izquierda del sistema</p>
                                    </div>
                                </div>
                            </div>

                            {/* Esquema de Colores */}
                            <div className="bg-white rounded-lg p-8 border border-gray-200">
                                <h3 className="text-xl font-semibold text-gray-800 mb-2">Esquema de Colores</h3>
                                <p className="text-sm text-gray-600 mb-8">Selecciona el color principal del sistema y ve los cambios en tiempo real</p>
                                
                                {/* Paleta de colores */}
                                <div className="mb-8">
                                    <label className="block text-sm font-medium text-gray-700 mb-6">Color Principal</label>
                                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                        {Object.keys(colorMap).map((colorName) => (
                                            <button
                                                key={colorName}
                                                onClick={() => {
                                                    handleColorChange(colorName);
                                                    handleColorPreview(colorName);
                                                }}
                                                className={`p-0 rounded-lg border-4 transition-all transform hover:scale-105 ${
                                                    selectedColor === colorName
                                                        ? `border-${colorName}-700 ring-4 ring-${colorName}-200 shadow-lg`
                                                        : 'border-gray-200 hover:border-gray-400'
                                                }`}
                                                title={colorName.charAt(0).toUpperCase() + colorName.slice(1)}
                                            >
                                                <div className={`w-full h-24 bg-${colorName}-500 rounded`}></div>
                                                <div className="bg-white py-2 px-3 rounded-b text-center">
                                                    <p className="text-xs font-semibold text-gray-700 capitalize">{colorName}</p>
                                                </div>
                                            </button>
                                        ))}
                                    </div>

                                    {colorSaveStatus.message && (
                                        <div className={`mt-6 p-3 rounded-lg text-sm ${
                                            colorSaveStatus.message.includes('✅') 
                                                ? 'bg-green-50 text-green-700 border border-green-200'
                                                : 'bg-red-50 text-red-700 border border-red-200'
                                        }`}>
                                            {colorSaveStatus.message}
                                        </div>
                                    )}
                                </div>

                                {/* Vista previa de la barra de navegación */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-3">Vista Previa de Navegación</label>
                                    <div id="color-preview-bar" className="w-full h-16 rounded-lg shadow-md transition-all duration-300 border border-gray-200" style={{backgroundColor: `rgb(${colorMap[previewColor]?.[600] || colorMap.green[600]})`}}></div>
                                    <p className="text-xs text-gray-500 mt-3">Esta barra de color se aplicará a la navegación principal del sistema</p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Sección Seguridad y Contraseñas */}
                    {activeSection === 'seguridad' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Seguridad y Contraseñas</h2>
                            <p className="text-gray-600 mb-8">Configura las políticas de seguridad del sistema</p>
                            
                            {/* Estado de Seguridad */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                {/* Contraseñas Seguras */}
                                <div className="bg-green-50 border border-green-200 rounded-lg p-6">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <CheckCircle className="w-6 h-6 text-white" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-gray-800">Contraseñas Seguras</h3>
                                            <p className="text-sm text-green-700 mt-1">Activo</p>
                                        </div>
                                    </div>
                                </div>

                                {/* Autenticación 2FA */}
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <AlertTriangle className="w-6 h-6 text-white" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-gray-800">Autenticación 2FA</h3>
                                            <p className="text-sm text-yellow-700 mt-1">Opcional</p>
                                        </div>
                                    </div>
                                </div>

                                {/* Bloqueo Automático */}
                                <div className="bg-red-50 border border-red-200 rounded-lg p-6">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <Shield className="w-6 h-6 text-white" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-gray-800">Bloqueo Automático</h3>
                                            <p className="text-sm text-red-700 mt-1">Inactivo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Políticas de Contraseña */}
                            <div className="bg-white rounded-lg p-8 border border-gray-200">
                                <h3 className="text-xl font-semibold text-gray-800 mb-8">Políticas de Contraseña</h3>
                                
                                <div className="space-y-6">
                                    {/* Longitud mínima */}
                                    <div className="flex items-center justify-between pb-6 border-b border-gray-100">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Longitud mínima</label>
                                            <p className="text-xs text-gray-500 mt-1">Número mínimo de caracteres</p>
                                        </div>
                                        <select
                                            value={securityConfig.min_length}
                                            onChange={(e) => setSecurityConfig({...securityConfig, min_length: e.target.value})}
                                            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                        >
                                            <option value="6">6</option>
                                            <option value="8">8</option>
                                            <option value="10">10</option>
                                            <option value="12">12</option>
                                            <option value="16">16</option>
                                        </select>
                                    </div>

                                    {/* Incluir mayúsculas */}
                                    <div className="flex items-center justify-between pb-6 border-b border-gray-100">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Incluir mayúsculas</label>
                                            <p className="text-xs text-gray-500 mt-1">Al menos una letra mayúscula</p>
                                        </div>
                                        <label className="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={securityConfig.require_uppercase}
                                                onChange={(e) => setSecurityConfig({...securityConfig, require_uppercase: e.target.checked})}
                                                className="sr-only peer"
                                            />
                                            <div className="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>

                                    {/* Incluir números */}
                                    <div className="flex items-center justify-between pb-6 border-b border-gray-100">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Incluir números</label>
                                            <p className="text-xs text-gray-500 mt-1">Al menos un dígito</p>
                                        </div>
                                        <label className="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={securityConfig.require_numbers}
                                                onChange={(e) => setSecurityConfig({...securityConfig, require_numbers: e.target.checked})}
                                                className="sr-only peer"
                                            />
                                            <div className="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>

                                    {/* Caracteres especiales */}
                                    <div className="flex items-center justify-between pb-6 border-b border-gray-100">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Caracteres especiales</label>
                                            <p className="text-xs text-gray-500 mt-1">Al menos un símbolo (!@#$%&*)</p>
                                        </div>
                                        <label className="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={securityConfig.require_special}
                                                onChange={(e) => setSecurityConfig({...securityConfig, require_special: e.target.checked})}
                                                className="sr-only peer"
                                            />
                                            <div className="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </div>

                                    {/* Expiración de contraseña */}
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <label className="text-sm font-medium text-gray-700">Expiración de contraseña</label>
                                            <p className="text-xs text-gray-500 mt-1">Días antes de cambio obligatorio</p>
                                        </div>
                                        <select
                                            value={securityConfig.expiration_days}
                                            onChange={(e) => setSecurityConfig({...securityConfig, expiration_days: e.target.value})}
                                            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                        >
                                            <option value="30">30 días</option>
                                            <option value="60">60 días</option>
                                            <option value="90">90 días</option>
                                            <option value="180">180 días</option>
                                            <option value="365">365 días</option>
                                            <option value="0">Sin expiración</option>
                                        </select>
                                    </div>
                                </div>

                                {/* Botones de acción */}
                                <div className="flex gap-4 mt-8 pt-8 border-t border-gray-100">
                                    <button
                                        onClick={handleGuardarSeguridad}
                                        disabled={securityStatus.loading}
                                        className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium py-3 px-8 rounded-lg transition-colors"
                                    >
                                        {securityStatus.loading ? 'Guardando...' : 'Guardar Políticas'}
                                    </button>
                                    <button
                                        onClick={() => window.location.reload()}
                                        className="bg-gray-700 hover:bg-gray-800 text-white font-medium py-3 px-8 rounded-lg transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>

                                {/* Mensaje de estado */}
                                {securityStatus.message && (
                                    <div className={`mt-6 p-4 rounded-lg text-sm ${
                                        securityStatus.message.includes('✅') 
                                            ? 'bg-green-50 text-green-700 border border-green-200'
                                            : 'bg-red-50 text-red-700 border border-red-200'
                                    }`}>
                                        {securityStatus.message}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Sección Respaldo y Restauración */}
                    {activeSection === 'respaldo' && (
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800 mb-2">Respaldo y Restauración</h2>
                            <p className="text-gray-600 mb-8">Administra copias de seguridad de la base de datos</p>
                            
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                {/* Crear Respaldo */}
                                <div className="bg-white rounded-lg p-6 border border-gray-200">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <Download className="w-5 h-5 text-green-600" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-gray-800">Crear Respaldo</h3>
                                    </div>
                                    
                                    <p className="text-sm text-gray-600 mb-6">
                                        Crea una copia de seguridad completa de toda la base de datos del sistema
                                    </p>
                                    
                                    <button
                                        onClick={handleCrearRespaldo}
                                        disabled={backupStatus.loading}
                                        className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <Download size={18} />
                                        {backupStatus.loading ? 'Creando respaldo...' : 'Descargar Respaldo'}
                                    </button>

                                    {backupStatus.message && (
                                        <div className={`mt-4 p-3 rounded-lg text-sm ${
                                            backupStatus.message.includes('✅') 
                                                ? 'bg-green-50 text-green-700 border border-green-200'
                                                : 'bg-red-50 text-red-700 border border-red-200'
                                        }`}>
                                            {backupStatus.message}
                                        </div>
                                    )}
                                </div>

                                {/* Restaurar Respaldo */}
                                <div className="bg-white rounded-lg p-6 border border-gray-200">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <Upload className="w-5 h-5 text-blue-600" />
                                        </div>
                                        <h3 className="text-lg font-semibold text-gray-800">Restaurar Respaldo</h3>
                                    </div>
                                    
                                    <p className="text-sm text-gray-600 mb-6">
                                        Restaura la base de datos desde un archivo de respaldo anterior
                                    </p>
                                    
                                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition-colors"
                                        onDragOver={(e) => {
                                            e.preventDefault();
                                            e.currentTarget.classList.add('bg-blue-50', 'border-blue-400');
                                        }}
                                        onDragLeave={(e) => {
                                            e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');
                                        }}
                                        onDrop={(e) => {
                                            e.preventDefault();
                                            e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');
                                            const file = e.dataTransfer.files[0];
                                            if (file) setUploadFile(file);
                                        }}
                                    >
                                        <input
                                            type="file"
                                            accept=".sql,.zip,.tar.gz"
                                            onChange={(e) => setUploadFile(e.target.files?.[0])}
                                            className="hidden"
                                            id="backup-upload"
                                        />
                                        <label htmlFor="backup-upload" className="cursor-pointer block">
                                            <Upload className="mx-auto h-8 w-8 text-gray-400 mb-2" />
                                            <p className="text-sm font-medium text-gray-900">Arrastra un archivo o haz clic</p>
                                            <p className="text-xs text-gray-500">SQL, ZIP o TAR.GZ (máx. 500MB)</p>
                                        </label>
                                    </div>

                                    {uploadFile && (
                                        <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p className="text-sm text-blue-700 font-medium">
                                                📄 {uploadFile.name}
                                            </p>
                                            <p className="text-xs text-blue-600 mt-1">
                                                Tamaño: {(uploadFile.size / 1024 / 1024).toFixed(2)} MB
                                            </p>
                                        </div>
                                    )}

                                    <button
                                        onClick={handleStartRestore}
                                        disabled={!uploadFile || isUploading}
                                        className="w-full mt-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <Upload size={18} />
                                        {isUploading ? 'Restaurando...' : 'Restaurar Base de Datos'}
                                    </button>
                                </div>
                            </div>

                            {/* Lista de respaldos disponibles */}
                            <div className="bg-white rounded-lg border border-gray-200 mt-8">
                                <div className="px-6 py-4 border-b border-gray-200">
                                    <h3 className="text-lg font-semibold text-gray-900">Respaldos Disponibles</h3>
                                </div>
                                
                                <div className="overflow-x-auto">
                                    {backupFiles && backupFiles.length > 0 ? (
                                        <table className="w-full">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Archivo
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Tamaño
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Fecha de Creación
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Acciones
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {backupFiles.map((backup, index) => (
                                                    <tr key={index} className="hover:bg-gray-50 transition-colors">
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex items-center gap-3">
                                                                <HardDrive className="w-5 h-5 text-gray-400" />
                                                                <span className="text-sm font-medium text-gray-900">{backup.name}</span>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className="text-sm text-gray-900 font-medium">
                                                                {backup.size_formatted || formatFileSize(backup.size)}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                            {backup.date_formatted}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex items-center gap-2">
                                                                <button
                                                                    onClick={() => handleDescargarRespaldo(backup.name)}
                                                                    className="inline-flex items-center gap-1 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-medium transition-colors"
                                                                    title="Descargar este respaldo"
                                                                >
                                                                    <Download size={14} />
                                                                    Descargar
                                                                </button>
                                                                <button
                                                                    onClick={() => {
                                                                        if (confirm(`¿Está seguro de eliminar el respaldo ${backup.name}?`)) {
                                                                            handleEliminarRespaldo(backup.name);
                                                                        }
                                                                    }}
                                                                    className="inline-flex items-center gap-1 px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-medium transition-colors"
                                                                    title="Eliminar este respaldo"
                                                                >
                                                                    <Trash2 size={14} />
                                                                    Eliminar
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    ) : (
                                        <div className="text-center py-16">
                                            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <HardDrive className="w-8 h-8 text-gray-400" />
                                            </div>
                                            <h3 className="text-sm font-medium text-gray-900 mb-2">No hay respaldos disponibles</h3>
                                            <p className="text-sm text-gray-500 mb-6">
                                                Crea el primer respaldo haciendo clic en el botón "Descargar Respaldo"
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </main>
            </div>

            {/* Modal de contraseña para restauración */}
            {showPasswordModal && (
                <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4">Confirmar Restauración</h3>
                        <p className="text-sm text-gray-600 mb-6">Por favor ingresa tu contraseña para confirmar la restauración de la base de datos.</p>
                        
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                            <input
                                type="password"
                                value={restorePassword}
                                onChange={handlePasswordChange}
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                            {restorePasswordError && (
                                <p className="text-sm text-red-600 mt-2">{restorePasswordError}</p>
                            )}
                        </div>

                        <div className="flex gap-4">
                            <button
                                onClick={handleConfirmRestore}
                                disabled={isRestoringWithPassword}
                                className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium py-3 px-6 rounded-lg transition-colors"
                            >
                                {isRestoringWithPassword ? 'Restaurando...' : 'Confirmar'}
                            </button>
                            <button
                                onClick={() => setShowPasswordModal(false)}
                                className="bg-gray-700 hover:bg-gray-800 text-white font-medium py-3 px-6 rounded-lg transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </SidebarLayout>
    );
}