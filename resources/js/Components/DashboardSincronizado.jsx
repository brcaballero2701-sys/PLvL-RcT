import React, { useState, useCallback } from 'react';
import { useWebSocket } from '@/hooks/useWebSocket';

/**
 * Componente para actualizar el dashboard en tiempo real
 * Se sincroniza automáticamente cuando hay cambios
 */
export const DashboardSincronizado = ({ children }) => {
    const [datosActualizados, setDatosActualizados] = useState(null);
    const [tipoUltimaCambio, setTipoUltimaCambio] = useState(null);
    const [timestampUltimoCambio, setTimestampUltimoCambio] = useState(null);

    // Manejar actualizaciones del dashboard
    const handleDashboardActualizado = useCallback((data) => {
        console.log('Dashboard actualizado:', data);
        setDatosActualizados(data.datos);
        setTipoUltimaCambio(data.tipo);
        setTimestampUltimoCambio(data.timestamp);

        // Emitir evento personalizado para que otros componentes se actualicen
        window.dispatchEvent(new CustomEvent('dashboard:actualizado', { detail: data }));
    }, []);

    // Escuchar eventos del dashboard
    useWebSocket(
        'dashboard',
        'DashboardSincronizado',
        handleDashboardActualizado,
        true
    );

    return (
        <div>
            {/* Indicador de último cambio */}
            {tipoUltimaCambio && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p className="text-sm text-blue-800">
                        <strong>Actualización en tiempo real:</strong> {tipoUltimaCambio}
                        {timestampUltimoCambio && (
                            <span className="text-blue-600 ml-2">
                                {new Date(timestampUltimoCambio).toLocaleTimeString()}
                            </span>
                        )}
                    </p>
                </div>
            )}
            
            {children}
        </div>
    );
};

export default DashboardSincronizado;
