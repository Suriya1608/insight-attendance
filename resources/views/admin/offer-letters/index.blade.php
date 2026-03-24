@extends('layouts.app')

@section('title', 'Offer Letters')

@push('styles')
<style>
    /* ── Page header ── */
    .ol-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .ol-header-left { display: flex; align-items: center; gap: .75rem; }
    .ol-header-icon {
        width: 40px; height: 40px;
        background: rgba(19,127,236,.1);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ol-header-icon .material-symbols-outlined { color: var(--primary); font-size: 1.25rem; }
    .ol-header-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1.2; }
    .ol-header-sub   { font-size: .8125rem; color: var(--text-muted); margin: .15rem 0 0; }

    .btn-add-ol {
        height: 2.5rem;
        padding: 0 1.25rem;
        background: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        color: #fff;
        font-size: .875rem;
        font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
        transition: background .15s, box-shadow .15s;
    }
    .btn-add-ol:hover { background: var(--primary-hover); color: #fff; box-shadow: 0 4px 14px rgba(19,127,236,.4); }
    .btn-add-ol .material-symbols-outlined { font-size: 1.125rem; }

    /* ── Table card ── */
    .ol-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .ol-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: #1e2a3b;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .ol-card-header-title { font-size: .9375rem; font-weight: 700; color: #fff; }
    .ol-count-pill {
        font-size: .75rem; font-weight: 600;
        color: #1e2a3b; background: #fff;
        border-radius: 999px; padding: .2rem .75rem; white-space: nowrap;
    }

    /* ── Table ── */
    .ol-table { width: 100%; border-collapse: collapse; }
    .ol-table thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
    .ol-table th {
        padding: .75rem 1.25rem;
        font-size: .6875rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--text-muted); text-align: left; white-space: nowrap;
    }
    .ol-table td {
        padding: .9rem 1.25rem;
        font-size: .875rem; color: var(--text-main);
        border-bottom: 1px solid var(--border); vertical-align: middle;
    }
    .ol-table tbody tr:last-child td { border-bottom: none; }
    .ol-table tbody tr:hover { background: #fafbfd; }

    .row-num { font-size: .8125rem; color: var(--text-muted); font-weight: 600; }

    .candidate-name { font-weight: 700; color: var(--text-main); font-size: .9rem; }
    .candidate-email { font-size: .775rem; color: var(--text-muted); margin-top: .1rem; }

    .desig-pill {
        display: inline-flex; align-items: center;
        padding: .25rem .75rem;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 999px;
        font-size: .75rem; font-weight: 600; color: #1d4ed8;
        white-space: nowrap;
    }

    .ctc-val { font-weight: 700; color: #15803d; font-size: .875rem; }
    .date-val { font-size: .85rem; color: var(--text-main); }

    /* Action buttons */
    .action-group { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }

    .btn-action {
        height: 2rem; padding: 0 .7rem;
        border: none; border-radius: 6px; color: #fff;
        font-size: .75rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .25rem;
        text-decoration: none; cursor: pointer;
        transition: background .15s, opacity .15s; white-space: nowrap;
    }
    .btn-action .material-symbols-outlined { font-size: .85rem; }
    .btn-pdf    { background: #16a34a; }  .btn-pdf:hover    { background: #15803d; color:#fff; }
    .btn-edit   { background: var(--primary); } .btn-edit:hover   { background: var(--primary-hover); color:#fff; }
    .btn-email  { background: #7c3aed; }  .btn-email:hover  { background: #6d28d9; color:#fff; }
    .btn-resend { background: #ea580c; }  .btn-resend:hover { background: #c2410c; color:#fff; }
    .btn-hist   { background: #0891b2; }  .btn-hist:hover   { background: #0e7490; color:#fff; }

    .btn-del {
        width: 32px; height: 32px; border-radius: 6px;
        border: 1.5px solid #fca5a5; background: #fff5f5; color: #ef4444;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .15s; flex-shrink: 0;
    }
    .btn-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
    .btn-del .material-symbols-outlined { font-size: .9rem; }

    /* Email status badge */
    .email-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .15rem .55rem; border-radius: 999px;
        font-size: .7rem; font-weight: 700; white-space: nowrap;
    }
    .badge-sent   { background: #dcfce7; color: #15803d; }
    .badge-failed { background: #fee2e2; color: #dc2626; }
    .badge-none   { background: #f1f5f9; color: #94a3b8; }

    /* Flash */
    .flash-success {
        display: flex; align-items: center; gap: .5rem;
        background: rgba(22,163,74,.07); border: 1px solid rgba(22,163,74,.25);
        border-left: 3px solid #22c55e; border-radius: var(--radius-sm);
        padding: .75rem 1rem; font-size: .875rem; color: #15803d; margin-bottom: 1.25rem;
    }
    .flash-success .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }
    .flash-error {
        display: flex; align-items: center; gap: .5rem;
        background: rgba(239,68,68,.07); border: 1px solid rgba(239,68,68,.25);
        border-left: 3px solid #ef4444; border-radius: var(--radius-sm);
        padding: .75rem 1rem; font-size: .875rem; color: #dc2626; margin-bottom: 1.25rem;
    }
    .flash-error .material-symbols-outlined { font-size: 1.1rem; flex-shrink: 0; }

    /* Empty state */
    .empty-state { text-align: center; padding: 3.5rem 1rem; }
    .empty-state .material-symbols-outlined { font-size: 3rem; color: #cbd5e1; margin-bottom: .75rem; display: block; }
    .empty-state p { font-size: .9rem; color: var(--text-secondary); margin: 0; }
    .empty-state a { color: var(--primary); font-weight: 600; text-decoration: none; }

    /* Pagination */
    .pagination-wrap { padding: 1rem 1.5rem; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="ol-header">
        <div class="ol-header-left">
            <div class="ol-header-icon">
                <span class="material-symbols-outlined">description</span>
            </div>
            <div>
                <div class="ol-header-title">Offer Letters</div>
                <div class="ol-header-sub">Create and manage candidate offer letters</div>
            </div>
        </div>
        <a href="{{ route('admin.offer-letters.create') }}" class="btn-add-ol">
            <span class="material-symbols-outlined">add</span>
            New Offer Letter
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-error">
            <span class="material-symbols-outlined">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="ol-card">
        <div class="ol-card-header">
            <span class="ol-card-header-title">All Offer Letters</span>
            <span class="ol-count-pill">{{ $offerLetters->total() }} letters</span>
        </div>

        @if($offerLetters->isEmpty())
            <div class="empty-state">
                <span class="material-symbols-outlined">description</span>
                <p>No offer letters yet. <a href="{{ route('admin.offer-letters.create') }}">Create the first one.</a></p>
            </div>
        @else
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;"><table class="ol-table">
                <thead>
                    <tr>
                        <th style="width:48px">#</th>
                        <th>Candidate</th>
                        <th>Designation</th>
                        <th style="width:130px">CTC (Annual)</th>
                        <th style="width:110px">Offer Date</th>
                        <th style="width:110px">Joining Date</th>
                        <th style="width:100px">Email Status</th>
                        <th style="width:260px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offerLetters as $i => $letter)
                    <tr>
                        <td><span class="row-num">{{ $offerLetters->firstItem() + $i }}</span></td>
                        <td>
                            <div class="candidate-name">{{ $letter->name }}</div>
                            @if($letter->email)
                                <div class="candidate-email">{{ $letter->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="desig-pill">{{ $letter->designation }}</span>
                        </td>
                        <td>
                            <span class="ctc-val">₹{{ number_format($letter->ctc, 0) }}</span>
                        </td>
                        <td>
                            <span class="date-val">{{ $letter->offer_date->format('d M Y') }}</span>
                        </td>
                        <td>
                            <span class="date-val">{{ $letter->joining_date->format('d M Y') }}</span>
                        </td>
                        <td>
                            @php $lastEmail = $letter->emailLogs->first(); @endphp
                            @if($lastEmail)
                                @if($lastEmail->status === 'sent')
                                    <span class="email-badge badge-sent">
                                        <span class="material-symbols-outlined" style="font-size:.75rem">mark_email_read</span>
                                        Sent
                                    </span>
                                @else
                                    <span class="email-badge badge-failed">
                                        <span class="material-symbols-outlined" style="font-size:.75rem">error</span>
                                        Failed
                                    </span>
                                @endif
                            @else
                                <span class="email-badge badge-none">Not sent</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                {{-- PDF --}}
                                <a href="{{ route('admin.offer-letters.pdf', $letter) }}"
                                   class="btn-action btn-pdf" target="_blank" title="Download PDF">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    PDF
                                </a>

                                {{-- Send / Resend email --}}
                                @if($letter->email)
                                    @if($lastEmail && $lastEmail->status === 'sent')
                                        <form method="POST"
                                              action="{{ route('admin.offer-letters.resend-email', $letter) }}"
                                              style="display:contents;"
                                              onsubmit="return confirm('Resend offer letter to {{ $letter->email }}?')">
                                            @csrf
                                            <button type="submit" class="btn-action btn-resend" title="Resend Email">
                                                <span class="material-symbols-outlined">forward_to_inbox</span>
                                                Resend
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.offer-letters.send-email', $letter) }}"
                                              style="display:contents;"
                                              onsubmit="return confirm('Send offer letter to {{ $letter->email }}?')">
                                            @csrf
                                            <button type="submit" class="btn-action btn-email" title="Send Email">
                                                <span class="material-symbols-outlined">send</span>
                                                Email
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                {{-- Email history --}}
                                @if($letter->email_logs_count > 0)
                                    <a href="{{ route('admin.offer-letters.email-history', $letter) }}"
                                       class="btn-action btn-hist" title="Email History">
                                        <span class="material-symbols-outlined">history</span>
                                    </a>
                                @endif

                                {{-- Edit --}}
                                <a href="{{ route('admin.offer-letters.edit', $letter) }}"
                                   class="btn-action btn-edit" title="Edit">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>

                                {{-- Delete --}}
                                <form method="POST"
                                      action="{{ route('admin.offer-letters.destroy', $letter) }}"
                                      style="display:contents;"
                                      onsubmit="return confirm('Delete offer letter for {{ $letter->name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-del" title="Delete">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>

            @if($offerLetters->hasPages())
                <div class="pagination-wrap">
                    {{ $offerLetters->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
