@extends('layouts.app')

@section('title', 'Change Password')

@section('content')

<style>
    .cp-header {
        margin-bottom: 28px;
    }
    .cp-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }
    .cp-header p {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 0;
    }

    .cp-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        max-width: 520px;
    }
    .cp-card-header {
        padding: 20px 24px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .cp-card-icon {
        width: 42px; height: 42px;
        background: var(--primary-subtle);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
        flex-shrink: 0;
    }
    .cp-card-icon .material-symbols-outlined { font-size: 22px; }
    .cp-card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
    }
    .cp-card-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }
    .pw-input-wrap {
        position: relative;
    }
    .pw-input-wrap input {
        width: 100%;
        padding: 10px 40px 10px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 14px;
        color: var(--text-main);
        background: var(--surface);
        outline: none;
        transition: border-color .15s;
    }
    .pw-input-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle);
    }
    .pw-input-wrap input.is-invalid {
        border-color: #dc2626;
    }
    .pw-toggle {
        position: absolute;
        right: 10px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none; padding: 0;
        cursor: pointer;
        color: var(--text-muted);
        display: flex; align-items: center;
    }
    .pw-toggle:hover { color: var(--text-secondary); }
    .pw-toggle .material-symbols-outlined { font-size: 18px; }
    .invalid-feedback {
        display: block;
        font-size: 12px;
        color: #dc2626;
        margin-top: 5px;
    }

    /* Password strength bar */
    .pw-strength { margin-top: 8px; }
    .pw-strength-bar {
        height: 4px;
        background: var(--border);
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 4px;
    }
    .pw-strength-fill {
        height: 100%;
        border-radius: 999px;
        width: 0;
        transition: width .25s, background .25s;
    }
    .pw-strength-label {
        font-size: 11px;
        font-weight: 600;
    }

    /* Requirements checklist */
    .pw-reqs {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        margin-top: 10px;
    }
    .pw-reqs-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .pw-req {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 5px;
    }
    .pw-req:last-child { margin-bottom: 0; }
    .pw-req .req-icon {
        font-size: 15px;
        color: var(--text-muted);
        transition: color .15s;
    }
    .pw-req.met .req-icon { color: #15803d; }
    .pw-req.met { color: var(--text-main); }

    .cp-divider {
        border: none;
        border-top: 1px solid var(--border);
        margin: 4px 0 20px;
    }

    .btn-change-pw {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-change-pw:hover { background: var(--primary-hover); }
    .btn-change-pw .material-symbols-outlined { font-size: 18px; }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        background: var(--surface);
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: background .15s;
    }
    .btn-cancel:hover { background: var(--bg-light); color: var(--text-main); }

    .alert-success-cp {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: var(--radius-sm);
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        font-weight: 500;
        color: #15803d;
    }
    .alert-success-cp .material-symbols-outlined { font-size: 18px; }
</style>

<div class="cp-header">
    <h1>Change Password</h1>
    <p>Keep your account secure by using a strong, unique password.</p>
</div>

@if(session('success'))
    <div class="alert-success-cp" style="max-width:520px;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
@endif

<div class="cp-card">
    <div class="cp-card-header">
        <div class="cp-card-icon">
            <span class="material-symbols-outlined">lock</span>
        </div>
        <div class="cp-card-title">Update Password</div>
    </div>

    <div class="cp-card-body">
        <form method="POST" action="{{ route('password.change.post') }}" id="cpForm">
            @csrf

            {{-- Current Password --}}
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="pw-input-wrap">
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           placeholder="Enter current password"
                           autocomplete="current-password"
                           class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                           required>
                    <button type="button" class="pw-toggle" onclick="togglePw('current_password', this)" tabindex="-1">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                @error('current_password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <hr class="cp-divider">

            {{-- New Password --}}
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="pw-input-wrap">
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           placeholder="Enter new password"
                           autocomplete="new-password"
                           class="{{ $errors->has('new_password') ? 'is-invalid' : '' }}"
                           oninput="checkStrength(this.value)"
                           required>
                    <button type="button" class="pw-toggle" onclick="togglePw('new_password', this)" tabindex="-1">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                @error('new_password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror

                {{-- Strength bar --}}
                <div class="pw-strength">
                    <div class="pw-strength-bar">
                        <div class="pw-strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="pw-strength-label" id="strengthLabel" style="color:var(--text-muted)"></span>
                </div>

                {{-- Requirements --}}
                <div class="pw-reqs" id="pwReqs">
                    <div class="pw-reqs-title">Password must include</div>
                    <div class="pw-req" id="req-len">
                        <span class="material-symbols-outlined req-icon">radio_button_unchecked</span>
                        At least 8 characters
                    </div>
                    <div class="pw-req" id="req-upper">
                        <span class="material-symbols-outlined req-icon">radio_button_unchecked</span>
                        At least 1 uppercase letter (A–Z)
                    </div>
                    <div class="pw-req" id="req-lower">
                        <span class="material-symbols-outlined req-icon">radio_button_unchecked</span>
                        At least 1 lowercase letter (a–z)
                    </div>
                    <div class="pw-req" id="req-digit">
                        <span class="material-symbols-outlined req-icon">radio_button_unchecked</span>
                        At least 1 number (0–9)
                    </div>
                    <div class="pw-req" id="req-special">
                        <span class="material-symbols-outlined req-icon">radio_button_unchecked</span>
                        At least 1 special character (@$!%*#?&amp;)
                    </div>
                </div>
            </div>

            {{-- Confirm New Password --}}
            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <div class="pw-input-wrap">
                    <input type="password"
                           id="new_password_confirmation"
                           name="new_password_confirmation"
                           placeholder="Re-enter new password"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="pw-toggle" onclick="togglePw('new_password_confirmation', this)" tabindex="-1">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:10px; align-items:center; margin-top:24px;">
                <button type="submit" class="btn-change-pw">
                    <span class="material-symbols-outlined">lock_reset</span>
                    Change Password
                </button>
                <a href="{{ route('profile') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePw(inputId, btn) {
    const inp = document.getElementById(inputId);
    const icon = btn.querySelector('.material-symbols-outlined');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        inp.type = 'password';
        icon.textContent = 'visibility';
    }
}

function setReq(id, met) {
    const el = document.getElementById(id);
    const icon = el.querySelector('.material-symbols-outlined');
    if (met) {
        el.classList.add('met');
        icon.textContent = 'check_circle';
    } else {
        el.classList.remove('met');
        icon.textContent = 'radio_button_unchecked';
    }
}

function checkStrength(val) {
    const len     = val.length >= 8;
    const upper   = /[A-Z]/.test(val);
    const lower   = /[a-z]/.test(val);
    const digit   = /[0-9]/.test(val);
    const special = /[@$!%*#?&]/.test(val);

    setReq('req-len',     len);
    setReq('req-upper',   upper);
    setReq('req-lower',   lower);
    setReq('req-digit',   digit);
    setReq('req-special', special);

    const score = [len, upper, lower, digit, special].filter(Boolean).length;
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    const levels = [
        { pct: 0,   color: '',        text: '' },
        { pct: 20,  color: '#dc2626', text: 'Very Weak' },
        { pct: 40,  color: '#ea580c', text: 'Weak' },
        { pct: 60,  color: '#ca8a04', text: 'Fair' },
        { pct: 80,  color: '#16a34a', text: 'Strong' },
        { pct: 100, color: '#15803d', text: 'Very Strong' },
    ];

    const lvl = levels[score];
    fill.style.width     = lvl.pct + '%';
    fill.style.background = lvl.color;
    label.textContent    = lvl.text;
    label.style.color    = lvl.color;
}
</script>

@endsection
