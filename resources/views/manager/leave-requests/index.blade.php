@extends('layouts.app')

@section('title', 'Team Leave & Permission Requests')

@push('styles')
<style>
    /* ── Flash ─────────────────────────────────────────────────────── */
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    /* ── KPI stats ─────────────────────────────────────────────────── */
    .kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:900px)  { .kpi-grid { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:480px)  { .kpi-grid { grid-template-columns:1fr; } }
    .kpi-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);padding:1.125rem 1.25rem;display:flex;align-items:center;gap:.875rem; }
    .kpi-icon { width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#fff; }
    .kpi-icon .material-symbols-outlined { font-size:22px;font-variation-settings:'FILL' 1; }
    .kpi-val  { font-size:1.625rem;font-weight:800;letter-spacing:-.04em;line-height:1;color:var(--text-main); }
    .kpi-lbl  { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:.2rem; }

    /* ── Card / filter ─────────────────────────────────────────────── */
    .card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm); }
    .filter-bar { display:flex;gap:.625rem;flex-wrap:wrap;padding:.875rem 1.25rem;border-bottom:1px solid var(--border);background:#fafbfc;align-items:center; }
    .filter-bar input,
    .filter-bar select { font-size:.8125rem;padding:.375rem .625rem;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-main);background:var(--surface);height:34px; }
    .filter-bar input { min-width:170px; }
    .filter-bar input:focus,
    .filter-bar select:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .btn-filter { height:34px;padding:0 .875rem;font-size:.8125rem;font-weight:600;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;text-decoration:none; }
    .btn-filter:hover { background:var(--bg-light);color:var(--text-main); }
    .btn-filter.active { background:var(--primary-subtle);border-color:var(--primary);color:var(--primary); }

    /* ── Table ─────────────────────────────────────────────────────── */
    .req-table { width:100%;border-collapse:collapse;font-size:.8375rem; }
    .req-table th { padding:.625rem 1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:2px solid var(--border);background:#f8fafc;white-space:nowrap; }
    .req-table td { padding:.75rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle; }
    .req-table tr:last-child td { border-bottom:none; }
    .req-table tr:hover td { background:#f8fafc; }

    /* ── Badges ────────────────────────────────────────────────────── */
    .badge { display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .6rem;border-radius:999px;font-size:.72rem;font-weight:700;letter-spacing:.02em;white-space:nowrap; }
    .badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }

    .sts-pending     { background:#fef9c3;color:#92400e; }
    .sts-pending     .badge-dot { background:#f59e0b; }
    .sts-approved_l1 { background:#eff6ff;color:#1d4ed8; }
    .sts-approved_l1 .badge-dot { background:#3b82f6; }
    .sts-approved    { background:#f0fdf4;color:#15803d; }
    .sts-approved    .badge-dot { background:#22c55e; }
    .sts-rejected    { background:#fff1f2;color:#dc2626; }
    .sts-rejected    .badge-dot { background:#ef4444; }

    .type-leave      { background:#f0fdf4;color:#166534;border-radius:4px;font-size:.7rem;font-weight:700;padding:.18rem .5rem; }
    .type-permission { background:#eff6ff;color:#1e40af;border-radius:4px;font-size:.7rem;font-weight:700;padding:.18rem .5rem; }

    /* ── Action buttons ─────────────────────────────────────────────── */
    .act-btn { display:inline-flex;align-items:center;justify-content:center;height:28px;padding:0 .6rem;gap:.25rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:transparent;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;color:var(--text-secondary); }
    .act-btn:hover { background:var(--bg-light);color:var(--primary);border-color:var(--primary-subtle); }
    .act-btn .material-symbols-outlined { font-size:.9rem; }
    .act-approve { color:#15803d;border-color:#bbf7d0;background:#f0fdf4; }
    .act-approve:hover { background:#dcfce7;color:#166534;border-color:#86efac; }
    .act-decline { color:#dc2626;border-color:#fecaca;background:#fff1f2; }
    .act-decline:hover { background:#fee2e2;color:#b91c1c;border-color:#fca5a5; }

    /* ── Empty state ────────────────────────────────────────────────── */
    .empty-state { text-align:center;padding:3rem 1rem;color:var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size:3rem;display:block;margin-bottom:.75rem;opacity:.35; }
    .empty-state p { font-size:.9rem;margin:0; }

    /* ── Modal ──────────────────────────────────────────────────────── */
    .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal-box { background:var(--surface);border-radius:var(--radius-md);box-shadow:0 20px 60px rgba(0,0,0,.2);width:100%;max-width:440px;margin:1rem; }
    .modal-header { display:flex;align-items:center;gap:.5rem;padding:1rem 1.25rem;border-bottom:1px solid var(--border); }
    .modal-header h6 { font-size:.9375rem;font-weight:700;margin:0;color:var(--text-main); }
    .modal-header .material-symbols-outlined { color:#dc2626;font-size:1.2rem; }
    .modal-body { padding:1.25rem; }
    .modal-footer { display:flex;justify-content:flex-end;gap:.625rem;padding:.875rem 1.25rem;border-top:1px solid var(--border); }
    .modal-close { margin-left:auto;background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.2rem;line-height:1;padding:0; }
    .modal-label { font-size:.8125rem;font-weight:600;color:var(--text-main);margin-bottom:.4rem;display:block; }
    .modal-textarea { width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;resize:vertical;min-height:90px; }
    .modal-textarea:focus { outline:none;border-color:#ef4444;box-shadow:0 0 0 3px #fee2e2; }
    .btn-danger { background:#dc2626;color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1.125rem;font-size:.875rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem; }
    .btn-danger:hover { background:#b91c1c; }
    .btn-cancel { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.875rem;font-weight:500;cursor:pointer; }
    .btn-cancel:hover { background:var(--bg-light); }
</style>
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Team Leave & Permission Requests</h1>
    <p class="page-subtitle" style="margin-bottom:0;">Review and approve leave or permission requests from your team members.</p>
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

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#3b82f6;">
            <span class="material-symbols-outlined">assignment</span>
        </div>
        <div>
            <div class="kpi-val">{{ $stats['total'] }}</div>
            <div class="kpi-lbl">Total Requests</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f59e0b;">
            <span class="material-symbols-outlined">pending_actions</span>
        </div>
        <div>
            <div class="kpi-val">{{ $stats['pending'] }}</div>
            <div class="kpi-lbl">Awaiting My Action</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#22c55e;">
            <span class="material-symbols-outlined">check_circle</span>
        </div>
        <div>
            <div class="kpi-val">{{ $stats['approved'] }}</div>
            <div class="kpi-lbl">Approved</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ef4444;">
            <span class="material-symbols-outlined">cancel</span>
        </div>
        <div>
            <div class="kpi-val">{{ $stats['declined'] }}</div>
            <div class="kpi-lbl">Declined</div>
        </div>
    </div>
</div>

{{-- Requests Table --}}
<div class="card">
    <form method="GET" action="{{ route('manager.leave-requests.index') }}">
        <div class="filter-bar">
            <select name="request_type">
                <option value="">All Types</option>
                <option value="leave"      {{ request('request_type') === 'leave'      ? 'selected' : '' }}>Leave</option>
                <option value="permission" {{ request('request_type') === 'permission' ? 'selected' : '' }}>Permission</option>
            </select>
            <select name="status">
                <option value="">All Status</option>
                <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Pending</option>
                <option value="approved_l1" {{ request('status') === 'approved_l1' ? 'selected' : '' }}>Approved (L1)</option>
                <option value="approved"    {{ request('status') === 'approved'    ? 'selected' : '' }}>Approved</option>
                <option value="rejected"    {{ request('status') === 'rejected'    ? 'selected' : '' }}>Declined</option>
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" title="Requested From">
            <input type="date" name="to_date"   value="{{ request('to_date') }}"   title="Requested To">
            <input type="text" name="employee"  value="{{ request('employee') }}"  placeholder="Employee name / code">
            <button type="submit" class="btn-filter active">
                <span class="material-symbols-outlined" style="font-size:.95rem;">filter_list</span>
                Filter
            </button>
            @if(request()->hasAny(['request_type','status','from_date','to_date','employee']))
                <a href="{{ route('manager.leave-requests.index') }}" class="btn-filter">Clear</a>
            @endif
        </div>
    </form>

    <div style="overflow-x:auto;">
        <table class="req-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Request Type</th>
                    <th>From Date</th>
                    <th>To Date / Hours</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $i => $req)
                    @php
                        $canAct = ($req->status === 'pending'     && $req->l1_manager_id === $manager->id)
                               || ($req->status === 'approved_l1' && $req->l2_manager_id === $manager->id);
                        $approveLabel = ($req->l2_manager_id && $req->status === 'pending') ? 'Approve & Fwd' : 'Approve';
                    @endphp
                    <tr>
                        <td style="color:var(--text-muted);font-size:.8rem;">{{ $requests->firstItem() + $i }}</td>

                        {{-- Employee --}}
                        <td>
                            <div style="font-weight:600;font-size:.8375rem;">{{ $req->user->name }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">{{ $req->user->employee_code }}</div>
                        </td>

                        {{-- Request Type --}}
                        <td>
                            <span class="{{ $req->request_type === 'permission' ? 'type-permission' : 'type-leave' }}">
                                {{ $req->type_label }}
                            </span>
                        </td>

                        {{-- From Date --}}
                        <td style="font-size:.8125rem;white-space:nowrap;">
                            @if($req->request_type === 'permission')
                                {{ $req->request_date->format('d M Y') }}
                            @else
                                {{ $req->from_date?->format('d M Y') ?? '—' }}
                            @endif
                        </td>

                        {{-- To Date / Hours --}}
                        <td style="font-size:.8125rem;white-space:nowrap;">
                            @if($req->request_type === 'permission')
                                <span style="font-weight:600;">{{ $req->permission_hours }}h</span>
                            @else
                                {{ $req->to_date?->format('d M Y') ?? '—' }}
                                @if($req->total_days)
                                    <span style="color:var(--text-muted);font-size:.75rem;margin-left:.25rem;">({{ $req->total_days }}d)</span>
                                @endif
                            @endif
                        </td>

                        {{-- Reason --}}
                        <td style="max-width:200px;">
                            <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;font-size:.8125rem;" title="{{ $req->reason }}">
                                {{ $req->reason }}
                            </div>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge sts-{{ $req->status }}">
                                <span class="badge-dot"></span>
                                {{ $req->status_label }}
                            </span>
                        </td>

                        {{-- Requested On --}}
                        <td style="font-size:.8rem;white-space:nowrap;color:var(--text-muted);">
                            {{ $req->created_at->format('d M Y') }}<br>
                            <span style="font-size:.75rem;">{{ $req->created_at->format('H:i') }}</span>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div style="display:flex;gap:.375rem;align-items:center;">
                                @if($canAct)
                                    {{-- Quick Approve --}}
                                    <form method="POST" action="{{ route('leave-requests.approve', $req) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="remarks" value="">
                                        <button type="submit" class="act-btn act-approve" title="{{ $approveLabel }}"
                                                onclick="return confirm('Approve this request?')">
                                            <span class="material-symbols-outlined">check</span>
                                            {{ $approveLabel }}
                                        </button>
                                    </form>
                                    {{-- Decline (modal) --}}
                                    <button type="button" class="act-btn act-decline" title="Decline"
                                            onclick="openDeclineModal('{{ route('leave-requests.reject', $req) }}')">
                                        <span class="material-symbols-outlined">close</span>
                                        Decline
                                    </button>
                                @endif
                                {{-- View --}}
                                <a href="{{ route('leave-requests.show', $req) }}" class="act-btn" title="View Details">
                                    <span class="material-symbols-outlined">visibility</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <span class="material-symbols-outlined">assignment</span>
                                <p>No requests found for your team.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            {{ $requests->links() }}
        </div>
    @endif
</div>

{{-- Decline Modal --}}
<div class="modal-overlay" id="declineModal">
    <div class="modal-box">
        <div class="modal-header">
            <span class="material-symbols-outlined">cancel</span>
            <h6>Decline Request</h6>
            <button class="modal-close" onclick="closeDeclineModal()">×</button>
        </div>
        <form method="POST" id="declineForm">
            @csrf
            @method('POST')
            <div class="modal-body">
                <label class="modal-label">Reason for Declining <span style="color:#ef4444;">*</span></label>
                <textarea name="remarks" class="modal-textarea" placeholder="Provide a reason for declining this request..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeclineModal()">Cancel</button>
                <button type="submit" class="btn-danger">
                    <span class="material-symbols-outlined" style="font-size:1rem;">cancel</span>
                    Decline Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openDeclineModal(actionUrl) {
    document.getElementById('declineForm').action = actionUrl;
    document.getElementById('declineModal').classList.add('open');
    document.querySelector('#declineModal textarea').value = '';
    document.querySelector('#declineModal textarea').focus();
}
function closeDeclineModal() {
    document.getElementById('declineModal').classList.remove('open');
}
// Close on overlay click
document.getElementById('declineModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeclineModal();
});
// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeclineModal();
});
</script>
@endpush
