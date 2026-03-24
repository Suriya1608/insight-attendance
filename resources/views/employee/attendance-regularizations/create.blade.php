@extends('layouts.app')

@section('title', 'Attendance Regularization')

@php
    $hasSelectedDate = $selectedDate !== '';
    $initialPunchIn = $attendance?->punch_in ? substr($attendance->punch_in, 0, 5) : '';
    $initialPunchOut = $attendance?->punch_out ? substr($attendance->punch_out, 0, 5) : '';
    $snapshotDateLabel = $hasSelectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d M Y') : '-';
    $oldRequestType = old('request_type', $selectedType);
    $requestedPunchIn = old('requested_punch_in', '');
    $requestedPunchOut = old('requested_punch_out', '');
@endphp

@push('styles')
<style>
    .page-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden; }
    .page-card-header { padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc; }
    .page-card-body { padding:1.25rem; }
    .grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem; }
    @media(max-width:768px){ .grid { grid-template-columns:1fr; } }
    .form-group label { display:block;font-size:.8rem;font-weight:700;color:var(--text-secondary);margin-bottom:.35rem; }
    .field-header { display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.35rem; }
    .field-chip { display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.7rem;font-weight:700; }
    .field-chip[hidden] { display:none; }
    .form-control { width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);font-size:.88rem; }
    .form-control.is-readonly { background:#f8fafc;color:var(--text-secondary); }
    .form-control.is-disabled,
    .form-control:disabled { background:#f8fafc;color:#94a3b8;cursor:not-allowed; }
    .form-help { font-size:.77rem;color:var(--text-muted);margin-top:.3rem; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.6rem 1rem;font-size:.84rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer; }
    .btn-outline { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.55rem .95rem;font-size:.84rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .snapshot { background:#f8fafc;border:1px dashed var(--border);border-radius:var(--radius-md);padding:1rem 1.1rem;margin-bottom:1rem; }
    .error-list { margin:0 0 1rem;padding:0;list-style:none;background:#fff1f2;border:1px solid #fecdd3;border-radius:var(--radius-md);color:#be123c; }
    .error-list li { padding:.7rem 1rem;border-bottom:1px solid #fecdd3; }
    .error-list li:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Attendance Regularization</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Correct missed punch-out, missed punch-in, or other attendance time issues.</p>
    </div>
    <a href="{{ route($indexRoute) }}" class="btn-outline">
        <span class="material-symbols-outlined" style="font-size:1rem;">arrow_back</span>
        Back to List
    </a>
</div>

<div class="page-card">
    <div class="page-card-header">
        <strong>System Snapshot</strong>
    </div>
    <div class="page-card-body">
        @if($errors->any())
            <ul class="error-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="snapshot">
            <div style="font-size:.85rem;color:var(--text-secondary);display:flex;gap:1rem;flex-wrap:wrap;">
                <span><strong>Date:</strong> <span data-snapshot="date">{{ $snapshotDateLabel }}</span></span>
                <span><strong>System Punch In:</strong> <span data-snapshot="punch_in">{{ $initialPunchIn ?: '-' }}</span></span>
                <span><strong>System Punch Out:</strong> <span data-snapshot="punch_out">{{ $initialPunchOut ?: '-' }}</span></span>
                <span><strong>Work Hours:</strong> <span data-snapshot="work_hours">{{ $attendance?->formatted_work_hours ?? '-' }}</span></span>
            </div>
        </div>

        <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data" data-regularization-form data-snapshot-url="{{ route($snapshotRoute) }}">
            @csrf
            <div class="grid">
                <div class="form-group">
                    <label for="regularization_date">Date</label>
                    <input type="date" id="regularization_date" name="date" class="form-control" value="{{ old('date', $selectedDate) }}" max="{{ now()->toDateString() }}" required>
                    <div class="form-help">Choose a date first. Requested punch times must stay on this same date.</div>
                </div>
                <div class="form-group">
                    <label for="request_type">Request Type</label>
                    <select id="request_type" name="request_type" class="form-control {{ $hasSelectedDate ? '' : 'is-disabled' }}" {{ $hasSelectedDate ? '' : 'disabled' }} required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ $oldRequestType === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-help" data-role="type-help">{{ $hasSelectedDate ? 'Select the correction type for the chosen date.' : 'Pick a date before choosing the request type.' }}</div>
                </div>
                <div class="form-group">
                    <div class="field-header">
                        <label for="requested_punch_in" style="margin-bottom:0;">Actual Punch-In Time</label>
                        <span class="field-chip" data-role="punch-in-chip">System Generated</span>
                    </div>
                    <input
                        type="time"
                        id="requested_punch_in"
                        name="requested_punch_in"
                        class="form-control"
                        value="{{ $requestedPunchIn }}"
                        data-system-value="{{ $initialPunchIn }}"
                        data-manual-value="{{ $requestedPunchIn }}"
                    >
                    <div class="form-help" data-role="punch-in-help">Choose a date to load the system punch-in time.</div>
                </div>
                <div class="form-group">
                    <div class="field-header">
                        <label for="requested_punch_out" style="margin-bottom:0;">Actual Punch-Out Time</label>
                        <span class="field-chip" data-role="punch-out-chip">System Generated</span>
                    </div>
                    <input
                        type="time"
                        id="requested_punch_out"
                        name="requested_punch_out"
                        class="form-control"
                        value="{{ $requestedPunchOut }}"
                        data-system-value="{{ $initialPunchOut }}"
                        data-manual-value="{{ $requestedPunchOut }}"
                    >
                    <div class="form-help" data-role="punch-out-help">Choose a date to load the system punch-out time.</div>
                </div>
            </div>
            <div class="form-group" style="margin-top:1rem;">
                <label for="reason">Reason</label>
                <textarea id="reason" name="reason" class="form-control" rows="5" required>{{ old('reason') }}</textarea>
            </div>
            <div class="form-group" style="margin-top:1rem;">
                <label for="attachment">Attachment</label>
                <input type="file" id="attachment" name="attachment" class="form-control">
                <div class="form-help">Optional. Accepted: JPG, PNG, PDF, DOC, DOCX up to 5 MB.</div>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem;">
                <button type="submit" name="action" value="save" class="btn-outline">Save Draft</button>
                <button type="submit" name="action" value="submit" class="btn-primary">
                    <span class="material-symbols-outlined" style="font-size:1rem;">send</span>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const today = '{{ now()->toDateString() }}';
        const currentTime = '{{ now()->format('H:i') }}';
        const fallbackType = 'missed_punch_out';

        function initRegularizationForm(form) {
            const snapshotUrl = form.dataset.snapshotUrl;
            const dateInput = form.querySelector('[name="date"]');
            const typeInput = form.querySelector('[name="request_type"]');
            const typeHelp = form.querySelector('[data-role="type-help"]');
            const punchInInput = form.querySelector('[name="requested_punch_in"]');
            const punchOutInput = form.querySelector('[name="requested_punch_out"]');
            const punchInChip = form.querySelector('[data-role="punch-in-chip"]');
            const punchOutChip = form.querySelector('[data-role="punch-out-chip"]');
            const punchInHelp = form.querySelector('[data-role="punch-in-help"]');
            const punchOutHelp = form.querySelector('[data-role="punch-out-help"]');
            const snapshotDate = document.querySelector('[data-snapshot="date"]');
            const snapshotPunchIn = document.querySelector('[data-snapshot="punch_in"]');
            const snapshotPunchOut = document.querySelector('[data-snapshot="punch_out"]');
            const snapshotWorkHours = document.querySelector('[data-snapshot="work_hours"]');
            let activeRequest = null;

            function setSnapshot(data) {
                snapshotDate.textContent = data.formatted_date || '-';
                snapshotPunchIn.textContent = data.system_punch_in || '-';
                snapshotPunchOut.textContent = data.system_punch_out || '-';
                snapshotWorkHours.textContent = data.work_hours || '-';
                punchInInput.dataset.systemValue = data.system_punch_in || '';
                punchOutInput.dataset.systemValue = data.system_punch_out || '';
            }

            function clearSnapshot() {
                setSnapshot({
                    formatted_date: '-',
                    system_punch_in: '',
                    system_punch_out: '',
                    work_hours: '-',
                });
            }

            function rememberManualValue(input) {
                if (!input.disabled) {
                    input.dataset.manualValue = input.value;
                }
            }

            function updateTimeLimit() {
                const maxTime = dateInput.value === today ? currentTime : '23:59';
                [punchInInput, punchOutInput].forEach((input) => {
                    input.max = maxTime;
                });
            }

            function setTypeAvailability(enabled) {
                typeInput.disabled = !enabled;
                typeInput.classList.toggle('is-disabled', !enabled);
                typeHelp.textContent = enabled
                    ? 'Select the correction type for the chosen date.'
                    : 'Pick a date before choosing the request type.';
            }

            function applyFieldState(input, chip, help, config) {
                input.disabled = !config.editable;
                input.readOnly = !config.editable;
                input.required = !!config.required;
                input.classList.toggle('is-readonly', !config.editable);
                chip.hidden = config.editable;
                help.textContent = config.help;
                input.value = config.editable ? config.value : config.systemValue;
            }

            function syncFields() {
                updateTimeLimit();

                const hasDate = dateInput.value !== '';
                setTypeAvailability(hasDate);

                if (!hasDate) {
                    clearSnapshot();
                    punchInInput.dataset.manualValue = '';
                    punchOutInput.dataset.manualValue = '';
                    applyFieldState(punchInInput, punchInChip, punchInHelp, {
                        editable: false,
                        required: false,
                        systemValue: '',
                        value: '',
                        help: 'Choose a date to load the system punch-in time.',
                    });
                    applyFieldState(punchOutInput, punchOutChip, punchOutHelp, {
                        editable: false,
                        required: false,
                        systemValue: '',
                        value: '',
                        help: 'Choose a date to load the system punch-out time.',
                    });
                    return;
                }

                const requestType = typeInput.value || fallbackType;
                const systemPunchIn = punchInInput.dataset.systemValue || '';
                const systemPunchOut = punchOutInput.dataset.systemValue || '';
                const manualPunchIn = punchInInput.dataset.manualValue || '';
                const manualPunchOut = punchOutInput.dataset.manualValue || '';

                if (requestType === 'missed_punch_out') {
                    applyFieldState(punchInInput, punchInChip, punchInHelp, {
                        editable: false,
                        required: false,
                        systemValue: systemPunchIn,
                        value: systemPunchIn,
                        help: 'System Generated. Punch-in is locked for missed punch-out requests.',
                    });
                    applyFieldState(punchOutInput, punchOutChip, punchOutHelp, {
                        editable: true,
                        required: true,
                        systemValue: systemPunchOut,
                        value: manualPunchOut,
                        help: 'Enter the missing punch-out time for the selected date.',
                    });
                    return;
                }

                if (requestType === 'missed_punch_in') {
                    applyFieldState(punchInInput, punchInChip, punchInHelp, {
                        editable: true,
                        required: true,
                        systemValue: systemPunchIn,
                        value: manualPunchIn,
                        help: 'Enter the missing punch-in time for the selected date.',
                    });
                    applyFieldState(punchOutInput, punchOutChip, punchOutHelp, {
                        editable: false,
                        required: false,
                        systemValue: systemPunchOut,
                        value: systemPunchOut,
                        help: 'System Generated. Punch-out is locked for missed punch-in requests.',
                    });
                    return;
                }

                applyFieldState(punchInInput, punchInChip, punchInHelp, {
                    editable: true,
                    required: false,
                    systemValue: systemPunchIn,
                    value: manualPunchIn || systemPunchIn,
                    help: 'Prefilled from the system. You can edit this value for a time correction.',
                });
                applyFieldState(punchOutInput, punchOutChip, punchOutHelp, {
                    editable: true,
                    required: false,
                    systemValue: systemPunchOut,
                    value: manualPunchOut || systemPunchOut,
                    help: 'Prefilled from the system. You can edit this value for a time correction.',
                });
            }

            async function loadSnapshot() {
                const selectedDate = dateInput.value;

                if (!selectedDate) {
                    syncFields();
                    return;
                }

                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new AbortController();

                try {
                    const response = await fetch(`${snapshotUrl}?date=${encodeURIComponent(selectedDate)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: activeRequest.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load attendance snapshot.');
                    }

                    const data = await response.json();
                    setSnapshot(data);
                    syncFields();
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    clearSnapshot();
                    syncFields();
                }
            }

            [punchInInput, punchOutInput].forEach((input) => {
                input.addEventListener('input', function () {
                    rememberManualValue(this);
                });
            });

            typeInput.addEventListener('change', function () {
                if (!dateInput.value) {
                    typeInput.value = fallbackType;
                    return;
                }
                syncFields();
            });

            dateInput.addEventListener('change', function () {
                if (!typeInput.value) {
                    typeInput.value = fallbackType;
                }
                loadSnapshot();
            });

            if (!typeInput.value) {
                typeInput.value = fallbackType;
            }

            if (dateInput.value) {
                loadSnapshot();
            } else {
                syncFields();
            }
        }

        document.querySelectorAll('[data-regularization-form]').forEach(initRegularizationForm);
    })();
</script>
@endpush
