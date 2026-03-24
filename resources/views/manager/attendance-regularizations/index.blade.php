@extends('layouts.app')

@section('title', 'Team Regularization Requests')

@push('styles')
<style>
    .stat-row { display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem; }
    @media(max-width:768px){ .stat-row { grid-template-columns:1fr; } }
    .stat-box { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem; }
    .stat-num { font-size:1.8rem;font-weight:800;line-height:1;margin-bottom:.25rem; }
    .stat-lbl { font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em; }
    .filter-bar { background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end; }
    .filter-bar label { font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.3rem; }
    .filter-bar select,.filter-bar input { padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface); }
    .btn-primary { background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);padding:.55rem 1rem;font-size:.84rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .btn-outline { background:transparent;color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.5rem .9rem;font-size:.84rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem; }
    .status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border-radius:999px;font-size:.72rem;font-weight:700; }
    .badge-gray { background:#f1f5f9;color:#475569; }
    .badge-orange { background:#fff7ed;color:#c2410c; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-green { background:#f0fdf4;color:#15803d; }
    .badge-red { background:#fff1f2;color:#dc2626; }
    .req-table { width:100%;border-collapse:collapse;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden; }
    .req-table th { padding:.7rem 1rem;background:#f8fafc;border-bottom:2px solid var(--border);font-size:.74rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);text-align:left; }
    .req-table td { padding:.8rem 1rem;border-bottom:1px solid var(--border);font-size:.86rem; }
    .req-table tr:last-child td { border-bottom:none; }
    .pending-alert { background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius-md);padding:.8rem 1rem;margin-bottom:1rem;color:#9a3412;display:flex;align-items:center;gap:.6rem; }
</style>
@endpush

@section('content')
@php
    $colorMap = ['gray' => 'badge-gray', 'orange' => 'badge-orange', 'blue' => 'badge-blue', 'green' => 'badge-green', 'red' => 'badge-red'];
@endphp

<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Team Regularization Requests</h1>
    <p class="page-subtitle" style="margin-bottom:0;">Review attendance corrections submitted by your team.</p>
</div>

@if($pendingCount > 0)
    <div class="pending-alert">
        <span class="material-symbols-outlined">pending_actions</span>
        <span>You have <strong>{{ $pendingCount }}</strong> request(s) awaiting action.</span>
    </div>
@endif

<div class="stat-row">
    <div class="stat-box"><div class="stat-num" style="color:#d97706;">{{ $stats['pending'] }}</div><div class="stat-lbl">Pending</div></div>
    <div class="stat-box"><div class="stat-num" style="color:#15803d;">{{ $stats['approved'] }}</div><div class="stat-lbl">Approved</div></div>
    <div class="stat-box"><div class="stat-num" style="color:#dc2626;">{{ $stats['rejected'] }}</div><div class="stat-lbl">Rejected</div></div>
</div>

<form method="GET" action="{{ route('manager.regularizations.index') }}" class="filter-bar">
    <div>
        <label>From</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}">
    </div>
    <div>
        <label>To</label>
        <input type="date" name="date_to" value="{{ $dateTo }}">
    </div>
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
    <div>
        <label>Employee</label>
        <select name="employee_id">
            <option value="">All Employees</option>
            @foreach($teamMembers as $member)
                <option value="{{ $member->id }}" {{ request('employee_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-primary"><span class="material-symbols-outlined" style="font-size:1rem;">filter_list</span>Filter</button>
    <a href="{{ route('manager.regularizations.index') }}" class="btn-outline">Clear</a>
</form>

<div style="overflow-x:auto;">
    <table class="req-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Type</th>
                <th>Requested Times</th>
                <th>Status</th>
                <th>Submitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($regularizations as $regularization)
                <tr>
                    <td>
                        <div style="font-weight:800;">{{ $regularization->user->name }}</div>
                        <div style="font-size:.76rem;color:var(--text-muted);">{{ $regularization->user->employee_code }}</div>
                    </td>
                    <td>{{ $regularization->date->format('d M Y') }}</td>
                    <td>{{ $regularization->type_label }}</td>
                    <td>{{ $regularization->requested_times_label }}</td>
                    <td><span class="status-badge {{ $colorMap[$regularization->status_color] ?? 'badge-gray' }}">{{ $regularization->status_label }}</span></td>
                    <td>{{ $regularization->submitted_at?->format('d M Y, h:i A') ?? '-' }}</td>
                    <td><a href="{{ route('manager.regularizations.show', $regularization) }}" class="btn-outline">View</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem 1rem;">No regularization requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($regularizations->hasPages())
    <div style="margin-top:1rem;">{{ $regularizations->links() }}</div>
@endif
@endsection
