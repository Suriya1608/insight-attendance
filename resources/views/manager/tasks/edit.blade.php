@extends('layouts.app')

@section('title', 'Edit Task')

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
    .form-control { width:100%;padding:.5625rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;color:var(--text-main);background:var(--surface);transition:border-color .15s,box-shadow .15s; }
    .form-control:focus { outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-subtle); }
    .form-control.is-invalid { border-color:#ef4444; }
    .invalid-feedback { font-size:.78rem;color:#dc2626;margin-top:.3rem; }
    textarea.form-control { resize:vertical;min-height:100px; }
    .form-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:1.25rem; }
    @media(max-width:640px){ .form-grid-2 { grid-template-columns:1fr; } }
    .form-grid-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem; }
    @media(max-width:700px){ .form-grid-3 { grid-template-columns:1fr 1fr; } }
    @media(max-width:480px){ .form-grid-3 { grid-template-columns:1fr; } }
    .form-footer { padding:1rem 1.5rem;border-top:1px solid var(--border);background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:.75rem; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.5625rem 1.25rem;font-size:.875rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:background .15s; }
    .btn-primary:hover { background:var(--primary-hover);color:#fff; }
    .btn-secondary { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem 1.125rem;font-size:.875rem;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:all .15s; }
    .btn-secondary:hover { background:var(--bg-light);color:var(--text-main);border-color:#cbd5e1; }
    .current-file { display:inline-flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--text-secondary);background:var(--bg-light);padding:.3rem .6rem;border-radius:var(--radius-sm);border:1px solid var(--border);text-decoration:none;margin-top:.4rem; }
    .current-file:hover { color:var(--primary); }
    .current-file .material-symbols-outlined { font-size:.95rem; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
    <a href="{{ route('manager.tasks.show', $task) }}" style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);text-decoration:none;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='var(--surface)'">
        <span class="material-symbols-outlined" style="font-size:1.1rem;">arrow_back</span>
    </a>
    <div>
        <h1 class="page-title">Edit Task</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Update task details and assignment.</p>
    </div>
</div>

@if($errors->any())
    <div style="background:#fff1f2;border:1px solid #fecaca;border-radius:var(--radius-md);padding:.875rem 1rem;margin-bottom:1rem;font-size:.875rem;color:#dc2626;">
        <strong>Please fix the following errors:</strong>
        <ul style="margin:.4rem 0 0 1.25rem;padding:0;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('manager.tasks.update', $task) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="form-card">
        <div class="form-card-header">
            <span class="material-symbols-outlined">edit</span>
            <h5>Task Details</h5>
        </div>
        <div class="form-body">

            <div class="form-group">
                <label class="form-label">Task Title <span class="req">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $task->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Assign To <span class="req">*</span></label>
                    <select name="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror">
                        <option value="">— Select Team Member —</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}" {{ old('assigned_to', $task->assigned_to) == $member->id ? 'selected' : '' }}>
                                {{ $member->name }} ({{ $member->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Priority <span class="req">*</span></label>
                    <select name="priority" class="form-control @error('priority') is-invalid @enderror">
                        <option value="low"    {{ old('priority', $task->priority) === 'low'    ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high"   {{ old('priority', $task->priority) === 'high'   ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">Start Date <span class="req">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', $task->start_date->toDateString()) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date <span class="req">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date', $task->due_date->toDateString()) }}">
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Status <span class="req">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="pending"     {{ old('status', $task->status) === 'pending'     ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed"   {{ old('status', $task->status) === 'completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Attachment</label>
                @if($task->attachment)
                    <div style="margin-bottom:.5rem;">
                        <a href="{{ Storage::url($task->attachment) }}" target="_blank" class="current-file">
                            <span class="material-symbols-outlined">attach_file</span>
                            Current attachment
                        </a>
                    </div>
                @endif
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                <div style="font-size:.77rem;color:var(--text-muted);margin-top:.3rem;">Leave empty to keep existing attachment. Accepted: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX — Max 4MB</div>
                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
        <div class="form-footer">
            <a href="{{ route('manager.tasks.show', $task) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">save</span>
                Save Changes
            </button>
        </div>
    </div>
</form>
@endsection
