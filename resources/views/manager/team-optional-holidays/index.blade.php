@extends('layouts.app')

@section('title', 'Team Optional Holidays')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* Filter bar */
    .filter-bar {
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: .875rem 1.25rem;
        margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);
    }
    .filter-bar label { font-size: .8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap; }
    .filter-bar select {
        height: 2.25rem; padding: 0 .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        outline: none; cursor: pointer; font-family: inherit;
    }
    .filter-bar select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .btn-filter {
        height: 2.25rem; padding: 0 1.125rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; transition: background .15s; display: flex; align-items: center; gap: .35rem;
    }
    .btn-filter:hover { background: var(--primary-hover); }
    .btn-filter .material-symbols-outlined { font-size: .95rem; }

    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.75rem; }
    @media (max-width:700px) { .stats-grid { grid-template-columns: 1fr 1fr; } }

    /* Section header */
    .section-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem; padding-bottom: .875rem;
        border-bottom: 1px solid var(--border);
    }
    .section-head h2 { font-size: 1rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: .4rem; }
    .section-head h2 .material-symbols-outlined { color: var(--primary); font-size: 1.15rem; }

    /* Member cards */
    .member-cards { display: flex; flex-direction: column; gap: 1rem; }

    .member-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .mc-header {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem;
        padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--border);
        cursor: pointer; user-select: none;
    }
    .mc-header:hover { background: #f1f5f9; }
    .mc-user { display: flex; align-items: center; gap: .875rem; }
    .mc-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #0f6fd4 100%);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: .9rem; font-weight: 700; flex-shrink: 0; font-family: inherit;
    }
    .mc-name { font-size: .9rem; font-weight: 700; color: var(--text-main); }
    .mc-dept { font-size: .76rem; color: var(--text-muted); margin-top: .1rem; }
    .mc-stats { display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; }
    .mc-stat-item { text-align: center; }
    .mc-stat-val  { font-size: 1.2rem; font-weight: 900; line-height: 1; }
    .mc-stat-lbl  { font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .1rem; }

    /* Progress mini */
    .mini-progress-wrap { display: flex; align-items: center; gap: .625rem; min-width: 120px; }
    .mini-prog-bar { flex: 1; height: 7px; background: var(--bg-light); border-radius: 999px; overflow: hidden; }
    .mini-prog-inner { height: 100%; border-radius: 999px; transition: width .4s; }
    .mini-prog-label { font-size: .76rem; font-weight: 600; white-space: nowrap; }

    /* Chevron */
    .mc-chevron { color: var(--text-muted); transition: transform .25s; }
    .mc-chevron.open { transform: rotate(180deg); }

    /* Selections list */
    .mc-body { display: none; padding: 1.125rem 1.25rem; }
    .mc-body.open { display: block; }

    .sel-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .sel-table thead tr { background: var(--bg-light); }
    .sel-table th {
        padding: .6rem .875rem; text-align: left; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-secondary); border-bottom: 1px solid var(--border);
    }
    .sel-table td { padding: .7rem .875rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .sel-table tr:last-child td { border-bottom: none; }
    .sel-table tr:hover td { background: #f8fafc; }

    /* Badge */
    .badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .2rem .625rem; border-radius: 999px;
        font-size: .73rem; font-weight: 600; line-height: 1.4;
    }
    .badge-upcoming  { background: #fef9c3; color: #854d0e; }
    .badge-past      { background: #f1f5f9; color: #64748b; }
    .badge-today     { background: #dcfce7; color: #15803d; }
    .badge-active    { background: #dcfce7; color: #15803d; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; }

    /* Empty state */
    .empty-cell { text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: .84rem; }
    .empty-cell .material-symbols-outlined { font-size: 1.75rem; display: block; margin-bottom: .3rem; }

    .no-team { text-align: center; padding: 4rem 2rem; }
    .no-team .material-symbols-outlined { font-size: 3rem; color: var(--text-muted); display: block; margin-bottom: .75rem; }
    .no-team h3 { font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: .4rem; }
    .no-team p  { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* Setting info banner */
    .setting-banner {
        display: flex; align-items: center; gap: .875rem;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: var(--radius-md); padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .setting-banner .material-symbols-outlined { font-size: 1.5rem; color: var(--primary); flex-shrink: 0; font-variation-settings: 'FILL' 1; }
    .setting-banner-text strong { font-size: .95rem; color: var(--text-main); }
    .setting-banner-text p { font-size: .82rem; color: var(--text-secondary); margin: .1rem 0 0; }
</style>
@endpush

@section('content')

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">beach_access</span>
            Team Optional Holidays
        </h1>
        <p>View optional holidays selected by your team members.</p>
    </div>

    {{-- Year filter --}}
    <form method="GET" action="{{ route('manager.team-optional-holidays.index') }}" class="filter-bar">
        <label>Year:</label>
        <select name="year">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-filter">
            <span class="material-symbols-outlined">filter_list</span> Filter
        </button>
    </form>

    {{-- Setting banner --}}
    @if($setting)
        <div class="setting-banner">
            <span class="material-symbols-outlined">info</span>
            <div class="setting-banner-text">
                <strong>{{ $year }} Optional Holiday Limit: {{ $maxAllowed }} per employee (full eligibility)</strong>
                <p>{{ $setting->description ?: 'Employees may select up to ' . $maxAllowed . ' optional holidays this year.' }} Mid-year joiners get pro-rata eligibility.</p>
            </div>
        </div>
    @else
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:.84rem;color:#92400e;">
            <strong>⚠ No optional holiday setting configured for {{ $year }}.</strong>
            Ask the administrator to set it up.
        </div>
    @endif

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card" style="--stat-color:#137fec;">
            <div class="stat-icon" style="background:#eff6ff;">
                <span class="material-symbols-outlined" style="color:#137fec;font-variation-settings:'FILL' 1;">group</span>
            </div>
            <div>
                <div class="stat-label">Team Members</div>
                <div class="stat-value" style="color:#137fec;">{{ $teamSummary->count() }}</div>
            </div>
        </div>
        <div class="stat-card" style="--stat-color:#16a34a;">
            <div class="stat-icon" style="background:#dcfce7;">
                <span class="material-symbols-outlined" style="color:#16a34a;font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            <div>
                <div class="stat-label">Total Selected</div>
                <div class="stat-value" style="color:#16a34a;">{{ $selections->count() }}</div>
            </div>
        </div>
        <div class="stat-card" style="--stat-color:#d97706;">
            <div class="stat-icon" style="background:#fffbeb;">
                <span class="material-symbols-outlined" style="color:#d97706;font-variation-settings:'FILL' 1;">pending</span>
            </div>
            <div>
                <div class="stat-label">Avg. Used / Member</div>
                <div class="stat-value" style="color:#d97706;">
                    {{ $teamSummary->count() > 0 ? number_format($selections->count() / $teamSummary->count(), 1) : '0' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Team member cards --}}
    <div class="section-head">
        <h2>
            <span class="material-symbols-outlined">people</span>
            Team Members
        </h2>
        <span style="font-size:.8rem;color:var(--text-muted);">Click a member to expand selections</span>
    </div>

    @if($teamSummary->isEmpty())
        <div class="no-team">
            <span class="material-symbols-outlined">group_off</span>
            <h3>No Team Members</h3>
            <p>You have no direct team members assigned.</p>
        </div>
    @else
        <div class="member-cards">
            @foreach($teamSummary as $i => $row)
            @php
                $eligible = $row['eligible'];
                $pct      = $eligible > 0 ? min(100, ($row['used'] / $eligible) * 100) : 0;
                $initials = collect(explode(' ', $row['user']->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                $barColor = $row['used'] >= $eligible ? '#dc2626' : ($row['used'] > 0 ? '#16a34a' : '#94a3b8');
            @endphp
            <div class="member-card">
                {{-- Header (clickable) --}}
                <div class="mc-header" onclick="toggleMember({{ $i }})">
                    <div class="mc-user">
                        <div class="mc-avatar">{{ $initials }}</div>
                        <div>
                            <div class="mc-name">
                                {{ $row['user']->name }}
                                @if($row['isProRata'])
                                    <span style="font-size:.65rem;font-weight:700;background:#fef9c3;color:#854d0e;padding:.1rem .45rem;border-radius:999px;letter-spacing:.04em;margin-left:.35rem;vertical-align:middle;">PRO-RATA</span>
                                @endif
                            </div>
                            <div class="mc-dept">{{ $row['user']->department?->name ?? 'No Department' }} · {{ $row['user']->employee_code ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="mc-stats">
                        <div class="mc-stat-item">
                            <div class="mc-stat-val" style="color:#137fec;">
                                {{ $eligible }}@if($row['isProRata'])<span style="font-size:.75rem;opacity:.6;font-weight:600;">/{{ $row['max'] }}</span>@endif
                            </div>
                            <div class="mc-stat-lbl">Eligible</div>
                        </div>
                        <div class="mc-stat-item">
                            <div class="mc-stat-val" style="color:#d97706;">{{ $row['used'] }}</div>
                            <div class="mc-stat-lbl">Used</div>
                        </div>
                        <div class="mc-stat-item">
                            <div class="mc-stat-val" style="color:{{ $row['remaining'] > 0 ? '#16a34a' : '#94a3b8' }};">{{ $row['remaining'] }}</div>
                            <div class="mc-stat-lbl">Remaining</div>
                        </div>
                        <div class="mini-progress-wrap">
                            <div class="mini-prog-bar">
                                <div class="mini-prog-inner" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                            </div>
                            <span class="mini-prog-label" style="color:{{ $barColor }};">{{ $row['used'] }}/{{ $eligible }}</span>
                        </div>
                    </div>

                    <span class="material-symbols-outlined mc-chevron" id="chevron-{{ $i }}">expand_more</span>
                </div>

                {{-- Selections body --}}
                <div class="mc-body" id="body-{{ $i }}">
                    @if($row['selections']->isEmpty())
                        <div class="empty-cell">
                            <span class="material-symbols-outlined">beach_access</span>
                            No optional holidays selected yet.
                        </div>
                    @else
                        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;"><table class="sel-table">
                            <thead>
                                <tr>
                                    <th>Holiday</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Date Status</th>
                                    <th>Selection</th>
                                    <th>Selected On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($row['selections'] as $sel)
                                @php
                                    $hDate   = $sel->holiday?->date;
                                    $isPast  = $hDate && $hDate->toDateString() < now()->toDateString();
                                    $isToday = $hDate && $hDate->isToday();
                                    $isCancelled = $sel->status === 'cancelled';
                                @endphp
                                <tr style="{{ $isCancelled ? 'opacity:.6;' : '' }}">
                                    <td style="font-weight:600;">{{ $sel->holiday?->name ?? '—' }}</td>
                                    <td>{{ $hDate ? $hDate->format('d M Y') : '—' }}</td>
                                    <td style="color:var(--text-secondary);">{{ $hDate ? $hDate->format('l') : '—' }}</td>
                                    <td>
                                        @if($isToday)
                                            <span class="badge badge-today">Today</span>
                                        @elseif($isPast)
                                            <span class="badge badge-past">Past</span>
                                        @else
                                            <span class="badge badge-upcoming">Upcoming</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isCancelled)
                                            <span class="badge badge-cancelled">
                                                <span class="material-symbols-outlined" style="font-size:.8rem;">cancel</span>
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="badge badge-active">
                                                <span class="material-symbols-outlined" style="font-size:.8rem;">check_circle</span>
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td style="color:var(--text-secondary);font-size:.8rem;">
                                        {{ $sel->selected_at->format('d M Y, h:i A') }}
                                        @if($isCancelled && $sel->cancelled_at)
                                            <br><span style="color:#dc2626;">Cancelled: {{ $sel->cancelled_at->format('d M Y, h:i A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

@endsection

@push('scripts')
<script>
    function toggleMember(i) {
        const body    = document.getElementById('body-' + i);
        const chevron = document.getElementById('chevron-' + i);
        body.classList.toggle('open');
        chevron.classList.toggle('open');
    }
</script>
@endpush
