@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* ── KPI Cards ── */
    .kpi-grid {
        display: grid; grid-template-columns: repeat(5, 1fr);
        gap: 1rem; margin-bottom: 1.75rem;
    }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 700px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: .25rem;
        position: relative; overflow: hidden;
    }
    .kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; background: var(--kpi-color, var(--primary));
    }
    .kpi-card .kpi-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--kpi-bg, var(--primary-subtle));
        display: flex; align-items: center; justify-content: center;
        margin-bottom: .5rem;
    }
    .kpi-card .kpi-icon .material-symbols-outlined {
        font-size: 1.4rem; color: var(--kpi-color, var(--primary));
        font-variation-settings: 'FILL' 1;
    }
    .kpi-card .kpi-value { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1; }
    .kpi-card .kpi-label { font-size: .75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .04em; margin-top: .25rem; }

    /* ── Charts Grid ── */
    .charts-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 1rem; margin-bottom: 1.75rem;
    }
    .chart-full { grid-column: 1 / -1; }
    @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } .chart-full { grid-column: auto; } }

    .chart-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .chart-card h3 {
        font-size: .875rem; font-weight: 700; color: var(--text-main);
        margin: 0 0 1rem; display: flex; align-items: center; gap: .4rem;
    }
    .chart-card h3 .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); }
    .chart-wrap { position: relative; height: 220px; }
    .chart-wrap.tall { height: 260px; }

    /* ── Section title ── */
    .section-title {
        font-size: 1rem; font-weight: 700; color: var(--text-main);
        margin: 0 0 1rem; display: flex; align-items: center; gap: .4rem;
    }
    .section-title .material-symbols-outlined { font-size: 1.2rem; color: var(--primary); font-variation-settings: 'FILL' 1; }

    /* ── Insights grid ── */
    .insights-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 1rem; margin-bottom: 1.75rem;
    }
    @media (max-width: 900px) { .insights-grid { grid-template-columns: 1fr; } }

    .insight-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .insight-card .ic-header {
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .5rem;
        font-size: .8rem; font-weight: 700; color: var(--text-main);
        text-transform: uppercase; letter-spacing: .04em; background: #f8fafc;
    }
    .insight-card .ic-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); font-variation-settings: 'FILL' 1; }

    .insight-list { list-style: none; margin: 0; padding: 0; }
    .insight-list li {
        display: flex; align-items: center; justify-content: space-between;
        padding: .625rem 1.25rem; border-bottom: 1px solid var(--border); font-size: .84rem;
    }
    .insight-list li:last-child { border-bottom: none; }
    .insight-list .emp-info { display: flex; flex-direction: column; }
    .insight-list .emp-name { font-weight: 600; color: var(--text-main); font-size: .84rem; }
    .insight-list .emp-dept { font-size: .72rem; color: var(--text-secondary); }
    .insight-list .ins-val  { font-weight: 700; font-size: .875rem; }
    .ins-val.red    { color: #dc2626; }
    .ins-val.orange { color: #ea580c; }
    .ins-val.blue   { color: #2563eb; }
    .ins-empty { padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: .84rem; }

    .avg-box {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.25rem 1.5rem; box-shadow: var(--shadow-sm);
        display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;
    }
    .avg-box .material-symbols-outlined { font-size: 2rem; color: var(--primary); font-variation-settings: 'FILL' 1; }
    .avg-box .avg-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
    .avg-box .avg-label { font-size: .8rem; color: var(--text-secondary); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">insights</span>
        Analytics Dashboard
    </h1>
    <p>Real-time overview of workforce attendance, leaves, payroll costs, and productivity insights.</p>
</div>

{{-- ── KPI Cards ── --}}
<div class="kpi-grid">
    <div class="kpi-card" style="--kpi-color:#137fec;--kpi-bg:rgba(19,127,236,.1);">
        <div class="kpi-icon"><span class="material-symbols-outlined">group</span></div>
        <div class="kpi-value">{{ $totalEmployees }}</div>
        <div class="kpi-label">Total Active Employees</div>
    </div>
    <div class="kpi-card" style="--kpi-color:#16a34a;--kpi-bg:#dcfce7;">
        <div class="kpi-icon"><span class="material-symbols-outlined">check_circle</span></div>
        <div class="kpi-value">{{ $presentToday }}</div>
        <div class="kpi-label">Present Today</div>
    </div>
    <div class="kpi-card" style="--kpi-color:#dc2626;--kpi-bg:#fee2e2;">
        <div class="kpi-icon"><span class="material-symbols-outlined">cancel</span></div>
        <div class="kpi-value">{{ $absentToday }}</div>
        <div class="kpi-label">Absent Today</div>
    </div>
    <div class="kpi-card" style="--kpi-color:#ea580c;--kpi-bg:#ffedd5;">
        <div class="kpi-icon"><span class="material-symbols-outlined">schedule</span></div>
        <div class="kpi-value">{{ $lateToday }}</div>
        <div class="kpi-label">Late Today (after 9:30)</div>
    </div>
    <div class="kpi-card" style="--kpi-color:#7c3aed;--kpi-bg:#ede9fe;">
        <div class="kpi-icon"><span class="material-symbols-outlined">pending_actions</span></div>
        <div class="kpi-value">{{ $pendingApprovals }}</div>
        <div class="kpi-label">Pending Approvals</div>
    </div>
</div>

{{-- ── Charts ── --}}
<div class="charts-grid">
    {{-- Attendance Trend (full width) --}}
    <div class="chart-card chart-full">
        <h3>
            <span class="material-symbols-outlined">trending_up</span>
            Attendance Trend — Last 30 Days
        </h3>
        <div class="chart-wrap tall">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Department Attendance --}}
    <div class="chart-card">
        <h3>
            <span class="material-symbols-outlined">corporate_fare</span>
            Department Attendance (This Month)
        </h3>
        <div class="chart-wrap">
            <canvas id="deptChart"></canvas>
        </div>
    </div>

    {{-- Attendance Rate by Day of Week --}}
    <div class="chart-card">
        <h3>
            <span class="material-symbols-outlined">calendar_view_week</span>
            Attendance Rate by Day of Week (This Month)
        </h3>
        <div class="chart-wrap">
            <canvas id="dowChart"></canvas>
        </div>
    </div>

    {{-- Payroll Cost --}}
    <div class="chart-card chart-full">
        <h3>
            <span class="material-symbols-outlined">payments</span>
            Payroll Cost — Last 6 Months (Net Salary)
        </h3>
        <div class="chart-wrap">
            <canvas id="payrollChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Productivity Insights ── --}}
<div class="section-title">
    <span class="material-symbols-outlined">psychology</span>
    Productivity Insights — Current Month
</div>

<div class="avg-box">
    <span class="material-symbols-outlined">timer</span>
    <div>
        <div class="avg-value">
            @php
                $avgH = (int) floor($avgWorkHours);
                $avgM = (int) round(($avgWorkHours - $avgH) * 60);
            @endphp
            {{ $avgH }}h {{ $avgM }}m
        </div>
        <div class="avg-label">Average Working Hours per Present Day (this month)</div>
    </div>
</div>

<div class="insights-grid">
    {{-- Frequent Late Users --}}
    <div class="insight-card">
        <div class="ic-header">
            <span class="material-symbols-outlined">schedule</span>
            Frequent Late Arrivals (Top 5)
        </div>
        @if($frequentLate->isEmpty())
            <div class="ins-empty">No late arrivals this month.</div>
        @else
            <ul class="insight-list">
                @foreach($frequentLate as $item)
                    <li>
                        <div class="emp-info">
                            <span class="emp-name">{{ $item->user?->name ?? '—' }}</span>
                            <span class="emp-dept">{{ $item->user?->department?->name ?? 'No Dept.' }}</span>
                        </div>
                        <span class="ins-val orange">{{ $item->late_count }} days</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- High LOP Employees --}}
    <div class="insight-card">
        <div class="ic-header">
            <span class="material-symbols-outlined">money_off</span>
            High LOP Employees (Top 5)
        </div>
        @if($highLop->isEmpty())
            <div class="ins-empty">No LOP records for this month.</div>
        @else
            <ul class="insight-list">
                @foreach($highLop as $item)
                    <li>
                        <div class="emp-info">
                            <span class="emp-name">{{ $item->employee?->name ?? '—' }}</span>
                            <span class="emp-dept">{{ $item->employee?->department?->name ?? 'No Dept.' }}</span>
                        </div>
                        <span class="ins-val red">{{ number_format($item->lop_days, 1) }} days</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Permission Usage --}}
    <div class="insight-card">
        <div class="ic-header">
            <span class="material-symbols-outlined">hourglass_top</span>
            Permission Usage (Top 5)
        </div>
        @if($permUsage->isEmpty())
            <div class="ins-empty">No permission hours recorded this month.</div>
        @else
            <ul class="insight-list">
                @foreach($permUsage as $item)
                    <li>
                        <div class="emp-info">
                            <span class="emp-name">{{ $item->user?->name ?? '—' }}</span>
                            <span class="emp-dept">{{ $item->user?->department?->name ?? 'No Dept.' }}</span>
                        </div>
                        <span class="ins-val blue">{{ number_format($item->total_perm, 1) }}h</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const primary  = '#137fec';
    const green    = '#16a34a';
    const red      = '#dc2626';
    const orange   = '#ea580c';
    const purple   = '#7c3aed';
    const slate    = '#64748b';

    const gridColor  = 'rgba(0,0,0,.06)';
    const fontFamily = "'Inter', 'Segoe UI', Arial, sans-serif";

    Chart.defaults.font.family  = fontFamily;
    Chart.defaults.font.size    = 12;
    Chart.defaults.color        = '#64748b';

    // ── 1. Attendance Trend ────────────────────────────────────────────────────
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [
                {
                    label: 'Present',
                    data: @json($trendPresent),
                    borderColor: green,
                    backgroundColor: 'rgba(22,163,74,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: .35,
                },
                {
                    label: 'Absent / Other',
                    data: @json($trendAbsent),
                    borderColor: red,
                    backgroundColor: 'rgba(220,38,38,.06)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: .35,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: { grid: { color: gridColor } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1 } },
            },
        },
    });

    // ── 2. Department Attendance ───────────────────────────────────────────────
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: @json($deptLabels),
            datasets: [{
                label: 'Present Days',
                data: @json($deptCounts),
                backgroundColor: primary,
                borderRadius: 5,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: gridColor } },
            },
        },
    });

    // ── 3. Attendance Rate by Day of Week ─────────────────────────────────────
    const dowRates = @json($dowRates);
    new Chart(document.getElementById('dowChart'), {
        type: 'bar',
        data: {
            labels: @json($dowLabels),
            datasets: [{
                label: 'Attendance Rate (%)',
                data: dowRates,
                backgroundColor: dowRates.map(v => v >= 80 ? green : v >= 60 ? orange : red),
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.parsed.y.toFixed(1) + '%' } },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: gridColor },
                    ticks: { callback: v => v + '%' },
                },
            },
        },
    });

    // ── 4. Payroll Cost ────────────────────────────────────────────────────────
    new Chart(document.getElementById('payrollChart'), {
        type: 'bar',
        data: {
            labels: @json($payrollLabels),
            datasets: [{
                label: 'Net Salary (₹)',
                data: @json($payrollCosts),
                backgroundColor: [primary, primary, primary, primary, primary, '#0f6fd4'],
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '₹' + ctx.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 }),
                    },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        callback: v => '₹' + (v >= 100000 ? (v / 100000).toFixed(1) + 'L' : v.toLocaleString()),
                    },
                },
            },
        },
    });
})();
</script>
@endpush
