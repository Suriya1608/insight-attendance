@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    /* ── Welcome banner ─────────────────────────────────────────────────── */
    .welcome-banner {
        background: linear-gradient(135deg, #0c1526 0%, #1e3a5f 50%, #0f172a 100%);
        border-radius: var(--radius-lg);
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(19,127,236,.2) 0%, transparent 70%);
        pointer-events: none;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -80px; left: 30%;
        width: 280px; height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .welcome-content { position: relative; z-index: 1; }
    .welcome-greeting {
        font-size: .8125rem;
        font-weight: 500;
        color: rgba(255,255,255,.5);
        margin-bottom: .25rem;
        letter-spacing: .02em;
    }
    .welcome-name {
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.025em;
        margin-bottom: .5rem;
    }
    .welcome-meta {
        font-size: .8125rem;
        color: rgba(255,255,255,.45);
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .welcome-meta .material-symbols-outlined { font-size: 1rem; }
    .welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        padding: .3125rem .875rem;
        background: rgba(19,127,236,.25);
        border: 1px solid rgba(19,127,236,.35);
        border-radius: 999px;
        color: #93c5fd;
        font-size: .75rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    .welcome-badge .material-symbols-outlined { font-size: .9375rem; }

    /* ── Stats grid ─────────────────────────────────────────────────────── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .stats-grid-2 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 1100px) {
        .stats-grid, .stats-grid-2 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px)  {
        .stats-grid, .stats-grid-2 { grid-template-columns: 1fr; }
    }

    /* ── Quick actions ──────────────────────────────────────────────────── */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    .section-title {
        font-size: .9375rem;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: -.01em;
    }
    .section-link {
        font-size: .8125rem;
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }
    .section-link:hover { text-decoration: underline; }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .875rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 1100px) { .quick-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px)  { .quick-grid { grid-template-columns: repeat(2, 1fr); } }

    .quick-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        text-decoration: none;
        color: var(--text-main);
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .quick-card:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle), var(--shadow-sm);
        transform: translateY(-1px);
        color: var(--text-main);
    }
    .quick-icon {
        width: 2.5rem; height: 2.5rem;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
    }
    .quick-icon .material-symbols-outlined { font-size: 1.375rem; }
    .quick-label { font-size: .875rem; font-weight: 600; color: var(--text-main); }
    .quick-desc  { font-size: .75rem; color: var(--text-secondary); margin-top: -.375rem; }

    /* ── Info card ──────────────────────────────────────────────────────── */
    .info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .info-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .875rem;
    }
    .info-card-title {
        font-size: .9375rem;
        font-weight: 700;
        color: var(--text-main);
    }

    /* ── Two-column layout ──────────────────────────────────────────────── */
    .dash-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 900px) { .dash-row { grid-template-columns: 1fr; } }

    .dash-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 1100px) { .dash-row-3 { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 700px) { .dash-row-3 { grid-template-columns: 1fr; } }

    /* ── Attendance table ───────────────────────────────────────────────── */
    .mini-table { width: 100%; border-collapse: collapse; }
    .mini-table th {
        font-size: .6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        padding: .5rem .75rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .mini-table td {
        font-size: .8125rem;
        color: var(--text-main);
        padding: .625rem .75rem;
        border-bottom: 1px solid var(--border-light, var(--border));
        vertical-align: middle;
    }
    .mini-table tr:last-child td { border-bottom: none; }
    .mini-table tr:hover td { background: var(--surface-hover, var(--primary-subtle)); }

    /* ── Status badge ───────────────────────────────────────────────────── */
    .badge-present  { background: rgba(22,163,74,.1);  color: #16a34a; padding: .15rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
    .badge-absent   { background: rgba(239,68,68,.1);  color: #dc2626; padding: .15rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
    .badge-leave    { background: rgba(234,88,12,.1);  color: #ea580c; padding: .15rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
    .badge-pending  { background: rgba(234,179,8,.1);  color: #b45309; padding: .15rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
    .badge-approved_l1 { background: rgba(99,102,241,.1); color: #6366f1; padding: .15rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }

    /* ── Leave request row ──────────────────────────────────────────────── */
    .leave-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 0;
        border-bottom: 1px solid var(--border-light, var(--border));
    }
    .leave-row:last-child { border-bottom: none; }
    .leave-avatar {
        width: 2rem; height: 2rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .6875rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
        background: var(--primary);
    }
    .leave-info { flex: 1; min-width: 0; }
    .leave-name { font-size: .8125rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .leave-meta { font-size: .75rem; color: var(--text-secondary); }

    /* ── Dept progress bar ──────────────────────────────────────────────── */
    .dept-row { display: flex; align-items: center; gap: .75rem; margin-bottom: .875rem; }
    .dept-row:last-child { margin-bottom: 0; }
    .dept-name { font-size: .8125rem; font-weight: 500; color: var(--text-main); width: 130px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dept-bar-wrap { flex: 1; height: 6px; background: var(--border); border-radius: 999px; overflow: hidden; }
    .dept-bar { height: 100%; background: var(--primary); border-radius: 999px; transition: width .4s; }
    .dept-count { font-size: .75rem; color: var(--text-secondary); white-space: nowrap; }

    /* ── Holiday list ───────────────────────────────────────────────────── */
    .holiday-item {
        display: flex;
        align-items: center;
        gap: .875rem;
        padding: .625rem 0;
        border-bottom: 1px solid var(--border-light, var(--border));
    }
    .holiday-item:last-child { border-bottom: none; }
    .holiday-date-box {
        width: 2.5rem;
        text-align: center;
        flex-shrink: 0;
    }
    .holiday-day  { font-size: 1.125rem; font-weight: 800; color: var(--primary); line-height: 1; }
    .holiday-mon  { font-size: .6875rem; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
    .holiday-name { font-size: .8125rem; font-weight: 600; color: var(--text-main); }
    .holiday-dow  { font-size: .75rem; color: var(--text-secondary); }

    /* ── Employee tile ──────────────────────────────────────────────────── */
    .emp-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .625rem;
    }
    @media (max-width: 700px) { .emp-grid { grid-template-columns: repeat(2, 1fr); } }
    .emp-tile {
        display: flex;
        align-items: center;
        gap: .625rem;
        padding: .625rem;
        border-radius: var(--radius-sm);
        background: var(--bg);
        border: 1px solid var(--border);
        text-decoration: none;
        color: var(--text-main);
        transition: border-color .15s;
        overflow: hidden;
    }
    .emp-tile:hover { border-color: var(--primary); color: var(--text-main); }
    .emp-avatar {
        width: 2rem; height: 2rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .6875rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
        background: var(--primary);
    }
    .emp-name { font-size: .8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .emp-dept { font-size: .7rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-muted);
        font-size: .875rem;
    }
    .empty-state .material-symbols-outlined { font-size: 2rem; display: block; margin-bottom: .5rem; opacity: .4; }
</style>
@endpush

@section('content')

{{-- Welcome banner --}}
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-greeting">Good {{ now()->format('G') < 12 ? 'morning' : (now()->format('G') < 17 ? 'afternoon' : 'evening') }},</div>
        <div class="welcome-name">{{ auth()->user()->name }}</div>
        <div class="welcome-meta">
            <span class="material-symbols-outlined">calendar_today</span>
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>
    <div class="welcome-badge">
        <span class="material-symbols-outlined">admin_panel_settings</span>
        Administrator
    </div>
</div>

{{-- Primary KPI row --}}
<div class="stats-grid">
    <div class="stat-card" style="--stat-color:#137fec;">
        <div class="stat-icon" style="background:rgba(19,127,236,.1);">
            <span class="material-symbols-outlined" style="color:#137fec;">group</span>
        </div>
        <div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value" style="color:#137fec;">{{ $totalEmployees }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#16a34a;">
        <div class="stat-icon" style="background:rgba(22,163,74,.1);">
            <span class="material-symbols-outlined" style="color:#16a34a;">check_circle</span>
        </div>
        <div>
            <div class="stat-label">Present Today</div>
            <div class="stat-value" style="color:#16a34a;">{{ $presentToday }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#ea580c;">
        <div class="stat-icon" style="background:rgba(234,88,12,.1);">
            <span class="material-symbols-outlined" style="color:#ea580c;">event_busy</span>
        </div>
        <div>
            <div class="stat-label">On Leave Today</div>
            <div class="stat-value" style="color:#ea580c;">{{ $onLeaveToday }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#dc2626;">
        <div class="stat-icon" style="background:rgba(239,68,68,.1);">
            <span class="material-symbols-outlined" style="color:#dc2626;">person_off</span>
        </div>
        <div>
            <div class="stat-label">Absent Today</div>
            <div class="stat-value" style="color:#dc2626;">{{ $absentToday }}</div>
        </div>
    </div>
</div>

{{-- Secondary KPI row --}}
<div class="stats-grid-2">
    <div class="stat-card" style="--stat-color:#6366f1;">
        <div class="stat-icon" style="background:rgba(99,102,241,.1);">
            <span class="material-symbols-outlined" style="color:#6366f1;">corporate_fare</span>
        </div>
        <div>
            <div class="stat-label">Departments</div>
            <div class="stat-value" style="color:#6366f1;">{{ $totalDepts }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#f59e0b;">
        <div class="stat-icon" style="background:rgba(245,158,11,.1);">
            <span class="material-symbols-outlined" style="color:#f59e0b;">pending_actions</span>
        </div>
        <div>
            <div class="stat-label">Pending Leaves</div>
            <div class="stat-value" style="color:#f59e0b;">{{ $pendingLeaves }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#0891b2;">
        <div class="stat-icon" style="background:rgba(8,145,178,.1);">
            <span class="material-symbols-outlined" style="color:#0891b2;">edit_calendar</span>
        </div>
        <div>
            <div class="stat-label">Pending Regularizations</div>
            <div class="stat-value" style="color:#0891b2;">{{ $pendingRegularizations }}</div>
        </div>
    </div>
    <div class="stat-card" style="--stat-color:#9333ea;">
        <div class="stat-icon" style="background:rgba(147,51,234,.1);">
            <span class="material-symbols-outlined" style="color:#9333ea;">celebration</span>
        </div>
        <div>
            <div class="stat-label">Upcoming Holidays</div>
            <div class="stat-value" style="color:#9333ea;">{{ $upcomingHolidays->count() }}</div>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="section-header">
    <div class="section-title">Quick Actions</div>
</div>
<div class="quick-grid">
    <a href="{{ route('admin.employees.create') }}" class="quick-card">
        <div class="quick-icon" style="background:rgba(22,163,74,.1);">
            <span class="material-symbols-outlined" style="color:#16a34a;">person_add</span>
        </div>
        <div>
            <div class="quick-label">Add Employee</div>
            <div class="quick-desc">Onboard a new hire</div>
        </div>
    </a>
    <a href="{{ route('admin.departments.index') }}" class="quick-card">
        <div class="quick-icon" style="background:rgba(19,127,236,.1);">
            <span class="material-symbols-outlined" style="color:#137fec;">corporate_fare</span>
        </div>
        <div>
            <div class="quick-label">Departments</div>
            <div class="quick-desc">Manage departments</div>
        </div>
    </a>
    <a href="{{ route('admin.holidays.create') }}" class="quick-card">
        <div class="quick-icon" style="background:rgba(234,88,12,.1);">
            <span class="material-symbols-outlined" style="color:#ea580c;">event</span>
        </div>
        <div>
            <div class="quick-label">Add Holiday</div>
            <div class="quick-desc">Schedule a public holiday</div>
        </div>
    </a>
    <a href="{{ route('admin.settings') }}" class="quick-card">
        <div class="quick-icon" style="background:rgba(100,116,139,.1);">
            <span class="material-symbols-outlined" style="color:#64748b;">settings</span>
        </div>
        <div>
            <div class="quick-label">Settings</div>
            <div class="quick-desc">Configure the system</div>
        </div>
    </a>
</div>

{{-- Attendance breakdown + Pending leaves --}}
<div class="dash-row">

    {{-- Department attendance breakdown --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span class="material-symbols-outlined" style="font-size:1.1rem;vertical-align:middle;margin-right:.25rem;color:var(--primary);">bar_chart</span>
                Today's Attendance by Department
            </div>
            <span style="font-size:.75rem;color:var(--text-muted);">{{ now()->format('d M Y') }}</span>
        </div>
        @if($deptBreakdown->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">corporate_fare</span>
                No departments found.
            </div>
        @else
            @foreach($deptBreakdown as $dept)
                @php $pct = $dept->total_employees > 0 ? round(($dept->present_count / $dept->total_employees) * 100) : 0; @endphp
                <div class="dept-row">
                    <div class="dept-name" title="{{ $dept->name }}">{{ $dept->name }}</div>
                    <div class="dept-bar-wrap">
                        <div class="dept-bar" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="dept-count">{{ $dept->present_count }}/{{ $dept->total_employees }}</div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Pending leave requests --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span class="material-symbols-outlined" style="font-size:1.1rem;vertical-align:middle;margin-right:.25rem;color:#f59e0b;">pending_actions</span>
                Pending Leave Requests
            </div>
            @if($pendingLeaveRequests->isNotEmpty())
                <a href="{{ route('leave-requests.index') }}" class="section-link">View all</a>
            @endif
        </div>
        @if($pendingLeaveRequests->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">check_circle</span>
                No pending leave requests.
            </div>
        @else
            @foreach($pendingLeaveRequests as $req)
                <div class="leave-row">
                    <div class="leave-avatar" style="background:{{ ['#137fec','#16a34a','#ea580c','#9333ea','#0891b2','#dc2626'][($loop->index % 6)] }};">
                        {{ strtoupper(substr($req->user->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="leave-info">
                        <div class="leave-name">{{ $req->user->name ?? '—' }}</div>
                        <div class="leave-meta">
                            {{ $req->leave_type ?? 'Leave' }} &middot;
                            {{ \Carbon\Carbon::parse($req->from_date)->format('d M') }}
                            @if($req->from_date !== $req->to_date)
                                – {{ \Carbon\Carbon::parse($req->to_date)->format('d M') }}
                            @endif
                        </div>
                    </div>
                    <span class="badge-{{ $req->status }}">
                        {{ $req->status === 'approved_l1' ? 'L1 Approved' : 'Pending' }}
                    </span>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Upcoming holidays + Recent employees --}}
<div class="dash-row">

    {{-- Upcoming holidays --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span class="material-symbols-outlined" style="font-size:1.1rem;vertical-align:middle;margin-right:.25rem;color:#9333ea;">celebration</span>
                Upcoming Holidays
            </div>
            <a href="{{ route('admin.holidays.index') }}" class="section-link">Manage</a>
        </div>
        @if($upcomingHolidays->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">event_available</span>
                No upcoming holidays.
            </div>
        @else
            @foreach($upcomingHolidays as $holiday)
                <div class="holiday-item">
                    <div class="holiday-date-box">
                        <div class="holiday-day">{{ $holiday->date->format('d') }}</div>
                        <div class="holiday-mon">{{ $holiday->date->format('M') }}</div>
                    </div>
                    <div>
                        <div class="holiday-name">{{ $holiday->name }}</div>
                        <div class="holiday-dow">{{ $holiday->date->format('l') }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Recently added employees --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-title">
                <span class="material-symbols-outlined" style="font-size:1.1rem;vertical-align:middle;margin-right:.25rem;color:#16a34a;">group_add</span>
                Recently Added Employees
            </div>
            <a href="{{ route('admin.employees.index') }}" class="section-link">View all</a>
        </div>
        @if($recentEmployees->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">group</span>
                No employees yet.
            </div>
        @else
            <div class="emp-grid">
                @foreach($recentEmployees as $emp)
                    <a href="{{ route('admin.employees.show', $emp) }}" class="emp-tile">
                        <div class="emp-avatar" style="background:{{ ['#137fec','#16a34a','#ea580c','#9333ea','#0891b2','#f59e0b'][($loop->index % 6)] }};">
                            {{ strtoupper(substr($emp->name ?? '?', 0, 2)) }}
                        </div>
                        <div style="min-width:0;">
                            <div class="emp-name">{{ $emp->name }}</div>
                            <div class="emp-dept">{{ $emp->department->name ?? '—' }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
