<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Credentials</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px; }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(15,23,42,.10);
        }
        .card-header {
            background: linear-gradient(135deg, #137fec 0%, #0c5cb5 100%);
            padding: 32px 36px 28px;
            text-align: center;
        }
        .card-header h1 {
            color: #fff;
            font-size: 1.375rem;
            font-weight: 700;
            letter-spacing: -.02em;
            margin-bottom: 4px;
        }
        .card-header p {
            color: rgba(255,255,255,.72);
            font-size: .875rem;
        }
        .card-body { padding: 32px 36px; }
        .greeting {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .intro {
            font-size: .9rem;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .credentials-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .credentials-box h2 {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 16px;
        }
        .cred-row {
            display: flex;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .cred-row:last-child { border-bottom: none; }
        .cred-label {
            font-size: .8rem;
            font-weight: 600;
            color: #64748b;
            min-width: 130px;
            flex-shrink: 0;
            padding-top: 1px;
        }
        .cred-value {
            font-size: .9rem;
            font-weight: 600;
            color: #0f172a;
            word-break: break-all;
        }
        .cred-value.password-val {
            font-family: 'Courier New', monospace;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 2px 10px;
            color: #1d4ed8;
            font-size: .875rem;
        }
        .notice {
            background: rgba(251,191,36,.08);
            border: 1px solid rgba(251,191,36,.3);
            border-left: 3px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: .84rem;
            color: #92400e;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn-wrap { text-align: center; margin-bottom: 24px; }
        .btn-login {
            display: inline-block;
            background: linear-gradient(135deg, #1488f5 0%, #0c5cb5 100%);
            color: #fff !important;
            text-decoration: none;
            padding: 12px 36px;
            border-radius: 8px;
            font-size: .9375rem;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .footer {
            border-top: 1px solid #e2e8f0;
            padding: 16px 36px 20px;
            text-align: center;
            font-size: .78rem;
            color: #94a3b8;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="card-header">
            @if(!empty($siteSettings['site_logo']))
                <div style="margin-bottom:14px;">
                    <img src="{{ asset(Storage::url($siteSettings['site_logo'])) }}"
                         alt="{{ $siteSettings['site_name'] ?? config('app.name') }}"
                         style="height:52px;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
                </div>
            @endif
            <h1>Welcome to {{ $siteSettings['site_name'] ?? config('app.name') }}</h1>
            <p>Your employee account has been created</p>
        </div>

        <div class="card-body">

            <p class="greeting">Hello, {{ $user->name }}!</p>
            <p class="intro">
                Your employee account has been set up successfully.
                Use the credentials below to sign in to the portal.
            </p>

            <div class="credentials-box">
                <h2>Login Credentials</h2>

                <div class="cred-row">
                    <span class="cred-label">Employee ID</span>
                    <span class="cred-value">{{ $user->employee_code }}</span>
                </div>
                @if($user->username)
                <div class="cred-row">
                    <span class="cred-label">Username</span>
                    <span class="cred-value">{{ $user->username }}</span>
                </div>
                @endif
                <div class="cred-row">
                    <span class="cred-label">Email</span>
                    <span class="cred-value">{{ $user->email }}</span>
                </div>
                <div class="cred-row">
                    <span class="cred-label">Password</span>
                    <span class="cred-value password-val">{{ $plainPassword }}</span>
                </div>
            </div>

            <div class="notice">
                <strong>Important:</strong> This is a system-generated password. Please log in and change it immediately from your profile settings for security.
            </div>

            <div class="btn-wrap">
                <a href="{{ config('app.url') }}/login" class="btn-login">Sign In Now</a>
            </div>

        </div>

        <div class="footer">
            You can sign in using your Employee ID, username, or email address.<br>
            &copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? config('app.name') }}. This is an automated message — please do not reply.
        </div>

    </div>
</div>
</body>
</html>
