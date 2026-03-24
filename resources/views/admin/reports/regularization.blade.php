@extends('layouts.app')

@section('title', 'Regularization Report')

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
        margin-bottom: 1rem; box-shadow: var(--shadow-sm);
    }
    .filter-bar label { font-size: .8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap; }
    .filter-bar select, .filter-bar input[type="date"] {
        height: 2.25rem; padding: 0 .75rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        outline: none; cursor: pointer; font-family: inherit;
    }
    .filter-bar select:focus, .filter-bar input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .btn-filter {
        height: 2.25rem; padding: 0 1.125rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: .35rem;
    }
    .btn-filter:hover { background: var(--primary-hover); }
    .btn-reset {
        height: 2.25rem; padding: 0 1rem;
        background: var(--bg-light); color: var(--text-secondary);
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer;
        text-decoration: none; display: flex; align-items: center;
    }
    .btn-reset:hover { background: var(--border); color: var(--text-main); }
    .btn-export {
        height: 2.25rem; padding: 0 1rem;
        background: #16a34a; color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: .35rem;
    }
    .btn-export:hover { background: #15803d; color: #fff; }
    .btn-print {
        height: 2.25rem; padding: 0 1rem;
        background: #64748b; color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: .35rem;
    }
    .btn-print:hover { background: #475569; }
    .action-bar { display: flex; justify-content: flex-end; gap: .5rem; margin-bottom: 1rem; }

    .table-wrap {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .rpt-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .rpt-table thead tr { background: #f8fafc; }
    .rpt-table th {
        padding: .7rem 1rem; text-align: left; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-secondary); border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .rpt-table td { padding: .7rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .rpt-table tr:last-child td { border-bottom: none; }
    .rpt-table tr:hover td { background: #f8fafc; }

    .emp-name { font-weight: 600; font-size: .875rem; color: var(--text-main); }
    .emp-code { font-size: .75rem; color: var(--text-secondary); font-family: monospace; }
    .dept-badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: .72rem; font-weight: 600; }
    .muted { color: var(--text-muted); }
    .time-change { font-size: .8rem; color: var(--text-secondary); display: flex; align-items: center; gap: .25rem; }

    .badge { display: inline-flex; align-items: center; padding: .2rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .badge-approved   { background: #dcfce7; color: #15803d; }
    .badge-rejected   { background: #fee2e2; color: #dc2626; }
    .badge-pending_l1 { background: #fef9c3; color: #92400e; }
    .badge-pending_l2 { background: #dbeafe; color: #1d4ed8; }
    .badge-draft      { background: #f1f5f9; color: #475569; }

    .empty-row td { text-align: center; padding: 3rem; color: var(--text-muted); }
    .empty-row .material-symbols-outlined { font-size: 2.5rem; display: block; margin-bottom: .5rem; }
    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .pagination-info { font-size: .8rem; color: var(--text-secondary); }

    @media print {
        .no-print { display: none !important; }
        .table-wrap { box-shadow: none; border: none; }
    }
</style>
@endpush

@section('content')
<div class="page-header no-print">
    <h1>
        <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">edit_calendar</span>
        Regularization Report
    </h1>
    <p>Review all attendance regularization requests with their approval status.</p>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.reports.regularization') }}" class="filter-bar no-print">
    <label>From:</label>
    <input type="date" name="from" value="{{ $from }}">

    <label>To:</label>
    <input type="date" name="to" value="{{ $to }}">

    <label>Department:</label>
    <select name="department_id">
        <option value="">All Departments</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </select>

    <label>Employee:</label>
    <select name="employee_id">
        <option value="">All Employees</option>
        @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ $empId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
        @endforeach
    </select>

    <label>Status:</label>
    <select name="status">
        <option value="">All Statuses</option>
        @foreach($statuses as $val => $label)
            <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-filter">
        <span class="material-symbols-outlined" style="font-size:1rem;">filter_list</span> Filter
    </button>
    <a href="{{ route('admin.reports.regularization') }}" class="btn-reset">Reset</a>
</form>

<div class="action-bar no-print">
    <a href="{{ route('admin.reports.regularization.export', request()->query()) }}"
       class="btn-export">
        <span class="material-symbols-outlined" style="font-size:1rem;">download</span> Export CSV
    </a>
    <button class="btn-print" onclick="window.print()">
        <span class="material-symbols-outlined" style="font-size:1rem;">print</span> Print / PDF
    </button>
</div>

<div class="table-wrap">
    <table class="rpt-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Date</th>
                <th>Type</th>
                <th>Requested Times</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td class="muted">{{ $rows->firstItem() + $i }}</td>
                    <td>
                        <div class="emp-name">{{ $r->user?->name ?? '—' }}</div>
                        <div class="emp-code">{{ $r->user?->employee_code ?? '' }}</div>
                    </td>
                    <td>
                        @if($r->user?->department)
                            <span class="dept-badge">{{ $r->user->department->name }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>{{ $r->date->format('d M Y') }}</td>
                    <td style="font-size:.8rem;">{{ $r->type_label }}</td>
                    <td>
                        <div class="time-change">
                            <span>{{ $r->requested_punch_in  ? substr($r->requested_punch_in,  0, 5) : '—' }}</span>
                            <span class="material-symbols-outlined" style="font-size:.9rem;color:var(--text-muted);">arrow_forward</span>
                            <span>{{ $r->requested_punch_out ? substr($r->requested_punch_out, 0, 5) : '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-{{ $r->status }}">{{ $r->status_label }}</span>
                    </td>
                    <td style="font-size:.8rem;color:var(--text-secondary);">
                        {{ $r->submitted_at?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="8">
                        <span class="material-symbols-outlined">search_off</span>
                        No regularization requests found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($rows->hasPages())
        <div class="pagination-wrap no-print">
            <span class="pagination-info">
                Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }} records
            </span>
            {{ $rows->links() }}
        </div>
    @endif
</div>
@endsection
