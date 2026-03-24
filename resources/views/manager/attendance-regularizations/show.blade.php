@extends('layouts.app')

@section('title', 'Regularization Review - ' . $regularization->user->name . ' - ' . $regularization->date->format('d M Y'))

@push('styles')
<style>
    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:999px;font-size:.78rem;font-weight:700; }
    .badge-gray { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-green { background:#f0fdf4;color:#15803d; }
    .badge-red { background:#fff1f2;color:#dc2626; }
    .layout { display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1.25rem;align-items:start; }
    @media(max-width:980px){ .layout { grid-template-columns:1fr; } }
    .card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden; }
    .card-header { padding:.9rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc;font-size:.88rem;font-weight:800; }
    .card-body { padding:1.25rem; }
    .detail-row { display:flex;justify-content:space-between;gap:1rem;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.84rem; }
    .detail-row:last-child { border-bottom:none; }
    .detail-row span:last-child { font-weight:700;color:var(--text-main);text-align:right; }
    .btn-success { background:#16a34a;color:#fff;border:none;border-radius:var(--radius-sm);padding:.55rem .95rem;font-size:.84rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer; }
    .btn-danger { background:#dc2626;color:#fff;border:none;border-radius:var(--radius-sm);padding:.55rem .95rem;font-size:.84rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer; }
    .btn-outline { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem .95rem;font-size:.84rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .form-control { width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);font-size:.88rem; }
    .comment-item { display:flex;gap:.75rem;margin-bottom:.9rem; }
    .avatar { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#60a5fa);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0; }
    .bubble { flex:1;background:#f8fafc;border:1px solid var(--border);border-radius:0 var(--radius-md) var(--radius-md) var(--radius-md);padding:.7rem .85rem; }
    .action-box { background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius-md);padding:1rem 1.1rem;margin-bottom:1rem; }
    .act-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem; }
    @media(max-width:600px){ .act-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
@php
    $colorMap = ['gray' => 'badge-gray', 'orange' => 'badge-orange', 'blue' => 'badge-blue', 'green' => 'badge-green', 'red' => 'badge-red'];
    $isL1 = $regularization->l1_manager_id === $manager->id && $regularization->status === 'pending_l1';
    $isL2 = $regularization->l2_manager_id === $manager->id && $regularization->status === 'pending_l2';
    $canAct = $isL1 || $isL2;
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title" style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            {{ $regularization->user->name }}'s Regularization Request
            <span class="status-badge {{ $colorMap[$regularization->status_color] ?? 'badge-gray' }}">{{ $regularization->status_label }}</span>
        </h1>
        <p class="page-subtitle" style="margin-bottom:0;">{{ $regularization->type_label }} for {{ $regularization->date->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('manager.regularizations.index') }}" class="btn-outline">
        <span class="material-symbols-outlined" style="font-size:1rem;">arrow_back</span>
        Back to Requests
    </a>
</div>

<div class="layout">
    <div>
        @if($canAct)
            <div class="action-box">
                <div style="font-weight:800;margin-bottom:.75rem;">Approval Action</div>
                <div class="act-grid">
                    <form method="POST" action="{{ route('manager.regularizations.approve', $regularization) }}">
                        @csrf
                        <textarea name="comment" class="form-control" rows="4" placeholder="Optional approval comment..."></textarea>
                        <button type="submit" class="btn-success" style="margin-top:.6rem;">
                            <span class="material-symbols-outlined" style="font-size:1rem;">check_circle</span>
                            {{ $isL1 ? 'Approve L1' : 'Approve L2' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('manager.regularizations.reject', $regularization) }}">
                        @csrf
                        <textarea name="comment" class="form-control" rows="4" placeholder="Required rejection comment..." required></textarea>
                        <button type="submit" class="btn-danger" style="margin-top:.6rem;">
                            <span class="material-symbols-outlined" style="font-size:1rem;">cancel</span>
                            Reject
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">Request Details</div>
            <div class="card-body">
                <div class="detail-row"><span>Employee</span><span>{{ $regularization->user->name }}</span></div>
                <div class="detail-row"><span>Employee Code</span><span>{{ $regularization->user->employee_code }}</span></div>
                <div class="detail-row"><span>Type</span><span>{{ $regularization->type_label }}</span></div>
                <div class="detail-row"><span>Original Punch In</span><span>{{ $regularization->original_punch_in ? substr($regularization->original_punch_in, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Original Punch Out</span><span>{{ $regularization->original_punch_out ? substr($regularization->original_punch_out, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Requested Punch In</span><span>{{ $regularization->requested_punch_in ? substr($regularization->requested_punch_in, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Requested Punch Out</span><span>{{ $regularization->requested_punch_out ? substr($regularization->requested_punch_out, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Submitted At</span><span>{{ $regularization->submitted_at?->format('d M Y, h:i A') ?? '-' }}</span></div>
                <div style="margin-top:1rem;font-size:.84rem;color:var(--text-secondary);line-height:1.6;">{{ $regularization->reason }}</div>
                @if($regularization->attachment_path)
                    <div style="margin-top:1rem;"><a href="{{ Storage::url($regularization->attachment_path) }}" target="_blank">Open attachment</a></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Discussion</div>
            <div class="card-body">
                @foreach($regularization->comments->whereNull('parent_id') as $comment)
                    <div class="comment-item">
                        <div class="avatar">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</div>
                        <div class="bubble">
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.35rem;">
                                <strong>{{ $comment->user->name }}</strong>
                                <span style="font-size:.74rem;color:var(--text-muted);">{{ ucfirst($comment->user->role) }}</span>
                                <span style="font-size:.74rem;color:var(--text-muted);margin-left:auto;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-size:.84rem;line-height:1.6;">{{ $comment->comment }}</div>
                            @if($comment->replies->count())
                                <div style="margin-top:.65rem;padding-top:.65rem;border-top:1px solid var(--border);">
                                    @foreach($comment->replies as $reply)
                                        <div style="margin-bottom:.6rem;font-size:.82rem;">
                                            <strong>{{ $reply->user->name }}</strong>
                                            <span style="color:var(--text-muted);font-size:.73rem;">{{ $reply->created_at->diffForHumans() }}</span>
                                            <div style="margin-top:.2rem;line-height:1.5;">{{ $reply->comment }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <form method="POST" action="{{ route('manager.regularizations.comment', $regularization) }}" style="margin-top:.65rem;">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <textarea name="comment" class="form-control" rows="2" placeholder="Reply to this comment..."></textarea>
                                <button type="submit" class="btn-outline" style="margin-top:.5rem;">Reply</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('manager.regularizations.comment', $regularization) }}">
                    @csrf
                    <textarea name="comment" class="form-control" rows="4" placeholder="Add a comment..."></textarea>
                    <button type="submit" class="btn-outline" style="margin-top:.65rem;">Post Comment</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">Workflow</div>
            <div class="card-body">
                <div class="detail-row"><span>L1 Manager</span><span>{{ $regularization->l1Manager?->name ?? '-' }}</span></div>
                <div class="detail-row"><span>L1 Comment</span><span>{{ $regularization->l1_comment ?: '-' }}</span></div>
                <div class="detail-row"><span>L2 Manager</span><span>{{ $regularization->l2Manager?->name ?? '-' }}</span></div>
                <div class="detail-row"><span>L2 Comment</span><span>{{ $regularization->l2_comment ?: '-' }}</span></div>
                <div class="detail-row"><span>Finalized At</span><span>{{ $regularization->finalized_at?->format('d M Y, h:i A') ?? '-' }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Attendance After Approval</div>
            <div class="card-body">
                <div class="detail-row"><span>Current Punch In</span><span>{{ $regularization->attendance?->punch_in ? substr($regularization->attendance->punch_in, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Current Punch Out</span><span>{{ $regularization->attendance?->punch_out ? substr($regularization->attendance->punch_out, 0, 5) : '-' }}</span></div>
                <div class="detail-row"><span>Work Hours</span><span>{{ $regularization->attendance?->formatted_work_hours ?? '-' }}</span></div>
                <div class="detail-row"><span>Status</span><span>{{ ucfirst(str_replace('_', ' ', $regularization->attendance?->status ?? '-')) }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
