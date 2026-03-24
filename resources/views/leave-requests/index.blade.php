@extends('layouts.app')

@section('title', 'Leave & Permission Requests')

@section('content')
<style>
    /* ── Page header ── */
    .lr-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; gap: 12px; flex-wrap: wrap;
    }
    .lr-page-header h1 { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0; }
    .lr-page-header p  { font-size: 13px; color: var(--text-secondary); margin: 2px 0 0; }

    .btn-apply {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 20px; background: var(--primary); color: #fff;
        border-radius: var(--radius-sm); font-size: 14px; font-weight: 600;
        text-decoration: none; white-space: nowrap;
        transition: background .15s;
    }
    .btn-apply:hover { background: var(--primary-hover); color: #fff; }
    .btn-apply .material-symbols-outlined { font-size: 18px; }

    /* ── KPI cards ── */
    .lr-kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
    @media(max-width:900px) { .lr-kpi-row { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:500px) { .lr-kpi-row { grid-template-columns: 1fr 1fr; } }

    .lr-kpi {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 16px 18px;
        box-shadow: var(--shadow-xs); border-top: 3px solid var(--kpi-color, var(--primary));
    }
    .lr-kpi .kpi-val  { font-size: 28px; font-weight: 800; color: var(--kpi-color, var(--primary)); line-height: 1.1; }
    .lr-kpi .kpi-lbl  { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: 4px; }
    .lr-kpi .kpi-sub  { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }
    .kpi-cl     { --kpi-color: #1d4ed8; }
    .kpi-perm   { --kpi-color: #7c3aed; }
    .kpi-pend   { --kpi-color: #ea580c; }
    .kpi-appr   { --kpi-color: #15803d; }

    /* ── Section card ── */
    .lr-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        margin-bottom: 24px; overflow: hidden;
    }
    .lr-card-header {
        padding: 16px 20px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
    }
    .lr-card-title {
        font-size: 14px; font-weight: 700; color: var(--text-main);
        display: flex; align-items: center; gap: 8px;
    }
    .lr-card-title .material-symbols-outlined { font-size: 18px; color: var(--text-secondary); }

    /* ── Table ── */
    .lr-table-wrap { overflow-x: auto; }
    .lr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .lr-table thead th {
        background: #f8fafc; padding: 9px 14px;
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--text-secondary);
        border-bottom: 1px solid var(--border); white-space: nowrap; text-align: left;
    }
    .lr-table tbody td { padding: 10px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .lr-table tbody tr:last-child td { border-bottom: none; }
    .lr-table tbody tr:hover td { background: #f8fafc; }
    .lr-empty { text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 13px; }
    .lr-empty .material-symbols-outlined { font-size: 40px; display: block; margin-bottom: 8px; opacity: .4; }

    /* ── Badges ── */
    .badge-lr {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 999px;
        font-size: 10px; font-weight: 700; letter-spacing: .03em; white-space: nowrap;
    }
    .badge-lr .dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
    .bl-pending    { background:#fff7ed; color:#c2410c; }  .bl-pending .dot  { background:#ea580c; }
    .bl-appr_l1    { background:#eff6ff; color:#1d4ed8; }  .bl-appr_l1 .dot { background:#3b82f6; }
    .bl-approved   { background:#f0fdf4; color:#15803d; }  .bl-approved .dot { background:#22c55e; }
    .bl-rejected   { background:#fff1f2; color:#be123c; }  .bl-rejected .dot { background:#f43f5e; }

    .badge-type {
        display: inline-block; padding: 2px 8px; border-radius: 4px;
        font-size: 10px; font-weight: 700; letter-spacing: .03em;
    }
    .bt-cl  { background:#dbeafe; color:#1e40af; }
    .bt-lop { background:#fee2e2; color:#b91c1c; }
    .bt-sat { background:#fef9c3; color:#854d0e; }
    .bt-perm{ background:#ede9fe; color:#6d28d9; }
    .bt-mixed { background:#f3e8ff; color:#6b21a8; }

    .lop-flag {
        display: inline-block; margin-left: 4px; padding: 1px 5px;
        background: #fef3c7; color: #92400e; border-radius: 3px;
        font-size: 9px; font-weight: 700; vertical-align: middle;
    }

    /* ── Day breakdown chips ── */
    .day-chips { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 3px; }
    .day-chip {
        display: inline-block; padding: 1px 6px; border-radius: 3px;
        font-size: 10px; font-weight: 700;
    }
    .dc-total { background: #f1f5f9; color: #475569; }
    .dc-cl    { background: #dcfce7; color: #15803d; }
    .dc-lop   { background: #fee2e2; color: #b91c1c; }

    /* ── Approval row highlight ── */
    .lr-table tbody tr.needs-action td { background: #fffbeb; }
    .lr-table tbody tr.needs-action:hover td { background: #fef3c7; }

    .btn-view {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; font-size: 12px; font-weight: 600;
        border: 1px solid var(--border); border-radius: var(--radius-xs);
        color: var(--text-secondary); text-decoration: none;
        background: var(--surface); transition: all .12s;
    }
    .btn-view:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-subtle); }

    /* ── Flash alerts ── */
    .alert-warn {
        display: flex; align-items: flex-start; gap: 10px;
        background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-sm);
        padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #92400e;
    }
    .alert-warn .material-symbols-outlined { font-size: 18px; color: #d97706; margin-top:1px; flex-shrink:0; }
    .alert-success {
        display: flex; align-items: flex-start; gap: 10px;
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-sm);
        padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #14532d; font-weight: 500;
    }
    .alert-success .material-symbols-outlined { font-size: 18px; color: #22c55e; flex-shrink: 0; }

    /* ── Pagination ── */
    .pag-wrap { display: flex; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid var(--border); }
</style>

{{-- Page header --}}
<div class="lr-page-header">
    <div>
        <h1>Leave & Permission Requests</h1>
        <p>Track your leaves, permissions and view approval status.</p>
    </div>
    <a href="{{ route('leave-requests.create') }}" class="btn-apply">
        <span class="material-symbols-outlined">add</span>
        Apply Request
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert-success">
        <span class="material-symbols-outlined">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- KPI Cards --}}
<div class="lr-kpi-row">
    <div class="lr-kpi kpi-cl">
        <div class="kpi-val">{{ number_format($clBalance->balance, 1) }}</div>
        <div class="kpi-lbl">CL Balance</div>
        <div class="kpi-sub">{{ number_format($clBalance->credited, 1) }} credited · {{ number_format($clBalance->used, 1) }} used</div>
    </div>
    <div class="lr-kpi kpi-perm">
        <div class="kpi-val">{{ $permRemaining }}</div>
        <div class="kpi-lbl">Permissions Remaining</div>
        <div class="kpi-sub">{{ $permLimit - $permRemaining }} used this month</div>
    </div>
    <div class="lr-kpi kpi-pend">
        <div class="kpi-val">{{ $pendingCount }}</div>
        <div class="kpi-lbl">Pending Requests</div>
        <div class="kpi-sub">Awaiting approval</div>
    </div>
    <div class="lr-kpi kpi-appr">
        <div class="kpi-val">{{ $approvedCount }}</div>
        <div class="kpi-lbl">Approved</div>
        <div class="kpi-sub">All time</div>
    </div>
</div>

{{-- ── Pending Approvals (only if I have items to approve) ── --}}
@if($pendingApprovals->isNotEmpty())
<div class="lr-card">
    <div class="lr-card-header">
        <div class="lr-card-title">
            <span class="material-symbols-outlined">approval</span>
            Pending Approvals
            <span style="background:#ea580c;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;">
                {{ $pendingApprovals->count() }}
            </span>
        </div>
    </div>
    <div class="lr-table-wrap">
        <table class="lr-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Submitted</th>
                    <th>Your Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingApprovals as $lr)
                <tr class="needs-action">
                    <td>
                        <div style="font-weight:600;">{{ $lr->user->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $lr->user->employee_code }}</div>
                    </td>
                    <td>
                        @php
                            $typeClass = $lr->request_type === 'permission' ? 'bt-perm'
                                : ($lr->cl_days > 0 && $lr->lop_days > 0 ? 'bt-mixed'
                                : ($lr->leave_type === 'CL' ? 'bt-cl'
                                : ($lr->leave_type === 'LOP' ? 'bt-lop' : 'bt-sat')));
                        @endphp
                        <span class="badge-type {{ $typeClass }}">{{ $lr->type_label }}</span>
                        @if($lr->auto_lop)<span class="lop-flag">Partial LOP</span>@endif
                    </td>
                    <td style="white-space:nowrap;">
                        {{ ($lr->from_date ?? $lr->request_date)->format('d M Y') }}
                    </td>
                    <td style="white-space:nowrap;">
                        {{ ($lr->to_date ?? $lr->request_date)->format('d M Y') }}
                    </td>
                    <td style="text-align:center;">
                        @if($lr->request_type === 'leave')
                            <span style="font-weight:700;">{{ $lr->total_days }}</span>
                            @if($lr->cl_days > 0 && $lr->lop_days > 0)
                            <div class="day-chips">
                                <span class="day-chip dc-cl">{{ $lr->cl_days }}CL</span>
                                <span class="day-chip dc-lop">{{ $lr->lop_days }}LOP</span>
                            </div>
                            @endif
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $lr->reason }}">{{ $lr->reason }}</td>
                    <td style="white-space:nowrap;font-size:12px;color:var(--text-secondary);">{{ $lr->created_at->format('d M, H:i') }}</td>
                    <td>
                        @if($lr->status === 'pending' && $lr->l1_manager_id === auth()->id())
                            <span style="font-size:11px;font-weight:600;color:#ea580c;">Level 1 Approver</span>
                        @else
                            <span style="font-size:11px;font-weight:600;color:#7c3aed;">Level 2 Approver</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('leave-requests.show', $lr) }}" class="btn-view">
                            <span class="material-symbols-outlined" style="font-size:14px;">open_in_new</span>
                            Review
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── My Requests ── --}}
<div class="lr-card">
    <div class="lr-card-header">
        <div class="lr-card-title">
            <span class="material-symbols-outlined">event_note</span>
            My Requests
        </div>
    </div>
    <div class="lr-table-wrap">
        <table class="lr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Total Days</th>
                    <th>CL Used</th>
                    <th>LOP Used</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRequests as $lr)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $lr->id }}</td>
                    <td style="white-space:nowrap;font-weight:600;">
                        {{ ($lr->from_date ?? $lr->request_date)->format('d M Y') }}
                    </td>
                    <td style="white-space:nowrap;">
                        {{ ($lr->to_date ?? $lr->request_date)->format('d M Y') }}
                    </td>
                    <td style="text-align:center;font-weight:700;">
                        @if($lr->request_type === 'leave')
                            {{ $lr->total_days }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($lr->request_type === 'leave' && $lr->cl_days > 0)
                            <span class="day-chip dc-cl">{{ $lr->cl_days }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($lr->request_type === 'leave' && $lr->lop_days > 0)
                            <span class="day-chip dc-lop">{{ $lr->lop_days }}</span>
                        @elseif($lr->request_type === 'leave' && $lr->cl_days === 0)
                            <span class="day-chip dc-lop">{{ $lr->total_days }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $typeClass = $lr->request_type === 'permission' ? 'bt-perm'
                                : ($lr->cl_days > 0 && $lr->lop_days > 0 ? 'bt-mixed'
                                : ($lr->leave_type === 'CL' ? 'bt-cl'
                                : ($lr->leave_type === 'LOP' ? 'bt-lop' : 'bt-sat')));
                        @endphp
                        <span class="badge-type {{ $typeClass }}">{{ $lr->type_label }}</span>
                        @if($lr->auto_lop)<span class="lop-flag">Partial LOP</span>@endif
                        @if($lr->request_type === 'permission')
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $lr->permission_hours }}h · {{ $lr->request_date->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;" title="{{ $lr->reason }}">
                        {{ $lr->reason }}
                    </td>
                    <td>
                        <span class="badge-lr bl-{{ str_replace('_', '', $lr->status === 'approved_l1' ? 'appr_l1' : $lr->status) }}">
                            <span class="dot"></span>
                            {{ $lr->status_label }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap;">{{ $lr->created_at->format('d M, H:i') }}</td>
                    <td>
                        <a href="{{ route('leave-requests.show', $lr) }}" class="btn-view">
                            <span class="material-symbols-outlined" style="font-size:14px;">visibility</span>
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="lr-empty">
                        <span class="material-symbols-outlined">inbox</span>
                        No requests submitted yet.
                        <a href="{{ route('leave-requests.create') }}" style="color:var(--primary);font-weight:600;margin-left:4px;">Apply now</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($myRequests->hasPages())
    <div class="pag-wrap">
        {{ $myRequests->links('vendor.pagination.simple-bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
