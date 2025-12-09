<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    /**
     * Mostrar listado de logs de auditoría (solo admin)
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(50);

        // Obtener acciones únicas para el filtro
        $actions = AuditLog::distinct()->pluck('action');
        $modelTypes = AuditLog::distinct()->pluck('model_type');

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'filters' => [
                'action' => $request->action,
                'user_id' => $request->user_id,
                'model_type' => $request->model_type,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'status' => $request->status,
            ]
        ]);
    }

    /**
     * Ver detalles de un log específico
     */
    public function show(AuditLog $auditLog)
    {
        return response()->json($auditLog->load('user'));
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public function stats()
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'logs_today' => AuditLog::whereDate('created_at', today())->count(),
            'failed_actions' => AuditLog::where('status', 'failed')->count(),
            'actions_by_type' => AuditLog::groupBy('action')
                ->selectRaw('action, count(*) as count')
                ->pluck('count', 'action'),
            'users_with_actions' => AuditLog::distinct('user_id')->count('user_id'),
            'models_modified' => AuditLog::groupBy('model_type')
                ->selectRaw('model_type, count(*) as count')
                ->where('action', 'update')
                ->pluck('count', 'model_type'),
        ];

        return response()->json($stats);
    }

    /**
     * Exportar logs a CSV
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Aplicar mismos filtros que en index
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $logs = $query->get();

        $csv = "Fecha,Usuario,Acción,Modelo,ID Modelo,Descripción,IP,Estado\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s",%s,"%s","%s","%s"' . "\n",
                $log->created_at->format('d/m/Y H:i:s'),
                $log->user?->name ?? 'Sistema',
                $log->getActionLabel(),
                $log->model_type ?? '-',
                $log->model_id ?? '-',
                str_replace('"', '""', $log->description ?? ''),
                $log->ip_address ?? '-',
                $log->status
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="auditoria_' . date('Y-m-d_H-i-s') . '.csv"',
        ]);
    }

    /**
     * Limpiar logs antiguos (más de 90 días)
     */
    public function cleanup()
    {
        $deletedCount = AuditLog::where('created_at', '<', now()->subDays(90))->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deletedCount,
            'message' => "Se eliminaron $deletedCount registros de auditoría más antiguos de 90 días",
        ]);
    }
}
