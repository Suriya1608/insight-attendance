@extends('layouts.app')

@section('title', 'Manager Dashboard')

@push('styles')
<style>
    /* —— Flash messages ——————————————————————————————————————————————————————— */
    .flash {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem 1rem; border-radius: var(--radius-md);
        font-size: .875rem; font-weight: 500; margin-bottom: 1rem;
        border: 1px solid transparent;
    }
    .flash .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-close {
        margin-left: auto; background: none; border: none; cursor: pointer;
        font-size: 1.1rem; opacity: .6; line-height: 1;
    }
    .flash-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .flash-error   { background: #fff1f2; color: #dc2626; border-color: #fecaca; }
    .flash-warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }

    /* —— Page greeting ———————————————————————————————————————————————————————— */
    .dash-greeting { font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 .2rem; }
    .dash-sub      { font-size: .85rem; color: var(--text-secondary); margin: 0 0 1.5rem; }

    /* —— KPI Cards ———————————————————————————————————————————————————————————— */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        padding: 1rem 1.125rem; display: flex; align-items: center; gap: .875rem;
    }
    .kpi-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; color: #fff;
    }
    .kpi-icon .material-symbols-outlined { font-size: 20px; font-variation-settings: 'FILL' 1; }
    .kpi-val  { font-size: 1.5rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; color: var(--text-main); }
    .kpi-lbl  { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .2rem; }

    /* —— Main dashboard grid —————————————————————————————————————————————————— */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1rem;
        align-items: start;
        margin-bottom: 1rem;
    }
    @media (max-width: 900px) { .dash-grid { grid-template-columns: 1fr; } }

    /* —— Shared card style ———————————————————————————————————————————————————— */
    .d-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: .75rem;
    }
    .d-card-header {
        padding: .75rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #f8fafc; display: flex; align-items: center; gap: .5rem;
    }
    .d-card-header h5 {
        font-size: .88rem; font-weight: 700; margin: 0; color: var(--text-main);
    }
    .d-card-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); }
    .d-card-body { padding: 1.25rem; }

    /* —— Punch Card ——————————————————————————————————————————————————————————— */
    .punch-wrap { text-align: center; padding: 1.75rem 1.25rem; }

    .punch-clock {
        font-size: 3rem; font-weight: 800; letter-spacing: -.04em;
        font-variant-numeric: tabular-nums; font-family: 'JetBrains Mono', monospace;
        line-height: 1; margin-bottom: .375rem;
    }
    .punch-clock.state-none { color: var(--text-muted); }
    .punch-clock.state-in   { color: #16a34a; }
    .punch-clock.state-done { color: #2563eb; }

    .punch-status-label { font-size: .8rem; color: var(--text-muted); margin-bottom: 1.25rem; }

    .punch-meta-row {
        display: flex; justify-content: center; gap: 2rem;
        margin-bottom: 1.25rem;
    }
    .punch-meta-item { text-align: center; }
    .punch-meta-key  { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); }
    .punch-meta-val  { font-size: 1rem; font-weight: 700; color: var(--text-main); font-family: monospace; }

    .btn-punch {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .75rem 2rem; border-radius: 50px; border: none;
        font-size: .9rem; font-weight: 700; cursor: pointer; transition: all .2s;
        letter-spacing: .01em;
    }
    .btn-punch .material-symbols-outlined { font-size: 1.1rem; }
    .btn-punch-in  { background: #22c55e; color: #fff; }
    .btn-punch-in:hover  { background: #16a34a; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34,197,94,.35); }
    .btn-punch-out { background: #ef4444; color: #fff; }
    .btn-punch-out:hover { background: #dc2626; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,.35); }
    .btn-punch:disabled { opacity: .65; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

    .punch-done-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;
        padding: .4rem 1rem; border-radius: 20px; font-size: .82rem; font-weight: 700; margin-bottom: 1rem;
    }
    .punch-done-badge .material-symbols-outlined { font-size: .95rem; }

    .location-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; color: var(--text-muted);
        background: #f1f5f9; border: 1px solid var(--border);
        padding: .2rem .6rem; border-radius: 20px; margin-top: .5rem;
    }
    .location-pill .material-symbols-outlined { font-size: .85rem; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin .8s linear infinite; }

    /* —— Attendance Calendar —————————————————————————————————————————————————— */
    .cal-month-label { font-size: .95rem; font-weight: 700; color: var(--text-main); }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
    .cal-hdr {
        text-align: center; font-size: .65rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-muted); padding: .3rem 0;
    }
    .cal-day {
        aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
        border-radius: 6px; font-size: .78rem; font-weight: 600;
        position: relative; cursor: default;
    }
    .cal-day.empty   { background: transparent; }
    .cal-day.future  { color: var(--text-muted); background: transparent; }
    .cal-day.sunday  { background: #f1f5f9; color: #94a3b8; }
    .cal-day.holiday { background: #dbeafe; color: #1d4ed8; }
    .cal-day.present { background: #dcfce7; color: #15803d; }
    .cal-day.absent  { background: #fee2e2; color: #dc2626; }
    .cal-day.leave   { background: #ede9fe; color: #7c3aed; }
    .cal-day.half_day { background: #fef9c3; color: #854d0e; }
    .cal-day.pending { background: #fef3c7; color: #d97706; }
    .cal-day.pending_regularization { background: #ffedd5; color: #c2410c; }
    .cal-day.today   { outline: 2px solid var(--primary); outline-offset: 1px; font-weight: 800; }

    .cal-legend { display: flex; flex-wrap: wrap; gap: .5rem 1rem; margin-top: 1rem; font-size: .72rem; color: var(--text-secondary); }
    .legend-item { display: flex; align-items: center; gap: .3rem; }
    .legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
    .legend-dot.present { background: #22c55e; }
    .legend-dot.absent  { background: #ef4444; }
    .legend-dot.holiday { background: #3b82f6; }
    .legend-dot.sunday  { background: #94a3b8; }
    .legend-dot.leave   { background: #8b5cf6; }

    /* —— Aside cards (right sidebar) —————————————————————————————————————————— */
    .aside-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1rem;
    }
    .aside-card-header {
        padding: .625rem 1rem; border-bottom: 1px solid var(--border);
        background: #f8fafc; display: flex; align-items: center; gap: .4rem;
    }
    .aside-card-header h6 { font-size: .82rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .aside-card-header .material-symbols-outlined { font-size: 1rem; color: var(--primary); }
    .aside-card-body { padding: .875rem 1rem; }

    .holiday-item { display: flex; align-items: flex-start; gap: .625rem; padding: .5rem 0; border-bottom: 1px solid var(--border); }
    .holiday-item:last-child { border-bottom: none; padding-bottom: 0; }
    .holiday-date-box { min-width: 40px; text-align: center; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: .25rem .3rem; flex-shrink: 0; }
    .holiday-date-day { font-size: .65rem; font-weight: 700; color: #2563eb; text-transform: uppercase; }
    .holiday-date-num { font-size: 1.1rem; font-weight: 800; color: #1d4ed8; line-height: 1; }
    .holiday-name { font-size: .82rem; font-weight: 600; color: var(--text-main); }
    .holiday-meta { font-size: .7rem; color: var(--text-muted); margin-top: .1rem; }
    .badge-nat { display: inline-block; padding: .1rem .4rem; border-radius: 4px; font-size: .65rem; font-weight: 700; background: #dbeafe; color: #1d4ed8; }
    .badge-opt { display: inline-block; padding: .1rem .4rem; border-radius: 4px; font-size: .65rem; font-weight: 700; background: #f3e8ff; color: #7c3aed; }
    .days-left { font-size: .68rem; color: var(--text-muted); }

    .bday-item { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid var(--border); }
    .bday-item:last-child { border-bottom: none; padding-bottom: 0; }
    .bday-avatar {
        width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #f59e0b, #ec4899);
        color: #fff; font-size: .78rem; font-weight: 700;
    }
    .bday-name  { font-size: .82rem; font-weight: 600; color: var(--text-main); }
    .bday-dept  { font-size: .7rem; color: var(--text-muted); }
    .bday-emoji { font-size: .85rem; }

    /* —— Leave blocks —————————————————————————————————————————————————————————— */
    .leave-block {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1rem;
    }
    .leave-block-header {
        display: flex; align-items: center; gap: .5rem;
        padding: .625rem 1rem; border-bottom: 1px solid var(--border); background: #f8fafc;
    }
    .leave-block-header .blk-icon {
        width: 26px; height: 26px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; color: #fff; flex-shrink: 0;
    }
    .leave-block-header h6 { font-size: .78rem; font-weight: 700; color: var(--text-main); margin: 0; text-transform: uppercase; letter-spacing: .04em; }
    .leave-block-header .blk-note { margin-left: auto; font-size: .68rem; color: var(--text-muted); white-space: nowrap; }
    .leave-block-body { padding: .75rem 1rem; }
    .mini-stat-row { display: flex; gap: 0; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; }
    .mini-stat { flex: 1; padding: .625rem .75rem; border-right: 1px solid var(--border); text-align: center; }
    .mini-stat:last-child { border-right: none; }
    .mini-stat-val { font-size: 1.3rem; font-weight: 800; letter-spacing: -.03em; line-height: 1; color: var(--text-main); }
    .mini-stat-lbl { font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .25rem; }
    .val-credited { color: #1d4ed8; }
    .val-used     { color: #dc2626; }
    .val-balance  { color: #16a34a; }
    .stat-note { font-size: .72rem; color: var(--text-muted); margin-top: .5rem; text-align: center; }

    /* —— Insufficient hours modal ——————————————————————————————————————————————— */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0,0,0,.45);
        display: none; align-items: center; justify-content: center; padding: 1rem;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff; border-radius: var(--radius-md);
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
        padding: 1.75rem; max-width: 380px; width: 100%; text-align: center;
    }
    .modal-icon { width: 52px; height: 52px; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; }
    .modal-icon.warning { background: #fef9c3; }
    .modal-icon.warning .material-symbols-outlined { font-size: 1.6rem; color: #d97706; font-variation-settings: 'FILL' 1; }
    .modal-box h4 { font-size: 1.05rem; font-weight: 700; margin: 0 0 .5rem; }
    .modal-box p  { font-size: .875rem; color: var(--text-secondary); margin: 0 0 .5rem; }
    .modal-sub    { font-size: .8rem !important; }
    .modal-actions { display: flex; gap: .75rem; margin-top: 1.25rem; }
    .btn-modal-cancel  { flex: 1; padding: .6rem; border-radius: var(--radius-sm); background: #f1f5f9; border: 1px solid var(--border); font-size: .875rem; font-weight: 600; cursor: pointer; color: var(--text-main); }
    .btn-modal-confirm { flex: 1; padding: .6rem; border-radius: var(--radius-sm); background: #ef4444; border: none; font-size: .875rem; font-weight: 700; cursor: pointer; color: #fff; }
    .btn-modal-confirm:hover { background: #dc2626; }

    /* —— Team Overview Table ———————————————————————————————————————————————————— */
    .team-table-wrap { overflow-x: auto; }
    .team-table {
        width: 100%; border-collapse: collapse; font-size: .82rem;
    }
    .team-table th {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--text-muted);
        padding: .5rem .75rem; text-align: left;
        border-bottom: 2px solid var(--border); white-space: nowrap;
    }
    .team-table td {
        padding: .65rem .75rem; border-bottom: 1px solid var(--border);
        color: var(--text-main); vertical-align: middle;
    }
    .team-table tr:last-child td { border-bottom: none; }
    .team-table tr:hover td { background: #f8fafc; }

    .member-avatar {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-size: .72rem; font-weight: 700;
    }
    .member-name  { font-weight: 600; font-size: .82rem; }
    .member-dept  { font-size: .7rem; color: var(--text-muted); }

    .status-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .55rem; border-radius: 20px;
        font-size: .7rem; font-weight: 700; white-space: nowrap;
    }
    .status-badge .material-symbols-outlined { font-size: .75rem; }
    .badge-present { background: #dcfce7; color: #15803d; }
    .badge-absent  { background: #fee2e2; color: #dc2626; }
    .badge-leave   { background: #ede9fe; color: #7c3aed; }
    .badge-half    { background: #fef9c3; color: #854d0e; }

    .time-val { font-family: monospace; font-size: .82rem; font-weight: 600; }
    .time-dash { color: var(--text-muted); }

    /* —— Tomorrow Leave & Permission widget ———————————————————————————————————— */
    .tlp-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .tlp-table th {
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--text-muted);
        padding: .5rem .875rem; text-align: left;
        border-bottom: 2px solid var(--border); white-space: nowrap;
        background: #f8fafc;
    }
    .tlp-table td { padding: .65rem .875rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .tlp-table tr:last-child td { border-bottom: none; }
    .tlp-table tr:hover td { background: #f8fafc; }

    .tlp-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .6rem; border-radius: 20px;
        font-size: .72rem; font-weight: 700; white-space: nowrap;
    }
    .tlp-badge-cl   { background: #dbeafe; color: #1d4ed8; }
    .tlp-badge-opt  { background: #f3e8ff; color: #7c3aed; }
    .tlp-badge-perm { background: #fef9c3; color: #92400e; }
    .tlp-badge-lop  { background: #fee2e2; color: #dc2626; }
    .tlp-badge-sat  { background: #ecfdf5; color: #065f46; }
    .tlp-empty { text-align: center; padding: 2rem 1rem; color: var(--text-muted); font-size: .85rem; }
    .tlp-empty .material-symbols-outlined { font-size: 2rem; display: block; margin-bottom: .4rem; color: #94a3b8; }
</style>
@endpush

@section('content')

{{-- —— Flash messages ———————————————————————————————————————————————————————— --}}
@foreach(['success', 'error', 'warning'] as $type)
    @if(session($type))
    <div class="flash flash-{{ $type }}">
        <span class="material-symbols-outlined">
            @if($type === 'success') check_circle @elseif($type === 'error') cancel @else warning @endif
        </span>
        {{ session($type) }}
        <button type="button" class="flash-close" aria-label="Dismiss">&times;</button>
    </div>
    @endif
@endforeach

{{-- —— Greeting ——————————————————————————————————————————————————————————————— --}}
<div class="dash-greeting">
    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
    {{ auth()->user()->name }}!
</div>
<p class="dash-sub">{{ now()->format('l, d F Y') }} &mdash; Manager dashboard &amp; team overview.</p>

{{-- —— KPI Cards —————————————————————————————————————————————————————————————— --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#22c55e;">
            <span class="material-symbols-outlined">how_to_reg</span>
        </div>
        <div>
            <div class="kpi-val">{{ $kpi['present'] }}</div>
            <div class="kpi-lbl">My Present Days</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#3b82f6;">
            <span class="material-symbols-outlined">timer</span>
        </div>
        <div>
            <div class="kpi-val">{{ $kpi['work_hours'] }}h</div>
            <div class="kpi-lbl">My Work Hours</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#6366f1;">
            <span class="material-symbols-outlined">groups</span>
        </div>
        <div>
            <div class="kpi-val">{{ $kpi['team_total'] }}</div>
            <div class="kpi-lbl">Team Members</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#10b981;">
            <span class="material-symbols-outlined">group_add</span>
        </div>
        <div>
            <div class="kpi-val">{{ $kpi['team_present'] }}</div>
            <div class="kpi-lbl">Team Present</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ef4444;">
            <span class="material-symbols-outlined">person_off</span>
        </div>
        <div>
            <div class="kpi-val">{{ $kpi['team_absent'] }}</div>
            <div class="kpi-lbl">Team Absent</div>
        </div>
    </div>
</div>

{{-- —— Task Stats ——————————————————————————————————————————————————————————————— --}}
<div style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
        <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;">
            <span class="material-symbols-outlined" style="font-size:1rem;color:var(--primary);">task_alt</span>
            Task Overview
        </div>
        <a href="{{ route('manager.tasks.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none;">View All →</a>
    </div>
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#3b82f6;">
                <span class="material-symbols-outlined">assignment</span>
            </div>
            <div>
                <div class="kpi-val">{{ $taskStats['total'] }}</div>
                <div class="kpi-lbl">Total Tasks</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#64748b;">
                <span class="material-symbols-outlined">radio_button_unchecked</span>
            </div>
            <div>
                <div class="kpi-val">{{ $taskStats['pending'] }}</div>
                <div class="kpi-lbl">Pending</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#22c55e;">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            <div>
                <div class="kpi-val">{{ $taskStats['completed'] }}</div>
                <div class="kpi-lbl">Completed</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#ef4444;">
                <span class="material-symbols-outlined">schedule</span>
            </div>
            <div>
                <div class="kpi-val">{{ $taskStats['overdue'] }}</div>
                <div class="kpi-lbl">Overdue</div>
            </div>
        </div>
    </div>
</div>

{{-- —— Main Dashboard Grid ————————————————————————————————————————————————————— --}}
<div class="dash-grid">

    {{-- —— LEFT COLUMN ———————————————————————————————————————————————————————— --}}
    <div>

        {{-- Punch Card --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="material-symbols-outlined">fingerprint</span>
                <h5>Today's Attendance</h5>
                <span style="margin-left:auto; font-size:.78rem; color:var(--text-muted);">
                    {{ now()->format('D, d M Y') }}
                </span>
            </div>

            {{-- State: Not Punched In --}}
            @if($punchState === 'none')
            <div class="punch-wrap">
                <div class="punch-clock state-none" id="currentClock">--:--:--</div>
                <p class="punch-status-label">You haven't checked in yet today</p>

                <form method="POST" action="{{ route('manager.punch.in') }}" id="punchInForm">
                    @csrf
                    <input type="hidden" name="latitude"          id="inLat">
                    <input type="hidden" name="longitude"         id="inLng">
                    <input type="hidden" name="location_label"    id="inLabel">
                    <input type="hidden" name="formatted_address" id="inFormattedAddress">
                    <input type="hidden" name="suburb"            id="inSuburb">
                    <input type="hidden" name="city"              id="inCity">
                    <input type="hidden" name="state"             id="inState">
                    <input type="hidden" name="country"           id="inCountry">
                    <button type="button" class="btn-punch btn-punch-in" id="punchInBtn">
                        <span class="material-symbols-outlined">login</span>
                        Punch In
                    </button>
                </form>
                <div id="locationStatus" class="location-pill" style="display:none;">
                    <span class="material-symbols-outlined">location_on</span>
                    <span id="locationText">Detecting locationÃ¢â‚¬Â¦</span>
                </div>
            </div>

            {{-- State: Currently Punched In --}}
            @elseif($punchState === 'in')
            <div class="punch-wrap">
                <div class="punch-clock state-in" id="liveTimer">00:00:00</div>
                <p class="punch-status-label">You are currently checked in</p>

                <div class="punch-meta-row">
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Checked In</div>
                        <div class="punch-meta-val">{{ substr($todayAttendance->punch_in, 0, 5) }}</div>
                    </div>
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Location</div>
                        <div class="punch-meta-val" style="font-size:.75rem; max-width:160px; word-break:break-word; line-height:1.3;">
                            {{ $punchInLocation ?: '—' }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('manager.punch.out') }}" id="punchOutForm">
                    @csrf
                    <input type="hidden" name="latitude"          id="outLat">
                    <input type="hidden" name="longitude"         id="outLng">
                    <input type="hidden" name="location_label"    id="outLabel">
                    <input type="hidden" name="formatted_address" id="outFormattedAddress">
                    <input type="hidden" name="suburb"            id="outSuburb">
                    <input type="hidden" name="city"              id="outCity">
                    <input type="hidden" name="state"             id="outState">
                    <input type="hidden" name="country"           id="outCountry">
                    <button type="button" class="btn-punch btn-punch-out" id="punchOutBtn">
                        <span class="material-symbols-outlined">logout</span>
                        Punch Out
                    </button>
                </form>
                <div id="outLocationStatus" class="location-pill" style="display:none;">
                    <span class="material-symbols-outlined">location_on</span>
                    <span id="outLocationText">Detecting locationÃ¢â‚¬Â¦</span>
                </div>
            </div>

            {{-- State: Completed --}}
            @else
            <div class="punch-wrap">
                <div class="punch-clock state-done">{{ $todayAttendance->formatted_work_hours }}</div>
                <p class="punch-status-label">Total work time today</p>

                <div class="punch-done-badge">
                    <span class="material-symbols-outlined">check_circle</span>
                    Attendance completed
                </div>

                <div class="punch-meta-row" style="margin-bottom:0; flex-wrap:wrap; gap:1rem 2rem;">
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Checked In</div>
                        <div class="punch-meta-val">{{ substr($todayAttendance->punch_in, 0, 5) }}</div>
                    </div>
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Checked Out</div>
                        <div class="punch-meta-val">{{ substr($todayAttendance->punch_out, 0, 5) }}</div>
                    </div>
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Punch-In Location</div>
                        <div class="punch-meta-val" style="font-size:.75rem; max-width:200px; word-break:break-word; line-height:1.3;">
                            {{ $punchInLocation ?: '—' }}
                        </div>
                    </div>
                    @if($punchOutLocation)
                    <div class="punch-meta-item">
                        <div class="punch-meta-key">Punch-Out Location</div>
                        <div class="punch-meta-val" style="font-size:.75rem; max-width:200px; word-break:break-word; line-height:1.3;">
                            {{ $punchOutLocation }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Attendance Calendar --}}
        <div class="d-card" id="calCard">
            <div class="d-card-header">
                <span class="material-symbols-outlined">calendar_month</span>
                <h5 class="cal-month-label" id="calMonthLabel">{{ $calMonthLabel }}</h5>
                <div style="margin-left:auto; display:flex; align-items:center; gap:.2rem;">
                    <button type="button" id="calNavPrev"
                            data-year="{{ $calPrev->year }}" data-month="{{ $calPrev->month }}"
                            title="{{ $calPrev->format('F Y') }}"
                            style="display:flex;align-items:center;padding:.15rem .2rem;border-radius:5px;color:var(--text-muted);background:none;border:none;cursor:pointer;">
                        <span class="material-symbols-outlined" style="font-size:1.15rem;">chevron_left</span>
                    </button>
                    <button type="button" id="calNavToday"
                            data-year="{{ now()->year }}" data-month="{{ now()->month }}"
                            style="font-size:.7rem;font-weight:700;color:var(--primary);background:none;border:1px solid var(--border);border-radius:4px;padding:.15rem .45rem;cursor:pointer;white-space:nowrap;">
                        Today
                    </button>
                    <button type="button" id="calNavNext"
                            data-year="{{ $calNext->year }}" data-month="{{ $calNext->month }}"
                            title="{{ $calNext->format('F Y') }}"
                            style="display:flex;align-items:center;padding:.15rem .2rem;border-radius:5px;color:var(--text-muted);background:none;border:none;cursor:pointer;">
                        <span class="material-symbols-outlined" style="font-size:1.15rem;">chevron_right</span>
                    </button>
                </div>
            </div>
            <div class="d-card-body">
                <div class="cal-grid" id="calGrid">
                    @foreach(['S','M','T','W','T','F','S'] as $h)
                        <div class="cal-hdr">{{ $h }}</div>
                    @endforeach
                    @for($i = 0; $i < $firstDayOffs; $i++)
                        <div class="cal-day empty"></div>
                    @endfor
                    @foreach($calendarDays as $d => $data)
                        <div class="cal-day {{ $data['status'] }} {{ $data['isToday'] ? 'today' : '' }}"
                             title="{{ ucfirst($data['status']) }} — {{ $data['date'] }}">
                            {{ $d }}
                        </div>
                    @endforeach
                </div>
                <div class="cal-legend">
                    <div class="legend-item"><div class="legend-dot present"></div> Present</div>
                    <div class="legend-item"><div class="legend-dot absent"></div> Absent</div>
                    <div class="legend-item"><div class="legend-dot holiday"></div> Holiday</div>
                    <div class="legend-item"><div class="legend-dot sunday"></div> Sunday</div>
                    <div class="legend-item"><div class="legend-dot leave"></div> Leave</div>
                </div>
            </div>
        </div>

    </div>{{-- /left --}}

    {{-- —— RIGHT SIDEBAR —————————————————————————————————————————————————————— --}}
    <div>

        {{-- Upcoming Holidays --}}
        <div class="aside-card">
            <div class="aside-card-header">
                <span class="material-symbols-outlined">celebration</span>
                <h6>Upcoming Holidays</h6>
            </div>
            <div class="aside-card-body">
                @forelse($upcomingHolidays as $holiday)
                    @php $daysLeft = now()->startOfDay()->diffInDays($holiday->date->startOfDay(), false); @endphp
                    <div class="holiday-item">
                        <div class="holiday-date-box">
                            <div class="holiday-date-day">{{ $holiday->date->format('M') }}</div>
                            <div class="holiday-date-num">{{ $holiday->date->format('d') }}</div>
                        </div>
                        <div>
                            <div class="holiday-name">{{ $holiday->name }}</div>
                            <div class="holiday-meta">
                                @if($holiday->type === 'national')
                                    <span class="badge-nat">National</span>
                                @else
                                    <span class="badge-opt">Optional</span>
                                @endif
                                <span class="days-left" style="margin-left:.25rem;">
                                    @if($daysLeft === 0) Today
                                    @elseif($daysLeft === 1) Tomorrow
                                    @else in {{ $daysLeft }} days
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="font-size:.8rem; color:var(--text-muted); margin:0; text-align:center; padding:.5rem 0;">
                        No upcoming holidays scheduled.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Today's Birthdays --}}
        <div class="aside-card">
            <div class="aside-card-header">
                <span class="material-symbols-outlined" style="color:#ec4899;">cake</span>
                <h6>Birthday Today</h6>
            </div>
            <div class="aside-card-body">
                @forelse($birthdays as $person)
                    <div class="bday-item">
                        <div class="bday-avatar">
                            {{ strtoupper(substr($person->name, 0, 1)) }}{{ strtoupper(substr(strrchr($person->name, ' ') ?: $person->name, 1, 1)) }}
                        </div>
                        <div>
                            <div class="bday-name">{{ $person->name }} <span class="bday-emoji">Ã°Å¸Å½â€°</span></div>
                            <div class="bday-dept">{{ $person->department?->name ?? ucfirst($person->role) }}</div>
                        </div>
                    </div>
                @empty
                    <p style="font-size:.8rem; color:var(--text-muted); margin:0; text-align:center; padding:.5rem 0;">
                        No birthdays today.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Casual Leave --}}
        <div class="leave-block">
            <div class="leave-block-header">
                <div class="blk-icon" style="background:#3b82f6;">
                    <span class="material-symbols-outlined" style="font-size:12px;font-variation-settings:'FILL' 1">event_note</span>
                </div>
                <h6>Casual Leave (CL)</h6>
                <span class="blk-note">{{ now()->year }}</span>
            </div>
            <div class="leave-block-body">
                <div class="mini-stat-row">
                    <div class="mini-stat"><div class="mini-stat-val val-credited">{{ number_format($leave['cl_credited'], 0) }}</div><div class="mini-stat-lbl">Credited</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-used">{{ number_format($leave['cl_used'], 0) }}</div><div class="mini-stat-lbl">Used</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-balance">{{ number_format($leave['cl_balance'], 0) }}</div><div class="mini-stat-lbl">Balance</div></div>
                </div>
                <p class="stat-note">Resets December&nbsp;31st &mdash; unused balance carries forward monthly.</p>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="leave-block">
            <div class="leave-block-header">
                <div class="blk-icon" style="background:#8b5cf6;">
                    <span class="material-symbols-outlined" style="font-size:12px;font-variation-settings:'FILL' 1">schedule</span>
                </div>
                <h6>Permissions</h6>
                <span class="blk-note">{{ now()->format('M Y') }}</span>
            </div>
            <div class="leave-block-body">
                <div class="mini-stat-row">
                    <div class="mini-stat"><div class="mini-stat-val val-credited">{{ number_format($leave['perm_credited'], 0) }}</div><div class="mini-stat-lbl">Credited</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-used">{{ number_format($leave['perm_used'], 0) }}</div><div class="mini-stat-lbl">Used</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-balance">{{ number_format($leave['perm_balance'], 0) }}</div><div class="mini-stat-lbl">Balance</div></div>
                </div>
                <p class="stat-note">2 hrs each &mdash; expires at month end.</p>
            </div>
        </div>

        {{-- Saturday Leave --}}
        @if($leave['has_saturday'])
        <div class="leave-block">
            <div class="leave-block-header">
                <div class="blk-icon" style="background:#f59e0b;">
                    <span class="material-symbols-outlined" style="font-size:12px;font-variation-settings:'FILL' 1">weekend</span>
                </div>
                <h6>Saturday Leave</h6>
                <span class="blk-note">{{ now()->format('M Y') }}</span>
            </div>
            <div class="leave-block-body">
                <div class="mini-stat-row">
                    <div class="mini-stat"><div class="mini-stat-val val-credited">{{ number_format($leave['sat_credited'], 0) }}</div><div class="mini-stat-lbl">Credited</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-used">{{ number_format($leave['sat_used'], 0) }}</div><div class="mini-stat-lbl">Used</div></div>
                    <div class="mini-stat"><div class="mini-stat-val val-balance">{{ number_format($leave['sat_balance'], 0) }}</div><div class="mini-stat-lbl">Balance</div></div>
                </div>
                <p class="stat-note">HR Recruiting only &mdash; lapses at month end.</p>
            </div>
        </div>
        @endif

        {{-- —— Optional Holidays ——————————————————————————————————————————— --}}
        @if($optStats['max'] > 0)
        <div class="leave-block" style="border-color:#7c3aed20;">
            <div class="leave-block-header">
                <div class="blk-icon" style="background:#7c3aed;">
                    <span class="material-symbols-outlined" style="font-size:12px;font-variation-settings:'FILL' 1">beach_access</span>
                </div>
                <h6>Optional Holidays</h6>
                <span class="blk-note">{{ now()->year }}</span>
            </div>
            <div class="leave-block-body">
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val" style="color:#7c3aed;">{{ $optStats['max'] }}</div>
                        <div class="mini-stat-lbl">Allowed</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val val-used">{{ $optStats['used'] }}</div>
                        <div class="mini-stat-lbl">Selected</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val {{ $optStats['remaining'] > 0 ? 'val-balance' : '' }}" style="{{ $optStats['remaining'] === 0 ? 'color:#94a3b8' : '' }}">{{ $optStats['remaining'] }}</div>
                        <div class="mini-stat-lbl">Remaining</div>
                    </div>
                </div>
                <p class="stat-note"><a href="{{ route('optional-holidays.index') }}" style="color:var(--primary);text-decoration:none;font-weight:600;">Select optional holidays →</a></p>
            </div>
        </div>
        @endif

    </div>{{-- /right --}}

</div>{{-- /dash-grid --}}

{{-- —— Tomorrow Leave & Permission ———————————————————————————————————————————— --}}
<div class="d-card" style="margin-bottom:1rem;">
    <div class="d-card-header">
        <span class="material-symbols-outlined" style="color:#f59e0b;">event_upcoming</span>
        <h5>Tomorrow Leave &amp; Permission</h5>
        <span style="margin-left:auto;display:flex;align-items:center;gap:.5rem;">
            @if($tomorrowAbsences->isNotEmpty())
            <span style="background:#fef3c7;color:#92400e;font-size:.72rem;font-weight:700;padding:.15rem .55rem;border-radius:20px;border:1px solid #fde68a;">
                {{ $tomorrowAbsences->count() }} employee{{ $tomorrowAbsences->count() !== 1 ? 's' : '' }}
            </span>
            @endif
            <span style="font-size:.78rem;color:var(--text-muted);">{{ now()->addDay()->format('D, d M Y') }}</span>
        </span>
    </div>
    @if($tomorrowAbsences->isEmpty())
        <div class="tlp-empty">
            <span class="material-symbols-outlined">event_available</span>
            All team members are available tomorrow.
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="tlp-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Time / Duration</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tomorrowAbsences as $row)
                @php
                    $badgeClass = match($row['type']) {
                        'CL'               => 'tlp-badge-cl',
                        'Optional Holiday' => 'tlp-badge-opt',
                        'Permission'       => 'tlp-badge-perm',
                        'Lop'              => 'tlp-badge-lop',
                        'Saturday Leave'   => 'tlp-badge-sat',
                        default            => 'tlp-badge-cl',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.625rem;">
                            <div class="member-avatar">
                                {{ strtoupper(substr($row['user']->name, 0, 1)) }}{{ strtoupper(substr(strrchr($row['user']->name, ' ') ?: $row['user']->name, 1, 1)) }}
                            </div>
                            <div>
                                <div class="member-name">{{ $row['user']->name }}</div>
                                <div class="member-dept">{{ $row['user']->employee_code ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.8rem;color:var(--text-secondary);">
                        {{ $row['user']->department?->name ?? '—' }}
                    </td>
                    <td>
                        <span class="tlp-badge {{ $badgeClass }}">{{ $row['type'] }}</span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-secondary);">{{ $row['time'] }}</td>
                    <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- —— Team Overview —————————————————————————————————————————————————————————— --}}
<div class="d-card">
    <div class="d-card-header">
        <span class="material-symbols-outlined">groups</span>
        <h5>Team Attendance Today</h5>
        <span style="margin-left:auto; font-size:.78rem; color:var(--text-muted);">
            {{ now()->format('D, d M Y') }}
        </span>
    </div>
    <div class="d-card-body" style="padding:0;">
        @if($teamOverview->isEmpty())
            <p style="font-size:.85rem; color:var(--text-muted); text-align:center; padding:1.5rem 1.25rem; margin:0;">
                No team members are assigned to you yet.
            </p>
        @else
        <div class="team-table-wrap">
            <table class="team-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Work Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamOverview as $row)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem;">
                                <div class="member-avatar">
                                    {{ strtoupper(substr($row['user']->name, 0, 1)) }}{{ strtoupper(substr(strrchr($row['user']->name, ' ') ?: $row['user']->name, 1, 1)) }}
                                </div>
                                <div>
                                    <div class="member-name">{{ $row['user']->name }}</div>
                                    <div class="member-dept">{{ $row['user']->employee_code ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.8rem; color:var(--text-secondary);">
                            {{ $row['user']->department?->name ?? '—' }}
                        </td>
                        <td>
                            @php
                                $s = $row['status'];
                                $badgeClass = match($s) {
                                    'present'  => 'badge-present',
                                    'leave'    => 'badge-leave',
                                    'half_day' => 'badge-half',
                                    default    => 'badge-absent',
                                };
                                $icon = match($s) {
                                    'present'  => 'check_circle',
                                    'leave'    => 'event_busy',
                                    'half_day' => 'schedule',
                                    default    => 'cancel',
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                <span class="material-symbols-outlined">{{ $icon }}</span>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </span>
                        </td>
                        <td>
                            @if($row['punch_in'])
                                <span class="time-val">{{ $row['punch_in'] }}</span>
                            @else
                                <span class="time-dash">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row['punch_out'])
                                <span class="time-val">{{ $row['punch_out'] }}</span>
                            @else
                                <span class="time-dash">—</span>
                            @endif
                        </td>
                        <td style="font-size:.82rem; font-weight:600;">{{ $row['work_hours'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- —— Insufficient Hours Modal ——————————————————————————————————————————————— --}}
<div class="modal-overlay" id="insufficientModal">
    <div class="modal-box">
        <div class="modal-icon warning">
            <span class="material-symbols-outlined">warning</span>
        </div>
        <h4>Insufficient Work Hours</h4>
        <p>You've worked <strong id="elapsedDisplay">0h 0m</strong> today, which is below the required <strong>9 hours</strong>.</p>
        <p class="modal-sub">Do you still want to punch out?</p>
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeInsuffModal()">Stay &amp; Continue</button>
            <button type="button" class="btn-modal-confirm" onclick="confirmPunchOut()">Punch Out Anyway</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // —— Live current-time clock (punch-none state) ———————————————————————————
    var clockEl = document.getElementById('currentClock');
    if (clockEl) {
        function updateClock() {
            var now = new Date();
            var h = String(now.getHours()).padStart(2, '0');
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = h + ':' + m + ':' + s;
        }
        setInterval(updateClock, 1000);
        updateClock();
    }

    // —— Geolocation helper ———————————————————————————————————————————————————
    function getLocation(callback) {
        if (!navigator.geolocation) { callback(null); return; }
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                callback({ lat: pos.coords.latitude, lng: pos.coords.longitude });
            },
            function () { callback(null); },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // —— Reverse geocoding (OpenStreetMap Nominatim) ———————————————————————————
    // Returns: { label, formatted_address, suburb, city, state, country }
    // Priority: neighbourhood > quarter > suburb > locality > village > hamlet
    function reverseGeocode(lat, lng, callback) {
        var url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1';
        fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var a       = data.address || {};
                var suburb  = a.neighbourhood || a.quarter || a.suburb || a.locality || a.village || a.hamlet || '';
                var city    = a.city || a.town || a.city_district || a.county || '';
                var state   = a.state || '';
                var country = a.country || '';

                // Full formatted address (neighbourhood, city, state, country)
                var addrParts = [suburb, city, state, country].filter(Boolean);
                var addrUniq  = addrParts.filter(function (v, i, arr) { return i === 0 || v !== arr[i - 1]; });
                var formattedAddress = addrUniq.join(', ')
                    || data.display_name
                    || (lat.toFixed(5) + ', ' + lng.toFixed(5));

                // Display label (without country for compact display)
                var lblParts = [suburb, city, state].filter(Boolean);
                var lblUniq  = lblParts.filter(function (v, i, arr) { return i === 0 || v !== arr[i - 1]; });
                var label    = lblUniq.length
                    ? lblUniq.join(', ')
                    : (data.display_name || (lat.toFixed(5) + ', ' + lng.toFixed(5)));

                callback({ label: label, formatted_address: formattedAddress, suburb: suburb, city: city, state: state, country: country });
            })
            .catch(function () {
                var fallback = lat.toFixed(5) + ', ' + lng.toFixed(5);
                callback({ label: fallback, formatted_address: fallback, suburb: '', city: '', state: '', country: '' });
            });
    }

    // —— Punch In —————————————————————————————————————————————————————————————
    var punchInBtn = document.getElementById('punchInBtn');
    if (punchInBtn) {
        punchInBtn.addEventListener('click', function () {
            punchInBtn.disabled = true;
            punchInBtn.innerHTML = '<span class="material-symbols-outlined spin">sync</span> Getting locationÃ¢â‚¬Â¦';

            var statusEl = document.getElementById('locationStatus');
            var textEl   = document.getElementById('locationText');
            statusEl.style.display = 'inline-flex';
            textEl.textContent = 'Detecting GPS locationÃ¢â‚¬Â¦';

            getLocation(function (loc) {
                if (loc) {
                    document.getElementById('inLat').value = loc.lat;
                    document.getElementById('inLng').value = loc.lng;
                    textEl.textContent = 'Resolving addressÃ¢â‚¬Â¦';
                    reverseGeocode(loc.lat, loc.lng, function (geo) {
                        document.getElementById('inLabel').value             = geo.label;
                        document.getElementById('inFormattedAddress').value  = geo.formatted_address;
                        document.getElementById('inSuburb').value            = geo.suburb;
                        document.getElementById('inCity').value              = geo.city;
                        document.getElementById('inState').value             = geo.state;
                        document.getElementById('inCountry').value           = geo.country;
                        textEl.textContent = geo.formatted_address;
                        punchInBtn.innerHTML = '<span class="material-symbols-outlined spin">sync</span> SubmittingÃ¢â‚¬Â¦';
                        document.getElementById('punchInForm').submit();
                    });
                } else {
                    textEl.textContent = 'Location unavailable — IP recorded';
                    punchInBtn.innerHTML = '<span class="material-symbols-outlined spin">sync</span> SubmittingÃ¢â‚¬Â¦';
                    document.getElementById('punchInForm').submit();
                }
            });
        });
    }

    // —— Live elapsed timer (punch-in state) ——————————————————————————————————
    @if($punchState === 'in' && $punchInTimestamp)
    var PUNCH_IN_MS = {{ $punchInTimestamp }};
    var timerEl     = document.getElementById('liveTimer');

    function updateTimer() {
        var elapsed = Math.floor((Date.now() - PUNCH_IN_MS) / 1000);
        var h = Math.floor(elapsed / 3600);
        var m = Math.floor((elapsed % 3600) / 60);
        var s = elapsed % 60;
        timerEl.textContent =
            String(h).padStart(2, '0') + ':' +
            String(m).padStart(2, '0') + ':' +
            String(s).padStart(2, '0');
    }
    setInterval(updateTimer, 1000);
    updateTimer();

    // —— Punch Out ————————————————————————————————————————————————————————————
    var punchOutBtn = document.getElementById('punchOutBtn');
    if (punchOutBtn) {
        punchOutBtn.addEventListener('click', function () {
            var elapsed = Math.floor((Date.now() - PUNCH_IN_MS) / 1000);
            var hours   = elapsed / 3600;

            if (hours < 9) {
                var h = Math.floor(hours);
                var m = Math.floor((hours - h) * 60);
                document.getElementById('elapsedDisplay').textContent = h + 'h ' + m + 'm';
                document.getElementById('insufficientModal').classList.add('open');
            } else {
                submitPunchOut();
            }
        });
    }
    @endif

    // —— Modal controls ———————————————————————————————————————————————————————
    window.closeInsuffModal = function () {
        document.getElementById('insufficientModal').classList.remove('open');
    };

    window.confirmPunchOut = function () {
        closeInsuffModal();
        submitPunchOut();
    };

    window.submitPunchOut = function () {
        var btn = document.getElementById('punchOutBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined spin">sync</span> ProcessingÃ¢â‚¬Â¦';
        }

        var statusEl = document.getElementById('outLocationStatus');
        var textEl   = document.getElementById('outLocationText');
        if (statusEl) {
            statusEl.style.display = 'inline-flex';
            textEl.textContent = 'Detecting GPS locationÃ¢â‚¬Â¦';
        }

        getLocation(function (loc) {
            if (loc) {
                document.getElementById('outLat').value = loc.lat;
                document.getElementById('outLng').value = loc.lng;
                if (textEl) textEl.textContent = 'Resolving addressÃ¢â‚¬Â¦';
                reverseGeocode(loc.lat, loc.lng, function (geo) {
                    document.getElementById('outLabel').value            = geo.label;
                    document.getElementById('outFormattedAddress').value = geo.formatted_address;
                    document.getElementById('outSuburb').value           = geo.suburb;
                    document.getElementById('outCity').value             = geo.city;
                    document.getElementById('outState').value            = geo.state;
                    document.getElementById('outCountry').value          = geo.country;
                    if (textEl) textEl.textContent = geo.formatted_address;
                    document.getElementById('punchOutForm').submit();
                });
            } else {
                if (textEl) textEl.textContent = 'Location unavailable — IP recorded';
                document.getElementById('punchOutForm').submit();
            }
        });
    };

    // —— Flash auto-dismiss after 6 seconds ———————————————————————————————————
    document.querySelectorAll('.flash-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.flash').remove();
        });
    });
    setTimeout(function () {
        document.querySelectorAll('.flash').forEach(function (el) {
            el.remove();
        });
    }, 6000);

    // —— Close modal on overlay click —————————————————————————————————————————
    document.getElementById('insufficientModal').addEventListener('click', function (e) {
        if (e.target === this) closeInsuffModal();
    });

    // —— AJAX Calendar navigation ——————————————————————————————————————————————
    var CAL_DATA_URL = '{{ route("manager.calendar-data") }}';
    var calLoading   = false;

    function loadCalendar(year, month) {
        if (calLoading) return;
        calLoading = true;

        var grid = document.getElementById('calGrid');
        grid.style.opacity = '0.4';

        fetch(CAL_DATA_URL + '?cal_year=' + year + '&cal_month=' + month, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            document.getElementById('calMonthLabel').textContent = d.calMonthLabel;

            var prev = document.getElementById('calNavPrev');
            var next = document.getElementById('calNavNext');
            prev.dataset.year  = d.calPrevYear;  prev.dataset.month  = d.calPrevMonth;  prev.title  = d.calPrevLabel;
            next.dataset.year  = d.calNextYear;  next.dataset.month  = d.calNextMonth;  next.title  = d.calNextLabel;

            var headers = grid.querySelectorAll('.cal-hdr');
            grid.innerHTML = '';
            headers.forEach(function (h) { grid.appendChild(h); });

            for (var i = 0; i < d.firstDayOffs; i++) {
                var empty = document.createElement('div');
                empty.className = 'cal-day empty';
                grid.appendChild(empty);
            }

            d.days.forEach(function (day) {
                var cell = document.createElement('div');
                cell.className = 'cal-day ' + day.status + (day.isToday ? ' today' : '');
                cell.title     = day.status.charAt(0).toUpperCase() + day.status.slice(1) + ' — ' + day.date;
                cell.textContent = day.day;
                grid.appendChild(cell);
            });

            grid.style.opacity = '1';
            calLoading = false;
        })
        .catch(function () {
            grid.style.opacity = '1';
            calLoading = false;
        });
    }

    function bindCalNav(id) {
        var btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener('click', function () {
                loadCalendar(parseInt(this.dataset.year), parseInt(this.dataset.month));
            });
        }
    }
    bindCalNav('calNavPrev');
    bindCalNav('calNavToday');
    bindCalNav('calNavNext');

})();
</script>
@endpush
