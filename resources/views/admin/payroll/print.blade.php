<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll — {{ $monthLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        /* ── Print page setup ── */
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }

        /* ── Header ── */
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1e3a5f;
        }
        .company-info h1 { font-size: 18px; color: #1e3a5f; font-weight: 700; }
        .company-info p  { font-size: 10px; color: #64748b; margin-top: 2px; }
        .doc-info { text-align: right; }
        .doc-info h2 { font-size: 14px; color: #1e3a5f; font-weight: 700; }
        .doc-info .period {
            display: inline-block; margin-top: 4px;
            background: #1e3a5f; color: #fff;
            padding: 3px 10px; border-radius: 4px;
            font-size: 11px; font-weight: 600;
        }
        .doc-info .generated { font-size: 9px; color: #94a3b8; margin-top: 4px; }

        /* ── Summary strip ── */
        .summary-strip {
            display: flex; gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 16px;
            margin-bottom: 16px;
        }
        .sum-item { text-align: center; }
        .sum-item .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        .sum-item .value { font-size: 13px; font-weight: 700; color: #1e293b; }
        .sum-item .value.green { color: #16a34a; }
        .sum-item .value.red   { color: #dc2626; }
        .sum-sep { width: 1px; background: #e2e8f0; margin: 4px 0; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr { background: #1e3a5f; color: #fff; }
        thead th {
            padding: 6px 8px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: right;
            white-space: nowrap;
        }
        thead th:first-child,
        thead th:nth-child(2),
        thead th:nth-child(3) { text-align: left; }

        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }

        td {
            padding: 5px 8px;
            text-align: right;
            vertical-align: middle;
            white-space: nowrap;
        }
        td:first-child,
        td:nth-child(2),
        td:nth-child(3) { text-align: left; }

        .emp-name { font-weight: 600; font-size: 11px; }
        .emp-code { font-size: 9px; color: #64748b; font-family: 'Courier New', monospace; }

        .dept-badge {
            display: inline-block;
            padding: 1px 6px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 8px;
            font-size: 9px;
            font-weight: 600;
        }

        .lop-days { color: #dc2626; font-weight: 600; }
        .net-salary { color: #16a34a; font-weight: 700; }
        .zero { color: #cbd5e1; }

        /* ── Footer row ── */
        tfoot tr { background: #1e3a5f; color: #fff; }
        tfoot td {
            padding: 6px 8px;
            font-weight: 700;
            font-size: 10px;
        }

        /* ── Print footer ── */
        .print-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #94a3b8;
        }
        .signature-block {
            display: flex; gap: 60px; margin-top: 30px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            padding-top: 4px;
            font-size: 9px;
            color: #64748b;
            width: 120px;
            text-align: center;
        }

        /* ── Screen-only controls ── */
        .screen-only {
            display: flex; gap: 8px; margin-bottom: 16px;
        }
        .btn-print {
            padding: 8px 20px;
            background: #1e3a5f; color: #fff;
            border: none; border-radius: 6px;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
        }
        .btn-close-win {
            padding: 8px 20px;
            background: transparent; color: #64748b;
            border: 1px solid #e2e8f0; border-radius: 6px;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
        }

        @media print {
            .screen-only { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Screen controls --}}
<div class="screen-only">
    <button class="btn-print" onclick="window.print()">🖨 Print / Save as PDF</button>
    <button class="btn-close-win" onclick="window.close()">✕ Close</button>
</div>

{{-- Header --}}
<div class="print-header">
    <div class="company-info" style="display:flex;align-items:center;gap:12px;">
        @if(!empty($siteSettings['site_logo']))
            <img src="{{ asset(Storage::url($siteSettings['site_logo'])) }}"
                 alt="Logo"
                 style="height:48px;width:auto;object-fit:contain;flex-shrink:0;">
        @endif
        <div>
            <h1>{{ $siteSettings['site_name'] ?? config('app.name', 'Company') }}</h1>
            <p>Monthly Payroll Statement</p>
        </div>
    </div>
    <div class="doc-info">
        <h2>Payroll Statement</h2>
        <span class="period">{{ $monthLabel }}</span>
        <div class="generated">Generated: {{ now()->format('d M Y, h:i A') }}</div>
    </div>
</div>

{{-- Summary strip --}}
@php
    $totalEmployees = $rows->count();
    $totalGross     = $rows->sum('salary');
    $totalLopAmt    = $rows->sum('lop_amount');
    $totalNet       = $rows->sum('net_salary');
    $totalDays      = $rows->first()['total_days'] ?? 0;
@endphp
<div class="summary-strip">
    <div class="sum-item">
        <div class="label">Employees</div>
        <div class="value">{{ $totalEmployees }}</div>
    </div>
    <div class="sum-sep"></div>
    <div class="sum-item">
        <div class="label">Calendar Days</div>
        <div class="value">{{ $totalDays }}</div>
    </div>
    <div class="sum-sep"></div>
    <div class="sum-item">
        <div class="label">Total Gross</div>
        <div class="value">₹{{ number_format($totalGross, 2) }}</div>
    </div>
    <div class="sum-sep"></div>
    <div class="sum-item">
        <div class="label">Total LOP Deduction</div>
        <div class="value red">₹{{ number_format($totalLopAmt, 2) }}</div>
    </div>
    <div class="sum-sep"></div>
    <div class="sum-item">
        <div class="label">Total Net Payable</div>
        <div class="value green">₹{{ number_format($totalNet, 2) }}</div>
    </div>
</div>

{{-- Payroll Table --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Department</th>
            <th>Total<br>Days</th>
            <th>Working<br>Days</th>
            <th>Present<br>Days</th>
            <th>Paid<br>Leaves</th>
            <th>LOP<br>Days</th>
            <th>Perm.<br>Hours</th>
            <th>Opt.<br>Hols</th>
            <th>Monthly<br>Salary (₹)</th>
            <th>Per Day<br>Salary (₹)</th>
            <th>LOP Amt<br>(₹)</th>
            <th>Net Salary<br>(₹)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $row)
            <tr>
                <td style="text-align:left;color:#94a3b8;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    <div class="emp-name">{{ $row['employee']->name }}</div>
                    <div class="emp-code">{{ $row['employee']->employee_code }}</div>
                </td>
                <td style="text-align:left;">
                    @if($row['employee']->department)
                        <span class="dept-badge">{{ $row['employee']->department->name }}</span>
                    @else
                        <span class="zero">—</span>
                    @endif
                </td>
                <td>{{ $row['total_days'] }}</td>
                <td>{{ $row['working_days'] }}</td>
                <td>
                    {{ $row['present_days'] == (int)$row['present_days']
                        ? (int)$row['present_days']
                        : number_format($row['present_days'], 1) }}
                </td>
                <td class="{{ $row['paid_leaves'] > 0 ? '' : 'zero' }}">
                    {{ $row['paid_leaves'] > 0 ? number_format($row['paid_leaves'], 1) : '0' }}
                </td>
                <td class="{{ $row['lop_days'] > 0 ? 'lop-days' : 'zero' }}">
                    {{ $row['lop_days'] > 0 ? number_format($row['lop_days'], 2) : '0' }}
                </td>
                <td class="{{ $row['permission_hours'] > 0 ? '' : 'zero' }}">
                    {{ $row['permission_hours'] > 0 ? number_format($row['permission_hours'], 1).'h' : '0' }}
                </td>
                <td class="{{ $row['optional_holidays_taken'] > 0 ? '' : 'zero' }}">
                    {{ $row['optional_holidays_taken'] ?: '0' }}
                </td>
                <td>{{ number_format($row['salary'], 2) }}</td>
                <td style="color:#64748b;">{{ number_format($row['per_day_salary'], 2) }}</td>
                <td class="{{ $row['lop_amount'] > 0 ? 'lop-days' : 'zero' }}">
                    {{ $row['lop_amount'] > 0 ? number_format($row['lop_amount'], 2) : '—' }}
                </td>
                <td class="net-salary">{{ number_format($row['net_salary'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10" style="text-align:left;">TOTAL ({{ $totalEmployees }} employees)</td>
            <td>{{ number_format($totalGross, 2) }}</td>
            <td></td>
            <td>{{ $totalLopAmt > 0 ? number_format($totalLopAmt, 2) : '—' }}</td>
            <td>{{ number_format($totalNet, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- Signature block + Footer --}}
<div class="signature-block">
    <div class="sig-line">Prepared By</div>
    <div class="sig-line">Verified By</div>
    <div class="sig-line">Authorised By</div>
</div>

<div class="print-footer">
    <span>Generated by {{ $siteSettings['site_name'] ?? config('app.name', 'Attendance System') }} — {{ now()->format('d M Y, h:i A') }}</span>
    <span>Payroll Period: {{ $monthLabel }}</span>
</div>

<script>
    // Auto-trigger print dialog when opened via "Print / PDF" button
    // Only if opened by another page (not direct navigation)
    if (window.opener || document.referrer) {
        // slight delay so the page renders first
        setTimeout(() => window.print(), 600);
    }
</script>
</body>
</html>
