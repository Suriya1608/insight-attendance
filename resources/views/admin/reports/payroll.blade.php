@extends('layouts.app')

@section('title', 'Payroll Report')

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
    .filter-bar select, .filter-bar input[type="number"] {
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

    .summary-cards {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem; margin-bottom: 1.5rem;
    }
    .sum-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1rem 1.25rem;
        box-shadow: var(--shadow-sm);
    }
    .sum-card .label { font-size: .72rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .3rem; }
    .sum-card .value { font-size: 1.25rem; font-weight: 700; color: var(--text-main); }
    .sum-card .value.green { color: #16a34a; }
    .sum-card .value.red   { color: #dc2626; }

    .table-wrap {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .rpt-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .rpt-table thead tr { background: #f8fafc; }
    .rpt-table th {
        padding: .7rem 1rem; text-align: right; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-secondary); border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .rpt-table th:nth-child(-n+3) { text-align: left; }
    .rpt-table td { padding: .7rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; text-align: right; }
    .rpt-table td:nth-child(-n+3) { text-align: left; }
    .rpt-table tr:last-child td { border-bottom: none; }
    .rpt-table tr:hover td { background: #f8fafc; }
    .rpt-table tfoot tr { background: #1e3a5f; color: #fff; font-weight: 700; }
    .rpt-table tfoot td { border: none; padding: .7rem 1rem; }

    .emp-name { font-weight: 600; font-size: .875rem; color: var(--text-main); }
    .emp-code { font-size: .75rem; color: var(--text-secondary); font-family: monospace; }
    .dept-badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: .72rem; font-weight: 600; }
    .muted { color: var(--text-muted); }
    .lop-val { color: #dc2626; font-weight: 600; }
    .net-val { color: #16a34a; font-weight: 700; }

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
        <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">receipt_long</span>
        Payroll Report
    </h1>
    <p>View saved payroll data. Use the Payroll module to generate and save records first.</p>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.reports.payroll') }}" class="filter-bar no-print">
    <label>Month:</label>
    <select name="month">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
            </option>
        @endforeach
    </select>

    <label>Year:</label>
    <input type="number" name="year" value="{{ $year }}" min="2020" max="2099" style="width:90px;">

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

    <button type="submit" class="btn-filter">
        <span class="material-symbols-outlined" style="font-size:1rem;">filter_list</span> Filter
    </button>
    <a href="{{ route('admin.reports.payroll') }}" class="btn-reset">Reset</a>
</form>

@if($rows->count())
    {{-- Summary cards --}}
    @php
        $allRows = $rows->getCollection();
        $totalGross = $allRows->sum('salary');
        $totalLop   = $allRows->sum('lop_amount');
        $totalNet   = $allRows->sum('net_salary');
    @endphp
    <div class="summary-cards no-print">
        <div class="sum-card">
            <div class="label">Period</div>
            <div class="value" style="font-size:1rem;">{{ $monthLabel }}</div>
        </div>
        <div class="sum-card">
            <div class="label">Employees</div>
            <div class="value">{{ $rows->total() }}</div>
        </div>
        <div class="sum-card">
            <div class="label">Total Gross</div>
            <div class="value">₹{{ number_format($totalGross, 0) }}</div>
        </div>
        <div class="sum-card">
            <div class="label">Total LOP Deduction</div>
            <div class="value red">₹{{ number_format($totalLop, 0) }}</div>
        </div>
        <div class="sum-card">
            <div class="label">Total Net Payable</div>
            <div class="value green">₹{{ number_format($totalNet, 0) }}</div>
        </div>
    </div>
@endif

<div class="action-bar no-print">
    <a href="{{ route('admin.reports.payroll.export', request()->query()) }}"
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
                <th style="text-align:left;">#</th>
                <th style="text-align:left;">Employee</th>
                <th style="text-align:left;">Department</th>
                <th>Working Days</th>
                <th>Present</th>
                <th>LOP Days</th>
                <th>Perm. Hrs</th>
                <th>Gross (₹)</th>
                <th>Per Day (₹)</th>
                <th>LOP Amt (₹)</th>
                <th>Net (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td style="text-align:left;color:var(--text-muted);">{{ $rows->firstItem() + $i }}</td>
                    <td style="text-align:left;">
                        <div class="emp-name">{{ $r->employee?->name ?? '—' }}</div>
                        <div class="emp-code">{{ $r->employee?->employee_code ?? '' }}</div>
                    </td>
                    <td style="text-align:left;">
                        @if($r->employee?->department)
                            <span class="dept-badge">{{ $r->employee->department->name }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>{{ $r->working_days }}</td>
                    <td>{{ number_format($r->present_days, 1) }}</td>
                    <td class="{{ $r->lop_days > 0 ? 'lop-val' : 'muted' }}">
                        {{ $r->lop_days > 0 ? number_format($r->lop_days, 2) : '0' }}
                    </td>
                    <td class="{{ $r->permission_hours > 0 ? '' : 'muted' }}">
                        {{ $r->permission_hours > 0 ? number_format($r->permission_hours, 1).'h' : '—' }}
                    </td>
                    <td>{{ number_format($r->salary, 2) }}</td>
                    <td style="color:var(--text-secondary);">{{ number_format($r->per_day_salary, 2) }}</td>
                    <td class="{{ $r->lop_amount > 0 ? 'lop-val' : 'muted' }}">
                        {{ $r->lop_amount > 0 ? number_format($r->lop_amount, 2) : '—' }}
                    </td>
                    <td class="net-val">{{ number_format($r->net_salary, 2) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="11">
                        <span class="material-symbols-outlined">search_off</span>
                        No saved payroll records found for {{ $monthLabel }}.
                        <br><small style="color:var(--text-muted);">Generate and save payroll from the Payroll module first.</small>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($rows->count())
            @php
                $pg = $rows->getCollection();
            @endphp
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:left;">TOTAL ({{ $rows->total() }} employees — page {{ $rows->currentPage() }})</td>
                    <td>{{ number_format($pg->sum('salary'), 2) }}</td>
                    <td></td>
                    <td>{{ number_format($pg->sum('lop_amount'), 2) }}</td>
                    <td>{{ number_format($pg->sum('net_salary'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
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
