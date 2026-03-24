@extends('layouts.app')

@section('title', $employee->name . ' — Leave Details')

@push('styles')
<style>
    .breadcrumb-bar {
        display:flex; align-items:center; gap:.375rem;
        font-size:.8125rem; color:var(--text-muted); margin-bottom:1.25rem;
    }
    .breadcrumb-bar a { color:var(--primary); text-decoration:none; font-weight:500; }
    .breadcrumb-bar a:hover { text-decoration:underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size:.9375rem; }

    /* Profile bar */
    .profile-bar {
        display:flex; align-items:center; gap:.875rem;
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius-md); padding:1rem 1.25rem;
        box-shadow:var(--shadow-sm); margin-bottom:1.25rem;
    }
    .profile-avatar {
        width:46px; height:46px; border-radius:50%;
        background:var(--primary); color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-size:1rem; font-weight:700; flex-shrink:0;
    }
    .profile-name  { font-size:1rem; font-weight:700; color:var(--text-main); }
    .profile-meta  { font-size:.8rem; color:var(--text-muted); }
    .role-pill {
        display:inline-block; padding:.15rem .6rem;
        border-radius:20px; font-size:.75rem; font-weight:600;
        background:#e0f2fe; color:#0369a1; margin-left:.5rem;
    }
    .role-pill.manager { background:#ede9fe; color:#6d28d9; }

    /* Balance cards */
    .bal-grid {
        display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
        gap:.875rem; margin-bottom:1.25rem;
    }
    .bal-card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius-md); box-shadow:var(--shadow-sm); overflow:hidden;
    }
    .bal-card-header {
        display:flex; align-items:center; gap:.5rem;
        padding:.75rem 1rem; border-bottom:1px solid var(--border); background:#fafbfd;
    }
    .bal-card-header .icon {
        width:28px; height:28px; border-radius:7px;
        display:flex; align-items:center; justify-content:center;
        font-size:15px; color:#fff; flex-shrink:0;
    }
    .bal-card-header h6 { font-size:.8rem; font-weight:700; margin:0; text-transform:uppercase; letter-spacing:.04em; }
    .bal-card-body { padding:.875rem 1rem; }
    .bal-stat-row { display:flex; gap:0; border:1px solid var(--border); border-radius:6px; overflow:hidden; }
    .bal-stat { flex:1; padding:.625rem .5rem; border-right:1px solid var(--border); text-align:center; }
    .bal-stat:last-child { border-right:none; }
    .bal-stat-val { font-size:1.375rem; font-weight:800; letter-spacing:-.02em; line-height:1; }
    .bal-stat-lbl { font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); margin-top:.25rem; }
    .v-cr  { color:#1d4ed8; }
    .v-us  { color:#dc2626; }
    .v-bal { color:#16a34a; }
    .no-bal { color:var(--text-muted); font-size:.8rem; margin-top:.5rem; }

    /* Adjust form card */
    .adjust-card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius-md); box-shadow:var(--shadow-sm); overflow:hidden;
        margin-bottom:1.25rem;
    }
    .adjust-card-header {
        display:flex; align-items:center; gap:.5rem;
        padding:.875rem 1.25rem; border-bottom:1px solid var(--border); background:#fafbfd;
    }
    .adjust-card-header .material-symbols-outlined { color:var(--primary); font-size:1.1rem; }
    .adjust-card-header h5 { font-size:.9rem; font-weight:700; margin:0; }
    .adjust-card-body { padding:1.25rem; }

    .form-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:.875rem; }
    @media(max-width:600px) { .form-row { grid-template-columns:1fr; } }

    .form-label {
        font-size:.8rem; font-weight:600; color:var(--text-main);
        margin-bottom:.3rem; display:block;
    }
    .form-control, .form-select {
        height:2.5rem; padding:0 .75rem;
        border:1.5px solid var(--border); border-radius:var(--radius-sm);
        font-size:.875rem; color:var(--text-main); background:#f8fafc;
        transition:border-color .2s; width:100%;
    }
    .form-control:focus, .form-select:focus {
        border-color:var(--primary); outline:none; background:#fff;
        box-shadow:0 0 0 3px var(--primary-subtle);
    }
    .form-control.is-invalid, .form-select.is-invalid { border-color:#ef4444; }
    .invalid-feedback { font-size:.78rem; color:#ef4444; margin-top:.25rem; display:block; }

    .btn-adjust {
        height:2.5rem; padding:0 1.25rem;
        background:var(--primary); border:none;
        border-radius:var(--radius-sm); color:#fff;
        font-size:.875rem; font-weight:600;
        display:inline-flex; align-items:center; gap:.375rem;
        cursor:pointer; transition:background .15s;
        box-shadow:0 2px 8px rgba(19,127,236,.25);
    }
    .btn-adjust:hover { background:var(--primary-hover); }

    /* Alert */
    .alert-success {
        padding:.75rem 1rem; background:#f0fdf4; border:1px solid #bbf7d0;
        border-radius:var(--radius-sm); color:#15803d; font-size:.84rem;
        margin-bottom:1rem; display:flex; align-items:center; gap:.5rem;
    }
    .alert-success .material-symbols-outlined { font-size:1.1rem; }

    /* Transaction history */
    .history-card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius-md); box-shadow:var(--shadow-sm); overflow:hidden;
    }
    .history-card-header {
        display:flex; align-items:center; gap:.5rem;
        padding:.875rem 1.25rem; border-bottom:1px solid var(--border); background:#fafbfd;
    }
    .history-card-header .material-symbols-outlined { color:var(--primary); font-size:1.1rem; }
    .history-card-header h5 { font-size:.9rem; font-weight:700; margin:0; }

    .hist-table { width:100%; border-collapse:collapse; font-size:.8375rem; }
    .hist-table thead th {
        padding:.625rem .875rem; text-align:left;
        font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
        color:var(--text-muted); border-bottom:1px solid var(--border); background:#fafbfd;
        white-space:nowrap;
    }
    .hist-table tbody tr:hover { background:rgba(0,0,0,.02); }
    .hist-table td {
        padding:.625rem .875rem; border-bottom:1px solid var(--border); vertical-align:middle;
    }
    .hist-table tbody tr:last-child td { border-bottom:none; }

    .type-pill {
        display:inline-block; padding:.15rem .5rem;
        border-radius:20px; font-size:.72rem; font-weight:600; white-space:nowrap;
    }
    .type-CL         { background:#dbeafe; color:#1e40af; }
    .type-permission { background:#ede9fe; color:#5b21b6; }
    .type-saturday_leave { background:#fef3c7; color:#92400e; }

    .tx-credit { color:#16a34a; font-weight:700; }
    .tx-debit  { color:#dc2626; font-weight:700; }
    .tx-lapse  { color:#ea580c; font-weight:700; }

    /* Pagination */
    .pag-wrap { padding:.875rem 1.25rem; border-top:1px solid var(--border); }
    .pag-wrap .pagination { margin:0; gap:.25rem; display:flex; flex-wrap:wrap; }
    .pag-wrap .page-link {
        height:2rem; min-width:2rem; padding:0 .5rem;
        display:flex; align-items:center; justify-content:center;
        border:1px solid var(--border); border-radius:var(--radius-sm);
        font-size:.8rem; color:var(--text-secondary); text-decoration:none;
        transition:all .15s;
    }
    .pag-wrap .page-link:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-subtle); }
    .pag-wrap .page-item.active .page-link { background:var(--primary); border-color:var(--primary); color:#fff; }
    .pag-wrap .page-item.disabled .page-link { opacity:.4; pointer-events:none; }

    .empty-hist { padding:2.5rem; text-align:center; color:var(--text-muted); }
</style>
@endpush

@section('content')

    <div class="breadcrumb-bar">
        <a href="{{ route('admin.leave.index') }}">Leave Management</a>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>{{ $employee->name }}</span>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile bar --}}
    <div class="profile-bar">
        <div class="profile-avatar">{{ $employee->initials() }}</div>
        <div>
            <div class="profile-name">
                {{ $employee->name }}
                <span class="role-pill {{ $employee->role }}">{{ ucfirst($employee->role) }}</span>
            </div>
            <div class="profile-meta">
                {{ $employee->employee_code }} &mdash; {{ $employee->department->name ?? 'No dept' }}
                &mdash; {{ $employee->email }}
            </div>
        </div>
    </div>

    {{-- Balance summary cards --}}
    <div class="bal-grid">

        {{-- CL --}}
        <div class="bal-card">
            <div class="bal-card-header">
                <div class="icon" style="background:#3b82f6;">
                    <span class="material-symbols-outlined" style="font-size:14px; font-variation-settings:'FILL' 1">event_note</span>
                </div>
                <h6>Casual Leave (CL) &mdash; {{ $year }}</h6>
            </div>
            <div class="bal-card-body">
                @if($cl)
                    <div class="bal-stat-row">
                        <div class="bal-stat">
                            <div class="bal-stat-val v-cr">{{ number_format($cl->credited, 0) }}</div>
                            <div class="bal-stat-lbl">Credited</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-us">{{ number_format($cl->used, 0) }}</div>
                            <div class="bal-stat-lbl">Used</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-bal">{{ number_format(max(0,$cl->credited - $cl->used - $cl->lapsed), 0) }}</div>
                            <div class="bal-stat-lbl">Balance</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val" style="color:#ea580c;">{{ number_format($cl->lapsed, 0) }}</div>
                            <div class="bal-stat-lbl">Lapsed</div>
                        </div>
                    </div>
                @else
                    <p class="no-bal">No CL balance record for {{ $year }}.</p>
                @endif
            </div>
        </div>

        {{-- Permissions --}}
        <div class="bal-card">
            <div class="bal-card-header">
                <div class="icon" style="background:#8b5cf6;">
                    <span class="material-symbols-outlined" style="font-size:14px; font-variation-settings:'FILL' 1">schedule</span>
                </div>
                <h6>Permissions &mdash; {{ now()->format('M Y') }}</h6>
            </div>
            <div class="bal-card-body">
                @if($perm)
                    <div class="bal-stat-row">
                        <div class="bal-stat">
                            <div class="bal-stat-val v-cr">{{ number_format($perm->credited, 0) }}</div>
                            <div class="bal-stat-lbl">Credited</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-us">{{ number_format($perm->used, 0) }}</div>
                            <div class="bal-stat-lbl">Used</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-bal">{{ number_format(max(0,$perm->credited - $perm->used - $perm->lapsed), 0) }}</div>
                            <div class="bal-stat-lbl">Balance</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val" style="color:#ea580c;">{{ number_format($perm->lapsed, 0) }}</div>
                            <div class="bal-stat-lbl">Lapsed</div>
                        </div>
                    </div>
                @else
                    <p class="no-bal">No permission record for {{ now()->format('M Y') }}.</p>
                @endif
            </div>
        </div>

        {{-- Saturday Leave --}}
        <div class="bal-card">
            <div class="bal-card-header">
                <div class="icon" style="background:#f59e0b;">
                    <span class="material-symbols-outlined" style="font-size:14px; font-variation-settings:'FILL' 1">weekend</span>
                </div>
                <h6>Saturday Leave &mdash; {{ now()->format('M Y') }}</h6>
            </div>
            <div class="bal-card-body">
                @if($sat)
                    <div class="bal-stat-row">
                        <div class="bal-stat">
                            <div class="bal-stat-val v-cr">{{ number_format($sat->credited, 0) }}</div>
                            <div class="bal-stat-lbl">Credited</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-us">{{ number_format($sat->used, 0) }}</div>
                            <div class="bal-stat-lbl">Used</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val v-bal">{{ number_format(max(0,$sat->credited - $sat->used - $sat->lapsed), 0) }}</div>
                            <div class="bal-stat-lbl">Balance</div>
                        </div>
                        <div class="bal-stat">
                            <div class="bal-stat-val" style="color:#ea580c;">{{ number_format($sat->lapsed, 0) }}</div>
                            <div class="bal-stat-lbl">Lapsed</div>
                        </div>
                    </div>
                @else
                    <p class="no-bal">No saturday leave record for {{ now()->format('M Y') }}.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Manual Adjustment Form --}}
    <div class="adjust-card">
        <div class="adjust-card-header">
            <span class="material-symbols-outlined">tune</span>
            <h5>Manual Balance Adjustment</h5>
        </div>
        <div class="adjust-card-body">
            <form method="POST" action="{{ route('admin.leave.adjust', $employee) }}">
                @csrf
                <div class="form-row mb-3">
                    <div>
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select @error('leave_type') is-invalid @enderror">
                            <option value="">— Select —</option>
                            <option value="CL"             {{ old('leave_type') === 'CL'             ? 'selected' : '' }}>Casual Leave (CL)</option>
                            <option value="permission"     {{ old('leave_type') === 'permission'     ? 'selected' : '' }}>Permission</option>
                            <option value="saturday_leave" {{ old('leave_type') === 'saturday_leave' ? 'selected' : '' }}>Saturday Leave</option>
                        </select>
                        @error('leave_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Action <span class="text-danger">*</span></label>
                        <select name="transaction_type" class="form-select @error('transaction_type') is-invalid @enderror">
                            <option value="">— Select —</option>
                            <option value="credit" {{ old('transaction_type') === 'credit' ? 'selected' : '' }}>Credit (add to balance)</option>
                            <option value="debit"  {{ old('transaction_type') === 'debit'  ? 'selected' : '' }}>Debit (mark as used)</option>
                            <option value="lapse"  {{ old('transaction_type') === 'lapse'  ? 'selected' : '' }}>Lapse (expire balance)</option>
                        </select>
                        @error('transaction_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.5" min="0.5" max="999"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}" placeholder="e.g. 1">
                        @error('amount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                    <input type="text" name="remarks"
                           class="form-control @error('remarks') is-invalid @enderror"
                           value="{{ old('remarks') }}"
                           placeholder="e.g. Manual correction for July carry-forward"
                           maxlength="255">
                    @error('remarks')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn-adjust">
                    <span class="material-symbols-outlined" style="font-size:1rem">save</span>
                    Apply Adjustment
                </button>
            </form>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="history-card">
        <div class="history-card-header">
            <span class="material-symbols-outlined">history</span>
            <h5>Transaction History</h5>
        </div>

        <div style="overflow-x:auto;">
            <table class="hist-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Leave Type</th>
                        <th>Action</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="white-space:nowrap; color:var(--text-secondary);">
                                {{ $tx->date->format('d M Y') }}
                            </td>
                            <td>
                                <span class="type-pill type-{{ $tx->leave_type }}">
                                    {{ match($tx->leave_type) {
                                        'CL'             => 'Casual Leave',
                                        'permission'     => 'Permission',
                                        'saturday_leave' => 'Saturday Leave',
                                        default          => $tx->leave_type,
                                    } }}
                                </span>
                            </td>
                            <td>
                                <span class="tx-{{ $tx->transaction_type }}">
                                    {{ ucfirst($tx->transaction_type) }}
                                </span>
                            </td>
                            <td style="font-weight:700; font-variant-numeric:tabular-nums;">
                                {{ number_format($tx->amount, 1) }}
                            </td>
                            <td style="color:var(--text-secondary); font-size:.8rem;">
                                {{ $tx->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-hist">No transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="pag-wrap">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

@endsection
