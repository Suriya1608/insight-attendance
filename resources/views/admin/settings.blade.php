@extends('layouts.app')

@section('title', 'Site Settings')

@push('styles')
<style>
    /* ── Settings-specific styles ── */
    .settings-card {
        background: var(--surface-light);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .settings-card-header {
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: .625rem;
    }
    .settings-card-header .material-symbols-outlined {
        color: var(--primary);
        font-size: 1.25rem;
    }
    .settings-card-header h5 {
        font-size: .9375rem;
        font-weight: 700;
        margin: 0;
    }
    .settings-card-body { padding: 1.5rem; }

    /* Form controls */
    .form-label {
        font-size: .8125rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: .375rem;
    }
    .form-label .text-danger { font-size: .75rem; }
    .form-control, .form-select {
        height: 2.625rem;
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border-light);
        font-size: .875rem;
        color: var(--text-main);
        background: var(--bg-light);
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(19,127,236,.12);
        background: #fff;
    }
    .form-control.is-invalid { border-color: #dc3545; background-image: none; }
    .invalid-feedback { font-size: .8rem; }

    /* Upload zone */
    .upload-zone {
        border: 2px dashed var(--border-light);
        border-radius: var(--radius-sm);
        padding: 1rem;
        background: var(--bg-light);
        transition: border-color .2s, background .2s;
        cursor: pointer;
    }
    .upload-zone:hover { border-color: var(--primary); background: rgba(19,127,236,.04); }
    .upload-zone input[type="file"] { display: none; }

    /* Image previews */
    .img-preview-wrap {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: .875rem;
    }
    .img-preview-box {
        width: 80px; height: 80px;
        border: 1.5px solid var(--border-light);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-light);
        overflow: hidden;
        flex-shrink: 0;
    }
    .img-preview-box img {
        max-width: 100%; max-height: 100%;
        object-fit: contain;
    }
    .img-preview-box .placeholder-icon {
        font-size: 2rem;
        color: #cbd5e1;
    }
    .favicon-preview-box { width: 48px; height: 48px; }

    .upload-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .8125rem;
        font-weight: 600;
        color: var(--primary);
        cursor: pointer;
        margin-bottom: .25rem;
    }
    .upload-label .material-symbols-outlined { font-size: 1.125rem; }
    .upload-hint { font-size: .75rem; color: var(--text-secondary); }

    /* Alert */
    .alert-success-custom {
        background: rgba(22,163,74,.07);
        border: 1px solid rgba(22,163,74,.25);
        border-radius: var(--radius-sm);
        padding: .75rem 1rem;
        font-size: .875rem;
        color: #15803d;
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1.5rem;
    }

    /* Submit button */
    .btn-save {
        height: 2.75rem;
        padding: 0 1.75rem;
        background: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        color: #fff;
        font-size: .9375rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        cursor: pointer;
        transition: background .2s, transform .1s;
    }
    .btn-save:hover  { background: #0f6fd4; }
    .btn-save:active { transform: scale(.98); }

    .btn-cancel {
        height: 2.75rem;
        padding: 0 1.25rem;
        background: transparent;
        border: 1.5px solid var(--border-light);
        border-radius: var(--radius-sm);
        color: var(--text-secondary);
        font-size: .9375rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        cursor: pointer;
        transition: .2s;
        text-decoration: none;
    }
    .btn-cancel:hover { border-color: #94a3b8; color: var(--text-main); }

    /* Section divider */
    .section-divider {
        height: 1px;
        background: var(--border-light);
        margin: 1.75rem 0;
    }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex align-items-center gap-2 mb-1">
        <div class="page-title">Site Settings</div>
    </div>
    <p class="page-subtitle">Manage your HR Portal's basic configuration.</p>

    {{-- Success alert --}}
    @if(session('success'))
        <div class="alert-success-custom">
            <span class="material-symbols-outlined" style="font-size:1.1rem">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.settings.update') }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        <div class="row g-4">

            {{-- ── Left column: General Info ── --}}
            <div class="col-lg-7">

                {{-- General Information --}}
                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined">info</span>
                        <h5>General Information</h5>
                    </div>
                    <div class="settings-card-body">

                        {{-- Site Title --}}
                        <div class="mb-3">
                            <label for="site_title" class="form-label">
                                Site Title <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="site_title"
                                name="site_title"
                                class="form-control @error('site_title') is-invalid @enderror"
                                value="{{ old('site_title', $settings['site_title'] ?? '') }}"
                                placeholder="e.g. HR Portal — Attendance & Payroll"
                                maxlength="150"
                                required
                            >
                            <div class="form-text">Shown in the browser tab title.</div>
                            @error('site_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Site Name --}}
                        <div class="mb-3">
                            <label for="site_name" class="form-label">
                                Site Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="site_name"
                                name="site_name"
                                class="form-control @error('site_name') is-invalid @enderror"
                                value="{{ old('site_name', $settings['site_name'] ?? '') }}"
                                placeholder="e.g. HR Portal"
                                maxlength="150"
                                required
                            >
                            <div class="form-text">Short name displayed in the header and emails.</div>
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Site URL --}}
                        <div class="mb-0">
                            <label for="site_url" class="form-label">
                                Site URL <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-radius:var(--radius-sm) 0 0 var(--radius-sm); border:1.5px solid var(--border-light); border-right:0; background:var(--bg-light); font-size:.875rem; color:var(--text-secondary);">
                                    <span class="material-symbols-outlined" style="font-size:1rem;">link</span>
                                </span>
                                <input
                                    type="url"
                                    id="site_url"
                                    name="site_url"
                                    class="form-control @error('site_url') is-invalid @enderror"
                                    style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;"
                                    value="{{ old('site_url', $settings['site_url'] ?? '') }}"
                                    placeholder="https://yoursite.com"
                                    maxlength="255"
                                    required
                                >
                                @error('site_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Offer Letter / Company Details --}}
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined">description</span>
                        <h5>Offer Letter Details</h5>
                    </div>
                    <div class="settings-card-body">

                        <div class="mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea id="company_address" name="company_address" rows="3"
                                      class="form-control @error('company_address') is-invalid @enderror"
                                      placeholder="e.g. 1-119, Kamarajar Street, Chennai, Tamil Nadu 600122"
                                      maxlength="500">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                            <div class="form-text">Displayed in the offer letter PDF header.</div>
                            @error('company_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="company_email" class="form-label">Company Email</label>
                                <input type="email" id="company_email" name="company_email"
                                       class="form-control @error('company_email') is-invalid @enderror"
                                       value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                                       placeholder="e.g. info@insighthcm.in" maxlength="200">
                                <div class="form-text">Shown in the PDF footer.</div>
                                @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="company_mobile" class="form-label">Company Mobile</label>
                                <input type="text" id="company_mobile" name="company_mobile"
                                       class="form-control @error('company_mobile') is-invalid @enderror"
                                       value="{{ old('company_mobile', $settings['company_mobile'] ?? '') }}"
                                       placeholder="e.g. +91 98765 43210" maxlength="20">
                                <div class="form-text">Shown in the PDF footer.</div>
                                @error('company_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-0">
                            <div class="col-sm-6">
                                <label for="signatory_name" class="form-label">Signatory Name</label>
                                <input type="text" id="signatory_name" name="signatory_name"
                                       class="form-control @error('signatory_name') is-invalid @enderror"
                                       value="{{ old('signatory_name', $settings['signatory_name'] ?? '') }}"
                                       placeholder="e.g. S.Murugan" maxlength="150">
                                @error('signatory_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="signatory_designation" class="form-label">Signatory Designation</label>
                                <input type="text" id="signatory_designation" name="signatory_designation"
                                       class="form-control @error('signatory_designation') is-invalid @enderror"
                                       value="{{ old('signatory_designation', $settings['signatory_designation'] ?? '') }}"
                                       placeholder="e.g. Managing Director" maxlength="150">
                                @error('signatory_designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- HR Configuration --}}
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined">badge</span>
                        <h5>HR Configuration</h5>
                    </div>
                    <div class="settings-card-body">

                        <div class="mb-0">
                            <label for="employee_id_prefix" class="form-label">
                                Employee ID Prefix <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    id="employee_id_prefix"
                                    name="employee_id_prefix"
                                    class="form-control @error('employee_id_prefix') is-invalid @enderror"
                                    value="{{ old('employee_id_prefix', $settings['employee_id_prefix'] ?? 'EMP') }}"
                                    placeholder="e.g. IHCM"
                                    maxlength="10"
                                    style="text-transform:uppercase;"
                                    oninput="this.value=this.value.toUpperCase();updatePreview()"
                                    required
                                >
                                <span class="input-group-text" style="border:1.5px solid var(--border-light);border-left:0;background:var(--bg-light);font-size:.82rem;color:var(--text-secondary);" id="prefixPreviewBadge">
                                    {{ strtoupper($settings['employee_id_prefix'] ?? 'EMP') }}0001
                                </span>
                            </div>
                            <div class="form-text">
                                Uppercase letters and numbers only. Employee IDs will be generated as
                                <strong id="prefixPreviewText">{{ strtoupper($settings['employee_id_prefix'] ?? 'EMP') }}0001</strong>,
                                <strong id="prefixPreviewText2">{{ strtoupper($settings['employee_id_prefix'] ?? 'EMP') }}0002</strong> …
                            </div>
                            @error('employee_id_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3 p-3 rounded" style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.3);font-size:.8rem;color:#92400e;display:flex;gap:.5rem;align-items:flex-start;">
                            <span class="material-symbols-outlined" style="font-size:1rem;flex-shrink:0;margin-top:1px;font-variation-settings:'FILL' 1">warning</span>
                            Changing the prefix only affects <strong>new</strong> employees. Existing employee IDs are not renamed.
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── Right column: Media ── --}}
            <div class="col-lg-5">

                {{-- Site Logo --}}
                <div class="settings-card mb-4">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined">image</span>
                        <h5>Site Logo</h5>
                    </div>
                    <div class="settings-card-body">

                        {{-- Current preview --}}
                        <div class="img-preview-wrap">
                            <div class="img-preview-box" id="logo-preview-box">
                                @if(!empty($settings['site_logo']))
                                    <img src="{{ Storage::url($settings['site_logo']) }}" alt="Current Logo" id="logo-preview-img">
                                @else
                                    <span class="material-symbols-outlined placeholder-icon" id="logo-placeholder">image</span>
                                @endif
                            </div>
                            <div>
                                <div style="font-size:.8125rem; font-weight:600; color:var(--text-main); margin-bottom:.2rem;">
                                    {{ !empty($settings['site_logo']) ? 'Current Logo' : 'No logo uploaded' }}
                                </div>
                                <div class="upload-hint">Recommended: 200×60 px, PNG/SVG</div>
                            </div>
                        </div>

                        @error('site_logo')
                            <div class="text-danger mb-2" style="font-size:.8rem">{{ $message }}</div>
                        @enderror

                        {{-- Upload zone --}}
                        <div class="upload-zone" onclick="document.getElementById('site_logo').click()">
                            <label class="upload-label mb-1" style="pointer-events:none;">
                                <span class="material-symbols-outlined">upload</span>
                                Choose logo file
                            </label>
                            <div class="upload-hint">JPG, PNG, SVG, WEBP &nbsp;·&nbsp; Max 2 MB</div>
                            <input
                                type="file"
                                id="site_logo"
                                name="site_logo"
                                accept="image/*"
                                onchange="previewImage(this, 'logo-preview-box', 'logo-preview-img', 'logo-placeholder')"
                            >
                        </div>

                    </div>
                </div>

                {{-- Favicon --}}
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined">tab</span>
                        <h5>Favicon</h5>
                    </div>
                    <div class="settings-card-body">

                        {{-- Current preview --}}
                        <div class="img-preview-wrap">
                            <div class="img-preview-box favicon-preview-box" id="favicon-preview-box">
                                @if(!empty($settings['site_favicon']))
                                    <img src="{{ Storage::url($settings['site_favicon']) }}" alt="Current Favicon" id="favicon-preview-img">
                                @else
                                    <span class="material-symbols-outlined placeholder-icon" style="font-size:1.5rem" id="favicon-placeholder">tab</span>
                                @endif
                            </div>
                            <div>
                                <div style="font-size:.8125rem; font-weight:600; color:var(--text-main); margin-bottom:.2rem;">
                                    {{ !empty($settings['site_favicon']) ? 'Current Favicon' : 'No favicon uploaded' }}
                                </div>
                                <div class="upload-hint">Recommended: 32×32 px, ICO/PNG</div>
                            </div>
                        </div>

                        @error('site_favicon')
                            <div class="text-danger mb-2" style="font-size:.8rem">{{ $message }}</div>
                        @enderror

                        {{-- Upload zone --}}
                        <div class="upload-zone" onclick="document.getElementById('site_favicon').click()">
                            <label class="upload-label mb-1" style="pointer-events:none;">
                                <span class="material-symbols-outlined">upload</span>
                                Choose favicon file
                            </label>
                            <div class="upload-hint">ICO, PNG, JPG, SVG &nbsp;·&nbsp; Max 512 KB</div>
                            <input
                                type="file"
                                id="site_favicon"
                                name="site_favicon"
                                accept="image/*,.ico"
                                onchange="previewImage(this, 'favicon-preview-box', 'favicon-preview-img', 'favicon-placeholder')"
                            >
                        </div>

                    </div>
                </div>

            </div>

        </div>{{-- /row --}}

        {{-- ── Action buttons ── --}}
        <div class="d-flex align-items-center gap-3 mt-4 pt-3" style="border-top:1px solid var(--border-light);">
            <button type="submit" class="btn-save">
                <span class="material-symbols-outlined" style="font-size:1.125rem">save</span>
                Update Settings
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn-cancel">
                <span class="material-symbols-outlined" style="font-size:1rem">close</span>
                Cancel
            </a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
    function updatePreview() {
        const prefix  = (document.getElementById('employee_id_prefix').value || 'EMP').toUpperCase();
        const sample1 = prefix + '0001';
        const sample2 = prefix + '0002';
        document.getElementById('prefixPreviewBadge').textContent = sample1;
        document.getElementById('prefixPreviewText').textContent  = sample1;
        document.getElementById('prefixPreviewText2').textContent = sample2;
    }

    /**
     * Live-preview selected image before upload.
     */
    function previewImage(input, boxId, imgId, placeholderId) {
        const box         = document.getElementById(boxId);
        const placeholder = document.getElementById(placeholderId);

        if (!input.files || !input.files[0]) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            // Remove placeholder icon
            if (placeholder) placeholder.style.display = 'none';

            // Find or create the <img>
            let img = document.getElementById(imgId);
            if (!img) {
                img = document.createElement('img');
                img.id = imgId;
                img.alt = 'Preview';
                box.appendChild(img);
            }
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
</script>
@endpush
