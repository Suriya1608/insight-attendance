<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Offer Letter</title>
<style>
  body { margin: 0; padding: 0; background: #f1f5f9; font-family: Arial, Helvetica, sans-serif; }
  .wrapper { max-width: 620px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
  .header  { background: #1e3a5f; padding: 28px 36px; text-align: center; }
  .header h1 { margin: 0; font-size: 22px; color: #ffffff; letter-spacing: 0.5px; }
  .header p  { margin: 6px 0 0; font-size: 13px; color: #93c5fd; }
  .accent-bar { height: 4px; background: linear-gradient(90deg, #137fec, #5ab4f7); }
  .body { padding: 32px 36px; }
  .greeting { font-size: 17px; font-weight: 700; color: #1e3a5f; margin-bottom: 14px; }
  .para { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 14px; }
  .highlight-box {
    background: #eff6ff; border-left: 4px solid #137fec;
    border-radius: 4px; padding: 16px 20px; margin: 20px 0;
  }
  .highlight-box table { width: 100%; border-collapse: collapse; }
  .highlight-box td { padding: 6px 0; font-size: 14px; color: #1e3a5f; }
  .highlight-box td:first-child { font-weight: 700; width: 46%; color: #334155; }
  .cta-wrap { text-align: center; margin: 28px 0; }
  .cta-btn {
    display: inline-block; background: #137fec; color: #ffffff;
    text-decoration: none; font-size: 14px; font-weight: 700;
    padding: 12px 32px; border-radius: 6px; letter-spacing: 0.3px;
  }
  .note { font-size: 12.5px; color: #94a3b8; text-align: center; margin-top: 10px; }
  .footer {
    background: #1e3a5f; padding: 20px 36px;
    text-align: center; font-size: 12px; color: #93c5fd;
    border-top: 3px solid #137fec;
  }
  .footer a { color: #5ab4f7; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

  {{-- Header --}}
  <div class="header">
    <h1>{{ $companyName }}</h1>
    <p>Human Resources &mdash; Offer of Employment</p>
  </div>
  <div class="accent-bar"></div>

  {{-- Body --}}
  <div class="body">
    <div class="greeting">Dear {{ explode(' ', trim($offerLetter->name))[0] }},</div>

    <p class="para">
      We are delighted to offer you the position of <strong>{{ $offerLetter->designation }}</strong>
      at <strong>{{ $companyName }} Pvt Ltd</strong>. Please find your official Offer Letter attached
      to this email.
    </p>

    {{-- Key details box --}}
    <div class="highlight-box">
      <table>
        <tr>
          <td>Candidate Name</td>
          <td>{{ $offerLetter->name }}</td>
        </tr>
        <tr>
          <td>Designation</td>
          <td>{{ $offerLetter->designation }}</td>
        </tr>
        <tr>
          <td>CTC (Annual)</td>
          <td>
            ₹{{ number_format((int) $offerLetter->ctc) }}/-
          </td>
        </tr>
        <tr>
          <td>Date of Joining</td>
          <td>{{ $offerLetter->joining_date->format('d F Y') }}</td>
        </tr>
        <tr>
          <td>Offer Date</td>
          <td>{{ $offerLetter->offer_date->format('d F Y') }}</td>
        </tr>
      </table>
    </div>

    <p class="para">
      Kindly review the attached Offer Letter carefully and confirm your acceptance within
      <strong>3 working days</strong>. On your joining date, please report to the HR department
      with all original educational certificates, experience letters, and two passport-size photographs.
    </p>

    <p class="para">
      We are truly excited to have you on board and look forward to your valuable contributions.
      Should you have any questions, feel free to reach out to our HR team.
    </p>

    <p class="para">
      Warm regards,<br>
      <strong>HR Department</strong><br>
      {{ $companyName }} Pvt Ltd
    </p>

    <div class="note">The Offer Letter PDF is attached to this email.</div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    &copy; {{ date('Y') }} {{ $companyName }} Pvt Ltd. All rights reserved.<br>
    This is an automated email. Please do not reply directly to this message.
  </div>

</div>
</body>
</html>
