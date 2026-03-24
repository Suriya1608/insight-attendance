<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History – {{ $user->name }} – {{ $rangeLabel }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px; color: #0f172a;
            background: #fff; padding: 24px;
        }

        /* Print header */
        .print-header { margin-bottom: 20px; }
        .print-header h1 { font-size: 18px; font-weight: 800; color: #0f172a; }
        .print-header .meta { font-size: 11px; color: #64748b; margin-top: 4px; }
        .print-header .meta span { margin-right: 16px; }

        /* KPI row */
        .kpi-row { display: flex; gap: 12px; margin-bottom: 20px; }
        .kpi-box {
            flex: 1; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 10px 14px; text-align: center;
        }
        .kpi-box .val { font-size: 20px; font-weight: 800; }
        .kpi-box .lbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-top: 2px; }
        .kpi-box.kpi-blue   .val { color: #1d4ed8; }
        .kpi-box.kpi-green  .val { color: #15803d; }
        .kpi-box.kpi-red    .val { color: #dc2626; }
        .kpi-box.kpi-purple .val { color: #7c3aed; }
        .kpi-box.kpi-orange .val { color: #ea580c; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f1f5f9; padding: 7px 10px;
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #64748b; border: 1px solid #e2e8f0;
            text-align: left; white-space: nowrap;
        }
        tbody td {
            padding: 6px 10px; border: 1px solid #e2e8f0;
            font-size: 11px; vertical-align: middle;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr.row-sunday  td { background: #f1f5f9; color: #64748b; }
        tbody tr.row-holiday td { background: #eff6ff; }
        tbody tr.row-absent  td { background: #fff5f5; }

        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            font-size: 9px; font-weight: 700; letter-spacing: .02em;
        }
        .bs-present    { background: #dcfce7; color: #15803d; }
        .bs-absent     { background: #fee2e2; color: #dc2626; }
        .bs-holiday    { background: #dbeafe; color: #1d4ed8; }
        .bs-sunday     { background: #f1f5f9; color: #64748b; }
        .bs-leave      { background: #ede9fe; color: #6d28d9; }
        .bs-half_day   { background: #fef9c3; color: #854d0e; }
        .bh-suff       { background: #dcfce7; color: #15803d; }
        .bh-insuff     { background: #fff7ed; color: #c2410c; }

        .td-mono { font-family: Consolas, monospace; }

        /* Print controls (screen only) */
        .print-controls {
            position: fixed; top: 16px; right: 16px;
            display: flex; gap: 8px; z-index: 999;
        }
        .btn-print, .btn-close {
            padding: 8px 18px; border-radius: 6px; border: none;
            font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-print { background: #137fec; color: #fff; }
        .btn-print:hover { background: #0f6fd4; }
        .btn-close { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .btn-close:hover { background: #e2e8f0; }

        @media print {
            .print-controls { display: none !important; }
            body { padding: 12px; }
            .kpi-row { break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">
        &#128438; Print / Save PDF
    </button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<div class="print-header">
    <h1>Attendance History</h1>
    <div class="meta">
        <span><strong>Employee:</strong> {{ $user->name }} ({{ $user->employee_code ?? 'N/A' }})</span>
        <span><strong>Period:</strong> {{ $rangeLabel }}</span>
        <span><strong>Generated:</strong> {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</span>
    </div>
</div>

<div class="kpi-row">
    <div class="kpi-box kpi-blue">
        <div class="val">{{ $kpi['work_days'] }}</div>
        <div class="lbl">Working Days</div>
    </div>
    <div class="kpi-box kpi-green">
        <div class="val">{{ $kpi['present'] }}</div>
        <div class="lbl">Present Days</div>
    </div>
    <div class="kpi-box kpi-red">
        <div class="val">{{ $kpi['absent'] }}</div>
        <div class="lbl">Absent Days</div>
    </div>
    <div class="kpi-box kpi-purple">
        <div class="val">{{ $kpi['hours_fmt'] }}</div>
        <div class="lbl">Total Hours</div>
    </div>
    <div class="kpi-box kpi-orange">
        <div class="val">{{ $kpi['insuff_days'] }}</div>
        <div class="lbl">Insufficient Hrs Days</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Day</th>
            <th>Status</th>
            <th>Punch In</th>
            <th>Punch Out</th>
            <th>Punch In Location</th>
            <th>Punch Out Location</th>
            <th>Work Hours</th>
            <th>Hours Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($days as $i => $row)
        @php
            $rowClass = match($row['row_status']) {
                'sunday'  => 'row-sunday',
                'holiday' => 'row-holiday',
                'absent'  => 'row-absent',
                default   => '',
            };
            $badgeClass = match($row['row_status']) {
                'present'  => 'bs-present',
                'absent'   => 'bs-absent',
                'holiday'  => 'bs-holiday',
                'sunday'   => 'bs-sunday',
                'leave'    => 'bs-leave',
                'half_day' => 'bs-half_day',
                default    => 'bs-absent',
            };
            $badgeLabel = match($row['row_status']) {
                'present'  => 'Present',
                'absent'   => 'Absent',
                'holiday'  => $row['holiday_name'] ?? 'Holiday',
                'sunday'   => 'Sunday',
                'future'   => 'Upcoming',
                'leave'    => 'Leave',
                'half_day' => 'Half Day',
                default    => ucfirst($row['row_status']),
            };
        @endphp
        <tr class="{{ $rowClass }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
            <td>{{ substr($row['day'], 0, 3) }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
            <td class="td-mono">{{ $row['punch_in']  ?? '—' }}</td>
            <td class="td-mono">{{ $row['punch_out'] ?? '—' }}</td>
            <td>{{ $row['punch_in_loc']  ?? '—' }}</td>
            <td>{{ $row['punch_out_loc'] ?? '—' }}</td>
            <td class="td-mono">{{ $row['hours_fmt'] ?? '—' }}</td>
            <td>
                @if($row['hours_status'] === 'sufficient')
                    <span class="badge bh-suff">Sufficient</span>
                @elseif($row['hours_status'] === 'insufficient')
                    <span class="badge bh-insuff">Insufficient</span>
                @else
                    —
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    // Auto-open print dialog after a short delay for "Save as PDF"
    // Only auto-trigger on first load, not when user explicitly navigates
    if (window.location.hash !== '#viewed') {
        window.location.hash = '#viewed';
    }
</script>

</body>
</html>
