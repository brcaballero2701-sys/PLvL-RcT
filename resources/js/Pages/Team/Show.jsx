import { Head, Link } from '@inertiajs/react';
import SidebarLayout from '@/Layouts/SidebarLayout';
import { Mail, Phone, IdCard, Briefcase } from 'lucide-react';

export default function TeamShow({ teamMembers }) {
    const groupedByRole = {};
    
    teamMembers.forEach(member => {
        if (!groupedByRole[member.rol]) {
            groupedByRole[member.rol] = [];
        }
        groupedByRole[member.rol].push(member);
    });

    const getRoleColor = (rol) => {
        if (rol.includes('Instructora')) return 'from-purple-500 to-pink-500';
        return 'from-blue-500 to-cyan-500';
    };

    const getRoleIcon = (rol) => {
        if (rol.includes('Instructora')) return '👩‍🏫';
        return '👨‍💻';
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <Head title="Equipo - Sistema SENA" />

            {/* Header */}
            <div className="bg-gradient-to-r from-green-600 to-green-700 text-white py-12">
                <div className="max-w-6xl mx-auto px-4">
                    <h1 className="text-4xl font-bold mb-2">Nuestro Equipo</h1>
                    <p className="text-green-100 text-lg">
                        Profesionales dedicados al desarrollo del Sistema de Control de Asistencia
                    </p>
                </div>
            </div>

            {/* Contenido Principal */}
            <div className="max-w-6xl mx-auto px-4 py-12">
                {Object.entries(groupedByRole).map(([rol, members]) => (
                    <div key={rol} className="mb-12">
                        <div className="mb-8">
                            <h2 className="text-3xl font-bold text-gray-800 mb-2">
                                {getRoleIcon(rol)} {rol}
                            </h2>
                            <div className={`h-1 w-20 bg-gradient-to-r ${getRoleColor(rol)}`}></div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {members.map((member) => (
                                <div
                                    key={member.id}
                                    className="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                                >
                                    {/* Card Header con Gradient */}
                                    <div className={`h-24 bg-gradient-to-r ${getRoleColor(member.rol)}`}></div>

                                    {/* Card Body */}
                                    <div className="px-6 py-4">
                                        {/* Avatar */}
                                        <div className="flex justify-center -mt-12 mb-4">
                                            <div className="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                                <span className="text-3xl">
                                                    {getRoleIcon(member.rol)}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Info */}
                                        <h3 className="text-xl font-bold text-gray-800 text-center mb-1">
                                            {member.nombre}
                                        </h3>
                                        <p className="text-sm text-green-600 font-semibold text-center mb-4">
                                            {member.rol}
                                        </p>

                                        {member.descripcion && (
                                            <p className="text-sm text-gray-600 text-center mb-4">
                                                {member.descripcion}
                                            </p>
                                        )}

                                        {/* Datos de Contacto */}
                                        <div className="space-y-3 border-t pt-4">
                                            {member.celular && (
                                                <div className="flex items-center gap-3 text-sm">
                                                    <Phone size={16} className="text-green-600" />
                                                    <a
                                                        href={`tel:${member.celular}`}
                                                        className="text-gray-700 hover:text-green-600 transition-colors"
                                                    >
                                                        {member.celular}
                                                    </a>
                                                </div>
                                            )}

                                            {member.email && (
                                                <div className="flex items-center gap-3 text-sm">
                                                    <Mail size={16} className="text-green-600" />
                                                    <a
                                                        href={`mailto:${member.email}`}
                                                        className="text-gray-700 hover:text-green-600 transition-colors truncate"
                                                    >
                                                        {member.email}
                                                    </a>
                                                </div>
                                            )}

                                            {member.cedula && (
                                                <div className="flex items-center gap-3 text-sm">
                                                    <IdCard size={16} className="text-green-600" />
                                                    <span className="text-gray-700">{member.cedula}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>

            {/* Footer */}
            <div className="bg-gray-800 text-gray-300 py-8 mt-12">
                <div className="max-w-6xl mx-auto px-4 text-center">
                    <p className="mb-4">
                        Sistema de Control de Asistencia de Instructores - SENA
                    </p>
                    <div className="flex justify-center gap-6 text-sm">
                        <Link href="/" className="hover:text-white transition-colors">
                            Inicio
                        </Link>
                        <Link href="/login" className="hover:text-white transition-colors">
                            Ingresar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
