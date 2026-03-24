@extends('layouts.app')

@section('title', 'My Timesheets')

@push('styles')
<style>
    .flash { display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;margin-bottom:1rem;border:1px solid transparent; }
    .flash .material-symbols-outlined { font-size:1.1rem;flex-shrink:0; }
    .flash-close { margin-left:auto;background:none;border:none;cursor:pointer;font-size:1.1rem;opacity:.6;line-height:1; }
    .flash-success { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; }
    .flash-error   { background:#fff1f2;color:#dc2626;border-color:#fecaca; }

    .stat-row { display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:768px){ .stat-row { grid-template-columns:repeat(2,1fr); } }
    .stat-box { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.3rem; }
    .stat-box .stat-num { font-size:1.75rem;font-weight:800;line-height:1; }
    .stat-box .stat-lbl { font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em; }

    .ts-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;text-decoration:none;color:inherit;transition:box-shadow .15s,border-color .15s;margin-bottom:.625rem; }
    .ts-card:hover { box-shadow:var(--shadow-md);border-color:#cbd5e1;color:inherit; }
    .ts-date-badge { display:flex;flex-direction:column;align-items:center;justify-content:center;width:52px;height:52px;border-radius:var(--radius-sm);background:var(--primary-subtle);color:var(--primary);flex-shrink:0; }
    .ts-date-badge .day  { font-size:1.375rem;font-weight:800;line-height:1; }
    .ts-date-badge .mon  { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em; }
    .ts-info { flex:1;min-width:0; }
    .ts-info .ts-title { font-size:.9375rem;font-weight:700;margin-bottom:.2rem; }
    .ts-info .ts-meta  { font-size:.8rem;color:var(--text-muted); }
    .ts-hours { font-size:1rem;font-weight:700;color:var(--text-main);text-align:right;flex-shrink:0; }
    .ts-hours .ts-hrs-lbl { font-size:.7rem;color:var(--text-muted);font-weight:500; }

    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:999px;font-size:.72rem;font-weight:700;letter-spacing:.03em;flex-shrink:0; }
    .badge-gray   { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue   { background:#eff6ff;color:#1d4ed8; }
    .badge-green  { background:#f0fdf4;color:#15803d; }
    .badge-red    { background:#fff1f2;color:#dc2626; }

    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5rem 1rem;font-size:.8375rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-outline { background:transparent;border:1px solid var(--border);color:var(--text-secondary);border-radius:var(--radius-sm);padding:.45rem .875rem;font-size:.8375rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;transition:all .15s; }
    .btn-outline:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }

    .month-nav { display:flex;align-items:center;gap:.5rem; }
    .month-nav a { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;transition:all .15s; }
    .month-nav a:hover { background:var(--bg-light);color:var(--text-main); }
    .month-label { font-size:.9375rem;font-weight:700;min-width:120px;text-align:center; }

    .empty-state { text-align:center;padding:3rem 1rem; }
    .empty-state .material-symbols-outlined { font-size:3rem;color:var(--text-muted);margin-bottom:.75rem; }
    .empty-state p { color:var(--text-muted);font-size:.9rem; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">My Timesheets</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Log and track your daily work activities.</p>
    </div>
    <a href="{{ $defaultTimelineUrl }}" class="btn-primary">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        Today's Timesheet
    </a>
</div>

@if(session('success'))
    <div class="flash flash-success">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
        <button class="flash-close" onclick="this.parentElement.remove()">x</button>
    </div>
@endif
@if(session('error'))
    <div class="flash flash-error">
        <span class="material-symbols-outlined">error</span>
        {{ session('error') }}
        <button class="flash-close" onclick="this.parentElement.remove()">x</button>
    </div>
@endif

{{-- Stats --}}
<div class="stat-row">
    <div class="stat-box">
        <div class="stat-num">{{ $stats['total'] }}</div>
        <div class="stat-lbl">Total</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#64748b;">{{ $stats['draft'] }}</div>
        <div class="stat-lbl">Draft</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#c2410c;">{{ $stats['pending'] }}</div>
        <div class="stat-lbl">Pending</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#15803d;">{{ $stats['approved'] }}</div>
        <div class="stat-lbl">Approved</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#dc2626;">{{ $stats['rejected'] }}</div>
        <div class="stat-lbl">Rejected</div>
    </div>
</div>

{{-- Month Navigation --}}
@php
    $prevMonth = $monthDate->copy()->subMonth()->format('Y-m');
    $nextMonth = $monthDate->copy()->addMonth()->format('Y-m');
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
    <div class="month-nav">
        <a href="{{ route($timesheetIndexRoute, ['month' => $prevMonth]) }}" title="Previous month">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">chevron_left</span>
        </a>
        <span class="month-label">{{ $monthDate->format('F Y') }}</span>
        <a href="{{ route($timesheetIndexRoute, ['month' => $nextMonth]) }}" title="Next month">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">chevron_right</span>
        </a>
    </div>
    <span style="font-size:.8rem;color:var(--text-muted);">{{ $timesheets->count() }} timesheet(s) this month</span>
</div>

{{-- Timesheet List --}}
@forelse($timesheets as $ts)
    @php
        $colorMap = ['gray'=>'badge-gray','orange'=>'badge-orange','blue'=>'badge-blue','green'=>'badge-green','red'=>'badge-red'];
        $badgeClass = $colorMap[$ts->status_color] ?? 'badge-gray';
    @endphp
    <a href="{{ route($timesheetShowRoute, ['date' => $ts->date->toDateString()]) }}" class="ts-card">
        <div class="ts-date-badge">
            <span class="day">{{ $ts->date->format('d') }}</span>
            <span class="mon">{{ $ts->date->format('M') }}</span>
        </div>
        <div class="ts-info">
            <div class="ts-title">{{ $ts->date->format('l, d M Y') }}</div>
            <div class="ts-meta">{{ $ts->entries->count() }} work {{ Str::plural('entry', $ts->entries->count()) }}</div>
        </div>
        <span class="status-badge {{ $badgeClass }}">{{ $ts->status_label }}</span>
        <div class="ts-hours">
            <div>{{ $ts->formatted_total_hours }}</div>
            <div class="ts-hrs-lbl">logged</div>
        </div>
        <span class="material-symbols-outlined" style="font-size:1.1rem;color:var(--text-muted);">chevron_right</span>
    </a>
@empty
    <div class="empty-state">
        <span class="material-symbols-outlined">schedule</span>
        <p>No timesheets found for {{ $monthDate->format('F Y') }}.</p>
        <a href="{{ $defaultTimelineUrl }}" class="btn-primary" style="display:inline-flex;margin-top:.75rem;">
            <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
            Start Today's Timesheet
        </a>
    </div>
@endforelse

@endsection
