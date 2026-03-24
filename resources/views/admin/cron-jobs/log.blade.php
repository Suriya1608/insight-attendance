@extends('layouts.app')

@section('title', 'Execution Log')

@push('styles')
<style>
    .back-link {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .84rem; color: var(--text-secondary); text-decoration: none;
        margin-bottom: 1.25rem; transition: color .15s;
    }
    .back-link:hover { color: var(--primary); }
    .back-link .material-symbols-outlined { font-size: 1rem; }

    .page-header { margin-bottom: 1.5rem; }
    .page-header h1 {
        display: flex; align-items: center; gap: .5rem;
        font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin: 0 0 .25rem;
    }
    .page-header p { font-size: .875rem; color: var(--text-secondary); margin: 0; }

    .log-grid { display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start; }
    @media (max-width: 860px) { .log-grid { grid-template-columns: 1fr; } }

    .log-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .log-card-header {
        padding: .875rem 1.25rem; border-bottom: 1px solid var(--border);
        background: #1e293b; display: flex; align-items: center; justify-content: space-between;
    }
    .log-card-header h5 { font-size: .9rem; font-weight: 700; margin: 0; color: #fff; }
    .log-card-body { padding: 0; }

    /* Terminal output */
    .terminal-wrap {
        background: #0f172a; border-radius: 0 0 var(--radius-md) var(--radius-md);
        overflow: hidden;
    }
    .terminal-bar {
        display: flex; align-items: center; gap: .5rem;
        padding: .6rem 1rem; background: #1e293b;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .term-dot { width: 12px; height: 12px; border-radius: 50%; }
    .term-dot.red    { background: #ef4444; }
    .term-dot.yellow { background: #f59e0b; }
    .term-dot.green  { background: #22c55e; }
    .terminal-bar span { font-size: .78rem; color: rgba(255,255,255,.35); margin-left: .25rem; font-family: monospace; }

    .terminal-output {
        padding: 1.25rem 1.5rem;
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
        font-size: .84rem;
        line-height: 1.65;
        color: #94a3b8;
        white-space: pre-wrap;
        word-break: break-all;
        min-height: 180px;
        max-height: 520px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.1) transparent;
    }
    .terminal-output::-webkit-scrollbar { width: 4px; }
    .terminal-output::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 2px; }

    /* Strip ANSI codes visually with color spans */
    .ansi-green  { color: #4ade80; }
    .ansi-red    { color: #f87171; }
    .ansi-yellow { color: #fbbf24; }
    .ansi-blue   { color: #60a5fa; }
    .ansi-gray   { color: #64748b; }
    .ansi-white  { color: #f8fafc; }

    /* Meta sidebar */
    .meta-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;
        margin-bottom: 1rem;
    }
    .meta-card-header { padding: .75rem 1rem; border-bottom: 1px solid var(--border); background: #f8fafc; }
    .meta-card-header h6 { font-size: .84rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .meta-card-body { padding: 1rem; }

    .meta-item { display: flex; flex-direction: column; gap: .2rem; margin-bottom: .875rem; }
    .meta-item:last-child { margin-bottom: 0; }
    .meta-key { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
    .meta-value { font-size: .875rem; color: var(--text-main); font-weight: 500; word-break: break-all; }

    .badge-success { display:inline-flex; align-items:center; gap:.25rem; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; padding:.25rem .7rem; border-radius:20px; font-size:.8rem; font-weight:700; }
    .badge-failed  { display:inline-flex; align-items:center; gap:.25rem; background:#fff1f2; color:#dc2626; border:1px solid #fecaca; padding:.25rem .7rem; border-radius:20px; font-size:.8rem; font-weight:700; }
    .badge-running { display:inline-flex; align-items:center; gap:.25rem; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:.25rem .7rem; border-radius:20px; font-size:.8rem; font-weight:700; }
    .badge-success .material-symbols-outlined,
    .badge-failed  .material-symbols-outlined,
    .badge-running .material-symbols-outlined { font-size:.9rem; }

    .command-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-family: monospace; font-size: .8rem;
        background: #0f172a; color: #94a3b8;
        padding: .35rem .75rem; border-radius: 6px; word-break: break-all;
    }
    .command-pill .material-symbols-outlined { font-size: .9rem; color: #4ade80; flex-shrink: 0; }
</style>
@endpush

@section('content')

    <a href="{{ route('admin.cron-jobs.index') }}" class="back-link">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to Cron Jobs
    </a>

    <div class="page-header">
        <h1>
            <span class="material-symbols-outlined" style="font-size:1.4rem; color:var(--primary); font-variation-settings:'FILL' 1;">article</span>
            Execution Log #{{ $log->id }}
        </h1>
        <p>{{ $log->cronJob->name }} — {{ $log->started_at->format('M d, Y H:i:s') }}</p>
    </div>

    <div class="log-grid">

        {{-- Terminal output --}}
        <div class="log-card">
            <div class="log-card-header">
                <h5>Command Output</h5>
                @if($log->status === 'success')
                    <span class="badge-success">
                        <span class="material-symbols-outlined">check_circle</span> Success
                    </span>
                @elseif($log->status === 'failed')
                    <span class="badge-failed">
                        <span class="material-symbols-outlined">cancel</span> Failed
                    </span>
                @else
                    <span class="badge-running">
                        <span class="material-symbols-outlined">pending</span> Running
                    </span>
                @endif
            </div>
            <div class="terminal-wrap">
                <div class="terminal-bar">
                    <span class="term-dot red"></span>
                    <span class="term-dot yellow"></span>
                    <span class="term-dot green"></span>
                    <span>php artisan {{ $log->cronJob->command }}</span>
                </div>
                <div class="terminal-output" id="termOutput">{{ $log->output ?: '(no output captured)' }}</div>
            </div>
        </div>

        {{-- Meta sidebar --}}
        <div>
            <div class="meta-card">
                <div class="meta-card-header"><h6>Execution Details</h6></div>
                <div class="meta-card-body">
                    <div class="meta-item">
                        <span class="meta-key">Status</span>
                        <span>
                            @if($log->status === 'success')
                                <span class="badge-success"><span class="material-symbols-outlined">check_circle</span> Success</span>
                            @elseif($log->status === 'failed')
                                <span class="badge-failed"><span class="material-symbols-outlined">cancel</span> Failed</span>
                            @else
                                <span class="badge-running"><span class="material-symbols-outlined">pending</span> Running</span>
                            @endif
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Duration</span>
                        <span class="meta-value">{{ $log->formattedDuration() }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Started At</span>
                        <span class="meta-value">{{ $log->started_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Finished At</span>
                        <span class="meta-value">{{ $log->finished_at ? $log->finished_at->format('M d, Y H:i:s') : '—' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Trigger</span>
                        <span class="meta-value">{{ ucfirst($log->trigger_type) }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Triggered By</span>
                        <span class="meta-value">
                            {{ $log->triggeredBy?->name ?? ($log->trigger_type === 'scheduled' ? 'System (Scheduler)' : '—') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="meta-card">
                <div class="meta-card-header"><h6>Job Info</h6></div>
                <div class="meta-card-body">
                    <div class="meta-item">
                        <span class="meta-key">Job Name</span>
                        <span class="meta-value">{{ $log->cronJob->name }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Command</span>
                        <div class="command-pill">
                            <span class="material-symbols-outlined">terminal</span>
                            php artisan {{ $log->cronJob->command }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Schedule</span>
                        <span class="meta-value">{{ $log->cronJob->schedule_display }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
