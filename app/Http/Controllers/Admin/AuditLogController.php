<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'from'   => ['nullable', 'date_format:Y-m-d'],
            'to'     => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $query = AuditLog::with(['user', 'performedBy'])
            ->orderByDesc('performed_at');

        if ($module = $request->get('module')) {
            $query->where('module_name', $module);
        }

        if ($action = $request->get('action')) {
            $query->where('action_type', $action);
        }

        if ($from = $request->get('from')) {
            $query->where('performed_at', '>=', Carbon::createFromFormat('Y-m-d', $from)->startOfDay());
        }

        if ($to = $request->get('to')) {
            $query->where('performed_at', '<=', Carbon::createFromFormat('Y-m-d', $to)->endOfDay());
        }

        $logs    = $query->paginate(50)->withQueryString();
        $modules = AuditLog::distinct()->orderBy('module_name')->pluck('module_name');

        return view('admin.audit-logs.index', compact('logs', 'modules'));
    }
}
