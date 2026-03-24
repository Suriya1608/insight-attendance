@extends('layouts.app')

@section('title', 'Optional Holiday Settings')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
    @media (max-width:900px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width:500px) { .stats-grid { grid-template-columns: 1fr; } }

    /* Card */
    .card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .card-header {
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b; display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    }
    .card-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: #fff; display: flex; align-items: center; gap: .5rem; }
    .card-body { padding: 1.5rem; }

    /* Form inline */
    .add-form-grid {
        display: grid;
        grid-template-columns: 130px 130px 1fr auto auto;
        gap: .875rem; align-items: end;
    }
    @media (max-width:860px) { .add-form-grid { grid-template-columns: 1fr 1fr; } }

    .form-label {
        display: block; font-size: .78rem; font-weight: 600;
        color: var(--text-secondary); margin-bottom: .4rem; letter-spacing: .01em;
    }
    .form-label .req { color: #ef4444; margin-left: .15rem; }
    .form-control {
        width: 100%; height: 2.5rem; padding: 0 .875rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; color: var(--text-main); background: var(--surface);
        outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-subtle); }
    .form-control.is-invalid { border-color: #ef4444; }
    .invalid-feedback { font-size: .78rem; color: #ef4444; margin-top: .3rem; display: block; }

    /* Buttons */
    .btn {
        height: 2.5rem; padding: 0 1.25rem;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: .4rem;
        border: none; text-decoration: none; white-space: nowrap;
    }
    .btn-primary  { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-hover); color: #fff; }
    .btn-sm { height: 2rem; padding: 0 .875rem; font-size: .8rem; }
    .btn-ghost { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--border); }
    .btn-ghost:hover { background: var(--bg-light); color: var(--text-main); }
    .btn-danger-ghost { background: transparent; color: #dc2626; border: 1.5px solid #fecaca; }
    .btn-danger-ghost:hover { background: #fff1f2; }

    /* Table */
    .table-wrap { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--border); margin-top: 1.5rem; }
    table { width: 100%; border-collapse: collapse; background: var(--surface); }
    thead tr { background: var(--bg-light); }
    th {
        padding: .75rem 1rem; text-align: left; font-weight: 700;
        font-size: .76rem; text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-secondary); border-bottom: 1px solid var(--border);
    }
    td { padding: .875rem 1rem; border-bottom: 1px solid var(--border); font-size: .875rem; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }

    /* Badge */
    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .25rem .625rem; border-radius: 999px;
        font-size: .75rem; font-weight: 600; line-height: 1;
    }
    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-inactive { background: #f1f5f9; color: #64748b; }
    .badge-year     { background: #eff6ff; color: #1d4ed8; font-size: .8rem; font-weight: 700; }

    /* Inline edit form inside table */
    .edit-row { display: none; }
    .edit-row.show { display: table-row; }
    .edit-row td { background: #f8fafc; }
    .inline-edit-grid {
        display: grid; grid-template-columns: 130px 1fr auto auto; gap: .75rem; align-items: end; padding: .5rem 0;
    }

    /* Toggle */
    .toggle-switch { position: relative; width: 38px; height: 22px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0; background: #cbd5e1; border-radius: 999px; cursor: pointer; transition: background .2s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; width: 16px; height: 16px; left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .toggle-switch input:checked + .toggle-slider { background: #22c55e; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }

    /* Flash */
    .flash {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem 1rem; border-radius: var(--radius-md);
        font-size: .875rem; font-weight: 500; margin-bottom: 1rem; border: 1px solid transparent;
    }
    .flash .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-close { margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.1rem; opacity: .6; line-height: 1; }
    .flash-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .flash-error   { background: #fff1f2; color: #dc2626; border-color: #fecaca; }

    /* Info callout */
    .callout-info {
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-sm);
        padding: .875rem 1rem; font-size: .82rem; color: #1e40af; line-height: 1.6; margin-bottom: 1.5rem;
    }

    /* Hint row */
    .no-settings {
        text-align: center; padding: 3rem 1rem;
        color: var(--text-secondary); font-size: .875rem;
    }
    .no-settings .material-symbols-outlined { font-size: 2.5rem; display: block; margin-bottom: .5rem; color: var(--text-muted); }
</style>
@endpush

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash flash-success" id="flash">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
            <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif
    @if(session('error'))
        <div class="flash flash-error" id="flash">
            <span class="material-symbols-outlined">error</span>
            {{ session('error') }}
            <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">beach_access</span>
            Optional Holiday Settings
        </h1>
        <p>Configure the number of optional holidays employees can select each year.</p>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card" style="--stat-color:#137fec;">
            <div class="stat-icon" style="background:#eff6ff;">
                <span class="material-symbols-outlined" style="color:#137fec;font-variation-settings:'FILL' 1;">settings</span>
            </div>
            <div>
                <div class="stat-label">Total Settings</div>
                <div class="stat-value" style="color:#137fec;">{{ $settings->count() }}</div>
            </div>
        </div>
        <div class="stat-card" style="--stat-color:#16a34a;">
            <div class="stat-icon" style="background:#dcfce7;">
                <span class="material-symbols-outlined" style="color:#16a34a;font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            <div>
                <div class="stat-label">Active</div>
                <div class="stat-value" style="color:#16a34a;">{{ $settings->where('status', true)->count() }}</div>
            </div>
        </div>
        <div class="stat-card" style="--stat-color:#d97706;">
            <div class="stat-icon" style="background:#fffbeb;">
                <span class="material-symbols-outlined" style="color:#d97706;font-variation-settings:'FILL' 1;">today</span>
            </div>
            <div>
                <div class="stat-label">Current Year</div>
                <div class="stat-value" style="color:#d97706;">{{ $currentYear }}</div>
            </div>
        </div>
        <div class="stat-card" style="--stat-color:#7c3aed;">
            <div class="stat-icon" style="background:#f5f3ff;">
                <span class="material-symbols-outlined" style="color:#7c3aed;font-variation-settings:'FILL' 1;">beach_access</span>
            </div>
            <div>
                <div class="stat-label">Optional Holidays ({{ $currentYear }})</div>
                <div class="stat-value" style="color:#7c3aed;">
                    {{ \App\Models\Holiday::where('type','optional')->whereYear('date',$currentYear)->where('status',true)->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="callout-info">
        <strong>How it works:</strong> Add a setting for each year to control how many optional holidays employees can select.
        Employees can only select up to the configured limit. The holiday list is managed under
        <strong>Holiday List</strong> (type = Optional).
    </div>

    {{-- Add new setting --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h5>
                <span class="material-symbols-outlined" style="font-size:1.1rem;">add_circle</span>
                Add Year Setting
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.optional-holiday-settings.store') }}">
                @csrf
                <div class="add-form-grid">
                    <div>
                        <label class="form-label">Year <span class="req">*</span></label>
                        <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                               value="{{ old('year', $currentYear) }}" min="2020" max="2100" placeholder="2026">
                        @error('year')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Max Allowed <span class="req">*</span></label>
                        <input type="number" name="max_allowed" class="form-control @error('max_allowed') is-invalid @enderror"
                               value="{{ old('max_allowed', 2) }}" min="1" max="30" placeholder="2">
                        @error('max_allowed')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description') }}" placeholder="Optional note for this year…">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <div style="display:flex;align-items:center;gap:.5rem;height:2.5rem;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-size:.82rem;color:var(--text-secondary);">Active</span>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" style="margin-top:0;">
                            <span class="material-symbols-outlined">save</span> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Settings list --}}
    <div class="card">
        <div class="card-header">
            <h5>
                <span class="material-symbols-outlined" style="font-size:1.1rem;">list</span>
                Year-wise Settings
            </h5>
        </div>

        @if($settings->isEmpty())
            <div class="no-settings">
                <span class="material-symbols-outlined">beach_access</span>
                No settings configured yet. Add your first year above.
            </div>
        @else
            <div class="table-wrap" style="border-radius:0;border:none;margin-top:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Max Allowed</th>
                            <th>Description</th>
                            <th>Optional Holidays Defined</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settings as $setting)
                        <tr id="row-{{ $setting->id }}">
                            <td>
                                <span class="badge badge-year">{{ $setting->year }}</span>
                                @if($setting->year === $currentYear)
                                    <span class="badge" style="background:#fef9c3;color:#854d0e;margin-left:.25rem;font-size:.7rem;">Current</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:1.2rem;font-weight:800;color:var(--primary);">{{ $setting->max_allowed }}</span>
                                <span style="font-size:.78rem;color:var(--text-muted);margin-left:.25rem;">days</span>
                            </td>
                            <td style="color:var(--text-secondary);font-size:.84rem;">{{ $setting->description ?: '—' }}</td>
                            <td>
                                @php
                                    $count = \App\Models\Holiday::where('type','optional')
                                        ->whereYear('date', $setting->year)
                                        ->where('status',true)->count();
                                @endphp
                                <span style="font-weight:600;color:{{ $count > 0 ? '#15803d' : 'var(--text-muted)' }};">{{ $count }}</span>
                                <span style="font-size:.78rem;color:var(--text-muted);"> holiday{{ $count !== 1 ? 's' : '' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $setting->status ? 'badge-active' : 'badge-inactive' }}">
                                    <span class="material-symbols-outlined" style="font-size:.75rem;">{{ $setting->status ? 'check_circle' : 'cancel' }}</span>
                                    {{ $setting->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <button class="btn btn-ghost btn-sm" onclick="toggleEdit('{{ $setting->getRouteKey() }}')">
                                    <span class="material-symbols-outlined" style="font-size:.95rem;">edit</span> Edit
                                </button>
                                <form method="POST" action="{{ route('admin.optional-holiday-settings.destroy', $setting) }}"
                                      style="display:inline;" onsubmit="return confirm('Delete setting for {{ $setting->year }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger-ghost btn-sm">
                                        <span class="material-symbols-outlined" style="font-size:.95rem;">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        {{-- Inline edit row --}}
                        <tr class="edit-row" id="edit-{{ $setting->getRouteKey() }}">
                            <td colspan="6">
                                <form method="POST" action="{{ route('admin.optional-holiday-settings.update', $setting) }}">
                                    @csrf @method('PUT')
                                    <div class="inline-edit-grid">
                                        <div>
                                            <label class="form-label">Max Allowed</label>
                                            <input type="number" name="max_allowed" class="form-control"
                                                   value="{{ $setting->max_allowed }}" min="1" max="30">
                                        </div>
                                        <div>
                                            <label class="form-label">Description</label>
                                            <input type="text" name="description" class="form-control"
                                                   value="{{ $setting->description }}">
                                        </div>
                                        <div>
                                            <label class="form-label">Status</label>
                                            <div style="display:flex;align-items:center;gap:.5rem;height:2.5rem;">
                                                <label class="toggle-switch">
                                                    <input type="checkbox" name="status" value="1" {{ $setting->status ? 'checked' : '' }}>
                                                    <span class="toggle-slider"></span>
                                                </label>
                                                <span style="font-size:.82rem;color:var(--text-secondary);">Active</span>
                                            </div>
                                        </div>
                                        <div style="display:flex;gap:.5rem;">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <span class="material-symbols-outlined" style="font-size:.9rem;">save</span> Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('{{ $setting->getRouteKey() }}')">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    function toggleEdit(id) {
        const editRow = document.getElementById('edit-' + id);
        editRow.classList.toggle('show');
    }

    // Auto-hide flash after 4s
    setTimeout(() => {
        const f = document.getElementById('flash');
        if (f) f.style.transition = 'opacity .4s', f.style.opacity = '0', setTimeout(() => f.remove(), 400);
    }, 4000);
</script>
@endpush
