@extends('layouts.app')

@section('title', 'Holiday List')

@push('styles')
<style>
    /* ── Next holiday banner ── */
    .next-holiday-banner {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        color: #fff;
        box-shadow: 0 4px 16px rgba(59,130,246,.3);
    }
    .next-holiday-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .next-holiday-icon .material-symbols-outlined { font-size: 1.75rem; }
    .next-holiday-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        opacity: .75;
        margin-bottom: .25rem;
    }
    .next-holiday-name { font-size: 1.2rem; font-weight: 800; line-height: 1.2; }
    .next-holiday-meta { font-size: .8375rem; opacity: .85; margin-top: .3rem; display: flex; gap: 1rem; flex-wrap: wrap; }
    .next-holiday-badge {
        margin-left: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: rgba(255,255,255,.15);
        border-radius: 12px;
        padding: .75rem 1.25rem;
        text-align: center;
        flex-shrink: 0;
    }
    .next-holiday-days { font-size: 2rem; font-weight: 900; line-height: 1; }
    .next-holiday-days-lbl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; opacity: .8; margin-top: .15rem; }
    @media(max-width:600px) {
        .next-holiday-banner { flex-wrap: wrap; }
        .next-holiday-badge  { margin-left: 0; width: 100%; flex-direction: row; gap: 1rem; justify-content: center; }
    }

    /* ── Stats strip ── */
    .holiday-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media(max-width:900px) { .holiday-stats { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:480px) { .holiday-stats { grid-template-columns: repeat(2,1fr); } }
    .stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); padding: .875rem 1rem; display: flex; align-items: center; gap: .75rem; }
    .stat-box-icon { width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; }
    .stat-box-icon .material-symbols-outlined { font-size: 19px; font-variation-settings: 'FILL' 1; }
    .stat-val { font-size: 1.35rem; font-weight: 800; letter-spacing: -.03em; line-height: 1; color: var(--text-main); }
    .stat-lbl { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .15rem; }

    /* ── Filter bar ── */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
    .filter-bar { display: flex; gap: .75rem; flex-wrap: wrap; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: #fafbfc; }
    .filter-bar select { font-size: .8125rem; padding: .375rem .625rem; border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-main); background: var(--surface); height: 34px; }
    .filter-bar select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .btn-filter { height: 34px; padding: 0 .875rem; font-size: .8125rem; font-weight: 600; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-secondary); cursor: pointer; display: inline-flex; align-items: center; gap: .35rem; transition: all .15s; text-decoration: none; }
    .btn-filter:hover { background: var(--bg-light); color: var(--text-main); }
    .btn-filter.active { background: var(--primary-subtle); border-color: var(--primary); color: var(--primary); }

    /* ── Holiday table ── */
    .holiday-table { width: 100%; border-collapse: collapse; font-size: .8375rem; }
    .holiday-table th { padding: .6rem 1rem; text-align: left; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); border-bottom: 2px solid var(--border); background: #f8fafc; white-space: nowrap; }
    .holiday-table td { padding: .8rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .holiday-table tr:last-child td { border-bottom: none; }
    .holiday-table tr:hover td { background: #f8fafc; }
    .holiday-table tr.row-today td { background: #eff6ff; }
    .holiday-table tr.row-today:hover td { background: #dbeafe; }
    .holiday-table tr.row-past td { opacity: .6; }

    .holiday-name { font-weight: 600; color: var(--text-main); }
    .holiday-name-today { color: #1d4ed8; }
    .today-badge { display: inline-flex; align-items: center; gap: .2rem; background: #dbeafe; color: #1d4ed8; font-size: .65rem; font-weight: 700; padding: .1rem .4rem; border-radius: 4px; vertical-align: middle; margin-left: .35rem; }
    .past-label { font-size: .65rem; color: var(--text-muted); font-weight: 600; padding: .1rem .4rem; border-radius: 4px; background: #f1f5f9; vertical-align: middle; margin-left: .35rem; }

    .type-badge { display: inline-block; padding: .2rem .6rem; border-radius: 4px; font-size: .7rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .type-national { background: #eff6ff; color: #1d4ed8; }
    .type-optional { background: #fef9c3; color: #92400e; }

    .scope-badge { display: inline-block; padding: .15rem .5rem; border-radius: 4px; font-size: .68rem; font-weight: 600; background: #f0fdf4; color: #15803d; }

    .holiday-desc { font-size: .775rem; color: var(--text-muted); max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size: 3rem; display: block; margin-bottom: .75rem; opacity: .35; }
    .empty-state p { font-size: .9rem; margin: 0; }

    /* Divider row */
    .table-divider-row td { padding: .3rem 1rem; background: #f1f5f9; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); border-bottom: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Holiday List</h1>
    <p class="page-subtitle" style="margin-bottom:0;">Public holidays and optional holidays for this year.</p>
</div>

{{-- ── Next Holiday Banner ── --}}
@if($nextHoliday)
    @php
        $daysUntil = now()->startOfDay()->diffInDays($nextHoliday->date->startOfDay(), false);
        $isToday   = $daysUntil === 0;
    @endphp
    <div class="next-holiday-banner">
        <div class="next-holiday-icon">
            <span class="material-symbols-outlined">celebration</span>
        </div>
        <div style="flex:1;min-width:0;">
            <div class="next-holiday-label">{{ $isToday ? 'Today is a Holiday!' : 'Next Holiday' }}</div>
            <div class="next-holiday-name">{{ $nextHoliday->name }}</div>
            <div class="next-holiday-meta">
                <span>
                    <span class="material-symbols-outlined" style="font-size:.85rem;vertical-align:middle;">calendar_today</span>
                    {{ $nextHoliday->date->format('l, d F Y') }}
                </span>
                <span>
                    <span class="material-symbols-outlined" style="font-size:.85rem;vertical-align:middle;">flag</span>
                    {{ $nextHoliday->type === 'national' ? 'National Holiday' : 'Optional Holiday' }}
                </span>
                @if($nextHoliday->scope === 'department' && $nextHoliday->department)
                    <span>
                        <span class="material-symbols-outlined" style="font-size:.85rem;vertical-align:middle;">corporate_fare</span>
                        {{ $nextHoliday->department->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="next-holiday-badge">
            @if($isToday)
                <span class="material-symbols-outlined" style="font-size:2rem;">today</span>
                <span class="next-holiday-days-lbl">Today</span>
            @else
                <div class="next-holiday-days">{{ $daysUntil }}</div>
                <div class="next-holiday-days-lbl">{{ $daysUntil === 1 ? 'day away' : 'days away' }}</div>
            @endif
        </div>
    </div>
@endif

{{-- ── Stats Strip ── --}}
@php
    $allHolidays   = \App\Models\Holiday::active()->get();
    $todayStr      = now()->toDateString();
    $totalCount    = $allHolidays->count();
    $upcomingCount = $allHolidays->where('date', '>=', $todayStr)->count();
    $nationalCount = $allHolidays->where('type', 'national')->count();
    $optionalCount = $allHolidays->where('type', 'optional')->count();
@endphp
<div class="holiday-stats">
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#3b82f6;">
            <span class="material-symbols-outlined">event</span>
        </div>
        <div>
            <div class="stat-val">{{ $totalCount }}</div>
            <div class="stat-lbl">Total</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#22c55e;">
            <span class="material-symbols-outlined">upcoming</span>
        </div>
        <div>
            <div class="stat-val">{{ $upcomingCount }}</div>
            <div class="stat-lbl">Upcoming</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#1d4ed8;">
            <span class="material-symbols-outlined">flag</span>
        </div>
        <div>
            <div class="stat-val">{{ $nationalCount }}</div>
            <div class="stat-lbl">National</div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-icon" style="background:#f59e0b;">
            <span class="material-symbols-outlined">star</span>
        </div>
        <div>
            <div class="stat-val">{{ $optionalCount }}</div>
            <div class="stat-lbl">Optional</div>
        </div>
    </div>
</div>

{{-- ── Filter Card & Table ── --}}
<div class="card">
    <form method="GET" action="{{ route('holidays.index') }}">
        <div class="filter-bar">
            <select name="year">
                <option value="">All Years</option>
                @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
            <select name="month">
                <option value="">All Months</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endforeach
            </select>
            <select name="type">
                <option value="">All Types</option>
                <option value="national" {{ request('type') === 'national' ? 'selected' : '' }}>National</option>
                <option value="optional" {{ request('type') === 'optional' ? 'selected' : '' }}>Optional</option>
            </select>
            <button type="submit" class="btn-filter active">
                <span class="material-symbols-outlined" style="font-size:.95rem;">filter_list</span>
                Filter
            </button>
            @if(request()->hasAny(['year','month','type']))
                <a href="{{ route('holidays.index') }}" class="btn-filter">Clear</a>
            @endif
        </div>
    </form>

    <div style="overflow-x:auto;">
        <table class="holiday-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Holiday Name</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Type</th>
                    <th>Scope</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $i => $holiday)
                    @php
                        $hDate   = $holiday->date->startOfDay();
                        $isToday = $hDate->isToday();
                        $isPast  = $hDate->isPast() && ! $isToday;
                    @endphp
                    <tr class="{{ $isToday ? 'row-today' : ($isPast ? 'row-past' : '') }}">
                        <td style="color:var(--text-muted);font-size:.8rem;">{{ $holidays->firstItem() + $i }}</td>
                        <td>
                            <div class="holiday-name {{ $isToday ? 'holiday-name-today' : '' }}">
                                {{ $holiday->name }}
                                @if($isToday)
                                    <span class="today-badge">
                                        <span class="material-symbols-outlined" style="font-size:.65rem;">today</span>Today
                                    </span>
                                @elseif($isPast)
                                    <span class="past-label">Past</span>
                                @endif
                            </div>
                        </td>
                        <td style="font-size:.8125rem;white-space:nowrap;{{ $isToday ? 'color:#1d4ed8;font-weight:600;' : '' }}">
                            {{ $holiday->date->format('d M Y') }}
                        </td>
                        <td style="font-size:.8125rem;color:var(--text-muted);">
                            {{ $holiday->date->format('l') }}
                        </td>
                        <td>
                            <span class="type-badge type-{{ $holiday->type }}">
                                {{ $holiday->type === 'national' ? 'National' : 'Optional' }}
                            </span>
                        </td>
                        <td>
                            <span class="scope-badge">{{ $holiday->getScopeLabel() }}</span>
                        </td>
                        <td>
                            @if($holiday->description)
                                <div class="holiday-desc" title="{{ $holiday->description }}">{{ $holiday->description }}</div>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="material-symbols-outlined">event_busy</span>
                                <p>No holidays found for the selected filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($holidays->hasPages())
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            {{ $holidays->links() }}
        </div>
    @endif
</div>
@endsection
