@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
<style>
    /* ── Back link ──────────────────────────────────────────────────────────── */
    .back-link {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .83rem; font-weight: 600; color: var(--text-secondary);
        text-decoration: none; margin-bottom: 1.25rem; transition: color .15s;
    }
    .back-link:hover { color: var(--primary); }
    .back-link .material-symbols-outlined { font-size: 1rem; }

    /* ── Form card ──────────────────────────────────────────────────────────── */
    .edit-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; margin-bottom: 1.25rem;
    }
    .edit-card-header {
        display: flex; align-items: center; gap: .5rem;
        padding: .8rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }
    .edit-card-header .material-symbols-outlined { font-size: 1.1rem; color: var(--primary); font-variation-settings: 'FILL' 1; }
    .edit-card-header h6 { margin: 0; font-size: .85rem; font-weight: 700; color: var(--text-main); }
    .edit-card-body { padding: 1.375rem 1.5rem; }

    /* ── Field grid ─────────────────────────────────────────────────────────── */
    .field-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem 1.5rem;
    }
    @media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
    .field-grid .full { grid-column: 1 / -1; }

    /* ── Form elements ──────────────────────────────────────────────────────── */
    .form-group { display: flex; flex-direction: column; gap: .375rem; }
    .form-label {
        font-size: .75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-muted);
    }
    .form-label .req { color: #ef4444; margin-left: .1rem; }
    .form-control {
        width: 100%; padding: .55rem .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        transition: border-color .15s, box-shadow .15s;
        outline: none;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle);
    }
    .form-control.is-invalid { border-color: #ef4444; }
    .form-control:disabled, .form-control[readonly] {
        background: #f8fafc; color: var(--text-muted); cursor: not-allowed;
    }
    .field-hint { font-size: .73rem; color: var(--text-muted); margin-top: .2rem; }
    .error-msg { font-size: .78rem; color: #dc2626; margin-top: .2rem; }

    /* ── Avatar upload ──────────────────────────────────────────────────────── */
    .avatar-upload-wrap {
        display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;
    }
    .avatar-preview {
        width: 72px; height: 72px; border-radius: 50%;
        object-fit: cover; border: 2px solid var(--border);
        flex-shrink: 0;
    }
    .avatar-preview-fallback {
        width: 72px; height: 72px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
        color: #fff; font-size: 1.5rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid var(--border); flex-shrink: 0;
    }
    .avatar-upload-info { flex: 1; }
    .avatar-upload-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        background: var(--bg-light); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); padding: .4rem .875rem;
        font-size: .82rem; font-weight: 600; color: var(--text-main);
        cursor: pointer; transition: border-color .15s;
    }
    .avatar-upload-btn:hover { border-color: var(--primary); color: var(--primary); }
    .avatar-upload-btn .material-symbols-outlined { font-size: .95rem; }
    #profileImageInput { display: none; }

    /* ── Read-only note ─────────────────────────────────────────────────────── */
    .readonly-note {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .73rem; color: var(--text-muted); margin-top: .3rem;
    }
    .readonly-note .material-symbols-outlined { font-size: .85rem; }

    /* ── Actions ────────────────────────────────────────────────────────────── */
    .form-actions {
        display: flex; align-items: center; gap: .75rem;
        padding-top: 1.25rem; border-top: 1px solid var(--border); margin-top: 1.5rem;
    }
    .btn-primary {
        display: inline-flex; align-items: center; gap: .4rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); padding: .6rem 1.375rem;
        font-size: .875rem; font-weight: 600; cursor: pointer; transition: background .15s;
    }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-primary .material-symbols-outlined { font-size: 1rem; }
    .btn-cancel {
        display: inline-flex; align-items: center; gap: .4rem;
        background: transparent; color: var(--text-secondary);
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        padding: .6rem 1.125rem; font-size: .875rem; font-weight: 600;
        cursor: pointer; text-decoration: none; transition: all .15s;
    }
    .btn-cancel:hover { border-color: var(--text-secondary); color: var(--text-main); }
</style>
@endpush

@section('content')

<a href="{{ route('profile') }}" class="back-link">
    <span class="material-symbols-outlined">arrow_back</span>
    Back to Profile
</a>

<h1 class="page-title">Edit Profile</h1>
<p class="page-subtitle">Update your contact details and address. Sensitive fields are managed by admin.</p>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php $detail = $user->employeeDetail; @endphp

    {{-- Profile Photo --}}
    <div class="edit-card">
        <div class="edit-card-header">
            <span class="material-symbols-outlined">photo_camera</span>
            <h6>Profile Photo</h6>
        </div>
        <div class="edit-card-body">
            <div class="avatar-upload-wrap">
                @if($detail?->profile_image)
                    <img src="{{ Storage::url($detail->profile_image) }}"
                         alt="{{ $user->name }}"
                         class="avatar-preview" id="avatarPreview">
                @else
                    <div class="avatar-preview-fallback" id="avatarFallback">{{ $user->initials() }}</div>
                    <img src="" alt="" class="avatar-preview" id="avatarPreview" style="display:none;">
                @endif
                <div class="avatar-upload-info">
                    <label class="avatar-upload-btn" for="profileImageInput">
                        <span class="material-symbols-outlined">upload</span>
                        Choose Photo
                    </label>
                    <input type="file" name="profile_image" id="profileImageInput" accept="image/*">
                    <div class="field-hint" style="margin-top:.5rem;">JPG, PNG or WebP · Max 2 MB</div>
                    @error('profile_image')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Details --}}
    <div class="edit-card">
        <div class="edit-card-header">
            <span class="material-symbols-outlined">phone</span>
            <h6>Contact Details</h6>
        </div>
        <div class="edit-card-body">
            <div class="field-grid">
                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                           value="{{ old('mobile', $user->mobile) }}"
                           placeholder="+91 99999 99999" maxlength="15">
                    @error('mobile')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact"
                           class="form-control @error('emergency_contact') is-invalid @enderror"
                           value="{{ old('emergency_contact', $detail?->emergency_contact) }}"
                           placeholder="+91 99999 99999" maxlength="15">
                    @error('emergency_contact')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="edit-card">
        <div class="edit-card-header">
            <span class="material-symbols-outlined">home</span>
            <h6>Address</h6>
        </div>
        <div class="edit-card-body">
            <div class="field-grid">
                <div class="form-group full">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line1"
                           class="form-control @error('address_line1') is-invalid @enderror"
                           value="{{ old('address_line1', $detail?->address_line1) }}"
                           placeholder="Door / Flat No., Street Name">
                    @error('address_line1')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group full">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2"
                           class="form-control @error('address_line2') is-invalid @enderror"
                           value="{{ old('address_line2', $detail?->address_line2) }}"
                           placeholder="Area, Landmark (optional)">
                    @error('address_line2')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city"
                           class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $detail?->city) }}"
                           placeholder="City">
                    @error('city')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" name="state"
                           class="form-control @error('state') is-invalid @enderror"
                           value="{{ old('state', $detail?->state) }}"
                           placeholder="State">
                    @error('state')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country"
                           class="form-control @error('country') is-invalid @enderror"
                           value="{{ old('country', $detail?->country ?? 'India') }}"
                           placeholder="Country">
                    @error('country')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Read-only notice --}}
    <div class="edit-card">
        <div class="edit-card-header">
            <span class="material-symbols-outlined">lock</span>
            <h6>Read-only Fields</h6>
        </div>
        <div class="edit-card-body">
            <div class="field-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                    <div class="readonly-note"><span class="material-symbols-outlined">lock</span> Managed by admin</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                    <div class="readonly-note"><span class="material-symbols-outlined">lock</span> Managed by admin</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Employee Code</label>
                    <input type="text" class="form-control" value="{{ $user->employee_code ?: '—' }}" readonly>
                    <div class="readonly-note"><span class="material-symbols-outlined">lock</span> Managed by admin</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                    <div class="readonly-note"><span class="material-symbols-outlined">lock</span> Managed by admin</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" class="form-control" value="{{ $user->department?->name ?? '—' }}" readonly>
                    <div class="readonly-note"><span class="material-symbols-outlined">lock</span> Managed by admin</div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <span class="material-symbols-outlined">save</span>
            Save Changes
        </button>
        <a href="{{ route('profile') }}" class="btn-cancel">Cancel</a>
    </div>

</form>

@endsection

@push('scripts')
<script>
(function () {
    var input   = document.getElementById('profileImageInput');
    var preview = document.getElementById('avatarPreview');
    var fallback = document.getElementById('avatarFallback');

    if (!input) return;

    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = '';
            if (fallback) fallback.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endpush
