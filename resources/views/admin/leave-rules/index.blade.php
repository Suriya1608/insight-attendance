@extends('layouts.app')

@section('title', 'Leave Rules')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* Policy banner */
    .policy-banner {
        display: flex; align-items: center; gap: .625rem;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: var(--radius-md); padding: .875rem 1.25rem;
        font-size: .85rem; color: #1e40af; margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .policy-banner .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .policy-banner strong { color: #1d4ed8; }
    .policy-banner .sep { color: #93c5fd; margin: 0 .375rem; }

    /* Stat cards */
    .stat-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: .875rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .stat-grid { grid-template-columns: 1fr; } }

    .stat-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.25rem;
        display: flex; flex-direction: column; align-items: center; gap: .5rem;
    }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }
    .stat-val {
        font-size: 2rem; font-weight: 800; line-height: 1;
        letter-spacing: -.04em; color: var(--text-main);
    }
    .stat-lbl {
        font-size: .72rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .05em; color: var(--text-muted); text-align: center;
    }
    .s-blue   { background: #eff6ff; color: #3b82f6; }
    .s-yellow { background: #fefce8; color: #d97706; }
    .s-purple { background: #f5f3ff; color: #8b5cf6; }
    .s-green  { background: #f0fdf4; color: #22c55e; }
    .s-blue-val   { color: #3b82f6; }
    .s-yellow-val { color: #d97706; }
    .s-purple-val { color: #8b5cf6; }
    .s-green-val  { color: #22c55e; }

    /* Table card */
    .rule-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .rule-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b;
    }
    .rule-card-header h5 {
        font-size: .9rem; font-weight: 700; margin: 0; color: #fff;
    }
    .dept-count {
        background: rgba(255,255,255,.12); color: #e2e8f0;
        border-radius: 20px; padding: .15rem .65rem;
        font-size: .75rem; font-weight: 600;
    }

    .rule-table { width: 100%; border-collapse: collapse; font-size: .8375rem; }
    .rule-table thead th {
        padding: .625rem 1rem; text-align: left;
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--text-muted);
        border-bottom: 1px solid var(--border); background: #fafbfd;
        white-space: nowrap;
    }
    .rule-table tbody tr { transition: background .1s; }
    .rule-table tbody tr:hover { background: rgba(0,0,0,.025); }
    .rule-table td {
        padding: .75rem 1rem; border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .rule-table tbody tr:last-child td { border-bottom: none; }

    /* Department cell */
    .dept-name { font-weight: 700; color: var(--text-main); font-size: .875rem; }
    .dept-code {
        display: inline-block; padding: .1rem .45rem;
        background: #f1f5f9; border-radius: 4px;
        font-size: .72rem; font-weight: 700; color: var(--text-muted);
        font-family: monospace; margin-top: .15rem;
    }

    /* Pills */
    .pill-cl {
        display: inline-flex; align-items: center;
        background: #f0fdf4; color: #15803d;
        border: 1px solid #bbf7d0; border-radius: 6px;
        padding: .2rem .6rem; font-size: .8rem; font-weight: 700;
    }
    .perm-val {
        font-size: .8rem; font-weight: 700; color: var(--text-secondary);
    }
    .perm-sub { font-size: .72rem; color: var(--text-muted); margin-top: .1rem; }

    .sat-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .65rem; border-radius: 20px;
        font-size: .775rem; font-weight: 600; white-space: nowrap;
    }
    .sat-none     { background: #f1f5f9; color: #64748b; }
    .sat-fixed    { background: #f3e8ff; color: #7c3aed; }
    .sat-flexible { background: #fef3c7; color: #b45309; }
    .sat-carry    { background: #fff7ed; color: #c2410c; }

    .carry-yes {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .775rem; font-weight: 600; color: #0369a1;
    }
    .carry-none { color: var(--text-muted); }

    /* Configure button */
    .btn-configure {
        display: inline-flex; align-items: center; gap: .3rem;
        height: 2rem; padding: 0 .875rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .8rem; font-weight: 600;
        text-decoration: none; cursor: pointer; transition: background .15s;
    }
    .btn-configure:hover { background: var(--primary-hover); color: #fff; }

    /* Alert */
    .alert-success {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0;
        border-radius: var(--radius-sm); color: #15803d; font-size: .84rem;
        margin-bottom: 1.25rem;
    }
</style>
@endpush

@section('content')

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="font-size:1.4rem; color:var(--primary); font-variation-settings:'FILL' 1;">policy</span>
            Leave Rules
        </h1>
        <p>Configure monthly leave entitlements and Saturday rules per department</p>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Policy banner --}}
    <div class="policy-banner">
        <span class="material-symbols-outlined">info</span>
        <span><strong>Working Policy:</strong> Monday – Saturday, minimum 9 hours login per day.</span>
        <span class="sep">|</span>
        <span><strong>All departments:</strong> 1 CL/month + 2 × 2-hour permissions/month.</span>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon s-blue">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">table_chart</span>
            </div>
            <div class="stat-val s-blue-val">{{ $stats['total'] }}</div>
            <div class="stat-lbl">Total Departments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon s-yellow">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">weekend</span>
            </div>
            <div class="stat-val s-yellow-val">{{ $stats['flexible_sat'] }}</div>
            <div class="stat-lbl">Flexible Saturday</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon s-purple">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">event_busy</span>
            </div>
            <div class="stat-val s-purple-val">{{ $stats['fixed_sat_off'] }}</div>
            <div class="stat-lbl">Fixed Saturday Off</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon s-green">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">verified</span>
            </div>
            <div class="stat-val s-green-val">{{ $stats['rules_configured'] }}</div>
            <div class="stat-lbl">Rules Configured</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rule-card">
        <div class="rule-card-header">
            <h5>Department Leave Rules</h5>
            <span class="dept-count">{{ $departments->count() }} departments</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="rule-table">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Department</th>
                        <th>CL / Month</th>
                        <th>Permissions / Month</th>
                        <th>Saturday Rule</th>
                        <th>Carry Forward</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $i => $dept)
                        @php
                            $satRule = $dept->saturday_rule ?? 'none';
                            $hasSat  = in_array($satRule, ['flexible_saturday', 'carry_forward']);
                            $cl      = $dept->cl_per_month ?? 1;
                            $perms   = $dept->permissions_per_month ?? 2;
                            $hrs     = $dept->hours_per_permission ?? 2;
                        @endphp
                        <tr>
                            <td style="color:var(--text-muted); font-size:.8rem;">{{ $i + 1 }}</td>
                            <td>
                                <div class="dept-name">{{ $dept->name }}</div>
                                @if($dept->code)
                                    <div><span class="dept-code">{{ $dept->code }}</span></div>
                                @endif
                            </td>
                            <td>
                                <span class="pill-cl">{{ number_format($cl, 0) }} day</span>
                            </td>
                            <td>
                                <div class="perm-val">
                                    <span style="background:#ede9fe; color:#6d28d9; padding:.15rem .55rem; border-radius:6px; font-size:.8rem; font-weight:700;">
                                        {{ $perms }} × {{ $hrs }}h
                                    </span>
                                </div>
                                <div class="perm-sub">{{ $perms * $hrs }}.0h total/month</div>
                            </td>
                            <td>
                                @switch($satRule)
                                    @case('2nd_saturday_off')
                                        <span class="sat-pill sat-fixed">
                                            <span class="material-symbols-outlined" style="font-size:.85rem;">event_busy</span>
                                            2nd Saturday Off (Fixed)
                                        </span>
                                        @break
                                    @case('4th_saturday_off')
                                        <span class="sat-pill sat-fixed">
                                            <span class="material-symbols-outlined" style="font-size:.85rem;">event_busy</span>
                                            4th Saturday Off (Fixed)
                                        </span>
                                        @break
                                    @case('flexible_saturday')
                                        <span class="sat-pill sat-flexible">
                                            <span class="material-symbols-outlined" style="font-size:.85rem;">shuffle</span>
                                            1 Flexible Saturday / Month
                                        </span>
                                        @break
                                    @case('carry_forward')
                                        <span class="sat-pill sat-carry">
                                            <span class="material-symbols-outlined" style="font-size:.85rem;">redo</span>
                                            Carry Forward if All Worked
                                        </span>
                                        @break
                                    @default
                                        <span class="sat-pill sat-none">— All Saturdays Working</span>
                                @endswitch
                            </td>
                            <td>
                                @if($hasSat)
                                    <span class="carry-yes">
                                        <span class="material-symbols-outlined" style="font-size:.9rem;">redo</span>
                                        Yes — to any weekday
                                    </span>
                                @else
                                    <span class="carry-none">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.leave-rules.edit', $dept) }}" class="btn-configure">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">tune</span>
                                    Configure
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                                No departments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
