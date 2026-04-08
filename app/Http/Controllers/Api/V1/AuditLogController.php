<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with('user:id,name');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->get('per_page', 25));

        $data = $logs->getCollection()->map(fn ($log) => [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'user_name' => $log->user?->name,
            'action' => $log->action,
            'module' => $log->module,
            'record_id' => $log->record_id,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Audit log retrieved successfully',
            'data' => [
                'id' => $auditLog->id,
                'user_id' => $auditLog->user_id,
                'user_name' => $auditLog->user?->name,
                'action' => $auditLog->action,
                'module' => $auditLog->module,
                'record_id' => $auditLog->record_id,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'ip_address' => $auditLog->ip_address,
                'user_agent' => $auditLog->user_agent,
                'created_at' => $auditLog->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::query()->with('user:id,name');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderByDesc('created_at');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-logs-' . date('Y-m-d-His') . '.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Action', 'Module', 'Record ID', 'Old Values', 'New Values', 'IP Address', 'Created At']);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->user?->name ?? '',
                        $log->action,
                        $log->module,
                        $log->record_id ?? '',
                        is_array($log->old_values) ? json_encode($log->old_values) : '',
                        is_array($log->new_values) ? json_encode($log->new_values) : '',
                        $log->ip_address ?? '',
                        $log->created_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
