import { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import useSystemColors from '@/hooks/useSystemColors';
import { 
    ArrowLeft, 
    Download, 
    Upload, 
    HardDrive, 
    Database, 
    Calendar,
    User,
    AlertTriangle,
    CheckCircle,
    Trash2,
    FileArchive
} from 'lucide-react';

export default function RespaldoRestauracion() {
    const { colors } = useSystemColors();
    const [backups, setBackups] = useState([]);
    const [diskSpace, setDiskSpace] = useState(null);
    const [databaseSize, setDatabaseSize] = useState(null);
    const [isGenerating, setIsGenerating] = useState(false);
    const [uploadFile, setUploadFile] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const [showConfirmRestore, setShowConfirmRestore] = useState(false);
    const [restoreData, setRestoreData] = useState(null);
    const [password, setPassword] = useState('');
    const [isRestoring, setIsRestoring] = useState(false);
    const [message, setMessage] = useState({ type: '', text: '' });

    // Cargar respaldos al iniciar
    useEffect(() => {
        cargarRespaldos();
    }, []);

    const cargarRespaldos = async () => {
        try {
            const response = await fetch(route('backup.list'));
            const data = await response.json();
            
            if (data.success) {
                setBackups(data.backups || []);
            }
        } catch (error) {
            console.error('Error al cargar respaldos:', error);
        }
    };

    const handleGenerateBackup = async () => {
        if (!confirm('¿Deseas crear un respaldo de la base de datos ahora?')) return;
        
        setIsGenerating(true);
        setMessage({ type: '', text: '' });
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('backup.generate'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                setMessage({ type: 'success', text: '✅ Respaldo creado exitosamente' });
                // Recargar la lista sin actualizar toda la página
                await cargarRespaldos();
                setTimeout(() => setMessage({ type: '', text: '' }), 3000);
            } else {
                setMessage({ type: 'error', text: '❌ ' + (data.message || 'Error al crear respaldo') });
            }
        } catch (error) {
            setMessage({ type: 'error', text: '❌ Error: ' + error.message });
        } finally {
            setIsGenerating(false);
        }
    };

    const handleFileUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            if (!file.name.endsWith('.sql')) {
                setMessage({ type: 'error', text: '❌ Por favor selecciona un archivo .sql válido' });
                return;
            }
            if (file.size > 100 * 1024 * 1024) { // 100MB
                setMessage({ type: 'error', text: '❌ El archivo es demasiado grande. Tamaño máximo: 100MB' });
                return;
            }
            setUploadFile(file);
            setMessage({ type: '', text: '' });
        }
    };

    const handleUploadRestore = async () => {
        if (!uploadFile) {
            setMessage({ type: 'error', text: '❌ Por favor selecciona un archivo primero' });
            return;
        }

        setIsUploading(true);
        const formData = new FormData();
        formData.append('backup_file', uploadFile);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('backup.upload'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                setRestoreData({
                    filename: data.filename,
                    backup_name: uploadFile.name
                });
                setShowConfirmRestore(true);
                setMessage({ type: 'success', text: '✅ Archivo subido. Confirma la restauración.' });
            } else {
                setMessage({ type: 'error', text: '❌ ' + (data.message || 'Error al subir archivo') });
            }
        } catch (error) {
            setMessage({ type: 'error', text: '❌ Error: ' + error.message });
        } finally {
            setIsUploading(false);
        }
    };

    const confirmRestore = async () => {
        if (!password) {
            setMessage({ type: 'error', text: '❌ Debes ingresar tu contraseña para restaurar' });
            return;
        }

        setIsRestoring(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('backup.restore'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    filename: restoreData.filename,
                    password: password
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setMessage({ type: 'success', text: '✅ Base de datos restaurada exitosamente. Recargando...' });
                setShowConfirmRestore(false);
                setRestoreData(null);
                setPassword('');
                setUploadFile(null);
                
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                setMessage({ type: 'error', text: '❌ ' + (data.message || 'Error al restaurar') });
            }
        } catch (error) {
            setMessage({ type: 'error', text: '❌ Error: ' + error.message });
        } finally {
            setIsRestoring(false);
        }
    };

    const handleDescargarRespaldo = async (filename) => {
        try {
            setMessage({ type: '', text: 'Descargando...' });
            const response = await fetch(route('backup.download', { filename: filename }), {
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
            
            setMessage({ type: 'success', text: '✅ Respaldo descargado correctamente' });
            setTimeout(() => setMessage({ type: '', text: '' }), 2000);
        } catch (error) {
            setMessage({ type: 'error', text: '❌ Error: ' + error.message });
        }
    };

    const handleEliminarRespaldo = async (filename) => {
        if (!confirm('¿Estás seguro de que deseas eliminar este respaldo? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('backup.delete', { filename: filename }), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            
            if (data.success) {
                setMessage({ type: 'success', text: '✅ Respaldo eliminado correctamente' });
                // Actualizar lista sin recargar página
                setBackups(backups.filter(b => b.filename !== filename));
                setTimeout(() => setMessage({ type: '', text: '' }), 2000);
            } else {
                setMessage({ type: 'error', text: '❌ ' + (data.message || 'Error al eliminar') });
            }
        } catch (error) {
            setMessage({ type: 'error', text: '❌ Error: ' + error.message });
        }
    };

    const formatDate = (dateString) => {
        if (typeof dateString === 'number') {
            return new Date(dateString * 1000).toLocaleString('es-ES');
        }
        return new Date(dateString).toLocaleString('es-ES');
    };

    const formatFileSize = (bytes) => {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${size.toFixed(1)} ${units[unitIndex]}`;
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title="Respaldo y Restauración - SENA" />
            
            {/* Header */}
            <div className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center py-6">
                        <div className="flex items-center">
                            <Link 
                                href={route('configuraciones')}
                                className="mr-4 p-2 text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <ArrowLeft size={20} />
                            </Link>
                            <div className="flex items-center">
                                <FileArchive className={`mr-3 ${colors.primaryText}`} size={24} />
                                <div>
                                    <h1 className="text-3xl font-bold text-gray-900">
                                        Respaldo y Restauración
                                    </h1>
                                    <p className="text-gray-600 mt-1">
                                        Gestiona los respaldos de tu base de datos
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Link
                            href={route('configuraciones')}
                            className={`px-4 py-2 ${colors.primary} text-white rounded-md hover:opacity-90 transition-opacity`}
                        >
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            {/* Mensaje de estado */}
            {message.text && (
                <div className={`max-w-7xl mx-auto mt-4 px-4 py-3 rounded-lg ${
                    message.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' :
                    message.type === 'error' ? 'bg-red-50 border border-red-200 text-red-800' :
                    'bg-blue-50 border border-blue-200 text-blue-800'
                }`}>
                    {message.text}
                </div>
            )}

            <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Panel Principal */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Sección de Respaldo */}
                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <Download className="mr-2 text-green-600" size={20} />
                                Crear Respaldo
                            </h3>
                            
                            <div className="space-y-4">
                                <p className="text-gray-600 text-sm">
                                    Genera un respaldo completo de la base de datos. Se descargará automáticamente 
                                    cuando esté listo.
                                </p>
                                
                                <button
                                    onClick={handleGenerateBackup}
                                    disabled={isGenerating}
                                    className={`w-full flex items-center justify-center px-4 py-3 ${colors.primary} text-white rounded-md hover:opacity-90 transition-opacity disabled:opacity-50 font-medium`}
                                >
                                    <Download className="mr-2" size={16} />
                                    {isGenerating ? '⏳ Generando Respaldo...' : '📦 Generar Respaldo Ahora'}
                                </button>
                            </div>
                        </div>

                        {/* Sección de Restauración */}
                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <Upload className="mr-2 text-orange-600" size={20} />
                                Restaurar Base de Datos
                            </h3>
                            
                            <div className="space-y-4">
                                <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div className="flex items-start">
                                        <AlertTriangle className="mr-2 text-yellow-600 mt-0.5" size={16} />
                                        <div>
                                            <p className="text-sm font-medium text-yellow-800">
                                                ⚠️ Advertencia Importante
                                            </p>
                                            <p className="text-xs text-yellow-700 mt-1">
                                                Esta acción sobrescribirá TODOS los datos actuales del sistema.
                                                Asegúrate de tener un respaldo de seguridad antes de proceder.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <label className="block text-sm font-medium text-gray-700">
                                        Archivo SQL a Restaurar
                                    </label>
                                    <input
                                        type="file"
                                        accept=".sql"
                                        onChange={handleFileUpload}
                                        className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    {uploadFile && (
                                        <p className="text-xs text-green-600">
                                            ✅ Archivo seleccionado: {uploadFile.name} ({formatFileSize(uploadFile.size)})
                                        </p>
                                    )}
                                </div>

                                <button
                                    onClick={handleUploadRestore}
                                    disabled={!uploadFile || isUploading}
                                    className="w-full flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors disabled:opacity-50 font-medium"
                                >
                                    <Upload className="mr-2" size={16} />
                                    {isUploading ? '⏳ Subiendo...' : '📤 Subir y Restaurar'}
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Panel de Historial */}
                    <div className="space-y-6">
                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <Calendar className="mr-2 text-blue-600" size={20} />
                                Respaldos Disponibles
                            </h3>
                            
                            <div className="space-y-2 max-h-96 overflow-y-auto">
                                {backups.length > 0 ? (
                                    backups.map((backup, index) => (
                                        <div key={index} className="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                            <div className="flex justify-between items-start mb-2">
                                                <div className="flex-1">
                                                    <p className="text-xs font-medium text-gray-900 break-all">
                                                        {backup.filename}
                                                    </p>
                                                    <p className="text-xs text-gray-500 mt-1">
                                                        📅 {formatDate(backup.date)}
                                                    </p>
                                                    <p className="text-xs text-gray-500">
                                                        💾 {backup.size_formatted || formatFileSize(backup.size)}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex gap-2 mt-2">
                                                <button
                                                    onClick={() => handleDescargarRespaldo(backup.filename)}
                                                    className="flex-1 text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors flex items-center justify-center gap-1"
                                                    title="Descargar"
                                                >
                                                    <Download size={12} />
                                                    Descargar
                                                </button>
                                                <button
                                                    onClick={() => handleEliminarRespaldo(backup.filename)}
                                                    className="flex-1 text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors flex items-center justify-center gap-1"
                                                    title="Eliminar"
                                                >
                                                    <Trash2 size={12} />
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-gray-500 text-center py-8">
                                        No hay respaldos disponibles
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Información del Sistema */}
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 className="font-semibold text-blue-900 mb-2 text-sm">
                                ℹ️ Consejos Útiles
                            </h3>
                            <ul className="space-y-1 text-xs text-blue-800">
                                <li>✓ Respaldo automático incluye toda la BD</li>
                                <li>✓ Descarga permite elegir ubicación</li>
                                <li>✓ Restaura desde cualquier .sql</li>
                                <li>✓ Respaldos seguros y encriptados</li>
                                <li>✓ Recomendado: respaldo semanal</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de Confirmación de Restauración */}
            {showConfirmRestore && restoreData && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg p-6 max-w-md w-full">
                        <div className="flex items-center mb-4">
                            <AlertTriangle className="mr-3 text-red-600" size={24} />
                            <h3 className="text-lg font-bold text-gray-900">
                                Confirmar Restauración
                            </h3>
                        </div>
                        
                        <div className="mb-4 p-3 bg-gray-50 rounded">
                            <p className="text-sm text-gray-600 mb-2">
                                Archivo a restaurar:
                            </p>
                            <p className="text-sm font-medium text-gray-900 break-all">
                                {restoreData.backup_name}
                            </p>
                        </div>

                        <div className="bg-red-50 border border-red-200 p-3 rounded mb-4">
                            <p className="text-sm text-red-800 font-medium">
                                ⚠️ ¡ADVERTENCIA! Esta acción es irreversible
                            </p>
                        </div>

                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Ingresa tu contraseña para confirmar:
                            </label>
                            <input
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && confirmRestore()}
                                placeholder="Tu contraseña"
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                autoFocus
                            />
                        </div>

                        <div className="flex gap-3">
                            <button
                                onClick={() => {
                                    setShowConfirmRestore(false);
                                    setPassword('');
                                }}
                                className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors font-medium"
                            >
                                Cancelar
                            </button>
                            <button
                                onClick={confirmRestore}
                                disabled={isRestoring || !password}
                                className="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors font-medium disabled:opacity-50"
                            >
                                {isRestoring ? '⏳ Restaurando...' : '✓ Confirmar'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}