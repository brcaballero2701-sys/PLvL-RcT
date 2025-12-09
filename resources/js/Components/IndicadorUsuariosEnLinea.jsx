import React, { useEffect, useState } from 'react';
import { useWebSocketMultiEvent } from '@/hooks/useWebSocket';

/**
 * Componente para mostrar usuarios en línea
 * Sincroniza el estado de conexión de todos los usuarios
 */
export const IndicadorUsuariosEnLinea = () => {
    const [usuariosEnLinea, setUsuariosEnLinea] = useState([]);
    const [conteo, setConteo] = useState(0);

    // Manejar cambios en usuarios en línea
    const handleUsuarioEnLinea = (data) => {
        console.log('Cambio de estado de usuario:', data);
        
        setUsuariosEnLinea(prevUsuarios => {
            const usuarioExistente = prevUsuarios.findIndex(u => u.user_id === data.user_id);
            
            if (data.estado === 'online') {
                if (usuarioExistente >= 0) {
                    const nuevosUsuarios = [...prevUsuarios];
                    nuevosUsuarios[usuarioExistente] = data;
                    return nuevosUsuarios;
                }
                return [...prevUsuarios, data];
            } else {
                // Offline
                if (usuarioExistente >= 0) {
                    return prevUsuarios.filter(u => u.user_id !== data.user_id);
                }
                return prevUsuarios;
            }
        });
    };

    // Escuchar eventos de usuarios en línea
    useWebSocketMultiEvent(
        'usuarios',
        { 'UsuarioEnLinea': handleUsuarioEnLinea },
        true
    );

    // Actualizar conteo cuando cambien los usuarios
    useEffect(() => {
        setConteo(usuariosEnLinea.length);
    }, [usuariosEnLinea]);

    return (
        <div className="bg-white rounded-lg shadow p-4">
            <h3 className="text-sm font-semibold text-gray-700 mb-3">
                Usuarios En Línea ({conteo})
            </h3>
            
            {usuariosEnLinea.length === 0 ? (
                <p className="text-sm text-gray-500">No hay usuarios en línea</p>
            ) : (
                <ul className="space-y-2">
                    {usuariosEnLinea.map(usuario => (
                        <li 
                            key={usuario.user_id} 
                            className="flex items-center gap-2 text-sm"
                        >
                            <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span className="text-gray-700">{usuario.nombre}</span>
                            <span className="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                {usuario.rol}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default IndicadorUsuariosEnLinea;
