<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    /**
     * RF033: Mostrar lista de dispositivos conectados
     */
    public function index(): Response
    {
        $dispositivos = Device::with('guardia')
            ->orderBy('ubicacion')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'reader_code' => $device->reader_code,
                    'ubicacion' => $device->ubicacion,
                    'ip_address' => $device->ip_address,
                    'mac_address' => $device->mac_address,
                    'modelo' => $device->modelo,
                    'firmware' => $device->firmware,
                    'active' => $device->active,
                    'conectado' => $device->estaConectado(),
                    'last_ping' => $device->last_ping?->format('Y-m-d H:i:s'),
                    'intentos_fallidos' => $device->intentos_fallidos,
                    'guardia' => $device->guardia?->name,
                    'notas' => $device->notas,
                ];
            });

        // Estadísticas
        $estadisticas = [
            'total' => Device::count(),
            'activos' => Device::where('active', true)->count(),
            'conectados' => Device::conectados()->count(),
            'desconectados' => Device::desconectados()->count(),
            'inactivos' => Device::where('active', false)->count(),
        ];

        return Inertia::render('Admin/Devices/Index', [
            'dispositivos' => $dispositivos,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * RF033: Formulario crear nuevo dispositivo
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Devices/Create');
    }

    /**
     * RF033: Guardar nuevo dispositivo
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reader_code' => 'required|unique:devices|string|max:50',
            'ubicacion' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'modelo' => 'nullable|string|max:100',
            'firmware' => 'nullable|string|max:50',
            'notas' => 'nullable|string|max:500',
        ]);

        $device = Device::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo creado exitosamente',
            'device' => $device
        ], 201);
    }

    /**
     * RF033: Mostrar detalles del dispositivo
     */
    public function show(Device $device): Response
    {
        return Inertia::render('Admin/Devices/Show', [
            'device' => $device->load('guardia')->toArray(),
        ]);
    }

    /**
     * RF033: Formulario editar dispositivo
     */
    public function edit(Device $device): Response
    {
        return Inertia::render('Admin/Devices/Edit', [
            'device' => $device->toArray(),
        ]);
    }

    /**
     * RF033: Actualizar dispositivo
     */
    public function update(Request $request, Device $device): JsonResponse
    {
        $validated = $request->validate([
            'ubicacion' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'modelo' => 'nullable|string|max:100',
            'firmware' => 'nullable|string|max:50',
            'active' => 'boolean',
            'notas' => 'nullable|string|max:500',
        ]);

        $device->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo actualizado exitosamente',
            'device' => $device
        ]);
    }

    /**
     * RF033: Eliminar dispositivo
     */
    public function destroy(Device $device): JsonResponse
    {
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo eliminado exitosamente'
        ]);
    }

    /**
     * RF033: Endpoint para que el dispositivo reporte su actividad (ping)
     * Llamado por el lector cada X segundos
     */
    public function ping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reader_code' => 'required|string',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string',
        ]);

        $device = Device::where('reader_code', $validated['reader_code'])->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo no registrado'
            ], 404);
        }

        // Actualizar información
        $device->update([
            'ip_address' => $validated['ip_address'] ?? $device->ip_address,
            'mac_address' => $validated['mac_address'] ?? $device->mac_address,
        ]);

        // Registrar ping
        $device->registrarPing();

        return response()->json([
            'success' => true,
            'message' => 'Ping registrado',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * RF033: Cambiar estado (activo/inactivo)
     */
    public function toggleActive(Device $device): JsonResponse
    {
        $device->update(['active' => !$device->active]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del dispositivo actualizado',
            'active' => $device->active
        ]);
    }

    /**
     * RF033: Resetear intentos fallidos
     */
    public function resetFailedAttempts(Device $device): JsonResponse
    {
        $device->update(['intentos_fallidos' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Intentos fallidos reseteados'
        ]);
    }

    /**
     * RF033: API endpoint para obtener estado de todos los dispositivos (para monitoreo en tiempo real)
     */
    public function status(): JsonResponse
    {
        $dispositivos = Device::select('id', 'reader_code', 'ubicacion', 'active', 'last_ping')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'reader_code' => $device->reader_code,
                    'ubicacion' => $device->ubicacion,
                    'conectado' => $device->estaConectado(),
                    'last_ping' => $device->last_ping?->format('H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'ok',
            'devices' => $dispositivos,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
