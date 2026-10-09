<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MediTrack - Medical Case Management' }} | @Jajal Medical</title>

    <!-- Google Fonts: Inter and JetBrains Mono for Coinbase design system -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Coinbase Design System Theme -->
    <link rel="stylesheet" href="{{ asset('css/coinbase-theme.css') }}">
</head>
<body>
    @auth
    <!-- Top Navigation Bar -->
    <header class="top-nav">
        <div class="app-container">
            <div class="nav-inner">
                <a href="{{ route('dashboard') }}" class="brand-logo" id="nav-brand-logo">
                    <span class="brand-icon">M</span>
                    <span>MediTrack <span style="font-size: 11px; font-weight: 500; color: var(--color-muted); text-transform: uppercase; margin-left: 4px;">@Jajal Medical</span></span>
                </a>

                <nav>
                    <ul class="nav-links">
                        <li>
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-link-dashboard">Dashboard</a>
                        </li>
                        <li>
                            <a href="{{ route('cases.index') }}" class="nav-link {{ request()->routeIs('cases.index') ? 'active' : '' }}" id="nav-link-cases">Cases</a>
                        </li>
                        @if(Auth::user()->isAdmin() || Auth::user()->isEngineer())
                        <li>
                            <a href="{{ route('cases.create') }}" class="btn btn-primary btn-sm" id="nav-btn-create-case" style="height: 36px; padding: 0 16px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>New Case</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-profile-pill">
                        <span style="font-weight: 600; color: var(--color-ink); font-size: 13px;">{{ Auth::user()->name }}</span>
                        <span class="badge badge-role-{{ Auth::user()->role }}">
                            {{ ucfirst(Auth::user()->role) }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" id="btn-logout" title="Sign Out">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    @endauth

    <!-- Main Content Area -->
    <main style="flex: 1; padding: 36px 0;">
        <div class="app-container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success" id="alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" id="alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any() && !request()->routeIs('login'))
                <div class="alert alert-danger">
                    <span>Please review and resolve the errors below before submitting.</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-bar">
        <div class="app-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong>MediTrack</strong> &bull; Medical Implant Case Management System &copy; {{ date('Y') }} Jajal Medical.
            </div>
            <div style="font-size: 12px;">
                Institutional Medical Device Tracking Platform &bull; Built with Laravel &amp; Coinbase Design System
            </div>
        </div>
    </footer>
</body>
</html>
