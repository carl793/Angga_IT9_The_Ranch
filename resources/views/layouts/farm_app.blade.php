<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The Ranch Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Farm Background with Opacity */
        body {
            background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=2832&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        /* Overlay for opacity control */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5); /* 50% white overlay */
            z-index: -1;
        }

        /* Professional Cards */
        .bg-white {
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        /* Sidebar */
        .sidebar { 
            width: 260px; 
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white; 
            min-height: 100vh; 
            position: fixed; 
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .main-content { 
            margin-left: 260px; 
            flex: 1; 
            padding: 2rem; 
        }
        
        .role-badge { 
            font-size: 0.65rem; 
            padding: 3px 10px; 
            border-radius: 12px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Nav Links */
        .nav-link {
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #60a5fa;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link.active::before {
            transform: scaleY(1);
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <!-- Header -->
        <div class="p-6 border-b border-slate-700">
            <h2 class="text-xl font-bold tracking-tight">The Ranch</h2>
            <p class="text-xs text-slate-400 mt-1">Inventory Management</p>
            <span class="role-badge mt-2 inline-block">{{ strtoupper(Auth::user()->role) }}</span>
        </div>

        <!-- Navigation -->
        <nav class="mt-4 space-y-1 px-3 flex-1 overflow-y-auto">
            
            <a href="{{ route('dashboard') }}" 
               class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'active bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            @if(Auth::user()->role !== 'staff')
                <div class="px-4 pt-6 pb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Management</div>
                
                <a href="{{ route('products.index') }}" 
                   class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('products.index') || request()->routeIs('categories.*') || request()->routeIs('units.*') || request()->routeIs('suppliers.*') ? 'active bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span>Product Management</span>
                </a>
            @endif

            <div class="px-4 pt-6 pb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Operations</div>

            <a href="{{ route('stock.operations') }}" 
               class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('stock.operations') ? 'active bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span>Stock Operations</span>
            </a>

            @if(Auth::user()->role !== 'staff')
                <a href="{{ route('products.archived') }}" 
                   class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('products.archived') ? 'active bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    <span>Archives</span>
                </a>
            @endif

            @if(auth()->user()->role === 'admin')
                <div class="px-4 pt-6 pb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Administration</div>
                
                <a href="{{ route('users.index') }}" 
                   class="nav-link flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('users.*') ? 'active bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>User Accounts</span>
                </a>
            @endif

        </nav>

        <!-- Logout -->
        <div class="border-t border-slate-700 p-3 mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-medium text-slate-300 rounded-lg hover:bg-red-600 hover:text-white transition-all duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>{{ Auth::user()->name }}</span>
                </button>
            </form>
        </div>
    </div>

    <main class="main-content">
        @yield('content')
    </main>

</body>
</html>
