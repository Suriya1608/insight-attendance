@extends('layouts.app')

@section('title', 'Attendance History')

@push('styles')
<style>
    /* ── Flash ──────────────────────────────────────────────────────────────── */
    .flash {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem 1rem; border-radius: var(--radius-md);
        font-size: .875rem; font-weight: 500; margin-bottom: 1.25rem;
        border: 1px solid transparent;
    }
    .flash .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-close { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.1rem; opacity: .6; }
    .flash-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }

    /* ── KPI grid ───────────────────────────────────────────────────────────── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem; margin-bottom: 1.5rem;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        padding: 1rem 1.125rem; display: flex; align-items: center; gap: .875rem;
        position: relative; overflow: hidden;
    }
    .kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--kpi-color, var(--primary));
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .kpi-icon {
        width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: var(--kpi-color, var(--primary)); color: #fff;
    }
    .kpi-icon .material-symbols-outlined { font-size: 21px; font-variation-settings: 'FILL' 1; }
    .kpi-val { font-size: 1.625rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; color: var(--text-main); }
    .kpi-lbl { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .2rem; }

    /* ── Filter card ────────────────────────────────────────────────────────── */
    .filter-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        margin-bottom: 1.25rem; overflow: hidden;
    }
    .filter-header {
        display: flex; align-items: center; gap: .5rem;
        padding: .7rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }
    .filter-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); }
    .filter-header h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--text-main); }
    .filter-body { padding: 1.125rem 1.25rem; }

    /* Filter tabs */
    .filter-tabs {
        display: flex; gap: 2px; background: var(--bg-light);
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        padding: 3px; width: fit-content; margin-bottom: 1rem;
    }
    .filter-tab {
        padding: .375rem .875rem; border-radius: 6px;
        font-size: .8rem; font-weight: 600; cursor: pointer;
        border: none; background: transparent; color: var(--text-secondary);
        transition: all .15s;
    }
    .filter-tab.active { background: var(--surface); color: var(--text-main); box-shadow: var(--shadow-xs); }

    .filter-row { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: .3rem; }
    .filter-label { font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
    .filter-select, .filter-input {
        padding: .45rem .75rem; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); font-size: .875rem; color: var(--text-main);
        background: var(--surface); outline: none; transition: border-color .15s;
        height: 38px;
    }
    .filter-select:focus, .filter-input:focus { border-color: var(--primary); }
    .btn-apply {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .45rem 1.125rem; background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: .875rem;
        font-weight: 600; cursor: pointer; transition: background .15s; height: 38px;
    }
    .btn-apply:hover { background: var(--primary-hover); }
    .btn-apply .material-symbols-outlined { font-size: 1rem; }

    /* ── Table card ─────────────────────────────────────────────────────────── */
    .table-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .table-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #f8fafc; gap: .75rem; flex-wrap: wrap;
    }
    .table-card-header-left { display: flex; align-items: center; gap: .5rem; }
    .table-card-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); }
    .table-card-header h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--text-main); }
    .range-pill {
        background: var(--primary-subtle); color: var(--primary);
        font-size: .72rem; font-weight: 700; padding: .15rem .55rem;
        border-radius: 999px; letter-spacing: .02em;
    }

    /* Export buttons */
    .export-group { display: flex; gap: .5rem; }
    .btn-export {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .35rem .875rem; border-radius: var(--radius-sm);
        font-size: .78rem; font-weight: 600; cursor: pointer;
        border: 1.5px solid var(--border); background: var(--surface);
        color: var(--text-secondary); text-decoration: none; transition: all .15s;
    }
    .btn-export:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-subtle); }
    .btn-export .material-symbols-outlined { font-size: .95rem; }
    .btn-export-pdf  { }
    .btn-export-csv  { }

    /* ── Attendance table ───────────────────────────────────────────────────── */
    .att-table { width: 100%; border-collapse: collapse; }
    .att-table thead th {
        padding: .625rem 1rem; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted);
        background: #f8fafc; border-bottom: 1px solid var(--border);
        white-space: nowrap; text-align: left;
    }
    .att-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background .1s;
    }
    .att-table tbody tr:last-child { border-bottom: none; }
    .att-table tbody tr:hover { background: #fafbfd; }

    /* Row tints */
    .att-table tbody tr.row-sunday           { background: #f8fafc; }
    .att-table tbody tr.row-holiday          { background: #eff6ff; }
    .att-table tbody tr.row-absent           { background: #fff5f5; }
    .att-table tbody tr.row-future           { opacity: .5; }
    .att-table tbody tr.row-missed_punch_out { background: #fff1f1; }
    .att-table tbody tr.row-pending_regularization { background: #fffbeb; }

    .att-table tbody tr.row-sunday:hover                 { background: #f1f5f9; }
    .att-table tbody tr.row-holiday:hover                { background: #dbeafe; }
    .att-table tbody tr.row-absent:hover                 { background: #fee2e2; }
    .att-table tbody tr.row-missed_punch_out:hover       { background: #fee2e2; }
    .att-table tbody tr.row-pending_regularization:hover { background: #fef3c7; }

    .att-table td {
        padding: .65rem 1rem; font-size: .84rem; color: var(--text-main);
        vertical-align: middle;
    }
    .td-date { white-space: nowrap; }
    .date-num { font-size: .95rem; font-weight: 700; color: var(--text-main); }
    .date-day { font-size: .72rem; color: var(--text-muted); font-weight: 500; margin-top: 1px; }
    .td-time  { font-family: 'JetBrains Mono', monospace; font-size: .82rem; font-weight: 600; white-space: nowrap; }
    .td-time.empty { color: var(--text-muted); font-weight: 400; }
    .td-loc   { font-size: .8rem; color: var(--text-secondary); max-width: 180px; }
    .td-loc .loc-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; max-width: 170px; }
    .td-hours { font-family: monospace; font-size: .85rem; font-weight: 600; white-space: nowrap; }

    /* Status badges */
    .badge-status {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem; border-radius: 999px;
        font-size: .7rem; font-weight: 700; letter-spacing: .02em;
        white-space: nowrap;
    }
    .badge-status::before {
        content: ''; width: 5px; height: 5px; border-radius: 50%;
        background: currentColor; display: inline-block;
    }
    .bs-present               { background: #dcfce7; color: #15803d; }
    .bs-absent                { background: #fee2e2; color: #dc2626; }
    .bs-holiday               { background: #dbeafe; color: #1d4ed8; }
    .bs-sunday                { background: #f1f5f9; color: #64748b; }
    .bs-future                { background: #f1f5f9; color: #94a3b8; }
    .bs-leave                 { background: #ede9fe; color: #6d28d9; }
    .bs-half_day              { background: #fef9c3; color: #854d0e; }
    .bs-missed_punch_out      { background: #fee2e2; color: #b91c1c; }
    .bs-pending_regularization{ background: #fef3c7; color: #92400e; }

    /* Apply Regularization quick-action button */
    .btn-regularize {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .65rem; border-radius: var(--radius-sm);
        font-size: .72rem; font-weight: 700; white-space: nowrap;
        background: #dc2626; color: #fff; border: none; cursor: pointer;
        text-decoration: none; transition: background .15s; margin-top: .3rem;
    }
    .btn-regularize:hover { background: #b91c1c; color: #fff; }
    .btn-regularize .material-symbols-outlined { font-size: .85rem; }

    /* Hours status badge */
    .badge-hours {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .18rem .55rem; border-radius: 999px;
        font-size: .68rem; font-weight: 700;
    }
    .bh-sufficient   { background: #dcfce7; color: #15803d; }
    .bh-insufficient { background: #fff7ed; color: #c2410c; }

    /* Empty state */
    .table-empty {
        text-align: center; padding: 3rem 1rem;
        color: var(--text-muted); font-size: .9rem;
    }
    .table-empty .material-symbols-outlined { font-size: 2.5rem; display: block; margin-bottom: .75rem; color: var(--border); }

    /* ── Pagination ─────────────────────────────────────────────────────────── */
    .pagination-wrap {
        display: flex; align-items: center; justify-content: space-between;
        padding: .875rem 1.25rem; border-top: 1px solid var(--border);
        flex-wrap: wrap; gap: .75rem;
    }
    .pag-info { font-size: .8rem; color: var(--text-muted); }
    .pag-links { display: flex; gap: 4px; }
    .pag-links a, .pag-links span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 .5rem;
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: .8rem; font-weight: 600; color: var(--text-secondary);
        text-decoration: none; transition: all .15s; background: var(--surface);
    }
    .pag-links a:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-subtle); }
    .pag-links span.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pag-links span.disabled { opacity: .4; cursor: not-allowed; }
</style>
@endpush

@section('content')

@php
$monthNames = [
    1=>'January',2=>'February',3=>'March',4=>'April',
    5=>'May',6=>'June',7=>'July',8=>'August',
    9=>'September',10=>'October',11=>'November',12=>'December',
];
$currentYear = \Carbon\Carbon::now()->year;
@endphp

<h1 class="page-title">Attendance History</h1>
<p class="page-subtitle">{{ $rangeLabel }} &mdash; Your attendance records and working hours summary.</p>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card" style="--kpi-color:#3b82f6;">
        <div class="kpi-icon"><span class="material-symbols-outlined">calendar_month</span></div>
        <div>
            <div class="kpi-val">{{ $kpi['work_days'] }}</div>
            <div class="kpi-lbl">Working Days</div>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-color:#16a34a;">
        <div class="kpi-icon"><span class="material-symbols-outlined">check_circle</span></div>
        <div>
            <div class="kpi-val">{{ $kpi['present'] }}</div>
            <div class="kpi-lbl">Present Days</div>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-color:#dc2626;">
        <div class="kpi-icon"><span class="material-symbols-outlined">cancel</span></div>
        <div>
            <div class="kpi-val">{{ $kpi['absent'] }}</div>
            <div class="kpi-lbl">Absent Days</div>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-color:#7c3aed;">
        <div class="kpi-icon"><span class="material-symbols-outlined">schedule</span></div>
        <div>
            <div class="kpi-val">{{ $kpi['hours_fmt'] }}</div>
            <div class="kpi-lbl">Total Hours</div>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-color:#ea580c;">
        <div class="kpi-icon"><span class="material-symbols-outlined">timer_off</span></div>
        <div>
            <div class="kpi-val">{{ $kpi['insuff_days'] }}</div>
            <div class="kpi-lbl">Insufficient Hrs Days</div>
        </div>
    </div>
</div>

@if($kpi['missed'] > 0)
<div style="display:flex;align-items:center;gap:.6rem;background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius-md);padding:.75rem 1.1rem;margin-bottom:1.25rem;font-size:.875rem;color:#b91c1c;">
    <span class="material-symbols-outlined" style="font-size:1.2rem;font-variation-settings:'FILL' 1;flex-shrink:0;">warning</span>
    <span>You have <strong>{{ $kpi['missed'] }}</strong> day{{ $kpi['missed'] !== 1 ? 's' : '' }} with a missed punch-out.
    Please <a href="{{ route($regularizationCreateRoute, ['request_type' => 'missed_punch_out']) }}" style="color:#b91c1c;font-weight:700;">apply for regularization</a> to correct these records.</span>
</div>
@endif

{{-- Filters --}}
<div class="filter-card">
    <div class="filter-header">
        <span class="material-symbols-outlined">filter_list</span>
        <h6>Filter</h6>
    </div>
    <div class="filter-body">
        <form method="GET" action="{{ route('attendance.history') }}" id="filterForm">

            {{-- Tab switch --}}
            <div class="filter-tabs" role="group">
                <button type="button" class="filter-tab {{ $filterType !== 'range' ? 'active' : '' }}"
                        onclick="switchTab('month')">By Month</button>
                <button type="button" class="filter-tab {{ $filterType === 'range' ? 'active' : '' }}"
                        onclick="switchTab('range')">Custom Range</button>
            </div>
            <input type="hidden" name="filter_type" id="filterType" value="{{ $filterType }}">

            {{-- Month view --}}
            <div id="monthPanel" class="{{ $filterType === 'range' ? 'd-none' : '' }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Year</label>
                        <select name="year" class="filter-select" style="width:100px;">
                            @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                                <option value="{{ $y }}" {{ $selYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Month</label>
                        <select name="month" class="filter-select" style="width:140px;">
                            @foreach($monthNames as $num => $name)
                                <option value="{{ $num }}" {{ $selMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-apply">
                        <span class="material-symbols-outlined">search</span> Apply
                    </button>
                </div>
            </div>

            {{-- Range view --}}
            <div id="rangePanel" class="{{ $filterType !== 'range' ? 'd-none' : '' }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">From</label>
                        <input type="date" name="date_from" class="filter-input"
                               value="{{ $dateFrom }}" style="width:160px;">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">To</label>
                        <input type="date" name="date_to" class="filter-input"
                               value="{{ $dateTo }}" style="width:160px;">
                    </div>
                    <button type="submit" class="btn-apply">
                        <span class="material-symbols-outlined">search</span> Apply
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-card-header">
        <div class="table-card-header-left">
            <span class="material-symbols-outlined">table_rows</span>
            <h6>Attendance Records</h6>
            <span class="range-pill">{{ $rangeLabel }}</span>
        </div>
        <div class="export-group">
            <a href="{{ route('attendance.export', 'pdf') }}?{{ http_build_query(request()->except('page')) }}"
               target="_blank" class="btn-export btn-export-pdf">
                <span class="material-symbols-outlined">picture_as_pdf</span> PDF
            </a>
            <a href="{{ route('attendance.export', 'csv') }}?{{ http_build_query(request()->except('page')) }}"
               class="btn-export btn-export-csv">
                <span class="material-symbols-outlined">download</span> CSV
            </a>
        </div>
    </div>

    @if($paginator->isEmpty())
        <div class="table-empty">
            <span class="material-symbols-outlined">event_busy</span>
            No attendance records found for this period.
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Punch In Location</th>
                        <th>Punch Out Location</th>
                        <th>Work Hours</th>
                        <th>Hours Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paginator->items() as $row)
                    @php
                        $rowClass = match($row['row_status']) {
                            'sunday'                  => 'row-sunday',
                            'holiday'                 => 'row-holiday',
                            'absent'                  => 'row-absent',
                            'future'                  => 'row-future',
                            'missed_punch_out'        => 'row-missed_punch_out',
                            'pending_regularization'  => 'row-pending_regularization',
                            default                   => '',
                        };
                        $statusBadge = match($row['row_status']) {
                            'present'                => ['class' => 'bs-present',                'label' => 'Present'],
                            'absent'                 => ['class' => 'bs-absent',                 'label' => 'Absent'],
                            'holiday'                => ['class' => 'bs-holiday',                'label' => $row['holiday_name'] ?? 'Holiday'],
                            'sunday'                 => ['class' => 'bs-sunday',                 'label' => 'Sunday'],
                            'future'                 => ['class' => 'bs-future',                 'label' => 'Upcoming'],
                            'leave'                  => ['class' => 'bs-leave',                  'label' => 'Leave'],
                            'half_day'               => ['class' => 'bs-half_day',               'label' => 'Half Day'],
                            'missed_punch_out'       => ['class' => 'bs-missed_punch_out',       'label' => 'Punch-Out Missed'],
                            'pending_regularization' => ['class' => 'bs-pending_regularization', 'label' => 'Regularization Pending'],
                            default                  => ['class' => 'bs-absent',                 'label' => ucfirst(str_replace('_', ' ', $row['row_status']))],
                        };
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="td-date">
                            <div class="date-num">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</div>
                            <div class="date-day">{{ $row['day'] }}</div>
                        </td>
                        <td>
                            <span class="badge-status {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                            @if($row['row_status'] === 'missed_punch_out')
                                <br>
                                <a href="{{ route($regularizationCreateRoute, ['date' => $row['date'], 'request_type' => 'missed_punch_out']) }}"
                                   class="btn-regularize">
                                    <span class="material-symbols-outlined">edit_calendar</span>Apply Regularization
                                </a>
                            @endif
                        </td>
                        <td class="td-time {{ $row['punch_in'] ? '' : 'empty' }}">
                            {{ $row['punch_in'] ?? '—' }}
                        </td>
                        <td class="td-time {{ $row['punch_out'] ? '' : 'empty' }}">
                            {{ $row['punch_out'] ?? '—' }}
                        </td>
                        <td class="td-loc">
                            @if($row['punch_in_loc'])
                                <span class="loc-text" title="{{ $row['punch_in_loc'] }}">{{ $row['punch_in_loc'] }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="td-loc">
                            @if($row['punch_out_loc'])
                                <span class="loc-text" title="{{ $row['punch_out_loc'] }}">{{ $row['punch_out_loc'] }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="td-hours">
                            {{ $row['hours_fmt'] ?? '—' }}
                        </td>
                        <td>
                            @if($row['hours_status'] === 'sufficient')
                                <span class="badge-hours bh-sufficient">Sufficient</span>
                            @elseif($row['hours_status'] === 'insufficient')
                                <span class="badge-hours bh-insufficient">Insufficient</span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($paginator->hasPages())
        <div class="pagination-wrap">
            <div class="pag-info">
                Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} days
            </div>
            <div class="pag-links">
                {{-- Previous --}}
                @if($paginator->onFirstPage())
                    <span class="disabled">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}">&lsaquo;</a>
                @endif

                {{-- Pages --}}
                @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $pg => $url)
                    @if($pg == $paginator->currentPage())
                        <span class="active">{{ $pg }}</span>
                    @else
                        <a href="{{ $url }}">{{ $pg }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}">&rsaquo;</a>
                @else
                    <span class="disabled">&rsaquo;</span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

{{-- Legend --}}
<div style="display:flex;flex-wrap:wrap;gap:.625rem;margin-top:1rem;font-size:.75rem;">
    <span class="badge-status bs-present">Present</span>
    <span class="badge-status bs-absent">Absent</span>
    <span class="badge-status bs-holiday">Holiday</span>
    <span class="badge-status bs-sunday">Sunday</span>
    <span class="badge-status bs-leave">Leave</span>
    <span class="badge-status bs-missed_punch_out">Punch-Out Missed</span>
    <span class="badge-status bs-pending_regularization">Regularization Pending</span>
    <span class="badge-hours bh-insufficient" style="border-radius:999px;padding:.2rem .6rem;">Insufficient Hours (below {{ $minHours }}h)</span>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab) {
    document.getElementById('filterType').value = tab;
    var monthPanel = document.getElementById('monthPanel');
    var rangePanel = document.getElementById('rangePanel');
    var tabs       = document.querySelectorAll('.filter-tab');

    if (tab === 'month') {
        monthPanel.classList.remove('d-none');
        rangePanel.classList.add('d-none');
        tabs[0].classList.add('active');
        tabs[1].classList.remove('active');
    } else {
        rangePanel.classList.remove('d-none');
        monthPanel.classList.add('d-none');
        tabs[1].classList.add('active');
        tabs[0].classList.remove('active');
    }
}
</script>
@endpush
