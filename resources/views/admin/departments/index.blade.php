@extends('layouts.app')

@section('title', 'Departments')

@push('styles')
<style>
    /* ── Page header ── */
    .dept-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .dept-header-left { display: flex; align-items: center; gap: .75rem; }
    .dept-header-icon {
        width: 40px; height: 40px;
        background: rgba(19,127,236,.1);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .dept-header-icon .material-symbols-outlined { color: var(--primary); font-size: 1.25rem; }
    .dept-header-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.2; }
    .dept-header-sub   { font-size: .8125rem; color: var(--text-muted); margin: .15rem 0 0; }

    .btn-add-dept {
        height: 2.5rem;
        padding: 0 1.25rem;
        background: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        color: #fff;
        font-size: .875rem;
        font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
        transition: background .15s, box-shadow .15s;
    }
    .btn-add-dept:hover { background: var(--primary-hover); color: #fff; box-shadow: 0 4px 14px rgba(19,127,236,.4); }
    .btn-add-dept .material-symbols-outlined { font-size: 1.125rem; }

    /* ── Stat cards ── */
    .dept-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 991px) { .dept-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .dept-stats { grid-template-columns: 1fr 1fr; } }

    .dstat-card {
        border-radius: 14px;
        padding: 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }
    .dstat-card.blue   { background: #eff6ff; border-color: #dbeafe; }
    .dstat-card.green  { background: #f0fdf4; border-color: #dcfce7; }
    .dstat-card.yellow { background: #fefce8; border-color: #fef9c3; }
    .dstat-card.purple { background: #faf5ff; border-color: #ede9fe; }

    .dstat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .dstat-card.blue   .dstat-icon { background: #2563eb; }
    .dstat-card.green  .dstat-icon { background: #16a34a; }
    .dstat-card.yellow .dstat-icon { background: #d97706; }
    .dstat-card.purple .dstat-icon { background: #7c3aed; }
    .dstat-icon .material-symbols-outlined { color: #fff; font-size: 1.375rem; font-variation-settings: 'FILL' 1; }

    .dstat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    .dstat-card.blue   .dstat-value { color: #1d4ed8; }
    .dstat-card.green  .dstat-value { color: #15803d; }
    .dstat-card.yellow .dstat-value { color: #92400e; }
    .dstat-card.purple .dstat-value { color: #6d28d9; }

    .dstat-label {
        font-size: .6875rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .dstat-card.blue   .dstat-label { color: #3b82f6; }
    .dstat-card.green  .dstat-label { color: #22c55e; }
    .dstat-card.yellow .dstat-label { color: #f59e0b; }
    .dstat-card.purple .dstat-label { color: #a855f7; }

    /* ── Table card ── */
    .dept-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .dept-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: #1e2a3b;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .dept-card-header-title {
        font-size: .9375rem;
        font-weight: 700;
        color: #fff;
    }
    .dept-count-pill {
        font-size: .75rem;
        font-weight: 600;
        color: #1e2a3b;
        background: #fff;
        border-radius: 999px;
        padding: .2rem .75rem;
        white-space: nowrap;
    }

    /* ── Table ── */
    .dept-table { width: 100%; border-collapse: collapse; }
    .dept-table thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
    .dept-table th {
        padding: .75rem 1.25rem;
        font-size: .6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-muted);
        text-align: left;
        white-space: nowrap;
    }
    .dept-table td {
        padding: 1rem 1.25rem;
        font-size: .875rem;
        color: var(--text-main);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .dept-table tbody tr:last-child td { border-bottom: none; }
    .dept-table tbody tr:hover { background: #fafbfd; }

    .row-num { font-size: .8125rem; color: var(--text-muted); font-weight: 600; }

    .dept-name-main { font-weight: 700; color: var(--text-main); font-size: .9rem; }
    .dept-name-sub  { font-size: .775rem; color: var(--text-muted); margin-top: .1rem; }

    /* Code pill */
    .code-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .2rem .6rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: .04em;
        min-width: 36px;
    }

    /* Saturday rule badge */
    .rule-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .25rem .75rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .rule-none   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .rule-active { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .rule-badge .material-symbols-outlined { font-size: .85rem; font-variation-settings: 'FILL' 1; }

    /* Employee count */
    .emp-count { font-weight: 600; color: var(--primary); font-size: .875rem; }

    /* Status badge */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: .3125rem;
        font-size: .8125rem;
        font-weight: 600;
    }
    .badge-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .badge-active .badge-dot   { background: #22c55e; }
    .badge-inactive .badge-dot { background: #94a3b8; }
    .badge-active   { color: #15803d; }
    .badge-inactive { color: #64748b; }

    /* Action buttons */
    .action-group { display: flex; align-items: center; gap: .5rem; }

    .btn-edit {
        height: 2.125rem;
        padding: 0 .875rem;
        background: var(--primary);
        border: none;
        border-radius: 7px;
        color: #fff;
        font-size: .8rem;
        font-weight: 600;
        display: inline-flex; align-items: center; gap: .3rem;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap;
    }
    .btn-edit:hover { background: var(--primary-hover); color: #fff; }
    .btn-edit .material-symbols-outlined { font-size: .9rem; }

    .btn-del {
        width: 34px; height: 34px;
        border-radius: 7px;
        border: 1.5px solid #fca5a5;
        background: #fff5f5;
        color: #ef4444;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all .15s;
    }
    .btn-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
    .btn-del .material-symbols-outlined { font-size: 1rem; }

    /* Flash */
    .flash-success {
        display: flex; align-items: center; gap: .5rem;
        background: rgba(22,163,74,.07);
        border: 1px solid rgba(22,163,74,.25);
        border-left: 3px solid #22c55e;
        border-radius: var(--radius-sm);
        padding: .75rem 1rem;
        font-size: .875rem;
        color: #15803d;
        margin-bottom: 1.25rem;
    }
    .flash-success .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }

    /* Empty state */
    .empty-state { text-align: center; padding: 3.5rem 1rem; }
    .empty-state .material-symbols-outlined { font-size: 3rem; color: #cbd5e1; margin-bottom: .75rem; display: block; }
    .empty-state p { font-size: .9rem; color: var(--text-secondary); margin: 0; }
    .empty-state a { color: var(--primary); font-weight: 600; text-decoration: none; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="dept-header">
        <div class="dept-header-left">
            <div class="dept-header-icon">
                <span class="material-symbols-outlined">corporate_fare</span>
            </div>
            <div>
                <div class="dept-header-title">Departments</div>
                <div class="dept-header-sub">Manage company departments and Saturday leave rules</div>
            </div>
        </div>
        <a href="{{ route('admin.departments.create') }}" class="btn-add-dept">
            <span class="material-symbols-outlined">add</span>
            Add Department
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="dept-stats">
        <div class="dstat-card blue">
            <div class="dstat-icon">
                <span class="material-symbols-outlined">corporate_fare</span>
            </div>
            <div class="dstat-value">{{ $stats['total'] }}</div>
            <div class="dstat-label">Total Departments</div>
        </div>
        <div class="dstat-card green">
            <div class="dstat-icon">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            <div class="dstat-value">{{ $stats['active'] }}</div>
            <div class="dstat-label">Active</div>
        </div>
        <div class="dstat-card yellow">
            <div class="dstat-icon">
                <span class="material-symbols-outlined">calendar_month</span>
            </div>
            <div class="dstat-value">{{ $stats['with_saturday'] }}</div>
            <div class="dstat-label">With Saturday Rule</div>
        </div>
        <div class="dstat-card purple">
            <div class="dstat-icon">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div class="dstat-value">{{ $stats['total_assigned'] }}</div>
            <div class="dstat-label">Total Assigned</div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="dept-card">
        <div class="dept-card-header">
            <span class="dept-card-header-title">All Departments</span>
            <span class="dept-count-pill">{{ $departments->count() }} departments</span>
        </div>

        @if($departments->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">corporate_fare</span>
                <p>No departments yet. <a href="{{ route('admin.departments.create') }}">Add the first one.</a></p>
            </div>
        @else
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;"><table class="dept-table">
                <thead>
                    <tr>
                        <th style="width:48px">#</th>
                        <th>Department</th>
                        <th style="width:100px">Code</th>
                        <th style="width:180px">Saturday Rule</th>
                        <th style="width:120px">Employees</th>
                        <th style="width:100px">Status</th>
                        <th style="width:130px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $i => $dept)
                    <tr>
                        <td><span class="row-num">{{ $i + 1 }}</span></td>
                        <td>
                            <div class="dept-name-main">{{ $dept->name }}</div>
                            @if($dept->description)
                                <div class="dept-name-sub">{{ $dept->description }}</div>
                            @endif
                        </td>
                        <td>
                            @if($dept->code)
                                <span class="code-pill">{{ strtoupper($dept->code) }}</span>
                            @else
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($dept->saturday_rule && $dept->saturday_rule !== 'none')
                                <span class="rule-badge rule-active">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                    {{ $dept->saturdayRuleLabel() }}
                                </span>
                            @else
                                <span class="rule-badge rule-none">— No Rule</span>
                            @endif
                        </td>
                        <td>
                            <span class="emp-count">{{ $dept->employees_count }} employees</span>
                        </td>
                        <td>
                            <span class="badge-status {{ $dept->isActive() ? 'badge-active' : 'badge-inactive' }}">
                                <span class="badge-dot"></span>
                                {{ $dept->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.departments.edit', $dept) }}" class="btn-edit">
                                    <span class="material-symbols-outlined">edit</span>
                                    Edit
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.departments.destroy', $dept) }}"
                                      style="display:contents;"
                                      onsubmit="return confirm('Delete department «{{ $dept->name }}»? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-del" title="Delete">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

@endsection
