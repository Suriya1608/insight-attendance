@extends('layouts.app')

@section('title', 'Generate Payroll')

@push('styles')
<style>
    .breadcrumb-bar {
        display: flex; align-items: center; gap: .375rem;
        font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.25rem;
    }
    .breadcrumb-bar a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-bar a:hover { text-decoration: underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size: .9375rem; }

    .filter-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 1.5rem; }
    .filter-header { padding: .875rem 1.5rem; border-bottom: 1px solid var(--border); background: #fafbfd; display: flex; align-items: center; gap: .625rem; }
    .filter-header .sec-icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(19,127,236,.1); display: flex; align-items: center; justify-content: center; }
    .filter-header .sec-icon .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); font-variation-settings: 'FILL' 1; }
    .filter-header h6 { font-size: .9rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .filter-body { padding: 1.25rem 1.5rem; }

    .grid-5 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
    @media (max-width: 900px) { .grid-5 { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 560px) { .grid-5 { grid-template-columns: 1fr; } }
    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-main); margin-bottom: .3rem; display: block; }
    .form-control, .form-select {
        height: 2.5rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border);
        font-size: .875rem; color: var(--text-main); background: #f8fafc; width: 100%;
        padding: 0 .75rem; transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); background: #fff; outline: none; }
    .btn-generate {
        height: 2.5rem; padding: 0 1.5rem; background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff; font-size: .875rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        cursor: pointer; transition: background .15s; white-space: nowrap;
    }
    .btn-generate:hover { background: var(--primary-hover); }

    .results-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; }
    .results-header { padding: .875rem 1.5rem; border-bottom: 1px solid var(--border); background: #fafbfd; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .results-header h6 { font-size: .9rem; font-weight: 700; margin: 0; color: var(--text-main); flex: 1; }
    .results-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

    .btn-save {
        height: 2.25rem; padding: 0 1.125rem; background: #16a34a; border: none;
        border-radius: var(--radius-sm); color: #fff; font-size: .82rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .3rem; cursor: pointer; transition: background .15s; text-decoration: none;
    }
    .btn-save:hover { background: #15803d; color: #fff; }
    .btn-export {
        height: 2.25rem; padding: 0 1.125rem; background: transparent; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary); font-size: .82rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .3rem; cursor: pointer; transition: all .15s; text-decoration: none;
    }
    .btn-export:hover { border-color: var(--primary); color: var(--primary); }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    thead tr { background: #f8fafc; }
    thead th { padding: .7rem .75rem; font-size: .72rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid var(--border); white-space: nowrap; text-align: right; }
    thead th:first-child, thead th:nth-child(2), thead th:nth-child(3) { text-align: left; }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
    tbody tr:hover { background: #f8fafc; }
    tbody tr:last-child { border-bottom: none; }
    td { padding: .65rem .75rem; color: var(--text-main); vertical-align: middle; text-align: right; white-space: nowrap; }
    td:first-child, td:nth-child(2), td:nth-child(3) { text-align: left; }
    .emp-code { font-size: .72rem; color: var(--text-secondary); font-family: monospace; }
    .emp-name { font-size: .85rem; font-weight: 600; color: var(--text-main); }
    .dept-badge { display: inline-block; padding: 2px 8px; background: rgba(19,127,236,.08); color: var(--primary); border-radius: 10px; font-size: .72rem; font-weight: 600; }
    .lop-days { color: #dc2626; font-weight: 600; }
    .net-salary { color: #16a34a; font-weight: 700; }
    .zero { color: #94a3b8; }
    .edit-link { color: var(--primary); text-decoration: none; font-size: .75rem; display: inline-flex; align-items: center; gap: 2px; margin-left: 4px; }
    .edit-link:hover { text-decoration: underline; }
    .edit-link .material-symbols-outlined { font-size: .85rem; }

    tfoot tr { background: #f1f5f9; border-top: 2px solid var(--border); }
    tfoot td { padding: .7rem .75rem; font-weight: 700; font-size: .8rem; color: var(--text-main); }

    .stats-bar { display: flex; gap: 1rem; flex-wrap: wrap; padding: .875rem 1.5rem; border-bottom: 1px solid var(--border); background: #fff; }
    .stat-chip { display: flex; align-items: center; gap: .5rem; font-size: .8rem; color: var(--text-secondary); }
    .stat-chip strong { color: var(--text-main); font-weight: 700; }
    .stat-sep { color: var(--border); }

    .pagination-bar { padding: .875rem 1.5rem; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; font-size: .8rem; color: var(--text-secondary); }
    .page-btns { display: flex; gap: .25rem; }
    .page-btn { min-width: 32px; height: 32px; padding: 0 .5rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: transparent; color: var(--text-secondary); font-size: .8rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: all .15s; }
    .page-btn:hover { border-color: var(--primary); color: var(--primary); }
    .page-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; cursor: default; }
    .page-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }

    .alert-box { display: flex; align-items: flex-start; gap: .75rem; padding: .875rem 1.25rem; border-radius: var(--radius-sm); font-size: .84rem; margin-bottom: 1rem; }
    .alert-box .material-symbols-outlined { font-size: 1.2rem; flex-shrink: 0; }
    .alert-box.warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .alert-box.danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-box.info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

    .empty-state { padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size: 3rem; display: block; margin-bottom: .75rem; }
    .empty-state p { font-size: .88rem; margin: 0; }
</style>
@endpush

@section('content')

<div class="breadcrumb-bar">
    <a href="{{ route('admin.payroll.index') }}">Payroll</a>
    <span class="material-symbols-outlined">chevron_right</span>
    <span>Generate</span>
</div>

<div style="margin-bottom:1.25rem;">
    <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-main);margin:0 0 .2rem;display:flex;align-items:center;gap:.4rem;">
        <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">calculate</span>
        Generate Payroll
    </h1>
    <p style="font-size:.85rem;color:var(--text-secondary);margin:0;">Compute and save monthly payroll for employees.</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── FILTER FORM ── --}}
<div class="filter-card">
    <div class="filter-header">
        <div class="sec-icon"><span class="material-symbols-outlined">tune</span></div>
        <h6>Payroll Parameters</h6>
    </div>
    <div class="filter-body">
        <form method="GET" action="{{ route('admin.payroll.generate') }}" id="filterForm">
            <input type="hidden" name="generate" value="1">
            <div class="grid-5">
                <div>
                    <label class="form-label">Month <span style="color:#ef4444;">*</span></label>
                    <select name="month" class="form-select" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Year <span style="color:#ef4444;">*</span></label>
                    <select name="year" class="form-select" required>
                        @foreach(range(now()->year - 3, now()->year) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" id="deptFilter">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" id="empFilter">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-dept="{{ $emp->department_id }}" {{ $empId == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-generate" style="width:100%;">
                        <span class="material-symbols-outlined" style="font-size:1rem;">calculate</span>
                        Generate
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── STATUS MESSAGES ── --}}
@if($isFuture)
    <div class="alert-box danger">
        <span class="material-symbols-outlined">block</span>
        <div><strong>Future month not allowed.</strong> You cannot generate payroll for a future month. Please select the current or a past month.</div>
    </div>
@elseif($existingBatch && $existingBatch->isLocked())
    <div class="alert-box info">
        <span class="material-symbols-outlined">lock</span>
        <div>
            <strong>Payroll for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }} is locked.</strong>
            No further edits are allowed. You can preview the data below.
        </div>
    </div>
@elseif($existingBatch)
    <div class="alert-box warning">
        <span class="material-symbols-outlined">warning</span>
        <div>
            <strong>Payroll for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }} is already generated.</strong>
            To modify individual rows, use the <strong>Edit</strong> button on each row below.
            You can also lock this batch from the <a href="{{ route('admin.payroll.index') }}">Payroll List</a>.
        </div>
    </div>
@elseif($prevNotLocked && $rows->isNotEmpty())
    <div class="alert-box warning">
        <span class="material-symbols-outlined">info</span>
        <div>
            <strong>Note:</strong> The previous month's payroll has not been locked.
            It will be <strong>auto-locked</strong> when you save this payroll.
        </div>
    </div>
@endif

{{-- ── RESULTS ── --}}
@if($rows->isNotEmpty())
    <div class="results-card" id="rows">
        <div class="results-header">
            <div style="flex:1;">
                <h6>Payroll — {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</h6>
            </div>
            <div class="results-actions">
                {{-- Save: only if not already generated --}}
                @if(!$existingBatch && !$isFuture)
                    <form method="POST" action="{{ route('admin.payroll.store') }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="month"         value="{{ $month }}">
                        <input type="hidden" name="year"          value="{{ $year }}">
                        <input type="hidden" name="department_id" value="{{ $deptId }}">
                        <input type="hidden" name="employee_id"   value="{{ $empId }}">
                        <button type="submit" class="btn-save"
                                onclick="return confirm('Save payroll for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}?')">
                            <span class="material-symbols-outlined" style="font-size:.95rem;">save</span>
                            Save Payroll
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.payroll.export.csv', ['month' => $month, 'year' => $year, 'department_id' => $deptId, 'employee_id' => $empId]) }}"
                   class="btn-export">
                    <span class="material-symbols-outlined" style="font-size:.95rem;">download</span>
                    Export CSV
                </a>
                <a href="{{ route('admin.payroll.print', ['month' => $month, 'year' => $year, 'department_id' => $deptId, 'employee_id' => $empId]) }}"
                   target="_blank" class="btn-export">
                    <span class="material-symbols-outlined" style="font-size:.95rem;">print</span>
                    Print / PDF
                </a>
            </div>
        </div>

        {{-- Stats bar --}}
        @php
            $totalNet    = $rows->sum('net_salary');
            $totalLop    = $rows->sum('lop_amount');
            $totalSalary = $rows->sum('salary');
        @endphp
        <div class="stats-bar">
            <div class="stat-chip">
                <span class="material-symbols-outlined" style="font-size:1rem;color:var(--primary)">group</span>
                Employees: <strong>{{ $rows->count() }}</strong>
            </div>
            <span class="stat-sep">|</span>
            <div class="stat-chip">
                <span class="material-symbols-outlined" style="font-size:1rem;color:#16a34a">payments</span>
                Total Gross: <strong>₹{{ number_format($totalSalary, 2) }}</strong>
            </div>
            <span class="stat-sep">|</span>
            <div class="stat-chip">
                <span class="material-symbols-outlined" style="font-size:1rem;color:#dc2626">remove_circle</span>
                LOP Deduction: <strong style="color:#dc2626;">₹{{ number_format($totalLop, 2) }}</strong>
            </div>
            <span class="stat-sep">|</span>
            <div class="stat-chip">
                <span class="material-symbols-outlined" style="font-size:1rem;color:#16a34a">account_balance_wallet</span>
                Net Payable: <strong style="color:#16a34a;">₹{{ number_format($totalNet, 2) }}</strong>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:left;">#</th>
                        <th style="text-align:left;">Employee</th>
                        <th style="text-align:left;">Department</th>
                        <th>Total<br>Days</th>
                        <th>Working<br>Days</th>
                        <th>Present<br>Days</th>
                        <th>Paid<br>Leaves</th>
                        <th>LOP<br>Days</th>
                        <th>Perm.<br>Hours</th>
                        <th>Opt. Hols<br>Taken</th>
                        <th>Monthly<br>Salary</th>
                        <th>Per Day<br>Salary</th>
                        <th>LOP<br>Amount</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paginated as $idx => $row)
                        @php
                            $serial = ($currentPage - 1) * $perPage + $loop->iteration;
                            // Find saved payroll row if batch exists
                            $savedRow = $existingBatch
                                ? \App\Models\Payroll::where('employee_id', $row['employee']->id)
                                    ->where('month', $month)->where('year', $year)->first()
                                : null;
                            $canEdit = $existingBatch && !$existingBatch->isLocked();
                        @endphp
                        <tr>
                            <td style="text-align:left;color:var(--text-muted);">{{ $serial }}</td>
                            <td style="text-align:left;">
                                <div class="emp-name">
                                    {{ $row['employee']->name }}
                                    @if($canEdit && $savedRow)
                                        <a href="{{ route('admin.payroll.edit', $savedRow->id) }}" class="edit-link" title="Edit row">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                    @endif
                                </div>
                                <div class="emp-code">{{ $row['employee']->employee_code }}</div>
                            </td>
                            <td style="text-align:left;">
                                @if($row['employee']->department)
                                    <span class="dept-badge">{{ $row['employee']->department->name }}</span>
                                @else
                                    <span style="color:#cbd5e1;">—</span>
                                @endif
                            </td>
                            <td>{{ $row['total_days'] }}</td>
                            <td>{{ $savedRow ? $savedRow->working_days : $row['working_days'] }}</td>
                            <td>
                                @php $pd = $savedRow ? $savedRow->present_days : $row['present_days']; @endphp
                                {{ $pd == (int)$pd ? (int)$pd : number_format($pd, 1) }}
                            </td>
                            <td class="{{ $row['paid_leaves'] > 0 ? '' : 'zero' }}">
                                {{ $row['paid_leaves'] > 0 ? number_format($row['paid_leaves'], 1) : '0' }}
                            </td>
                            <td class="{{ ($savedRow ? $savedRow->lop_days : $row['lop_days']) > 0 ? 'lop-days' : 'zero' }}">
                                @php $ld = $savedRow ? $savedRow->lop_days : $row['lop_days']; @endphp
                                {{ $ld > 0 ? number_format($ld, 2) : '0' }}
                            </td>
                            <td class="{{ $row['permission_hours'] > 0 ? '' : 'zero' }}">
                                {{ $row['permission_hours'] > 0 ? number_format($row['permission_hours'], 1).'h' : '0' }}
                            </td>
                            <td class="{{ $row['optional_holidays_taken'] > 0 ? '' : 'zero' }}">
                                {{ $row['optional_holidays_taken'] ?: '0' }}
                            </td>
                            <td>₹{{ number_format($row['salary'], 2) }}</td>
                            <td style="color:var(--text-secondary);">₹{{ number_format($row['per_day_salary'], 2) }}</td>
                            <td class="{{ ($savedRow ? $savedRow->lop_amount : $row['lop_amount']) > 0 ? 'lop-days' : 'zero' }}">
                                @php $la = $savedRow ? $savedRow->lop_amount : $row['lop_amount']; @endphp
                                {{ $la > 0 ? '₹'.number_format($la, 2) : '—' }}
                            </td>
                            <td class="net-salary">₹{{ number_format($savedRow ? $savedRow->net_salary : $row['net_salary'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($rows->count() > 1)
                <tfoot>
                    <tr>
                        <td colspan="10" style="text-align:left;font-size:.8rem;color:var(--text-secondary);">
                            Page total ({{ $paginated->count() }} employees)
                        </td>
                        <td>₹{{ number_format($paginated->sum('salary'), 2) }}</td>
                        <td></td>
                        <td class="lop-days">
                            @if($paginated->sum('lop_amount') > 0) ₹{{ number_format($paginated->sum('lop_amount'), 2) }}
                            @else —
                            @endif
                        </td>
                        <td class="net-salary">₹{{ number_format($paginated->sum('net_salary'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if($total > $perPage)
            @php $totalPages = (int) ceil($total / $perPage); @endphp
            <div class="pagination-bar">
                <span>Showing {{ ($currentPage - 1) * $perPage + 1 }}–{{ min($currentPage * $perPage, $total) }} of {{ $total }} employees</span>
                <div class="page-btns">
                    <a class="page-btn {{ $currentPage <= 1 ? 'disabled' : '' }}"
                       href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                        <span class="material-symbols-outlined" style="font-size:.9rem;">chevron_left</span>
                    </a>
                    @for($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++)
                        <a class="page-btn {{ $p === $currentPage ? 'active' : '' }}"
                           href="{{ request()->fullUrlWithQuery(['page' => $p]) }}">{{ $p }}</a>
                    @endfor
                    <a class="page-btn {{ $currentPage >= $totalPages ? 'disabled' : '' }}"
                       href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                        <span class="material-symbols-outlined" style="font-size:.9rem;">chevron_right</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

@elseif(request()->has('generate') && !$isFuture)
    <div class="results-card">
        <div class="empty-state">
            <span class="material-symbols-outlined" style="color:#94a3b8;">group_off</span>
            <p>No active employees found for the selected filters.</p>
        </div>
    </div>
@elseif(!request()->has('generate'))
    <div class="results-card">
        <div class="empty-state">
            <span class="material-symbols-outlined" style="color:#94a3b8;">calculate</span>
            <p>Select a month and year above and click <strong>Generate</strong> to compute payroll.</p>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.getElementById('deptFilter').addEventListener('change', function () {
    const deptId  = this.value;
    const empSel  = document.getElementById('empFilter');
    const options = empSel.querySelectorAll('option');
    options.forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!deptId || opt.dataset.dept === deptId) ? '' : 'none';
    });
    const selected = empSel.options[empSel.selectedIndex];
    if (selected.value && selected.style.display === 'none') empSel.value = '';
});
document.getElementById('deptFilter').dispatchEvent(new Event('change'));
</script>
@endpush
