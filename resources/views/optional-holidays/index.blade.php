@extends('layouts.app')

@section('title', 'Optional Holidays')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* Summary bar */
    .summary-bar {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.75rem;
    }
    @media (max-width:600px) { .summary-bar { grid-template-columns: 1fr; } }

    .summary-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.125rem 1.375rem;
        display: flex; align-items: center; gap: 1rem;
        box-shadow: var(--shadow-sm);
        position: relative; overflow: hidden;
    }
    .summary-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; background: var(--sc-color, var(--primary));
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .sc-icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .sc-icon .material-symbols-outlined { font-size: 1.4rem; font-variation-settings: 'FILL' 1; }
    .sc-val { font-size: 1.75rem; font-weight: 900; letter-spacing: -.04em; line-height: 1; }
    .sc-lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .15rem; }

    /* Progress bar */
    .progress-wrap { margin-bottom: 1.75rem; }
    .progress-bar-outer {
        background: var(--bg-light); border-radius: 999px;
        height: 10px; overflow: hidden; border: 1px solid var(--border);
    }
    .progress-bar-inner {
        height: 100%; border-radius: 999px; transition: width .5s;
        background: linear-gradient(90deg, var(--primary) 0%, #22c55e 100%);
    }
    .progress-label {
        display: flex; justify-content: space-between;
        font-size: .8rem; color: var(--text-secondary); margin-bottom: .5rem;
    }

    /* Callouts */
    .callout {
        display: flex; gap: .75rem; align-items: flex-start;
        padding: .875rem 1rem; border-radius: var(--radius-sm);
        font-size: .84rem; line-height: 1.6; margin-bottom: 1.5rem; border: 1px solid;
    }
    .callout .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; margin-top: .1rem; }
    .callout-info  { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
    .callout-warn  { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .callout-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

    /* Flash */
    .flash {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem 1rem; border-radius: var(--radius-md);
        font-size: .875rem; font-weight: 500; margin-bottom: 1rem; border: 1px solid transparent;
    }
    .flash .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-close { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.1rem; opacity: .6; line-height: 1; }
    .flash-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .flash-error   { background: #fff1f2; color: #dc2626; border-color: #fecaca; }
    .flash-warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }

    /* Section header */
    .section-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem; padding-bottom: .875rem;
        border-bottom: 1px solid var(--border);
    }
    .section-head h2 { font-size: 1rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: .4rem; }
    .section-head h2 .material-symbols-outlined { color: var(--primary); font-size: 1.15rem; }

    /* Holiday cards grid */
    .holidays-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 1.125rem;
    }

    .h-card {
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex; flex-direction: column; gap: .875rem;
        box-shadow: var(--shadow-sm);
        transition: box-shadow .2s, transform .2s, border-color .2s;
        position: relative; overflow: hidden;
    }
    .h-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .h-card.selected {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .h-card.past {
        opacity: .65;
    }
    .h-card.full {
        opacity: .75;
    }

    /* Upcoming ribbon */
    .ribbon-upcoming {
        position: absolute; top: 12px; right: -22px;
        background: #f59e0b; color: #fff;
        font-size: .65rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; padding: .2rem 2.2rem;
        transform: rotate(35deg); box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .ribbon-selected {
        position: absolute; top: 12px; right: -22px;
        background: #16a34a; color: #fff;
        font-size: .65rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; padding: .2rem 2.2rem;
        transform: rotate(35deg); box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .ribbon-past {
        position: absolute; top: 12px; right: -22px;
        background: #94a3b8; color: #fff;
        font-size: .65rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; padding: .2rem 2.2rem;
        transform: rotate(35deg); box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .ribbon-cancelled {
        position: absolute; top: 12px; right: -22px;
        background: #ef4444; color: #fff;
        font-size: .65rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; padding: .2rem 2.2rem;
        transform: rotate(35deg); box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .h-card.cancelled { opacity: .7; }

    .h-date {
        display: flex; align-items: center; gap: .75rem;
    }
    .h-date-box {
        width: 52px; height: 52px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--primary) 0%, #0f6fd4 100%);
        color: #fff; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 3px 10px rgba(19,127,236,.3);
    }
    .h-card.selected .h-date-box {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        box-shadow: 0 3px 10px rgba(22,163,74,.3);
    }
    .h-date-day { font-size: 1.35rem; font-weight: 900; line-height: 1; }
    .h-date-mon { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; opacity: .85; }
    .h-date-meta { flex: 1; }
    .h-date-weekday { font-size: .78rem; color: var(--text-muted); }
    .h-date-days-away { font-size: .78rem; color: #d97706; font-weight: 600; }
    .h-date-days-away.past-label { color: var(--text-muted); }

    .h-name { font-size: 1rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .h-desc { font-size: .82rem; color: var(--text-secondary); line-height: 1.55; margin: 0; }

    .h-scope {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .75rem; color: var(--text-muted); font-weight: 500;
    }
    .h-scope .material-symbols-outlined { font-size: .9rem; }

    /* Action button */
    .btn-select {
        display: flex; align-items: center; justify-content: center; gap: .4rem;
        width: 100%; height: 2.375rem; border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer;
        transition: all .15s; border: none;
    }
    .btn-select.do-select {
        background: var(--primary); color: #fff;
    }
    .btn-select.do-select:hover { background: var(--primary-hover); }
    .btn-select.do-deselect {
        background: #dcfce7; color: #15803d; border: 1.5px solid #bbf7d0;
    }
    .btn-select.do-deselect:hover { background: #bbf7d0; }
    .btn-select:disabled {
        background: var(--bg-light); color: var(--text-muted);
        border: 1.5px solid var(--border); cursor: not-allowed;
    }
    .btn-select .material-symbols-outlined { font-size: 1rem; }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 4rem 2rem;
        background: var(--surface); border: 1.5px dashed var(--border);
        border-radius: var(--radius-md);
    }
    .empty-state .material-symbols-outlined { font-size: 3rem; color: var(--text-muted); display: block; margin-bottom: .75rem; }
    .empty-state h3 { font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: .4rem; }
    .empty-state p  { font-size: .875rem; color: var(--text-secondary); margin: 0; }
</style>
@endpush

@section('content')

    {{-- Flash --}}
    @foreach(['success','error','warning'] as $type)
    @if(session($type))
        <div class="flash flash-{{ $type }}" id="flash">
            <span class="material-symbols-outlined">{{ $type === 'success' ? 'check_circle' : ($type === 'error' ? 'error' : 'warning') }}</span>
            {{ session($type) }}
            <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif
    @endforeach

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">beach_access</span>
            Optional Holidays — {{ $year }}
        </h1>
        <p>
            Select optional holidays you wish to take this year.
            @if($isProRata)
                You joined mid-year, so your pro-rata eligibility is <strong>{{ $eligibleCount }}</strong> of {{ $maxAllowed }}.
            @else
                You can select up to <strong>{{ $eligibleCount }}</strong> optional holidays.
            @endif
        </p>
    </div>

    {{-- Summary bar --}}
    <div class="summary-bar">
        <div class="summary-card" style="--sc-color:#137fec;">
            <div class="sc-icon" style="background:#eff6ff;">
                <span class="material-symbols-outlined" style="color:#137fec;">event_available</span>
            </div>
            <div>
                <div class="sc-val" style="color:#137fec;">
                    {{ $eligibleCount }}@if($isProRata)<span style="font-size:1rem;font-weight:600;opacity:.6;"> / {{ $maxAllowed }}</span>@endif
                </div>
                <div class="sc-lbl">Eligible</div>
            </div>
        </div>
        <div class="summary-card" style="--sc-color:#d97706;">
            <div class="sc-icon" style="background:#fffbeb;">
                <span class="material-symbols-outlined" style="color:#d97706;">check_circle</span>
            </div>
            <div>
                <div class="sc-val" style="color:#d97706;">{{ $usedCount }}</div>
                <div class="sc-lbl">Used</div>
            </div>
        </div>
        <div class="summary-card" style="--sc-color:{{ $remaining > 0 ? '#16a34a' : '#94a3b8' }};">
            <div class="sc-icon" style="background:{{ $remaining > 0 ? '#dcfce7' : '#f1f5f9' }};">
                <span class="material-symbols-outlined" style="color:{{ $remaining > 0 ? '#16a34a' : '#94a3b8' }};">hourglass_empty</span>
            </div>
            <div>
                <div class="sc-val" style="color:{{ $remaining > 0 ? '#16a34a' : '#94a3b8' }};">{{ $remaining }}</div>
                <div class="sc-lbl">Remaining</div>
            </div>
        </div>
    </div>

    {{-- Pro-rata notice --}}
    @if($isProRata)
    <div class="callout callout-info" style="margin-bottom:1rem;">
        <span class="material-symbols-outlined">calculate</span>
        <span>
            <strong>Pro-rata eligibility applied.</strong>
            Based on your joining month, you are eligible for <strong>{{ $eligibleCount }}</strong> of {{ $maxAllowed }} optional holidays for {{ $year }}.
            From next year you will have full eligibility ({{ $maxAllowed }}).
        </span>
    </div>
    @endif

    {{-- Progress --}}
    @if($eligibleCount > 0)
    <div class="progress-wrap">
        <div class="progress-label">
            <span>{{ $usedCount }} of {{ $eligibleCount }} optional holiday{{ $eligibleCount !== 1 ? 's' : '' }} used</span>
            <span>{{ $remaining }} remaining</span>
        </div>
        <div class="progress-bar-outer">
            <div class="progress-bar-inner" style="width:{{ min(100, ($eligibleCount > 0 ? ($usedCount / $eligibleCount) * 100 : 0)) }}%;
                background:{{ $usedCount >= $eligibleCount ? 'linear-gradient(90deg,#d97706,#dc2626)' : 'linear-gradient(90deg,var(--primary),#22c55e)' }};"></div>
        </div>
    </div>
    @endif

    {{-- No setting configured --}}
    @if(! $setting)
        <div class="callout callout-warn">
            <span class="material-symbols-outlined">warning</span>
            <span>Optional holiday selection is <strong>not configured</strong> for {{ $year }}. Please ask your administrator to set up the optional holiday limit.</span>
        </div>
    @elseif($remaining === 0 && $usedCount > 0)
        <div class="callout callout-success">
            <span class="material-symbols-outlined">check_circle</span>
            <span>You have selected all <strong>{{ $eligibleCount }}</strong> optional holidays you are eligible for in {{ $year }}. No more selections can be made.</span>
        </div>
    @else
        <div class="callout callout-info">
            <span class="material-symbols-outlined">info</span>
            <span>You may select <strong>{{ $remaining }}</strong> more optional holiday{{ $remaining !== 1 ? 's' : '' }} for {{ $year }}. Past holidays cannot be selected.</span>
        </div>
    @endif

    {{-- Holidays list --}}
    <div class="section-head">
        <h2>
            <span class="material-symbols-outlined">calendar_month</span>
            Available Optional Holidays ({{ $holidays->count() }})
        </h2>
        <span style="font-size:.8rem;color:var(--text-muted);">{{ $year }}</span>
    </div>

    @if($holidays->isEmpty())
        <div class="empty-state">
            <span class="material-symbols-outlined">beach_access</span>
            <h3>No Optional Holidays Defined</h3>
            <p>No optional holidays have been set up for {{ $year }}. Check back later or contact your administrator.</p>
        </div>
    @else
        <div class="holidays-grid">
            @foreach($holidays as $h)
            @php
                $daysAway    = now()->startOfDay()->diffInDays($h->date, false);
                $isPast      = $h->is_past;       // today or earlier
                $isSel       = $h->is_selected;   // active selection
                $isCancelled = $h->is_cancelled;  // previously cancelled
                $isFull      = !$isSel && ($remaining <= 0 || $usedCount >= $eligibleCount);
            @endphp
            <div class="h-card {{ $isSel ? 'selected' : '' }} {{ $isPast ? 'past' : '' }} {{ $isFull ? 'full' : '' }} {{ $isCancelled && !$isSel ? 'cancelled' : '' }}">

                {{-- Ribbon --}}
                @if($isSel)
                    <div class="ribbon-selected">Selected</div>
                @elseif($isCancelled && $isPast)
                    <div class="ribbon-past">Cancelled</div>
                @elseif($isCancelled)
                    <div class="ribbon-cancelled">Cancelled</div>
                @elseif($isPast)
                    <div class="ribbon-past">Past</div>
                @elseif($daysAway <= 14 && $daysAway >= 0)
                    <div class="ribbon-upcoming">Upcoming</div>
                @endif

                {{-- Date box + meta --}}
                <div class="h-date">
                    <div class="h-date-box">
                        <div class="h-date-day">{{ $h->date->format('d') }}</div>
                        <div class="h-date-mon">{{ $h->date->format('M') }}</div>
                    </div>
                    <div class="h-date-meta">
                        <div class="h-date-weekday">{{ $h->date->format('l, Y') }}</div>
                        @if($isPast)
                            <div class="h-date-days-away past-label">Past</div>
                        @elseif($daysAway === 0)
                            <div class="h-date-days-away">Today!</div>
                        @elseif($daysAway > 0)
                            <div class="h-date-days-away">{{ $daysAway }} day{{ $daysAway !== 1 ? 's' : '' }} away</div>
                        @endif
                    </div>
                </div>

                {{-- Name & desc --}}
                <div>
                    <p class="h-name">{{ $h->name }}</p>
                    @if($h->description)
                        <p class="h-desc" style="margin-top:.3rem;">{{ $h->description }}</p>
                    @endif
                </div>

                {{-- Scope --}}
                <div>
                    <span class="h-scope">
                        <span class="material-symbols-outlined">{{ $h->scope === 'all' ? 'domain' : 'corporate_fare' }}</span>
                        {{ $h->getScopeLabel() }}
                    </span>
                </div>

                {{-- Action --}}
                @if($isSel)
                    @if($isPast)
                        {{-- Today or past: cannot cancel --}}
                        <button class="btn-select" disabled title="Cannot cancel: holiday is today or already passed">
                            <span class="material-symbols-outlined">check_circle</span>
                            Selected (Locked)
                        </button>
                    @else
                        <form method="POST" action="{{ route('optional-holidays.deselect') }}">
                            @csrf
                            <input type="hidden" name="holiday_id" value="{{ $h->id }}">
                            <button type="submit" class="btn-select do-deselect"
                                    onclick="return confirm('Remove &quot;{{ $h->name }}&quot; from your optional holidays?')">
                                <span class="material-symbols-outlined">remove_circle</span>
                                Remove Selection
                            </button>
                        </form>
                    @endif
                @elseif($isCancelled && ! $isPast && $setting && ! $isFull)
                    {{-- Previously cancelled, can re-select if future and quota available --}}
                    <form method="POST" action="{{ route('optional-holidays.select') }}">
                        @csrf
                        <input type="hidden" name="holiday_id" value="{{ $h->id }}">
                        <button type="submit" class="btn-select do-select">
                            <span class="material-symbols-outlined">restart_alt</span>
                            Re-select Holiday
                        </button>
                    </form>
                @elseif($isPast)
                    <button class="btn-select" disabled>
                        <span class="material-symbols-outlined">event_busy</span>
                        Past Holiday
                    </button>
                @elseif(! $setting)
                    <button class="btn-select" disabled>Not Configured</button>
                @elseif($isFull)
                    <button class="btn-select" disabled>
                        <span class="material-symbols-outlined">block</span>
                        Limit Reached
                    </button>
                @else
                    <form method="POST" action="{{ route('optional-holidays.select') }}">
                        @csrf
                        <input type="hidden" name="holiday_id" value="{{ $h->id }}">
                        <button type="submit" class="btn-select do-select">
                            <span class="material-symbols-outlined">add_circle</span>
                            Select This Holiday
                        </button>
                    </form>
                @endif

            </div>
            @endforeach
        </div>
    @endif

@endsection

@push('scripts')
<script>
    setTimeout(() => {
        const f = document.getElementById('flash');
        if (f) { f.style.transition = 'opacity .4s'; f.style.opacity = '0'; setTimeout(() => f.remove(), 400); }
    }, 4000);
</script>
@endpush
