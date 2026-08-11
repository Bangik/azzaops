<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AzzaOps')</title>

    {{-- Bootstrap 5.3 CSS --}}
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    {{-- DataTables CSS --}}
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    {{-- Flatpickr CSS --}}
    <link href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    {{-- Custom CSS --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

    {{-- Mobile sidebar toggle --}}
    <button class="btn btn-dark d-md-none position-fixed top-0 start-0 m-2 z-3" id="sidebarToggle" type="button">
        <i class="bi bi-list fs-5"></i>
    </button>

    {{-- Sidebar --}}
    <nav id="sidebar" class="sidebar d-flex flex-column">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">
                <span class="brand-icon"><i class="bi bi-tools"></i></span>
                <span>AzzaOps</span>
            </a>
        </div>

        <ul class="nav flex-column flex-grow-1 px-2">
            <li class="nav-section-label">Operasional</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.work-orders.*') ? 'active' : '' }}"
                   href="{{ route('admin.work-orders.index') }}">
                    <i class="bi bi-clipboard-check me-2"></i>Work Order
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                   href="{{ route('admin.customers.index') }}">
                    <i class="bi bi-people me-2"></i>Customer
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}"
                   href="{{ route('admin.service-categories.index') }}">
                    <i class="bi bi-tags me-2"></i>Kategori Jasa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.work-order-types.*') ? 'active' : '' }}"
                   href="{{ route('admin.work-order-types.index') }}">
                    <i class="bi bi-gear-wide-connected me-2"></i>Tipe Pekerjaan
                </a>
            </li>
            <li class="nav-section-label">Transaksi</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}"
                   href="{{ route('admin.invoices.index') }}">
                    <i class="bi bi-receipt me-2"></i>Invoice
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rab.*') ? 'active' : '' }}"
                   href="{{ route('admin.rab.index') }}">
                    <i class="bi bi-calculator me-2"></i>RAB
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}"
                   href="{{ route('admin.finance.index') }}">
                    <i class="bi bi-cash-stack me-2"></i>Keuangan
                </a>
            </li>

            @if(auth()->user()->role->value === 'super_admin')
            <li class="nav-section-label">Administrasi</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"
                   href="{{ route('admin.staff.index') }}">
                    <i class="bi bi-person-badge me-2"></i>Staff
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}"
                   href="{{ route('admin.devices.index') }}">
                    <i class="bi bi-phone me-2"></i>Device Staff
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.financial-accounts.*') ? 'active' : '' }}"
                   href="{{ route('admin.financial-accounts.index') }}">
                    <i class="bi bi-bank me-2"></i>Akun Keuangan
                </a>
            </li>
            @endif

            @if(auth()->user()->role->value === 'super_admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.settings.index') }}">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.app-versions.*') ? 'active' : '' }}"
                   href="{{ route('admin.app-versions.index') }}">
                    <i class="bi bi-cloud-arrow-up me-2"></i>App Version
                </a>
            </li>
            @endif
        </ul>

        {{-- User info at bottom --}}
        <div class="sidebar-user">
            <div class="d-flex align-items-center text-white">
                <div class="user-avatar me-2">
                    <i class="bi bi-person"></i>
                </div>
                <div class="small lh-sm">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-white-50">{{ auth()->user()->role->label() }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- Main wrapper --}}
    <div class="main-content">
        {{-- Top navbar --}}
        <nav class="navbar navbar-expand navbar-light bg-white border-bottom px-4 py-2">
            <div>
                <h5 class="mb-0 ms-md-0 ms-4">@yield('page-title')</h5>
                <div class="text-muted small ms-md-0 ms-4">Sistem operasional lapangan yang lebih rapi dan terukur</div>
            </div>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                       data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ auth()->user()->name }}
                        <span class="badge bg-primary ms-2">{{ auth()->user()->role->label() }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        {{-- Content --}}
        <div class="page-content">
            {{-- Breadcrumb --}}
            @yield('breadcrumb')

            {{-- Flash messages --}}
            <x-alert />

            {{-- Page content --}}
            @yield('content')
        </div>
    </div>

    {{-- Sidebar overlay for mobile --}}
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    {{-- jQuery --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    {{-- Bootstrap 5.3 JS --}}
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    {{-- DataTables JS --}}
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    {{-- Flatpickr JS --}}
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/id.js') }}"></script>
    {{-- Chart.js --}}
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    {{-- Custom JS --}}
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')
</body>
</html>
