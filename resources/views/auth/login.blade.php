<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ $siteSettings['site_title'] ?? ($siteSettings['site_name'] ?? 'HR Portal') }}</title>

    @if(!empty($siteSettings['site_favicon']))
        <link rel="icon" href="{{ Storage::url($siteSettings['site_favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:        #137fec;
            --primary-hover:  #0f6fd4;
            --brand-dark:     #0c1526;
            --brand-accent:   #38bdf8;
            --brand-accent2:  #818cf8;
            --bg-page:        #f1f5f9;
            --surface:        #ffffff;
            --text-main:      #0f172a;
            --text-secondary: #64748b;
            --text-muted:     #94a3b8;
            --border:         #e2e8f0;
            --radius-sm:      8px;
            --radius-md:      12px;
            --radius-lg:      18px;
            --shadow-card:    0 24px 64px -12px rgba(15,23,42,.22), 0 4px 20px -2px rgba(15,23,42,.1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ══════════════════════════════════════════
           SPLIT LAYOUT
        ══════════════════════════════════════════ */
        .login-root { display: flex; min-height: 100vh; }

        /* ── LEFT PANEL ── */
        .login-brand {
            flex: 0 0 460px;
            background: var(--brand-dark);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.75rem 2.75rem 2.25rem;
            overflow: hidden;
        }

        .brand-bg-layer {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }
        .brand-bg-layer::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 26px 26px;
        }
        .brand-bg-layer::after {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 380px; height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56,189,248,.14) 0%, transparent 68%);
        }
        .brand-glow-2 {
            position: absolute;
            bottom: -100px; left: -60px;
            width: 340px; height: 340px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(129,140,248,.1) 0%, transparent 68%);
            pointer-events: none;
        }
        .brand-accent-line {
            position: absolute;
            top: 0; right: 0;
            width: 1px; height: 100%;
            background: linear-gradient(180deg,
                transparent 0%,
                rgba(56,189,248,.25) 30%,
                rgba(19,127,236,.35) 60%,
                transparent 100%);
            pointer-events: none;
        }

        .brand-top { position: relative; z-index: 1; }

        .brand-logo-wrap {
            display: inline-flex;
            align-items: center;
            gap: .875rem;
            margin-bottom: 3rem;
            padding: .5rem .875rem .5rem .625rem;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: var(--radius-md);
        }
        .brand-logo-img {
            height: 40px;
            max-width: 160px;
            object-fit: contain;
            border-radius: var(--radius-sm);
        }
        .brand-logo-fallback {
            width: 38px; height: 38px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--primary) 0%, var(--brand-accent) 100%);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .brand-logo-fallback .material-symbols-outlined { color: #fff; font-size: 1.5rem; }
        .brand-site-name { font-size: 1.0625rem; font-weight: 700; color: #fff; letter-spacing: -.02em; }

        .brand-headline {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -.035em;
            color: #fff;
            margin-bottom: 1rem;
        }
        .brand-headline .accent {
            background: linear-gradient(90deg, var(--brand-accent), var(--brand-accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-tagline {
            font-size: .9375rem;
            color: rgba(255,255,255,.48);
            line-height: 1.7;
            max-width: 320px;
            margin-bottom: 2.75rem;
        }

        .brand-features { position: relative; z-index: 1; display: flex; flex-direction: column; gap: .625rem; }
        .brand-feature {
            display: flex; align-items: center; gap: .875rem;
            padding: .75rem 1rem;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: var(--radius-sm);
            transition: background .2s;
        }
        .brand-feature:hover { background: rgba(255,255,255,.065); }
        .brand-feature-icon {
            width: 36px; height: 36px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, rgba(56,189,248,.15) 0%, rgba(19,127,236,.15) 100%);
            border: 1px solid rgba(56,189,248,.15);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .brand-feature-icon .material-symbols-outlined { font-size: 1.125rem; color: var(--brand-accent); }
        .brand-feature-text { font-size: .875rem; color: rgba(255,255,255,.6); font-weight: 500; }

        .brand-footer { position: relative; z-index: 1; font-size: .8rem; color: rgba(255,255,255,.25); margin-top: 2rem; }

        /* ── RIGHT PANEL ── */
        .login-form-panel {
            flex: 1;
            background: var(--bg-page);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            position: relative;
        }
        .login-form-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(19,127,236,.04) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(226,232,240,.7);
            width: 100%;
            max-width: 420px;
            padding: 2.75rem 2.25rem 2.25rem;
            position: relative;
            z-index: 1;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 12%; right: 12%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), var(--brand-accent), transparent);
            border-radius: 999px;
        }

        .card-logo-wrap {
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-logo-img { height: 72px; max-width: 220px; object-fit: contain; }
        .card-logo-fallback {
            width: 66px; height: 66px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 24px rgba(19,127,236,.35);
        }
        .card-logo-fallback .material-symbols-outlined { color: #fff; font-size: 2rem; }
        .card-logo-name { font-size: 1.25rem; font-weight: 800; color: var(--text-main); letter-spacing: -.025em; }
        .card-logo-tagline { font-size: .7rem; color: var(--text-muted); letter-spacing: .05em; text-transform: uppercase; font-weight: 600; }

        .login-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.03em; color: var(--text-main); margin-bottom: .25rem; }
        .login-subtitle { font-size: .875rem; color: var(--text-secondary); margin-bottom: 1.75rem; }

        .form-label { font-size: .8125rem; font-weight: 600; color: var(--text-main); margin-bottom: .375rem; display: block; }

        .field-wrap { position: relative; }
        .field-icon {
            position: absolute;
            top: 50%; left: .875rem;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 1.0625rem;
            transition: color .2s;
        }
        .field-wrap:focus-within .field-icon { color: var(--primary); }

        .form-control {
            height: 2.75rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-size: .9rem;
            color: var(--text-main);
            background: #f8fafc;
            padding-left: 2.75rem;
            transition: border-color .2s, box-shadow .2s, background .2s;
            width: 100%;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(19,127,236,.14);
            background: #fff;
        }
        .form-control.is-invalid { border-color: #ef4444; background: #fff8f8; background-image: none; }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.14); }
        .has-toggle .form-control { padding-right: 3rem; }

        .toggle-password {
            position: absolute;
            top: 50%; right: .875rem;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted);
            cursor: pointer; padding: 0;
            font-size: 1.0625rem; line-height: 1;
            transition: color .2s;
        }
        .toggle-password:hover { color: var(--primary); }
        .invalid-msg { font-size: .8rem; color: #ef4444; margin-top: .3125rem; }

        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-check-label { font-size: .875rem; color: var(--text-secondary); cursor: pointer; }
        .forgot-link { font-size: .875rem; color: var(--primary); font-weight: 500; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            height: 2.875rem;
            background: linear-gradient(135deg, #1488f5 0%, #0f6fd4 50%, #0c5cb5 100%);
            border: none;
            border-radius: var(--radius-sm);
            color: #fff;
            font-size: .9375rem;
            font-weight: 600;
            letter-spacing: .01em;
            width: 100%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: opacity .15s, transform .1s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(19,127,236,.38), 0 1px 4px rgba(19,127,236,.2);
        }
        .btn-login:hover  { opacity: .93; box-shadow: 0 6px 22px rgba(19,127,236,.48); }
        .btn-login:active { transform: scale(.985); }

        .alert-error {
            background: rgba(239,68,68,.06);
            border: 1px solid rgba(239,68,68,.18);
            border-left: 3px solid #ef4444;
            border-radius: var(--radius-sm);
            padding: .75rem 1rem;
            font-size: .875rem;
            color: #b91c1c;
            display: flex; align-items: flex-start; gap: .5rem;
            margin-bottom: 1.375rem;
        }
        .alert-error .material-symbols-outlined { font-size: 1.0625rem; flex-shrink: 0; margin-top: .0625rem; }

        .divider {
            display: flex; align-items: center; gap: .75rem;
            margin: 1.75rem 0 1.375rem;
            color: var(--text-muted);
            font-size: .8125rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .role-badges { display: flex; justify-content: center; gap: .5rem; flex-wrap: wrap; }
        .role-badge {
            font-size: .75rem; font-weight: 500;
            padding: .25rem .875rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            background: #f8fafc;
        }

        .card-footer-note {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: .8125rem;
            color: var(--text-muted);
        }

        @media (max-width: 960px) {
            .login-brand { display: none; }
            .login-form-panel { background: var(--brand-dark); }
            .login-form-panel::before { display: none; }
            .login-card { max-width: 440px; }
        }
        @media (max-width: 480px) {
            .login-form-panel { padding: 1.25rem 1rem; align-items: flex-start; padding-top: 2rem; }
            .login-card { padding: 2rem 1.5rem 1.75rem; border-radius: var(--radius-md); }
        }
    </style>
</head>
<body>

<div class="login-root">

    {{-- ── LEFT BRAND PANEL ── --}}
    <aside class="login-brand">
        <div class="brand-bg-layer"></div>
        <div class="brand-glow-2"></div>
        <div class="brand-accent-line"></div>

        <div class="brand-top">
            <div class="brand-logo-wrap">
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ Storage::url($siteSettings['site_logo']) }}"
                         alt="{{ $siteSettings['site_name'] ?? 'Logo' }}"
                         class="brand-logo-img">
                @else
                    <div class="brand-logo-fallback">
                        <span class="material-symbols-outlined">token</span>
                    </div>
                    <span class="brand-site-name">{{ $siteSettings['site_name'] ?? 'HR Portal' }}</span>
                @endif
            </div>

            <h2 class="brand-headline">
                Your workforce,<br>
                <span class="accent">fully in control.</span>
            </h2>
            <p class="brand-tagline">
                Manage attendance, payroll, leaves, and your entire team — all in one modern platform built for growing organisations.
            </p>

            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-icon"><span class="material-symbols-outlined">schedule</span></div>
                    <span class="brand-feature-text">Real-time attendance tracking</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon"><span class="material-symbols-outlined">payments</span></div>
                    <span class="brand-feature-text">Automated payroll processing</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon"><span class="material-symbols-outlined">event_note</span></div>
                    <span class="brand-feature-text">Smart leave management</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon"><span class="material-symbols-outlined">bar_chart</span></div>
                    <span class="brand-feature-text">Insightful HR analytics</span>
                </div>
            </div>
        </div>

        <div class="brand-footer">
            &copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'HR Portal' }}. All rights reserved.
        </div>
    </aside>

    {{-- ── RIGHT FORM PANEL ── --}}
    <main class="login-form-panel">
        <div class="login-card">

            <div class="card-logo-wrap">
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ Storage::url($siteSettings['site_logo']) }}"
                         alt="{{ $siteSettings['site_name'] ?? 'Logo' }}"
                         class="card-logo-img">
                @else
                    <div class="card-logo-fallback">
                        <span class="material-symbols-outlined">token</span>
                    </div>
                @endif
                <span class="card-logo-name">{{ $siteSettings['site_name'] ?? 'HR Portal' }}</span>
                <span class="card-logo-tagline">Attendance &amp; Payroll System</span>
            </div>

            <h1 class="login-title">Welcome back</h1>
            <p class="login-subtitle">Sign in to your {{ $siteSettings['site_name'] ?? 'HR Portal' }} account</p>

            @if ($errors->any())
                <div class="alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @elseif (session('error'))
                <div class="alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email / Username / Employee ID</label>
                    <div class="field-wrap">
                        <span class="material-symbols-outlined field-icon">badge</span>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="email, username or employee ID"
                            autocomplete="username"
                            autofocus
                            required
                        >
                    </div>
                    @error('email')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="field-wrap has-toggle">
                        <span class="material-symbols-outlined field-icon">lock</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()" title="Show / hide password">
                            <span class="material-symbols-outlined" id="eye-icon">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">
                    <span class="material-symbols-outlined" style="font-size:1.125rem">login</span>
                    Sign In
                </button>

            </form>

            <div class="divider">Single sign-in for all roles</div>

            <div class="role-badges">
                <span class="role-badge">Admin</span>
                <span class="role-badge">Manager</span>
                <span class="role-badge">Employee</span>
            </div>

            <div class="card-footer-note">
                &copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'HR Portal' }} &mdash; Attendance &amp; Payroll
            </div>

        </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>
</body>
</html>
