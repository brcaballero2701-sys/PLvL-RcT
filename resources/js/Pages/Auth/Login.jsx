import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Login({ status, canResetPassword }) {
    const [showPassword, setShowPassword] = useState(false);
    const [generalError, setGeneralError] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    /**
     * Mostrar error general cuando hay errores de autenticación
     */
    useEffect(() => {
        if (errors.email) {
            setGeneralError(errors.email);
        } else {
            setGeneralError(null);
        }
    }, [errors.email]);

    const submit = (e) => {
        e.preventDefault();
        setGeneralError(null);

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    const togglePasswordVisibility = () => {
        setShowPassword(!showPassword);
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-green-400 to-green-600 flex flex-col justify-center items-center px-4">
            <Head title="Sistema de Control de Asistencia - SENA" />

            {/* Contenedor principal del formulario */}
            <div className="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
                {/* Logo del SENA - Solo texto */}
                <div className="text-center mb-8">
                    <h1 className="text-5xl font-bold text-green-600 tracking-wider">SENA</h1>
                </div>

                {/* Mensaje de estado si existe */}
                {status && (
                    <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm font-medium text-green-700">
                        {status}
                    </div>
                )}

                {/* Banner de error general (autenticación fallida) */}
                {generalError && (
                    <div className="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div className="flex items-start">
                            <svg className="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                            </svg>
                            <p className="ml-3 text-sm text-red-700 font-medium">
                                {generalError}
                            </p>
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    {/* Campo Usuario */}
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                            Usuario
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className={`w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:border-transparent transition-colors ${
                                errors.email
                                    ? 'border-red-500 focus:ring-red-500'
                                    : 'border-gray-300 focus:ring-green-500'
                            }`}
                            autoComplete="username"
                            autoFocus
                            placeholder="usuario@sena.edu.co"
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    {/* Campo Contraseña con botón para mostrar/ocultar */}
                    <div>
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div className="relative">
                            <input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                name="password"
                                value={data.password}
                                className={`w-full px-4 py-3 pr-12 border rounded-lg focus:ring-2 focus:border-transparent transition-colors ${
                                    errors.password
                                        ? 'border-red-500 focus:ring-red-500'
                                        : 'border-gray-300 focus:ring-green-500'
                                }`}
                                autoComplete="current-password"
                                placeholder="••••••••••"
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                            <button
                                type="button"
                                onClick={togglePasswordVisibility}
                                className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                            >
                                {showPassword ? (
                                    // Ícono de ojo cerrado (ocultar contraseña)
                                    <svg 
                                        className="w-5 h-5" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                        title="Ocultar contraseña"
                                    >
                                        <path 
                                            strokeLinecap="round" 
                                            strokeLinejoin="round" 
                                            strokeWidth={2} 
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" 
                                        />
                                    </svg>
                                ) : (
                                    // Ícono de ojo abierto (mostrar contraseña)
                                    <svg 
                                        className="w-5 h-5" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                        title="Mostrar contraseña"
                                    >
                                        <path 
                                            strokeLinecap="round" 
                                            strokeLinejoin="round" 
                                            strokeWidth={2} 
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" 
                                        />
                                        <path 
                                            strokeLinecap="round" 
                                            strokeLinejoin="round" 
                                            strokeWidth={2} 
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.275 4.057-5.065 7-9.543 7-4.477 0-8.268-2.943-9.542-7z" 
                                        />
                                    </svg>
                                )}
                            </button>
                        </div>
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    {/* Checkbox Recordarme */}
                    <div className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="ml-2 text-sm text-gray-600">
                            Recordarme
                        </span>
                    </div>

                    {/* Botón de inicio de sesión */}
                    <PrimaryButton
                        className="w-full bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-800 justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled={processing}
                    >
                        {processing ? (
                            <span className="flex items-center">
                                <svg className="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Iniciando Sesión...
                            </span>
                        ) : (
                            'Iniciar Sesión'
                        )}
                    </PrimaryButton>

                    {/* Enlace de contraseña olvidada */}
                    {canResetPassword && (
                        <div className="text-center">
                            <Link
                                href={route('password.request')}
                                className="text-sm text-green-600 hover:text-green-700 hover:underline"
                            >
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>
                    )}

                    {/* Enlace de registro */}
                    <div className="text-center pt-4 border-t border-gray-200">
                        <span className="text-sm text-gray-600">
                            ¿No tienes cuenta?{' '}
                            <Link
                                href={route('register')}
                                className="text-green-600 hover:text-green-700 font-medium hover:underline transition-colors"
                            >
                                Regístrate aquí
                            </Link>
                        </span>
                    </div>
                </form>
            </div>

            {/* Footer */}
            <div className="mt-8 text-center text-white text-sm">
                <p className="font-medium">Sistema de Control de Asistencia de Instructores</p>
                <p className="mt-1 opacity-90">SENA - Servicio Nacional de Aprendizaje</p>
            </div>
        </div>
    );
}
