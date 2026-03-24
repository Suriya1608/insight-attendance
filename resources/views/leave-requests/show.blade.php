@extends('layouts.app')

@section('title', 'Request #' . $leaveRequest->id)

@section('content')
<style>
    .show-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .show-header h1 { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0; }
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border: 1px solid var(--border);
        border-radius: var(--radius-sm); font-size: 13px; font-weight: 500;
        color: var(--text-secondary); text-decoration: none; background: var(--surface);
        transition: background .12s;
    }
    .btn-back:hover { background: var(--bg-light); color: var(--text-main); }
    .btn-back .material-symbols-outlined { font-size: 16px; }

    /* ── Two-column layout ── */
    .show-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; align-items: start; }
    @media(max-width:900px) { .show-grid { grid-template-columns: 1fr; } }

    /* ── Cards ── */
    .show-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .show-card-header {
        padding: 14px 20px; border-bottom: 1px solid var(--border);
        font-size: 13px; font-weight: 700; color: var(--text-main);
        display: flex; align-items: center; gap: 8px;
    }
    .show-card-header .material-symbols-outlined { font-size: 17px; color: var(--text-secondary); }
    .show-card-body { padding: 20px; }

    /* ── Detail rows ── */
    .detail-row {
        display: flex; gap: 8px; padding: 9px 0;
        border-bottom: 1px solid var(--border); align-items: flex-start;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-lbl {
        width: 160px; flex-shrink: 0;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--text-muted); padding-top: 1px;
    }
    .detail-val { font-size: 13px; color: var(--text-main); font-weight: 500; flex: 1; }

    /* ── Status badge ── */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 999px;
        font-size: 11px; font-weight: 700; letter-spacing: .03em;
    }
    .badge-status .dot { width: 7px; height: 7px; border-radius: 50%; }
    .bs-pending  { background:#fff7ed; color:#c2410c; }  .bs-pending .dot  { background:#ea580c; }
    .bs-appr_l1  { background:#eff6ff; color:#1d4ed8; }  .bs-appr_l1 .dot { background:#3b82f6; }
    .bs-approved { background:#f0fdf4; color:#15803d; }  .bs-approved .dot { background:#22c55e; }
    .bs-rejected { background:#fff1f2; color:#be123c; }  .bs-rejected .dot { background:#f43f5e; }

    .badge-type-lg {
        display: inline-block; padding: 4px 12px; border-radius: 6px;
        font-size: 12px; font-weight: 700;
    }
    .bt-cl  { background:#dbeafe; color:#1e40af; }
    .bt-lop { background:#fee2e2; color:#b91c1c; }
    .bt-sat { background:#fef9c3; color:#854d0e; }
    .bt-perm{ background:#ede9fe; color:#6d28d9; }

    .lop-tag {
        display: inline-block; margin-left: 6px; padding: 2px 7px;
        background: #fef3c7; color: #92400e; border-radius: 4px;
        font-size: 10px; font-weight: 700; vertical-align: middle;
    }

    /* ── Timeline ── */
    .timeline { position: relative; padding-left: 28px; }
    .timeline::before {
        content: ''; position: absolute; left: 10px; top: 10px; bottom: 10px;
        width: 2px; background: var(--border);
    }
    .tl-item { position: relative; margin-bottom: 20px; }
    .tl-item:last-child { margin-bottom: 0; }
    .tl-dot {
        position: absolute; left: -24px; top: 4px;
        width: 14px; height: 14px; border-radius: 50%;
        border: 2px solid var(--border); background: var(--surface);
    }
    .tl-dot.done  { background: #22c55e; border-color: #22c55e; }
    .tl-dot.pend  { background: #f59e0b; border-color: #f59e0b; }
    .tl-dot.wait  { background: var(--border); border-color: var(--border); }
    .tl-dot.rejected { background: #f43f5e; border-color: #f43f5e; }
    .tl-label { font-size: 13px; font-weight: 700; color: var(--text-main); }
    .tl-meta  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .tl-remark {
        margin-top: 6px; padding: 8px 12px;
        background: #f8fafc; border: 1px solid var(--border); border-radius: 6px;
        font-size: 12px; color: var(--text-secondary); font-style: italic;
    }

    /* ── Approval form ── */
    .approval-form-card { margin-bottom: 16px; }
    .approve-form-body { padding: 16px 20px; }
    .form-label-sm {
        display: block; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em;
        color: var(--text-secondary); margin-bottom: 5px;
    }
    .form-textarea-sm {
        width: 100%; padding: 8px 10px; border: 1px solid var(--border);
        border-radius: var(--radius-sm); font-size: 13px; color: var(--text-main);
        background: var(--surface); outline: none; resize: vertical; min-height: 70px;
        font-family: inherit; transition: border-color .15s;
    }
    .form-textarea-sm:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .invalid-feedback { display: block; font-size: 12px; color: #dc2626; margin-top: 3px; }

    .btn-approve {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; background: #16a34a; color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .12s;
    }
    .btn-approve:hover { background: #15803d; }
    .btn-approve .material-symbols-outlined { font-size: 16px; }

    .btn-reject {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; background: #dc2626; color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
        cursor: pointer; transition: background .12s;
    }
    .btn-reject:hover { background: #b91c1c; }
    .btn-reject .material-symbols-outlined { font-size: 16px; }

    .action-divider { height: 1px; background: var(--border); margin: 12px 0; }

    /* ── Flash ── */
    .alert-flash {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px 16px; border-radius: var(--radius-sm);
        margin-bottom: 20px; font-size: 13px; font-weight: 500;
    }
    .alert-flash .material-symbols-outlined { font-size: 18px; flex-shrink: 0; }
    .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#14532d; }
    .alert-success .material-symbols-outlined { color: #22c55e; }
    .alert-danger  { background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; }
    .alert-danger  .material-symbols-outlined { color: #f43f5e; }

    /* ── Attachment ── */
    .attach-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--primary); font-size: 13px; text-decoration: none;
    }
    .attach-link:hover { text-decoration: underline; }
    .attach-link .material-symbols-outlined { font-size: 16px; }
</style>

{{-- Flash --}}
@if(session('success'))
<div class="alert-flash alert-success">
    <span class="material-symbols-outlined">check_circle</span>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert-flash alert-danger">
    <span class="material-symbols-outlined">error</span>
    <div>
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
</div>
@endif

{{-- Header --}}
<div class="show-header">
    <a href="{{ route('leave-requests.index') }}" class="btn-back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
    </a>
    <h1>Request #{{ $leaveRequest->id }}</h1>
    <span class="badge-status bs-{{ str_replace('_', '', $leaveRequest->status === 'approved_l1' ? 'appr_l1' : $leaveRequest->status) }}">
        <span class="dot"></span>
        {{ $leaveRequest->status_label }}
    </span>
</div>

<div class="show-grid">

    {{-- ── Left: Details ── --}}
    <div>
        <div class="show-card">
            <div class="show-card-header">
                <span class="material-symbols-outlined">info</span>
                Request Details
            </div>
            <div class="show-card-body">
                <div class="detail-row">
                    <div class="detail-lbl">Submitted by</div>
                    <div class="detail-val">{{ $leaveRequest->user->name }}
                        <span style="font-size:11px;color:var(--text-muted);margin-left:6px;">{{ $leaveRequest->user->employee_code }}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Request Type</div>
                    <div class="detail-val">
                        <span class="badge-type-lg {{ $leaveRequest->request_type === 'permission' ? 'bt-perm' : ($leaveRequest->leave_type === 'CL' ? 'bt-cl' : ($leaveRequest->leave_type === 'LOP' ? 'bt-lop' : 'bt-sat')) }}">
                            {{ $leaveRequest->type_label }}
                        </span>
                        @if($leaveRequest->auto_lop)
                            <span class="lop-tag">Auto-converted from CL (balance exhausted)</span>
                        @endif
                    </div>
                </div>
                @if($leaveRequest->request_type === 'leave')
                <div class="detail-row">
                    <div class="detail-lbl">From Date</div>
                    <div class="detail-val" style="font-weight:700;">
                        {{ ($leaveRequest->from_date ?? $leaveRequest->request_date)->format('l, d M Y') }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">To Date</div>
                    <div class="detail-val" style="font-weight:700;">
                        {{ ($leaveRequest->to_date ?? $leaveRequest->request_date)->format('l, d M Y') }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-lbl">Total Days</div>
                    <div class="detail-val">
                        <strong>{{ $leaveRequest->total_days }}</strong> working day(s)
                        @if($leaveRequest->cl_days > 0 || $leaveRequest->lop_days > 0)
                        <span style="margin-left:8px;">
                            @if($leaveRequest->cl_days > 0)
                                <span style="background:#dcfce7;color:#15803d;padding:1px 7px;border-radius:3px;font-size:11px;font-weight:700;">{{ $leaveRequest->cl_days }} CL</span>
                            @endif
                            @if($leaveRequest->lop_days > 0)
                                <span style="background:#fee2e2;color:#b91c1c;padding:1px 7px;border-radius:3px;font-size:11px;font-weight:700;margin-left:4px;">{{ $leaveRequest->lop_days }} LOP</span>
                            @endif
                        </span>
                        @endif
                    </div>
                </div>
                @else
                <div class="detail-row">
                    <div class="detail-lbl">Date</div>
                    <div class="detail-val" style="font-weight:700;">
                        {{ $leaveRequest->request_date->format('l, d M Y') }}
                    </div>
                </div>
                @endif
                @if($leaveRequest->request_type === 'permission')
                <div class="detail-row">
                    <div class="detail-lbl">Permission Hours</div>
                    <div class="detail-val">{{ $leaveRequest->permission_hours }}h</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-lbl">Reason</div>
                    <div class="detail-val" style="white-space:pre-wrap;">{{ $leaveRequest->reason }}</div>
                </div>
                @if($leaveRequest->attachment)
                <div class="detail-row">
                    <div class="detail-lbl">Attachment</div>
                    <div class="detail-val">
                        <a href="{{ Storage::url($leaveRequest->attachment) }}" target="_blank" class="attach-link">
                            <span class="material-symbols-outlined">attach_file</span>
                            View Attachment
                        </a>
                    </div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-lbl">Submitted on</div>
                    <div class="detail-val">{{ $leaveRequest->created_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right: Timeline + Approval ── --}}
    <div>

        {{-- Approval actions (for L1 or L2 approver) --}}
        @if($canApproveL1 || $canApproveL2)
        <div class="show-card approval-form-card">
            <div class="show-card-header" style="background:#fffbeb;border-color:#fde68a;color:#92400e;">
                <span class="material-symbols-outlined" style="color:#d97706;">pending_actions</span>
                Action Required
            </div>
            <div class="approve-form-body">
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:12px;">
                    This request is awaiting your approval as
                    <strong>{{ $canApproveL1 ? 'Level 1 Approver' : 'Level 2 Approver (Final)' }}</strong>.
                </div>

                {{-- Approve form --}}
                <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}" id="approveForm">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label class="form-label-sm">Remarks (optional)</label>
                        <textarea name="remarks" class="form-textarea-sm" placeholder="Add any remarks…"></textarea>
                    </div>
                    <button type="submit" class="btn-approve">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ $canApproveL1 && $leaveRequest->l2_manager_id ? 'Approve & Forward to L2' : 'Approve (Final)' }}
                    </button>
                </form>

                <div class="action-divider"></div>

                {{-- Reject form --}}
                <form method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}" id="rejectForm">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label class="form-label-sm">Rejection Reason <span style="color:#dc2626;">*</span></label>
                        <textarea name="remarks" class="form-textarea-sm {{ $errors->has('remarks') ? 'is-invalid' : '' }}"
                                  placeholder="Provide reason for rejection…" required></textarea>
                        @error('remarks')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn-reject" onclick="return confirm('Are you sure you want to reject this request?')">
                        <span class="material-symbols-outlined">cancel</span>
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Approval Timeline --}}
        <div class="show-card">
            <div class="show-card-header">
                <span class="material-symbols-outlined">timeline</span>
                Approval Timeline
            </div>
            <div class="show-card-body">
                <div class="timeline">

                    {{-- Step 1: Submitted --}}
                    <div class="tl-item">
                        <div class="tl-dot done"></div>
                        <div class="tl-label">Request Submitted</div>
                        <div class="tl-meta">{{ $leaveRequest->created_at->format('d M Y, H:i') }} · by {{ $leaveRequest->user->name }}</div>
                    </div>

                    {{-- Step 2: L1 approval --}}
                    @if($leaveRequest->l1_manager_id)
                    <div class="tl-item">
                        @if(in_array($leaveRequest->status, ['approved_l1', 'approved']))
                            <div class="tl-dot done"></div>
                        @elseif($leaveRequest->status === 'rejected' && $leaveRequest->l1_actioned_at)
                            <div class="tl-dot rejected"></div>
                        @elseif($leaveRequest->status === 'pending')
                            <div class="tl-dot pend"></div>
                        @else
                            <div class="tl-dot wait"></div>
                        @endif
                        <div class="tl-label">Level 1 Approval
                            @if($leaveRequest->l1Manager)
                                <span style="font-weight:400;color:var(--text-muted);font-size:12px;">— {{ $leaveRequest->l1Manager->name }}</span>
                            @endif
                        </div>
                        @if($leaveRequest->l1_actioned_at)
                            <div class="tl-meta">{{ $leaveRequest->l1_actioned_at->format('d M Y, H:i') }}</div>
                        @else
                            <div class="tl-meta">Pending</div>
                        @endif
                        @if($leaveRequest->l1_remarks)
                            <div class="tl-remark">"{{ $leaveRequest->l1_remarks }}"</div>
                        @endif
                    </div>
                    @endif

                    {{-- Step 3: L2 approval --}}
                    @if($leaveRequest->l2_manager_id)
                    <div class="tl-item">
                        @if($leaveRequest->status === 'approved')
                            <div class="tl-dot done"></div>
                        @elseif($leaveRequest->status === 'rejected' && $leaveRequest->l2_actioned_at)
                            <div class="tl-dot rejected"></div>
                        @elseif($leaveRequest->status === 'approved_l1')
                            <div class="tl-dot pend"></div>
                        @else
                            <div class="tl-dot wait"></div>
                        @endif
                        <div class="tl-label">Level 2 Approval (Final)
                            @if($leaveRequest->l2Manager)
                                <span style="font-weight:400;color:var(--text-muted);font-size:12px;">— {{ $leaveRequest->l2Manager->name }}</span>
                            @endif
                        </div>
                        @if($leaveRequest->l2_actioned_at)
                            <div class="tl-meta">{{ $leaveRequest->l2_actioned_at->format('d M Y, H:i') }}</div>
                        @else
                            <div class="tl-meta">
                                {{ $leaveRequest->isApprovedL1() ? 'Pending your review' : 'Waiting' }}
                            </div>
                        @endif
                        @if($leaveRequest->l2_remarks)
                            <div class="tl-remark">"{{ $leaveRequest->l2_remarks }}"</div>
                        @endif
                    </div>
                    @endif

                    {{-- Final outcome --}}
                    <div class="tl-item">
                        @if($leaveRequest->isApproved())
                            <div class="tl-dot done"></div>
                            <div class="tl-label" style="color:#15803d;">Approved ✓</div>
                        @elseif($leaveRequest->isRejected())
                            <div class="tl-dot rejected"></div>
                            <div class="tl-label" style="color:#be123c;">Rejected</div>
                        @else
                            <div class="tl-dot wait"></div>
                            <div class="tl-label" style="color:var(--text-muted);">Outcome Pending</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
