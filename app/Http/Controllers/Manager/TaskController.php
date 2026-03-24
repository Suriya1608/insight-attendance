<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\IdCrypt;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $manager = $this->currentUser();

        $query = Task::where('assigned_by', $manager->id)
            ->with(['assignee', 'assignee.department']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($encEmployeeId = $request->input('employee_id')) {
            $employeeId = IdCrypt::decode($encEmployeeId);
            if ($employeeId) {
                $query->where('assigned_to', $employeeId);
            }
        }

        $tasks = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $teamMembers = $this->teamMembers($manager->id);

        $today = now()->toDateString();
        $stats = [
            'total'       => Task::where('assigned_by', $manager->id)->count(),
            'pending'     => Task::where('assigned_by', $manager->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_by', $manager->id)->where('status', 'in_progress')->count(),
            'completed'   => Task::where('assigned_by', $manager->id)->where('status', 'completed')->count(),
            'overdue'     => Task::where('assigned_by', $manager->id)
                ->whereNotIn('status', ['completed'])
                ->where('due_date', '<', $today)
                ->count(),
        ];

        return view('manager.tasks.index', compact('tasks', 'teamMembers', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $manager     = $this->currentUser();
        $teamMembers = $this->teamMembers($manager->id);

        return view('manager.tasks.create', compact('teamMembers'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $manager = $this->currentUser();

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'required|exists:users,id',
            'priority'    => 'required|in:low,medium,high',
            'start_date'  => 'required|date',
            'due_date'    => 'required|date|after_or_equal:start_date',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:4096',
        ]);

        if (! $this->isTeamMember($manager->id, (int) $request->assigned_to)) {
            return back()->withErrors(['assigned_to' => 'You can only assign tasks to your team members.'])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('task-attachments', 'public');
        }

        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $manager->id,
            'priority'    => $request->priority,
            'start_date'  => $request->start_date,
            'due_date'    => $request->due_date,
            'status'      => 'pending',
            'attachment'  => $attachmentPath,
        ]);

        $task->load(['assignee', 'assigner']);
        $assigneeUrl = $task->assignee->isManager()
            ? route('manager.my-tasks.show', $task)
            : route('employee.tasks.show', $task);
        $task->assignee->notify(new TaskAssignedNotification($task, $assigneeUrl));
        AppNotification::notify(
            $task->assigned_to,
            'New Task Assigned',
            "You have been assigned a new task: \"{$task->title}\" (Due: {$task->due_date->format('d M Y')}).",
            'task',
            $task->id,
            $assigneeUrl
        );

        return redirect()->route('manager.tasks.index')
            ->with('success', "Task \"{$task->title}\" assigned to {$task->assignee->name} successfully.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_by !== $manager->id) {
            abort(403);
        }

        $task->load(['assignee', 'assignee.department', 'assigner', 'comments.user']);

        return view('manager.tasks.show', compact('task'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_by !== $manager->id) {
            abort(403);
        }

        $teamMembers = $this->teamMembers($manager->id);

        return view('manager.tasks.edit', compact('task', 'teamMembers'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_by !== $manager->id) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'required|exists:users,id',
            'priority'    => 'required|in:low,medium,high',
            'start_date'  => 'required|date',
            'due_date'    => 'required|date|after_or_equal:start_date',
            'status'      => 'required|in:pending,in_progress,completed',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:4096',
        ]);

        if (! $this->isTeamMember($manager->id, (int) $request->assigned_to)) {
            return back()->withErrors(['assigned_to' => 'You can only assign tasks to your team members.'])->withInput();
        }

        $attachmentPath = $task->attachment;
        if ($request->hasFile('attachment')) {
            if ($task->attachment) {
                Storage::disk('public')->delete($task->attachment);
            }
            $attachmentPath = $request->file('attachment')->store('task-attachments', 'public');
        }

        $wasCompleted  = $task->status === 'completed';
        $wasInProgress = $task->status === 'in_progress';

        $updates = [
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'priority'    => $request->priority,
            'start_date'  => $request->start_date,
            'due_date'    => $request->due_date,
            'status'      => $request->status,
            'attachment'  => $attachmentPath,
        ];

        if ($request->status === 'in_progress' && ! $wasInProgress && ! $task->started_at) {
            $updates['started_at'] = now();
        }
        if ($request->status === 'completed' && ! $wasCompleted) {
            $updates['completed_at'] = now();
        }

        $task->update($updates);

        // Notify if newly reassigned
        if ((int) $request->assigned_to !== $task->getOriginal('assigned_to')) {
            $task->load(['assignee', 'assigner']);
            $reassignUrl = $task->assignee->isManager()
                ? route('manager.my-tasks.show', $task)
                : route('employee.tasks.show', $task);
            $task->assignee->notify(new TaskAssignedNotification($task, $reassignUrl));
            AppNotification::notify(
                $task->assigned_to,
                'Task Reassigned to You',
                "The task \"{$task->title}\" has been assigned to you (Due: {$task->due_date->format('d M Y')}).",
                'task',
                $task->id,
                $reassignUrl
            );
        }

        return redirect()->route('manager.tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_by !== $manager->id) {
            abort(403);
        }

        if ($task->attachment) {
            Storage::disk('public')->delete($task->attachment);
        }

        $task->delete();

        return redirect()->route('manager.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    // ── Add Comment ───────────────────────────────────────────────────────────

    public function addComment(Request $request, Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_by !== $manager->id) {
            abort(403);
        }

        $request->validate(['comment' => 'required|string|max:2000']);

        $task->comments()->create([
            'user_id' => $manager->id,
            'comment' => $request->comment,
        ]);

        $snippet = mb_strlen($request->comment) > 100
            ? mb_substr($request->comment, 0, 100) . '…'
            : $request->comment;

        AppNotification::notify(
            $task->assigned_to,
            "Manager Comment: {$task->title}",
            "{$manager->name} commented on your task \"{$task->title}\": \"{$snippet}\" — " . now()->format('d M Y, h:i A'),
            'task',
            $task->id,
            route('employee.tasks.show', $task)
        );

        return back()->with('success', 'Comment added.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function teamMembers(int $managerId)
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
              ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->orderBy('name')->get();
    }

    private function isTeamMember(int $managerId, int $employeeId): bool
    {
        return User::where('id', $employeeId)
            ->where(function ($q) use ($managerId) {
                $q->where('level1_manager_id', $managerId)
                  ->orWhere('level2_manager_id', $managerId);
            })->exists();
    }
}
