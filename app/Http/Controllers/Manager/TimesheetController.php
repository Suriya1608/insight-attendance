<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\TimesheetComment;
use App\Models\User;
use App\Notifications\TimesheetApprovedNotification;
use App\Notifications\TimesheetCommentNotification;
use App\Notifications\TimesheetL1ApprovedNotification;
use App\Notifications\TimesheetRejectedNotification;
use App\Support\TimesheetSupport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $manager = $this->currentUser();
        $month = $request->input('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = now()->startOfMonth();
        }

        $teamIds = $this->teamMemberIds($manager->id);

        $query = Timesheet::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$monthDate->toDateString(), $monthDate->copy()->endOfMonth()->toDateString()])
            ->with(['user', 'user.department', 'entries']);

        // Only show submitted timesheets (exclude drafts)
        $query->where('status', '!=', 'draft');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($employeeId = $request->input('employee_id')) {
            if (in_array((int) $employeeId, $teamIds, true)) {
                $query->where('user_id', $employeeId);
            }
        }

        $timesheets = $query->orderByDesc('date')->orderBy('user_id')->paginate(20)->withQueryString();
        $teamMembers = $this->teamMembers($manager->id);
        $pendingCount = Timesheet::pendingApprovalBy($manager->id)->count();

        return view('manager.timesheets.index', compact('timesheets', 'teamMembers', 'pendingCount', 'month', 'monthDate'));
    }

    public function show(Timesheet $timesheet)
    {
        $manager = $this->currentUser();

        if (! $this->canManageTimesheet($manager->id, $timesheet)) {
            abort(403);
        }

        $timesheet->load(['user', 'user.department', 'entries', 'comments.user', 'comments.replies.user', 'l1Manager', 'l2Manager']);

        $attendance = Attendance::where('user_id', $timesheet->user_id)
            ->where('date', $timesheet->date->toDateString())
            ->first();

        $gridMeta = TimesheetSupport::buildGridMeta($attendance, $timesheet->entries);

        return view('manager.timesheets.show', compact('timesheet', 'manager', 'attendance', 'gridMeta'));
    }

    public function approve(Request $request, Timesheet $timesheet)
    {
        $manager = $this->currentUser();

        if (! $this->canManageTimesheet($manager->id, $timesheet)) {
            abort(403);
        }

        $request->validate(['remarks' => 'nullable|string|max:1000']);

        $isL1 = $timesheet->l1_manager_id === $manager->id && $timesheet->status === 'pending_l1';
        $isL2 = $timesheet->l2_manager_id === $manager->id && $timesheet->status === 'pending_l2';

        if (! $isL1 && ! $isL2) {
            return back()->with('error', 'You cannot approve this timesheet at this stage.');
        }

        $timesheet->load(['user', 'entries', 'l2Manager']);
        $employeeUrl = $this->selfTimesheetUrl($timesheet);

        if ($isL1) {
            $timesheet->update([
                'status' => 'pending_l2',
                'l1_remarks' => $request->remarks,
                'l1_actioned_at' => now(),
            ]);

            if ($timesheet->l2_manager_id) {
                $l2Url = route('manager.timesheets.show', $timesheet);

                $timesheet->l2Manager->notify(new TimesheetL1ApprovedNotification($timesheet, $l2Url));

                AppNotification::notify(
                    $timesheet->l2_manager_id,
                    'Timesheet Awaiting Your Approval',
                    "{$timesheet->user->name}'s timesheet for {$timesheet->date->format('d M Y')} was approved by L1 and needs your final review.",
                    'general',
                    $timesheet->id,
                    $l2Url
                );
            } else {
                $timesheet->update([
                    'status' => 'approved',
                    'l2_actioned_at' => now(),
                ]);

                $timesheet->user->notify(new TimesheetApprovedNotification($timesheet, $employeeUrl));

                AppNotification::notify(
                    $timesheet->user_id,
                    'Timesheet Fully Approved',
                    "Your timesheet for {$timesheet->date->format('d M Y')} has been fully approved.",
                    'general',
                    $timesheet->id,
                    $employeeUrl
                );
            }

            return back()->with('success', 'Timesheet approved at L1. ' . ($timesheet->l2_manager_id ? 'Sent to L2 manager for final review.' : 'Timesheet is now fully approved.'));
        }

        $timesheet->update([
            'status' => 'approved',
            'l2_remarks' => $request->remarks,
            'l2_actioned_at' => now(),
        ]);

        $timesheet->user->notify(new TimesheetApprovedNotification($timesheet, $employeeUrl));

        AppNotification::notify(
            $timesheet->user_id,
            'Timesheet Fully Approved',
            "Your timesheet for {$timesheet->date->format('d M Y')} has been fully approved.",
            'general',
            $timesheet->id,
            $employeeUrl
        );

        return back()->with('success', 'Timesheet fully approved. Employee has been notified.');
    }

    public function reject(Request $request, Timesheet $timesheet)
    {
        $manager = $this->currentUser();

        if (! $this->canManageTimesheet($manager->id, $timesheet)) {
            abort(403);
        }

        $request->validate(['remarks' => 'required|string|max:1000']);

        $isL1 = $timesheet->l1_manager_id === $manager->id && $timesheet->status === 'pending_l1';
        $isL2 = $timesheet->l2_manager_id === $manager->id && $timesheet->status === 'pending_l2';

        if (! $isL1 && ! $isL2) {
            return back()->with('error', 'You cannot reject this timesheet at this stage.');
        }

        $level = $isL1 ? 'L1' : 'L2';

        if ($isL1) {
            $timesheet->update([
                'status' => 'rejected',
                'l1_remarks' => $request->remarks,
                'l1_actioned_at' => now(),
            ]);
        } else {
            $timesheet->update([
                'status' => 'rejected',
                'l2_remarks' => $request->remarks,
                'l2_actioned_at' => now(),
            ]);
        }

        $timesheet->load('user');
        $employeeUrl = $this->selfTimesheetUrl($timesheet);

        $timesheet->user->notify(new TimesheetRejectedNotification(
            $timesheet,
            $employeeUrl,
            $manager->name . " ({$level} Manager)",
            $request->remarks
        ));

        AppNotification::notify(
            $timesheet->user_id,
            'Timesheet Rejected',
            "Your timesheet for {$timesheet->date->format('d M Y')} was rejected by {$manager->name} ({$level}): \"{$request->remarks}\"",
            'general',
            $timesheet->id,
            $employeeUrl
        );

        return back()->with('success', 'Timesheet rejected. Employee has been notified to revise and resubmit.');
    }

    public function addComment(Request $request, Timesheet $timesheet)
    {
        $manager = $this->currentUser();

        if (! $this->canManageTimesheet($manager->id, $timesheet)) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:timesheet_comments,id',
        ]);

        TimesheetComment::create([
            'timesheet_id' => $timesheet->id,
            'user_id' => $manager->id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        $this->notifyCommentParticipants($timesheet, $manager, $request->comment);

        return back()->with('success', 'Comment added.');
    }

    private function selfTimesheetUrl(Timesheet $timesheet): string
    {
        $route = $timesheet->user->isManager() ? 'manager.my-timesheets.show' : 'employee.timesheets.show';

        return route($route, ['date' => $timesheet->date->toDateString()]);
    }

    private function notifyCommentParticipants(Timesheet $timesheet, User $author, string $comment): void
    {
        $timesheet->loadMissing(['user', 'l1Manager', 'l2Manager']);

        $snippet = mb_strlen($comment) > 120
            ? mb_substr($comment, 0, 120) . '...'
            : $comment;

        $participants = User::whereIn('id', array_filter([
            $timesheet->user_id,
            $timesheet->l1_manager_id,
            $timesheet->l2_manager_id,
        ]))
            ->where('id', '!=', $author->id)
            ->get();

        foreach ($participants as $participant) {
            $url = $participant->id === $timesheet->user_id
                ? $this->selfTimesheetUrl($timesheet)
                : route('manager.timesheets.show', $timesheet);

            $participant->notify(new TimesheetCommentNotification($timesheet, $author, $comment, $url));

            AppNotification::notify(
                $participant->id,
                "Comment on Timesheet: {$timesheet->date->format('d M Y')}",
                "{$author->name} commented: \"{$snippet}\" - " . now()->format('d M Y, h:i A'),
                'general',
                $timesheet->id,
                $url
            );
        }
    }

    private function canManageTimesheet(int $managerId, Timesheet $timesheet): bool
    {
        return $timesheet->l1_manager_id === $managerId || $timesheet->l2_manager_id === $managerId;
    }

    private function teamMembers(int $managerId)
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
                ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->orderBy('name')->get();
    }

    private function teamMemberIds(int $managerId): array
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
                ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->pluck('id')->toArray();
    }
}
