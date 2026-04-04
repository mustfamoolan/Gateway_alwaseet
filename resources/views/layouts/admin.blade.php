<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Al-Waseet Gateway' }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-satellite-dish"></i>
                <span>Gateway</span>
            </div>
            
            <nav>
                <ul class="nav-links">
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'active' : '' }}">
                            <i class="fas fa-list-ul"></i>
                            <span>Request Logs</span>
                        </a>
                    </li>
                    <li style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800;">{{ $title ?? 'Admin Dashboard' }}</h1>
                    <p style="color: var(--text-gray); font-size: 0.875rem;">Welcome back, Administrator</p>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div class="ip-badge">
                        <i class="fas fa-network-wired" style="margin-right: 0.5rem;"></i>
                        Server IP: {{ $serverIp ?? 'Checking...' }}
                    </div>
                    <form action="#" method="POST">
                        @csrf
                        <button class="btn btn-primary" style="padding: 0.5rem 1rem; background: rgba(239, 68, 68, 0.1); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </header>

            @if(session('success'))
                <div class="card" style="margin-bottom: 2rem; border-color: var(--secondary); background: rgba(16, 185, 129, 0.05); color: var(--secondary); padding: 1rem;">
                    <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @yield('scripts')
</body>
</html>
