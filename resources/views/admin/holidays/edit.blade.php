@extends('layouts.app')

@section('title', 'Edit Holiday')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }
    .back-link {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .84rem; color: var(--text-secondary); text-decoration: none;
        margin-bottom: 1.25rem; transition: color .15s;
    }
    .back-link:hover { color: var(--primary); }
    .back-link .material-symbols-outlined { font-size: 1rem; }

    .form-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; }
    @media (max-width: 860px) { .form-grid { grid-template-columns: 1fr; } }

    .form-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .form-card-header {
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b;
    }
    .form-card-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: #fff; }
    .form-card-body { padding: 1.5rem; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
    .form-group { margin-bottom: 1.25rem; }
    .form-group:last-child { margin-bottom: 0; }

    .form-label {
        display: block; font-size: .8rem; font-weight: 600;
        color: var(--text-secondary); margin-bottom: .45rem; letter-spacing: .01em;
    }
    .form-label .req { color: #ef4444; margin-left: .15rem; }

    .form-control {
        width: 100%; height: 2.5rem; padding: 0 .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        outline: none; transition: border-color .15s, box-shadow .15s;
        font-family: inherit;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .form-control.is-invalid { border-color: #ef4444; }
    textarea.form-control { height: auto; padding: .625rem .875rem; resize: vertical; }

    .invalid-feedback { font-size: .8rem; color: #ef4444; margin-top: .35rem; display: block; }

    .radio-group { display: flex; gap: .75rem; flex-wrap: wrap; }
    .radio-option {
        display: flex; align-items: center; gap: .5rem;
        padding: .5rem .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        cursor: pointer; transition: all .15s; font-size: .875rem; font-weight: 500;
        color: var(--text-secondary);
    }
    .radio-option:has(input:checked) { border-color: var(--primary); background: #eff6ff; color: var(--primary); font-weight: 600; }
    .radio-option input { display: none; }
    .type-dot { width: 10px; height: 10px; border-radius: 50%; }
    .dot-national { background: #d97706; }
    .dot-optional { background: #22c55e; }

    .scope-options { display: flex; gap: .75rem; flex-wrap: wrap; }
    .scope-option {
        display: flex; align-items: center; gap: .5rem;
        padding: .5rem .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        cursor: pointer; transition: all .15s; font-size: .875rem; font-weight: 500;
        color: var(--text-secondary);
    }
    .scope-option:has(input:checked) { border-color: var(--primary); background: #eff6ff; color: var(--primary); font-weight: 600; }
    .scope-option input { display: none; }
    .scope-option .material-symbols-outlined { font-size: 1.1rem; }

    #deptRow { display: none; }

    .toggle-wrap { display: flex; align-items: center; gap: .75rem; }
    .toggle-switch { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0; background: #cbd5e1;
        border-radius: 999px; cursor: pointer; transition: background .2s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 18px; height: 18px; left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%; transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .toggle-switch input:checked + .toggle-slider { background: #22c55e; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }
    .toggle-desc { font-size: .875rem; color: var(--text-secondary); }

    .sidebar-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
        margin-bottom: 1rem;
    }
    .sidebar-card-header { padding: .75rem 1rem; border-bottom: 1px solid var(--border); background: #f8fafc; }
    .sidebar-card-header h6 { font-size: .84rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .sidebar-card-body { padding: 1rem; }

    .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; font-size: .84rem; }
    .summary-row:last-child { margin-bottom: 0; }
    .summary-label { color: var(--text-muted); }
    .summary-value { font-weight: 600; color: var(--text-main); }

    .form-footer { display: flex; gap: .75rem; margin-top: 1.5rem; }
    .btn-submit {
        height: 2.5rem; padding: 0 1.5rem; background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; transition: background .15s; display: flex; align-items: center; gap: .4rem;
    }
    .btn-submit:hover { background: var(--primary-hover); }
    .btn-submit .material-symbols-outlined { font-size: 1rem; }
    .btn-cancel {
        height: 2.5rem; padding: 0 1.25rem; background: var(--surface); color: var(--text-secondary);
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer; transition: all .15s;
        display: flex; align-items: center; gap: .4rem; text-decoration: none;
    }
    .btn-cancel:hover { background: var(--bg-light); color: var(--text-main); }
</style>
@endpush

@section('content')

    <a href="{{ route('admin.holidays.index') }}" class="back-link">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to Holiday List
    </a>

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="font-size:1.4rem; color:var(--primary); font-variation-settings:'FILL' 1;">edit_calendar</span>
            Edit Holiday
        </h1>
        <p>Update details for "{{ $holiday->name }}"</p>
    </div>

    <form method="POST" action="{{ route('admin.holidays.update', $holiday) }}" id="holidayForm">
        @csrf @method('PUT')
        <div class="form-grid">

            {{-- Main form --}}
            <div>
                <div class="form-card">
                    <div class="form-card-header"><h5>Holiday Details</h5></div>
                    <div class="form-card-body">

                        <div class="form-group">
                            <label class="form-label">Holiday Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $holiday->name) }}" autofocus>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Date <span class="req">*</span></label>
                                <input type="date" name="date" id="dateInput"
                                       class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', $holiday->date->format('Y-m-d')) }}">
                                @error('date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Type <span class="req">*</span></label>
                                <div class="radio-group" style="margin-top:.35rem;">
                                    <label class="radio-option">
                                        <input type="radio" name="type" value="national"
                                               {{ old('type', $holiday->type) === 'national' ? 'checked' : '' }}
                                               onchange="updateSummary()">
                                        <span class="type-dot dot-national"></span> National
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="type" value="optional"
                                               {{ old('type', $holiday->type) === 'optional' ? 'checked' : '' }}
                                               onchange="updateSummary()">
                                        <span class="type-dot dot-optional"></span> Optional
                                    </label>
                                </div>
                                @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:1.25rem;">
                            <label class="form-label">Scope <span class="req">*</span></label>
                            <div class="scope-options">
                                <label class="scope-option">
                                    <input type="radio" name="scope" value="all"
                                           {{ old('scope', $holiday->scope) === 'all' ? 'checked' : '' }}
                                           onchange="toggleDept(this.value)">
                                    <span class="material-symbols-outlined">domain</span>
                                    All Departments
                                </label>
                                <label class="scope-option">
                                    <input type="radio" name="scope" value="department"
                                           {{ old('scope', $holiday->scope) === 'department' ? 'checked' : '' }}
                                           onchange="toggleDept(this.value)">
                                    <span class="material-symbols-outlined">corporate_fare</span>
                                    Specific Department
                                </label>
                            </div>
                            @error('scope')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group" id="deptRow">
                            <label class="form-label">Department <span class="req">*</span></label>
                            <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                                <option value="">— Select Department —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id', $holiday->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Note / Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $holiday->description) }}</textarea>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn-submit">
                        <span class="material-symbols-outlined">save</span>
                        Update Holiday
                    </button>
                    <a href="{{ route('admin.holidays.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </div>

            {{-- Sidebar --}}
            <div>
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><h6>Preview</h6></div>
                    <div class="sidebar-card-body">
                        <div class="summary-row">
                            <span class="summary-label">Name</span>
                            <span class="summary-value" id="prevName">{{ $holiday->name }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Date</span>
                            <span class="summary-value" id="prevDate">{{ $holiday->date->format('M d, Y') }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Day</span>
                            <span class="summary-value" id="prevDay">{{ $holiday->day_name }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Type</span>
                            <span class="summary-value" id="prevType">{{ ucfirst($holiday->type) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Scope</span>
                            <span class="summary-value" id="prevScope">{{ $holiday->getScopeLabel() }}</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-card-header"><h6>Status</h6></div>
                    <div class="sidebar-card-body">
                        <div class="toggle-wrap">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status" value="1"
                                       {{ old('status', $holiday->status ? '1' : '') ? 'checked' : '' }}
                                       id="statusToggle">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-desc" id="statusLabel">
                                {{ $holiday->status ? 'Active — will appear in employee calendar' : 'Inactive — hidden from employee calendar' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:var(--radius-sm); padding:.875rem 1rem; font-size:.8rem; color:#92400e; line-height:1.55;">
                    <strong>Note:</strong> Only <strong>active</strong> holidays appear in the employee calendar. Duplicate dates for the same scope are not allowed.
                </div>
            </div>

        </div>
    </form>

@endsection

@push('scripts')
<script>
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    function toggleDept(val) {
        const row = document.getElementById('deptRow');
        row.style.display = val === 'department' ? 'block' : 'none';
        updateSummary();
    }

    function updateSummary() {
        const nameEl  = document.querySelector('[name="name"]');
        const dateEl  = document.getElementById('dateInput');
        const typeEl  = document.querySelector('[name="type"]:checked');
        const scopeEl = document.querySelector('[name="scope"]:checked');

        document.getElementById('prevName').textContent = nameEl?.value || '—';

        if (dateEl?.value) {
            const d = new Date(dateEl.value + 'T00:00:00');
            document.getElementById('prevDate').textContent = d.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
            document.getElementById('prevDay').textContent  = days[d.getDay()];
        } else {
            document.getElementById('prevDate').textContent = '—';
            document.getElementById('prevDay').textContent  = '—';
        }

        document.getElementById('prevType').textContent  = typeEl  ? (typeEl.value === 'national' ? 'National' : 'Optional') : '—';
        document.getElementById('prevScope').textContent = scopeEl ? (scopeEl.value === 'all' ? 'All Departments' : 'Specific Dept') : '—';
    }

    document.getElementById('statusToggle').addEventListener('change', function() {
        document.getElementById('statusLabel').textContent = this.checked
            ? 'Active — will appear in employee calendar'
            : 'Inactive — hidden from employee calendar';
    });

    document.querySelector('[name="name"]').addEventListener('input', updateSummary);
    document.getElementById('dateInput').addEventListener('input', updateSummary);

    // Init
    toggleDept('{{ old('scope', $holiday->scope) }}');
    updateSummary();
</script>
@endpush
