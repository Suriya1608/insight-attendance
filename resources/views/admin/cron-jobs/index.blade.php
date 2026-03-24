@extends('layouts.app')

@section('title', 'Cron Jobs')

@push('styles')
<style>
    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    /* ── Stat cards ── */
    .stat-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: .875rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width:900px) { .stat-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width:480px) { .stat-grid { grid-template-columns: 1fr; } }

    .stat-card-c {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 1.125rem 1.25rem;
        display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);
    }
    .stat-icon-c {
        width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }
    .stat-val-c  { font-size: 1.625rem; font-weight: 800; letter-spacing: -.03em; line-height: 1; }
    .stat-lbl-c  { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .2rem; }
    .ic-blue   { background:#eff6ff; color:#3b82f6; } .v-blue   { color:#3b82f6; }
    .ic-green  { background:#f0fdf4; color:#22c55e; } .v-green  { color:#22c55e; }
    .ic-teal   { background:#f0fdfa; color:#0d9488; } .v-teal   { color:#0d9488; }
    .ic-red    { background:#fff1f2; color:#ef4444; } .v-red    { color:#ef4444; }

    /* ── Alert banners ── */
    .alert-success, .alert-error {
        display: flex; align-items: center; gap: .5rem;
        padding: .75rem 1rem; border-radius: var(--radius-sm);
        font-size: .84rem; margin-bottom: 1.25rem;
    }
    .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
    .alert-error   { background:#fff1f2; border:1px solid #fecaca; color:#dc2626; }
    .alert-success .material-symbols-outlined,
    .alert-error   .material-symbols-outlined { font-size:1.1rem; flex-shrink:0; }

    /* ── Job cards grid ── */
    .jobs-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(460px, 1fr));
        gap: 1rem; margin-bottom: 2rem;
    }
    @media (max-width:560px) { .jobs-grid { grid-template-columns: 1fr; } }

    .job-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm);
        overflow: hidden; display: flex; flex-direction: column;
    }
    .job-card-head {
        padding: 1rem 1.25rem .875rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
    }
    .job-card-title {
        font-size: .9375rem; font-weight: 700; color: var(--text-main); margin: 0 0 .2rem;
    }
    .job-command {
        display: inline-flex; align-items: center; gap: .3rem;
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: .77rem; color: var(--text-muted); background: #f1f5f9;
        padding: .2rem .5rem; border-radius: 5px;
    }
    .job-command .material-symbols-outlined { font-size: .85rem; color: #94a3b8; }
    .job-desc { font-size: .82rem; color: var(--text-secondary); margin-top: .45rem; line-height: 1.55; }

    /* Status badge */
    .badge-success { display:inline-flex; align-items:center; gap:.25rem; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; padding:.2rem .6rem; border-radius:20px; font-size:.775rem; font-weight:700; }
    .badge-failed  { display:inline-flex; align-items:center; gap:.25rem; background:#fff1f2; color:#dc2626; border:1px solid #fecaca; padding:.2rem .6rem; border-radius:20px; font-size:.775rem; font-weight:700; }
    .badge-never   { display:inline-flex; align-items:center; gap:.25rem; background:#f8fafc; color:#94a3b8; border:1px solid #e2e8f0; padding:.2rem .6rem; border-radius:20px; font-size:.775rem; font-weight:700; }
    .badge-inactive{ display:inline-flex; align-items:center; gap:.25rem; background:#fef9c3; color:#a16207; border:1px solid #fde68a; padding:.2rem .6rem; border-radius:20px; font-size:.775rem; font-weight:700; }
    .badge-success .material-symbols-outlined,
    .badge-failed  .material-symbols-outlined,
    .badge-never   .material-symbols-outlined,
    .badge-inactive .material-symbols-outlined { font-size:.85rem; }

    .job-card-body { padding: .875rem 1.25rem; flex: 1; }

    .meta-row {
        display: flex; align-items: center; gap: .5rem;
        font-size: .82rem; color: var(--text-secondary);
        margin-bottom: .4rem;
    }
    .meta-row:last-child { margin-bottom: 0; }
    .meta-row .material-symbols-outlined { font-size: 1rem; color: var(--text-muted); flex-shrink: 0; }
    .meta-label { color: var(--text-muted); font-weight: 600; min-width: 85px; }
    .meta-val   { color: var(--text-main); }

    .job-card-footer {
        padding: .75rem 1.25rem;
        border-top: 1px solid var(--border);
        background: #fafbfd;
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    }

    /* Run Now button */
    .btn-run {
        display: inline-flex; align-items: center; gap: .375rem;
        height: 2.125rem; padding: 0 1rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        font-size: .84rem; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .btn-run:hover { background: var(--primary-hover); }
    .btn-run[disabled] { opacity: .5; cursor: not-allowed; }
    .btn-run .material-symbols-outlined { font-size: 1rem; }

    .btn-run-inactive {
        display: inline-flex; align-items: center; gap: .375rem;
        height: 2.125rem; padding: 0 1rem;
        background: #f1f5f9; color: #94a3b8;
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: .84rem; font-weight: 600; cursor: not-allowed;
    }
    .btn-run-inactive .material-symbols-outlined { font-size: 1rem; }

    .schedule-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #f3e8ff; color: #7c3aed;
        padding: .2rem .65rem; border-radius: 20px;
        font-size: .775rem; font-weight: 600;
    }
    .schedule-chip .material-symbols-outlined { font-size: .9rem; }

    /* ── Logs section ── */
    .section-title {
        font-size: 1rem; font-weight: 700; color: var(--text-main);
        display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem;
    }
    .section-title .material-symbols-outlined { font-size: 1.2rem; color: var(--primary); }

    .logs-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .logs-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b;
    }
    .logs-card-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: #fff; }
    .logs-filter {
        display: flex; align-items: center; gap: .5rem;
    }
    .logs-filter select {
        height: 1.875rem; border: 1px solid rgba(255,255,255,.2); border-radius: var(--radius-sm);
        font-size: .8rem; padding: 0 .6rem; color: #e2e8f0;
        background: rgba(255,255,255,.08); outline: none;
    }
    .logs-filter select option { background: #1e293b; }
    .btn-filter-sm {
        height: 1.875rem; padding: 0 .75rem; background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm); font-size: .8rem; font-weight: 600;
        cursor: pointer; transition: background .15s;
    }
    .btn-filter-sm:hover { background: var(--primary-hover); }

    .log-table { width: 100%; border-collapse: collapse; font-size: .835rem; }
    .log-table thead th {
        padding: .6rem 1rem; text-align: left;
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: var(--text-muted);
        border-bottom: 1px solid var(--border); background: #fafbfd; white-space: nowrap;
    }
    .log-table tbody tr { transition: background .1s; }
    .log-table tbody tr:hover { background: rgba(0,0,0,.025); }
    .log-table td { padding: .65rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .log-table tbody tr:last-child td { border-bottom: none; }

    .log-status-success { color:#15803d; font-weight:700; font-size:.8rem; }
    .log-status-failed  { color:#dc2626; font-weight:700; font-size:.8rem; }
    .log-status-running { color:#2563eb; font-weight:700; font-size:.8rem; }

    .trigger-manual    { background:#dbeafe; color:#1d4ed8; padding:.15rem .5rem; border-radius:4px; font-size:.75rem; font-weight:700; }
    .trigger-scheduled { background:#f3e8ff; color:#7c3aed; padding:.15rem .5rem; border-radius:4px; font-size:.75rem; font-weight:700; }

    .btn-view-log {
        display: inline-flex; align-items: center; gap: .2rem;
        height: 1.75rem; padding: 0 .6rem;
        background: transparent; color: var(--primary);
        border: 1.5px solid var(--primary); border-radius: var(--radius-xs);
        font-size: .78rem; font-weight: 600; text-decoration: none;
        cursor: pointer; transition: all .15s;
    }
    .btn-view-log:hover { background: var(--primary); color: #fff; }
    .btn-view-log .material-symbols-outlined { font-size: .85rem; }

    /* Pagination */
    .pagination-wrap { padding: .875rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }
    .pagination-wrap .pagination { margin: 0; }

    /* Running spinner */
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin 1s linear infinite; }

    /* Confirm run overlay */
    .confirm-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.45); backdrop-filter: blur(3px);
        z-index: 9000; align-items: center; justify-content: center;
    }
    .confirm-overlay.open { display: flex; }
    .confirm-box {
        background: var(--surface); border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg); padding: 2rem; max-width: 440px; width: 90%;
    }
    .confirm-box h4 { font-size: 1.125rem; font-weight: 700; margin: 0 0 .5rem; }
    .confirm-box p  { font-size: .875rem; color: var(--text-secondary); margin: 0 0 1.5rem; line-height: 1.6; }
    .confirm-actions { display: flex; gap: .75rem; justify-content: flex-end; }
    .btn-confirm-run {
        height: 2.25rem; padding: 0 1.25rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer; transition: background .15s;
    }
    .btn-confirm-run:hover { background: var(--primary-hover); }
    .btn-cancel-confirm {
        height: 2.25rem; padding: 0 1.25rem;
        background: var(--surface); color: var(--text-secondary);
        border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: .875rem; font-weight: 600; cursor: pointer;
    }
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="font-size:1.4rem; color:var(--primary); font-variation-settings:'FILL' 1;">schedule_send</span>
            Cron Jobs
        </h1>
        <p>Manage and monitor scheduled background jobs. Run jobs manually or view execution history.</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert-success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-error">
            <span class="material-symbols-outlined">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card-c">
            <div class="stat-icon-c ic-blue">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">schedule_send</span>
            </div>
            <div>
                <div class="stat-val-c v-blue">{{ $stats['total'] }}</div>
                <div class="stat-lbl-c">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card-c">
            <div class="stat-icon-c ic-teal">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">play_circle</span>
            </div>
            <div>
                <div class="stat-val-c v-teal">{{ $stats['active'] }}</div>
                <div class="stat-lbl-c">Active Jobs</div>
            </div>
        </div>
        <div class="stat-card-c">
            <div class="stat-icon-c ic-green">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            <div>
                <div class="stat-val-c v-green">{{ $stats['last_success'] }}</div>
                <div class="stat-lbl-c">Last Run OK</div>
            </div>
        </div>
        <div class="stat-card-c">
            <div class="stat-icon-c ic-red">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">cancel</span>
            </div>
            <div>
                <div class="stat-val-c v-red">{{ $stats['last_failed'] }}</div>
                <div class="stat-lbl-c">Last Run Failed</div>
            </div>
        </div>
    </div>

    {{-- Job Cards --}}
    <div class="section-title">
        <span class="material-symbols-outlined">task</span>
        Registered Jobs
    </div>

    <div class="jobs-grid">
        @foreach($jobs as $job)
            <div class="job-card">
                <div class="job-card-head">
                    <div style="flex:1; min-width:0;">
                        <h3 class="job-card-title">{{ $job->name }}</h3>
                        <div class="job-command">
                            <span class="material-symbols-outlined">terminal</span>
                            php artisan {{ $job->command }}
                        </div>
                        @if($job->description)
                            <p class="job-desc">{{ $job->description }}</p>
                        @endif
                    </div>
                    <div style="flex-shrink:0;">
                        @if(! $job->is_active)
                            <span class="badge-inactive">
                                <span class="material-symbols-outlined">pause_circle</span>
                                Inactive
                            </span>
                        @elseif($job->last_run_status === 'success')
                            <span class="badge-success">
                                <span class="material-symbols-outlined">check_circle</span>
                                Success
                            </span>
                        @elseif($job->last_run_status === 'failed')
                            <span class="badge-failed">
                                <span class="material-symbols-outlined">cancel</span>
                                Failed
                            </span>
                        @else
                            <span class="badge-never">
                                <span class="material-symbols-outlined">radio_button_unchecked</span>
                                Never Run
                            </span>
                        @endif
                    </div>
                </div>

                <div class="job-card-body">
                    <div class="meta-row">
                        <span class="material-symbols-outlined">event_repeat</span>
                        <span class="meta-label">Schedule</span>
                        <span class="meta-val">{{ $job->schedule_display }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="material-symbols-outlined">history</span>
                        <span class="meta-label">Last Run</span>
                        <span class="meta-val">
                            {{ $job->last_run_at ? $job->last_run_at->format('M d, Y H:i:s') : '—' }}
                            @if($job->last_run_at)
                                <span style="color:var(--text-muted); font-size:.78rem;">({{ $job->last_run_at->diffForHumans() }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="material-symbols-outlined">timer</span>
                        <span class="meta-label">Duration</span>
                        <span class="meta-val">{{ $job->formattedDuration() }}</span>
                    </div>
                </div>

                <div class="job-card-footer">
                    <span class="schedule-chip">
                        <span class="material-symbols-outlined">calendar_clock</span>
                        {{ $job->schedule_display }}
                    </span>
                    @if($job->is_active)
                        <button class="btn-run"
                                onclick="confirmRun('{{ $job->id }}', '{{ addslashes($job->name) }}', '{{ addslashes($job->command) }}')"
                                id="run-btn-{{ $job->id }}">
                            <span class="material-symbols-outlined">play_arrow</span>
                            Run Now
                        </button>
                    @else
                        <span class="btn-run-inactive">
                            <span class="material-symbols-outlined">block</span>
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Logs --}}
    <div class="section-title">
        <span class="material-symbols-outlined">article</span>
        Execution Logs
    </div>

    <div class="logs-card">
        <div class="logs-card-header">
            <h5>Recent Executions</h5>
            <form method="GET" action="{{ route('admin.cron-jobs.index') }}" class="logs-filter">
                <select name="job" onchange="this.form.submit()">
                    <option value="">All Jobs</option>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}" {{ request('job') == $j->id ? 'selected' : '' }}>{{ $j->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table class="log-table">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Job</th>
                        <th>Trigger</th>
                        <th>Triggered By</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Started At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                        <tr>
                            <td style="color:var(--text-muted); font-size:.8rem;">{{ $logs->firstItem() + $i }}</td>
                            <td style="font-weight:600; color:var(--text-main);">{{ $log->cronJob->name ?? '—' }}</td>
                            <td>
                                <span class="trigger-{{ $log->trigger_type }}">{{ ucfirst($log->trigger_type) }}</span>
                            </td>
                            <td style="font-size:.82rem; color:var(--text-secondary);">
                                {{ $log->triggeredBy?->name ?? ($log->trigger_type === 'scheduled' ? 'System' : '—') }}
                            </td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="log-status-success">✓ Success</span>
                                @elseif($log->status === 'failed')
                                    <span class="log-status-failed">✗ Failed</span>
                                @else
                                    <span class="log-status-running">
                                        <span class="spin">↻</span> Running
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:.82rem; color:var(--text-secondary);">{{ $log->formattedDuration() }}</td>
                            <td style="font-size:.82rem; color:var(--text-secondary); white-space:nowrap;">
                                {{ $log->started_at->format('M d, Y H:i:s') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.cron-jobs.log', $log) }}" class="btn-view-log">
                                    <span class="material-symbols-outlined">open_in_new</span>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:2.5rem; text-align:center; color:var(--text-muted);">
                                No execution logs yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="pagination-wrap">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Confirm Run Modal --}}
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-box">
            <h4>
                <span class="material-symbols-outlined" style="font-size:1.25rem; color:var(--primary); vertical-align:-3px;">play_circle</span>
                Run Job Manually?
            </h4>
            <p id="confirmMsg">Are you sure you want to run this job now?</p>
            <div class="confirm-actions">
                <button class="btn-cancel-confirm" onclick="closeConfirm()">Cancel</button>
                <form id="runForm" method="POST">
                    @csrf
                    <button type="submit" class="btn-confirm-run" id="confirmRunBtn">Run Now</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    let pendingJobId = null;

    function confirmRun(id, name, command) {
        pendingJobId = id;
        document.getElementById('confirmMsg').innerHTML =
            'Run <strong>' + name + '</strong>?<br>'
            + '<code style="font-size:.8rem; color:#64748b;">php artisan ' + command + '</code><br><br>'
            + '<span style="color:#dc2626; font-size:.82rem;">⚠ This will execute on live data.</span>';
        document.getElementById('runForm').action = '/admin/cron-jobs/' + id + '/run';
        document.getElementById('confirmOverlay').classList.add('open');
    }

    function closeConfirm() {
        document.getElementById('confirmOverlay').classList.remove('open');
        pendingJobId = null;
    }

    // Close on backdrop click
    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeConfirm();
    });

    // Show spinner on confirm
    document.getElementById('runForm').addEventListener('submit', function() {
        const btn = document.getElementById('confirmRunBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spin" style="display:inline-block; animation:spin 1s linear infinite;">↻</span> Running…';
    });
</script>
@endpush
