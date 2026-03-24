@extends('layouts.app')

@section('title', 'Add Department')

@push('styles')
<style>
    .breadcrumb-bar {
        display: flex; align-items: center; gap: .375rem;
        font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.25rem;
    }
    .breadcrumb-bar a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-bar a:hover { text-decoration: underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size: .9375rem; }

    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        max-width: 600px;
    }
    .form-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .625rem;
        background: #fafbfd;
    }
    .form-card-header .material-symbols-outlined { color: var(--primary); font-size: 1.25rem; }
    .form-card-header h5 { font-size: .9375rem; font-weight: 700; margin: 0; }
    .form-card-body { padding: 1.5rem; }

    .form-label {
        font-size: .8125rem; font-weight: 600; color: var(--text-main);
        margin-bottom: .375rem; display: block;
    }
    .form-control, .form-select {
        height: 2.625rem;
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border);
        font-size: .875rem; color: var(--text-main);
        background: #f8fafc;
        transition: border-color .2s, box-shadow .2s, background .2s;
        width: 100%;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle);
        background: #fff; outline: none;
    }
    .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; background-image: none; }
    .invalid-feedback { font-size: .8rem; color: #ef4444; margin-top: .25rem; display: block; }
    .form-hint { font-size: .8rem; color: var(--text-muted); margin-top: .3rem; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 575px) { .form-row { grid-template-columns: 1fr; } }

    .btn-save {
        height: 2.625rem; padding: 0 1.5rem;
        background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        cursor: pointer; transition: background .15s, box-shadow .15s;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
    }
    .btn-save:hover { background: var(--primary-hover); box-shadow: 0 4px 14px rgba(19,127,236,.4); }

    .btn-cancel {
        height: 2.625rem; padding: 0 1.25rem;
        background: transparent; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none; cursor: pointer; transition: all .15s;
    }
    .btn-cancel:hover { border-color: #94a3b8; color: var(--text-main); background: var(--bg-light); }
</style>
@endpush

@section('content')

    <div class="breadcrumb-bar">
        <a href="{{ route('admin.departments.index') }}">Departments</a>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>Add Department</span>
    </div>

    <div class="page-title">Add Department</div>
    <p class="page-subtitle">Create a new department for your organisation.</p>

    <div class="form-card">
        <div class="form-card-header">
            <span class="material-symbols-outlined">corporate_fare</span>
            <h5>Department Details</h5>
        </div>
        <div class="form-card-body">

            <form method="POST" action="{{ route('admin.departments.store') }}" novalidate>
                @csrf

                {{-- Name + Code --}}
                <div class="form-row mb-4">
                    <div>
                        <label for="name" class="form-label">
                            Department Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Human Resources"
                               maxlength="150" autofocus required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="code" class="form-label">Department Code</label>
                        <input type="text" id="code" name="code"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}"
                               placeholder="e.g. HR"
                               maxlength="10">
                        <div class="form-hint">Short abbreviation displayed in the table.</div>
                        @error('code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" name="description"
                           class="form-control @error('description') is-invalid @enderror"
                           value="{{ old('description') }}"
                           placeholder="e.g. Human Resources and Talent Acquisition"
                           maxlength="255">
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Saturday Leave flag --}}
                <div class="mb-4">
                    <label class="form-label">Leave Rules</label>
                    <div style="display:flex; align-items:center; gap:.625rem;
                                padding:.75rem 1rem; border:1.5px solid var(--border);
                                border-radius:var(--radius-sm); background:#f8fafc;">
                        <input type="hidden" name="has_saturday_leave" value="0">
                        <input type="checkbox" id="has_saturday_leave" name="has_saturday_leave" value="1"
                               {{ old('has_saturday_leave') ? 'checked' : '' }}
                               style="width:1rem; height:1rem; cursor:pointer; accent-color:var(--primary);">
                        <label for="has_saturday_leave" style="cursor:pointer; margin:0; font-size:.875rem; font-weight:600; color:var(--text-main);">
                            Enable Saturday Leave
                        </label>
                        <span style="font-size:.78rem; color:var(--text-muted); margin-left:.25rem;">
                            — 1 optional Saturday leave credited per month (HR Recruiting dept)
                        </span>
                    </div>
                </div>

                {{-- Saturday Rule + Status --}}
                <div class="form-row mb-4">
                    <div>
                        <label for="saturday_rule" class="form-label">
                            Saturday Rule <span class="text-danger">*</span>
                        </label>
                        <select id="saturday_rule" name="saturday_rule"
                                class="form-select @error('saturday_rule') is-invalid @enderror">
                            <option value="none"                  {{ old('saturday_rule', 'none') === 'none'                  ? 'selected' : '' }}>No Rule</option>
                            <option value="2nd_saturday_off"      {{ old('saturday_rule') === '2nd_saturday_off'      ? 'selected' : '' }}>2nd Saturday Off</option>
                            <option value="all_saturdays_off"     {{ old('saturday_rule') === 'all_saturdays_off'     ? 'selected' : '' }}>All Saturdays Off</option>
                            <option value="alternating_saturdays" {{ old('saturday_rule') === 'alternating_saturdays' ? 'selected' : '' }}>Alternating Saturdays</option>
                        </select>
                        @error('saturday_rule')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="status" class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select id="status" name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="form-hint">Only active departments appear in dropdowns.</div>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2"
                     style="border-top:1px solid var(--border); margin-top:1.25rem; padding-top:1.25rem;">
                    <button type="submit" class="btn-save">
                        <span class="material-symbols-outlined" style="font-size:1rem">save</span>
                        Save Department
                    </button>
                    <a href="{{ route('admin.departments.index') }}" class="btn-cancel">
                        <span class="material-symbols-outlined" style="font-size:1rem">close</span>
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

@endsection
