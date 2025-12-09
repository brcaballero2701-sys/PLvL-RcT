import { useEffect, useRef, useCallback } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Hook personalizado para manejar WebSockets con Laravel Echo
 * Permite escuchar eventos en canales privados
 * 
 * @param {string} channel - Nombre del canal a escuchar
 * @param {string} event - Nombre del evento a escuchar
 * @param {function} callback - Función a ejecutar cuando se recibe el evento
 * @param {boolean} isPrivate - Si es un canal privado (default: true)
 */
export const useWebSocket = (channel, event, callback, isPrivate = true) => {
    const echoRef = useRef(null);
    const subscriptionRef = useRef(null);

    useEffect(() => {
        // Inicializar Echo si no existe
        if (!echoRef.current) {
            echoRef.current = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                encrypted: true,
            });
        }

        const echo = echoRef.current;

        try {
            // Suscribirse al canal
            if (isPrivate) {
                subscriptionRef.current = echo.private(channel);
            } else {
                subscriptionRef.current = echo.channel(channel);
            }

            // Escuchar el evento
            subscriptionRef.current.listen(event, callback);

            // Cleanup
            return () => {
                if (subscriptionRef.current) {
                    echo.leaveChannel(channel);
                    subscriptionRef.current = null;
                }
            };
        } catch (error) {
            console.error('Error al suscribirse al canal WebSocket:', error);
        }
    }, [channel, event, callback, isPrivate]);

    return echoRef.current;
};

/**
 * Hook para escuchar múltiples eventos en un mismo canal
 * 
 * @param {string} channel - Nombre del canal
 * @param {object} events - Objeto con {nombreEvento: callback}
 * @param {boolean} isPrivate - Si es un canal privado
 */
export const useWebSocketMultiEvent = (channel, events, isPrivate = true) => {
    const echoRef = useRef(null);
    const subscriptionRef = useRef(null);

    useEffect(() => {
        if (!echoRef.current) {
            echoRef.current = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                encrypted: true,
            });
        }

        const echo = echoRef.current;

        try {
            if (isPrivate) {
                subscriptionRef.current = echo.private(channel);
            } else {
                subscriptionRef.current = echo.channel(channel);
            }

            // Registrar todos los eventos
            Object.entries(events).forEach(([eventName, callback]) => {
                subscriptionRef.current.listen(eventName, callback);
            });

            return () => {
                if (subscriptionRef.current) {
                    echo.leaveChannel(channel);
                    subscriptionRef.current = null;
                }
            };
        } catch (error) {
            console.error('Error al suscribirse al canal WebSocket:', error);
        }
    }, [channel, events, isPrivate]);

    return echoRef.current;
};

/**
 * Hook para monitorear el estado de conexión
 */
export const useWebSocketStatus = () => {
    const [status, setStatus] = React.useState('disconnected');
    const echoRef = useRef(null);

    useEffect(() => {
        if (!echoRef.current) {
            echoRef.current = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                encrypted: true,
            });
        }

        const echo = echoRef.current;
        const pusher = echo.connector.pusher;

        // Escuchar cambios de estado
        pusher.connection.bind('connected', () => {
            setStatus('connected');
        });

        pusher.connection.bind('disconnected', () => {
            setStatus('disconnected');
        });

        pusher.connection.bind('failed', () => {
            setStatus('failed');
        });

        return () => {
            pusher.connection.unbind('connected');
            pusher.connection.unbind('disconnected');
            pusher.connection.unbind('failed');
        };
    }, []);

    return status;
};
