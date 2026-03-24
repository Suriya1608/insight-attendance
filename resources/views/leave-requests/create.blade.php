@extends('layouts.app')

@section('title', 'Apply Leave / Permission')

@section('content')
<style>
    .create-header { margin-bottom: 24px; }
    .create-header h1 { font-size: 22px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
    .create-header p  { font-size: 13px; color: var(--text-secondary); margin: 0; }

    /* ── Balance strip ── */
    .balance-strip { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
    .balance-chip {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: var(--radius-sm);
        border: 1px solid var(--border); background: var(--surface);
        font-size: 12px; font-weight: 600;
    }
    .balance-chip .bc-icon { font-size: 16px; }
    .balance-chip .bc-val  { font-size: 16px; font-weight: 800; margin-left: 4px; }
    .bc-cl   { color: #1d4ed8; }
    .bc-perm { color: #7c3aed; }

    /* ── Form card ── */
    .create-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
        max-width: 680px;
    }
    .create-card-header {
        padding: 18px 24px 0; display: flex; align-items: center; gap: 12px;
    }
    .create-card-icon {
        width: 40px; height: 40px; background: var(--primary-subtle);
        border-radius: var(--radius-sm); display: flex; align-items: center;
        justify-content: center; color: var(--primary); flex-shrink: 0;
    }
    .create-card-icon .material-symbols-outlined { font-size: 20px; }
    .create-card-title { font-size: 15px; font-weight: 700; color: var(--text-main); }
    .create-card-body  { padding: 24px; }

    /* ── Request type toggle ── */
    .type-toggle { display: flex; gap: 10px; margin-bottom: 22px; }
    .type-btn {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 16px; border: 2px solid var(--border); border-radius: var(--radius-sm);
        background: var(--surface); cursor: pointer; font-size: 14px; font-weight: 600;
        color: var(--text-secondary); transition: all .15s; user-select: none;
    }
    .type-btn input[type=radio] { display: none; }
    .type-btn .material-symbols-outlined { font-size: 20px; }
    .type-btn.active {
        border-color: var(--primary); background: var(--primary-subtle);
        color: var(--primary);
    }

    /* ── Form groups ── */
    .form-group { margin-bottom: 18px; }
    .form-group label {
        display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary);
        text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px;
    }
    .form-group label .req { color: #dc2626; margin-left: 2px; }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 10px 12px; border: 1px solid var(--border);
        border-radius: var(--radius-sm); font-size: 14px; color: var(--text-main);
        background: var(--surface); outline: none; transition: border-color .15s;
        font-family: inherit;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle);
    }
    .form-input.is-invalid, .form-select.is-invalid, .form-textarea.is-invalid {
        border-color: #dc2626;
    }
    .form-textarea { resize: vertical; min-height: 90px; }
    .invalid-feedback { display: block; font-size: 12px; color: #dc2626; margin-top: 4px; }

    /* ── Date range row ── */
    .date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 480px) { .date-row { grid-template-columns: 1fr; } }

    /* ── Day preview ── */
    .day-preview {
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        background: #f8fafc; padding: 14px 16px; margin-top: 12px;
        display: none;
    }
    .day-preview-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: var(--text-muted); margin-bottom: 10px;
    }
    .dp-chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .dp-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: var(--radius-sm);
        font-size: 13px; font-weight: 700;
    }
    .dp-chip .material-symbols-outlined { font-size: 15px; }
    .dp-total { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .dp-cl    { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .dp-lop   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .dp-warn  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; margin-top: 8px;
                font-size: 12px; font-weight: 500; border-radius: var(--radius-sm);
                padding: 8px 12px; display: flex; align-items: flex-start; gap: 6px; }
    .dp-warn .material-symbols-outlined { font-size: 15px; flex-shrink: 0; }

    /* ── Hours selector ── */
    .hours-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .hours-btn {
        padding: 8px 16px; border: 2px solid var(--border); border-radius: var(--radius-sm);
        background: var(--surface); cursor: pointer; font-size: 13px; font-weight: 600;
        color: var(--text-secondary); transition: all .15s;
    }
    .hours-btn input[type=radio] { display: none; }
    .hours-btn.active { border-color: #7c3aed; background: #ede9fe; color: #6d28d9; }

    /* ── Info / warn box ── */
    .info-box {
        display: flex; align-items: flex-start; gap: 10px;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: var(--radius-sm); padding: 10px 14px;
        font-size: 12px; color: #1e40af; margin-bottom: 10px;
    }
    .info-box .material-symbols-outlined { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
    .warn-box {
        display: flex; align-items: flex-start; gap: 10px;
        background: #fffbeb; border: 1px solid #fde68a;
        border-radius: var(--radius-sm); padding: 10px 14px;
        font-size: 12px; color: #92400e; margin-bottom: 10px;
    }
    .warn-box .material-symbols-outlined { font-size: 16px; flex-shrink: 0; }

    /* ── Divider ── */
    .form-divider { border: none; border-top: 1px solid var(--border); margin: 4px 0 20px; }

    /* ── Actions ── */
    .form-actions { display: flex; gap: 10px; align-items: center; margin-top: 24px; }
    .btn-submit {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 24px; background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .btn-submit:hover { background: var(--primary-hover); }
    .btn-submit .material-symbols-outlined { font-size: 18px; }
    .btn-cancel {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 18px; border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 500; color: var(--text-secondary);
        text-decoration: none; background: var(--surface);
        transition: background .15s;
    }
    .btn-cancel:hover { background: var(--bg-light); color: var(--text-main); }
</style>

<div class="create-header">
    <h1>Apply Leave / Permission</h1>
    <p>Submit a new leave or permission request for approval.</p>
</div>

{{-- Balance strip --}}
<div class="balance-strip">
    <div class="balance-chip">
        <span class="material-symbols-outlined bc-icon bc-cl">event_available</span>
        <span>CL Balance:</span>
        <span class="bc-val bc-cl">{{ number_format($clBalance->balance, 1) }}</span>
        <span style="font-weight:400;color:var(--text-muted);">({{ $clMonthRemaining }} usable this month)</span>
    </div>
    <div class="balance-chip">
        <span class="material-symbols-outlined bc-icon bc-perm">access_time</span>
        <span>Permissions Remaining:</span>
        <span class="bc-val bc-perm">{{ $permRemaining }}</span>
        <span style="font-weight:400;color:var(--text-muted);">of {{ $permLimit }} this month</span>
    </div>
</div>

<div class="create-card">
    <div class="create-card-header">
        <div class="create-card-icon">
            <span class="material-symbols-outlined">add_task</span>
        </div>
        <div class="create-card-title">New Request</div>
    </div>
    <div class="create-card-body">
        <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data" id="lrForm">
            @csrf

            {{-- Request Type --}}
            <div class="form-group">
                <label>Request Type <span class="req">*</span></label>
                <div class="type-toggle">
                    <label class="type-btn {{ old('request_type', 'leave') === 'leave' ? 'active' : '' }}" id="btn-leave">
                        <input type="radio" name="request_type" value="leave"
                               {{ old('request_type', 'leave') === 'leave' ? 'checked' : '' }}
                               onchange="switchType('leave')">
                        <span class="material-symbols-outlined">event_note</span>
                        Leave
                    </label>
                    <label class="type-btn {{ old('request_type') === 'permission' ? 'active' : '' }}" id="btn-permission">
                        <input type="radio" name="request_type" value="permission"
                               {{ old('request_type') === 'permission' ? 'checked' : '' }}
                               onchange="switchType('permission')">
                        <span class="material-symbols-outlined">access_time</span>
                        Permission
                    </label>
                </div>
                @error('request_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- ── LEAVE section ── --}}
            <div id="leave-section" style="{{ old('request_type') === 'permission' ? 'display:none;' : '' }}">

                {{-- Info: auto CL/LOP --}}
                @if($clBalance->balance <= 0)
                <div class="warn-box">
                    <span class="material-symbols-outlined">warning</span>
                    <div>Your CL balance is <strong>0</strong>. All leave days will be taken as <strong>LOP</strong>.</div>
                </div>
                @elseif($clMonthRemaining <= 0)
                <div class="warn-box">
                    <span class="material-symbols-outlined">warning</span>
                    <div>You have already used 3 CL days this month. Additional leave days will be taken as <strong>LOP</strong>.</div>
                </div>
                @else
                <div class="info-box">
                    <span class="material-symbols-outlined">info</span>
                    <div>The system will automatically assign <strong>CL</strong> days first (up to your balance &amp; monthly cap of 3), then convert remaining days to <strong>LOP</strong>.</div>
                </div>
                @endif

                {{-- Date range --}}
                <div class="date-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="from_date">From Date <span class="req">*</span></label>
                        <input type="date" name="from_date" id="from_date"
                               value="{{ old('from_date', now()->toDateString()) }}"
                               min="{{ now()->toDateString() }}"
                               class="form-input {{ $errors->has('from_date') ? 'is-invalid' : '' }}"
                               onchange="calcLeaveDays()">
                        @error('from_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="to_date">To Date <span class="req">*</span></label>
                        <input type="date" name="to_date" id="to_date"
                               value="{{ old('to_date', now()->toDateString()) }}"
                               min="{{ now()->toDateString() }}"
                               class="form-input {{ $errors->has('to_date') ? 'is-invalid' : '' }}"
                               onchange="calcLeaveDays()">
                        @error('to_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Day preview --}}
                <div class="day-preview" id="day-preview">
                    <div class="day-preview-title">Leave Day Preview</div>
                    <div class="dp-chips">
                        <span class="dp-chip dp-total">
                            <span class="material-symbols-outlined">calendar_month</span>
                            <span id="dp-total">0</span> Working Day(s)
                        </span>
                        <span class="dp-chip dp-cl" id="dp-cl-chip">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span id="dp-cl">0</span> CL
                        </span>
                        <span class="dp-chip dp-lop" id="dp-lop-chip" style="display:none;">
                            <span class="material-symbols-outlined">money_off</span>
                            <span id="dp-lop">0</span> LOP
                        </span>
                    </div>
                    <div class="dp-warn" id="dp-warn" style="display:none;">
                        <span class="material-symbols-outlined">warning</span>
                        <span id="dp-warn-text"></span>
                    </div>
                </div>

            </div>

            {{-- ── PERMISSION section ── --}}
            <div id="permission-section" style="{{ old('request_type') !== 'permission' ? 'display:none;' : '' }}">
                <div class="form-group">
                    <label>Permission Hours <span class="req">*</span></label>
                    @php $oldHours = old('permission_hours', '1'); @endphp
                    <div class="hours-btns">
                        @for($h = 1; $h <= $hoursPerPerm; $h++)
                        <label class="hours-btn {{ (string)$oldHours == (string)$h ? 'active' : '' }}" id="hbtn-{{ $h }}">
                            <input type="radio" name="permission_hours" value="{{ $h }}"
                                   {{ (string)$oldHours == (string)$h ? 'checked' : '' }}
                                   onchange="selectHour({{ $h }})">
                            {{ $h }} Hour{{ $h > 1 ? 's' : '' }}
                        </label>
                        @endfor
                    </div>
                    @error('permission_hours')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div class="info-box" style="margin-top:10px;">
                        <span class="material-symbols-outlined">info</span>
                        Approved permission hours are added to your effective working hours in attendance reports.
                        You have <strong>{{ $permRemaining }}</strong> permission{{ $permRemaining !== 1 ? 's' : '' }} remaining this month.
                    </div>
                </div>

                <div class="form-group">
                    <label for="request_date">Date <span class="req">*</span></label>
                    <input type="date"
                           name="request_date"
                           id="request_date"
                           value="{{ old('request_date', now()->toDateString()) }}"
                           min="{{ now()->toDateString() }}"
                           class="form-input {{ $errors->has('request_date') ? 'is-invalid' : '' }}">
                    @error('request_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="form-divider">

            {{-- Reason --}}
            <div class="form-group">
                <label for="reason">Reason <span class="req">*</span></label>
                <textarea name="reason" id="reason"
                          class="form-textarea {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                          placeholder="Briefly describe the reason for your request…"
                          maxlength="1000">{{ old('reason') }}</textarea>
                @error('reason')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Attachment --}}
            <div class="form-group">
                <label for="attachment">Attachment <span style="font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></label>
                <input type="file" name="attachment" id="attachment"
                       class="form-input {{ $errors->has('attachment') ? 'is-invalid' : '' }}"
                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                       style="padding:7px 12px;">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                    Accepted: JPG, PNG, PDF, DOC, DOCX · Max 4 MB
                </div>
                @error('attachment')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <span class="material-symbols-outlined">send</span>
                    Submit Request
                </button>
                <a href="{{ route('leave-requests.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
const HOLIDAY_DATES   = @json($holidayDates);
const CL_BALANCE      = {{ (int) floor($clBalance->balance) }};
const CL_MONTH_REMAIN = {{ $clMonthRemaining }};

function switchType(type) {
    const leaveSection = document.getElementById('leave-section');
    const permSection  = document.getElementById('permission-section');
    const btnLeave     = document.getElementById('btn-leave');
    const btnPerm      = document.getElementById('btn-permission');

    if (type === 'leave') {
        leaveSection.style.display = '';
        permSection.style.display  = 'none';
        btnLeave.classList.add('active');
        btnPerm.classList.remove('active');
        calcLeaveDays();
    } else {
        leaveSection.style.display = 'none';
        permSection.style.display  = '';
        btnPerm.classList.add('active');
        btnLeave.classList.remove('active');
    }
}

function selectHour(h) {
    document.querySelectorAll('.hours-btn').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('hbtn-' + h);
    if (btn) btn.classList.add('active');
}

function calcLeaveDays() {
    const fromVal = document.getElementById('from_date').value;
    const toVal   = document.getElementById('to_date').value;
    const preview = document.getElementById('day-preview');

    if (!fromVal || !toVal) { preview.style.display = 'none'; return; }

    const from = new Date(fromVal + 'T00:00:00');
    const to   = new Date(toVal   + 'T00:00:00');

    if (to < from) { preview.style.display = 'none'; return; }

    let workingDays = 0;
    const cur = new Date(from);
    while (cur <= to) {
        const dow     = cur.getDay(); // 0 = Sunday
        const dateStr = cur.toISOString().split('T')[0];
        if (dow !== 0 && !HOLIDAY_DATES.includes(dateStr)) {
            workingDays++;
        }
        cur.setDate(cur.getDate() + 1);
    }

    if (workingDays === 0) {
        preview.style.display = 'block';
        document.getElementById('dp-total').textContent = '0';
        document.getElementById('dp-cl').textContent    = '0';
        document.getElementById('dp-lop').textContent   = '0';
        document.getElementById('dp-cl-chip').style.display  = 'none';
        document.getElementById('dp-lop-chip').style.display = 'none';
        const warn = document.getElementById('dp-warn');
        warn.style.display = 'flex';
        document.getElementById('dp-warn-text').textContent =
            'The selected range has no working days (all Sundays or holidays). Please choose different dates.';
        return;
    }

    const clAvailable = Math.min(CL_BALANCE, CL_MONTH_REMAIN);
    const clDays  = Math.min(workingDays, Math.max(0, clAvailable));
    const lopDays = workingDays - clDays;

    document.getElementById('dp-total').textContent = workingDays;
    document.getElementById('dp-cl').textContent    = clDays;
    document.getElementById('dp-lop').textContent   = lopDays;

    document.getElementById('dp-cl-chip').style.display  = clDays  > 0 ? 'inline-flex' : 'none';
    document.getElementById('dp-lop-chip').style.display = lopDays > 0 ? 'inline-flex' : 'none';

    const warn = document.getElementById('dp-warn');
    if (lopDays > 0 && clDays > 0) {
        warn.style.display = 'flex';
        document.getElementById('dp-warn-text').textContent =
            `${clDays} day(s) will be CL and ${lopDays} day(s) will be LOP (balance/monthly cap partially exhausted).`;
    } else if (lopDays > 0 && clDays === 0) {
        warn.style.display = 'flex';
        document.getElementById('dp-warn-text').textContent =
            'All days will be LOP — your CL balance or monthly cap is exhausted.';
    } else {
        warn.style.display = 'none';
    }

    preview.style.display = 'block';
}

// Run on page load to show preview if old values exist
document.addEventListener('DOMContentLoaded', () => {
    const type = document.querySelector('input[name=request_type]:checked')?.value;
    if (type === 'leave') calcLeaveDays();
});
</script>
@endsection
