<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BOB-AGS') }} - @yield('title', 'Dashboard')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Figtree', sans-serif; }
            .bg-navy-900 { background-color: #0A1628; }
            .bg-navy-800 { background-color: #1A2640; }
            .border-navy-700 { border-color: #1A2640; }
            .text-navy-900 { color: #0A1628; }
            .border-navy-900 { border-color: #0A1628; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <link rel="stylesheet" href="{{ asset('build/assets/app-I06scgRA.css') }}">
        <link rel="stylesheet" href="{{ asset('build/assets/app-Qn_Qb4EM.css') }}">
        <script type="module" src="{{ asset('build/assets/app-Cxmlabc5.js') }}"></script>
        <div class="flex h-screen">
            <!-- Sidebar -->
            <aside class="w-64 bg-navy-900 text-white flex flex-col fixed h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-navy-700">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB Logo" class="h-10 w-auto mr-3">
                        <span class="font-bold text-xl">BOB-AGS</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 py-6 overflow-y-auto">
                    <div class="px-4 space-y-1">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('calls.index') }}" class="{{ request()->routeIs('calls.*') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Calls
                        </a>

                        <a href="{{ route('qa.logs') }}" class="{{ request()->routeIs('qa.*') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            QA Logs
                        </a>

                        <!-- Live Monitoring Section -->
                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Live Monitoring</p>
                        </div>

                        <a href="{{ route('live-monitoring.index') }}" class="{{ request()->routeIs('live-monitoring*') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Agent View
                        </a>

                        @can('access-supervisor')
                        <a href="{{ route('supervisor') }}" class="{{ request()->routeIs('supervisor*') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Supervisor View
                        </a>
                        @endcan

                        <!-- Admin Section -->
                        @can('manage-settings')
                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administration</p>
                        </div>

                        <a href="{{ route('knowledge-base.index') }}" class="{{ request()->routeIs('knowledge-base*') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Knowledge Base
                        </a>
                        @endcan

                        <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'bg-navy-800 text-white' : 'text-gray-300 hover:bg-navy-800 hover:text-white' }} flex items-center px-4 py-3 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </a>
                    </div>
                </nav>

                <!-- User & Logout -->
                <div class="p-4 border-t border-navy-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-semibold">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'User' }}</p>
                                <p class="text-xs text-gray-400">{{ Auth::user()->role ?? 'viewer' }}</p>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-navy-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 ml-64 overflow-y-auto">
                <!-- Page Header -->
                @isset($header)
                    <header class="bg-white shadow-sm border-b border-gray-200">
                        <div class="px-8 py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <div class="p-8">
                    @yield('content')
                </div>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 py-4 px-8 mt-auto">
                    <div class="text-center text-sm text-gray-500">
                        <p>&copy; {{ date('Y') }} BOB-AGS. All rights reserved.</p>
                    </div>
                </footer>
            </main>
        </div>
    </body>
</html>
