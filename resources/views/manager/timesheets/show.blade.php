@extends('layouts.app')

@section('title', 'Timesheet — ' . $timesheet->user->name . ' · ' . $timesheet->date->format('d M Y'))

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    .btn-primary  { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-success  { background:#16a34a;color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-success:hover { background:#15803d;color:#fff; }
    .btn-danger   { background:#dc2626;color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-danger:hover { background:#b91c1c;color:#fff; }
    .btn-outline  { background:transparent;border:1px solid var(--border);color:var(--text-secondary);border-radius:var(--radius-sm);padding:.45rem .875rem;font-size:.8375rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:all .15s; }
    .btn-outline:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }

    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:999px;font-size:.78rem;font-weight:700; }
    .badge-gray   { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue   { background:#eff6ff;color:#1d4ed8; }
    .badge-green  { background:#f0fdf4;color:#15803d; }
    .badge-red    { background:#fff1f2;color:#dc2626; }

    .ts-layout { display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start; }
    @media(max-width:900px) { .ts-layout { grid-template-columns:1fr; } }

    .d-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1rem; }
    .d-card-header { padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;justify-content:space-between;gap:.5rem; }
    .d-card-header h5 { font-size:.88rem;font-weight:700;margin:0;color:var(--text-main);display:flex;align-items:center;gap:.4rem; }
    .d-card-header h5 .material-symbols-outlined { font-size:1rem;color:var(--primary); }
    .d-card-body { padding:1.25rem; }

    .detail-row { display:flex;padding:.5rem 0;border-bottom:1px solid var(--border);gap:.75rem; }
    .detail-row:last-child { border-bottom:none; }
    .detail-lbl { font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;min-width:90px;flex-shrink:0; }
    .detail-val { font-size:.875rem;color:var(--text-main); }

    /* Calendar (read-only) */
    .cal-wrapper { overflow-x:auto; }
    .cal-grid { position:relative;display:grid;grid-template-columns:52px 1fr;min-width:400px; }
    .cal-time-col { position:relative; }
    .cal-time-slot { height:45px;display:flex;align-items:flex-start;justify-content:flex-end;padding-right:10px;font-size:.7rem;font-weight:600;color:var(--text-muted);box-sizing:border-box;margin-top:-1px;user-select:none; }
    .cal-event-col { position:relative;border-left:2px solid var(--border); }
    .cal-hour-line { position:absolute;left:0;right:0;border-top:1px solid var(--border);height:0; }
    .cal-half-line { position:absolute;left:0;right:0;border-top:1px dashed #e2e8f0;height:0; }
    .cal-entry { position:absolute;left:4px;right:4px;border-radius:6px;padding:4px 8px;overflow:hidden;z-index:5;box-shadow:0 1px 3px rgba(0,0,0,.12); }
    .cal-entry .entry-title { font-size:.75rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#fff; }
    .cal-entry .entry-time  { font-size:.65rem;color:rgba(255,255,255,.85); }
    .entry-color-0 { background:linear-gradient(135deg,#137fec,#3b82f6); }
    .entry-color-1 { background:linear-gradient(135deg,#7c3aed,#a78bfa); }
    .entry-color-2 { background:linear-gradient(135deg,#059669,#34d399); }
    .entry-color-3 { background:linear-gradient(135deg,#d97706,#fbbf24); }
    .entry-color-4 { background:linear-gradient(135deg,#db2777,#f472b6); }
    .entry-color-5 { background:linear-gradient(135deg,#0891b2,#67e8f9); }

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
    .c-reply-btn { font-size:.72rem;color:var(--primary);cursor:pointer;background:none;border:none;padding:0;margin-top:.3rem;font-weight:600; }
    .c-replies { margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:.625rem; }
    .comment-form textarea { width:100%;padding:.625rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;color:var(--text-main);background:var(--surface);resize:vertical;min-height:80px;transition:border-color .15s,box-shadow .15s;box-sizing:border-box; }
    .comment-form textarea:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }

    /* Approve/Reject modal */
    .action-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center; }
    .action-modal-overlay.open { display:flex; }
    .action-modal { background:var(--surface);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);width:100%;max-width:440px;margin:1rem; }
    .action-modal-header { padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
    .action-modal-header h4 { font-size:.9375rem;font-weight:700;margin:0; }
    .action-modal-close { background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--text-muted); }
    .action-modal-body { padding:1.25rem; }
    .action-modal-footer { padding:.875rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.5rem; }
    .form-group { margin-bottom:1rem; }
    .form-group label { display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem; }
    .form-control { width:100%;padding:.55rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;color:var(--text-main);background:var(--surface);box-sizing:border-box; }
    .form-control:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
</style>
@endpush

@section('content')

@php
    $colorMap   = ['gray'=>'badge-gray','orange'=>'badge-orange','blue'=>'badge-blue','green'=>'badge-green','red'=>'badge-red'];
    $badgeClass = $colorMap[$timesheet->status_color] ?? 'badge-gray';
    $isL1       = $timesheet->l1_manager_id === $manager->id && $timesheet->isSubmitted();
    $isL2       = $timesheet->l2_manager_id === $manager->id && $timesheet->isApprovedL1();
    $canAct     = $isL1 || $isL2;
    $punchIn    = $attendance?->punch_in ? substr($attendance->punch_in, 0, 5) : null;
    $punchOut   = $attendance?->punch_out ? substr($attendance->punch_out, 0, 5) : null;
    $slotMinutes = $gridMeta['slot_minutes'];
    $slotHeight  = $gridMeta['slot_height'];
    $gridStartMinutes = $gridMeta['start_minutes'];
    $gridEndMinutes   = $gridMeta['end_minutes'];
    $ppm        = $gridMeta['pixels_per_minute'];
@endphp

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <a href="{{ route('manager.timesheets.index') }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='var(--surface)'">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">arrow_back</span>
        </a>
        <div>
            <h1 class="page-title" style="display:flex;align-items:center;gap:.625rem;">
                {{ $timesheet->user->name }}'s Timesheet
                <span class="status-badge {{ $badgeClass }}">{{ $timesheet->status_label }}</span>
            </h1>
            <p class="page-subtitle" style="margin-bottom:0;">{{ $timesheet->date->format('l, d F Y') }}</p>
        </div>
    </div>

    @if($canAct)
    <div style="display:flex;gap:.5rem;">
        <button class="btn-success" onclick="openActionModal('approve')">
            <span class="material-symbols-outlined" style="font-size:1rem;">check_circle</span>
            {{ $isL1 ? 'Approve (L1)' : 'Final Approve (L2)' }}
        </button>
        <button class="btn-danger" onclick="openActionModal('reject')">
            <span class="material-symbols-outlined" style="font-size:1rem;">cancel</span>
            Reject
        </button>
    </div>
    @endif
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

<div class="ts-layout">

    {{-- ── LEFT: Timeline + Comments ── --}}
    <div>
        {{-- Calendar --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">grid_on</span>Work Timeline</h5>
                <span style="font-size:.8rem;font-weight:700;color:var(--primary);">
                    {{ $timesheet->formatted_total_hours }} logged
                </span>
            </div>
            <div class="d-card-body" style="padding:1rem;">
                @if($punchIn || $punchOut)
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.875rem;flex-wrap:wrap;">
                    @if($punchIn)
                    <div style="display:flex;align-items:center;gap:.35rem;font-size:.78rem;">
                        <span style="display:inline-block;width:10px;height:10px;background:#16a34a;border-radius:50%;flex-shrink:0;"></span>
                        <span style="font-weight:600;color:var(--text-secondary);">Punch In:</span>
                        <span style="font-weight:700;">{{ $punchIn }}</span>
                    </div>
                    @endif
                    @if($punchOut)
                    <div style="display:flex;align-items:center;gap:.35rem;font-size:.78rem;">
                        <span style="display:inline-block;width:10px;height:10px;background:#dc2626;border-radius:50%;flex-shrink:0;"></span>
                        <span style="font-weight:600;color:var(--text-secondary);">Punch Out:</span>
                        <span style="font-weight:700;">{{ $punchOut }}</span>
                    </div>
                    @endif
                </div>
                @endif
                <div class="cal-wrapper">
                    <div class="cal-grid">
                        {{-- Time Labels --}}
                        <div class="cal-time-col">
                            @for($minute = $gridStartMinutes; $minute <= $gridEndMinutes; $minute += $slotMinutes)
                                @php
                                    $slotHour = intdiv($minute, 60);
                                    $slotMin = $minute % 60;
                                @endphp
                                <div class="cal-time-slot" style="height:{{ $slotHeight }}px;">
                                    {{ str_pad($slotHour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($slotMin, 2, '0', STR_PAD_LEFT) }}
                                </div>
                            @endfor
                        </div>

                        {{-- Events --}}
                        @php $totalSlots = $gridMeta['total_minutes']; @endphp
                        <div class="cal-event-col" style="height:{{ $totalSlots * $ppm }}px;">
                            {{-- Slot lines --}}
                            @for($offset = 0; $offset <= $totalSlots; $offset += $slotMinutes)
                                <div class="cal-hour-line" style="top:{{ $offset * $ppm }}px;"></div>
                            @endfor

                            {{-- Entries --}}
                            @foreach($timesheet->entries as $index => $entry)
                            @php
                                [$sh, $sm] = explode(':', $entry->start_time);
                                [$eh, $em] = explode(':', $entry->end_time);
                                $startMin = (int)$sh * 60 + (int)$sm;
                                $endMin   = (int)$eh * 60 + (int)$em;
                                $top      = ($startMin - $gridStartMinutes) * $ppm;
                                $height   = max(($endMin - $startMin) * $ppm, 22);
                                $colorIdx = $index % 6;
                            @endphp
                            <div class="cal-entry entry-color-{{ $colorIdx }}"
                                 style="top:{{ $top }}px;height:{{ $height }}px;"
                                 title="{{ $entry->title }} · {{ $entry->start_time }}–{{ $entry->end_time }}">
                                <div class="entry-title">{{ $entry->title }}</div>
                                <div class="entry-time">{{ $entry->start_time }} – {{ $entry->end_time }} · {{ $entry->formatted_duration }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Work Entries Detail --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">list_alt</span>Work Entries ({{ $timesheet->entries->count() }})</h5>
            </div>
            <div>
                @forelse($timesheet->entries as $index => $entry)
                <div style="display:flex;gap:1rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border);align-items:flex-start;">
                    <div style="width:4px;border-radius:4px;flex-shrink:0;height:auto;align-self:stretch;" class="entry-color-{{ $index % 6 }}"></div>
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.9rem;">{{ $entry->title }}</div>
                        @if($entry->description)
                            <div style="font-size:.8125rem;color:var(--text-secondary);margin-top:.25rem;line-height:1.5;">{{ $entry->description }}</div>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:.8rem;font-weight:700;color:var(--primary);">{{ $entry->formatted_duration }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $entry->start_time }} – {{ $entry->end_time }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:1.25rem;text-align:center;color:var(--text-muted);font-size:.85rem;">No work entries recorded.</div>
                @endforelse
            </div>
        </div>

        {{-- Comments --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">forum</span>Discussion</h5>
                <span style="font-size:.78rem;color:var(--text-muted);">{{ $timesheet->comments->whereNull('parent_id')->count() }} comment(s)</span>
            </div>
            <div class="d-card-body">
                @if($timesheet->comments->whereNull('parent_id')->isNotEmpty())
                    <div class="comment-feed" style="margin-bottom:1.25rem;">
                        @foreach($timesheet->comments->whereNull('parent_id') as $comment)
                            <div class="comment-item">
                                <div class="c-avatar">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</div>
                                <div class="c-bubble">
                                    <div class="c-meta">
                                        <span class="c-name">{{ $comment->user->name }}</span>
                                        <span class="c-role">{{ ucfirst($comment->user->role) }}</span>
                                        <span class="c-time">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="c-text">{{ $comment->comment }}</div>
                                    <button class="c-reply-btn" onclick="toggleReplyForm({{ $comment->id }})">Reply</button>

                                    @if($comment->replies->isNotEmpty())
                                    <div class="c-replies">
                                        @foreach($comment->replies as $reply)
                                        <div class="comment-item">
                                            <div class="c-avatar" style="width:26px;height:26px;font-size:.65rem;">{{ strtoupper(substr($reply->user->name, 0, 2)) }}</div>
                                            <div class="c-bubble" style="background:#fff;">
                                                <div class="c-meta">
                                                    <span class="c-name">{{ $reply->user->name }}</span>
                                                    <span class="c-role">{{ ucfirst($reply->user->role) }}</span>
                                                    <span class="c-time">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="c-text">{{ $reply->comment }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div id="reply-form-{{ $comment->id }}" style="display:none;margin-top:.75rem;">
                                        <form method="POST" action="{{ route('manager.timesheets.comment', $timesheet) }}" class="comment-form">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="comment" placeholder="Write a reply..." rows="2"></textarea>
                                            <div style="margin-top:.5rem;display:flex;gap:.5rem;">
                                                <button type="submit" class="btn-primary" style="font-size:.8rem;padding:.35rem .75rem;">
                                                    <span class="material-symbols-outlined" style="font-size:.9rem;">send</span>Reply
                                                </button>
                                                <button type="button" class="btn-outline" style="font-size:.8rem;padding:.35rem .75rem;" onclick="toggleReplyForm({{ $comment->id }})">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem;">No comments yet.</p>
                @endif

                <form method="POST" action="{{ route('manager.timesheets.comment', $timesheet) }}" class="comment-form">
                    @csrf
                    <textarea name="comment" placeholder="Add a comment or feedback..."></textarea>
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

    {{-- ── RIGHT: Sidebar ── --}}
    <div>
        {{-- Employee Card --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">person</span>Employee</h5>
            </div>
            <div class="d-card-body" style="text-align:center;padding:1.5rem 1.25rem;">
                <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary) 0%,#6366f1 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;margin:0 auto .75rem;">
                    {{ strtoupper(substr($timesheet->user->name, 0, 2)) }}
                </div>
                <div style="font-weight:700;font-size:.9375rem;margin-bottom:.2rem;">{{ $timesheet->user->name }}</div>
                <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem;">{{ $timesheet->user->employee_code }}</div>
                @if($timesheet->user->department)
                <div style="font-size:.8rem;color:var(--text-secondary);background:var(--bg-light);display:inline-block;padding:.2rem .6rem;border-radius:999px;">
                    {{ $timesheet->user->department->name }}
                </div>
                @endif
            </div>
        </div>

        {{-- Summary --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">summarize</span>Summary</h5>
            </div>
            <div class="d-card-body">
                <div class="detail-row">
                    <div class="detail-lbl">Date</div>
                    <div class="detail-val">{{ $timesheet->date->format('d M Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Status</div>
                    <div class="detail-val"><span class="status-badge {{ $badgeClass }}">{{ $timesheet->status_label }}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Entries</div>
                    <div class="detail-val">{{ $timesheet->entries->count() }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Total Hours</div>
                    <div class="detail-val" style="font-weight:700;color:var(--primary);">{{ $timesheet->formatted_total_hours }}</div>
                </div>
                @if($punchIn)
                <div class="detail-row">
                    <div class="detail-lbl">Punch In</div>
                    <div class="detail-val" style="color:#15803d;font-weight:600;">{{ $punchIn }}</div>
                </div>
                @endif
                @if($punchOut)
                <div class="detail-row">
                    <div class="detail-lbl">Punch Out</div>
                    <div class="detail-val" style="color:#dc2626;font-weight:600;">{{ $punchOut }}</div>
                </div>
                @endif
                @if($timesheet->submitted_at)
                <div class="detail-row">
                    <div class="detail-lbl">Submitted</div>
                    <div class="detail-val" style="font-size:.8125rem;">{{ $timesheet->submitted_at->format('d M, h:i A') }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Action Buttons --}}
        @if($canAct)
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">approval_delegation</span>Actions</h5>
            </div>
            <div class="d-card-body" style="display:flex;flex-direction:column;gap:.625rem;">
                <button class="btn-success" onclick="openActionModal('approve')" style="justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:1rem;">check_circle</span>
                    {{ $isL1 ? 'Approve (L1 Review)' : 'Final Approve (L2)' }}
                </button>
                <button class="btn-danger" onclick="openActionModal('reject')" style="justify-content:center;">
                    <span class="material-symbols-outlined" style="font-size:1rem;">cancel</span>
                    Reject
                </button>
            </div>
        </div>
        @endif

        {{-- Approval Trail --}}
        <div class="d-card">
            <div class="d-card-header">
                <h5><span class="material-symbols-outlined">approval_delegation</span>Approval Trail</h5>
            </div>
            <div class="d-card-body">
                <div style="display:flex;flex-direction:column;gap:.875rem;">
                    <div style="display:flex;gap:.625rem;align-items:flex-start;">
                        <span class="material-symbols-outlined" style="font-size:1.1rem;color:{{ $timesheet->submitted_at ? '#16a34a' : '#94a3b8' }};flex-shrink:0;margin-top:.1rem;">
                            {{ $timesheet->submitted_at ? 'check_circle' : 'radio_button_unchecked' }}
                        </span>
                        <div>
                            <div style="font-size:.8rem;font-weight:700;">Submitted by Employee</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">
                                {{ $timesheet->submitted_at ? $timesheet->submitted_at->format('d M Y, h:i A') : 'Not yet submitted' }}
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:.625rem;align-items:flex-start;">
                        @php
                            $l1Done   = $timesheet->l1_actioned_at;
                            $l1Passed = in_array($timesheet->status, ['approved_l1', 'approved']);
                        @endphp
                        <span class="material-symbols-outlined" style="font-size:1.1rem;color:{{ $l1Done ? ($l1Passed ? '#16a34a' : '#dc2626') : '#94a3b8' }};flex-shrink:0;margin-top:.1rem;">
                            {{ $l1Done ? ($l1Passed ? 'verified' : 'cancel') : 'radio_button_unchecked' }}
                        </span>
                        <div>
                            <div style="font-size:.8rem;font-weight:700;">L1 Review — {{ $timesheet->l1Manager?->name ?? 'L1 Manager' }}</div>
                            @if($l1Done)
                                <div style="font-size:.75rem;color:var(--text-muted);">{{ $timesheet->l1_actioned_at->format('d M Y, h:i A') }}</div>
                                @if($timesheet->l1_remarks)
                                    <div style="font-size:.78rem;font-style:italic;color:var(--text-secondary);margin-top:.2rem;">"{{ $timesheet->l1_remarks }}"</div>
                                @endif
                            @else
                                <div style="font-size:.75rem;color:var(--text-muted);">Pending</div>
                            @endif
                        </div>
                    </div>

                    <div style="display:flex;gap:.625rem;align-items:flex-start;">
                        @php $l2Done = $timesheet->l2_actioned_at; @endphp
                        <span class="material-symbols-outlined" style="font-size:1.1rem;color:{{ $l2Done ? ($timesheet->isApproved() ? '#16a34a' : '#dc2626') : '#94a3b8' }};flex-shrink:0;margin-top:.1rem;">
                            {{ $l2Done ? ($timesheet->isApproved() ? 'verified' : 'cancel') : 'radio_button_unchecked' }}
                        </span>
                        <div>
                            <div style="font-size:.8rem;font-weight:700;">L2 Final Review — {{ $timesheet->l2Manager?->name ?? 'L2 Manager' }}</div>
                            @if($l2Done)
                                <div style="font-size:.75rem;color:var(--text-muted);">{{ $timesheet->l2_actioned_at->format('d M Y, h:i A') }}</div>
                                @if($timesheet->l2_remarks)
                                    <div style="font-size:.78rem;font-style:italic;color:var(--text-secondary);margin-top:.2rem;">"{{ $timesheet->l2_remarks }}"</div>
                                @endif
                            @else
                                <div style="font-size:.75rem;color:var(--text-muted);">Pending</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Approve / Reject Modal ──────────────────────────────────────────────── --}}
<div class="action-modal-overlay" id="actionModal" onclick="if(event.target===this)closeActionModal()">
    <div class="action-modal">
        <div class="action-modal-header">
            <h4 id="actionModalTitle">Approve Timesheet</h4>
            <button class="action-modal-close" onclick="closeActionModal()">&times;</button>
        </div>
        <form id="actionForm" method="POST">
            @csrf
            <div class="action-modal-body">
                <p id="actionModalDesc" style="font-size:.875rem;color:var(--text-secondary);margin-bottom:1rem;"></p>
                <div class="form-group">
                    <label id="remarksLabel" for="remarksInput">Remarks (optional)</label>
                    <textarea id="remarksInput" name="remarks" class="form-control" rows="3"
                              placeholder="Add any notes or feedback..."></textarea>
                </div>
            </div>
            <div class="action-modal-footer">
                <button type="button" class="btn-outline" onclick="closeActionModal()">Cancel</button>
                <button type="submit" id="actionSubmitBtn" class="btn-success">Confirm Approve</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openActionModal(action) {
    const isApprove = action === 'approve';
    const modal     = document.getElementById('actionModal');
    const form      = document.getElementById('actionForm');
    const title     = document.getElementById('actionModalTitle');
    const desc      = document.getElementById('actionModalDesc');
    const btn       = document.getElementById('actionSubmitBtn');
    const lbl       = document.getElementById('remarksLabel');
    const textarea  = document.getElementById('remarksInput');

    if (isApprove) {
        form.action  = '{{ route("manager.timesheets.approve", $timesheet) }}';
        title.textContent = '{{ $isL1 ? "Approve Timesheet (L1)" : "Final Approve Timesheet (L2)" }}';
        desc.textContent  = '{{ $isL1 ? "Approve this timesheet and forward to L2 Manager for final review." : "Give final approval for this timesheet." }}';
        btn.className     = 'btn-success';
        btn.textContent   = '{{ $isL1 ? "Approve (L1)" : "Final Approve" }}';
        lbl.textContent   = 'Remarks (optional)';
        textarea.required = false;
        textarea.placeholder = 'Add any notes or feedback...';
    } else {
        form.action  = '{{ route("manager.timesheets.reject", $timesheet) }}';
        title.textContent = 'Reject Timesheet';
        desc.textContent  = 'The employee will be notified to revise and resubmit. Please provide a reason.';
        btn.className     = 'btn-danger';
        btn.textContent   = 'Reject';
        lbl.textContent   = 'Rejection Reason *';
        textarea.required = true;
        textarea.placeholder = 'Explain why this timesheet is being rejected...';
    }

    textarea.value = '';
    modal.classList.add('open');
    setTimeout(() => textarea.focus(), 50);
}

function closeActionModal() {
    document.getElementById('actionModal').classList.remove('open');
}

function toggleReplyForm(id) {
    const el = document.getElementById('reply-form-' + id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeActionModal(); });
</script>
@endpush
