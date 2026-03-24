@php
/* ── Helpers ── */
if (!function_exists('indFmt')) {
    function indFmt(float $n): string {
        $n = (int) $n;
        if ($n === 0) return '0';
        $last3     = $n % 1000;
        $remaining = (int) ($n / 1000);
        $result    = str_pad($last3, 3, '0', STR_PAD_LEFT);
        while ($remaining > 0) {
            $chunk     = $remaining % 100;
            $remaining = (int) ($remaining / 100);
            $result    = ($remaining > 0 ? str_pad($chunk, 2, '0', STR_PAD_LEFT) : (string) $chunk) . ',' . $result;
        }
        return $result;
    }
}
if (!function_exists('n2w')) {
    function n2w(int $n): string {
        if ($n === 0) return 'Zero';
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                 'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                 'Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $r = '';
        if ($n >= 10000000) { $r .= n2w((int)($n/10000000)).' Crore '; $n %= 10000000; }
        if ($n >= 100000)   { $r .= n2w((int)($n/100000))  .' Lakh ';  $n %= 100000;   }
        if ($n >= 1000)     { $r .= n2w((int)($n/1000))    .' Thousand '; $n %= 1000;  }
        if ($n >= 100)      { $r .= $ones[(int)($n/100)]   .' Hundred '; $n %= 100;    }
        if ($n >= 20)       { $r .= $tens[(int)($n/10)]    .' '; $n %= 10; }
        if ($n > 0)         { $r .= $ones[$n].' '; }
        return trim($r);
    }
}

$companyName   = strtoupper($settings['site_name'] ?? config('app.name'));
$companyAddr   = $settings['company_address'] ?? '';
$siteUrl       = $settings['site_url'] ?? '';
$siteEmail     = $settings['company_email'] ?? env('MAIL_FROM_ADDRESS', '');
$companyMobile = $settings['company_mobile'] ?? '';
$sigName       = $settings['signatory_name'] ?? '';
$sigDesig      = $settings['signatory_designation'] ?? '';

$ctcInt       = (int) $offerLetter->ctc;
$ctcFormatted = '₹' . indFmt($ctcInt) . '/-';
$ctcWords     = 'Rupees ' . n2w($ctcInt) . ' Only';

$offerDateFmt = $offerLetter->offer_date->format('d-m-Y');
$joinDateLong = $offerLetter->joining_date->format('d F Y');
$firstName    = explode(' ', trim($offerLetter->name))[0];
$siteDisplay  = preg_replace('#^https?://#', '', rtrim($siteUrl, '/'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Offer Letter &mdash; {{ $offerLetter->name }}</title>
<style>

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10.5pt;
    color: #1a1a2e;
    background: #ffffff;
    line-height: 1.68;
}

/*
 * @page margins = the safe content area on EVERY page.
 * Fixed header (top:0) fills the top margin space.
 * Fixed footer (bottom:0) fills the bottom margin space.
 * This prevents content from overlapping header/footer on page 2+.
 */
@page {
    size: A4 portrait;
    margin-top: 155px;
    margin-bottom: 68px;
    margin-left: 0;
    margin-right: 0;
}

/* ═══════════════════════════════════
   WATERMARK — centered on every page
   ═══════════════════════════════════ */
.watermark {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: table;
    z-index: 0;
}
.watermark-cell {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
}
.watermark-img {
    width: 320px;
    height: auto;
    opacity: 0.07;
}

/* ═══════════════════════════════════
   FIXED HEADER — repeats every page
   ═══════════════════════════════════ */
.pdf-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    z-index: 10;
}
.hdr-inner {
    padding: 10px 36px 6px;
    display: table;
    width: 100%;
    table-layout: fixed;
}
.hdr-left {
    display: table-cell;
    vertical-align: middle;
    width: 58%;
}
.hdr-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    width: 42%;
}
.hdr-company-name {
    font-size: 15pt;
    font-weight: 700;
    color: #1e3a5f;
    letter-spacing: 0.5px;
    line-height: 1.2;
}
.hdr-company-addr {
    font-size: 8pt;
    color: #64748b;
    margin-top: 4px;
    line-height: 1.5;
}
.hdr-logo {
    height: 90px;
    width: auto;
    max-width: 260px;
    display: block;
    margin-left: auto;
    margin-bottom: 4px;
}
.hdr-date {
    font-size: 8.5pt;
    color: #334155;
    font-weight: 700;
    letter-spacing: 0.2px;
}
.hdr-border {
    border: 0;
    border-top: 3px solid #137fec;
    margin: 0;
}

/* ═══════════════════════════════════
   FIXED FOOTER — repeats every page
   ═══════════════════════════════════ */
.pdf-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: #1e3a5f;
    border-top: 3px solid #137fec;
    z-index: 10;
}
.ft-table {
    display: table;
    width: 100%;
    height: 57px;
    table-layout: fixed;
    padding: 0 30px;
}
.ft-cell {
    display: table-cell;
    vertical-align: middle;
    font-size: 8.5pt;
    color: #c8daf4;
    white-space: nowrap;
}
.ft-center { text-align: center; }
.ft-right  { text-align: right; }
.ft-icon {
    color: #5ab4f7;
    margin-right: 5px;
    font-size: 9pt;
}

/* ═══════════════════════════════════
   CONTENT AREA
   No margin-top/bottom here — @page handles it.
   ═══════════════════════════════════ */
.page-wrap {
    padding: 16px 36px 0;
    position: relative;
    z-index: 1;
}

/* ─── Recipient ─── */
.recipient {
    margin-bottom: 14px;
    padding: 10px 14px;
    background: #f8fafc;
    border-left: 4px solid #1e3a5f;
}
.recipient-name {
    font-size: 11pt;
    font-weight: 700;
    color: #1e3a5f;
    letter-spacing: 0.3px;
    margin-bottom: 3px;
}
.recipient-line {
    font-size: 9pt;
    color: #475569;
    line-height: 1.55;
}

/* ─── Document title ─── */
.doc-title {
    text-align: center;
    font-size: 14pt;
    font-weight: 700;
    color: #1a1a2e;
    text-decoration: underline;
    margin: 14px 0 10px;
    letter-spacing: 1.5px;
}

/* ─── Subject ─── */
.doc-subject {
    font-size: 10pt;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 12px;
    padding: 5px 10px;
    background: #eef4fb;
    border-left: 3px solid #137fec;
}

/* ─── Salutation ─── */
.salutation {
    font-size: 10.5pt;
    margin-bottom: 8px;
    color: #1a1a2e;
    font-weight: 600;
}

/* ─── Intro ─── */
.intro {
    font-size: 10.5pt;
    color: #334155;
    margin-bottom: 10px;
    line-height: 1.68;
    text-align: justify;
}

/* ─── Terms heading ─── */
.terms-heading {
    font-size: 10.5pt;
    font-weight: 700;
    color: #1a1a2e;
    margin: 10px 0 6px;
    text-decoration: underline;
    letter-spacing: 0.3px;
}

/* ─── Section heading ─── */
.sec-head {
    display: block;
    background: #eef4fb;
    border-left: 4px solid #137fec;
    padding: 5px 10px;
    margin: 12px 0 6px;
    font-size: 10.5pt;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.3;
}

/* ─── Paragraph ─── */
.para {
    font-size: 10.5pt;
    color: #334155;
    line-height: 1.68;
    margin-bottom: 6px;
    text-align: justify;
}

/* ─── Bullet list ─── */
.bullet-list {
    margin: 4px 0 6px 20px;
    padding: 0;
}
.bullet-list li {
    font-size: 10.5pt;
    color: #334155;
    margin-bottom: 5px;
    line-height: 1.65;
    text-align: justify;
}

/* ─── Notes box ─── */
.notes-box {
    border-left: 4px solid #137fec;
    padding: 8px 14px;
    background: #f0f7ff;
    margin: 6px 0;
    font-size: 10pt;
    color: #1e3a5f;
    line-height: 1.65;
}

/* ─── Closing ─── */
.closing {
    font-size: 10.5pt;
    color: #334155;
    line-height: 1.68;
    margin: 12px 0 16px;
    text-align: justify;
}

/* ─── Signature ─── */
.sig-block { margin-top: 10px; }
.sig-for   { font-size: 10.5pt; color: #1a1a2e; margin-bottom: 2px; }
.sig-company {
    font-size: 10.5pt;
    font-weight: 700;
    color: #1e3a5f;
    margin-bottom: 40px;
}
.sig-line {
    width: 190px;
    border-top: 1.5px solid #1e3a5f;
    margin-bottom: 5px;
}
.sig-label    { font-size: 10pt; color: #1a1a2e; font-weight: 600; }
.sig-name-row { font-size: 10pt; color: #475569; line-height: 1.55; }

</style>
</head>
<body>

{{-- ══ Watermark ══ --}}
@if($logoPath)
<div class="watermark">
    <div class="watermark-cell">
        <img class="watermark-img" src="{{ $logoPath }}" alt="">
    </div>
</div>
@endif

{{-- ══ Fixed Header ══ --}}
<div class="pdf-header">
    <div class="hdr-inner">
        <div class="hdr-left">
            <div class="hdr-company-name">{{ $companyName }}</div>
            @if($companyAddr)
                <div class="hdr-company-addr">{{ $companyAddr }}</div>
            @endif
        </div>
        <div class="hdr-right">
            @if($logoPath)
                <img class="hdr-logo" src="{{ $logoPath }}" alt="Logo">
            @endif
            <div class="hdr-date">Date: {{ $offerDateFmt }}</div>
        </div>
    </div>
    <hr class="hdr-border">
</div>

{{-- ══ Fixed Footer ══ --}}
<div class="pdf-footer">
    <div class="ft-table">
        <div class="ft-cell">
            @if($siteDisplay)
                <span class="ft-icon">&#9679;</span>{{ $siteDisplay }}
            @endif
        </div>
        <div class="ft-cell ft-center">
            @if($companyMobile)
                <span class="ft-icon">&#9742;</span>{{ $companyMobile }}
            @endif
        </div>
        <div class="ft-cell ft-right">
            @if($siteEmail)
                <span class="ft-icon">&#9993;</span>{{ $siteEmail }}
            @endif
        </div>
    </div>
</div>

{{-- ══ Page Content ══ --}}
<div class="page-wrap">

    {{-- Recipient --}}
    <div class="recipient">
        <div class="recipient-name">{{ strtoupper($offerLetter->name) }}</div>
        @if($offerLetter->address)
            @foreach(explode("\n", $offerLetter->address) as $addrLine)
                @if(trim($addrLine))
                    <div class="recipient-line">{{ trim($addrLine) }}</div>
                @endif
            @endforeach
        @endif
        @if($offerLetter->mobile)
            <div class="recipient-line">Mob: {{ $offerLetter->mobile }}</div>
        @endif
        @if($offerLetter->email)
            <div class="recipient-line">{{ $offerLetter->email }}</div>
        @endif
    </div>

    {{-- Title --}}
    <div class="doc-title">OFFER LETTER</div>

    {{-- Subject --}}
    <div class="doc-subject">Subject: Offer of Employment &ndash; {{ $offerLetter->designation }}</div>

    {{-- Salutation --}}
    <div class="salutation">Dear {{ $firstName }},</div>

    {{-- Intro --}}
    <div class="intro">
        We are pleased to extend an offer for the position of <strong>{{ $offerLetter->designation }}</strong>
        with <strong>{{ $settings['site_name'] ?? config('app.name') }} Pvt Ltd</strong> at our Chennai office.
    </div>

    {{-- Terms heading --}}
    <div class="terms-heading">Terms of the Offer:</div>

    {{-- 1. Compensation --}}
    <div class="sec-head">1.&nbsp; Compensation</div>
    <div class="para">
        Your overall emoluments on a Cost-to-Company (CTC) basis will be
        <strong>{{ $ctcFormatted }} ({{ $ctcWords }} per annum)</strong>.
    </div>

    {{-- 2. Probation Period --}}
    <div class="sec-head">2.&nbsp; Probation Period</div>
    <div class="para">
        You will be on probation for a period of <strong>six (6) months</strong> from the date of joining.
        During this period, your performance and conduct will be evaluated. Upon satisfactory completion
        of the probation period, your employment will be confirmed in writing.
    </div>

    {{-- 3. Appointment Order --}}
    <div class="sec-head">3.&nbsp; Appointment Order</div>
    <div class="para">
        A detailed <strong>appointment letter</strong> outlining your roles, responsibilities, and company
        policies will be issued upon your joining
        <strong>{{ $settings['site_name'] ?? config('app.name') }} Pvt Ltd</strong> and submission of all
        required documents to the HR team as part of the joining formalities.
    </div>

    {{-- 4. Joining Date --}}
    <div class="sec-head">4.&nbsp; Joining Date</div>
    <div class="para">
        You are expected to join on <strong>{{ $joinDateLong }}</strong> and report to:
        <strong>{{ $settings['site_name'] ?? config('app.name') }} Pvt Ltd, Chennai</strong>.
    </div>
    <div class="para">
        Please report to the HR department with all original educational certificates, experience letters,
        and two passport-size photographs. Kindly confirm your acceptance of this offer within
        <strong>3 working days</strong>.
    </div>

    {{-- 5. Terms & Conditions --}}
    <div class="sec-head">5.&nbsp; Terms &amp; Conditions</div>
    <ul class="bullet-list">
        <li>The company's policies and rules will govern your employment with {{ $settings['site_name'] ?? config('app.name') }} Pvt Ltd as applicable from time to time.</li>
        <li>During the probation period, either party may terminate the employment by providing one month's notice.</li>
        <li>Upon successful completion of the probation period and confirmation of employment, the employee will be required to serve a notice period of ninety (90) days in case of resignation, or pay salary instead of the notice period, subject to the company's discretion.</li>
        <li>You will be required to maintain the confidentiality of all company information, documents, and client data during and after your employment.</li>
        <li>This offer is subject to satisfactory background verification. If any information provided by you is found to be false or misleading, the company reserves the right to withdraw this offer or terminate employment without notice.</li>
        <li>You are expected to adhere to all company policies, code of conduct, and professional standards during your employment.</li>
        <li><strong>Working Hours:</strong> Standard working hours are 9 hours per day, 6 days per week, subject to change based on project requirements.</li>
        <li><strong>Leave Policy:</strong> You will be entitled to leave as per the company's leave policy, communicated to you after joining.</li>
    </ul>

    @if($offerLetter->content)
    {{-- 6. Additional Terms --}}
    <div class="sec-head">6.&nbsp; Additional Terms</div>
    <div class="notes-box">{{ $offerLetter->content }}</div>
    @endif

    {{-- Closing --}}
    <div class="closing">
        This offer is valid upon your <strong>acceptance on or before {{ $joinDateLong }}</strong>.
        We are excited to welcome you to the <strong>{{ $settings['site_name'] ?? config('app.name') }}</strong> family
        and look forward to your valuable contribution as a <strong>{{ $offerLetter->designation }}</strong>.
    </div>

    {{-- Signature --}}
    <div class="sig-block">
        <div class="sig-for">For</div>
        <div class="sig-company">{{ $companyName }}</div>
        <div class="sig-line"></div>
        <div class="sig-label">Authorised Signatory</div>
        @if($sigName)  <div class="sig-name-row">Name: {{ $sigName }}</div> @endif
        @if($sigDesig) <div class="sig-name-row">Designation: {{ $sigDesig }}</div> @endif
    </div>

</div>{{-- /page-wrap --}}
</body>
</html>
