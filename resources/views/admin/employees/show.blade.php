@extends('layouts.app')

@section('title', 'Employee Profile – ' . $employee->name)

@push('styles')
<style>
    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }
    .page-header .header-icon {
        width: 42px; height: 42px;
        background: var(--primary-subtle);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
        font-size: 22px;
        flex-shrink: 0;
    }
    .page-header h1 { font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .page-header p  { font-size: .82rem; color: var(--text-secondary); margin: 0; }

    /* ── Profile card ── */
    .profile-card {
        background: var(--surface);
        border-radius: 14px;
        border: 1px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .profile-banner {
        height: 90px;
        background: linear-gradient(135deg, #1e3a5f 0%, var(--primary) 100%);
    }
    .profile-body {
        padding: 0 28px 24px;
        display: flex;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
    }
    .profile-avatar-wrap {
        margin-top: -40px;
        flex-shrink: 0;
    }
    .profile-avatar {
        width: 80px; height: 80px;
        border-radius: 50%;
        border: 3px solid var(--surface);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; font-weight: 700; color: #fff;
        background: var(--primary);
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.15);
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-info { padding-top: 14px; flex: 1; min-width: 200px; }
    .profile-info h2 { font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px; }
    .profile-info .emp-code { font-size: .78rem; color: var(--text-secondary); font-family: monospace; }
    .profile-actions { margin-top: 14px; display: flex; gap: 10px; }

    /* ── Role / Status badges ── */
    .badge-role {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 600;
    }
    .badge-role.manager  { background: rgba(139,92,246,.12); color: #7c3aed; }
    .badge-role.employee { background: rgba(19,127,236,.12);  color: var(--primary); }
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 600;
    }
    .badge-status.active   { background: rgba(16,185,129,.12); color: #059669; }
    .badge-status.inactive { background: rgba(100,116,139,.12); color: #64748b; }
    .status-dot {
        width: 7px; height: 7px; border-radius: 50%; display: inline-block;
    }
    .status-dot.active   { background: #10b981; }
    .status-dot.inactive { background: #94a3b8; }

    /* ── Info sections ── */
    .info-section {
        background: var(--surface);
        border-radius: 14px;
        border: 1px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .info-section-header {
        display: flex; align-items: center; gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }
    .info-section-header .sec-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; color: #fff;
        flex-shrink: 0;
    }
    .info-section-header h6 {
        font-size: .82rem; font-weight: 700; color: var(--text-main);
        margin: 0; letter-spacing: .03em; text-transform: uppercase;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0;
        padding: 0;
    }
    .info-item {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        border-right: 1px solid var(--border);
    }
    .info-item:nth-last-child(-n+3):last-child,
    .info-item:last-child { border-bottom: none; }
    .info-item label {
        display: block; font-size: .72rem; font-weight: 600;
        color: var(--text-secondary); text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: 4px;
    }
    .info-item .val {
        font-size: .88rem; font-weight: 500; color: var(--text-main);
    }
    .info-item .val.empty { color: #cbd5e1; font-style: italic; }

    /* ── Manager badges ── */
    .manager-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--bg-light); border: 1px solid var(--border);
        border-radius: 30px; padding: 5px 12px 5px 5px;
        font-size: .82rem; font-weight: 500; color: var(--text-main);
    }
    .manager-badge .mini-avatar {
        width: 26px; height: 26px; border-radius: 50%;
        background: var(--primary); color: #fff;
        font-size: .7rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* ── Action buttons ── */
    .btn-edit {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--primary); color: #fff;
        border: none; border-radius: 8px;
        padding: 8px 18px; font-size: .83rem; font-weight: 600;
        text-decoration: none; cursor: pointer;
        transition: background .18s;
    }
    .btn-edit:hover { background: var(--primary-hover); color: #fff; }
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: transparent; color: var(--text-secondary);
        border: 1px solid var(--border); border-radius: 8px;
        padding: 8px 16px; font-size: .83rem; font-weight: 500;
        text-decoration: none; cursor: pointer;
        transition: all .18s;
    }
    .btn-back:hover { border-color: var(--primary); color: var(--primary); }

    /* ── Masked field toggle ── */
    .masked-field { display: flex; align-items: center; gap: 8px; }
    .btn-reveal {
        width: 26px; height: 26px;
        background: var(--bg-light); border: 1px solid var(--border);
        border-radius: 6px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        color: var(--text-secondary); flex-shrink: 0; transition: all .15s;
        padding: 0;
    }
    .btn-reveal:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-subtle); }
    .btn-reveal .material-symbols-outlined { font-size: 14px; }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="page-header">
    <div class="header-icon">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">person</span>
    </div>
    <div>
        <h1>Employee Profile</h1>
        <p>View and manage employee information</p>
    </div>
</div>

{{-- Success/Error alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Profile banner card ── --}}
<div class="profile-card">
    <div class="profile-banner"></div>
    <div class="profile-body">
        <div class="profile-avatar-wrap">
            <div class="profile-avatar">
                @if($employee->employeeDetail?->profile_image)
                    <img src="{{ Storage::url($employee->employeeDetail->profile_image) }}" alt="{{ $employee->name }}">
                @else
                    {{ $employee->initials() }}
                @endif
            </div>
        </div>
        <div class="profile-info">
            <h2>{{ $employee->name }}</h2>
            <span class="emp-code">{{ $employee->employee_code }}</span>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="badge-role {{ $employee->role }}">
                    <span class="material-symbols-outlined" style="font-size:14px;font-variation-settings:'FILL' 1">
                        {{ $employee->role === 'manager' ? 'manage_accounts' : 'badge' }}
                    </span>
                    {{ ucfirst($employee->role) }}
                </span>
                <span class="badge-status {{ $employee->emp_status }}">
                    <span class="status-dot {{ $employee->emp_status }}"></span>
                    {{ ucfirst($employee->emp_status) }}
                </span>
                @if($employee->department)
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.75rem;color:var(--text-secondary);background:var(--bg-light);padding:4px 10px;border-radius:20px;">
                        <span class="material-symbols-outlined" style="font-size:14px;">corporate_fare</span>
                        {{ $employee->department->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="profile-actions ms-auto">
            <a href="{{ route('admin.employees.index') }}" class="btn-back">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Back
            </a>
            <a href="{{ route('admin.employees.documents.index', $employee) }}"
               style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.25);border-radius:8px;padding:8px 16px;font-size:.83rem;font-weight:600;text-decoration:none;transition:all .18s;"
               onmouseover="this.style.background='#059669';this.style.color='#fff';"
               onmouseout="this.style.background='rgba(16,185,129,.1)';this.style.color='#059669';">
                <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">folder_shared</span>
                Documents
            </a>
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-edit">
                <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">edit</span>
                Edit Employee
            </a>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Basic Details --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="sec-icon" style="background:#3b82f6;">
                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">person</span>
                </div>
                <h6>Basic Details</h6>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <div class="val">{{ $employee->name }}</div>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <div class="val">{{ $employee->email }}</div>
                </div>
                <div class="info-item">
                    <label>Mobile</label>
                    <div class="val {{ $employee->mobile ? '' : 'empty' }}">
                        {{ $employee->mobile ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Date of Birth</label>
                    <div class="val {{ $employee->dob ? '' : 'empty' }}">
                        {{ $employee->dob ? $employee->dob->format('d M Y') : '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Date of Joining</label>
                    <div class="val {{ $employee->doj ? '' : 'empty' }}">
                        {{ $employee->doj ? $employee->doj->format('d M Y') : '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Employee Code</label>
                    <div class="val" style="font-family:monospace;">{{ $employee->employee_code }}</div>
                </div>
                <div class="info-item">
                    <label>Department</label>
                    <div class="val {{ $employee->department ? '' : 'empty' }}">
                        {{ $employee->department?->name ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Blood Group</label>
                    <div class="val {{ $employee->employeeDetail?->blood_group ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->blood_group ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Father's Name</label>
                    <div class="val {{ $employee->employeeDetail?->father_name ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->father_name ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Mother's Name</label>
                    <div class="val {{ $employee->employeeDetail?->mother_name ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->mother_name ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Monthly Salary</label>
                    <div class="val" style="font-weight:600;color:#059669;">
                        @if($employee->salary > 0)
                            ₹{{ number_format($employee->salary, 2) }}
                        @else
                            <span class="empty">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="sec-icon" style="background:#f59e0b;">
                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">location_on</span>
                </div>
                <h6>Address</h6>
            </div>
            <div class="info-grid">
                <div class="info-item" style="grid-column: span 2;">
                    <label>Address Line 1</label>
                    <div class="val {{ $employee->employeeDetail?->address_line1 ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->address_line1 ?? '—' }}
                    </div>
                </div>
                <div class="info-item" style="grid-column: span 2;">
                    <label>Address Line 2</label>
                    <div class="val {{ $employee->employeeDetail?->address_line2 ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->address_line2 ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>City</label>
                    <div class="val {{ $employee->employeeDetail?->city ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->city ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>State</label>
                    <div class="val {{ $employee->employeeDetail?->state ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->state ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Country</label>
                    <div class="val">{{ $employee->employeeDetail?->country ?? 'India' }}</div>
                </div>
                <div class="info-item">
                    <label>Emergency Contact</label>
                    <div class="val {{ $employee->employeeDetail?->emergency_contact ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->emergency_contact ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Manager Hierarchy --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="sec-icon" style="background:#8b5cf6;">
                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">account_tree</span>
                </div>
                <h6>Manager Hierarchy</h6>
            </div>
            <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                        Level 1 Manager
                    </div>
                    @if($employee->level1Manager)
                        <div class="manager-badge">
                            <div class="mini-avatar">{{ $employee->level1Manager->initials() }}</div>
                            <div>
                                <div style="font-size:.82rem;font-weight:600;line-height:1.2;">{{ $employee->level1Manager->name }}</div>
                                <div style="font-size:.72rem;color:var(--text-secondary);">{{ $employee->level1Manager->employee_code }}</div>
                            </div>
                        </div>
                    @else
                        <span style="font-size:.85rem;color:#cbd5e1;font-style:italic;">Not assigned</span>
                    @endif
                </div>

                @if($employee->role === 'employee')
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                        Level 2 Manager
                    </div>
                    @if($employee->level2Manager)
                        <div class="manager-badge">
                            <div class="mini-avatar">{{ $employee->level2Manager->initials() }}</div>
                            <div>
                                <div style="font-size:.82rem;font-weight:600;line-height:1.2;">{{ $employee->level2Manager->name }}</div>
                                <div style="font-size:.72rem;color:var(--text-secondary);">{{ $employee->level2Manager->employee_code }}</div>
                            </div>
                        </div>
                    @else
                        <span style="font-size:.85rem;color:#cbd5e1;font-style:italic;">Not assigned</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Identity Details --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="sec-icon" style="background:#10b981;">
                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">badge</span>
                </div>
                <h6>Identity Details</h6>
            </div>
            <div style="padding:0;">
                <div class="info-item">
                    <label>Aadhaar Number</label>
                    @if($employee->employeeDetail?->aadhaar_number)
                        @php
                            $aadhaarFull   = $employee->employeeDetail->aadhaar_number;
                            $aadhaarMasked = 'XXXX-XXXX-' . substr($aadhaarFull, -4);
                        @endphp
                        <div class="masked-field">
                            <span class="val" id="aadhaar-val" style="font-family:monospace;"
                                  data-masked="{{ $aadhaarMasked }}"
                                  data-full="{{ $aadhaarFull }}">{{ $aadhaarMasked }}</span>
                            <button type="button" class="btn-reveal" onclick="toggleMask('aadhaar-val', this)"
                                    title="Show / Hide">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    @else
                        <div class="val empty">—</div>
                    @endif
                </div>
                <div class="info-item" style="border-bottom:none;">
                    <label>PAN Number</label>
                    @if($employee->employeeDetail?->pan_number)
                        @php
                            $panFull   = $employee->employeeDetail->pan_number;
                            $panMasked = 'XXXXX' . substr($panFull, -5);
                        @endphp
                        <div class="masked-field">
                            <span class="val" id="pan-val" style="font-family:monospace;"
                                  data-masked="{{ $panMasked }}"
                                  data-full="{{ $panFull }}">{{ $panMasked }}</span>
                            <button type="button" class="btn-reveal" onclick="toggleMask('pan-val', this)"
                                    title="Show / Hide">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    @else
                        <div class="val empty">—</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bank Details --}}
        <div class="info-section">
            <div class="info-section-header">
                <div class="sec-icon" style="background:#f43f5e;">
                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1">account_balance</span>
                </div>
                <h6>Bank Details</h6>
            </div>
            <div style="padding:0;">
                <div class="info-item">
                    <label>Bank Name</label>
                    <div class="val {{ $employee->employeeDetail?->bank_name ? '' : 'empty' }}">
                        {{ $employee->employeeDetail?->bank_name ?? '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <label>Account Number</label>
                    @if($employee->employeeDetail?->bank_account_number)
                        @php
                            $accFull   = $employee->employeeDetail->bank_account_number;
                            $accMasked = str_repeat('X', max(0, strlen($accFull) - 4)) . substr($accFull, -4);
                        @endphp
                        <div class="masked-field">
                            <span class="val" id="acc-val" style="font-family:monospace;"
                                  data-masked="{{ $accMasked }}"
                                  data-full="{{ $accFull }}">{{ $accMasked }}</span>
                            <button type="button" class="btn-reveal" onclick="toggleMask('acc-val', this)"
                                    title="Show / Hide">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    @else
                        <div class="val empty">—</div>
                    @endif
                </div>
                <div class="info-item" style="border-bottom:none;">
                    <label>IFSC Code</label>
                    <div class="val {{ $employee->employeeDetail?->ifsc_code ? '' : 'empty' }}" style="font-family:monospace;">
                        {{ $employee->employeeDetail?->ifsc_code ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function toggleMask(id, btn) {
    const el   = document.getElementById(id);
    const icon = btn.querySelector('.material-symbols-outlined');
    const isHidden = el.textContent === el.dataset.masked;
    el.textContent = isHidden ? el.dataset.full : el.dataset.masked;
    icon.textContent = isHidden ? 'visibility_off' : 'visibility';
}
</script>
@endpush

@endsection
