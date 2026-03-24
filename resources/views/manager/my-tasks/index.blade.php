@extends('layouts.app')

@section('title', 'My Tasks')

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    .task-stats { display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:1100px){.task-stats{grid-template-columns:repeat(3,1fr);}}
    @media(max-width:640px) {.task-stats{grid-template-columns:repeat(2,1fr);}}
    .stat-box { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);padding:1rem 1.125rem;display:flex;align-items:center;gap:.875rem; }
    .stat-box-icon { width:40px;height:40px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#fff; }
    .stat-box-icon .material-symbols-outlined { font-size:20px;font-variation-settings:'FILL' 1; }
    .stat-val { font-size:1.5rem;font-weight:800;letter-spacing:-.04em;line-height:1;color:var(--text-main); }
    .stat-lbl { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:.2rem; }

    .card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm); }
    .filter-bar { display:flex;gap:.75rem;flex-wrap:wrap;padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:#fafbfc; }
    .filter-bar select { font-size:.8125rem;padding:.375rem .625rem;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-main);background:var(--surface);height:34px; }
    .filter-bar select:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .btn-filter { height:34px;padding:0 .875rem;font-size:.8125rem;font-weight:600;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;text-decoration:none; }
    .btn-filter:hover { background:var(--bg-light);color:var(--text-main); }
    .btn-filter.active { background:var(--primary-subtle);border-color:var(--primary);color:var(--primary); }

    .task-table { width:100%;border-collapse:collapse;font-size:.8375rem; }
    .task-table th { padding:.625rem 1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:2px solid var(--border);background:#f8fafc;white-space:nowrap; }
    .task-table td { padding:.75rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle; }
    .task-table tr:last-child td { border-bottom:none; }
    .task-table tr:hover td { background:#f8fafc; }
    .task-table tr.row-overdue td { background:#fff5f5; }
    .task-table tr.row-overdue:hover td { background:#fee2e2; }

    .task-title { font-weight:600;color:var(--text-main);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .task-desc { font-size:.775rem;color:var(--text-muted);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.15rem; }
    .overdue-badge { display:inline-flex;align-items:center;gap:.2rem;background:#fee2e2;color:#b91c1c;font-size:.65rem;font-weight:700;padding:.1rem .4rem;border-radius:4px;vertical-align:middle;margin-left:.35rem; }

    .pri { display:inline-block;padding:.2rem .55rem;border-radius:4px;font-size:.7rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase; }
    .pri-low    { background:#dcfce7;color:#15803d; }
    .pri-medium { background:#fef9c3;color:#92400e; }
    .pri-high   { background:#fee2e2;color:#b91c1c; }

    .sts { display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .6rem;border-radius:999px;font-size:.75rem;font-weight:600; }
    .sts .material-symbols-outlined { font-size:.85rem; }
    .sts-pending     { background:#f1f5f9;color:#475569; }
    .sts-in_progress { background:#eff6ff;color:#1d4ed8; }
    .sts-completed   { background:#f0fdf4;color:#15803d; }

    .act-btn { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;transition:all .15s;text-decoration:none; }
    .act-btn:hover { background:var(--bg-light);color:var(--primary);border-color:var(--primary-subtle); }
    .act-btn .material-symbols-outlined { font-size:1rem; }

    .empty-state { text-align:center;padding:3rem 1rem;color:var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size:3rem;display:block;margin-bottom:.75rem;opacity:.35; }
    .empty-state p { font-size:.9rem;margin:0; }
</style>
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">My Tasks</h1>
    <p class="page-subtitle" style="margin-bottom:0;">Tasks assigned to you.</p>
</div>

@if(session('success'))
    <div class="flash flash-success">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif
@if(session('error'))
    <div class="flash flash-error">
        <span class="material-symbols-outlined">error</span>
        {{ session('error') }}
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif

{{-- Stats --}}
<div class="task-stats">
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#3b82f6;">
            <span class="material-symbols-outlined">task</span>
        </div>
        <div>
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-lbl">Total</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#64748b;">
            <span class="material-symbols-outlined">radio_button_unchecked</span>
        </div>
        <div>
            <div class="stat-val">{{ $stats['pending'] }}</div>
            <div class="stat-lbl">Pending</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#f59e0b;">
            <span class="material-symbols-outlined">autorenew</span>
        </div>
        <div>
            <div class="stat-val">{{ $stats['in_progress'] }}</div>
            <div class="stat-lbl">In Progress</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#22c55e;">
            <span class="material-symbols-outlined">check_circle</span>
        </div>
        <div>
            <div class="stat-val">{{ $stats['completed'] }}</div>
            <div class="stat-lbl">Completed</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#ef4444;">
            <span class="material-symbols-outlined">schedule</span>
        </div>
        <div>
            <div class="stat-val">{{ $stats['overdue'] }}</div>
            <div class="stat-lbl">Overdue</div>
        </div>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('manager.my-tasks.index') }}">
        <div class="filter-bar">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
            </select>
            <select name="priority">
                <option value="">All Priority</option>
                <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low"    {{ request('priority') === 'low'    ? 'selected' : '' }}>Low</option>
            </select>
            <button type="submit" class="btn-filter active">
                <span class="material-symbols-outlined" style="font-size:.95rem;">filter_list</span>
                Filter
            </button>
            @if(request()->hasAny(['status','priority']))
                <a href="{{ route('manager.my-tasks.index') }}" class="btn-filter">Clear</a>
            @endif
        </div>
    </form>

    <div style="overflow-x:auto;">
        <table class="task-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Assigned By</th>
                    <th>Priority</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Status Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $i => $task)
                    @php $overdue = $task->isOverdue(); @endphp
                    <tr class="{{ $overdue ? 'row-overdue' : '' }}">
                        <td style="color:var(--text-muted);font-size:.8rem;">{{ $tasks->firstItem() + $i }}</td>
                        <td>
                            <div class="task-title">
                                {{ $task->title }}
                                @if($overdue)
                                    <span class="overdue-badge">
                                        <span class="material-symbols-outlined" style="font-size:.7rem;">schedule</span>Overdue
                                    </span>
                                @endif
                            </div>
                            @if($task->description)
                                <div class="task-desc">{{ $task->description }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.8375rem;">{{ $task->assigner->name }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">Manager</div>
                        </td>
                        <td><span class="pri pri-{{ $task->priority }}">{{ $task->priorityLabel() }}</span></td>
                        <td style="font-size:.8125rem;white-space:nowrap;">{{ $task->start_date->format('d M Y') }}</td>
                        <td style="font-size:.8125rem;white-space:nowrap;{{ $overdue ? 'color:#b91c1c;font-weight:600;' : '' }}">
                            {{ $task->due_date->format('d M Y') }}
                        </td>
                        <td>
                            <span class="sts sts-{{ $task->status }}">
                                @if($task->status === 'completed')<span class="material-symbols-outlined">check_circle</span>
                                @elseif($task->status === 'in_progress')<span class="material-symbols-outlined">autorenew</span>
                                @else<span class="material-symbols-outlined">radio_button_unchecked</span>@endif
                                {{ $task->statusLabel() }}
                            </span>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap;">
                            @if($task->status === 'completed' && $task->completed_at)
                                <span style="color:#15803d;">{{ $task->completed_at->format('d M Y, h:i A') }}</span>
                            @elseif($task->status === 'in_progress' && $task->started_at)
                                <span style="color:#1d4ed8;">{{ $task->started_at->format('d M Y, h:i A') }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('manager.my-tasks.show', $task) }}" class="act-btn" title="View / Update Status">
                                <span class="material-symbols-outlined">visibility</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <span class="material-symbols-outlined">assignment_ind</span>
                                <p>No tasks assigned to you yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
