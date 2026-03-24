@extends('layouts.app')

@section('title', 'Edit Payroll Row')

@push('styles')
<style>
    .breadcrumb-bar { display: flex; align-items: center; gap: .375rem; font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.25rem; }
    .breadcrumb-bar a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-bar a:hover { text-decoration: underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size: .9375rem; }

    .edit-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; }
    @media (max-width: 860px) { .edit-grid { grid-template-columns: 1fr; } }

    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; }
    .card-header { padding: .875rem 1.25rem; border-bottom: 1px solid var(--border); background: #fafbfd; display: flex; align-items: center; gap: .5rem; }
    .card-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); font-variation-settings: 'FILL' 1; }
    .card-header h6 { font-size: .9rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .card-body { padding: 1.25rem; }

    .info-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid var(--border); font-size: .84rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row .lbl { color: var(--text-secondary); font-weight: 500; }
    .info-row .val { font-weight: 600; color: var(--text-main); }

    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-main); margin-bottom: .3rem; display: block; }
    .form-control { height: 2.5rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-size: .9rem; color: var(--text-main); background: #fff; width: 100%; padding: 0 .75rem; transition: border-color .2s, box-shadow .2s; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); outline: none; }
    .form-control[readonly] { background: #f8fafc; color: var(--text-secondary); cursor: default; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    @media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }

    .calc-section { margin-top: 1.25rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border); }
    .calc-section h6 { font-size: .78rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .04em; margin: 0 0 .875rem; }
    .calc-row { display: flex; justify-content: space-between; align-items: center; padding: .35rem 0; font-size: .84rem; border-bottom: 1px dashed var(--border); }
    .calc-row:last-child { border-bottom: none; padding-top: .5rem; margin-top: .25rem; font-weight: 700; font-size: .9rem; }
    .calc-row .c-lbl { color: var(--text-secondary); }
    .calc-row .c-val { font-weight: 600; }
    .calc-row .c-val.red   { color: #dc2626; }
    .calc-row .c-val.green { color: #16a34a; }

    .formula-note { font-size: .75rem; color: var(--text-muted); background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: .5rem .75rem; margin-top: .75rem; }

    .btn-row { display: flex; gap: .75rem; margin-top: 1.5rem; }
    .btn-save { height: 2.5rem; padding: 0 1.5rem; background: var(--primary); border: none; border-radius: var(--radius-sm); color: #fff; font-size: .875rem; font-weight: 600; display: inline-flex; align-items: center; gap: .35rem; cursor: pointer; transition: background .15s; }
    .btn-save:hover { background: var(--primary-hover); }
    .btn-cancel { height: 2.5rem; padding: 0 1.25rem; background: transparent; border: 1.5px solid var(--border); border-radius: var(--radius-sm); color: var(--text-secondary); font-size: .875rem; font-weight: 600; display: inline-flex; align-items: center; gap: .35rem; text-decoration: none; cursor: pointer; }
    .btn-cancel:hover { border-color: var(--primary); color: var(--primary); }

    .badge { display: inline-flex; align-items: center; gap: 4px; padding: .2rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .badge-generated { background: #dbeafe; color: #1d4ed8; }
</style>
@endpush

@section('content')

<div class="breadcrumb-bar">
    <a href="{{ route('admin.payroll.index') }}">Payroll</a>
    <span class="material-symbols-outlined">chevron_right</span>
    <a href="{{ route('admin.payroll.generate', ['month' => $payroll->month, 'year' => $payroll->year, 'generate' => 1]) }}">
        {{ \Carbon\Carbon::create($payroll->year, $payroll->month)->format('F Y') }}
    </a>
    <span class="material-symbols-outlined">chevron_right</span>
    <span>Edit Row</span>
</div>

<div style="margin-bottom:1.25rem;">
    <h1 style="font-size:1.35rem;font-weight:700;color:var(--text-main);margin:0 0 .2rem;display:flex;align-items:center;gap:.4rem;">
        <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">edit_note</span>
        Edit Payroll —
        {{ \Carbon\Carbon::create($payroll->year, $payroll->month)->format('F Y') }}
    </h1>
    <p style="font-size:.85rem;color:var(--text-secondary);margin:0;">
        Manually adjust working days or LOP days. Net salary recalculates automatically.
    </p>
</div>

<div class="edit-grid">
    {{-- Left: Employee info --}}
    <div class="card">
        <div class="card-header">
            <span class="material-symbols-outlined">person</span>
            <h6>Employee Details</h6>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(19,127,236,.1);display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;font-size:1.5rem;">person</span>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.95rem;">{{ $payroll->employee->name }}</div>
                    <div style="font-size:.75rem;color:var(--text-secondary);font-family:monospace;">{{ $payroll->employee->employee_code }}</div>
                </div>
            </div>

            <div class="info-row">
                <span class="lbl">Department</span>
                <span class="val">{{ $payroll->employee->department?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Period</span>
                <span class="val">{{ \Carbon\Carbon::create($payroll->year, $payroll->month)->format('F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Status</span>
                <span><span class="badge badge-generated">Generated</span></span>
            </div>
            <div class="info-row">
                <span class="lbl">Monthly Salary</span>
                <span class="val">₹{{ number_format($payroll->salary, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Calendar Days</span>
                <span class="val">{{ $payroll->total_days }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Present Days</span>
                <span class="val">{{ number_format($payroll->present_days, 1) }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Permission Hours</span>
                <span class="val">{{ number_format($payroll->permission_hours, 1) }}h</span>
            </div>
        </div>
    </div>

    {{-- Right: Edit form --}}
    <div class="card">
        <div class="card-header">
            <span class="material-symbols-outlined">edit</span>
            <h6>Adjust Values</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payroll.update', $payroll->id) }}" id="editForm">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div>
                        <label class="form-label" for="working_days">
                            Working Days <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" id="working_days" name="working_days"
                               class="form-control @error('working_days') is-invalid @enderror"
                               value="{{ old('working_days', $payroll->working_days) }}"
                               min="0" max="31" step="0.5" required>
                        @error('working_days')
                            <div style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label" for="lop_days">
                            LOP Days <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" id="lop_days" name="lop_days"
                               class="form-control @error('lop_days') is-invalid @enderror"
                               value="{{ old('lop_days', $payroll->lop_days) }}"
                               min="0" max="31" step="0.01" required>
                        @error('lop_days')
                            <div style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Live calculation display --}}
                <div class="calc-section">
                    <h6>Live Calculation</h6>
                    <div class="calc-row">
                        <span class="c-lbl">Per Day Salary</span>
                        <span class="c-val" id="perDay">₹{{ number_format($payroll->salary / $payroll->total_days, 2) }}</span>
                    </div>
                    <div class="calc-row">
                        <span class="c-lbl">LOP Amount</span>
                        <span class="c-val red" id="lopAmount">₹{{ number_format($payroll->lop_amount, 2) }}</span>
                    </div>
                    <div class="calc-row">
                        <span class="c-lbl">Net Salary</span>
                        <span class="c-val green" id="netSalary">₹{{ number_format($payroll->net_salary, 2) }}</span>
                    </div>
                </div>

                <div class="formula-note">
                    <strong>Formula:</strong> Per Day = Salary ÷ Total Days &nbsp;|&nbsp;
                    LOP Amount = Per Day × LOP Days &nbsp;|&nbsp;
                    Net = Salary − LOP Amount
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn-save">
                        <span class="material-symbols-outlined" style="font-size:1rem;">save</span>
                        Save Changes
                    </button>
                    <a href="{{ route('admin.payroll.generate', ['month' => $payroll->month, 'year' => $payroll->year, 'generate' => 1]) }}"
                       class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const salary    = {{ (float) $payroll->salary }};
    const totalDays = {{ (int) $payroll->total_days }};
    const perDay    = salary / totalDays;

    const lopInput  = document.getElementById('lop_days');
    const perDayEl  = document.getElementById('perDay');
    const lopAmtEl  = document.getElementById('lopAmount');
    const netEl     = document.getElementById('netSalary');

    const fmt = v => '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    perDayEl.textContent = fmt(perDay);

    function recalc() {
        const lop      = parseFloat(lopInput.value) || 0;
        const lopAmt   = perDay * lop;
        const net      = salary - lopAmt;
        lopAmtEl.textContent = fmt(lopAmt);
        netEl.textContent    = fmt(net);
    }

    lopInput.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
