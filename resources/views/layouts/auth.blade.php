<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login - AzzaOps')</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="text-center mb-4">
            <div class="auth-brand-icon mb-3">
                <i class="bi bi-tools"></i>
            </div>
            <h2 class="fw-bold mb-1">AzzaOps</h2>
            <p class="text-muted mb-0">Field Service Management</p>
        </div>
        <div class="card auth-card border-0">
            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
        <p class="text-center text-muted small mt-4 mb-0">&copy; {{ date('Y') }} PT. Azza Karunia Jaya</p>
    </div>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
