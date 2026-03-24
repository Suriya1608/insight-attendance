@extends('layouts.app')

@section('title', 'Email History — ' . $offerLetter->name)

@push('styles')
<style>
    /* ── Page header ── */
    .eh-header { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.5rem; }
    .eh-back {
        height: 2.25rem; padding: 0 .9rem;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-muted);
        font-size: .8125rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .35rem;
        text-decoration: none; transition: all .15s;
    }
    .eh-back:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .eh-back .material-symbols-outlined { font-size: 1rem; }
    .eh-title-wrap { flex: 1; }
    .eh-title { font-size: 1.4rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.2; }
    .eh-sub   { font-size: .8125rem; color: var(--text-muted); margin: .15rem 0 0; }

    /* ── Candidate card ── */
    .cand-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.1rem 1.5rem;
        display: flex; align-items: center; gap: 1.25rem;
        margin-bottom: 1.5rem; flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .cand-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        background: rgba(19,127,236,.12);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .cand-avatar .material-symbols-outlined { color: var(--primary); font-size: 1.35rem; }
    .cand-name  { font-size: 1rem; font-weight: 700; color: var(--text-main); }
    .cand-meta  { font-size: .8125rem; color: var(--text-muted); margin-top: .1rem; }
    .cand-pills { margin-left: auto; display: flex; gap: .5rem; flex-wrap: wrap; }
    .cand-pill  {
        font-size: .75rem; font-weight: 600;
        padding: .25rem .75rem; border-radius: 999px;
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8;
    }

    /* ── Log table card ── */
    .log-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .log-card-header {
        background: #1e2a3b; padding: .9rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between;
    }
    .log-card-title { font-size: .9375rem; font-weight: 700; color: #fff; }
    .log-count-pill {
        font-size: .75rem; font-weight: 600;
        background: #fff; color: #1e2a3b;
        border-radius: 999px; padding: .2rem .7rem;
    }

    .log-table { width: 100%; border-collapse: collapse; }
    .log-table thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
    .log-table th {
        padding: .7rem 1.25rem;
        font-size: .6875rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--text-muted); text-align: left; white-space: nowrap;
    }
    .log-table td {
        padding: .85rem 1.25rem;
        font-size: .875rem; color: var(--text-main);
        border-bottom: 1px solid var(--border); vertical-align: top;
    }
    .log-table tbody tr:last-child td { border-bottom: none; }
    .log-table tbody tr:hover { background: #fafbfd; }

    .status-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .25rem .7rem; border-radius: 999px;
        font-size: .75rem; font-weight: 700; white-space: nowrap;
    }
    .badge-sent   { background: #dcfce7; color: #15803d; }
    .badge-failed { background: #fee2e2; color: #dc2626; }
    .status-badge .material-symbols-outlined { font-size: .8rem; }

    .err-msg {
        font-size: .775rem; color: #dc2626;
        margin-top: .3rem; line-height: 1.5;
        word-break: break-all;
    }

    .date-val  { font-size: .85rem; color: var(--text-main); }
    .date-sub  { font-size: .75rem; color: var(--text-muted); margin-top: .1rem; }

    /* ── Action bar ── */
    .action-bar {
        padding: 1rem 1.5rem; border-top: 1px solid var(--border);
        background: #f8fafc; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    }
    .btn-send-now {
        height: 2.25rem; padding: 0 1.1rem;
        background: #7c3aed; border: none; border-radius: var(--radius-sm);
        color: #fff; font-size: .8125rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .35rem;
        cursor: pointer; transition: background .15s; white-space: nowrap;
    }
    .btn-send-now:hover { background: #6d28d9; }
    .btn-send-now .material-symbols-outlined { font-size: 1rem; }

    /* ── Empty state ── */
    .empty-state { text-align: center; padding: 3rem 1rem; }
    .empty-state .material-symbols-outlined { font-size: 2.75rem; color: #cbd5e1; display: block; margin-bottom: .65rem; }
    .empty-state p { font-size: .9rem; color: var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="eh-header">
    <a href="{{ route('admin.offer-letters.index') }}" class="eh-back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
    </a>
    <div class="eh-title-wrap">
        <div class="eh-title">Email History</div>
        <div class="eh-sub">All email deliveries for this offer letter</div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div style="display:flex;align-items:center;gap:.5rem;background:rgba(22,163,74,.07);border:1px solid rgba(22,163,74,.25);border-left:3px solid #22c55e;border-radius:6px;padding:.75rem 1rem;font-size:.875rem;color:#15803d;margin-bottom:1.25rem;">
        <span class="material-symbols-outlined" style="font-size:1.1rem">check_circle</span>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="display:flex;align-items:center;gap:.5rem;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.25);border-left:3px solid #ef4444;border-radius:6px;padding:.75rem 1rem;font-size:.875rem;color:#dc2626;margin-bottom:1.25rem;">
        <span class="material-symbols-outlined" style="font-size:1.1rem">error</span>
        {{ session('error') }}
    </div>
@endif

{{-- Candidate summary card --}}
<div class="cand-card">
    <div class="cand-avatar">
        <span class="material-symbols-outlined">person</span>
    </div>
    <div>
        <div class="cand-name">{{ $offerLetter->name }}</div>
        <div class="cand-meta">
            {{ $offerLetter->designation }}
            @if($offerLetter->email) &bull; {{ $offerLetter->email }} @endif
        </div>
    </div>
    <div class="cand-pills">
        <span class="cand-pill">Offer: {{ $offerLetter->offer_date->format('d M Y') }}</span>
        <span class="cand-pill">Join: {{ $offerLetter->joining_date->format('d M Y') }}</span>
        <span class="cand-pill">{{ $logs->count() }} email{{ $logs->count() === 1 ? '' : 's' }} sent</span>
    </div>
</div>

{{-- Log table --}}
<div class="log-card">
    <div class="log-card-header">
        <span class="log-card-title">Email Delivery Log</span>
        <span class="log-count-pill">{{ $logs->count() }} records</span>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <span class="material-symbols-outlined">mail</span>
            <p>No emails sent yet for this offer letter.</p>
        </div>
    @else
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;"><table class="log-table">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>Recipient</th>
                    <th style="width:130px">Status</th>
                    <th style="width:190px">Sent At</th>
                    <th>Error Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $i => $log)
                <tr>
                    <td style="color:var(--text-muted);font-weight:600;font-size:.8125rem">{{ $i + 1 }}</td>
                    <td style="font-weight:600">{{ $log->email }}</td>
                    <td>
                        @if($log->status === 'sent')
                            <span class="status-badge badge-sent">
                                <span class="material-symbols-outlined">check_circle</span>
                                Sent
                            </span>
                        @else
                            <span class="status-badge badge-failed">
                                <span class="material-symbols-outlined">cancel</span>
                                Failed
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($log->sent_at)
                            <div class="date-val">{{ $log->sent_at->format('d M Y') }}</div>
                            <div class="date-sub">{{ $log->sent_at->format('h:i A') }}</div>
                        @else
                            <span style="color:var(--text-muted);font-size:.8125rem">—</span>
                        @endif
                    </td>
                    <td>
                        @if($log->error_message)
                            <div class="err-msg">{{ $log->error_message }}</div>
                        @else
                            <span style="color:var(--text-muted);font-size:.8125rem">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    @endif

    {{-- Action bar --}}
    @if($offerLetter->email)
    <div class="action-bar">
        @php $lastSent = $logs->where('status','sent')->first(); @endphp
        @if($lastSent)
            <form method="POST"
                  action="{{ route('admin.offer-letters.resend-email', $offerLetter) }}"
                  onsubmit="return confirm('Resend offer letter to {{ $offerLetter->email }}?')">
                @csrf
                <button type="submit" class="btn-send-now">
                    <span class="material-symbols-outlined">forward_to_inbox</span>
                    Resend to {{ $offerLetter->email }}
                </button>
            </form>
        @else
            <form method="POST"
                  action="{{ route('admin.offer-letters.send-email', $offerLetter) }}"
                  onsubmit="return confirm('Send offer letter to {{ $offerLetter->email }}?')">
                @csrf
                <button type="submit" class="btn-send-now">
                    <span class="material-symbols-outlined">send</span>
                    Send to {{ $offerLetter->email }}
                </button>
            </form>
        @endif

        <a href="{{ route('admin.offer-letters.pdf', $offerLetter) }}"
           target="_blank"
           style="height:2.25rem;padding:0 1rem;background:#16a34a;border-radius:6px;color:#fff;font-size:.8125rem;font-weight:600;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;">
            <span class="material-symbols-outlined" style="font-size:1rem">picture_as_pdf</span>
            Preview PDF
        </a>
    </div>
    @endif
</div>

@endsection
