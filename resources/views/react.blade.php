<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MediTrack - Medical Case Management | @Jajal Medical</title>

    <!-- Google Fonts: Inter and JetBrains Mono for Coinbase design system -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Coinbase Design System Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/coinbase-theme.css') }}">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="react-app" data-user="{{ Auth::user() ? json_encode(['id' => Auth::user()->id, 'name' => Auth::user()->name, 'email' => Auth::user()->email, 'role' => Auth::user()->role]) : '' }}"></div>
</body>
</html>
