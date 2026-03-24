@extends('layouts.app')

@section('title', 'Audit Logs')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    .filter-bar {
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: .875rem 1.25rem;
        margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);
    }
    .filter-bar label { font-size: .8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap; }
    .filter-bar select, .filter-bar input[type="date"] {
        height: 2.25rem; padding: 0 .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        outline: none; cursor: pointer; font-family: inherit;
    }
    .filter-bar select:focus, .filter-bar input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .btn-filter {
        height: 2.25rem; padding: 0 1.125rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; transition: background .15s; display: flex; align-items: center; gap: .35rem;
    }
    .btn-filter:hover { background: var(--primary-hover); }
    .btn-reset {
        height: 2.25rem; padding: 0 1rem;
        background: var(--bg-light); color: var(--text-secondary); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; text-decoration: none; display: flex; align-items: center;
    }
    .btn-reset:hover { background: var(--border); color: var(--text-main); }

    .table-wrap {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .audit-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .audit-table thead tr { background: #f8fafc; }
    .audit-table th {
        padding: .7rem 1rem; text-align: left; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-secondary); border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .audit-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .audit-table tr:last-child td { border-bottom: none; }
    .audit-table tr:hover td { background: #f8fafc; }

    .badge {
        display: inline-flex; align-items: center; gap: .2rem;
        padding: .2rem .625rem; border-radius: 999px;
        font-size: .72rem; font-weight: 700; white-space: nowrap;
    }
    .badge-create, .badge-created, .badge-approved, .badge-approved_l1, .badge-submitted {
        background: #dcfce7; color: #15803d;
    }
    .badge-update, .badge-updated, .badge-commented, .badge-regularization_applied {
        background: #dbeafe; color: #1d4ed8;
    }
    .badge-delete, .badge-rejected {
        background: #fee2e2; color: #dc2626;
    }
    .badge-cancel {
        background: #fef9c3; color: #92400e;
    }
    .badge-generic {
        background: #f1f5f9; color: #475569;
    }

    .user-chip { display: flex; align-items: center; gap: .5rem; }
    .user-avatar {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary) 0%, #0f6fd4 100%);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 700;
    }
    .user-name  { font-size: .84rem; font-weight: 600; color: var(--text-main); }
    .user-role  { font-size: .72rem; color: var(--text-muted); }

    .json-toggle { font-size: .75rem; color: var(--primary); cursor: pointer; text-decoration: underline; white-space: nowrap; }
    .json-box { display: none; margin-top: .375rem; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: .5rem; font-size: .72rem; font-family: monospace; white-space: pre-wrap; max-height: 140px; overflow-y: auto; color: var(--text-secondary); }
    .json-box.open { display: block; }

    .empty-row td { text-align: center; padding: 3rem; color: var(--text-muted); }
    .empty-row .material-symbols-outlined { font-size: 2.5rem; display: block; margin-bottom: .5rem; }

    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }
</style>
@endpush

@section('content')
@php
    $actionOptions = [
        'create' => 'Create',
        'created' => 'Created',
        'update' => 'Update',
        'updated' => 'Updated',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'submitted' => 'Submitted',
        'commented' => 'Commented',
        'approved' => 'Approved',
        'approved_l1' => 'Approved L1',
        'rejected' => 'Rejected',
        'regularization_applied' => 'Regularization Applied',
    ];

    $badgeClassMap = [
        'create' => 'badge-create',
        'created' => 'badge-created',
        'update' => 'badge-update',
        'updated' => 'badge-updated',
        'delete' => 'badge-delete',
        'cancel' => 'badge-cancel',
        'submitted' => 'badge-submitted',
        'commented' => 'badge-commented',
        'approved' => 'badge-approved',
        'approved_l1' => 'badge-approved_l1',
        'rejected' => 'badge-rejected',
        'regularization_applied' => 'badge-regularization_applied',
    ];
@endphp

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">manage_search</span>
            Audit Logs
        </h1>
        <p>Read-only record of all significant system actions. Logs cannot be edited or deleted.</p>
    </div>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="filter-bar">
        <label>Module:</label>
        <select name="module">
            <option value="">All Modules</option>
            @foreach($modules as $mod)
                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
            @endforeach
        </select>

        <label>Action:</label>
        <select name="action">
            <option value="">All Actions</option>
            @foreach($actionOptions as $value => $label)
                <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <label>From:</label>
        <input type="date" name="from" value="{{ request('from') }}">

        <label>To:</label>
        <input type="date" name="to" value="{{ request('to') }}">

        <button type="submit" class="btn-filter">
            <span class="material-symbols-outlined">filter_list</span> Filter
        </button>
        <a href="{{ route('admin.audit-logs.index') }}" class="btn-reset">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User (Affected)</th>
                    <th>Module</th>
                    <th>Record ID</th>
                    <th>Action</th>
                    <th>Performed By</th>
                    <th>IP Address</th>
                    <th>Date &amp; Time</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php
                    $badgeClass = $badgeClassMap[$log->action_type] ?? 'badge-generic';
                    $actionLabel = $actionOptions[$log->action_type] ?? \Illuminate\Support\Str::headline($log->action_type);
                @endphp
                <tr>
                    <td style="color:var(--text-muted);font-size:.8rem;">{{ $log->id }}</td>
                    <td>
                        @if($log->user)
                            <div class="user-chip">
                                <div class="user-avatar">{{ strtoupper(substr($log->user->name, 0, 1)) }}</div>
                                <div>
                                    <div class="user-name">{{ $log->user->name }}</div>
                                    <div class="user-role">{{ $log->user->employee_code ?? $log->user->role }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">{{ $log->module_name }}</td>
                    <td style="color:var(--text-muted);">{{ $log->record_id ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">
                            {{ $actionLabel }}
                        </span>
                    </td>
                    <td>
                        @if($log->performedBy)
                            <div class="user-chip">
                                <div class="user-avatar" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                                    {{ strtoupper(substr($log->performedBy->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="user-name">{{ $log->performedBy->name }}</div>
                                    <div class="user-role">{{ $log->performedBy->role }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--text-muted);">System</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:var(--text-secondary);font-family:monospace;">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                    <td style="font-size:.8rem;color:var(--text-secondary);white-space:nowrap;">
                        {{ $log->performed_at->format('d M Y') }}<br>
                        <span style="font-family:monospace;">{{ $log->performed_at->format('h:i:s A') }}</span>
                    </td>
                    <td>
                        @if($log->old_value || $log->new_value)
                            <span class="json-toggle" onclick="toggleJson({{ $log->id }})">View diff</span>
                            <div class="json-box" id="json-{{ $log->id }}">@if($log->old_value)OLD:
{{ json_encode($log->old_value, JSON_PRETTY_PRINT) }}
@endif
@if($log->new_value)NEW:
{{ json_encode($log->new_value, JSON_PRETTY_PRINT) }}
@endif</div>
                        @else
                            <span style="color:var(--text-muted);font-size:.8rem;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="9">
                        <span class="material-symbols-outlined">manage_search</span>
                        No audit logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
        <div class="pagination-wrap">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    function toggleJson(id) {
        const box = document.getElementById('json-' + id);
        box.classList.toggle('open');
    }
</script>
@endpush
