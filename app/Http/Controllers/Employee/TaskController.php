<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Task;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $employee = $this->currentUser();

        $query = Task::where('assigned_to', $employee->id)
            ->with('assigner');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        $tasks = $query->orderBy('due_date')->paginate(15)->withQueryString();

        $today = now()->toDateString();
        $stats = [
            'total'       => Task::where('assigned_to', $employee->id)->count(),
            'pending'     => Task::where('assigned_to', $employee->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_to', $employee->id)->where('status', 'in_progress')->count(),
            'completed'   => Task::where('assigned_to', $employee->id)->where('status', 'completed')->count(),
            'overdue'     => Task::where('assigned_to', $employee->id)
                ->whereNotIn('status', ['completed'])
                ->where('due_date', '<', $today)
                ->count(),
        ];

        return view('employee.tasks.index', compact('tasks', 'stats'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Task $task)
    {
        $employee = $this->currentUser();

        if ($task->assigned_to !== $employee->id) {
            abort(403);
        }

        $task->load(['assigner', 'comments.user']);

        return view('employee.tasks.show', compact('task'));
    }

    // ── Update Status ─────────────────────────────────────────────────────────

    public function updateStatus(Request $request, Task $task)
    {
        $employee = $this->currentUser();

        if ($task->assigned_to !== $employee->id) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $wasCompleted  = $task->status === 'completed';
        $wasInProgress = $task->status === 'in_progress';

        $updates = ['status' => $request->status];

        if ($request->status === 'in_progress' && ! $wasInProgress && ! $task->started_at) {
            $updates['started_at'] = now();
        }
        if ($request->status === 'completed' && ! $wasCompleted) {
            $updates['completed_at'] = now();
        }

        $task->update($updates);

        if ($request->status === 'completed' && ! $wasCompleted) {
            $task->load(['assignee', 'assigner']);
            $task->assigner->notify(new TaskCompletedNotification($task));
            AppNotification::notify(
                $task->assigned_by,
                'Task Completed',
                "{$task->assignee->name} has completed the task: \"{$task->title}\".",
                'task',
                $task->id,
                route('manager.tasks.show', $task)
            );
        }

        $label = ucwords(str_replace('_', ' ', $request->status));
        return back()->with('success', "Task status updated to \"{$label}\".");
    }

    // ── Add Comment ───────────────────────────────────────────────────────────

    public function addComment(Request $request, Task $task)
    {
        $employee = $this->currentUser();

        if ($task->assigned_to !== $employee->id) {
            abort(403);
        }

        $request->validate(['comment' => 'required|string|max:2000']);

        $task->comments()->create([
            'user_id' => $employee->id,
            'comment' => $request->comment,
        ]);

        $snippet = mb_strlen($request->comment) > 100
            ? mb_substr($request->comment, 0, 100) . '…'
            : $request->comment;

        AppNotification::notify(
            $task->assigned_by,
            "Task Update: {$task->title}",
            "{$employee->name} added an update on task \"{$task->title}\": \"{$snippet}\" — " . now()->format('d M Y, h:i A'),
            'task',
            $task->id,
            route('manager.tasks.show', $task)
        );

        return back()->with('success', 'Update added successfully.');
    }
}
