<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="Knowledge Hub - Repositori Karya Ilmiah dan Publikasi Riset Universitas">
    <title>@yield('title', 'Workspace Publikasi') - {{ config('app.name', 'Knowledge Hub') }}</title>

    <!-- Google Font: Public Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/iconify-icons.css') }}">

    <!-- Core CSS (Vuexy Bootstrap 5) -->
    <link rel="stylesheet" href="{{ asset('assets/css/core.css') }}">

    <style>
        :root {
            --bs-primary: #7367F0;
            --bs-primary-rgb: 115, 103, 240;
            --bs-success: #28C76F;
            --bs-warning: #FF9F43;
            --bs-danger: #EA5455;
            --bs-info: #00CFE8;
            --bs-body-bg: #F8F7FA;
            --bs-body-color: #6F6B7D;
            --bs-heading-color: #4B465C;
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .layout-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Scoped Vertical Sidebar */
        .app-sidebar {
            width: 260px;
            min-width: 260px;
            max-width: 260px;
            background: #FFFFFF;
            border-right: 1px solid #DBDADE;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.2s ease-in-out;
        }

        .sidebar-brand {
            padding: 1.25rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid #F1F0F2;
        }

        .sidebar-brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #7367F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.25rem;
            box-shadow: 0 4px 8px rgba(115, 103, 240, 0.25);
            flex-shrink: 0;
        }

        .sidebar-brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: #4B465C;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .sidebar-brand-subtitle {
            font-size: 0.72rem;
            color: #8C869B;
            font-weight: 500;
        }

        /* Force vertical layout for nav list */
        .sidebar-nav {
            display: flex !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
            list-style: none !important;
            padding: 1rem 0.75rem !important;
            margin: 0 !important;
            gap: 0.25rem !important;
            overflow-y: auto;
            flex-grow: 1;
        }

        .sidebar-nav-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8C869B;
            padding: 0.75rem 0.75rem 0.25rem 0.75rem;
            margin-top: 0.5rem;
            display: block !important;
            width: 100% !important;
        }

        .sidebar-nav-item {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
        }

        .sidebar-nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            padding: 0.65rem 0.85rem !important;
            border-radius: 6px !important;
            color: #4B465C !important;
            font-weight: 500 !important;
            font-size: 0.9rem !important;
            text-decoration: none !important;
            width: 100% !important;
            transition: all 0.15s ease-in-out;
        }

        .sidebar-nav-link:hover {
            background-color: #F8F7FA !important;
            color: #7367F0 !important;
        }

        .sidebar-nav-link.active {
            background: #7367F0 !important;
            color: #FFFFFF !important;
            box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
        }

        .sidebar-nav-link.active i {
            color: #FFFFFF !important;
        }

        .sidebar-nav-icon {
            font-size: 1.2rem;
            color: #8C869B;
            flex-shrink: 0;
        }

        /* Layout Page & Content */
        .layout-page {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background-color: var(--bs-body-bg);
        }

        /* Top Navbar */
        .layout-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid #DBDADE;
            padding: 0.75rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 990;
        }

        .avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(115, 103, 240, 0.12);
            color: #7367F0;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .content-wrapper {
            padding: 1.25rem 1.75rem 2rem 1.75rem;
            flex-grow: 1;
        }

        .layout-footer {
            background: #FFFFFF;
            border-top: 1px solid #DBDADE;
            padding: 0.85rem 1.75rem;
            color: #8C869B;
            font-size: 0.8rem;
            margin-top: auto;
        }

        /* Clean Status Badges */
        .badge-status-pending {
            background-color: rgba(255, 159, 67, 0.12);
            color: #B26A00;
            font-weight: 600;
            border: 1px solid rgba(255, 159, 67, 0.25);
        }

        .badge-status-approved {
            background-color: rgba(40, 199, 111, 0.12);
            color: #1B7A43;
            font-weight: 600;
            border: 1px solid rgba(40, 199, 111, 0.25);
        }

        .badge-status-rejected {
            background-color: rgba(234, 84, 85, 0.12);
            color: #B52B2C;
            font-weight: 600;
            border: 1px solid rgba(234, 84, 85, 0.25);
        }

        .badge-category {
            background-color: rgba(0, 207, 232, 0.12);
            color: #007A8C;
            font-weight: 600;
        }

        /* Mobile Backdrop Overlay */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(47, 43, 61, 0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        .sidebar-backdrop.show {
            display: block;
        }

        /* Card Customizations */
        .card {
            border: 1px solid #DBDADE;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(47, 43, 61, 0.04);
            background-color: #FFFFFF;
        }

        /* Accessible focus ring (R-32) */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #7367F0;
            outline-offset: 2px;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                position: fixed;
                left: -260px;
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
            }
            .app-sidebar.show {
                left: 0;
            }
            .content-wrapper {
                padding: 1rem 1rem 1.5rem 1rem;
            }
            .layout-footer {
                padding: 0.75rem 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="layout-wrapper">
        <!-- Mobile Backdrop -->
        <div id="sidebar-backdrop" class="sidebar-backdrop" aria-hidden="true"></div>

        <!-- Vertical Sidebar Menu -->
        <aside id="layout-sidebar" class="app-sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-logo">
                    <i class="icon-base ti tabler-books"></i>
                </div>
                <div>
                    <div class="sidebar-brand-text">Knowledge Hub</div>
                    <div class="sidebar-brand-subtitle">Repositori Riset Sivitas</div>
                </div>
            </div>

            <ul class="sidebar-nav">
                <li class="sidebar-nav-header">Workspace</li>
                
                <li class="sidebar-nav-item">
                    <a href="{{ route('publications.index') }}" class="sidebar-nav-link {{ request()->routeIs('publications.*') ? 'active' : '' }}">
                        <i class="sidebar-nav-icon icon-base ti tabler-files"></i>
                        <span>Naskah Publikasi</span>
                    </a>
                </li>

                <li class="sidebar-nav-header">Repositori</li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('home') }}" class="sidebar-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="sidebar-nav-icon icon-base ti tabler-search"></i>
                        <span>Katalog Publikasi</span>
                    </a>
                </li>

                @if (Auth::user()?->isAdminLPPM())
                    <li class="sidebar-nav-header">LPPM</li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.review.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                            <i class="sidebar-nav-icon icon-base ti tabler-checklist"></i>
                            <span>Verifikasi Naskah</span>
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Sidebar Footer: User Identity & Logout -->
            <div class="p-3 border-top bg-light">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="avatar-initials">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="text-truncate">
                            <div class="fw-semibold text-dark text-truncate" style="font-size: 0.82rem;">{{ Auth::user()->name ?? 'Pengguna SSO' }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.72rem;">{{ Auth::user()->role?->label() ?? 'Dosen' }}</div>
                        </div>
                    </div>
                    <form action="{{ route('auth.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Keluar dari Sistem" aria-label="Keluar dari Sistem">
                            <i class="icon-base ti tabler-logout fs-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Layout Page -->
        <div class="layout-page">
            <!-- Top Navbar -->
            <header class="layout-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-icon btn-outline-secondary d-lg-none" type="button" id="sidebar-toggle" aria-label="Buka Menu Navigasi">
                        <i class="icon-base ti tabler-menu-2"></i>
                    </button>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('publications.index') }}" class="text-muted text-decoration-none">Knowledge Hub</a></li>
                            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">@yield('title', 'Workspace')</li>
                        </ol>
                    </nav>
                </div>

                <!-- Clean User Info Header -->
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold text-dark" style="font-size: 0.875rem;">{{ Auth::user()->name ?? 'Dosen Pengusul' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            {{ Auth::user()->faculty ?? 'Fakultas Riset' }}
                        </div>
                    </div>
                    <div class="avatar-initials d-none d-sm-flex">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content-wrapper">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-3 py-2 px-3" role="alert">
                        <i class="icon-base ti tabler-circle-check fs-5"></i>
                        <div class="flex-grow-1 small">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-3 py-2 px-3" role="alert">
                        <i class="icon-base ti tabler-alert-triangle fs-5"></i>
                        <div class="flex-grow-1 small">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-3 py-2 px-3" role="alert">
                        <i class="icon-base ti tabler-info-circle fs-5"></i>
                        <div class="flex-grow-1 small">{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Clean App Footer -->
            <footer class="layout-footer">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                    <div>
                        <span>&copy; {{ date('Y') }} <strong>{{ config('app.institution_name', 'Universitas') }}</strong> &bull; Repositori Karya Ilmiah & Riset Sivitas Akademika</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span>Knowledge Hub</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('layout-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (backdrop) backdrop.classList.add('show');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar && sidebar.classList.contains('show')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
