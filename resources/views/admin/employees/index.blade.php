@extends('layouts.app')

@section('title', 'Employees')

@push('styles')
<style>
    /* ── Page header ── */
    .emp-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 1rem;
        margin-bottom: 1.5rem; flex-wrap: wrap;
    }
    .emp-header-left { display: flex; align-items: center; gap: .75rem; }
    .emp-header-icon {
        width: 40px; height: 40px; background: rgba(19,127,236,.1);
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
    }
    .emp-header-icon .material-symbols-outlined { color: var(--primary); font-size: 1.25rem; }
    .emp-header-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.2; }
    .emp-header-sub   { font-size: .8125rem; color: var(--text-muted); margin: .15rem 0 0; }

    .btn-add-emp {
        height: 2.5rem; padding: 0 1.25rem;
        background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .875rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none; white-space: nowrap;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
        transition: background .15s;
    }
    .btn-add-emp:hover { background: var(--primary-hover); color: #fff; }
    .btn-add-emp .material-symbols-outlined { font-size: 1.1rem; }

    /* ── KPI Cards ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: .875rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1199px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 767px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 479px)  { .kpi-grid { grid-template-columns: 1fr 1fr; } }

    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.125rem;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
    }
    .kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        background: var(--kpi-color, var(--primary));
    }
    .kpi-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
    .kpi-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--kpi-bg, rgba(19,127,236,.1));
    }
    .kpi-icon .material-symbols-outlined {
        font-size: 1.25rem; color: var(--kpi-color, var(--primary));
        font-variation-settings: 'FILL' 1;
    }
    .kpi-value { font-size: 2rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; color: var(--text-main); }
    .kpi-label { font-size: .775rem; color: var(--text-muted); font-weight: 500; margin-top: .25rem; }

    /* ── Filter bar ── */
    .filter-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .filter-search {
        flex: 1; min-width: 220px;
        position: relative;
    }
    .filter-search .material-symbols-outlined {
        position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
        font-size: 1.1rem; color: var(--text-muted); pointer-events: none;
    }
    .filter-search input {
        width: 100%; height: 2.375rem;
        padding: 0 .875rem 0 2.375rem;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: .875rem; background: #f8fafc;
        transition: border-color .2s;
    }
    .filter-search input:focus { border-color: var(--primary); outline: none; background: #fff; }

    .filter-select {
        height: 2.375rem; padding: 0 2rem 0 .75rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .8125rem; color: var(--text-main); background: #f8fafc;
        cursor: pointer; min-width: 130px;
        transition: border-color .2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .625rem center;
    }
    .filter-select:focus { border-color: var(--primary); outline: none; }

    .btn-filter-reset {
        height: 2.375rem; padding: 0 1rem;
        background: transparent; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        font-size: .8125rem; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: .25rem;
        text-decoration: none; transition: all .15s;
    }
    .btn-filter-reset:hover { border-color: #94a3b8; color: var(--text-main); }
    .btn-filter-reset .material-symbols-outlined { font-size: 1rem; }

    /* ── Employee table card ── */
    .emp-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .emp-card-header {
        display: flex; align-items: center;
        justify-content: space-between; gap: 1rem;
        padding: 1rem 1.5rem;
        background: #1e2a3b;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .emp-card-header-title { font-size: .9375rem; font-weight: 700; color: #fff; }
    .emp-count-pill {
        font-size: .75rem; font-weight: 600;
        color: #1e2a3b; background: #fff;
        border-radius: 999px; padding: .2rem .75rem;
    }

    /* ── Table ── */
    .emp-table { width: 100%; border-collapse: collapse; }
    .emp-table thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
    .emp-table th {
        padding: .75rem 1.25rem;
        font-size: .6875rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--text-muted); text-align: left; white-space: nowrap;
    }
    .emp-table td {
        padding: .9375rem 1.25rem;
        font-size: .875rem; color: var(--text-main);
        border-bottom: 1px solid var(--border); vertical-align: middle;
    }
    .emp-table tbody tr:last-child td { border-bottom: none; }
    .emp-table tbody tr:hover { background: #fafbfd; }

    /* Avatar */
    .emp-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .8125rem; font-weight: 700; color: #fff; flex-shrink: 0;
        overflow: hidden;
    }
    .emp-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .emp-info { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .emp-name { font-weight: 600; font-size: .875rem; color: var(--text-main); }
    .emp-email { font-size: .75rem; color: var(--text-muted); margin-top: .1rem; }

    /* Employee ID pill */
    .emp-id-pill {
        display: inline-block; padding: .15rem .6rem;
        background: #f1f5f9; border: 1px solid #e2e8f0;
        border-radius: 6px; font-size: .75rem; font-weight: 700;
        color: #475569; letter-spacing: .04em;
    }

    /* Department */
    .dept-label { font-size: .8125rem; color: var(--text-secondary); }

    /* Role badge */
    .role-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .25rem .6rem; border-radius: 999px;
        font-size: .75rem; font-weight: 600; white-space: nowrap;
    }
    .role-manager  { background: rgba(124,58,237,.1); color: #6d28d9; }
    .role-employee { background: rgba(19,127,236,.1);  color: #1d4ed8; }

    /* Status badge */
    .status-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .8125rem; font-weight: 600;
    }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .status-active   .status-dot { background: #22c55e; }
    .status-inactive .status-dot { background: #94a3b8; }
    .status-active   { color: #15803d; }
    .status-inactive { color: #64748b; }

    /* Action buttons */
    .action-group { display: flex; align-items: center; gap: .4rem; }
    .btn-view {
        height: 2rem; padding: 0 .75rem;
        background: rgba(19,127,236,.08);
        border: 1.5px solid rgba(19,127,236,.2);
        border-radius: 6px; color: var(--primary);
        font-size: .775rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .25rem;
        text-decoration: none; transition: all .15s;
    }
    .btn-view:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .btn-view .material-symbols-outlined { font-size: .9rem; }

    .btn-edit-emp {
        height: 2rem; padding: 0 .75rem;
        background: var(--primary); border: none;
        border-radius: 6px; color: #fff;
        font-size: .775rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .25rem;
        text-decoration: none; transition: background .15s;
    }
    .btn-edit-emp:hover { background: var(--primary-hover); color: #fff; }
    .btn-edit-emp .material-symbols-outlined { font-size: .9rem; }

    /* Flash */
    .flash-success {
        display: flex; align-items: center; gap: .5rem;
        background: rgba(22,163,74,.07); border: 1px solid rgba(22,163,74,.25);
        border-left: 3px solid #22c55e; border-radius: var(--radius-sm);
        padding: .75rem 1rem; font-size: .875rem; color: #15803d;
        margin-bottom: 1.25rem;
    }
    .flash-success .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }

    /* Empty state */
    .empty-state { text-align: center; padding: 3.5rem 1rem; }
    .empty-state .material-symbols-outlined { font-size: 3rem; color: #cbd5e1; margin-bottom: .75rem; display: block; }
    .empty-state p { font-size: .9rem; color: var(--text-secondary); margin: 0; }

    /* Pagination */
    .pagination-wrap {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
    }
    .pagination-info { font-size: .8125rem; color: var(--text-muted); }
    .pagination { margin: 0; gap: .25rem; }
    .page-link {
        width: 34px; height: 34px; padding: 0;
        display: flex; align-items: center; justify-content: center;
        border-radius: var(--radius-sm); font-size: .8125rem; font-weight: 500;
        border: 1.5px solid var(--border); color: var(--text-secondary);
        transition: all .15s;
    }
    .page-link:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-subtle); }
    .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
    .page-item.disabled .page-link { opacity: .4; pointer-events: none; }
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="emp-header">
        <div class="emp-header-left">
            <div class="emp-header-icon">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div>
                <div class="emp-header-title">Employees</div>
                <div class="emp-header-sub">Manage your workforce — employees and managers</div>
            </div>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn-add-emp">
            <span class="material-symbols-outlined">person_add</span>
            Add Employee
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color:#137fec; --kpi-bg:rgba(19,127,236,.1);">
            <div class="kpi-top">
                <div class="kpi-icon"><span class="material-symbols-outlined">people</span></div>
            </div>
            <div class="kpi-value">{{ $stats['total'] }}</div>
            <div class="kpi-label">Total Employees</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#7c3aed; --kpi-bg:rgba(124,58,237,.1);">
            <div class="kpi-top">
                <div class="kpi-icon"><span class="material-symbols-outlined">manage_accounts</span></div>
            </div>
            <div class="kpi-value">{{ $stats['managers'] }}</div>
            <div class="kpi-label">Total Managers</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#16a34a; --kpi-bg:rgba(22,163,74,.1);">
            <div class="kpi-top">
                <div class="kpi-icon"><span class="material-symbols-outlined">check_circle</span></div>
            </div>
            <div class="kpi-value">{{ $stats['active'] }}</div>
            <div class="kpi-label">Active Employees</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#d97706; --kpi-bg:rgba(217,119,6,.1);">
            <div class="kpi-top">
                <div class="kpi-icon"><span class="material-symbols-outlined">corporate_fare</span></div>
            </div>
            <div class="kpi-value">{{ $stats['departments'] }}</div>
            <div class="kpi-label">Total Departments</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#0891b2; --kpi-bg:rgba(8,145,178,.1);">
            <div class="kpi-top">
                <div class="kpi-icon"><span class="material-symbols-outlined">person_add</span></div>
            </div>
            <div class="kpi-value">{{ $stats['new_month'] }}</div>
            <div class="kpi-label">New This Month</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.employees.index') }}" class="filter-bar">
        <div class="filter-search">
            <span class="material-symbols-outlined">search</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name, email, ID, mobile…">
        </div>

        <select name="department" class="filter-select">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                @php $encDeptId = \App\Helpers\IdCrypt::encode($dept->id); @endphp
                <option value="{{ $encDeptId }}" {{ request('department') === $encDeptId ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>

        <select name="role" class="filter-select">
            <option value="">All Roles</option>
            <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager"  {{ request('role') === 'manager'  ? 'selected' : '' }}>Manager</option>
        </select>

        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit" class="btn-add-emp" style="background:#475569; box-shadow:none;">
            <span class="material-symbols-outlined">filter_list</span>
            Filter
        </button>

        @if(request()->hasAny(['search','department','role','status']))
            <a href="{{ route('admin.employees.index') }}" class="btn-filter-reset">
                <span class="material-symbols-outlined">close</span>
                Reset
            </a>
        @endif
    </form>

    {{-- Table card --}}
    <div class="emp-card">
        <div class="emp-card-header">
            <span class="emp-card-header-title">All Employees</span>
            <span class="emp-count-pill">{{ $employees->total() }} employees</span>
        </div>

        @if($employees->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">group</span>
                <p>No employees found.
                    @if(request()->hasAny(['search','department','role','status']))
                        Try adjusting your filters.
                    @else
                        <a href="{{ route('admin.employees.create') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Add the first one.</a>
                    @endif
                </p>
            </div>
        @else
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th style="width:48px">#</th>
                        <th>Employee</th>
                        <th style="width:100px">Emp ID</th>
                        <th style="width:150px">Department</th>
                        <th style="width:110px">Role</th>
                        <th style="width:130px">Mobile</th>
                        <th style="width:90px">Status</th>
                        <th style="width:130px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarColors = ['#137fec','#7c3aed','#16a34a','#d97706','#0891b2','#dc2626','#db2777','#0284c7'];
                    @endphp
                    @foreach($employees as $i => $emp)
                    @php $color = $avatarColors[$i % count($avatarColors)]; @endphp
                    <tr>
                        <td style="color:var(--text-muted);font-size:.8125rem;font-weight:600;">
                            {{ $employees->firstItem() + $i }}
                        </td>
                        <td>
                            <div class="emp-info">
                                <div class="emp-avatar" style="background:{{ $color }};">
                                    @if($emp->employeeDetail?->profile_image)
                                        <img src="{{ Storage::url($emp->employeeDetail->profile_image) }}" alt="{{ $emp->name }}">
                                    @else
                                        {{ $emp->initials() }}
                                    @endif
                                </div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($emp->employee_code)
                                <span class="emp-id-pill">{{ $emp->employee_code }}</span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="dept-label">{{ $emp->department?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="role-badge {{ $emp->role === 'manager' ? 'role-manager' : 'role-employee' }}">
                                {{ ucfirst($emp->role) }}
                            </span>
                        </td>
                        <td style="font-size:.8125rem; color:var(--text-secondary);">
                            {{ $emp->mobile ?? '—' }}
                        </td>
                        <td>
                            <span class="status-badge {{ $emp->emp_status === 'active' ? 'status-active' : 'status-inactive' }}">
                                <span class="status-dot"></span>
                                {{ ucfirst($emp->emp_status ?? 'active') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.employees.show', $emp) }}" class="btn-view">
                                    <span class="material-symbols-outlined">visibility</span>
                                    View
                                </a>
                                <a href="{{ route('admin.employees.documents.index', $emp) }}"
                                   class="btn-view" title="Manage Documents"
                                   style="background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.25);color:#059669;">
                                    <span class="material-symbols-outlined">folder_shared</span>
                                    Docs
                                </a>
                                <a href="{{ route('admin.employees.edit', $emp) }}" class="btn-edit-emp">
                                    <span class="material-symbols-outlined">edit</span>
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            </div>

            {{-- Pagination --}}
            @if($employees->hasPages())
                <div class="pagination-wrap">
                    <span class="pagination-info">
                        Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }} employees
                    </span>
                    {{ $employees->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
