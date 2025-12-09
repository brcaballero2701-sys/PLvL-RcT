<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Instructor;
use App\Models\Asistencia;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with statistics
     */
    public function dashboard(): Response
    {
        // Estadísticas de usuarios
        $userStats = [
            'totalUsers' => User::count(),
            'totalGuardias' => User::where('role', 'guardia')->count(),
            'activeUsers' => User::where('email_verified_at', '!=', null)->count(),
            'weekAsistencias' => Asistencia::whereBetween('fecha_hora_registro', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('tipo_movimiento', 'entrada')
                ->distinct('instructor_id')
                ->count(),
            'todayAsistencias' => Asistencia::whereDate('fecha_hora_registro', today())
                ->where('tipo_movimiento', 'entrada')
                ->distinct('instructor_id')
                ->count(),
        ];

        // Obtener instructores reales
        $instructores = Instructor::with(['asistencias' => function($query) {
            $query->whereDate('fecha_hora_registro', today())
                  ->orderBy('fecha_hora_registro', 'desc');
        }])->get();

        // Estadísticas de asistencias reales
        $totalInstructores = Instructor::count();
        $presentesHoy = Asistencia::whereDate('fecha_hora_registro', today())
                                  ->where('tipo_movimiento', 'entrada')
                                  ->distinct('instructor_id')
                                  ->count();
        $llegadasTarde = Asistencia::whereDate('fecha_hora_registro', today())
                                  ->where('tipo_movimiento', 'entrada')
                                  ->where('es_tardanza', true)
                                  ->count();
        $ausentesHoy = max(0, $totalInstructores - $presentesHoy);

        $asistenciasStats = [
            'total_hoy' => Asistencia::whereDate('fecha_hora_registro', today())->count(),
            'total_instructores' => $totalInstructores,
            'puntuales' => Asistencia::whereDate('fecha_hora_registro', today())
                                   ->where('tipo_movimiento', 'entrada')
                                   ->where('es_tardanza', false)
                                   ->count(),
            'tarde' => $llegadasTarde,
            'ausencias' => $ausentesHoy
        ];

        // Usuarios recientes
        $recentUsers = User::latest('created_at')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role === 'admin' ? 'Administrador' : ($user->role === 'guardia' ? 'Guardia' : 'Usuario'),
                    'created_at' => Carbon::parse($user->created_at)->format('d/m/Y H:i')
                ];
            });

        // Asistencias recientes
        $allAsistencias = Asistencia::with('instructor')
            ->whereDate('fecha_hora_registro', today())
            ->orderBy('fecha_hora_registro', 'desc')
            ->limit(20)
            ->get();

        // Agrupar por instructor_id para combinar entrada y salida
        $agrupadasHoy = $allAsistencias->groupBy('instructor_id');
        
        $recentAsistencias = $agrupadasHoy->map(function($grupo) {
            $entrada = $grupo->firstWhere('tipo_movimiento', 'entrada');
            $salida = $grupo->firstWhere('tipo_movimiento', 'salida');
            
            // Usar el registro de entrada si existe, sino el de salida
            $registroPrincipal = $entrada ?? $salida;
            
            if (!$registroPrincipal) {
                return null;
            }
            
            $instructor = $registroPrincipal->instructor;
            $fechaEntrada = $entrada ? Carbon::parse($entrada->fecha_hora_registro) : null;
            $fechaSalida = $salida ? Carbon::parse($salida->fecha_hora_registro) : null;
            
            // Determinar estado
            $estado = 'Presente';
            if ($entrada) {
                $estado = $entrada->es_tardanza ? 'Tarde' : 'Puntual';
            } elseif ($salida) {
                $estado = 'Solo Salida';
            }

            return [
                'id' => $entrada ? $entrada->id : $salida->id,
                'user_name' => $instructor ? ($instructor->nombres . ' ' . $instructor->apellidos) : 'No disponible',
                'area' => $instructor ? ($instructor->area_asignada ?? 'No asignada') : 'No disponible',
                'fecha' => $fechaEntrada ? $fechaEntrada->format('d/m/Y') : ($fechaSalida ? $fechaSalida->format('d/m/Y') : 'N/A'),
                'hora_entrada' => $fechaEntrada ? $fechaEntrada->format('H:i') : '-',
                'hora_salida' => $fechaSalida ? $fechaSalida->format('H:i') : '-',
                'estado' => $estado,
                'tipo_movimiento' => $entrada ? 'entrada' : 'salida'
            ];
        })->filter()->values()->toArray();

        // Distribución de roles
        $rolesDistribution = [
            'admin' => User::where('role', 'admin')->count(),
            'guardia' => User::where('role', 'guardia')->count(),
            'user' => User::where('role', 'user')->count(),
        ];

        // Asistencias de la semana
        $weeklyAsistencias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $count = Asistencia::whereDate('fecha_hora_registro', $fecha->toDateString())
                ->where('tipo_movimiento', 'entrada')
                ->distinct('instructor_id')
                ->count();
            
            $weeklyAsistencias[] = [
                'day' => $fecha->format('D'),
                'date' => $fecha->format('d'),
                'count' => $count
            ];
        }

        // Estado del sistema
        $systemStatus = [
            'server_status' => 'online',
            'database_status' => 'connected',
            'last_backup' => now()->subHours(2)->format('d/m/Y H:i'),
            'system_uptime' => '99.8%'
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $userStats,
            'asistenciasStats' => $asistenciasStats,
            'recentUsers' => $recentUsers->toArray(),
            'recentAsistencias' => $recentAsistencias,
            'rolesDistribution' => $rolesDistribution,
            'weeklyAsistencias' => $weeklyAsistencias,
            'systemStatus' => $systemStatus,
            'chartData' => [
                'asistenciasPorHora' => $this->getAsistenciasPorHora(),
                'puntualidadData' => $this->getPuntualidadData(),
                'asistenciasUltimos7Dias' => $this->getAsistenciasUltimos7Dias()
            ],
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }

    /**
     * Obtener datos de asistencias agrupadas por hora para el gráfico de barras
     */
    private function getAsistenciasPorHora()
    {
        $asistenciasHoy = Asistencia::whereDate('fecha_hora_registro', today())
            ->where('tipo_movimiento', 'entrada')
            ->get();

        // Inicializar contadores por rango de horas
        $rangosHora = [
            '06:00-08:00' => 0,
            '08:00-10:00' => 0,
            '10:00-12:00' => 0,
            '12:00-14:00' => 0,
            '14:00-16:00' => 0,
            '16:00-18:00' => 0,
        ];

        foreach ($asistenciasHoy as $asistencia) {
            $hora = Carbon::parse($asistencia->fecha_hora_registro)->format('H:i');
            
            if ($hora >= '06:00' && $hora < '08:00') {
                $rangosHora['06:00-08:00']++;
            } elseif ($hora >= '08:00' && $hora < '10:00') {
                $rangosHora['08:00-10:00']++;
            } elseif ($hora >= '10:00' && $hora < '12:00') {
                $rangosHora['10:00-12:00']++;
            } elseif ($hora >= '12:00' && $hora < '14:00') {
                $rangosHora['12:00-14:00']++;
            } elseif ($hora >= '14:00' && $hora < '16:00') {
                $rangosHora['14:00-16:00']++;
            } elseif ($hora >= '16:00' && $hora < '18:00') {
                $rangosHora['16:00-18:00']++;
            }
        }

        // Convertir a formato para el gráfico
        $labels = array_keys($rangosHora);
        $values = array_values($rangosHora);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Asistencias',
                    'data' => $values,
                    'backgroundColor' => '#16A085', // Verde SENA
                    'borderColor' => '#138D75',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Obtener datos de puntualidad para el gráfico circular
     */
    private function getPuntualidadData()
    {
        $totalEntradas = Asistencia::whereDate('fecha_hora_registro', today())
            ->where('tipo_movimiento', 'entrada')
            ->count();

        $llegadasTarde = Asistencia::whereDate('fecha_hora_registro', today())
            ->where('tipo_movimiento', 'entrada')
            ->where('es_tardanza', true)
            ->count();

        $llegadasPuntuales = $totalEntradas - $llegadasTarde;

        // Calcular porcentajes
        $porcentajePuntual = $totalEntradas > 0 ? round(($llegadasPuntuales / $totalEntradas) * 100, 1) : 0;
        $porcentajeTarde = $totalEntradas > 0 ? round(($llegadasTarde / $totalEntradas) * 100, 1) : 0;

        return [
            'labels' => ['Puntuales', 'Tarde'],
            'datasets' => [
                [
                    'data' => [$llegadasPuntuales, $llegadasTarde],
                    'backgroundColor' => [
                        '#16A085', // Verde para puntuales
                        '#E74C3C'  // Rojo para tarde
                    ],
                    'borderColor' => [
                        '#138D75',
                        '#C0392B'
                    ],
                    'borderWidth' => 2
                ]
            ],
            'percentages' => [
                'puntual' => $porcentajePuntual,
                'tarde' => $porcentajeTarde
            ],
            'totals' => [
                'puntual' => $llegadasPuntuales,
                'tarde' => $llegadasTarde,
                'total' => $totalEntradas
            ]
        ];
    }

    /**
     * Obtener datos de asistencias de los últimos 7 días para el gráfico de líneas
     */
    private function getAsistenciasUltimos7Dias()
    {
        $fechas = [];
        $valores = [];

        // Generar fechas de los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $fechaFormateada = $fecha->format('Y-m-d');
            
            // Contar asistencias de entrada de ese día
            $asistenciasDelDia = Asistencia::whereDate('fecha_hora_registro', $fechaFormateada)
                ->where('tipo_movimiento', 'entrada')
                ->distinct('instructor_id')
                ->count();
            
            $fechas[] = $fecha->format('D'); // Día de la semana abreviado
            $valores[] = $asistenciasDelDia;
        }

        return [
            'labels' => $fechas,
            'datasets' => [
                [
                    'label' => 'Asistencias',
                    'data' => $valores,
                    'borderColor' => '#16A085', // Verde SENA
                    'backgroundColor' => 'rgba(22, 160, 133, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#16A085',
                    'pointBorderColor' => '#138D75',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7
                ]
            ],
            'maxValue' => max($valores) + 5, // Para el eje Y
            'totalSemana' => array_sum($valores)
        ];
    }

    /**
     * Calcular horas trabajadas de manera precisa
     */
    private function calcularHorasTrabajadas($fechaHoraEntrada, $fechaHoraSalida)
    {
        if (!$fechaHoraEntrada || !$fechaHoraSalida) {
            return '-';
        }

        try {
            $inicio = Carbon::parse($fechaHoraEntrada);
            $fin = Carbon::parse($fechaHoraSalida);
            
            // Verificar que la salida sea después de la entrada
            if ($fin->lessThan($inicio)) {
                return 'Error';
            }
            
            // Calcular la diferencia en minutos totales
            $totalMinutos = $inicio->diffInMinutes($fin);
            
            // Convertir a horas y minutos
            $horas = intval($totalMinutos / 60);
            $minutos = $totalMinutos % 60;
            
            // Formatear como HH:MM
            return sprintf('%02d:%02d', $horas, $minutos);
            
        } catch (Exception $e) {
            return 'Error';
        }
    }

    /**
     * Mostrar historial de asistencias
     */
    public function historial(Request $request): Response
    {
        // Obtener todos los instructores para el filtro
        $instructores = Instructor::orderBy('nombres')->get();

        // Construir la consulta base
        $query = Asistencia::with('instructor')
            ->orderBy('fecha_hora_registro', 'desc');

        // Variable para manejar el caso especial de ausentes
        $mostrarAusentes = false;

        // Aplicar filtro por fecha
        if ($request->filled('fecha')) {
            $fecha = $request->get('fecha');
            $query->whereDate('fecha_hora_registro', $fecha);
        }

        // Aplicar filtro por instructor
        if ($request->filled('instructor')) {
            $instructorNombre = trim($request->get('instructor'));
            $query->whereHas('instructor', function($q) use ($instructorNombre) {
                $q->where(\DB::raw("CONCAT(nombres, ' ', apellidos)"), 'like', '%' . $instructorNombre . '%')
                  ->orWhere('nombres', 'like', '%' . $instructorNombre . '%')
                  ->orWhere('apellidos', 'like', '%' . $instructorNombre . '%');
            });
        }

        // Aplicar filtro por tipo de registro
        if ($request->filled('tipo')) {
            $tipo = $request->get('tipo');
            switch($tipo) {
                case 'entrada':
                    $query->where('tipo_movimiento', 'entrada');
                    break;
                case 'salida':
                    $query->where('tipo_movimiento', 'salida');
                    break;
                case 'puntual':
                    $query->where('tipo_movimiento', 'entrada')
                          ->where('es_tardanza', false);
                    break;
                case 'tarde':
                    $query->where('tipo_movimiento', 'entrada')
                          ->where('es_tardanza', true);
                    break;
                case 'ausente':
                    $mostrarAusentes = true;
                    break;
                default:
                    // Sin filtro específico de tipo
                    break;
            }
        }

        // Manejar el caso especial de ausentes
        if ($mostrarAusentes) {
            $fechaConsulta = $request->get('fecha', today()->format('Y-m-d'));
            
            // Obtener instructores con asistencia en la fecha seleccionada
            $instructoresConAsistencia = Asistencia::whereDate('fecha_hora_registro', $fechaConsulta)
                ->where('tipo_movimiento', 'entrada')
                ->pluck('instructor_id')
                ->toArray();
            
            // Obtener instructores ausentes
            $instructoresAusentesQuery = Instructor::whereNotIn('id', $instructoresConAsistencia);
            
            // Aplicar filtro de instructor si existe
            if ($request->filled('instructor')) {
                $instructorNombre = trim($request->get('instructor'));
                $instructoresAusentesQuery->where(function($q) use ($instructorNombre) {
                    $q->where(\DB::raw("CONCAT(nombres, ' ', apellidos)"), 'like', '%' . $instructorNombre . '%')
                      ->orWhere('nombres', 'like', '%' . $instructorNombre . '%')
                      ->orWhere('apellidos', 'like', '%' . $instructorNombre . '%');
                });
            }
            
            $instructoresAusentes = $instructoresAusentesQuery->get();
            
            // Crear registros virtuales para ausentes
            $historialData = collect();
            foreach($instructoresAusentes as $instructor) {
                $historialData->push((object)[
                    'id' => 'ausente-' . $instructor->id,
                    'instructor' => $instructor,
                    'fecha_hora_registro' => $fechaConsulta . ' 00:00:00',
                    'tipo_movimiento' => 'ausente',
                    'es_tardanza' => false,
                    'observaciones' => 'Ausente - Sin registro de entrada'
                ]);
            }
            
            // Paginación manual para ausentes
            $page = $request->get('page', 1);
            $perPage = 50;
            $total = $historialData->count();
            $items = $historialData->slice(($page - 1) * $perPage, $perPage)->values();
            
            $historialData = new \Illuminate\Pagination\LengthAwarePaginator(
                $items, $total, $perPage, $page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            // Ejecutar consulta normal y agrupar por instructor y fecha
            $allAsistencias = $query->get();
            
            // Agrupar por instructor_id y fecha
            $agrupadosPorDia = $allAsistencias->groupBy(function($item) {
                return $item->instructor_id . '-' . Carbon::parse($item->fecha_hora_registro)->format('Y-m-d');
            });
            
            // Transformar grupos en registros únicos con entrada y salida
            $historialAgrupado = $agrupadosPorDia->map(function($grupo) {
                $entrada = $grupo->firstWhere('tipo_movimiento', 'entrada');
                $salida = $grupo->firstWhere('tipo_movimiento', 'salida');
                
                // Usar el registro de entrada si existe, sino el de salida
                $registroPrincipal = $entrada ?? $salida;
                
                if (!$registroPrincipal) {
                    return null;
                }
                
                $instructor = $registroPrincipal->instructor;
                $fechaEntrada = $entrada ? Carbon::parse($entrada->fecha_hora_registro) : null;
                $fechaSalida = $salida ? Carbon::parse($salida->fecha_hora_registro) : null;
                
                // Determinar estado
                $estado = 'Normal';
                if ($entrada) {
                    $estado = $entrada->es_tardanza ? 'Tarde' : 'Puntual';
                } elseif ($salida) {
                    $estado = 'Solo Salida';
                }
                
                return [
                    'id' => $entrada ? $entrada->id : $salida->id,
                    'instructor' => $instructor ? $instructor->nombres . ' ' . $instructor->apellidos : 'No disponible',
                    'area' => $instructor ? ($instructor->area_asignada ?? 'No asignada') : 'No disponible',
                    'fecha' => $fechaEntrada ? $fechaEntrada->format('d/m/Y') : ($fechaSalida ? $fechaSalida->format('d/m/Y') : 'N/A'),
                    'horaEntrada' => $fechaEntrada ? $fechaEntrada->format('H:i') : '-',
                    'horaSalida' => $fechaSalida ? $fechaSalida->format('H:i') : '-',
                    'estado' => $estado,
                    'tipo_movimiento' => $entrada ? 'entrada' : 'salida',
                    'es_tardanza' => $entrada ? $entrada->es_tardanza : false,
                    'observaciones' => $entrada ? ($entrada->observaciones ?? '') : ($salida ? ($salida->observaciones ?? '') : '')
                ];
            })->filter()->values();
            
            // Paginar manualmente
            $page = $request->get('page', 1);
            $perPage = 50;
            $total = $historialAgrupado->count();
            $items = $historialAgrupado->slice(($page - 1) * $perPage, $perPage)->values();
            
            $historialData = new \Illuminate\Pagination\LengthAwarePaginator(
                $items, $total, $perPage, $page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        // Generar estadísticas del historial (aplicar filtros si existen)
        $fechaEstadisticas = $request->filled('fecha') ? $request->get('fecha') : today()->format('Y-m-d');
        
        // Contar registros con filtros aplicados
        $queryEstadisticas = Asistencia::whereDate('fecha_hora_registro', $fechaEstadisticas);
        
        // Aplicar filtro de instructor en estadísticas si existe
        if ($request->filled('instructor')) {
            $instructorNombre = trim($request->get('instructor'));
            $queryEstadisticas->whereHas('instructor', function($q) use ($instructorNombre) {
                $q->where(\DB::raw("CONCAT(nombres, ' ', apellidos)"), 'like', '%' . $instructorNombre . '%')
                  ->orWhere('nombres', 'like', '%' . $instructorNombre . '%')
                  ->orWhere('apellidos', 'like', '%' . $instructorNombre . '%');
            });
        }
        
        $estadisticas = [
            'totalRegistrosHoy' => (clone $queryEstadisticas)->count(),
            'llegadasPuntuales' => (clone $queryEstadisticas)
                ->where('tipo_movimiento', 'entrada')
                ->where('es_tardanza', false)
                ->count(),
            'llegadasTarde' => (clone $queryEstadisticas)
                ->where('tipo_movimiento', 'entrada')
                ->where('es_tardanza', true)
                ->count(),
            'ausencias' => $mostrarAusentes ? $historialData->total() : max(0, Instructor::count() - 
                (clone $queryEstadisticas)->where('tipo_movimiento', 'entrada')->distinct('instructor_id')->count())
        ];

        return Inertia::render('Admin/Historial', [
            'instructores' => $instructores->map(function($instructor) {
                return [
                    'id' => $instructor->id,
                    'nombre' => $instructor->nombres . ' ' . $instructor->apellidos,
                    'area' => $instructor->area_asignada ?? 'No asignada'
                ];
            }),
            'historialData' => $historialData,
            'estadisticas' => $estadisticas,
            'filtros' => $request->only(['fecha', 'instructor', 'tipo'])
        ]);
    }

    /**
     * Mostrar página de reportes con datos reales
     */
    public function reportes(Request $request): Response
    {
        // Obtener todos los instructores para el filtro
        $instructores = Instructor::orderBy('nombres')->get();

        // Obtener áreas únicas de instructores
        $areas = Instructor::whereNotNull('area_asignada')
            ->distinct()
            ->pluck('area_asignada')
            ->sort()
            ->values();

        // Construir la consulta base para reportes
        $query = Asistencia::with('instructor')
            ->orderBy('fecha_hora_registro', 'desc');

        // Si no hay filtros, mostrar últimas asistencias (últimos 7 días)
        $tienesFiltros = $request->filled('fechaInicio') || $request->filled('fechaFin') || 
                         $request->filled('instructor') || $request->filled('area');

        if (!$tienesFiltros) {
            // Por defecto: últimos 7 días
            $query->whereDate('fecha_hora_registro', '>=', Carbon::now()->subDays(7)->toDateString());
        } else {
            // Aplicar filtro por fecha de inicio
            if ($request->filled('fechaInicio')) {
                $fechaInicio = $request->get('fechaInicio');
                $query->whereDate('fecha_hora_registro', '>=', $fechaInicio);
            }

            // Aplicar filtro por fecha de fin
            if ($request->filled('fechaFin')) {
                $fechaFin = $request->get('fechaFin');
                $query->whereDate('fecha_hora_registro', '<=', $fechaFin);
            }
        }

        // Aplicar filtro por instructor
        if ($request->filled('instructor') && $request->get('instructor') !== '') {
            $instructorId = $request->get('instructor');
            $query->where('instructor_id', $instructorId);
        }

        // Aplicar filtro por área
        if ($request->filled('area') && $request->get('area') !== '') {
            $area = $request->get('area');
            $query->whereHas('instructor', function($q) use ($area) {
                $q->where('area_asignada', $area);
            });
        }

        // Ejecutar consulta con paginación
        $allAsistencias = $query->get();

        // Agrupar por instructor_id y fecha
        $agrupadosPorDia = $allAsistencias->groupBy(function($item) {
            return $item->instructor_id . '-' . Carbon::parse($item->fecha_hora_registro)->format('Y-m-d');
        });

        // Transformar grupos en registros únicos con entrada y salida
        $reportesAgrupados = $agrupadosPorDia->map(function($grupo) {
            $entrada = $grupo->firstWhere('tipo_movimiento', 'entrada');
            $salida = $grupo->firstWhere('tipo_movimiento', 'salida');
            
            // Usar el registro de entrada si existe, sino el de salida
            $registroPrincipal = $entrada ?? $salida;
            
            if (!$registroPrincipal) {
                return null;
            }
            
            $instructor = $registroPrincipal->instructor;
            $fechaEntrada = $entrada ? Carbon::parse($entrada->fecha_hora_registro) : null;
            $fechaSalida = $salida ? Carbon::parse($salida->fecha_hora_registro) : null;
            
            // Determinar estado
            $estado = 'Completo';
            if ($entrada) {
                $estado = $entrada->es_tardanza ? 'Tarde' : 'Puntual';
            } elseif ($salida) {
                $estado = 'Sin salida';
            }

            return [
                'id' => $entrada ? $entrada->id : $salida->id,
                'instructor' => $instructor ? $instructor->nombres . ' ' . $instructor->apellidos : 'No disponible',
                'area' => $instructor ? ($instructor->area_asignada ?? 'No asignada') : 'No disponible',
                'fecha' => $fechaEntrada ? $fechaEntrada->format('d/m/Y') : ($fechaSalida ? $fechaSalida->format('d/m/Y') : 'N/A'),
                'horaEntrada' => $fechaEntrada ? $fechaEntrada->format('H:i') : '-',
                'horaSalida' => $fechaSalida ? $fechaSalida->format('H:i') : '-',
                'horasTrabajadas' => $fechaEntrada && $fechaSalida ? $this->calcularHorasTrabajadas($fechaEntrada, $fechaSalida) : '-',
                'estado' => $estado,
                'tipo_movimiento' => $entrada ? 'entrada' : 'salida'
            ];
        })->filter()->values();

        // Paginar manualmente
        $page = $request->get('page', 1);
        $perPage = 15;
        $total = $reportesAgrupados->count();
        $items = $reportesAgrupados->slice(($page - 1) * $perPage, $perPage)->values();
        
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Transformar datos para la vista
        $reportesData = $items->toArray();

        // Calcular estadísticas reales
        $totalInstructores = Instructor::count();
        $presentesHoy = Asistencia::whereDate('fecha_hora_registro', today())
            ->where('tipo_movimiento', 'entrada')
            ->distinct('instructor_id')
            ->count();
        $ausentesHoy = max(0, $totalInstructores - $presentesHoy);
        $tardesHoy = Asistencia::whereDate('fecha_hora_registro', today())
            ->where('tipo_movimiento', 'entrada')
            ->where('es_tardanza', true)
            ->count();

        // Calcular promedio de asistencia
        $totalAsistenciasRegistradas = Asistencia::where('tipo_movimiento', 'entrada')->count();
        $totalAsistenciasEsperadas = max(1, Instructor::count() * 30); // Aproximación: 30 días
        $promedioAsistencia = $totalAsistenciasEsperadas > 0 
            ? round(($totalAsistenciasRegistradas / $totalAsistenciasEsperadas) * 100) 
            : 0;

        $estadisticas = [
            'totalInstructores' => $totalInstructores,
            'presentesHoy' => $presentesHoy,
            'ausentesHoy' => $ausentesHoy,
            'tardesHoy' => $tardesHoy,
            'promedioAsistencia' => $promedioAsistencia . '%',
            'periodo' => [
                'inicio' => $request->get('fechaInicio', Carbon::now()->subDays(7)->format('d/m/Y')),
                'fin' => $request->get('fechaFin', today()->format('d/m/Y'))
            ]
        ];

        return Inertia::render('Admin/Reportes', [
            'instructores' => $instructores->map(function($instructor) {
                return [
                    'id' => $instructor->id,
                    'nombres' => $instructor->nombres,
                    'apellidos' => $instructor->apellidos,
                    'area' => $instructor->area_asignada ?? 'No asignada'
                ];
            })->toArray(),
            'reportesData' => $reportesData,
            'estadisticas' => $estadisticas,
            'filtros' => $request->only(['fechaInicio', 'fechaFin', 'instructor', 'area']),
            'areas' => $areas->values()->toArray(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'from' => $paginatedData->firstItem() ?? 0,
                'to' => $paginatedData->lastItem() ?? 0
            ]
        ]);
    }

    /**
     * Exportar reportes en formato PDF o Excel
     */
    public function exportarReporte(Request $request)
    {
        // Construir consulta con filtros
        $query = Asistencia::with('instructor')
            ->orderBy('fecha_hora_registro', 'desc');

        if ($request->filled('fechaInicio')) {
            $query->whereDate('fecha_hora_registro', '>=', $request->get('fechaInicio'));
        }

        if ($request->filled('fechaFin')) {
            $query->whereDate('fecha_hora_registro', '<=', $request->get('fechaFin'));
        }

        if ($request->filled('instructor') && $request->get('instructor') !== '') {
            $query->where('instructor_id', $request->get('instructor'));
        }

        if ($request->filled('area') && $request->get('area') !== '') {
            $area = $request->get('area');
            $query->whereHas('instructor', function($q) use ($area) {
                $q->where('area_asignada', $area);
            });
        }

        $asistencias = $query->get();

        // Generar CSV
        $filename = 'reporte_asistencias_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($asistencias) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, ['Instructor', 'Área', 'Fecha', 'Hora Entrada', 'Hora Salida', 'Estado', 'Tipo Movimiento'], ';');

            // Datos
            foreach ($asistencias as $asistencia) {
                $instructor = $asistencia->instructor;
                $fechaHora = Carbon::parse($asistencia->fecha_hora_registro);

                $estado = 'Normal';
                if ($asistencia->tipo_movimiento === 'entrada') {
                    $estado = $asistencia->es_tardanza ? 'Tarde' : 'Puntual';
                } elseif ($asistencia->tipo_movimiento === 'salida') {
                    $estado = 'Sin salida';
                }

                fputcsv($file, [
                    $instructor ? $instructor->nombres . ' ' . $instructor->apellidos : 'No disponible',
                    $instructor ? ($instructor->area_asignada ?? 'No asignada') : 'No disponible',
                    $fechaHora->format('d/m/Y'),
                    $asistencia->tipo_movimiento === 'entrada' ? $fechaHora->format('H:i') : '--',
                    $asistencia->tipo_movimiento === 'salida' ? $fechaHora->format('H:i') : '--',
                    $estado,
                    $asistencia->tipo_movimiento
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mostrar página de configuraciones
     */
    public function configuraciones(): Response
    {
        // Obtener estadísticas de usuarios reales de la base de datos (sin usuarios regulares)
        $rolesStats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'guardia' => User::where('role', 'guardia')->count()
        ];

        // Obtener estadísticas de asistencias para la sección de horarios
        $asistenciasStats = [
            'total_hoy' => Asistencia::whereDate('fecha_hora_registro', today())->count(),
            'puntuales' => Asistencia::whereDate('fecha_hora_registro', today())
                                   ->where('tipo_movimiento', 'entrada')
                                   ->where('es_tardanza', false)
                                   ->count(),
            'tarde' => Asistencia::whereDate('fecha_hora_registro', today())
                                ->where('tipo_movimiento', 'entrada')
                                ->where('es_tardanza', true)
                                ->count(),
            'ausencias' => Instructor::count() - Asistencia::whereDate('fecha_hora_registro', today())
                                               ->where('tipo_movimiento', 'entrada')
                                               ->distinct('instructor_id')
                                               ->count()
        ];

        // Obtener asistencias recientes
        $asistenciasRecientes = Asistencia::with('instructor')
            ->orderBy('fecha_hora_registro', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($asistencia) {
                $instructor = $asistencia->instructor;
                $fechaHora = Carbon::parse($asistencia->fecha_hora_registro);
                
                return [
                    'instructor' => $instructor ? $instructor->nombres . ' ' . $instructor->apellidos : 'No disponible',
                    'area' => $instructor ? ($instructor->area_asignada ?? 'No asignada') : 'No disponible',
                    'fecha' => $fechaHora->format('d/m/Y'),
                    'entrada' => $asistencia->tipo_movimiento === 'entrada' ? $fechaHora->format('H:i') : '--',
                    'salida' => $asistencia->tipo_movimiento === 'salida' ? $fechaHora->format('H:i') : '--',
                    'estado' => $asistencia->es_tardanza ? 'Tarde' : 
                               ($asistencia->tipo_movimiento === 'entrada' ? 'Puntual' : 'Presente'),
                    'tipo_movimiento' => $asistencia->tipo_movimiento
                ];
            });

        // Configuraciones del sistema
        $systemSettings = [
            'system_name' => config('app.name', 'Gestión Instructores SENA'),
            'logo_path' => '/images/logo-sena.png',
            'primary_color' => '#16a34a',
            'language' => 'es'
        ];

        return Inertia::render('Admin/Configuraciones', [
            'rolesStats' => $rolesStats,
            'asistenciasStats' => $asistenciasStats,
            'asistenciasRecientes' => $asistenciasRecientes->toArray(),
            'systemSettings' => $systemSettings
        ]);
    }

    /**
     * Mostrar formulario de crear vigilante
     */
    public function createVigilante(): Response
    {
        return Inertia::render('Admin/Vigilantes/Create');
    }

    /**
     * Crear nuevo vigilante
     */
    public function storeVigilante(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'correo_electronico' => 'required|string|email|max:255|unique:users,email',
                'telefono' => 'required|string|max:20',
                'codigo_vigilante' => 'required|string|max:20|unique:users,cedula',
                'ubicacion_asignada' => 'required|string',
                'hora_inicio_turno' => 'required|string',
                'hora_fin_turno' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'correo_electronico.unique' => 'El correo ya existe',
                'codigo_vigilante.unique' => 'El código de vigilante ya está registrado',
                'password.confirmed' => 'Las contraseñas no coinciden',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'nombre_completo.required' => 'El nombre completo es obligatorio',
                'correo_electronico.required' => 'El correo electrónico es obligatorio',
                'telefono.required' => 'El teléfono es obligatorio',
                'codigo_vigilante.required' => 'El código de vigilante es obligatorio',
                'ubicacion_asignada.required' => 'La ubicación asignada es obligatoria',
                'hora_inicio_turno.required' => 'La hora de inicio del turno es obligatoria',
                'hora_fin_turno.required' => 'La hora de fin del turno es obligatoria'
            ]);

            User::create([
                'name' => $validated['nombre_completo'],
                'email' => $validated['correo_electronico'],
                'password' => bcrypt($validated['password']),
                'role' => 'vigilante',
                'cedula' => $validated['codigo_vigilante'],
                'telefono' => $validated['telefono'],
                'ubicacion_asignada' => $validated['ubicacion_asignada'],
                'hora_inicio_turno' => $validated['hora_inicio_turno'],
                'hora_fin_turno' => $validated['hora_fin_turno'],
                'fecha_inicio' => now()
            ]);

            return redirect()->route('admin.vigilantes.create')
                ->with('success', 'Vigilante registrado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Error en la validación de datos');
        }
    }

    /**
     * Mapear número de guardia a turno
     */
    private function mapearGuardiaATurno($guardia): string
    {
        return match($guardia) {
            '1' => 'mañana',
            '2' => 'tarde', 
            '3' => 'noche',
            default => 'mañana'
        };
    }

    /**
     * Actualizar configuración del sistema
     */
    public function updateSistema(Request $request)
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'language' => 'required|in:es,en,fr,pt',
            'color_scheme' => 'required|string',
            'timezone' => 'required|string',
        ]);

        try {
            SystemSetting::setSetting('system_name', $validated['system_name'], 'string', 'Nombre del sistema');
            SystemSetting::setSetting('language', $validated['language'], 'string', 'Idioma del sistema');
            SystemSetting::setSetting('color_scheme', $validated['color_scheme'], 'string', 'Esquema de colores');
            SystemSetting::setSetting('timezone', $validated['timezone'], 'string', 'Zona horaria del sistema');

            return back()->with('success', 'Configuración del sistema actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar configuración del sistema: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar configuración de mensajes
     */
    public function updateMensajes(Request $request)
    {
        $validated = $request->validate([
            'success_color' => 'required|in:green,blue,purple',
            'error_color' => 'required|in:red,orange',
            'warning_color' => 'required|in:yellow,orange',
            'info_color' => 'required|in:blue,gray',
            'duration' => 'required|integer|min:0|max:30',
            'position' => 'required|in:top-right,top-left,bottom-right,bottom-left,top-center',
            'sound_enabled' => 'boolean',
            'animation' => 'required|in:slide,fade,bounce,scale',
        ]);

        try {
            foreach ($validated as $key => $value) {
                SystemSetting::setSetting(
                    'notification_' . $key, 
                    $value, 
                    is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                    'Configuración de notificaciones: ' . $key
                );
            }

            return back()->with('success', 'Configuración de mensajes actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar configuración de mensajes: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar configuración de horarios
     */
    public function updateHorarios(Request $request)
    {
        $validated = $request->validate([
            'manana_inicio' => 'required|date_format:H:i',
            'manana_fin' => 'required|date_format:H:i|after:manana_inicio',
            'tarde_inicio' => 'required|date_format:H:i|after:manana_fin',
            'tarde_fin' => 'required|date_format:H:i|after:tarde_inicio',
            'noche_inicio' => 'required|date_format:H:i|after:tarde_fin',
            'noche_fin' => 'required|date_format:H:i',
            'tolerancia_minutos' => 'required|integer|min:0|max:60',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'manana_fin.after' => 'La hora de fin de la mañana debe ser posterior al inicio',
            'tarde_inicio.after' => 'La hora de inicio de la tarde debe ser posterior al fin de la mañana',
            'tarde_fin.after' => 'La hora de fin de la tarde debe ser posterior al inicio',
            'noche_inicio.after' => 'La hora de inicio de la noche debe ser posterior al fin de la tarde',
            'hasta.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde',
        ]);

        try {
            // Guardar horarios
            SystemSetting::setSetting('horario_manana_inicio', $validated['manana_inicio'], 'string', 'Hora inicio turno mañana');
            SystemSetting::setSetting('horario_manana_fin', $validated['manana_fin'], 'string', 'Hora fin turno mañana');
            SystemSetting::setSetting('horario_tarde_inicio', $validated['tarde_inicio'], 'string', 'Hora inicio turno tarde');
            SystemSetting::setSetting('horario_tarde_fin', $validated['tarde_fin'], 'string', 'Hora fin turno tarde');
            SystemSetting::setSetting('horario_noche_inicio', $validated['noche_inicio'], 'string', 'Hora inicio turno noche');
            SystemSetting::setSetting('horario_noche_fin', $validated['noche_fin'], 'string', 'Hora fin turno noche');
            SystemSetting::setSetting('tolerancia_tardanza', $validated['tolerancia_minutos'], 'integer', 'Tolerancia en minutos para tardanza');

            // Guardar periodo de validez si se proporciona
            if ($validated['desde']) {
                SystemSetting::setSetting('periodo_desde', $validated['desde'], 'date', 'Fecha inicio del periodo de horarios');
            }
            if ($validated['hasta']) {
                SystemSetting::setSetting('periodo_hasta', $validated['hasta'], 'date', 'Fecha fin del periodo de horarios');
            }

            return back()->with('success', 'Configuración de horarios actualizada exitosamente. Los cambios aplicarán a partir de las próximas asistencias registradas.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar configuración de horarios: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar lista de vigilantes y registros de vigilancia
     */
    public function indexVigilantes(Request $request): Response
    {
        // Obtener registros de vigilancia en lugar de usuarios vigilantes
        $query = collect(); // Inicializar como colección vacía por ahora
        
        // Simular datos de registro de vigilancia como en la imagen
        $registros = collect([
            [
                'id' => 1,
                'vigilante_nombre' => 'Valeria Peña',
                'fecha' => '01/04/2024',
                'hora_entrada' => '06:00',
                'hora_salida' => '12:00',
                'numero' => '1',
                'puerta' => 'Puerta superior'
            ],
            [
                'id' => 2,
                'vigilante_nombre' => 'Carlos Mendez',
                'fecha' => '01/04/2024',
                'hora_entrada' => '14:00',
                'hora_salida' => '22:00',
                'numero' => '2',
                'puerta' => 'Puerta principal'
            ],
            [
                'id' => 3,
                'vigilante_nombre' => 'Ana Rodriguez',
                'fecha' => '02/04/2024',
                'hora_entrada' => '22:00',
                'hora_salida' => '06:00',
                'numero' => '3',
                'puerta' => 'Puerta lateral'
            ],
            [
                'id' => 4,
                'vigilante_nombre' => 'Miguel Torres',
                'fecha' => '02/04/2024',
                'hora_entrada' => '06:30',
                'hora_salida' => '14:30',
                'numero' => '4',
                'puerta' => 'Puerta secundaria'
            ],
            [
                'id' => 5,
                'vigilante_nombre' => 'Sofia López',
                'fecha' => '03/04/2024',
                'hora_entrada' => '15:00',
                'hora_salida' => '23:00',
                'numero' => '5',
                'puerta' => 'Entrada cafetería'
            ]
        ]);

        // Aplicar filtros de búsqueda
        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $registros = $registros->filter(function($registro) use ($search) {
                return str_contains(strtolower($registro['vigilante_nombre']), $search) ||
                       str_contains(strtolower($registro['numero']), $search) ||
                       str_contains(strtolower($registro['puerta']), $search);
            });
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $fechaFilter = $request->get('fecha');
            $fechaFormateada = '';
            
            switch($fechaFilter) {
                case 'hoy':
                    $fechaFormateada = now()->format('d/m/Y');
                    break;
                case 'ayer':
                    $fechaFormateada = now()->subDay()->format('d/m/Y');
                    break;
                case 'esta_semana':
                    // Filtrar por esta semana
                    $registros = $registros->filter(function($registro) {
                        $fecha = \Carbon\Carbon::createFromFormat('d/m/Y', $registro['fecha']);
                        return $fecha->isCurrentWeek();
                    });
                    break;
                case 'este_mes':
                    // Filtrar por este mes
                    $registros = $registros->filter(function($registro) {
                        $fecha = \Carbon\Carbon::createFromFormat('d/m/Y', $registro['fecha']);
                        return $fecha->isCurrentMonth();
                    });
                    break;
            }
            
            if ($fechaFormateada) {
                $registros = $registros->filter(function($registro) use ($fechaFormateada) {
                    return $registro['fecha'] === $fechaFormateada;
                });
            }
        }

        // Filtro por número
        if ($request->filled('numero')) {
            $numero = $request->get('numero');
            $registros = $registros->filter(function($registro) use ($numero) {
                return str_contains($registro['numero'], $numero);
            });
        }

        return Inertia::render('Admin/Vigilantes/Index', [
            'registros' => $registros->values()->toArray(),
            'filtros' => $request->only(['search', 'fecha', 'numero'])
        ]);
    }

    /**
     * Mostrar un vigilante específico
     */
    public function showVigilante(User $vigilante): Response
    {
        if ($vigilante->role !== 'guardia') {
            abort(404);
        }

        return Inertia::render('Admin/Vigilantes/Show', [
            'vigilante' => $vigilante
        ]);
    }

    /**
     * Mostrar formulario de edición de vigilante
     */
    public function editVigilante(User $vigilante): Response
    {
        if ($vigilante->role !== 'guardia') {
            abort(404);
        }

        return Inertia::render('Admin/Vigilantes/Edit', [
            'vigilante' => $vigilante
        ]);
    }

    /**
     * Actualizar vigilante
     */
    public function updateVigilante(Request $request, User $vigilante)
    {
        if ($vigilante->role !== 'guardia') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $vigilante->id,
            'telefono' => 'required|string|max:20',
            'cedula' => 'required|string|max:20|unique:users,cedula,' . $vigilante->id,
            'ubicacion_asignada' => 'required|string',
            'hora_inicio_turno' => 'required|string',
            'hora_fin_turno' => 'required|string',
        ]);

        $vigilante->update($validated);

        return redirect()->route('admin.vigilantes.index')
                        ->with('success', 'Vigilante actualizado exitosamente');
    }

    /**
     * Eliminar vigilante
     */
    public function destroyVigilante(User $vigilante)
    {
        if ($vigilante->role !== 'guardia') {
            abort(404);
        }

        $vigilante->delete();

        return redirect()->route('admin.vigilantes.index')
                        ->with('success', 'Vigilante eliminado exitosamente');
    }

    /**
     * Mostrar lista de vigilantes y registros de vigilancia
     */
    public function vigilantes(Request $request): Response
    {
        // Obtener vigilantes (usuarios con rol 'guardia')
        $vigilantesQuery = User::where('role', 'guardia')
            ->orderBy('name');
        
        // Aplicar búsqueda si existe
        if ($request->filled('search')) {
            $search = $request->get('search');
            $vigilantesQuery->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('cedula', 'like', "%{$search}%")
                      ->orWhere('ubicacion_asignada', 'like', "%{$search}%");
            });
        }
        
        $vigilantes = $vigilantesQuery->get()
            ->map(function($vigilante) {
                return [
                    'id' => $vigilante->id,
                    'nombre' => $vigilante->name ?? 'No registrado',
                    'email' => $vigilante->email ?? 'No registrado',
                    'telefono' => $vigilante->phone ?? $vigilante->telefono ?? 'No registrado',
                    'cedula' => $vigilante->cedula ?? 'No registrada',
                    'ubicacion_asignada' => $vigilante->ubicacion_asignada ?? 'No asignada',
                    'hora_inicio_turno' => $vigilante->hora_inicio_turno ?? '06:00',
                    'hora_fin_turno' => $vigilante->hora_fin_turno ?? '14:00',
                    'fecha_creacion' => $vigilante->created_at ? $vigilante->created_at->format('d/m/Y') : 'N/A',
                    'estado' => 'Activo'
                ];
            })->values()->all();

        // Estadísticas de vigilantes
        $estadisticas = [
            'total_vigilantes' => User::where('role', 'guardia')->count(),
            'vigilantes_activos' => User::where('role', 'guardia')->count(),
            'turnos_cubiertos' => User::where('role', 'guardia')
                ->whereNotNull('hora_inicio_turno')
                ->count(),
            'ubicaciones_asignadas' => User::where('role', 'guardia')
                ->whereNotNull('ubicacion_asignada')
                ->count()
        ];

        return Inertia::render('Admin/Vigilantes', [
            'vigilantes' => $vigilantes,
            'estadisticas' => $estadisticas,
            'filtros' => $request->only(['search'])
        ]);
    }
}
