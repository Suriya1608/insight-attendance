<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveTransaction;
use App\Models\User;
use App\Notifications\LeaveRequestApprovedNotification;
use App\Notifications\LeaveRequestL1ApprovedNotification;
use App\Notifications\LeaveRequestRejectedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = $this->currentUser()->load('department');
        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        // CL annual balance
        $clBalance = LeaveBalance::clForYear($user->id, $year);

        // Permission: count used this month vs limit
        $permLimit     = $user->department?->permissions_per_month ?? 2;
        $permUsedMonth = $this->permissionsUsedThisMonth($user->id, $year, $month);
        $permRemaining = max(0, $permLimit - $permUsedMonth);

        // Saturday leave eligibility
        $hasSatLeave = $user->department?->has_saturday_leave ?? false;

        // My requests (paginated)
        $myRequests = LeaveRequest::where('user_id', $user->id)
            ->with(['l1Manager', 'l2Manager'])
            ->orderByDesc('from_date')
            ->paginate(15, ['*'], 'my_page');

        // Pending approvals (where I am L1 or L2)
        $pendingApprovals = LeaveRequest::pendingApprovalBy($user->id)
            ->with(['user', 'l1Manager', 'l2Manager'])
            ->orderBy('created_at')
            ->get();

        // Stats
        $pendingCount  = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved_l1'])->count();
        $approvedCount = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')->count();

        return view('leave-requests.index', compact(
            'clBalance', 'permRemaining', 'permLimit', 'hasSatLeave',
            'myRequests', 'pendingApprovals',
            'pendingCount', 'approvedCount',
        ));
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function create()
    {
        $user  = $this->currentUser()->load('department');
        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        $clBalance     = LeaveBalance::clForYear($user->id, $year);
        $permLimit     = $user->department?->permissions_per_month ?? 2;
        $hoursPerPerm  = $user->department?->hours_per_permission ?? 2;
        $permUsedMonth = $this->permissionsUsedThisMonth($user->id, $year, $month);
        $permRemaining = max(0, $permLimit - $permUsedMonth);
        $hasSatLeave   = $user->department?->has_saturday_leave ?? false;

        // CL days already used/pending this month (sum of cl_days on approved/pending requests)
        $clUsedMonth = (int) LeaveRequest::where('user_id', $user->id)
            ->where('request_type', 'leave')
            ->whereIn('status', ['pending', 'approved_l1', 'approved'])
            ->whereYear('from_date', $year)
            ->whereMonth('from_date', $month)
            ->sum('cl_days');
        $clMonthRemaining = max(0, 3 - $clUsedMonth);

        // Holiday dates from today onward (for JS preview)
        $holidayDates = Holiday::active()
            ->where('date', '>=', $now->toDateString())
            ->pluck('date')
            ->map(fn ($d) => $d->toDateString())
            ->values()
            ->toArray();

        return view('leave-requests.create', compact(
            'clBalance', 'clMonthRemaining',
            'permRemaining', 'permLimit', 'hoursPerPerm',
            'hasSatLeave', 'holidayDates',
        ));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user        = $this->currentUser()->load('department');
        $requestType = $request->input('request_type');

        // ── Validate ───────────────────────────────────────────────────────────
        $rules = [
            'request_type' => 'required|in:leave,permission',
            'reason'       => 'required|string|max:1000',
            'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ];

        if ($requestType === 'leave') {
            $rules['from_date'] = 'required|date|after_or_equal:today';
            $rules['to_date']   = 'required|date|after_or_equal:from_date';
        } else {
            $rules['request_date']     = 'required|date|after_or_equal:today';
            $rules['permission_hours'] = 'required|numeric|min:0.5|max:' .
                ($user->department?->hours_per_permission ?? 2);
        }

        $request->validate($rules);

        // ── Attachment ─────────────────────────────────────────────────────────
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $l1Id = $user->level1_manager_id;
        $l2Id = $user->level2_manager_id;

        // ══════════════════════════════════════════════════════════════════════
        // PERMISSION
        // ══════════════════════════════════════════════════════════════════════
        if ($requestType === 'permission') {
            $requestDate = Carbon::parse($request->input('request_date'));
            $year        = $requestDate->year;
            $month       = $requestDate->month;

            // Monthly limit check
            $permLimit = $user->department?->permissions_per_month ?? 2;
            $permUsed  = $this->permissionsUsedThisMonth($user->id, $year, $month);
            if ($permUsed >= $permLimit) {
                return back()
                    ->withErrors(['request_type' => "You have already used all {$permLimit} permissions for " .
                        $requestDate->format('F Y') . '.'])
                    ->withInput();
            }

            // Duplicate permission on same date
            $duplicate = LeaveRequest::where('user_id', $user->id)
                ->where('request_type', 'permission')
                ->whereIn('status', ['pending', 'approved_l1', 'approved'])
                ->where('request_date', $requestDate->toDateString())
                ->exists();
            if ($duplicate) {
                return back()
                    ->withErrors(['request_date' => 'You already have an active permission request for this date.'])
                    ->withInput();
            }

            $lr = LeaveRequest::create([
                'user_id'          => $user->id,
                'request_type'     => 'permission',
                'leave_type'       => null,
                'request_date'     => $requestDate->toDateString(),
                'from_date'        => $requestDate->toDateString(),
                'to_date'          => $requestDate->toDateString(),
                'total_days'       => 1,
                'cl_days'          => 0,
                'lop_days'         => 0,
                'permission_hours' => (float) $request->input('permission_hours'),
                'reason'           => $request->input('reason'),
                'attachment'       => $attachmentPath,
                'status'           => 'pending',
                'l1_manager_id'    => $l1Id,
                'l2_manager_id'    => $l2Id,
                'auto_lop'         => false,
            ]);

            $this->notifyAfterSubmit($lr, $l1Id, $l2Id);

            return redirect()->route('leave-requests.index')
                ->with('success', 'Permission request submitted successfully.');
        }

        // ══════════════════════════════════════════════════════════════════════
        // LEAVE (date range)
        // ══════════════════════════════════════════════════════════════════════
        $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
        $toDate   = Carbon::parse($request->input('to_date'))->startOfDay();

        // Get holidays in the requested range
        $holidays = Holiday::active()
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->pluck('date')
            ->map(fn ($d) => $d->toDateString())
            ->toArray();

        // Calculate working days (exclude Sundays + holidays)
        $workingDays = $this->getWorkingDays($fromDate, $toDate, $holidays);
        $totalDays   = count($workingDays);

        if ($totalDays === 0) {
            return back()
                ->withErrors(['from_date' => 'The selected date range contains no working days (all Sundays or holidays).'])
                ->withInput();
        }

        // Overlap check with existing active leave requests
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->where('request_type', 'leave')
            ->whereIn('status', ['pending', 'approved_l1', 'approved'])
            ->where('from_date', '<=', $toDate->toDateString())
            ->where('to_date', '>=', $fromDate->toDateString())
            ->exists();
        if ($overlap) {
            return back()
                ->withErrors(['from_date' => 'You already have an active leave request that overlaps with the selected dates.'])
                ->withInput();
        }

        // ── CL vs LOP auto-split ───────────────────────────────────────────────
        $year  = $fromDate->year;
        $month = $fromDate->month;

        $clBal = LeaveBalance::clForYear($user->id, $year);

        $clUsedMonth = (int) LeaveRequest::where('user_id', $user->id)
            ->where('request_type', 'leave')
            ->whereIn('status', ['pending', 'approved_l1', 'approved'])
            ->whereYear('from_date', $year)
            ->whereMonth('from_date', $month)
            ->sum('cl_days');

        $clMonthRemaining = max(0, 3 - $clUsedMonth);
        $clAvailable      = min((int) floor($clBal->balance), $clMonthRemaining);
        $clDays           = min($totalDays, max(0, $clAvailable));
        $lopDays          = $totalDays - $clDays;
        $leaveType        = $clDays > 0 ? 'CL' : 'LOP';
        $autoLop          = $lopDays > 0;

        // ── Create record ──────────────────────────────────────────────────────
        $lr = LeaveRequest::create([
            'user_id'       => $user->id,
            'request_type'  => 'leave',
            'leave_type'    => $leaveType,
            'request_date'  => $fromDate->toDateString(),
            'from_date'     => $fromDate->toDateString(),
            'to_date'       => $toDate->toDateString(),
            'total_days'    => $totalDays,
            'cl_days'       => $clDays,
            'lop_days'      => $lopDays,
            'permission_hours' => null,
            'reason'        => $request->input('reason'),
            'attachment'    => $attachmentPath,
            'status'        => 'pending',
            'l1_manager_id' => $l1Id,
            'l2_manager_id' => $l2Id,
            'auto_lop'      => $autoLop,
        ]);

        $this->notifyAfterSubmit($lr, $l1Id, $l2Id);

        $msg = 'Leave request submitted successfully.';
        if ($autoLop) {
            $msg .= " Note: {$clDays} day(s) as CL, {$lopDays} day(s) auto-converted to LOP due to balance/cap limits.";
        }

        return redirect()->route('leave-requests.index')->with('success', $msg);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(LeaveRequest $leaveRequest)
    {
        $user = $this->currentUser();

        if (
            $leaveRequest->user_id !== $user->id &&
            $leaveRequest->l1_manager_id !== $user->id &&
            $leaveRequest->l2_manager_id !== $user->id &&
            !$user->isAdmin()
        ) {
            abort(403);
        }

        $leaveRequest->load(['user', 'l1Manager', 'l2Manager']);

        $canApproveL1 = $user->id === $leaveRequest->l1_manager_id && $leaveRequest->isPending();
        $canApproveL2 = $user->id === $leaveRequest->l2_manager_id && $leaveRequest->isApprovedL1();

        return view('leave-requests.show', compact('leaveRequest', 'canApproveL1', 'canApproveL2'));
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $user    = $this->currentUser();
        $remarks = trim($request->input('remarks', ''));

        if ($leaveRequest->isPending() && $leaveRequest->l1_manager_id === $user->id) {
            $leaveRequest->load(['user', 'l1Manager', 'l2Manager']);

            if ($leaveRequest->l2_manager_id) {
                $leaveRequest->update([
                    'status'         => 'approved_l1',
                    'l1_remarks'     => $remarks,
                    'l1_actioned_at' => now(),
                ]);
                if ($l2 = User::find($leaveRequest->l2_manager_id)) {
                    $l2->notify(new LeaveRequestL1ApprovedNotification($leaveRequest));
                    AppNotification::notify(
                        $leaveRequest->l2_manager_id,
                        'Leave Request Awaiting Your Approval',
                        "{$leaveRequest->user->name}'s {$leaveRequest->type_label} request was approved by L1 and awaits your final approval.",
                        'leave_request',
                        $leaveRequest->id,
                        route('leave-requests.show', $leaveRequest)
                    );
                }
            } else {
                $this->finalApprove($leaveRequest, $remarks, 'l1');
            }

        } elseif ($leaveRequest->isApprovedL1() && $leaveRequest->l2_manager_id === $user->id) {
            $leaveRequest->load(['user', 'l1Manager', 'l2Manager']);
            $this->finalApprove($leaveRequest, $remarks, 'l2');

        } else {
            abort(403);
        }

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'Request approved successfully.');
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $user = $this->currentUser();
        $request->validate(['remarks' => 'required|string|max:500']);
        $remarks = $request->input('remarks');

        if ($leaveRequest->isPending() && $leaveRequest->l1_manager_id === $user->id) {
            $leaveRequest->update([
                'status'         => 'rejected',
                'l1_remarks'     => $remarks,
                'l1_actioned_at' => now(),
            ]);
        } elseif ($leaveRequest->isApprovedL1() && $leaveRequest->l2_manager_id === $user->id) {
            $leaveRequest->update([
                'status'         => 'rejected',
                'l2_remarks'     => $remarks,
                'l2_actioned_at' => now(),
            ]);
        } else {
            abort(403);
        }

        $leaveRequest->load('user');
        $leaveRequest->user->notify(new LeaveRequestRejectedNotification($leaveRequest));
        AppNotification::notify(
            $leaveRequest->user_id,
            $leaveRequest->type_label . ' Request Declined',
            "Your {$leaveRequest->type_label} request has been declined by your manager.",
            'leave_request',
            $leaveRequest->id,
            route('leave-requests.show', $leaveRequest)
        );

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'Request rejected.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Return array of working date strings between $from and $to
     * (excluding Sundays and the provided holiday date strings).
     */
    private function getWorkingDays(Carbon $from, Carbon $to, array $holidayDates): array
    {
        $days    = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            if ($current->dayOfWeek !== Carbon::SUNDAY &&
                !in_array($current->toDateString(), $holidayDates, true)) {
                $days[] = $current->toDateString();
            }
            $current->addDay();
        }

        return $days;
    }

    private function permissionsUsedThisMonth(int $userId, int $year, int $month): int
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('request_type', 'permission')
            ->whereIn('status', ['pending', 'approved_l1', 'approved'])
            ->whereMonth('request_date', $month)
            ->whereYear('request_date', $year)
            ->count();
    }

    private function notifyAfterSubmit(LeaveRequest $lr, ?int $l1Id, ?int $l2Id): void
    {
        $lr->load(['user', 'l1Manager', 'l2Manager']);

        if ($l1Id && ($l1 = User::find($l1Id))) {
            $l1->notify(new LeaveRequestSubmittedNotification($lr));
            AppNotification::notify(
                $l1Id,
                'New ' . $lr->type_label . ' Request',
                "{$lr->user->name} submitted a {$lr->type_label} request and is awaiting your approval.",
                'leave_request',
                $lr->id,
                route('leave-requests.show', $lr)
            );
        } elseif ($l2Id && ($l2 = User::find($l2Id))) {
            // No L1 — auto-advance to L2
            $lr->update(['status' => 'approved_l1', 'l1_actioned_at' => now()]);
            $l2->notify(new LeaveRequestL1ApprovedNotification($lr));
            AppNotification::notify(
                $l2Id,
                'Leave Request Awaiting Your Approval',
                "{$lr->user->name}'s {$lr->type_label} request is awaiting your final approval.",
                'leave_request',
                $lr->id,
                route('leave-requests.show', $lr)
            );
        }
        // If neither manager set, request stays 'pending' for admin
    }

    private function finalApprove(LeaveRequest $lr, string $remarks, string $level): void
    {
        $remarkField = $level === 'l1' ? 'l1_remarks'     : 'l2_remarks';
        $actionField = $level === 'l1' ? 'l1_actioned_at' : 'l2_actioned_at';

        $lr->update([
            'status'     => 'approved',
            $remarkField => $remarks,
            $actionField => now(),
        ]);

        // ── Balance debit ──────────────────────────────────────────────────────
        if ($lr->request_type === 'leave') {
            $date = $lr->from_date ?? $lr->request_date;

            if ($lr->leave_type === 'CL' && $lr->cl_days > 0) {
                $bal = LeaveBalance::clForYear($lr->user_id, $date->year);
                $bal->increment('used', $lr->cl_days);
                LeaveTransaction::create([
                    'user_id'          => $lr->user_id,
                    'leave_type'       => 'CL',
                    'transaction_type' => 'debit',
                    'amount'           => $lr->cl_days,
                    'year'             => $date->year,
                    'month'            => $date->month,
                    'date'             => $date,
                    'remarks'          => "CL leave approved ({$lr->cl_days} day(s)) — request #{$lr->id}",
                ]);
            }

            if ($lr->leave_type === 'saturday_leave') {
                $bal = LeaveBalance::monthlyFor($lr->user_id, 'saturday_leave', $date->year, $date->month);
                $bal->increment('used', 1);
                LeaveTransaction::create([
                    'user_id'          => $lr->user_id,
                    'leave_type'       => 'saturday_leave',
                    'transaction_type' => 'debit',
                    'amount'           => 1,
                    'year'             => $date->year,
                    'month'            => $date->month,
                    'date'             => $date,
                    'remarks'          => "Saturday leave approved — request #{$lr->id}",
                ]);
            }
            // LOP days: no balance deduction needed

        } elseif ($lr->request_type === 'permission') {
            $date = $lr->request_date;
            $bal  = LeaveBalance::monthlyFor($lr->user_id, 'permission', $date->year, $date->month);
            $bal->increment('used', 1);
            LeaveTransaction::create([
                'user_id'          => $lr->user_id,
                'leave_type'       => 'permission',
                'transaction_type' => 'debit',
                'amount'           => 1,
                'year'             => $date->year,
                'month'            => $date->month,
                'date'             => $date,
                'remarks'          => "Permission approved — request #{$lr->id}",
            ]);

            // Adjust attendance working hours
            $att = Attendance::where('user_id', $lr->user_id)
                ->where('date', $date->toDateString())
                ->first();
            if ($att) {
                $att->increment('permission_hours', $lr->permission_hours);
            }
        }

        // Notify requester
        $lr->user->notify(new LeaveRequestApprovedNotification($lr));
        AppNotification::notify(
            $lr->user_id,
            $lr->type_label . ' Request Approved',
            "Your {$lr->type_label} request has been fully approved.",
            'leave_request',
            $lr->id,
            route('leave-requests.show', $lr)
        );
    }
}
