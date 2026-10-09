@extends('layouts.app', ['title' => 'Sign In'])

@section('content')
<div style="max-width: 460px; margin: 40px auto;">
    <div class="card" style="padding: 40px 36px;">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 48px; height: 48px; border-radius: 9999px; background-color: var(--color-primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin-bottom: 16px;">
                M
            </div>
            <h1 style="font-size: 26px; font-weight: 500; color: var(--color-ink); margin-bottom: 8px;">Sign in to MediTrack</h1>
            <p style="color: var(--color-muted); font-size: 14px;">Clinical Case Management &bull; @Jajal Medical</p>
        </div>

        @if($errors->has('email'))
            <div class="alert alert-danger" id="login-error-alert" style="margin-bottom: 24px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" id="login-form">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Work Email Address</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email', 'admin@jajalmedical.com') }}"
                    required
                    autocomplete="email"
                    autofocus
                    placeholder="name@jajalmedical.com"
                >
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
                </div>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    required
                    autocomplete="current-password"
                    value="Password123!"
                    placeholder="Enter password"
                >
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; font-size: 13px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--color-body);">
                    <input type="checkbox" name="remember" id="remember" style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                    <span>Keep me signed in</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-submit-login" style="width: 100%; height: 48px; font-size: 15px;">
                Sign In
            </button>
        </form>

        <!-- Reviewer Quick-Login Shortcuts -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-hairline);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-muted); margin-bottom: 12px; text-align: center;">
                Quick Test Accounts for Reviewers
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('admin@jajalmedical.com')" style="justify-content: space-between; width: 100%;">
                    <span><strong>Admin:</strong> admin@jajalmedical.com</span>
                    <span class="badge badge-role-admin">Full Access</span>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('viewer@jajalmedical.com')" style="justify-content: space-between; width: 100%;">
                    <span><strong>Viewer:</strong> viewer@jajalmedical.com</span>
                    <span class="badge badge-role-viewer">Read Only</span>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setCredentials('engineer@jajalmedical.com')" style="justify-content: space-between; width: 100%;">
                    <span><strong>Engineer:</strong> engineer@jajalmedical.com</span>
                    <span class="badge badge-role-engineer">Bonus Role</span>
                </button>
            </div>
            <p style="text-align: center; font-size: 11px; color: var(--color-muted); margin-top: 10px;">
                Default Password: <code class="mono" style="background: var(--color-surface-strong); padding: 2px 6px; border-radius: 4px;">Password123!</code>
            </p>
        </div>
    </div>
</div>

<script>
    function setCredentials(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'Password123!';
    }
</script>
@endsection
