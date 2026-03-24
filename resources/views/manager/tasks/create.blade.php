@extends('layouts.app')

@section('title', 'Create Task')

@push('styles')
<style>
    .form-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);overflow:hidden; }
    .form-card-header { padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;gap:.5rem; }
    .form-card-header h5 { font-size:.9375rem;font-weight:700;margin:0;color:var(--text-main); }
    .form-card-header .material-symbols-outlined { color:var(--primary);font-size:1.2rem; }
    .form-body { padding:1.5rem; }

    .form-group { margin-bottom:1.25rem; }
    .form-label { display:block;font-size:.8125rem;font-weight:600;color:var(--text-main);margin-bottom:.4rem; }
    .form-label .req { color:#ef4444;margin-left:.2rem; }
    .form-control {
        width:100%;padding:.5625rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);
        font-size:.875rem;color:var(--text-main);background:var(--surface);transition:border-color .15s,box-shadow .15s;
    }
    .form-control:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .form-control.is-invalid { border-color:#ef4444; }
    .invalid-feedback { font-size:.78rem;color:#dc2626;margin-top:.3rem; }

    textarea.form-control { resize:vertical;min-height:100px; }

    .form-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:1.25rem; }
    @media(max-width:640px){ .form-grid-2 { grid-template-columns:1fr; } }

    .form-footer { padding:1rem 1.5rem;border-top:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:.75rem; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5625rem 1.25rem;font-size:.875rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-secondary { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem 1.125rem;font-size:.875rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:all .15s; }
    .btn-secondary:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }

    .pri-option { display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:all .15s;font-size:.875rem; }
    .pri-option input { display:none; }
    .pri-option:has(input:checked) { border-color:var(--primary);background:var(--primary-subtle); }
    .pri-options { display:flex;gap:.75rem;flex-wrap:wrap; }
    .pri-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
    <a href="{{ route('manager.tasks.index') }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;transition:all .15s;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='var(--surface)'">
        <span class="material-symbols-outlined" style="font-size:1.1rem;">arrow_back</span>
    </a>
    <div>
        <h1 class="page-title">Create Task</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Assign a new task to a team member.</p>
    </div>
</div>

<form method="POST" action="{{ route('manager.tasks.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-card">
        <div class="form-card-header">
            <span class="material-symbols-outlined">add_task</span>
            <h5>Task Details</h5>
        </div>
        <div class="form-body">

            <div class="form-group">
                <label class="form-label">Task Title <span class="req">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="e.g. Prepare monthly report">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Describe what needs to be done...">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Assign To <span class="req">*</span></label>
                    <select name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror">
                        <option value="">— Select Team Member —</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}" {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }} ({{ $member->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($teamMembers->isEmpty())
                        <div style="font-size:.78rem;color:#b45309;margin-top:.35rem;">No active team members found.</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Priority <span class="req">*</span></label>
                    <select name="priority" class="form-control @error('priority') is-invalid @enderror">
                        <option value="low"    {{ old('priority','medium') === 'low'    ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority','medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high"   {{ old('priority','medium') === 'high'   ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Start Date <span class="req">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', now()->toDateString()) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date <span class="req">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date') }}">
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Attachment <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                <div style="font-size:.77rem;color:var(--text-muted);margin-top:.3rem;">Accepted: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX — Max 4MB</div>
                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
        <div class="form-footer">
            <a href="{{ route('manager.tasks.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">add_task</span>
                Assign Task
            </button>
        </div>
    </div>
</form>
@endsection
