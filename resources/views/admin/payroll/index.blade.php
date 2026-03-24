@extends('layouts.app')

@section('title', 'Payroll')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .page-header h1 { font-size: 1.4rem; font-weight: 700; color: var(--text-main); margin: 0 0 .2rem; display: flex; align-items: center; gap: .4rem; }
    .page-header p  { font-size: .85rem; color: var(--text-secondary); margin: 0; }

    .btn-primary {
        height: 2.375rem; padding: 0 1.25rem;
        background: var(--primary); color: #fff; border: none;
        border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .35rem;
        text-decoration: none; cursor: pointer; transition: background .15s;
    }
    .btn-primary:hover { background: var(--primary-hover); color: #fff; }

    .card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }

    .tbl { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .tbl thead tr { background: #f8fafc; }
    .tbl th {
        padding: .7rem 1rem; text-align: left;
        font-size: .72rem; font-weight: 700; color: var(--text-secondary);
        text-transform: uppercase; letter-spacing: .05em;
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .tbl th.r { text-align: right; }
    .tbl td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .tbl td.r { text-align: right; }
    .tbl tr:last-child td { border-bottom: none; }
    .tbl tr:hover td { background: #f8fafc; }

    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: .2rem .65rem; border-radius: 999px;
        font-size: .72rem; font-weight: 700;
    }
    .badge-generated { background: #dbeafe; color: #1d4ed8; }
    .badge-locked    { background: #dcfce7; color: #15803d; }

    .month-label { font-weight: 700; font-size: .9rem; color: var(--text-main); }
    .year-label  { font-size: .75rem; color: var(--text-secondary); }
    .payout      { font-weight: 700; color: #16a34a; }
    .meta-text   { font-size: .75rem; color: var(--text-muted); margin-top: 2px; }

    .actions { display: flex; gap: .375rem; justify-content: flex-end; flex-wrap: wrap; }
    .btn-sm {
        height: 2rem; padding: 0 .75rem;
        border-radius: var(--radius-sm); font-size: .78rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .25rem;
        text-decoration: none; cursor: pointer; border: 1.5px solid; transition: all .15s;
    }
    .btn-sm .material-symbols-outlined { font-size: .9rem; }
    .btn-lock   { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
    .btn-lock:hover { background: #ffedd5; }
    .btn-view   { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .btn-view:hover { background: #dbeafe; }
    .btn-locked-icon { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; cursor: default; }

    .empty-state { padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); }
    .empty-state .material-symbols-outlined { font-size: 3rem; display: block; margin-bottom: .75rem; color: #cbd5e1; }
    .empty-state p { font-size: .875rem; margin: .5rem 0 1.25rem; }

    .info-row {
        display: flex; align-items: center; gap: .5rem;
        font-size: .8rem; color: var(--text-secondary);
        padding: .75rem 1rem; background: #fefce8;
        border-bottom: 1px solid #fef08a;
    }
    .info-row .material-symbols-outlined { font-size: 1rem; color: #ca8a04; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1>
            <span class="material-symbols-outlined" style="color:var(--primary);font-variation-settings:'FILL' 1;">payments</span>
            Payroll
        </h1>
        <p>Generate, review, lock, and manage monthly payroll batches.</p>
    </div>
    <a href="{{ route('admin.payroll.generate') }}" class="btn-primary">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        Generate Payroll
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    @if($batches->isEmpty())
        <div class="empty-state">
            <span class="material-symbols-outlined">receipt_long</span>
            <p>No payroll batches generated yet.</p>
            <a href="{{ route('admin.payroll.generate') }}" class="btn-primary">
                <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
                Generate First Payroll
            </a>
        </div>
    @else
        <div class="info-row">
            <span class="material-symbols-outlined">info</span>
            Only the latest unlocked batch can be edited per row. Locking a batch prevents all edits.
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;"><table class="tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th class="r">Employees</th>
                    <th class="r">Total Payout</th>
                    <th>Status</th>
                    <th>Generated By</th>
                    <th>Locked By</th>
                    <th class="r">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batches as $i => $batch)
                    <tr>
                        <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                        <td>
                            <div class="month-label">{{ \Carbon\Carbon::create($batch->year, $batch->month)->format('F') }}</div>
                            <div class="year-label">{{ $batch->year }}</div>
                        </td>
                        <td class="r">{{ $batch->total_employees }}</td>
                        <td class="r payout">₹{{ number_format($batch->total_payout, 2) }}</td>
                        <td>
                            @if($batch->isLocked())
                                <span class="badge badge-locked">
                                    <span class="material-symbols-outlined" style="font-size:.8rem;">lock</span>
                                    Locked
                                </span>
                            @else
                                <span class="badge badge-generated">
                                    <span class="material-symbols-outlined" style="font-size:.8rem;">pending</span>
                                    Generated
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:.84rem;font-weight:600;">{{ $batch->generatedBy?->name ?? '—' }}</div>
                            <div class="meta-text">{{ $batch->created_at->format('d M Y, h:i A') }}</div>
                        </td>
                        <td>
                            @if($batch->isLocked())
                                <div style="font-size:.84rem;font-weight:600;">{{ $batch->lockedBy?->name ?? '—' }}</div>
                                <div class="meta-text">{{ $batch->locked_at?->format('d M Y, h:i A') }}</div>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="r">
                            <div class="actions">
                                {{-- View employees in this batch --}}
                                <a href="{{ route('admin.payroll.generate', ['month' => $batch->month, 'year' => $batch->year, 'generate' => 1]) }}"
                                   class="btn-sm btn-view" title="Preview">
                                    <span class="material-symbols-outlined">visibility</span>
                                    Preview
                                </a>

                                @if($batch->isLocked())
                                    <span class="btn-sm btn-locked-icon" title="Locked">
                                        <span class="material-symbols-outlined">lock</span>
                                        Locked
                                    </span>
                                @else
                                    {{-- Edit rows: only for latest batch --}}
                                    @if($batch->id === $latestBatchId)
                                        <a href="{{ route('admin.payroll.generate', ['month' => $batch->month, 'year' => $batch->year, 'generate' => 1]) }}#rows"
                                           class="btn-sm btn-view" title="Edit rows">
                                            <span class="material-symbols-outlined">edit</span>
                                            Edit
                                        </a>
                                    @endif

                                    {{-- Lock --}}
                                    <form method="POST"
                                          action="{{ route('admin.payroll.lock', ['month' => $batch->month, 'year' => $batch->year]) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Lock payroll for {{ \Carbon\Carbon::create($batch->year, $batch->month)->format('F Y') }}? This cannot be undone.')">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-lock">
                                            <span class="material-symbols-outlined">lock</span>
                                            Lock
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    @endif
</div>

@endsection
