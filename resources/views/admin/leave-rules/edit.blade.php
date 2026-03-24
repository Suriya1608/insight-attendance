@extends('layouts.app')

@section('title', 'Configure Leave Rules — ' . $department->name)

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 { font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 .2rem; }
    .page-header p  { font-size: .85rem; color: var(--text-secondary); margin: 0; }

    .back-btn {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .8125rem; color: var(--text-muted); text-decoration: none;
        margin-bottom: 1.25rem; transition: color .15s;
    }
    .back-btn:hover { color: var(--primary); }
    .back-btn .material-symbols-outlined { font-size: 1.1rem; }

    .grid-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; } }

    /* Section cards */
    .section-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1.25rem;
    }
    .section-card:last-child { margin-bottom: 0; }
    .section-header {
        display: flex; align-items: center; gap: .625rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: #fafbfd;
    }
    .section-header .s-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .s-green  { background: #dcfce7; color: #16a34a; }
    .s-yellow { background: #fef9c3; color: #ca8a04; }
    .section-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .section-body { padding: 1.25rem; }

    /* Form controls */
    .fields-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    @media (max-width: 600px) { .fields-row { grid-template-columns: 1fr; } }

    .form-label {
        display: block; font-size: .8rem; font-weight: 600;
        color: var(--text-main); margin-bottom: .35rem;
    }
    .form-label span { color: #ef4444; }
    .form-hint { font-size: .75rem; color: var(--text-muted); margin-top: .25rem; }
    .form-control, .form-select {
        height: 2.625rem; padding: 0 .75rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .9rem; color: var(--text-main); background: #f8fafc;
        transition: border-color .2s, box-shadow .2s; width: 100%;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary); outline: none; background: #fff;
        box-shadow: 0 0 0 3px var(--primary-subtle);
    }
    .form-control.is-invalid { border-color: #ef4444; }
    .invalid-feedback { font-size: .78rem; color: #ef4444; margin-top: .25rem; display: block; }

    /* Credit preview */
    .credit-note {
        margin-top: 1rem; padding: .75rem 1rem;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: var(--radius-sm); font-size: .8rem; color: #1e40af;
    }
    .credit-note strong { color: #1d4ed8; }

    /* Saturday rule radios */
    .sat-options { display: flex; flex-direction: column; gap: .625rem; margin-top: .5rem; }
    .sat-option {
        display: flex; align-items: flex-start; gap: .875rem;
        padding: .875rem 1rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        cursor: pointer; transition: border-color .2s, background .2s;
        background: #fff;
    }
    .sat-option:hover { border-color: #93c5fd; background: #f8faff; }
    .sat-option.selected {
        border-color: var(--primary);
        background: #eff6ff;
    }
    .sat-option.carry-option {
        border-color: #fed7aa; background: #fff7ed;
    }
    .sat-option.carry-option.selected {
        border-color: #f97316; background: #fff7ed;
    }
    .sat-option input[type="radio"] {
        width: 1rem; height: 1rem; margin-top: .1rem;
        accent-color: var(--primary); flex-shrink: 0; cursor: pointer;
    }
    .sat-option .opt-icon {
        width: 28px; height: 28px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0; margin-top: .05rem;
    }
    .sat-option .opt-body { flex: 1; }
    .sat-option .opt-title {
        font-size: .875rem; font-weight: 700; color: var(--text-main); line-height: 1.2;
    }
    .sat-option .opt-desc {
        font-size: .78rem; color: var(--text-muted); margin-top: .2rem; line-height: 1.4;
    }

    /* Sidebar cards */
    .side-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1rem;
    }
    .side-card:last-child { margin-bottom: 0; }
    .side-card-header {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1rem; border-bottom: 1px solid var(--border); background: #fafbfd;
    }
    .side-card-header .sh-icon {
        width: 26px; height: 26px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: .875rem;
    }
    .sh-blue   { background: #dbeafe; color: #2563eb; }
    .sh-gray   { background: #f1f5f9; color: #64748b; }
    .side-card-header h6 {
        font-size: .82rem; font-weight: 700; margin: 0; color: var(--text-main);
    }
    .side-card-body { padding: 1rem; }

    /* Policy summary list */
    .policy-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .5rem; }
    .policy-list li {
        display: flex; align-items: center; gap: .5rem;
        font-size: .8125rem; color: var(--text-secondary);
    }
    .policy-list li .material-symbols-outlined { font-size: .95rem; flex-shrink: 0; }
    .pol-green  { color: #16a34a; }
    .pol-blue   { color: #2563eb; }
    .pol-orange { color: #d97706; }

    /* Notes textarea */
    .form-textarea {
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .85rem; color: var(--text-main); background: #f8fafc;
        transition: border-color .2s; width: 100%; resize: vertical;
        padding: .625rem .75rem; min-height: 90px;
    }
    .form-textarea:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 3px var(--primary-subtle); }

    /* Toggle switch */
    .toggle-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem;
    }
    .toggle-info .toggle-title { font-size: .875rem; font-weight: 600; color: var(--text-main); }
    .toggle-info .toggle-desc  { font-size: .78rem; color: var(--text-muted); margin-top: .15rem; }
    .toggle-switch {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-track {
        position: absolute; inset: 0; background: #cbd5e1;
        border-radius: 12px; cursor: pointer; transition: background .2s;
    }
    .toggle-track::after {
        content: ''; position: absolute; left: 3px; top: 3px;
        width: 18px; height: 18px; border-radius: 50%;
        background: #fff; transition: transform .2s;
    }
    .toggle-switch input:checked + .toggle-track { background: var(--primary); }
    .toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }

    /* Save button */
    .btn-save {
        height: 2.625rem; padding: 0 1.5rem;
        background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        cursor: pointer; transition: background .15s;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
    }
    .btn-save:hover { background: var(--primary-hover); }

    /* Alert */
    .alert-success {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0;
        border-radius: var(--radius-sm); color: #15803d; font-size: .84rem;
        margin-bottom: 1.25rem;
    }
</style>
@endpush

@section('content')

    <a href="{{ route('admin.leave-rules.index') }}" class="back-btn">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>

    @if(session('success'))
        <div class="alert-success">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="page-header">
        <h1>Leave Rules: {{ $department->name }}</h1>
        <p>Configure monthly leave entitlements and Saturday policy</p>
    </div>

    <form method="POST" action="{{ route('admin.leave-rules.update', $department) }}" id="leaveRuleForm">
        @csrf
        @method('PUT')

        <div class="grid-layout">

            {{-- ── LEFT COLUMN ── --}}
            <div>

                {{-- Monthly Leave Entitlements --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="s-icon s-green">
                            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1; font-size:.9rem;">calendar_month</span>
                        </div>
                        <h5>Monthly Leave Entitlements</h5>
                    </div>
                    <div class="section-body">
                        <div class="fields-row">
                            <div>
                                <label class="form-label">CL Per Month <span>*</span></label>
                                <input type="number" name="cl_per_month" step="0.5" min="0" max="30"
                                       class="form-control @error('cl_per_month') is-invalid @enderror"
                                       value="{{ old('cl_per_month', $department->cl_per_month ?? 1) }}"
                                       id="clInput">
                                <div class="form-hint">Casual Leave days</div>
                                @error('cl_per_month')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="form-label">Permissions / Month <span>*</span></label>
                                <input type="number" name="permissions_per_month" step="1" min="0" max="10"
                                       class="form-control @error('permissions_per_month') is-invalid @enderror"
                                       value="{{ old('permissions_per_month', $department->permissions_per_month ?? 2) }}"
                                       id="permInput">
                                <div class="form-hint">Number of permission requests</div>
                                @error('permissions_per_month')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="form-label">Hours Per Permission <span>*</span></label>
                                <input type="number" name="hours_per_permission" step="1" min="1" max="8"
                                       class="form-control @error('hours_per_permission') is-invalid @enderror"
                                       value="{{ old('hours_per_permission', $department->hours_per_permission ?? 2) }}"
                                       id="hrsInput">
                                <div class="form-hint">Duration each permission</div>
                                @error('hours_per_permission')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="credit-note" id="creditNote">
                            Employees in <strong>{{ $department->name }}</strong> receive
                            <strong id="noteClVal">{{ number_format($department->cl_per_month ?? 1, 0) }} CL</strong>
                            and <strong id="notePermVal">{{ $department->permissions_per_month ?? 2 }} × {{ $department->hours_per_permission ?? 2 }}h permissions</strong> per month.
                        </div>
                    </div>
                </div>

                {{-- Saturday Leave Rule --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="s-icon s-yellow">
                            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1; font-size:.9rem;">weekend</span>
                        </div>
                        <h5>Saturday Leave Rule</h5>
                    </div>
                    <div class="section-body">

                        <div style="font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:.625rem;">
                            Select Saturday Policy
                        </div>

                        <div class="sat-options">

                            @php $currentRule = old('saturday_rule', $department->saturday_rule ?? 'none'); @endphp

                            {{-- All Saturdays Working --}}
                            <label class="sat-option {{ $currentRule === 'none' ? 'selected' : '' }}">
                                <input type="radio" name="saturday_rule" value="none"
                                       {{ $currentRule === 'none' ? 'checked' : '' }} onchange="selectSatOption(this)">
                                <div class="opt-icon" style="background:#f1f5f9; color:#64748b;">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">work</span>
                                </div>
                                <div class="opt-body">
                                    <div class="opt-title">All Saturdays Working</div>
                                    <div class="opt-desc">No Saturday leave. All Saturdays are regular working days.</div>
                                </div>
                            </label>

                            {{-- 2nd Saturday Off --}}
                            <label class="sat-option {{ $currentRule === '2nd_saturday_off' ? 'selected' : '' }}">
                                <input type="radio" name="saturday_rule" value="2nd_saturday_off"
                                       {{ $currentRule === '2nd_saturday_off' ? 'checked' : '' }} onchange="selectSatOption(this)">
                                <div class="opt-icon" style="background:#ede9fe; color:#7c3aed;">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">event_busy</span>
                                </div>
                                <div class="opt-body">
                                    <div class="opt-title">2nd Saturday Off (Fixed)</div>
                                    <div class="opt-desc">The 2nd Saturday of every month is a leave day for all employees in this department.</div>
                                </div>
                            </label>

                            {{-- 4th Saturday Off --}}
                            <label class="sat-option {{ $currentRule === '4th_saturday_off' ? 'selected' : '' }}">
                                <input type="radio" name="saturday_rule" value="4th_saturday_off"
                                       {{ $currentRule === '4th_saturday_off' ? 'checked' : '' }} onchange="selectSatOption(this)">
                                <div class="opt-icon" style="background:#ede9fe; color:#7c3aed;">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">event_busy</span>
                                </div>
                                <div class="opt-body">
                                    <div class="opt-title">4th Saturday Off (Fixed)</div>
                                    <div class="opt-desc">The 4th Saturday of every month is a leave day for all employees in this department.</div>
                                </div>
                            </label>

                            {{-- 1 Flexible Saturday --}}
                            <label class="sat-option {{ $currentRule === 'flexible_saturday' ? 'selected' : '' }}">
                                <input type="radio" name="saturday_rule" value="flexible_saturday"
                                       {{ $currentRule === 'flexible_saturday' ? 'checked' : '' }} onchange="selectSatOption(this)">
                                <div class="opt-icon" style="background:#fef3c7; color:#d97706;">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">shuffle</span>
                                </div>
                                <div class="opt-body">
                                    <div class="opt-title">1 Flexible Saturday / Month</div>
                                    <div class="opt-desc">
                                        Employees may choose any one Saturday per month as their leave day.<br>
                                        Ideal for departments where team coverage rotates.
                                    </div>
                                </div>
                            </label>

                            {{-- Carry Forward --}}
                            <label class="sat-option carry-option {{ $currentRule === 'carry_forward' ? 'selected' : '' }}">
                                <input type="radio" name="saturday_rule" value="carry_forward"
                                       {{ $currentRule === 'carry_forward' ? 'checked' : '' }} onchange="selectSatOption(this)">
                                <div class="opt-icon" style="background:#ffedd5; color:#c2410c;">
                                    <span class="material-symbols-outlined" style="font-size:.9rem;">redo</span>
                                </div>
                                <div class="opt-body">
                                    <div class="opt-title">Carry Forward if All Saturdays Worked</div>
                                    <div class="opt-desc">
                                        If an employee works all Saturdays in a month, the unused flexible Saturday leave
                                        may be taken on any other working day that same month.
                                    </div>
                                </div>
                            </label>

                        </div>
                        @error('saturday_rule')
                            <span class="invalid-feedback" style="display:block; margin-top:.5rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Save button (bottom of left column) --}}
                <div style="padding-top:.25rem;">
                    <button type="submit" class="btn-save">
                        <span class="material-symbols-outlined" style="font-size:1rem">save</span>
                        Save Leave Rules
                    </button>
                </div>

            </div>

            {{-- ── RIGHT COLUMN ── --}}
            <div>

                {{-- Policy Summary --}}
                <div class="side-card">
                    <div class="side-card-header">
                        <div class="sh-icon sh-blue">
                            <span class="material-symbols-outlined" style="font-size:.875rem; font-variation-settings:'FILL' 1">info</span>
                        </div>
                        <h6>Policy Summary</h6>
                    </div>
                    <div class="side-card-body">
                        <ul class="policy-list">
                            <li>
                                <span class="material-symbols-outlined pol-green">check_circle</span>
                                Monday – Saturday working week
                            </li>
                            <li>
                                <span class="material-symbols-outlined pol-green">check_circle</span>
                                Minimum 9 hours login per day
                            </li>
                            <li>
                                <span class="material-symbols-outlined pol-blue">event_note</span>
                                <span id="summCl">1 Casual Leave per month</span>
                            </li>
                            <li>
                                <span class="material-symbols-outlined pol-blue">schedule</span>
                                <span id="summPerm">2 × 2h permissions per month</span>
                            </li>
                            <li id="summSatRow" style="{{ in_array($currentRule, ['flexible_saturday','carry_forward']) ? '' : 'display:none' }}">
                                <span class="material-symbols-outlined pol-orange">weekend</span>
                                1 flexible Saturday leave / month
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="side-card">
                    <div class="side-card-header">
                        <div class="sh-icon sh-gray">
                            <span class="material-symbols-outlined" style="font-size:.875rem;">edit_note</span>
                        </div>
                        <h6>Notes</h6>
                    </div>
                    <div class="side-card-body">
                        <textarea name="leave_rule_notes" class="form-textarea"
                                  placeholder="Optional admin notes about this rule…">{{ old('leave_rule_notes', $department->leave_rule_notes) }}</textarea>
                    </div>
                </div>

                {{-- Status --}}
                <div class="side-card">
                    <div class="side-card-header">
                        <div class="sh-icon" style="background:#dcfce7; color:#16a34a;">
                            <span class="material-symbols-outlined" style="font-size:.875rem; font-variation-settings:'FILL' 1;">toggle_on</span>
                        </div>
                        <h6>Status</h6>
                    </div>
                    <div class="side-card-body">
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <div class="toggle-title">Rule Active</div>
                                <div class="toggle-desc">Inactive rules are ignored in attendance and leave calculations.</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="hidden" name="leave_rule_active" value="0">
                                <input type="checkbox" name="leave_rule_active" value="1"
                                       {{ old('leave_rule_active', $department->leave_rule_active ?? true) ? 'checked' : '' }}
                                       id="ruleActiveToggle">
                                <span class="toggle-track"></span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </form>

@endsection

@push('scripts')
<script>
function selectSatOption(radio) {
    document.querySelectorAll('.sat-option').forEach(el => el.classList.remove('selected'));
    radio.closest('.sat-option').classList.add('selected');

    // Show/hide saturday leave row in policy summary
    const hasSat = ['flexible_saturday', 'carry_forward'].includes(radio.value);
    document.getElementById('summSatRow').style.display = hasSat ? '' : 'none';
}

function updateCreditNote() {
    const cl   = parseFloat(document.getElementById('clInput').value)   || 0;
    const perm = parseInt(document.getElementById('permInput').value)    || 0;
    const hrs  = parseInt(document.getElementById('hrsInput').value)     || 0;

    document.getElementById('noteClVal').textContent  = cl + ' CL';
    document.getElementById('notePermVal').textContent = perm + ' × ' + hrs + 'h permissions';
    document.getElementById('summCl').textContent     = cl + ' Casual Leave per month';
    document.getElementById('summPerm').textContent   = perm + ' × ' + hrs + 'h permissions per month';
}

['clInput','permInput','hrsInput'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateCreditNote);
});
</script>
@endpush
