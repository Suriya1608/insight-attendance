@extends('layouts.app')

@section('title', 'Attendance Regularization - ' . $regularization->date->format('d M Y'))

@php
    $systemPunchIn = $regularization->original_punch_in ? substr($regularization->original_punch_in, 0, 5) : '';
    $systemPunchOut = $regularization->original_punch_out ? substr($regularization->original_punch_out, 0, 5) : '';
    $requestedPunchIn = old('requested_punch_in', $regularization->requested_punch_in ? substr($regularization->requested_punch_in, 0, 5) : '');
    $requestedPunchOut = old('requested_punch_out', $regularization->requested_punch_out ? substr($regularization->requested_punch_out, 0, 5) : '');
@endphp

@push('styles')
<style>
    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:999px;font-size:.78rem;font-weight:700; }
    .badge-gray { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-green { background:#f0fdf4;color:#15803d; }
    .badge-red { background:#fff1f2;color:#dc2626; }
    .layout { display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.25rem;align-items:start; }
    @media(max-width:960px){ .layout { grid-template-columns:1fr; } }
    .card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden; }
    .card-header { padding:.9rem 1.25rem;border-bottom:1px solid var(--border);background:#f8fafc;font-size:.88rem;font-weight:800; }
    .card-body { padding:1.25rem; }
    .grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem; }
    @media(max-width:768px){ .grid { grid-template-columns:1fr; } }
    .form-group label { display:block;font-size:.8rem;font-weight:700;color:var(--text-secondary);margin-bottom:.35rem; }
    .field-header { display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.35rem; }
    .field-chip { display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .6rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.7rem;font-weight:700; }
    .field-chip[hidden] { display:none; }
    .form-control { width:100%;padding:.6rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);font-size:.88rem; }
    .form-control.is-readonly { background:#f8fafc;color:var(--text-secondary); }
    .form-help { font-size:.77rem;color:var(--text-muted);margin-top:.3rem; }
    .detail-row { display:flex;justify-content:space-between;gap:1rem;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.84rem; }
    .detail-row:last-child { border-bottom:none; }
    .detail-row span:last-child { font-weight:700;color:var(--text-main);text-align:right; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.55rem 1rem;font-size:.84rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer; }
    .btn-outline { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem .95rem;font-size:.84rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer; }
    .comment-item { display:flex;gap:.75rem;margin-bottom:.9rem; }
    .avatar { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#60a5fa);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0; }
    .bubble { flex:1;background:#f8fafc;border:1px solid var(--border);border-radius:0 var(--radius-md) var(--radius-md) var(--radius-md);padding:.7rem .85rem; }
    .reply-box { margin-top:.6rem;padding-top:.6rem;border-top:1px solid var(--border); }
    .error-list { margin:0 0 1rem;padding:0;list-style:none;background:#fff1f2;border:1px solid #fecdd3;border-radius:var(--radius-md);color:#be123c; }
    .error-list li { padding:.7rem 1rem;border-bottom:1px solid #fecdd3; }
    .error-list li:last-child { border-bottom:none; }
</style>
@endpush

@section('content')
@php
    $colorMap = ['gray' => 'badge-gray', 'orange' => 'badge-orange', 'blue' => 'badge-blue', 'green' => 'badge-green', 'red' => 'badge-red'];
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title" style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            Attendance Regularization
            <span class="status-badge {{ $colorMap[$regularization->status_color] ?? 'badge-gray' }}">{{ $regularization->status_label }}</span>
        </h1>
        <p class="page-subtitle" style="margin-bottom:0;">{{ $regularization->type_label }} for {{ $regularization->date->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route($indexRoute) }}" class="btn-outline">
        <span class="material-symbols-outlined" style="font-size:1rem;">arrow_back</span>
        Back to List
    </a>
</div>

<div class="layout">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">Request Details</div>
            <div class="card-body">
                @if($regularization->isEditable())
                    @if($errors->any())
                        <ul class="error-list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route($updateRoute, $regularization) }}" enctype="multipart/form-data" data-regularization-form>
                        @csrf
                        @method('PUT')
                        <div class="grid">
                            <div class="form-group">
                                <label for="regularization_date">Date</label>
                                <input type="date" id="regularization_date" name="date" class="form-control" value="{{ old('date', $regularization->date->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                                <div class="form-help">Requested punch times must stay on this same date.</div>
                            </div>
                            <div class="form-group">
                                <label for="request_type">Request Type</label>
                                <select id="request_type" name="request_type" class="form-control" required>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('request_type', $regularization->request_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
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
                                    data-system-value="{{ $systemPunchIn }}"
                                    data-manual-value="{{ $requestedPunchIn }}"
                                >
                                <div class="form-help" data-role="punch-in-help">Prefilled from the system when available.</div>
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
                                    data-system-value="{{ $systemPunchOut }}"
                                    data-manual-value="{{ $requestedPunchOut }}"
                                >
                                <div class="form-help" data-role="punch-out-help">Prefilled from the system when available.</div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:1rem;">
                            <label for="reason">Reason</label>
                            <textarea id="reason" name="reason" class="form-control" rows="5" required>{{ old('reason', $regularization->reason) }}</textarea>
                        </div>
                        <div class="form-group" style="margin-top:1rem;">
                            <label for="attachment">Attachment</label>
                            <input type="file" id="attachment" name="attachment" class="form-control">
                            @if($regularization->attachment_path)
                                <div style="margin-top:.45rem;"><a href="{{ Storage::url($regularization->attachment_path) }}" target="_blank">View current attachment</a></div>
                            @endif
                        </div>
                        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem;">
                            <button type="submit" name="action" value="save" class="btn-outline">Save Changes</button>
                            <button type="submit" name="action" value="submit" class="btn-primary">
                                <span class="material-symbols-outlined" style="font-size:1rem;">send</span>
                                Submit Request
                            </button>
                        </div>
                    </form>
                @else
                    <div class="detail-row"><span>Request Type</span><span>{{ $regularization->type_label }}</span></div>
                    <div class="detail-row"><span>Original Punch In</span><span>{{ $regularization->original_punch_in ? substr($regularization->original_punch_in, 0, 5) : '-' }}</span></div>
                    <div class="detail-row"><span>Original Punch Out</span><span>{{ $regularization->original_punch_out ? substr($regularization->original_punch_out, 0, 5) : '-' }}</span></div>
                    <div class="detail-row"><span>Requested Punch In</span><span>{{ $regularization->requested_punch_in ? substr($regularization->requested_punch_in, 0, 5) : '-' }}</span></div>
                    <div class="detail-row"><span>Requested Punch Out</span><span>{{ $regularization->requested_punch_out ? substr($regularization->requested_punch_out, 0, 5) : '-' }}</span></div>
                    <div class="detail-row"><span>Submitted At</span><span>{{ $regularization->submitted_at?->format('d M Y, h:i A') ?? '-' }}</span></div>
                    <div style="margin-top:1rem;font-size:.84rem;color:var(--text-secondary);line-height:1.6;">{{ $regularization->reason }}</div>
                    @if($regularization->attachment_path)
                        <div style="margin-top:1rem;"><a href="{{ Storage::url($regularization->attachment_path) }}" target="_blank">Open attachment</a></div>
                    @endif
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Discussion</div>
            <div class="card-body">
                @foreach($regularization->comments->whereNull('parent_id') as $comment)
                    <div class="comment-item">
                        <div class="avatar">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</div>
                        <div class="bubble">
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.35rem;">
                                <strong>{{ $comment->user->name }}</strong>
                                <span style="font-size:.74rem;color:var(--text-muted);">{{ ucfirst($comment->user->role) }}</span>
                                <span style="font-size:.74rem;color:var(--text-muted);margin-left:auto;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-size:.84rem;line-height:1.6;">{{ $comment->comment }}</div>
                            @if($comment->replies->count())
                                <div class="reply-box">
                                    @foreach($comment->replies as $reply)
                                        <div style="margin-bottom:.6rem;font-size:.82rem;">
                                            <strong>{{ $reply->user->name }}</strong>
                                            <span style="color:var(--text-muted);font-size:.73rem;">{{ $reply->created_at->diffForHumans() }}</span>
                                            <div style="margin-top:.2rem;line-height:1.5;">{{ $reply->comment }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <form method="POST" action="{{ route($commentRoute, $regularization) }}" style="margin-top:.65rem;">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <textarea name="comment" class="form-control" rows="2" placeholder="Reply to this comment..."></textarea>
                                <button type="submit" class="btn-outline" style="margin-top:.5rem;">Reply</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route($commentRoute, $regularization) }}">
                    @csrf
                    <div class="form-group">
                        <label>Add Comment</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Add a comment or response..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Post Comment</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">Approval Summary</div>
            <div class="card-body">
                <div class="detail-row"><span>L1 Manager</span><span>{{ $regularization->l1Manager?->name ?? '-' }}</span></div>
                <div class="detail-row"><span>L1 Comment</span><span>{{ $regularization->l1_comment ?: '-' }}</span></div>
                <div class="detail-row"><span>L2 Manager</span><span>{{ $regularization->l2Manager?->name ?? '-' }}</span></div>
                <div class="detail-row"><span>L2 Comment</span><span>{{ $regularization->l2_comment ?: '-' }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Attendance Snapshot</div>
            <div class="card-body">
                <div class="detail-row"><span>Current Punch In</span><span>{{ $regularization->attendance?->punch_in ? substr($regularization->attendance->punch_in, 0, 5) : ($regularization->original_punch_in ? substr($regularization->original_punch_in, 0, 5) : '-') }}</span></div>
                <div class="detail-row"><span>Current Punch Out</span><span>{{ $regularization->attendance?->punch_out ? substr($regularization->attendance->punch_out, 0, 5) : ($regularization->original_punch_out ? substr($regularization->original_punch_out, 0, 5) : '-') }}</span></div>
                <div class="detail-row"><span>Working Hours</span><span>{{ $regularization->attendance?->formatted_work_hours ?? '-' }}</span></div>
                <div class="detail-row"><span>Finalized At</span><span>{{ $regularization->finalized_at?->format('d M Y, h:i A') ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($regularization->isEditable())
    @push('scripts')
    <script>
        (function () {
            const today = '{{ now()->toDateString() }}';
            const currentTime = '{{ now()->format('H:i') }}';

            function initRegularizationForm(form) {
                const typeInput = form.querySelector('[name="request_type"]');
                const dateInput = form.querySelector('[name="date"]');
                const punchInInput = form.querySelector('[name="requested_punch_in"]');
                const punchOutInput = form.querySelector('[name="requested_punch_out"]');
                const punchInChip = form.querySelector('[data-role="punch-in-chip"]');
                const punchOutChip = form.querySelector('[data-role="punch-out-chip"]');
                const punchInHelp = form.querySelector('[data-role="punch-in-help"]');
                const punchOutHelp = form.querySelector('[data-role="punch-out-help"]');

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

                    const requestType = typeInput.value;
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

                [punchInInput, punchOutInput].forEach((input) => {
                    input.addEventListener('input', function () {
                        rememberManualValue(this);
                    });
                });

                typeInput.addEventListener('change', syncFields);
                dateInput.addEventListener('change', updateTimeLimit);

                syncFields();
            }

            document.querySelectorAll('[data-regularization-form]').forEach(initRegularizationForm);
        })();
    </script>
    @endpush
@endif
