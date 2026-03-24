@extends('layouts.app')

@section('title', 'Holiday List')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }
    .page-header-row {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        flex-wrap: wrap;
    }

    /* Stat cards */
    .stat-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: .875rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .stat-grid { grid-template-columns: 1fr; } }

    .stat-card-h {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.25rem;
        display: flex; align-items: center; gap: 1rem;
        box-shadow: var(--shadow-sm);
    }
    .stat-icon-h {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; flex-shrink: 0;
    }
    .stat-val-h { font-size: 1.75rem; font-weight: 800; line-height: 1; letter-spacing: -.03em; }
    .stat-lbl-h { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .2rem; }

    .s-blue   { background: #eff6ff; color: #3b82f6; }
    .s-green  { background: #f0fdf4; color: #22c55e; }
    .s-orange { background: #fff7ed; color: #f97316; }
    .s-purple { background: #f5f3ff; color: #8b5cf6; }
    .v-blue   { color: #3b82f6; }
    .v-green  { color: #22c55e; }
    .v-orange { color: #f97316; }
    .v-purple { color: #8b5cf6; }

    /* Filter bar */
    .filter-bar {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1rem 1.25rem;
        margin-bottom: 1.25rem; box-shadow: var(--shadow-sm);
    }
    .filter-row {
        display: flex; align-items: flex-end; gap: .75rem; flex-wrap: wrap;
    }
    .filter-group { display: flex; flex-direction: column; gap: .35rem; flex: 1; min-width: 160px; }
    .filter-group label { font-size: .75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
    .filter-group input,
    .filter-group select {
        height: 2.25rem; border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; padding: 0 .75rem; color: var(--text-main);
        background: var(--surface); outline: none; transition: border-color .15s;
        width: 100%;
    }
    .filter-group input:focus,
    .filter-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .filter-actions { display: flex; gap: .5rem; align-items: flex-end; flex-shrink: 0; }

    .btn-filter {
        height: 2.25rem; padding: 0 1rem; background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: .84rem; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: .3rem; white-space: nowrap;
        transition: background .15s; text-decoration: none;
    }
    .btn-filter:hover { background: var(--primary-hover); color: #fff; }
    .btn-filter .material-symbols-outlined { font-size: 1rem; }

    .btn-reset {
        height: 2.25rem; padding: 0 .875rem; background: var(--surface); color: var(--text-secondary);
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: .84rem; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: .3rem; transition: all .15s;
        text-decoration: none; white-space: nowrap;
    }
    .btn-reset:hover { background: var(--bg-light); color: var(--text-main); }
    .btn-reset .material-symbols-outlined { font-size: 1rem; }

    /* Main card */
    .holiday-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .holiday-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b;
    }
    .holiday-card-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: #fff; }
    .holiday-count {
        background: rgba(255,255,255,.12); color: #e2e8f0;
        border-radius: 20px; padding: .15rem .65rem; font-size: .75rem; font-weight: 600;
    }

    .holiday-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .holiday-table thead th {
        padding: .625rem 1rem; text-align: left;
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--text-muted);
        border-bottom: 1px solid var(--border); background: #fafbfd; white-space: nowrap;
    }
    .holiday-table tbody tr { transition: background .1s; }
    .holiday-table tbody tr:hover { background: rgba(0,0,0,.025); }
    .holiday-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .holiday-table tbody tr:last-child td { border-bottom: none; }

    .date-cell { font-weight: 600; color: var(--text-main); white-space: nowrap; }
    .date-sub  { font-size: .75rem; color: var(--text-muted); margin-top: .1rem; }

    .holiday-name { font-weight: 600; color: var(--text-main); }
    .holiday-desc { font-size: .76rem; color: var(--text-muted); margin-top: .15rem; }

    /* Type badges */
    .badge-national {
        display: inline-flex; align-items: center; gap: .25rem;
        background: #fef9c3; color: #a16207; border: 1px solid #fde68a;
        padding: .2rem .6rem; border-radius: 20px; font-size: .775rem; font-weight: 700;
    }
    .badge-optional {
        display: inline-flex; align-items: center; gap: .25rem;
        background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;
        padding: .2rem .6rem; border-radius: 20px; font-size: .775rem; font-weight: 700;
    }
    .badge-national .material-symbols-outlined,
    .badge-optional .material-symbols-outlined { font-size: .85rem; }

    /* Scope badge */
    .scope-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #f1f5f9; color: var(--text-secondary); border: 1px solid var(--border);
        padding: .2rem .65rem; border-radius: 6px; font-size: .775rem; font-weight: 600;
    }

    /* Status dot */
    .status-active   { display:inline-flex; align-items:center; gap:.35rem; font-size:.775rem; font-weight:600; color:#15803d; }
    .status-inactive { display:inline-flex; align-items:center; gap:.35rem; font-size:.775rem; font-weight:600; color:#64748b; }
    .status-dot { width:7px; height:7px; border-radius:50%; }
    .dot-active   { background:#22c55e; }
    .dot-inactive { background:#94a3b8; }

    /* Action buttons */
    .btn-edit {
        display: inline-flex; align-items: center; gap: .25rem;
        height: 1.875rem; padding: 0 .75rem;
        background: transparent; color: var(--primary);
        border: 1.5px solid var(--primary); border-radius: var(--radius-sm);
        font-size: .8rem; font-weight: 600; text-decoration: none;
        cursor: pointer; transition: all .15s;
    }
    .btn-edit:hover { background: var(--primary); color: #fff; }
    .btn-edit .material-symbols-outlined { font-size: .9rem; }

    .btn-del {
        display: inline-flex; align-items: center; gap: .25rem;
        height: 1.875rem; padding: 0 .75rem;
        background: transparent; color: #dc2626;
        border: 1.5px solid #fca5a5; border-radius: var(--radius-sm);
        font-size: .8rem; font-weight: 600;
        cursor: pointer; transition: all .15s;
    }
    .btn-del:hover { background: #fef2f2; border-color: #dc2626; }
    .btn-del .material-symbols-outlined { font-size: .9rem; }

    /* Add button */
    .btn-add {
        display: inline-flex; align-items: center; gap: .4rem;
        height: 2.25rem; padding: 0 1.125rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; text-decoration: none;
        cursor: pointer; transition: background .15s; white-space: nowrap;
        flex-shrink: 0;
    }
    .btn-add:hover { background: var(--primary-hover); color: #fff; }
    .btn-add .material-symbols-outlined { font-size: 1rem; }

    /* Alert */
    .alert-success {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0;
        border-radius: var(--radius-sm); color: #15803d; font-size: .84rem;
        margin-bottom: 1.25rem;
    }
    .alert-success .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }

    /* Past date row */
    .row-past td { opacity: .6; }
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="page-header-row">
        <div class="page-header">
            <h1>
                <span class="material-symbols-outlined" style="font-size:1.4rem; color:var(--primary); font-variation-settings:'FILL' 1;">calendar_month</span>
                Holiday List
            </h1>
            <p>Manage national and department-specific holidays.</p>
        </div>
        <a href="{{ route('admin.holidays.create') }}" class="btn-add">
            <span class="material-symbols-outlined">add</span>
            + Add Holiday
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card-h">
            <div class="stat-icon-h s-blue">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">calendar_month</span>
            </div>
            <div>
                <div class="stat-val-h v-blue">{{ $stats['total'] }}</div>
                <div class="stat-lbl-h">Total Holidays</div>
            </div>
        </div>
        <div class="stat-card-h">
            <div class="stat-icon-h s-green">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">today</span>
            </div>
            <div>
                <div class="stat-val-h v-green">{{ $stats['today'] }}</div>
                <div class="stat-lbl-h">Today</div>
            </div>
        </div>
        <div class="stat-card-h">
            <div class="stat-icon-h s-orange">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">upcoming</span>
            </div>
            <div>
                <div class="stat-val-h v-orange">{{ $stats['next30'] }}</div>
                <div class="stat-lbl-h">Next 30 Days</div>
            </div>
        </div>
        <div class="stat-card-h">
            <div class="stat-icon-h s-purple">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">corporate_fare</span>
            </div>
            <div>
                <div class="stat-val-h v-purple">{{ $stats['departments'] }}</div>
                <div class="stat-lbl-h">Departments</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.holidays.index') }}" id="filterForm">
            <div class="filter-row">
                <div class="filter-group" style="min-width:200px;">
                    <label>Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Holiday name...">
                </div>
                <div class="filter-group" style="max-width:130px;">
                    <label>Year</label>
                    <select name="year">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="max-width:150px;">
                    <label>Type</label>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="national"  {{ request('type') === 'national'  ? 'selected' : '' }}>National</option>
                        <option value="optional"  {{ request('type') === 'optional'  ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>
                <div class="filter-group" style="max-width:200px;">
                    <label>Department</label>
                    <select name="department">
                        <option value="all">All</option>
                        @foreach($departments as $dept)
                            @php $encDeptId = \App\Helpers\IdCrypt::encode($dept->id); @endphp
                            <option value="{{ $encDeptId }}" {{ request('department') === $encDeptId ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <span class="material-symbols-outlined">search</span>
                        Filter
                    </button>
                    <a href="{{ route('admin.holidays.index') }}" class="btn-reset">
                        <span class="material-symbols-outlined">refresh</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="holiday-card">
        <div class="holiday-card-header">
            <h5>Holiday List</h5>
            <span class="holiday-count">{{ $holidays->count() }} holidays</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="holiday-table">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Date</th>
                        <th>Holiday Name</th>
                        <th>Day</th>
                        <th>Type</th>
                        <th>Scope</th>
                        <th>Note</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $i => $holiday)
                        @php $isPast = $holiday->date->isPast() && !$holiday->date->isToday(); @endphp
                        <tr class="{{ $isPast ? 'row-past' : '' }}">
                            <td style="color:var(--text-muted); font-size:.8rem;">{{ $i + 1 }}</td>
                            <td>
                                <div class="date-cell">{{ $holiday->date->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="holiday-name">{{ $holiday->name }}</div>
                            </td>
                            <td style="color:var(--text-secondary); font-size:.83rem;">{{ $holiday->day_name }}</td>
                            <td>
                                @if($holiday->type === 'national')
                                    <span class="badge-national">
                                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">flag</span>
                                        National
                                    </span>
                                @else
                                    <span class="badge-optional">
                                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span>
                                        Optional
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="scope-badge">{{ $holiday->getScopeLabel() }}</span>
                            </td>
                            <td style="color:var(--text-muted); font-size:.8rem; max-width:180px;">
                                {{ $holiday->description ? \Illuminate\Support\Str::limit($holiday->description, 50) : '—' }}
                            </td>
                            <td>
                                @if($holiday->status)
                                    <span class="status-active">
                                        <span class="status-dot dot-active"></span> Active
                                    </span>
                                @else
                                    <span class="status-inactive">
                                        <span class="status-dot dot-inactive"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:.375rem;">
                                    <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn-edit">
                                        <span class="material-symbols-outlined">edit</span>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}"
                                          onsubmit="return confirm('Delete holiday \'{{ addslashes($holiday->name) }}\'?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">
                                            <span class="material-symbols-outlined">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                                No holidays found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
