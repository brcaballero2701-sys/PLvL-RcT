import React, { useEffect, useState } from 'react';
import { useWebSocketStatus } from '@/hooks/useWebSocket';

/**
 * Componente para mostrar el estado de la conexión WebSocket
 */
export const WebSocketStatus = () => {
    const status = useWebSocketStatus();
    const [visible, setVisible] = useState(true);

    // Ocultar automáticamente cuando está conectado
    useEffect(() => {
        if (status === 'connected') {
            const timer = setTimeout(() => setVisible(false), 3000);
            return () => clearTimeout(timer);
        } else {
            setVisible(true);
        }
    }, [status]);

    if (!visible && status === 'connected') return null;

    const statusConfig = {
        connected: {
            bg: 'bg-green-50',
            border: 'border-green-200',
            icon: '✓',
            color: 'text-green-800',
            mensaje: 'Conectado',
        },
        disconnected: {
            bg: 'bg-yellow-50',
            border: 'border-yellow-200',
            icon: '⚠',
            color: 'text-yellow-800',
            mensaje: 'Desconectado',
        },
        failed: {
            bg: 'bg-red-50',
            border: 'border-red-200',
            icon: '✕',
            color: 'text-red-800',
            mensaje: 'Error de conexión',
        },
    };

    const config = statusConfig[status] || statusConfig.disconnected;

    return (
        <div className={`fixed bottom-4 right-4 ${config.bg} border ${config.border} rounded-lg p-3 shadow-lg max-w-xs`}>
            <div className="flex items-center gap-2">
                <span className={`${config.color} font-bold`}>{config.icon}</span>
                <p className={`${config.color} text-sm font-medium`}>
                    WebSocket: {config.mensaje}
                </p>
            </div>
        </div>
    );
};

export default WebSocketStatus;
