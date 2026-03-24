@extends('layouts.app')

@section('title', 'Task Details')

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-warning { background:#fffbeb;color:#b45309;border-color:#fde68a; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    .detail-grid { display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start; }
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

    .sts-lg { display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .875rem;border-radius:999px;font-size:.875rem;font-weight:600; }
    .sts-lg .material-symbols-outlined { font-size:1.1rem; }
    .sts-pending     { background:#f1f5f9;color:#475569; }
    .sts-in_progress { background:#eff6ff;color:#1d4ed8; }
    .sts-completed   { background:#f0fdf4;color:#15803d; }

    .overdue-chip { display:inline-flex;align-items:center;gap:.25rem;background:#fee2e2;color:#b91c1c;font-size:.75rem;font-weight:700;padding:.2rem .6rem;border-radius:999px; }
    .overdue-chip .material-symbols-outlined { font-size:.85rem; }

    /* Status update */
    .status-options { display:grid;grid-template-columns:repeat(3,1fr);gap:.625rem; }
    .status-opt { position:relative; }
    .status-opt input[type=radio] { position:absolute;opacity:0;width:0;height:0; }
    .status-opt label { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;padding:.75rem .5rem;border:2px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:all .15s;font-size:.8rem;font-weight:600;color:var(--text-secondary);text-align:center; }
    .status-opt label .material-symbols-outlined { font-size:1.4rem; }
    .status-opt input:checked + label { border-color:var(--primary);background:var(--primary-subtle);color:var(--primary); }
    .status-opt label:hover { border-color:#cbd5e1;background:var(--bg-light); }

    /* Comments */
    .comment-feed { display:flex;flex-direction:column;gap:.875rem; }
    .comment-item { display:flex;gap:.75rem; }
    .c-avatar { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary) 0%,#6366f1 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0; }
    .c-bubble { flex:1;background:#f8fafc;border:1px solid var(--border);border-radius:0 var(--radius-md) var(--radius-md) var(--radius-md);padding:.625rem .875rem; }
    .c-meta { display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;flex-wrap:wrap; }
    .c-name { font-size:.8rem;font-weight:700;color:var(--text-main); }
    .c-role { font-size:.7rem;color:var(--text-muted);background:var(--bg-light);padding:.05rem .4rem;border-radius:3px; }
    .c-time { font-size:.72rem;color:var(--text-muted);margin-left:auto; }
    .c-text { font-size:.8375rem;color:var(--text-main);line-height:1.55; }

    .comment-form textarea { width:100%;padding:.625rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;color:var(--text-main);background:var(--surface);resize:vertical;min-height:80px;transition:border-color .15s,box-shadow .15s; }
    .comment-form textarea:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .comment-form .invalid-feedback { font-size:.78rem;color:#dc2626;margin-top:.3rem; }

    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s;width:100%;justify-content:center; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-primary .material-symbols-outlined { font-size:1rem; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
    <a href="{{ route('employee.tasks.index') }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='var(--surface)'">
        <span class="material-symbols-outlined" style="font-size:1.1rem;">arrow_back</span>
    </a>
    <div>
        <h1 class="page-title">Task Details</h1>
        <p class="page-subtitle" style="margin-bottom:0;">View your task and update its progress.</p>
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

@php $overdue = $task->isOverdue(); @endphp
@if($overdue)
    <div class="flash" style="background:#fff5f5;color:#b91c1c;border-color:#fecaca;margin-bottom:1rem;">
        <span class="material-symbols-outlined">schedule</span>
        This task is <strong>overdue</strong> — it was due on {{ $task->due_date->format('d M Y') }}. Please update the status.
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
@endif

<div class="detail-grid">
    <div>
        {{-- Task Info --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">task</span>Task Information</h5>
                <span class="sts-lg sts-{{ $task->status }}">
                    @if($task->status === 'completed')<span class="material-symbols-outlined">check_circle</span>
                    @elseif($task->status === 'in_progress')<span class="material-symbols-outlined">autorenew</span>
                    @else<span class="material-symbols-outlined">radio_button_unchecked</span>@endif
                    {{ $task->statusLabel() }}
                </span>
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
                <div class="detail-row">
                    <div class="detail-lbl">Assigned By</div>
                    <div class="detail-val" style="font-weight:600;">{{ $task->assigner->name }}</div>
                </div>
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
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem;">No comments yet. Add an update below.</p>
                @endif

                <form method="POST" action="{{ route('employee.tasks.comment', $task) }}" class="comment-form">
                    @csrf
                    <textarea name="comment" placeholder="Add a comment or progress update...">{{ old('comment') }}</textarea>
                    @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="margin-top:.625rem;">
                        <button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;">
                            <span class="material-symbols-outlined" style="font-size:1rem;">send</span>
                            Post Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        @if($task->status !== 'completed')
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">sync</span>Update Status</h5>
            </div>
            <div class="d-card-body">
                <form method="POST" action="{{ route('employee.tasks.status', $task) }}">
                    @csrf @method('PATCH')
                    <div class="status-options" style="margin-bottom:1rem;">
                        <div class="status-opt">
                            <input type="radio" name="status" id="s-pending" value="pending" {{ $task->status === 'pending' ? 'checked' : '' }}>
                            <label for="s-pending">
                                <span class="material-symbols-outlined" style="color:#64748b;">radio_button_unchecked</span>
                                Pending
                            </label>
                        </div>
                        <div class="status-opt">
                            <input type="radio" name="status" id="s-inprog" value="in_progress" {{ $task->status === 'in_progress' ? 'checked' : '' }}>
                            <label for="s-inprog">
                                <span class="material-symbols-outlined" style="color:#1d4ed8;">autorenew</span>
                                In Progress
                            </label>
                        </div>
                        <div class="status-opt">
                            <input type="radio" name="status" id="s-done" value="completed" {{ $task->status === 'completed' ? 'checked' : '' }}>
                            <label for="s-done">
                                <span class="material-symbols-outlined" style="color:#15803d;">check_circle</span>
                                Completed
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">update</span>
                        Update Status
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="d-card">
            <div class="d-card-body" style="text-align:center;padding:1.5rem 1.25rem;">
                <span class="material-symbols-outlined" style="font-size:2.5rem;color:#22c55e;display:block;margin-bottom:.5rem;font-variation-settings:'FILL' 1;">check_circle</span>
                <div style="font-weight:700;color:#15803d;margin-bottom:.25rem;">Task Completed!</div>
                <div style="font-size:.8rem;color:var(--text-muted);">Great work! Your manager has been notified.</div>
            </div>
        </div>
        @endif

        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">info</span>Task Summary</h5>
            </div>
            <div class="d-card-body" style="font-size:.8375rem;">
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Priority</span>
                    <span class="pri pri-{{ $task->priority }}">{{ $task->priorityLabel() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Due In</span>
                    <span style="{{ $overdue ? 'color:#b91c1c;font-weight:600;' : 'font-weight:600;' }}">{{ $task->due_date->diffForHumans() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Assigned By</span>
                    <span style="font-weight:600;">{{ $task->assigner->name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Assigned On</span>
                    <span>{{ $task->created_at->format('d M Y') }}</span>
                </div>
                @if($task->started_at)
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Started</span>
                    <span style="color:#1d4ed8;">{{ $task->started_at->format('d M Y') }}</span>
                </div>
                @endif
                @if($task->completed_at)
                <div style="display:flex;justify-content:space-between;padding:.4rem 0;">
                    <span style="color:var(--text-muted);">Completed</span>
                    <span style="color:#15803d;font-weight:600;">{{ $task->completed_at->format('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
