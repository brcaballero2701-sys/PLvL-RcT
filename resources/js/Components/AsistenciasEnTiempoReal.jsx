import React, { useEffect, useState, useCallback } from 'react';
import { useWebSocket } from '@/hooks/useWebSocket';

/**
 * Componente para sincronizar asistencias en tiempo real
 * Escucha eventos de asistencias registradas y actualizadas
 */
export const AsistenciasEnTiempoReal = ({ instructorId, onNuevaAsistencia, onAsistenciaActualizada }) => {
    const [estadoConexion, setEstadoConexion] = useState('desconectado');
    const [ultimaAsistencia, setUltimaAsistencia] = useState(null);

    // Manejar nueva asistencia registrada
    const handleNuevaAsistencia = useCallback((data) => {
        console.log('Nueva asistencia registrada:', data);
        setUltimaAsistencia(data);
        setEstadoConexion('conectado');
        
        if (onNuevaAsistencia) {
            onNuevaAsistencia(data);
        }
    }, [onNuevaAsistencia]);

    // Manejar asistencia actualizada
    const handleAsistenciaActualizada = useCallback((data) => {
        console.log('Asistencia actualizada:', data);
        setEstadoConexion('conectado');
        
        if (onAsistenciaActualizada) {
            onAsistenciaActualizada(data);
        }
    }, [onAsistenciaActualizada]);

    // Escuchar asistencias específicas del instructor
    useWebSocket(
        `asistencias.${instructorId}`,
        'AsistenciaRegistrada',
        handleNuevaAsistencia,
        true
    );

    // Escuchar actualizaciones de asistencias
    useWebSocket(
        `asistencias.${instructorId}`,
        'AsistenciaActualizada',
        handleAsistenciaActualizada,
        true
    );

    return (
        <div className="flex items-center gap-2 text-sm">
            <div className={`w-3 h-3 rounded-full ${
                estadoConexion === 'conectado' 
                    ? 'bg-green-500' 
                    : 'bg-gray-400'
            }`}></div>
            <span className="text-gray-600">
                {estadoConexion === 'conectado' 
                    ? 'Sincronizado en tiempo real' 
                    : 'Conectando...'}
            </span>
            {ultimaAsistencia && (
                <span className="text-xs text-gray-500">
                    Última: {ultimaAsistencia.instructor_name}
                </span>
            )}
        </div>
    );
};

export default AsistenciasEnTiempoReal;
