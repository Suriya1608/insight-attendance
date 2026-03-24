@extends('layouts.app')

@section('title', 'Task Details')

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    .detail-grid { display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start; }
    @media(max-width:900px){ .detail-grid { grid-template-columns:1fr; } }

    .d-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1rem; }
    .d-card-header { padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;justify-content:space-between;gap:.5rem; }
    .d-card-header h5 { font-size:.88rem;font-weight:700;margin:0;color:var(--text-main);display:flex;align-items:center;gap:.4rem; }
    .d-card-header h5 .material-symbols-outlined { font-size:1rem;color:var(--primary); }
    .d-card-body { padding:1.25rem; }

    .detail-row { display:flex;padding:.625rem 0;border-bottom:1px solid var(--border);gap:1rem; }
    .detail-row:last-child { border-bottom:none; }
    .detail-lbl { font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;min-width:120px;flex-shrink:0;padding-top:.1rem; }
    .detail-val { font-size:.875rem;color:var(--text-main);flex:1; }

    .pri { display:inline-block;padding:.2rem .55rem;border-radius:4px;font-size:.7rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase; }
    .pri-low    { background:#dcfce7;color:#15803d; }
    .pri-medium { background:#fef9c3;color:#92400e; }
    .pri-high   { background:#fee2e2;color:#b91c1c; }

    .sts { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:999px;font-size:.8rem;font-weight:600; }
    .sts .material-symbols-outlined { font-size:.95rem; }
    .sts-pending     { background:#f1f5f9;color:#475569; }
    .sts-in_progress { background:#eff6ff;color:#1d4ed8; }
    .sts-completed   { background:#f0fdf4;color:#15803d; }

    .overdue-chip { display:inline-flex;align-items:center;gap:.25rem;background:#fee2e2;color:#b91c1c;font-size:.75rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;margin-left:.5rem; }
    .overdue-chip .material-symbols-outlined { font-size:.85rem; }

    /* Comments */
    .comment-feed { display:flex;flex-direction:column;gap:.875rem; }
    .comment-item { display:flex;gap:.75rem; }
    .c-avatar { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary) 0%,#6366f1 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0; }
    .c-bubble { flex:1;background:#f8fafc;border:1px solid var(--border);border-radius:0 var(--radius-md) var(--radius-md) var(--radius-md);padding:.625rem .875rem; }
    .c-meta { display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem; }
    .c-name { font-size:.8rem;font-weight:700;color:var(--text-main); }
    .c-role { font-size:.7rem;color:var(--text-muted);background:var(--bg-light);padding:.05rem .4rem;border-radius:3px; }
    .c-time { font-size:.72rem;color:var(--text-muted);margin-left:auto; }
    .c-text { font-size:.8375rem;color:var(--text-main);line-height:1.55; }

    .comment-form textarea { width:100%;padding:.625rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;color:var(--text-main);background:var(--surface);resize:vertical;min-height:80px;transition:border-color .15s,box-shadow .15s; }
    .comment-form textarea:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .comment-form .invalid-feedback { font-size:.78rem;color:#dc2626;margin-top:.3rem; }

    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-outline { background:transparent;border:1px solid var(--border);color:var(--text-secondary);border-radius:var(--radius-sm);padding:.45rem .875rem;font-size:.8375rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:all .15s; }
    .btn-outline:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }
    .btn-danger-outline { background:transparent;border:1px solid #fecaca;color:#dc2626;border-radius:var(--radius-sm);padding:.45rem .875rem;font-size:.8375rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:all .15s; }
    .btn-danger-outline:hover { background:#fff1f2;border-color:#ef4444; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <a href="{{ route('manager.tasks.index') }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='var(--surface)'">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">arrow_back</span>
        </a>
        <div>
            <h1 class="page-title">Task Details</h1>
            <p class="page-subtitle" style="margin-bottom:0;">View and manage task information.</p>
        </div>
    </div>
    <div style="display:flex;gap:.625rem;">
        <a href="{{ route('manager.tasks.edit', $task) }}" class="btn-outline">
            <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>Edit
        </a>
        <form method="POST" action="{{ route('manager.tasks.destroy', $task) }}"
              onsubmit="return confirm('Delete this task? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger-outline">
                <span class="material-symbols-outlined" style="font-size:1rem;">delete</span>Delete
            </button>
        </form>
    </div>
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

<div class="detail-grid">
    <div>
        {{-- Task Info --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">task</span>Task Information</h5>
                @php $overdue = $task->isOverdue(); @endphp
                @if($overdue)
                    <span class="overdue-chip">
                        <span class="material-symbols-outlined">schedule</span>Overdue
                    </span>
                @endif
            </div>
            <div class="d-card-body">
                <div class="detail-row">
                    <div class="detail-lbl">Title</div>
                    <div class="detail-val" style="font-weight:700;font-size:.9375rem;">{{ $task->title }}</div>
                </div>
                @if($task->description)
                <div class="detail-row">
                    <div class="detail-lbl">Description</div>
                    <div class="detail-val" style="white-space:pre-line;line-height:1.6;">{{ $task->description }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-lbl">Status</div>
                    <div class="detail-val">
                        <span class="sts sts-{{ $task->status }}">
                            @if($task->status === 'completed')<span class="material-symbols-outlined">check_circle</span>
                            @elseif($task->status === 'in_progress')<span class="material-symbols-outlined">autorenew</span>
                            @else<span class="material-symbols-outlined">radio_button_unchecked</span>@endif
                            {{ $task->statusLabel() }}
                        </span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Priority</div>
                    <div class="detail-val"><span class="pri pri-{{ $task->priority }}">{{ $task->priorityLabel() }}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Start Date</div>
                    <div class="detail-val">{{ $task->start_date->format('l, d M Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Due Date</div>
                    <div class="detail-val" style="{{ $overdue ? 'color:#b91c1c;font-weight:700;' : 'font-weight:600;' }}">
                        {{ $task->due_date->format('l, d M Y') }}
                        @if($overdue)
                            <span style="font-size:.78rem;font-weight:400;">({{ $task->due_date->diffForHumans() }})</span>
                        @endif
                    </div>
                </div>
                @if($task->attachment)
                <div class="detail-row">
                    <div class="detail-lbl">Attachment</div>
                    <div class="detail-val">
                        <a href="{{ Storage::url($task->attachment) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:.35rem;color:var(--primary);font-size:.8375rem;font-weight:500;text-decoration:none;">
                            <span class="material-symbols-outlined" style="font-size:1rem;">attach_file</span>
                            Download Attachment
                        </a>
                    </div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-lbl">Created</div>
                    <div class="detail-val" style="color:var(--text-secondary);font-size:.8125rem;">{{ $task->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">In Progress</div>
                    <div class="detail-val" style="color:var(--text-secondary);font-size:.8125rem;">
                        @if($task->started_at)
                            {{ $task->started_at->format('d M Y, h:i A') }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Completed</div>
                    <div class="detail-val" style="color:var(--text-secondary);font-size:.8125rem;">
                        @if($task->completed_at)
                            <span style="color:#15803d;">{{ $task->completed_at->format('d M Y, h:i A') }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Comments --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">forum</span>Comments & Updates</h5>
                <span style="font-size:.78rem;color:var(--text-muted);">{{ $task->comments->count() }} comment(s)</span>
            </div>
            <div class="d-card-body">
                @if($task->comments->isNotEmpty())
                    <div class="comment-feed" style="margin-bottom:1.25rem;">
                        @foreach($task->comments as $comment)
                            <div class="comment-item">
                                <div class="c-avatar">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</div>
                                <div class="c-bubble">
                                    <div class="c-meta">
                                        <span class="c-name">{{ $comment->user->name }}</span>
                                        <span class="c-role">{{ ucfirst($comment->user->role) }}</span>
                                        <span class="c-time">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="c-text">{{ $comment->comment }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem;">No comments yet.</p>
                @endif

                <form method="POST" action="{{ route('manager.tasks.comment', $task) }}" class="comment-form">
                    @csrf
                    <textarea name="comment" placeholder="Add a comment or update..."
                              class="{{ $errors->has('comment') ? 'is-invalid' : '' }}">{{ old('comment') }}</textarea>
                    @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="margin-top:.625rem;">
                        <button type="submit" class="btn-primary">
                            <span class="material-symbols-outlined" style="font-size:1rem;">send</span>
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">person</span>Assigned Employee</h5>
            </div>
            <div class="d-card-body" style="text-align:center;padding:1.5rem 1.25rem;">
                <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary) 0%,#6366f1 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;margin:0 auto .75rem;">
                    {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                </div>
                <div style="font-weight:700;font-size:.9375rem;margin-bottom:.2rem;">{{ $task->assignee->name }}</div>
                <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem;">{{ $task->assignee->employee_code }}</div>
                @if($task->assignee->department)
                    <div style="font-size:.8rem;color:var(--text-secondary);background:var(--bg-light);display:inline-block;padding:.2rem .6rem;border-radius:999px;">
                        {{ $task->assignee->department->name }}
                    </div>
                @endif
            </div>
        </div>

        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">info</span>Quick Actions</h5>
            </div>
            <div class="d-card-body" style="display:flex;flex-direction:column;gap:.625rem;">
                <a href="{{ route('manager.tasks.edit', $task) }}" class="btn-primary" style="justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>
                    Edit Task
                </a>
                @if($task->status !== 'completed')
                    <form method="POST" action="{{ route('manager.tasks.update', $task) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="title"       value="{{ $task->title }}">
                        <input type="hidden" name="description" value="{{ $task->description }}">
                        <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                        <input type="hidden" name="priority"    value="{{ $task->priority }}">
                        <input type="hidden" name="start_date"  value="{{ $task->start_date->toDateString() }}">
                        <input type="hidden" name="due_date"    value="{{ $task->due_date->toDateString() }}">
                        <input type="hidden" name="status"      value="completed">
                        <button type="submit" class="btn-outline" style="width:100%;justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:1rem;">check_circle</span>
                            Mark as Completed
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
