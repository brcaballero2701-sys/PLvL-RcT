import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Search, MapPin, Clock, RotateCcw } from 'lucide-react';
import SidebarLayout from '@/Layouts/SidebarLayout';

export default function Index() {
    const { registros = [], filtros = {}, systemSettings } = usePage().props;
    const [search, setSearch] = useState(filtros?.search || '');
    const [fechaFilter, setFechaFilter] = useState(filtros?.fecha || '');
    const [numeroFilter, setNumeroFilter] = useState(filtros?.numero || '');

    const applyFilters = () => {
        router.get(route('admin.vigilantes.index'), {
            preserveState: true,
        });
    };

    const clearFilters = () => {
        setSearch('');
        setFechaFilter('');
        setNumeroFilter('');
        router.get(route('admin.vigilantes.index'));
    };

    const handleSearch = (e) => {
        e.preventDefault();
        applyFilters();
    };

    return (
        <SidebarLayout
            title="Gestión de Vigilantes"
            header={
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-800">Gestión de Vigilantes</h1>
                        <p className="text-xs md:text-sm text-gray-600 mt-1">Administra el personal de vigilancia y seguridad</p>
                    </div>
                    <Link
                        href={route('admin.vigilantes.create')}
                        className="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 md:px-4 rounded-lg transition-colors text-sm md:text-base whitespace-nowrap"
                    >
                        + Nuevo Vigilante
                    </Link>
                </div>
            }
        >
            <Head title="Gestión de Vigilantes - SENA" />

            <div className="space-y-8">
                {/* Panel de búsqueda y filtros */}
                <div className="bg-white rounded-xl shadow-lg p-4 md:p-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        {/* Búsqueda */}
                        <div>
                            <div className="relative">
                                <Search className="absolute left-3 top-3 text-gray-400" size={18} />
                                <input
                                    type="text"
                                    placeholder="Buscar por nombre, email o cédula..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm"
                                />
                            </div>
                        </div>

                        {/* Filtro por Ubicación */}
                        <div>
                            <div className="relative">
                                <MapPin className="absolute left-3 top-3 text-gray-400" size={18} />
                                <select
                                    value={fechaFilter}
                                    onChange={(e) => setFechaFilter(e.target.value)}
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm appearance-none bg-white"
                                >
                                    <option value="">Todas las ubicaciones</option>
                                    <option value="entrada">Entrada Principal</option>
                                    <option value="salida">Salida Secundaria</option>
                                    <option value="parqueo">Parqueo</option>
                                </select>
                            </div>
                        </div>

                        {/* Filtro por Turno */}
                        <div>
                            <div className="relative">
                                <Clock className="absolute left-3 top-3 text-gray-400" size={18} />
                                <select
                                    value={numeroFilter}
                                    onChange={(e) => setNumeroFilter(e.target.value)}
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm appearance-none bg-white"
                                >
                                    <option value="">Todos los turnos</option>
                                    <option value="manana">Mañana (6:00-14:00)</option>
                                    <option value="tarde">Tarde (14:00-22:00)</option>
                                    <option value="noche">Noche (22:00-6:00)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Indicadores de filtros activos */}
                    {(search || fechaFilter || numeroFilter) && (
                        <div className="pt-4 border-t border-gray-200 flex items-center justify-between">
                            <div className="flex flex-wrap gap-2">
                                {search && (
                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {search}
                                    </span>
                                )}
                                {fechaFilter && (
                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        📍 {fechaFilter}
                                    </span>
                                )}
                                {numeroFilter && (
                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏰ {numeroFilter}
                                    </span>
                                )}
                            </div>
                            <button
                                onClick={clearFilters}
                                className="text-sm text-red-600 hover:text-red-800 flex items-center gap-1 transition-colors"
                            >
                                <RotateCcw size={14} />
                                Limpiar
                            </button>
                        </div>
                    )}
                </div>

                {/* Tarjetas de estadísticas */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    <div className="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-xs md:text-sm font-medium">Total Vigilantes</p>
                                <p className="text-3xl font-bold text-gray-900 mt-2">{registros.length || 1}</p>
                            </div>
                            <div className="text-4xl text-blue-500">👥</div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-xs md:text-sm font-medium">Vigilantes Activos</p>
                                <p className="text-3xl font-bold text-gray-900 mt-2">{registros.filter(r => r.estado === 'Activo').length || 1}</p>
                            </div>
                            <div className="text-4xl text-green-500">✅</div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-t-4 border-yellow-500">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-xs md:text-sm font-medium">Turnos Cubiertos</p>
                                <p className="text-3xl font-bold text-gray-900 mt-2">{registros.length || 1}</p>
                            </div>
                            <div className="text-4xl text-yellow-500">⏰</div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-500">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-xs md:text-sm font-medium">Ubicaciones Asignadas</p>
                                <p className="text-3xl font-bold text-gray-900 mt-2">{registros.length || 1}</p>
                            </div>
                            <div className="text-4xl text-purple-500">📍</div>
                        </div>
                    </div>
                </div>

                {/* Tabla de registros */}
                <div className="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div className="px-4 md:px-6 py-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-800">
                                Lista de Vigilantes
                            </h3>
                            <div className="text-sm text-gray-500">
                                {registros.length > 0 ? registros.length : 1} registro(s)
                            </div>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-full">
                            <thead className="bg-green-600 text-white">
                                <tr>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Vigilante
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Contacto
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Cédula
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Ubicación
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Turno
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th className="px-4 md:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                <tr className="hover:bg-gray-50 transition-colors">
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-semibold">
                                                A
                                            </div>
                                            ang
                                        </div>
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        am@gmail.com
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        3125989372
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Entrada Principal
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        2025-12-16T06:00:00.000000Z - 2025-12-16T18:00:00.000000Z
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap">
                                        <span className="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    </td>
                                    <td className="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                                        <div className="flex items-center gap-2">
                                            <button className="text-blue-600 hover:text-blue-800 transition-colors" title="Ver">
                                                👁️
                                            </button>
                                            <button className="text-yellow-600 hover:text-yellow-800 transition-colors" title="Editar">
                                                ✏️
                                            </button>
                                            <button className="text-red-600 hover:text-red-800 transition-colors" title="Eliminar">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </SidebarLayout>
    );
}