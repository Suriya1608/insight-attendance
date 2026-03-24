@extends('layouts.app')

@section('title', 'Leave Management')

@push('styles')
<style>
    .page-title   { font-size:1.25rem; font-weight:700; color:var(--text-main); margin:0 0 .25rem; }
    .page-subtitle{ font-size:.8375rem; color:var(--text-secondary); margin:0 0 1.5rem; }

    /* Month/year pill */
    .period-pill {
        display:inline-flex; align-items:center; gap:.375rem;
        background:var(--primary-subtle); color:var(--primary);
        border-radius:20px; padding:.2rem .75rem; font-size:.78rem; font-weight:600;
        margin-bottom:1.25rem;
    }

    /* Table card */
    .leave-card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius-md); box-shadow:var(--shadow-sm); overflow:hidden;
    }
    .leave-card-header {
        display:flex; align-items:center; justify-content:space-between;
        padding:.875rem 1.25rem; border-bottom:1px solid var(--border); background:#fafbfd;
        flex-wrap:wrap; gap:.5rem;
    }
    .leave-card-header h5 { font-size:.9rem; font-weight:700; margin:0; }

    /* Search */
    .search-wrap { position:relative; }
    .search-wrap input {
        height:2.25rem; padding:0 .75rem 0 2.1rem;
        border:1.5px solid var(--border); border-radius:var(--radius-sm);
        font-size:.8375rem; width:220px; background:#fff;
        transition:border-color .2s;
    }
    .search-wrap input:focus { border-color:var(--primary); outline:none; }
    .search-wrap .material-symbols-outlined {
        position:absolute; left:.5rem; top:50%; transform:translateY(-50%);
        font-size:1rem; color:var(--text-muted); pointer-events:none;
    }

    /* Table */
    .leave-table { width:100%; border-collapse:collapse; font-size:.8375rem; }
    .leave-table thead th {
        padding:.625rem .875rem; text-align:left;
        font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
        color:var(--text-muted); border-bottom:1px solid var(--border);
        white-space:nowrap; background:#fafbfd;
    }
    .leave-table thead th.num { text-align:center; }
    .leave-table tbody tr:hover { background:rgba(0,0,0,.02); }
    .leave-table td {
        padding:.625rem .875rem; border-bottom:1px solid var(--border);
        vertical-align:middle;
    }
    .leave-table tbody tr:last-child td { border-bottom:none; }
    .leave-table td.num { text-align:center; }

    /* Employee info cell */
    .emp-avatar {
        width:30px; height:30px; border-radius:50%;
        background:var(--primary); color:#fff;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.75rem; font-weight:700; flex-shrink:0;
    }
    .emp-name  { font-weight:600; color:var(--text-main); font-size:.84rem; }
    .emp-meta  { font-size:.75rem; color:var(--text-muted); }
    .role-pill {
        display:inline-block; padding:.1rem .5rem;
        border-radius:20px; font-size:.72rem; font-weight:600;
        background:#e0f2fe; color:#0369a1;
    }
    .role-pill.manager { background:#ede9fe; color:#6d28d9; }

    /* Balance pills */
    .bal-row { display:flex; gap:.375rem; justify-content:center; flex-wrap:wrap; }
    .bal-chip {
        display:inline-flex; flex-direction:column; align-items:center;
        padding:.2rem .5rem; border-radius:6px; min-width:42px;
        font-size:.72rem; font-weight:700; line-height:1.2;
    }
    .bal-chip .lbl { font-size:.62rem; font-weight:500; opacity:.75; }
    .chip-cr  { background:#dbeafe; color:#1d4ed8; }
    .chip-us  { background:#fee2e2; color:#dc2626; }
    .chip-bal { background:#dcfce7; color:#16a34a; }
    .chip-na  { background:#f1f5f9; color:#94a3b8; }

    /* Actions */
    .btn-view {
        display:inline-flex; align-items:center; gap:.25rem;
        height:2rem; padding:0 .75rem;
        background:var(--primary); color:#fff; border:none;
        border-radius:var(--radius-sm); font-size:.8rem; font-weight:600;
        text-decoration:none; cursor:pointer; transition:background .15s;
    }
    .btn-view:hover { background:var(--primary-hover); color:#fff; }
    .btn-view .material-symbols-outlined { font-size:.95rem; }

    /* Empty */
    .empty-row td { padding:2.5rem; text-align:center; color:var(--text-muted); }
</style>
@endpush

@section('content')

    <div class="page-title">Leave Management</div>
    <p class="page-subtitle">View and manage leave balances for all active employees and managers.</p>

    <div class="period-pill">
        <span class="material-symbols-outlined" style="font-size:.9rem;">calendar_month</span>
        {{ now()->format('F Y') }} &mdash; {{ $year }}
    </div>

    @if(session('success'))
        <div style="padding:.75rem 1rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm);
                    color:#15803d; font-size:.84rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem;">
            <span class="material-symbols-outlined" style="font-size:1.1rem;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="leave-card">
        <div class="leave-card-header">
            <h5>
                <span class="material-symbols-outlined" style="font-size:1rem; vertical-align:-2px; color:var(--primary);">event_note</span>
                Active Employees &amp; Managers
            </h5>
            <div class="search-wrap">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="empSearch" placeholder="Search name / dept…" oninput="filterTable()">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="leave-table" id="leaveTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th class="num" colspan="3" style="background:#eff6ff;">CL ({{ $year }})</th>
                        <th class="num" colspan="3" style="background:#f5f3ff;">Permissions ({{ now()->format('M') }})</th>
                        <th class="num" colspan="3" style="background:#fffbeb;">Sat. Leave ({{ now()->format('M') }})</th>
                        <th></th>
                    </tr>
                    <tr>
                        <th></th><th></th>
                        <th class="num" style="background:#eff6ff; color:#1d4ed8;">Cr</th>
                        <th class="num" style="background:#eff6ff; color:#dc2626;">Used</th>
                        <th class="num" style="background:#eff6ff; color:#16a34a;">Bal</th>
                        <th class="num" style="background:#f5f3ff; color:#1d4ed8;">Cr</th>
                        <th class="num" style="background:#f5f3ff; color:#dc2626;">Used</th>
                        <th class="num" style="background:#f5f3ff; color:#16a34a;">Bal</th>
                        <th class="num" style="background:#fffbeb; color:#1d4ed8;">Cr</th>
                        <th class="num" style="background:#fffbeb; color:#dc2626;">Used</th>
                        <th class="num" style="background:#fffbeb; color:#16a34a;">Bal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php
                            $cl   = $clBalances[$emp->id]   ?? null;
                            $perm = $permBalances[$emp->id] ?? null;
                            $sat  = $satBalances[$emp->id]  ?? null;

                            $clCr   = $cl   ? number_format($cl->credited, 0)   : '—';
                            $clUs   = $cl   ? number_format($cl->used, 0)       : '—';
                            $clBal  = $cl   ? number_format(max(0, $cl->credited - $cl->used - $cl->lapsed), 0) : '—';

                            $pCr    = $perm ? number_format($perm->credited, 0) : '—';
                            $pUs    = $perm ? number_format($perm->used, 0)     : '—';
                            $pBal   = $perm ? number_format(max(0, $perm->credited - $perm->used - $perm->lapsed), 0) : '—';

                            $sCr    = $sat  ? number_format($sat->credited, 0)  : '—';
                            $sUs    = $sat  ? number_format($sat->used, 0)      : '—';
                            $sBal   = $sat  ? number_format(max(0, $sat->credited - $sat->used - $sat->lapsed), 0)  : '—';
                        @endphp
                        <tr class="emp-row"
                            data-name="{{ strtolower($emp->name) }}"
                            data-dept="{{ strtolower($emp->department->name ?? '') }}">
                            <td>
                                <div style="display:flex; align-items:center; gap:.625rem;">
                                    <div class="emp-avatar">{{ $emp->initials() }}</div>
                                    <div>
                                        <div class="emp-name">{{ $emp->name }}</div>
                                        <div class="emp-meta">{{ $emp->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:.82rem; color:var(--text-secondary);">{{ $emp->department->name ?? '—' }}</div>
                                <span class="role-pill {{ $emp->role }}">{{ ucfirst($emp->role) }}</span>
                            </td>

                            {{-- CL --}}
                            <td class="num" style="background:#eff6ff22;">{{ $clCr }}</td>
                            <td class="num" style="background:#eff6ff22;">{{ $clUs }}</td>
                            <td class="num" style="background:#eff6ff22; font-weight:700; color:{{ $cl ? '#16a34a' : 'var(--text-muted)' }};">{{ $clBal }}</td>

                            {{-- Permissions --}}
                            <td class="num" style="background:#f5f3ff22;">{{ $pCr }}</td>
                            <td class="num" style="background:#f5f3ff22;">{{ $pUs }}</td>
                            <td class="num" style="background:#f5f3ff22; font-weight:700; color:{{ $perm ? '#16a34a' : 'var(--text-muted)' }};">{{ $pBal }}</td>

                            {{-- Saturday --}}
                            <td class="num" style="background:#fffbeb22;">{{ $sCr }}</td>
                            <td class="num" style="background:#fffbeb22;">{{ $sUs }}</td>
                            <td class="num" style="background:#fffbeb22; font-weight:700; color:{{ $sat ? '#16a34a' : 'var(--text-muted)' }};">{{ $sBal }}</td>

                            <td>
                                <a href="{{ route('admin.leave.show', $emp) }}" class="btn-view">
                                    <span class="material-symbols-outlined">open_in_new</span>
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="13">No active employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function filterTable() {
    const q    = document.getElementById('empSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#leaveTable .emp-row');
    rows.forEach(r => {
        const match = r.dataset.name.includes(q) || r.dataset.dept.includes(q);
        r.style.display = match ? '' : 'none';
    });
}
</script>
@endpush
