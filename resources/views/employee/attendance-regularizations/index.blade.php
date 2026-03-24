@extends('layouts.app')

@section('title', 'Attendance Regularization')

@push('styles')
<style>
    .stat-row { display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:768px){ .stat-row { grid-template-columns:1fr; } }
    .stat-box { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem; }
    .stat-num { font-size:1.8rem;font-weight:800;line-height:1;margin-bottom:.25rem; }
    .stat-lbl { font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em; }
    .filter-bar { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end; }
    .filter-bar label { font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.3rem; }
    .filter-bar select { padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);min-width:180px; }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.55rem 1rem;font-size:.84rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .btn-outline { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem .9rem;font-size:.84rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border-radius:999px;font-size:.72rem;font-weight:700; }
    .badge-gray { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-green { background:#f0fdf4;color:#15803d; }
    .badge-red { background:#fff1f2;color:#dc2626; }
    .req-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:.75rem; }
    @media(max-width:768px){ .req-card { flex-direction:column;align-items:flex-start; } }
    .req-meta { display:flex;flex-wrap:wrap;gap:1rem;align-items:center;font-size:.82rem;color:var(--text-secondary);margin-top:.35rem; }
    .req-title { font-size:.96rem;font-weight:800;color:var(--text-main); }
    .empty-state { background:var(--surface);border:1px dashed var(--border);border-radius:var(--radius-md);padding:2.5rem 1rem;text-align:center;color:var(--text-muted); }
</style>
@endpush

@section('content')
@php
    $colorMap = ['gray' => 'badge-gray', 'orange' => 'badge-orange', 'blue' => 'badge-blue', 'green' => 'badge-green', 'red' => 'badge-red'];
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Attendance Regularization</h1>
        <p class="page-subtitle" style="margin-bottom:0;">Request punch corrections with approval tracking and comments.</p>
    </div>
    <a href="{{ route($createRoute) }}" class="btn-primary">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        New Request
    </a>
</div>

<div class="stat-row">
    <div class="stat-box">
        <div class="stat-num" style="color:#d97706;">{{ $stats['pending'] }}</div>
        <div class="stat-lbl">Pending</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#15803d;">{{ $stats['approved'] }}</div>
        <div class="stat-lbl">Approved</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#dc2626;">{{ $stats['rejected'] }}</div>
        <div class="stat-lbl">Rejected</div>
    </div>
</div>

<form method="GET" action="{{ route($indexRoute) }}" class="filter-bar">
    <div>
        <label>Status</label>
        <select name="status">
            <option value="">All Statuses</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="pending_l1" {{ request('status') === 'pending_l1' ? 'selected' : '' }}>Pending L1</option>
            <option value="pending_l2" {{ request('status') === 'pending_l2' ? 'selected' : '' }}>Pending L2</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
    </div>
    <button type="submit" class="btn-primary"><span class="material-symbols-outlined" style="font-size:1rem;">filter_list</span>Filter</button>
    <a href="{{ route($indexRoute) }}" class="btn-outline">Clear</a>
</form>

@if($regularizations->count())
    @foreach($regularizations as $regularization)
        <div class="req-card">
            <div>
                <div class="req-title">{{ $regularization->type_label }} for {{ $regularization->date->format('d M Y') }}</div>
                <div class="req-meta">
                    <span>Requested: {{ $regularization->requested_times_label }}</span>
                    <span>Submitted: {{ $regularization->submitted_at?->format('d M Y, h:i A') ?? '-' }}</span>
                    <span>Comments: {{ $regularization->comments()->count() }}</span>
                </div>
                <div style="margin-top:.45rem;font-size:.83rem;color:var(--text-secondary);">{{ \Illuminate\Support\Str::limit($regularization->reason, 140) }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <span class="status-badge {{ $colorMap[$regularization->status_color] ?? 'badge-gray' }}">{{ $regularization->status_label }}</span>
                <a href="{{ route($showRoute, $regularization) }}" class="btn-outline">View</a>
            </div>
        </div>
    @endforeach

    @if($regularizations->hasPages())
        <div style="margin-top:1rem;">{{ $regularizations->links() }}</div>
    @endif
@else
    <div class="empty-state">
        <span class="material-symbols-outlined" style="font-size:2.8rem;display:block;margin-bottom:.75rem;">edit_calendar</span>
        <p>No attendance regularization requests found.</p>
    </div>
@endif
@endsection
