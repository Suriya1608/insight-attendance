@extends('layouts.app')

@section('title', 'Add Employee')

@push('styles')
<style>
    .breadcrumb-bar {
        display: flex; align-items: center; gap: .375rem;
        font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.25rem;
    }
    .breadcrumb-bar a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-bar a:hover { text-decoration: underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size: .9375rem; }

    /* ── Form layout ── */
    .form-layout { display: grid; grid-template-columns: 1fr; gap: 1.25rem; max-width: 860px; }

    .form-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .form-section-header {
        padding: .875rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .625rem;
        background: #fafbfd;
    }
    .form-section-header .sec-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: var(--sec-color, rgba(19,127,236,.1));
    }
    .form-section-header .sec-icon .material-symbols-outlined {
        font-size: 1.1rem; color: var(--sec-icon, var(--primary));
        font-variation-settings: 'FILL' 1;
    }
    .form-section-header h6 { font-size: .9rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .form-section-body { padding: 1.375rem 1.5rem; }

    /* ── Grid helpers ── */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) {
        .grid-2, .grid-3 { grid-template-columns: 1fr; }
    }
    @media (min-width: 641px) and (max-width: 800px) {
        .grid-3 { grid-template-columns: 1fr 1fr; }
    }

    /* ── Form controls ── */
    .form-label {
        font-size: .8rem; font-weight: 600; color: var(--text-main);
        margin-bottom: .3rem; display: block;
    }
    .form-control, .form-select {
        height: 2.5rem; border-radius: var(--radius-sm);
        border: 1.5px solid var(--border);
        font-size: .875rem; color: var(--text-main);
        background: #f8fafc; width: 100%;
        padding: 0 .75rem;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle);
        background: #fff; outline: none;
    }
    .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; background-image: none; }
    .invalid-feedback { font-size: .775rem; color: #ef4444; margin-top: .25rem; display: block; }
    .form-hint { font-size: .775rem; color: var(--text-muted); margin-top: .3rem; }

    /* Photo upload */
    .photo-upload-area {
        border: 2px dashed var(--border);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .photo-upload-area:hover { border-color: var(--primary); background: var(--primary-subtle); }
    .photo-upload-area input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-upload-area .material-symbols-outlined { font-size: 2rem; color: var(--text-muted); display: block; margin-bottom: .375rem; }
    .photo-upload-area p { font-size: .8rem; color: var(--text-muted); margin: 0; }
    #photoPreview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto .5rem; display: none; }

    /* Manager section (conditional) */
    #managerSection { display: none; }
    #level2Group     { display: none; }

    /* ── Action bar ── */
    .form-actions {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.125rem 1.5rem;
        display: flex; align-items: center; gap: .75rem;
        box-shadow: var(--shadow-sm);
    }
    .btn-save {
        height: 2.625rem; padding: 0 1.75rem;
        background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        cursor: pointer; box-shadow: 0 2px 8px rgba(19,127,236,.3);
        transition: background .15s;
    }
    .btn-save:hover { background: var(--primary-hover); }
    .btn-cancel {
        height: 2.625rem; padding: 0 1.25rem;
        background: transparent; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none; cursor: pointer; transition: all .15s;
    }
    .btn-cancel:hover { border-color: #94a3b8; color: var(--text-main); }

    /* Required star */
    .req { color: #ef4444; }

    /* ── Phone input group ── */
    .phone-group { display: flex; }
    .phone-group .phone-prefix {
        height: 2.5rem; padding: 0 .75rem;
        border: 1.5px solid var(--border); border-right: none;
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        background: var(--bg-light); color: var(--text-secondary);
        font-size: .8rem; font-weight: 600;
        display: flex; align-items: center; white-space: nowrap;
        flex-shrink: 0;
    }
    .phone-group .form-control {
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }
    .phone-group:focus-within .phone-prefix { border-color: var(--primary); }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

    <div class="breadcrumb-bar">
        <a href="{{ route('admin.employees.index') }}">Employees</a>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>Add Employee</span>
    </div>

    <div class="page-title">Add Employee</div>
    <p class="page-subtitle">Fill in the details below to create a new employee account.</p>

    <form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="form-layout">

            {{-- ── 1. BASIC DETAILS ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(19,127,236,.1); --sec-icon:#137fec;">
                    <div class="sec-icon"><span class="material-symbols-outlined">person</span></div>
                    <h6>Basic Details</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-2 mb-3">
                        <div>
                            <label class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Rahul Sharma" required autofocus>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Email ID <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="e.g. rahul@company.com" required>
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="grid-2 mb-3">
                        <div>
                            <label class="form-label">Username</label>
                            <div style="display:flex;">
                                <input type="text" id="usernameInput" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username') }}" placeholder="e.g. rahul.sharma"
                                       maxlength="50" autocomplete="off"
                                       oninput="checkUsername(this)"
                                       style="border-radius:var(--radius-sm) 0 0 var(--radius-sm);">
                                <span id="usernameStatusBox"
                                      style="display:flex;align-items:center;justify-content:center;
                                             width:2.5rem;border:1.5px solid var(--border);border-left:none;
                                             border-radius:0 var(--radius-sm) var(--radius-sm) 0;
                                             background:var(--bg-light);color:var(--text-secondary);">
                                    <span class="material-symbols-outlined" id="usernameStatusIcon"
                                          style="font-size:1.1rem;">person</span>
                                </span>
                            </div>
                            @error('username')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            <span class="form-hint" id="usernameHint">Optional. Letters, numbers, dots, dashes. Used to log in.</span>
                        </div>
                        <div>{{-- spacer --}}</div>
                    </div>
                    <div class="grid-3 mb-3">
                        <div>
                            <label class="form-label">Mobile Number</label>
                            <div class="phone-group">
                                <span class="phone-prefix">+91</span>
                                <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                       value="{{ old('mobile') }}" placeholder="9876543210"
                                       maxlength="10" inputmode="numeric" oninput="numericOnly(this)">
                            </div>
                            @error('mobile')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <span class="form-hint">10 digits only, without country code</span>
                        </div>
                        <div>
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                   value="{{ old('dob') }}" max="{{ date('Y-m-d') }}">
                            @error('dob')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Date of Joining</label>
                            <input type="date" name="doj" class="form-control @error('doj') is-invalid @enderror"
                                   value="{{ old('doj', date('Y-m-d')) }}">
                            @error('doj')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Profile Photo</label>
                        <div class="photo-upload-area" onclick="document.getElementById('photoInput').click()">
                            <input type="file" id="photoInput" name="profile_image" accept="image/*"
                                   onchange="previewPhoto(this)" style="display:none;">
                            <img id="photoPreview" alt="Preview">
                            <span class="material-symbols-outlined" id="uploadIcon">add_photo_alternate</span>
                            <p>Click to upload photo (max 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 2. IDENTITY DETAILS ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(124,58,237,.1); --sec-icon:#7c3aed;">
                    <div class="sec-icon"><span class="material-symbols-outlined">badge</span></div>
                    <h6>Identity Details</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-2">
                        <div>
                            <label class="form-label">Aadhaar Number</label>
                            <input type="text" name="aadhaar_number"
                                   class="form-control @error('aadhaar_number') is-invalid @enderror"
                                   value="{{ old('aadhaar_number') }}" placeholder="12-digit Aadhaar"
                                   maxlength="12" inputmode="numeric" oninput="numericOnly(this)">
                            @error('aadhaar_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <span class="form-hint">Exactly 12 digits, numbers only</span>
                        </div>
                        <div>
                            <label class="form-label">PAN Card Number</label>
                            <input type="text" name="pan_number"
                                   class="form-control @error('pan_number') is-invalid @enderror"
                                   value="{{ old('pan_number') }}" placeholder="ABCDE1234F" maxlength="10"
                                   style="text-transform:uppercase;">
                            @error('pan_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 3. BANK DETAILS ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(22,163,74,.1); --sec-icon:#16a34a;">
                    <div class="sec-icon"><span class="material-symbols-outlined">account_balance</span></div>
                    <h6>Bank Details</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-3">
                        <div>
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name"
                                   class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name') }}" placeholder="e.g. State Bank of India">
                            @error('bank_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number"
                                   class="form-control @error('bank_account_number') is-invalid @enderror"
                                   value="{{ old('bank_account_number') }}" placeholder="Account number"
                                   maxlength="20" inputmode="numeric" oninput="numericOnly(this)">
                            @error('bank_account_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <span class="form-hint">8–20 digits, numbers only</span>
                        </div>
                        <div>
                            <label class="form-label">IFSC Code</label>
                            <input type="text" name="ifsc_code"
                                   class="form-control @error('ifsc_code') is-invalid @enderror"
                                   value="{{ old('ifsc_code') }}" placeholder="e.g. SBIN0001234" maxlength="11"
                                   style="text-transform:uppercase;">
                            @error('ifsc_code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 4. EMERGENCY CONTACT & FAMILY ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(239,68,68,.1); --sec-icon:#dc2626;">
                    <div class="sec-icon"><span class="material-symbols-outlined">emergency</span></div>
                    <h6>Emergency Contact &amp; Family</h6>
                </div>
                <div class="form-section-body">
                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Number</label>
                        <div class="phone-group">
                            <span class="phone-prefix">+91</span>
                            <input type="tel" name="emergency_contact"
                                   class="form-control @error('emergency_contact') is-invalid @enderror"
                                   value="{{ old('emergency_contact') }}" placeholder="9876543210"
                                   maxlength="10" inputmode="numeric" oninput="numericOnly(this)">
                        </div>
                        @error('emergency_contact')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <span class="form-hint">10 digits only, without country code</span>
                    </div>
                    <div class="grid-2">
                        <div>
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name"
                                   class="form-control @error('father_name') is-invalid @enderror"
                                   value="{{ old('father_name') }}" placeholder="Father's full name">
                            @error('father_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="mother_name"
                                   class="form-control @error('mother_name') is-invalid @enderror"
                                   value="{{ old('mother_name') }}" placeholder="Mother's full name">
                            @error('mother_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 5. ADDRESS ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(245,158,11,.1); --sec-icon:#d97706;">
                    <div class="sec-icon"><span class="material-symbols-outlined">location_on</span></div>
                    <h6>Address</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-2 mb-3">
                        <div>
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1"
                                   class="form-control @error('address_line1') is-invalid @enderror"
                                   value="{{ old('address_line1') }}" placeholder="House / Flat / Building">
                            @error('address_line1')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2"
                                   class="form-control @error('address_line2') is-invalid @enderror"
                                   value="{{ old('address_line2') }}" placeholder="Street / Area / Locality">
                            @error('address_line2')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="grid-3">
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" name="city"
                                   class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city') }}" placeholder="City">
                            @error('city')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" name="state"
                                   class="form-control @error('state') is-invalid @enderror"
                                   value="{{ old('state') }}" placeholder="State">
                            @error('state')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Country</label>
                            <input type="text" name="country"
                                   class="form-control @error('country') is-invalid @enderror"
                                   value="{{ old('country', 'India') }}" placeholder="Country">
                            @error('country')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 5. ADDITIONAL DETAILS ── --}}
            <div class="form-section">
                <div class="form-section-header"
                     style="--sec-color:rgba(217,119,6,.1); --sec-icon:#d97706;">
                    <div class="sec-icon"><span class="material-symbols-outlined">info</span></div>
                    <h6>Additional Details</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-2 mb-3">
                        <div>
                            <label class="form-label">Monthly Salary <span class="req">*</span></label>
                            <div style="display:flex;">
                                <span style="height:2.5rem;padding:0 .75rem;border:1.5px solid var(--border);border-right:none;
                                             border-radius:var(--radius-sm) 0 0 var(--radius-sm);background:var(--bg-light);
                                             color:var(--text-secondary);font-size:.9rem;font-weight:600;
                                             display:flex;align-items:center;flex-shrink:0;">₹</span>
                                <input type="number" name="salary" step="0.01" min="0"
                                       class="form-control @error('salary') is-invalid @enderror"
                                       value="{{ old('salary', '0.00') }}" placeholder="e.g. 25000.00"
                                       style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;">
                            </div>
                            @error('salary')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <span class="form-hint">Enter gross monthly salary in INR</span>
                        </div>
                        <div>{{-- spacer --}}</div>
                    </div>
                    <div class="grid-3 mb-3">
                        <div>
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                            @error('blood_group')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="emp_status" class="form-select @error('emp_status') is-invalid @enderror">
                                <option value="active"   {{ old('emp_status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('emp_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('emp_status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Role selector --}}
                    <div>
                        <label class="form-label">Role <span class="req">*</span></label>
                        <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;
                                          padding:.5rem 1rem; border:1.5px solid var(--border);
                                          border-radius:var(--radius-sm); font-size:.875rem; font-weight:600;
                                          transition:all .15s;" id="roleEmpLabel">
                                <input type="radio" name="role" value="employee" id="roleEmployee"
                                       {{ old('role','employee') === 'employee' ? 'checked' : '' }}
                                       style="accent-color:var(--primary);">
                                <span>Employee</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;
                                          padding:.5rem 1rem; border:1.5px solid var(--border);
                                          border-radius:var(--radius-sm); font-size:.875rem; font-weight:600;
                                          transition:all .15s;" id="roleMgrLabel">
                                <input type="radio" name="role" value="manager" id="roleManager"
                                       {{ old('role') === 'manager' ? 'checked' : '' }}
                                       style="accent-color:var(--primary);">
                                <span>Manager</span>
                            </label>
                        </div>
                        @error('role')<span class="invalid-feedback" style="display:block;margin-top:.25rem;">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- ── 6. MANAGER HIERARCHY ── --}}
            <div class="form-section" id="managerSection">
                <div class="form-section-header"
                     style="--sec-color:rgba(8,145,178,.1); --sec-icon:#0891b2;">
                    <div class="sec-icon"><span class="material-symbols-outlined">account_tree</span></div>
                    <h6>Manager Hierarchy</h6>
                </div>
                <div class="form-section-body">
                    <div class="grid-2">
                        <div>
                            <label class="form-label" id="lvl1Label">Level 1 Manager <span class="req">*</span></label>
                            <select name="level1_manager_id" id="level1Select"
                                    class="form-select @error('level1_manager_id') is-invalid @enderror">
                                <option value="">Select Level 1 Manager</option>
                                @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}" {{ old('level1_manager_id') == $mgr->id ? 'selected' : '' }}>
                                        {{ $mgr->name }}
                                        @if($mgr->department) ({{ $mgr->department->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('level1_manager_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div id="level2Group">
                            <label class="form-label">Level 2 Manager <span class="req">*</span></label>
                            <select name="level2_manager_id" id="level2Select"
                                    class="form-select @error('level2_manager_id') is-invalid @enderror">
                                <option value="">Select Level 2 Manager</option>
                                @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}" {{ old('level2_manager_id') == $mgr->id ? 'selected' : '' }}>
                                        {{ $mgr->name }}
                                        @if($mgr->department) ({{ $mgr->department->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('level2_manager_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <p class="form-hint" style="margin-top:.75rem;">
                        <span class="material-symbols-outlined" style="font-size:.9rem;vertical-align:middle;">info</span>
                        Level 1 and Level 2 managers cannot be the same person.
                    </p>
                </div>
            </div>

            {{-- ── ACTION BAR ── --}}
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <span class="material-symbols-outlined" style="font-size:1.1rem">save</span>
                    Save Employee
                </button>
                <a href="{{ route('admin.employees.index') }}" class="btn-cancel">
                    <span class="material-symbols-outlined" style="font-size:1rem">close</span>
                    Cancel
                </a>
                <span style="font-size:.775rem;color:var(--text-muted);margin-left:auto;">
                    Default password: <strong>Password@123</strong>
                </span>
            </div>

        </div>{{-- .form-layout --}}
    </form>

@endsection

@push('scripts')
<script>
(function () {
    const roleEmp   = document.getElementById('roleEmployee');
    const roleMgr   = document.getElementById('roleManager');
    const mgrSec    = document.getElementById('managerSection');
    const lvl2Group = document.getElementById('level2Group');
    const lvl1Sel   = document.getElementById('level1Select');
    const lvl2Sel   = document.getElementById('level2Select');

    function updateHierarchy() {
        const isEmployee = roleEmp.checked;
        const isManager  = roleMgr.checked;

        // Show/hide entire manager section
        mgrSec.style.display = (isEmployee || isManager) ? 'block' : 'none';

        // Show Level 2 only for employee role
        lvl2Group.style.display = isEmployee ? 'block' : 'none';

        // Required attributes
        lvl1Sel.required = (isEmployee || isManager);
        lvl2Sel.required = isEmployee;
    }

    // Validate Level1 ≠ Level2
    function validateManagers() {
        if (lvl1Sel.value && lvl2Sel.value && lvl1Sel.value === lvl2Sel.value) {
            lvl2Sel.setCustomValidity('Level 1 and Level 2 managers cannot be the same person.');
        } else {
            lvl2Sel.setCustomValidity('');
        }
    }

    // Role radio style toggle
    function styleRoles() {
        const empLabel = document.getElementById('roleEmpLabel');
        const mgrLabel = document.getElementById('roleMgrLabel');
        if (roleEmp.checked) {
            empLabel.style.borderColor = 'var(--primary)';
            empLabel.style.background  = 'var(--primary-subtle)';
            mgrLabel.style.borderColor = 'var(--border)';
            mgrLabel.style.background  = 'transparent';
        } else {
            mgrLabel.style.borderColor = 'var(--primary)';
            mgrLabel.style.background  = 'var(--primary-subtle)';
            empLabel.style.borderColor = 'var(--border)';
            empLabel.style.background  = 'transparent';
        }
    }

    roleEmp.addEventListener('change', function () { updateHierarchy(); styleRoles(); });
    roleMgr.addEventListener('change', function () { updateHierarchy(); styleRoles(); });
    lvl1Sel.addEventListener('change', validateManagers);
    lvl2Sel.addEventListener('change', validateManagers);

    // Init
    updateHierarchy();
    styleRoles();
})();

function numericOnly(input) {
    input.value = input.value.replace(/\D/g, '');
}

/* ── Live username uniqueness check ── */
let _unTimer = null;
function checkUsername(input) {
    const val     = input.value.trim();
    const icon    = document.getElementById('usernameStatusIcon');
    const hint    = document.getElementById('usernameHint');
    const box     = document.getElementById('usernameStatusBox');

    clearTimeout(_unTimer);
    input.classList.remove('is-valid', 'is-invalid');

    if (!val) {
        icon.textContent      = 'person';
        icon.style.color      = '';
        hint.textContent      = 'Optional. Letters, numbers, dots, dashes. Used to log in.';
        hint.style.color      = '';
        return;
    }

    // Basic format check client-side
    if (!/^[a-zA-Z0-9._-]+$/.test(val)) {
        input.classList.add('is-invalid');
        icon.textContent = 'cancel';
        icon.style.color = '#dc2626';
        hint.textContent = 'Only letters, numbers, dots (.), underscores (_) and dashes (-) allowed.';
        hint.style.color = '#dc2626';
        return;
    }

    icon.textContent  = 'autorenew';
    icon.style.color  = '#94a3b8';
    icon.style.animation = 'spin .8s linear infinite';

    _unTimer = setTimeout(() => {
        fetch(`{{ route('admin.employees.check-username') }}?username=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(data => {
                icon.style.animation = '';
                if (data.available) {
                    input.classList.add('is-valid');
                    icon.textContent = 'check_circle';
                    icon.style.color = '#16a34a';
                    hint.textContent = 'Username is available.';
                    hint.style.color = '#16a34a';
                } else {
                    input.classList.add('is-invalid');
                    icon.textContent = 'cancel';
                    icon.style.color = '#dc2626';
                    hint.textContent = 'Username is already taken.';
                    hint.style.color = '#dc2626';
                }
            })
            .catch(() => { icon.style.animation = ''; });
    }, 450);
}

function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    const icon    = document.getElementById('uploadIcon');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
