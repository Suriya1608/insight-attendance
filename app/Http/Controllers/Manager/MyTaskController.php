<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Task;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $manager = $this->currentUser();

        $query = Task::where('assigned_to', $manager->id)
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
            'total'       => Task::where('assigned_to', $manager->id)->count(),
            'pending'     => Task::where('assigned_to', $manager->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_to', $manager->id)->where('status', 'in_progress')->count(),
            'completed'   => Task::where('assigned_to', $manager->id)->where('status', 'completed')->count(),
            'overdue'     => Task::where('assigned_to', $manager->id)
                ->whereNotIn('status', ['completed'])
                ->where('due_date', '<', $today)
                ->count(),
        ];

        return view('manager.my-tasks.index', compact('tasks', 'stats'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_to !== $manager->id) {
            abort(403);
        }

        $task->load(['assigner', 'comments.user']);

        return view('manager.my-tasks.show', compact('task'));
    }

    // ── Update Status ─────────────────────────────────────────────────────────

    public function updateStatus(Request $request, Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_to !== $manager->id) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $wasCompleted  = $task->status === 'completed';
        $wasInProgress = $task->status === 'in_progress';
        $newStatus     = $request->status;

        $updates = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && ! $wasInProgress && ! $task->started_at) {
            $updates['started_at'] = now();
        }
        if ($newStatus === 'completed' && ! $wasCompleted) {
            $updates['completed_at'] = now();
        }

        $task->update($updates);

        // Notify the assigner about the status change
        $task->load(['assignee', 'assigner']);
        $taskUrl   = route('manager.tasks.show', $task);
        $statusLbl = ucwords(str_replace('_', ' ', $newStatus));

        $task->assigner->notify(new TaskStatusChangedNotification($task, $newStatus, $taskUrl));

        AppNotification::notify(
            $task->assigned_by,
            "Task Status Updated: {$task->title}",
            "{$manager->name} updated task \"{$task->title}\" status to {$statusLbl}.",
            'task',
            $task->id,
            $taskUrl
        );

        if ($newStatus === 'completed') {
            return back()->with('success', 'Task marked as completed. Your manager has been notified.');
        }

        return back()->with('success', "Task status updated to \"{$statusLbl}\".");
    }

    // ── Add Comment ───────────────────────────────────────────────────────────

    public function addComment(Request $request, Task $task)
    {
        $manager = $this->currentUser();

        if ($task->assigned_to !== $manager->id) {
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
            $task->assigned_by,
            "Task Update: {$task->title}",
            "{$manager->name} added an update on task \"{$task->title}\": \"{$snippet}\" — " . now()->format('d M Y, h:i A'),
            'task',
            $task->id,
            route('manager.tasks.show', $task)
        );

        return back()->with('success', 'Comment added.');
    }
}
