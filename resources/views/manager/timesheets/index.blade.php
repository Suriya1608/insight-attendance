@extends('layouts.app')

@section('title', 'Team Timesheets')

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .filter-bar { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:.875rem 1.25rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end; }
    .filter-bar .fg { display:flex;flex-direction:column;gap:.3rem; }
    .filter-bar label { font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em; }
    .filter-bar select { padding:.45rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;background:var(--surface);color:var(--text-main);min-width:160px; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-outline { background:transparent;border:1px solid var(--border);color:var(--text-secondary);border-radius:var(--radius-sm);padding:.45rem .875rem;font-size:.8375rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:all .15s; }
    .btn-outline:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }
    .ts-table { width:100%;border-collapse:collapse; }
    .ts-table th { padding:.625rem 1rem;font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;text-align:left;border-bottom:2px solid var(--border);background:#f8fafc; }
    .ts-table td { padding:.75rem 1rem;font-size:.875rem;color:var(--text-main);border-bottom:1px solid var(--border);vertical-align:middle; }
    .ts-table tr:hover td { background:#f8fafc; }
    .ts-table a { text-decoration:none;color:var(--primary);font-weight:600; }
    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:999px;font-size:.72rem;font-weight:700; }
    .badge-gray { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-green { background:#f0fdf4;color:#15803d; }
    .badge-red { background:#fff1f2;color:#dc2626; }
    .avatar { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#6366f1);color:#fff;font-size:.68rem;font-weight:800;flex-shrink:0; }
    .pending-alert { background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius-md);padding:.75rem 1.25rem;display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;font-size:.875rem; }
    .pending-alert .material-symbols-outlined { font-size:1.2rem;color:#c2410c;flex-shrink:0; }
    .month-nav { display:flex;align-items:center;gap:.5rem; }
    .month-nav a { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;transition:all .15s; }
    .month-nav a:hover { background:var(--bg-light); }
    .month-label { font-size:.9375rem;font-weight:700;min-width:120px;text-align:center; }
    .empty-state { text-align:center;padding:3rem 1rem;color:var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size:3rem;display:block;margin-bottom:.75rem; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Team Timesheets</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Review and approve your team's daily work reports.</p>
    </div>
</div>

@if(session('success'))
    <div class="flash flash-success">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
        <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
@endif

@if($pendingCount > 0)
    <div class="pending-alert">
        <span class="material-symbols-outlined">pending_actions</span>
        <span>You have <strong>{{ $pendingCount }}</strong> timesheet(s) awaiting your review.</span>
    </div>
@endif

@php
    $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
    $queryBase = ['month' => $month];
    if(request('status')) $queryBase['status'] = request('status');
    if(request('employee_id')) $queryBase['employee_id'] = request('employee_id');
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
    <div class="month-nav">
        <a href="{{ route('manager.timesheets.index', array_merge($queryBase, ['month' => $prevMonth])) }}" title="Previous month"><span class="material-symbols-outlined" style="font-size:1.1rem;">chevron_left</span></a>
        <span class="month-label">{{ $monthDate->format('F Y') }}</span>
        <a href="{{ route('manager.timesheets.index', array_merge($queryBase, ['month' => $nextMonth])) }}" title="Next month"><span class="material-symbols-outlined" style="font-size:1.1rem;">chevron_right</span></a>
    </div>
</div>

<form method="GET" action="{{ route('manager.timesheets.index') }}">
    <input type="hidden" name="month" value="{{ $month }}">
    <div class="filter-bar">
        <div class="fg">
            <label>Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending_l1" {{ request('status') === 'pending_l1' ? 'selected' : '' }}>Pending L1 Review</option>
                <option value="pending_l2" {{ request('status') === 'pending_l2' ? 'selected' : '' }}>Pending L2 Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="fg">
            <label>Employee</label>
            <select name="employee_id">
                <option value="">All Employees</option>
                @foreach($teamMembers as $member)
                    <option value="{{ $member->id }}" {{ request('employee_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary" style="align-self:flex-end;"><span class="material-symbols-outlined" style="font-size:1rem;">filter_list</span>Filter</button>
        <a href="{{ route('manager.timesheets.index', ['month' => $month]) }}" class="btn-outline" style="align-self:flex-end;">Clear</a>
    </div>
</form>

<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
    @if($timesheets->isNotEmpty())
        <div style="overflow-x:auto;">
            <table class="ts-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Entries</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timesheets as $ts)
                        @php
                            $colorMap = ['gray'=>'badge-gray','orange'=>'badge-orange','blue'=>'badge-blue','green'=>'badge-green','red'=>'badge-red'];
                            $badgeClass = $colorMap[$ts->status_color] ?? 'badge-gray';
                            $isPending = in_array($ts->status, ['pending_l1', 'pending_l2'], true);
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.625rem;">
                                    <div class="avatar">{{ strtoupper(substr($ts->user->name, 0, 2)) }}</div>
                                    <div>
                                        <div style="font-weight:700;">{{ $ts->user->name }}</div>
                                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $ts->user->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $ts->date->format('l, d M Y') }}</td>
                            <td>{{ $ts->entries->count() }}</td>
                            <td style="font-weight:700;{{ $ts->total_minutes > 0 ? 'color:var(--primary);' : 'color:var(--text-muted);' }}">{{ $ts->formatted_total_hours }}</td>
                            <td><span class="status-badge {{ $badgeClass }}">{{ $ts->status_label }}</span></td>
                            <td style="font-size:.8125rem;color:var(--text-muted);">{{ $ts->submitted_at ? $ts->submitted_at->format('d M, h:i A') : '-' }}</td>
                            <td><a href="{{ route('manager.timesheets.show', $ts) }}">{{ $isPending ? 'Review ->' : 'View ->' }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($timesheets->hasPages())
            <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">{{ $timesheets->links() }}</div>
        @endif
    @else
        <div class="empty-state">
            <span class="material-symbols-outlined">schedule</span>
            <p>No timesheets found for {{ $monthDate->format('F Y') }}.</p>
        </div>
    @endif
</div>
@endsection
