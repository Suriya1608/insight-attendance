@extends('layouts.app')

@section('title', 'Team Attendance History')

@push('styles')
<style>
    /* ── Stats ──────────────────────────────────────────────────────────────── */
    .att-stats { display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:1100px){.att-stats{grid-template-columns:repeat(3,1fr);}}
    @media(max-width:640px) {.att-stats{grid-template-columns:repeat(2,1fr);}}
    .stat-box { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);padding:1rem 1.125rem;display:flex;align-items:center;gap:.875rem; }
    .stat-box-icon { width:40px;height:40px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#fff; }
    .stat-box-icon .material-symbols-outlined { font-size:20px;font-variation-settings:'FILL' 1; }
    .stat-val { font-size:1.5rem;font-weight:800;letter-spacing:-.04em;line-height:1;color:var(--text-main); }
    .stat-lbl { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:.2rem; }

    /* ── Card ───────────────────────────────────────────────────────────────── */
    .card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm); }
    .card-header { padding:.875rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap; }
    .card-header h5 { font-size:.9rem;font-weight:700;margin:0;color:var(--text-main); }
    .card-header-meta { font-size:.8rem;color:var(--text-muted); }

    /* ── Filter bar ─────────────────────────────────────────────────────────── */
    .filter-bar { display:flex;gap:.625rem;flex-wrap:wrap;padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:#fafbfc;align-items:center; }
    .filter-bar select,
    .filter-bar input[type=date] { font-size:.8125rem;padding:.375rem .625rem;border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-main);background:var(--surface);height:34px; }
    .filter-bar select:focus,
    .filter-bar input[type=date]:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .btn-filter { height:34px;padding:0 .875rem;font-size:.8125rem;font-weight:600;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;transition:all .15s;text-decoration:none;white-space:nowrap; }
    .btn-filter:hover { background:var(--bg-light);color:var(--text-main); }
    .btn-filter.active { background:var(--primary-subtle);border-color:var(--primary);color:var(--primary); }
    .filter-sep { width:1px;height:22px;background:var(--border);flex-shrink:0; }
    .filter-label { font-size:.75rem;font-weight:600;color:var(--text-muted);white-space:nowrap; }

    /* ── Table ──────────────────────────────────────────────────────────────── */
    .att-table { width:100%;border-collapse:collapse;font-size:.8375rem; }
    .att-table th { padding:.625rem 1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:2px solid var(--border);background:#f8fafc;white-space:nowrap; }
    .att-table td { padding:.75rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle; }
    .att-table tr:last-child td { border-bottom:none; }
    .att-table tr:hover td { background:#f8fafc; }

    .emp-name { font-weight:600;color:var(--text-main);white-space:nowrap; }
    .emp-code { font-size:.75rem;color:var(--text-muted);margin-top:.1rem; }
    .loc-text { font-size:.775rem;color:var(--text-secondary);max-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

    /* ── Status badges ──────────────────────────────────────────────────────── */
    .sts { display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .6rem;border-radius:999px;font-size:.75rem;font-weight:600;white-space:nowrap; }
    .sts .material-symbols-outlined { font-size:.85rem; }
    .sts-present               { background:#f0fdf4;color:#15803d; }
    .sts-absent                { background:#fff1f2;color:#dc2626; }
    .sts-leave                 { background:#fff7ed;color:#c2410c; }
    .sts-half_day              { background:#fefce8;color:#92400e; }
    .sts-holiday               { background:#eff6ff;color:#1d4ed8; }
    .sts-sunday                { background:#f1f5f9;color:#475569; }
    .sts-missed_punch_out      { background:#fee2e2;color:#b91c1c; }
    .sts-pending_regularization{ background:#fef3c7;color:#92400e; }

    .hrs-badge { display:inline-block;padding:.2rem .55rem;border-radius:4px;font-size:.7rem;font-weight:700;letter-spacing:.03em;white-space:nowrap; }
    .hrs-sufficient   { background:#dcfce7;color:#15803d; }
    .hrs-insufficient { background:#fee2e2;color:#b91c1c; }

    /* ── Misc ───────────────────────────────────────────────────────────────── */
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.875rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-primary .material-symbols-outlined { font-size:1.1rem; }

    .empty-state { text-align:center;padding:3rem 1rem;color:var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size:3rem;display:block;margin-bottom:.75rem;opacity:.35; }
    .empty-state p { font-size:.9rem;margin:0; }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Team Attendance History</h1>
    <p class="page-subtitle" style="margin-bottom:0;">Attendance records for all employees reporting to you.</p>
</div>

{{-- ── KPI Cards ──────────────────────────────────────────────────────────────── --}}
<div class="att-stats">
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#3b82f6;">
            <span class="material-symbols-outlined">groups</span>
        </div>
        <div>
            <div class="stat-val">{{ $kpi['total'] }}</div>
            <div class="stat-lbl">Team Members</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#22c55e;">
            <span class="material-symbols-outlined">check_circle</span>
        </div>
        <div>
            <div class="stat-val">{{ $kpi['present'] }}</div>
            <div class="stat-lbl">Present Today</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#ef4444;">
            <span class="material-symbols-outlined">cancel</span>
        </div>
        <div>
            <div class="stat-val">{{ $kpi['absent'] }}</div>
            <div class="stat-lbl">Absent Today</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#f59e0b;">
            <span class="material-symbols-outlined">event_busy</span>
        </div>
        <div>
            <div class="stat-val">{{ $kpi['leave'] }}</div>
            <div class="stat-lbl">On Leave Today</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#8b5cf6;">
            <span class="material-symbols-outlined">timer_off</span>
        </div>
        <div>
            <div class="stat-val">{{ $kpi['insuff'] }}</div>
            <div class="stat-lbl">Insufficient Hrs</div>
        </div>
    </div>
</div>

{{-- ── Attendance Table ─────────────────────────────────────────────────────────── --}}
<div class="card">

    {{-- Card header --}}
    <div class="card-header">
        <h5>
            <span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle;margin-right:.3rem;">calendar_month</span>
            {{ $rangeLabel }}
        </h5>
        <span class="card-header-meta">{{ $records->total() }} record{{ $records->total() !== 1 ? 's' : '' }}</span>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('manager.team-attendance.index') }}" id="filterForm">
        <input type="hidden" name="filter_type" id="filterTypeInput" value="{{ $filterType }}">

        <div class="filter-bar">

            {{-- Employee --}}
            <select name="employee_id">
                <option value="">All Employees</option>
                @foreach($teamMembers as $m)
                    @php $encId = \App\Helpers\IdCrypt::encode($m->id); @endphp
                    <option value="{{ $encId }}" {{ request('employee_id') === $encId ? 'selected' : '' }}>
                        {{ $m->name }}
                    </option>
                @endforeach
            </select>

            {{-- Department --}}
            <select name="department">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    @php $encDeptId = \App\Helpers\IdCrypt::encode($dept->id); @endphp
                    <option value="{{ $encDeptId }}" {{ request('department') === $encDeptId ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            {{-- Status --}}
            <select name="status">
                <option value="">All Status</option>
                <option value="present"               {{ request('status') === 'present'               ? 'selected' : '' }}>Present</option>
                <option value="absent"                {{ request('status') === 'absent'                ? 'selected' : '' }}>Absent</option>
                <option value="leave"                 {{ request('status') === 'leave'                 ? 'selected' : '' }}>Leave</option>
                <option value="half_day"              {{ request('status') === 'half_day'              ? 'selected' : '' }}>Half Day</option>
                <option value="holiday"               {{ request('status') === 'holiday'               ? 'selected' : '' }}>Holiday</option>
                <option value="missed_punch_out"      {{ request('status') === 'missed_punch_out'      ? 'selected' : '' }}>Punch-Out Missed</option>
                <option value="pending_regularization"{{ request('status') === 'pending_regularization'? 'selected' : '' }}>Regularization Pending</option>
            </select>

            <div class="filter-sep"></div>

            {{-- Date mode toggle --}}
            @if($filterType === 'range')
                <span class="filter-label">From</span>
                <input type="date" name="date_from" value="{{ $dateFrom }}">
                <span class="filter-label">To</span>
                <input type="date" name="date_to" value="{{ $dateTo }}">
                <button type="button" class="btn-filter" onclick="switchDateMode('month')">
                    <span class="material-symbols-outlined" style="font-size:.9rem;">calendar_view_month</span>Month
                </button>
            @else
                <select name="month">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selMonth === $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <select name="year">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ $selYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="button" class="btn-filter" onclick="switchDateMode('range')">
                    <span class="material-symbols-outlined" style="font-size:.9rem;">date_range</span>Date Range
                </button>
            @endif

            <div class="filter-sep"></div>

            <button type="submit" class="btn-filter active">
                <span class="material-symbols-outlined" style="font-size:.95rem;">filter_list</span>
                Filter
            </button>

            @if(request()->hasAny(['employee_id', 'department', 'status', 'date_from', 'date_to']) || request('month') || request('year'))
                <a href="{{ route('manager.team-attendance.index') }}" class="btn-filter">
                    <span class="material-symbols-outlined" style="font-size:.9rem;">close</span>Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table class="att-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Punch In</th>
                    <th>Punch Out</th>
                    <th>Punch In Location</th>
                    <th>Punch Out Location</th>
                    <th>Working Hours</th>
                    <th>Hours Status</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $i => $att)
                    <tr>
                        <td style="color:var(--text-muted);font-size:.8rem;">{{ $records->firstItem() + $i }}</td>

                        {{-- Employee --}}
                        <td>
                            <div class="emp-name">{{ $att->user->name }}</div>
                            <div class="emp-code">{{ $att->user->employee_code }}</div>
                        </td>

                        {{-- Department --}}
                        <td style="font-size:.8125rem;color:var(--text-secondary);">
                            {{ $att->user->department?->name ?? '—' }}
                        </td>

                        {{-- Date --}}
                        <td style="white-space:nowrap;font-size:.8125rem;">
                            <div style="font-weight:600;">{{ $att->date->format('d M Y') }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">{{ $att->date->format('l') }}</div>
                        </td>

                        {{-- Punch In --}}
                        <td style="font-size:.8125rem;white-space:nowrap;">
                            {{ $att->punch_in ? substr((string) $att->punch_in, 0, 5) : '—' }}
                        </td>

                        {{-- Punch Out --}}
                        <td style="font-size:.8125rem;white-space:nowrap;">
                            {{ $att->punch_out ? substr((string) $att->punch_out, 0, 5) : '—' }}
                        </td>

                        {{-- Punch In Location --}}
                        <td>
                            @if($att->punch_in_loc)
                                <span class="loc-text" title="{{ $att->punch_in_loc }}">{{ $att->punch_in_loc }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>

                        {{-- Punch Out Location --}}
                        <td>
                            @if($att->punch_out_loc)
                                <span class="loc-text" title="{{ $att->punch_out_loc }}">{{ $att->punch_out_loc }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>

                        {{-- Working Hours --}}
                        <td style="font-size:.8125rem;white-space:nowrap;">
                            @if($att->effective_fmt)
                                <span style="font-weight:600;">{{ $att->effective_fmt }}</span>
                                @if($att->hours_fmt && $att->hours_fmt !== $att->effective_fmt)
                                    <div style="font-size:.7rem;color:var(--text-muted);">{{ $att->hours_fmt }} work</div>
                                @endif
                            @elseif($att->hours_fmt)
                                <span style="font-weight:600;">{{ $att->hours_fmt }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>

                        {{-- Hours Status --}}
                        <td>
                            @if($att->hours_status === 'sufficient')
                                <span class="hrs-badge hrs-sufficient">Sufficient</span>
                            @elseif($att->hours_status === 'insufficient')
                                <span class="hrs-badge hrs-insufficient">Insufficient</span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="sts sts-{{ $att->status }}">
                                @if($att->status === 'present')
                                    <span class="material-symbols-outlined">check_circle</span>Present
                                @elseif($att->status === 'absent')
                                    <span class="material-symbols-outlined">cancel</span>Absent
                                @elseif($att->status === 'leave')
                                    <span class="material-symbols-outlined">event_busy</span>Leave
                                @elseif($att->status === 'half_day')
                                    <span class="material-symbols-outlined">contrast</span>Half Day
                                @elseif($att->status === 'holiday')
                                    <span class="material-symbols-outlined">celebration</span>Holiday
                                @elseif($att->status === 'sunday')
                                    <span class="material-symbols-outlined">weekend</span>Sunday
                                @elseif($att->status === 'missed_punch_out')
                                    <span class="material-symbols-outlined">timer_off</span>Punch-Out Missed
                                @elseif($att->status === 'pending_regularization')
                                    <span class="material-symbols-outlined">pending</span>Regularization Pending
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <span class="material-symbols-outlined">event_note</span>
                                <p>No attendance records found for the selected filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($records->hasPages())
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            {{ $records->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function switchDateMode(mode) {
    document.getElementById('filterTypeInput').value = mode;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
