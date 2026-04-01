@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
    /* ── Flash ──────────────────────────────────────────────────────────────── */
    .flash {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem 1rem; border-radius: var(--radius-md);
        font-size: .875rem; font-weight: 500; margin-bottom: 1.25rem;
        border: 1px solid transparent;
    }
    .flash .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-close { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.1rem; opacity: .6; }
    .flash-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .flash-error   { background: #fff1f2; color: #dc2626; border-color: #fecaca; }

    /* ── Hero card ──────────────────────────────────────────────────────────── */
    .profile-hero {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1.5rem;
    }
    .profile-hero-banner {
        height: 100px;
        background: linear-gradient(135deg, #137fec 0%, #6366f1 60%, #8b5cf6 100%);
    }
    .profile-hero-body {
        padding: 0 1.75rem 1.5rem;
        display: flex; align-items: flex-end; gap: 1.25rem;
        flex-wrap: wrap;
    }
    .profile-avatar-wrap {
        margin-top: -42px; flex-shrink: 0;
    }
    .profile-avatar {
        width: 84px; height: 84px; border-radius: 50%;
        border: 3px solid var(--surface); box-shadow: 0 2px 12px rgba(0,0,0,.15);
        object-fit: cover;
    }
    .profile-avatar-fallback {
        width: 84px; height: 84px; border-radius: 50%;
        border: 3px solid var(--surface); box-shadow: 0 2px 12px rgba(0,0,0,.15);
        background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
        color: #fff; font-size: 1.875rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        letter-spacing: .02em;
    }
    .profile-hero-info {
        padding-top: 1rem; flex: 1; min-width: 0;
    }
    .profile-name {
        font-size: 1.375rem; font-weight: 800; color: var(--text-main);
        letter-spacing: -.025em; margin-bottom: .3rem;
    }
    .profile-meta {
        display: flex; align-items: center; flex-wrap: wrap; gap: .5rem;
        font-size: .8rem;
    }
    .profile-meta-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: var(--bg-light); border: 1px solid var(--border);
        border-radius: 999px; padding: .2rem .65rem;
        font-size: .75rem; font-weight: 600; color: var(--text-secondary);
    }
    .profile-meta-chip .material-symbols-outlined { font-size: .9rem; }
    .badge-role {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .65rem; border-radius: 999px;
        font-size: .72rem; font-weight: 700; text-transform: capitalize;
        letter-spacing: .02em;
    }
    .badge-manager  { background: #ede9fe; color: #6d28d9; }
    .badge-employee { background: #dbeafe; color: #1d4ed8; }
    .badge-admin    { background: #fef3c7; color: #92400e; }
    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-inactive { background: #fee2e2; color: #dc2626; }
    .profile-hero-actions {
        padding-top: 1rem; flex-shrink: 0; align-self: flex-start; margin-top: 48px;
    }
    .btn-edit-profile {
        display: inline-flex; align-items: center; gap: .4rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        padding: .5rem 1.125rem; font-size: .85rem; font-weight: 600;
        cursor: pointer; text-decoration: none; transition: background .15s;
    }
    .btn-edit-profile:hover { background: var(--primary-hover); color: #fff; }
    .btn-edit-profile .material-symbols-outlined { font-size: 1rem; }

    /* ── Info grid ──────────────────────────────────────────────────────────── */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }

    /* ── Info card ──────────────────────────────────────────────────────────── */
    .info-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .info-card-header {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }
    .info-card-header .material-symbols-outlined {
        font-size: 1.1rem; color: var(--primary);
        font-variation-settings: 'FILL' 1;
    }
    .info-card-header h6 {
        margin: 0; font-size: .85rem; font-weight: 700; color: var(--text-main);
    }
    .info-rows { padding: .25rem 0; }
    .info-row {
        display: flex; align-items: flex-start;
        padding: .6rem 1.25rem; gap: .75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-key {
        width: 140px; flex-shrink: 0;
        font-size: .75rem; font-weight: 600; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: .04em;
        padding-top: .1rem;
    }
    .info-val {
        flex: 1; font-size: .875rem; color: var(--text-main); font-weight: 500;
        word-break: break-all;
    }
    .info-val.empty { color: var(--text-muted); font-style: italic; font-weight: 400; }
    .info-val.masked { font-family: monospace; letter-spacing: .05em; }

    /* Manager chip in reporting */
    .manager-chip {
        display: inline-flex; align-items: center; gap: .5rem;
        background: var(--bg-light); border: 1px solid var(--border);
        border-radius: var(--radius-sm); padding: .35rem .75rem;
        font-size: .82rem; font-weight: 600; color: var(--text-main);
    }
    .manager-chip .material-symbols-outlined { font-size: 1rem; color: var(--text-muted); }

    /* Full-width card */
    .info-grid .full-width { grid-column: 1 / -1; }
</style>
@endpush

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="flash flash-success" id="flashMsg">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
        <button class="flash-close" onclick="this.closest('.flash').remove()">✕</button>
    </div>
@endif
@if(session('error'))
    <div class="flash flash-error" id="flashMsg">
        <span class="material-symbols-outlined">error</span>
        {{ session('error') }}
        <button class="flash-close" onclick="this.closest('.flash').remove()">✕</button>
    </div>
@endif

@php
    $detail = $user->employeeDetail;
@endphp

{{-- Hero --}}
<div class="profile-hero">
    <div class="profile-hero-banner"></div>
    <div class="profile-hero-body">
        <div class="profile-avatar-wrap">
            @if($detail?->profile_image)
                <img src="{{ Storage::url($detail->profile_image) }}" alt="{{ $user->name }}" class="profile-avatar">
            @else
                <div class="profile-avatar-fallback">{{ $user->initials() }}</div>
            @endif
        </div>
        <div class="profile-hero-info">
            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-meta">
                <span class="badge-role badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                @if($user->employee_code)
                    <span class="profile-meta-chip">
                        <span class="material-symbols-outlined">badge</span>
                        {{ $user->employee_code }}
                    </span>
                @endif
                @if($user->department)
                    <span class="profile-meta-chip">
                        <span class="material-symbols-outlined">corporate_fare</span>
                        {{ $user->department->name }}
                    </span>
                @endif
                <span class="badge-role {{ $user->emp_status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                    {{ ucfirst($user->emp_status ?? 'active') }}
                </span>
            </div>
        </div>
        {{-- <div class="profile-hero-actions">
            <a href="{{ route('profile.edit') }}" class="btn-edit-profile">
                <span class="material-symbols-outlined">edit</span>
                Edit Profile
            </a>
        </div> --}}
    </div>
</div>

{{-- Info grid --}}
<div class="info-grid">

    {{-- Basic Information --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">person</span>
            <h6>Basic Information</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Email</span>
                <span class="info-val">{{ $user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Username</span>
                <span class="info-val {{ $user->username ? '' : 'empty' }}">{{ $user->username ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Mobile</span>
                <span class="info-val {{ $user->mobile ? '' : 'empty' }}">{{ $user->mobile ?: 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Date of Joining</span>
                <span class="info-val {{ $user->doj ? '' : 'empty' }}">
                    {{ $user->doj ? $user->doj->format('d M Y') : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-key">Employee Code</span>
                <span class="info-val {{ $user->employee_code ? '' : 'empty' }}">{{ $user->employee_code ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Department</span>
                <span class="info-val {{ $user->department ? '' : 'empty' }}">{{ $user->department?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Designation</span>
                <span class="info-val {{ $user->designation ? '' : 'empty' }}">{{ $user->designation ?: '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Personal Details --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">face</span>
            <h6>Personal Details</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Date of Birth</span>
                <span class="info-val {{ $user->dob ? '' : 'empty' }}">
                    {{ $user->dob ? $user->dob->format('d M Y') : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-key">Father's Name</span>
                <span class="info-val {{ $detail?->father_name ? '' : 'empty' }}">{{ $detail?->father_name ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Mother's Name</span>
                <span class="info-val {{ $detail?->mother_name ? '' : 'empty' }}">{{ $detail?->mother_name ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Blood Group</span>
                <span class="info-val {{ $detail?->blood_group ? '' : 'empty' }}">{{ $detail?->blood_group ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Aadhaar No.</span>
                @php
                    $aadRaw = $detail?->aadhaar_number ?? '';
                    $aadMasked = $aadRaw ? 'XXXX-XXXX-' . substr(preg_replace('/\D/', '', $aadRaw), -4) : null;
                @endphp
                <span class="info-val {{ $aadMasked ? 'masked' : 'empty' }}">{{ $aadMasked ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">PAN Number</span>
                @php
                    $panRaw = $detail?->pan_number ?? '';
                    $panMasked = $panRaw ? substr($panRaw, 0, 2) . str_repeat('X', max(0, strlen($panRaw) - 4)) . substr($panRaw, -2) : null;
                @endphp
                <span class="info-val {{ $panMasked ? 'masked' : 'empty' }}">{{ $panMasked ?: '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">home</span>
            <h6>Address</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Address Line 1</span>
                <span class="info-val {{ $detail?->address_line1 ? '' : 'empty' }}">{{ $detail?->address_line1 ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Address Line 2</span>
                <span class="info-val {{ $detail?->address_line2 ? '' : 'empty' }}">{{ $detail?->address_line2 ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">City</span>
                <span class="info-val {{ $detail?->city ? '' : 'empty' }}">{{ $detail?->city ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">State</span>
                <span class="info-val {{ $detail?->state ? '' : 'empty' }}">{{ $detail?->state ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Country</span>
                <span class="info-val">{{ $detail?->country ?: 'India' }}</span>
            </div>
        </div>
    </div>

    {{-- Bank Details --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">account_balance</span>
            <h6>Bank Details</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Bank Name</span>
                <span class="info-val {{ $detail?->bank_name ? '' : 'empty' }}">{{ $detail?->bank_name ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Account No.</span>
                @php
                    $accRaw = $detail?->bank_account_number ?? '';
                    $accMasked = $accRaw
                        ? str_repeat('X', max(0, strlen($accRaw) - 4)) . substr($accRaw, -4)
                        : null;
                @endphp
                <span class="info-val {{ $accMasked ? 'masked' : 'empty' }}">{{ $accMasked ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">IFSC Code</span>
                <span class="info-val {{ $detail?->ifsc_code ? '' : 'empty' }}">{{ $detail?->ifsc_code ?: '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Emergency Contact --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">emergency</span>
            <h6>Emergency Contact</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Contact No.</span>
                <span class="info-val {{ $detail?->emergency_contact ? '' : 'empty' }}">
                    {{ $detail?->emergency_contact ?: 'Not provided' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Reporting Structure --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="material-symbols-outlined">account_tree</span>
            <h6>Reporting Structure</h6>
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">Level 1 Manager</span>
                <span class="info-val">
                    @if($user->level1Manager)
                        <span class="manager-chip">
                            <span class="material-symbols-outlined">person</span>
                            {{ $user->level1Manager->name }}
                            @if($user->level1Manager->employee_code)
                                <span style="color:var(--text-muted);font-weight:400;">({{ $user->level1Manager->employee_code }})</span>
                            @endif
                        </span>
                    @else
                        <span class="empty">Not assigned</span>
                    @endif
                </span>
            </div>
            @if($user->role === 'employee')
            <div class="info-row">
                <span class="info-key">Level 2 Manager</span>
                <span class="info-val">
                    @if($user->level2Manager)
                        <span class="manager-chip">
                            <span class="material-symbols-outlined">person</span>
                            {{ $user->level2Manager->name }}
                            @if($user->level2Manager->employee_code)
                                <span style="color:var(--text-muted);font-weight:400;">({{ $user->level2Manager->employee_code }})</span>
                            @endif
                        </span>
                    @else
                        <span class="empty">Not assigned</span>
                    @endif
                </span>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
