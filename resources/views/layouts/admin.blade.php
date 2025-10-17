<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ISP Manager</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <!-- Custom Style for Hamburger -->
    <style>
        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .hamburger {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay (untuk mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h4 class="mb-0 fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-wifi"></i> ISP MANAGER
            </h4>
        </div>

        <div class="sidebar-menu">
            <div class="px-3 mb-2">
                <small class="text-muted fw-semibold">MENU</small>
            </div>

            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            @can('view_users')
            <a href="{{ route('users.index') }}" class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>User Management</span>
            </a>
            @endcan

            @can('view_customers')
            <a href="{{ route('customers.index') }}" class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Customers</span>
            </a>
            @endcan

            @can('view_packages')
            <a href="{{ route('packages.index') }}" class="menu-item {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Packages</span>
            </a>
            @endcan

            @can('view_invoices')
            <a href="{{ route('invoices.index') }}" class="menu-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Invoices</span>
            </a>
            @endcan

            @can('view_all_tickets')
            <a href="{{ route('tickets.index') }}" class="menu-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i>
                <span>Tickets</span>
            </a>
            @endcan

            <div class="px-3 mt-4 mb-2">
                <small class="text-muted fw-semibold">NETWORK</small>
            </div>

            @can('view_routers')
            <a href="{{ route('routers.index') }}" class="menu-item {{ request()->routeIs('routers.*') ? 'active' : '' }}">
                <i class="bi bi-router"></i>
                <span>Routers</span>
            </a>
            @endcan

            @can('view_olts')
            <a href="{{ route('olts.index') }}" class="menu-item {{ request()->routeIs('olts.*') ? 'active' : '' }}">
                <i class="bi bi-hdd-network"></i>
                <span>OLTs</span>
            </a>
            @endcan

            <!-- ✅ NEW: ODF -->
            <a href="{{ route('odfs.index') }}" class="menu-item {{ request()->routeIs('odfs.*') ? 'active' : '' }}">
                <i class="bi bi-columns"></i>
                <span>ODF (Patch Panel)</span>
            </a>

            <!-- ✅ NEW: ODC -->
            <a href="{{ route('odcs.index') }}" class="menu-item {{ request()->routeIs('odcs.*') ? 'active' : '' }}">
                <i class="bi bi-server"></i>
                <span>ODC (Cabinet)</span>
            </a>

            <a href="{{ route('splitters.index') }}" class="menu-item {{ request()->routeIs('splitters.*') ? 'active' : '' }}">
                <i class="bi bi-shuffle"></i>
                <span>Splitters</span>
            </a>

            <a href="{{ route('odps.index') }}" class="menu-item {{ request()->routeIs('odps.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i>
                <span>ODP (Distribution)</span>
            </a>

            <a href="{{ route('onts.index') }}" class="menu-item {{ request()->routeIs('onts.*') ? 'active' : '' }}">
                <i class="bi bi-modem"></i>
                <span>ONTs (Customer)</span>
            </a>

            <a href="{{ route('switches.index') }}" class="menu-item {{ request()->routeIs('switches.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span>Switches</span>
            </a>

            <a href="{{ route('access-points.index') }}" class="menu-item {{ request()->routeIs('access-points.*') ? 'active' : '' }}">
                <i class="bi bi-wifi"></i>
                <span>Access Points</span>
            </a>

            <!-- ✅ UPDATED: Fiber Infrastructure Submenu -->
            <div class="menu-item" style="cursor: pointer;" onclick="toggleSubmenu('fiberInfra')">
                <i class="bi bi-bezier2"></i>
                <span>Fiber Infrastructure</span>
                <i class="bi bi-chevron-down ms-auto" id="fiberInfraChevron"></i>
            </div>

            <div id="fiberInfraSubmenu" style="display: none; padding-left: 1rem;">
                <a href="{{ route('joint-boxes.index') }}" class="menu-item {{ request()->routeIs('joint-boxes.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Joint Boxes</span>
                </a>

                <a href="{{ route('cable-segments.index') }}" class="menu-item {{ request()->routeIs('cable-segments.*') ? 'active' : '' }}">
                    <i class="bi bi-bezier"></i>
                    <span>Cable Segments</span>
                </a>

                <a href="{{ route('cores.index') }}" class="menu-item {{ request()->routeIs('cores.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-2"></i>
                    <span>Fiber Cores</span>
                </a>

                <!-- ✅ NEW: Fiber Splices -->
                <a href="{{ route('fiber-splices.index') }}" class="menu-item {{ request()->routeIs('fiber-splices.*') ? 'active' : '' }}">
                    <i class="bi bi-link-45deg"></i>
                    <span>Fiber Splices</span>
                </a>

                <!-- ✅ NEW: Test Results -->
                <a href="{{ route('fiber-test-results.index') }}" class="menu-item {{ request()->routeIs('fiber-test-results.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i>
                    <span>Test Results (OTDR)</span>
                </a>
            </div>

            <div class="menu-item" style="cursor: pointer;" onclick="toggleSubmenu('acsMenu')">
                <i class="bi bi-sliders"></i>
                <span>ACS Management</span>
                @php
                    $alertCount = \App\Models\AcsAlert::where('status', 'new')->count();
                @endphp
                @if($alertCount > 0)
                    <span class="badge bg-danger">{{ $alertCount }}</span>
                @endif
                <i class="bi bi-chevron-down ms-auto" id="acsMenuChevron"></i>
            </div>

            <div id="acsMenuSubmenu" style="display: {{ request()->routeIs('acs.*') ? 'block' : 'none' }}; padding-left: 1rem;">
                <a href="{{ route('acs.index') }}" class="menu-item {{ request()->routeIs('acs.index') ? 'active' : '' }}">
                    <i class="bi bi-hdd-network"></i>
                    <span>Devices</span>
                </a>

                <a href="{{ route('acs.templates.index') }}" class="menu-item {{ request()->routeIs('acs.templates.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text"></i>
                    <span>Templates</span>
                </a>

                <a href="{{ route('acs.bulk.index') }}" class="menu-item {{ request()->routeIs('acs.bulk.*') ? 'active' : '' }}">
                    <i class="bi bi-stack"></i>
                    <span>Bulk Operations</span>
                </a>

                <a href="{{ route('acs.alerts.index') }}" class="menu-item {{ request()->routeIs('acs.alerts.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i>
                    <span>Alerts</span>
                    @if($alertCount > 0)
                        <span class="badge bg-danger ms-2">{{ $alertCount }}</span>
                    @endif
                </a>

                <a href="{{ route('acs.alert-rules.index') }}" class="menu-item {{ request()->routeIs('acs.alert-rules.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span>Alert Rules</span>
                </a>

                <a href="{{ route('acs.provisioning.queue') }}" class="menu-item {{ request()->routeIs('acs.provisioning.*') ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split"></i>
                    <span>Provisioning Queue</span>
                    @php
                        $queueCount = \App\Models\AcsProvisioningQueue::where('status', 'pending')->count();
                    @endphp
                    @if($queueCount > 0)
                        <span class="badge bg-info ms-2">{{ $queueCount }}</span>
                    @endif
                </a>

                <a href="{{ route('acs.statistics') }}" class="menu-item {{ request()->routeIs('acs.statistics') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>
                    <span>Statistics</span>
                </a>

                <a href="{{ route('acs.unprovisioned') }}" class="menu-item {{ request()->routeIs('acs.unprovisioned') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Unprovisioned</span>
                </a>
            </div>


            <!-- ✅ NEW: Network Topology -->
            <a href="{{ route('network.topology') }}" class="menu-item {{ request()->routeIs('network.topology') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span>Network Topology</span>
            </a>

            <a href="{{ route('map.index') }}" class="menu-item {{ request()->routeIs('map.*') ? 'active' : '' }}">
                <i class="bi bi-map"></i>
                <span>Network Map</span>
            </a>

            <div class="px-3 mt-4 mb-2">
                <small class="text-muted fw-semibold">LAPORAN</small>
            </div>

            @can('view_financial_reports')
            <a href="{{ route('reports.index') }}" class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart"></i>
                <span>Reports</span>
            </a>
            @endcan

            <div class="px-3 mt-4 mb-2">
                <small class="text-muted fw-semibold">PENGATURAN</small>
            </div>

            @can('view_settings')
            <a href="#" class="menu-item">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
            @endcan

            <a href="{{ route('profile.edit') }}" class="menu-item">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="menu-item border-0 bg-transparent w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <!-- Hamburger Menu (Mobile) -->
                <button class="hamburger" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0 fw-bold">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted d-none d-md-inline">{{ auth()->user()->getRoleNames()->first() }}</span>
                <div class="d-flex align-items-center gap-2">
                    @if(auth()->user()->photo)
                        <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="User" class="rounded-circle" width="40" height="40">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="d-none d-md-block">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <small class="text-muted">{{ strtoupper(auth()->user()->getRoleNames()->first()) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    @stack('scripts')

    <!-- Main Scripts - HANYA SATU KALI -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Toggle sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Hamburger clicked'); // Debug
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }

            // Close sidebar when overlay clicked
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Close sidebar when menu item clicked (mobile only)
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });
        });

        // Toggle submenu function
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById(menuId + 'Submenu');
            const chevron = document.getElementById(menuId + 'Chevron');

            if (submenu && chevron) {
                if (submenu.style.display === 'none' || submenu.style.display === '') {
                    submenu.style.display = 'block';
                    chevron.classList.remove('bi-chevron-down');
                    chevron.classList.add('bi-chevron-up');
                } else {
                    submenu.style.display = 'none';
                    chevron.classList.remove('bi-chevron-up');
                    chevron.classList.add('bi-chevron-down');
                }
            }
        }

        // Auto expand if current route is in submenu
        document.addEventListener('DOMContentLoaded', function() {
            const currentRoute = '{{ request()->route()->getName() }}';

            // Fiber Infrastructure submenu routes
            const fiberRoutes = [
                'joint-boxes.',
                'cable-segments.',
                'cores.',
                'fiber-splices.',      // ✅ NEW
                'fiber-test-results.'  // ✅ NEW
            ];

            if (fiberRoutes.some(route => currentRoute.startsWith(route))) {
                const submenu = document.getElementById('fiberInfraSubmenu');
                const chevron = document.getElementById('fiberInfraChevron');
                if (submenu && chevron) {
                    submenu.style.display = 'block';
                    chevron.classList.remove('bi-chevron-down');
                    chevron.classList.add('bi-chevron-up');
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
