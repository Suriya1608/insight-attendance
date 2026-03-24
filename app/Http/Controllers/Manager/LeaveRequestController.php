<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $manager = $this->currentUser();

        // Base query: all requests where this manager is L1 or L2
        $base = LeaveRequest::query()
            ->where(function ($q) use ($manager) {
                $q->where('l1_manager_id', $manager->id)
                  ->orWhere('l2_manager_id', $manager->id);
            });

        // KPI stats (before filters)
        $stats = [
            'total'    => (clone $base)->count(),
            'pending'  => (clone $base)->where(function ($q) use ($manager) {
                              $q->where(function ($q2) use ($manager) {
                                  $q2->where('status', 'pending')
                                     ->where('l1_manager_id', $manager->id);
                              })->orWhere(function ($q2) use ($manager) {
                                  $q2->where('status', 'approved_l1')
                                     ->where('l2_manager_id', $manager->id);
                              });
                          })->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'declined' => (clone $base)->where('status', 'rejected')->count(),
        ];

        $request->validate([
            'request_type' => ['nullable', 'in:leave,permission'],
            'status'       => ['nullable', 'in:pending,approved_l1,approved,rejected'],
            'from_date'    => ['nullable', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'employee'     => ['nullable', 'string', 'max:100'],
        ]);

        // Apply filters
        $query = $base->with(['user', 'user.department']);

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->input('request_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
        if ($request->filled('employee')) {
            // Escape LIKE wildcards to prevent wildcard injection
            $safe = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->input('employee'));
            $query->whereHas('user', function ($q) use ($safe) {
                $q->where('name', 'like', '%' . $safe . '%')
                  ->orWhere('employee_code', 'like', '%' . $safe . '%');
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Team members for employee filter dropdown
        $teamMembers = User::where(function ($q) use ($manager) {
            $q->where('level1_manager_id', $manager->id)
              ->orWhere('level2_manager_id', $manager->id);
        })->where('emp_status', 'active')->orderBy('name')->get(['id', 'name', 'employee_code']);

        return view('manager.leave-requests.index', compact('requests', 'stats', 'teamMembers', 'manager'));
    }
}
