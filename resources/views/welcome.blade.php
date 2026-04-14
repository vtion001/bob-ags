<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'BOB-AGS') }} - Quality Assurance System</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Figtree', sans-serif; }
            .bg-navy-900 { background-color: #0A1628; }
            .text-navy-900 { color: #0A1628; }
            .border-navy-900 { border-color: #0A1628; }
            .hover\:bg-navy-800:hover { background-color: #1A2640; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-navy-900 min-h-screen flex flex-col">
        <!-- Header -->
        <header class="w-full py-6 px-8">
            <div class="flex items-center justify-center">
                <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1773964807/mlchltgq4cem5dfoogl3.png" alt="BOB Logo" class="h-20 w-auto">
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center px-6">
            <div class="max-w-4xl w-full text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    BOB-AGS
                </h1>
                <p class="text-xl md:text-2xl text-blue-200 mb-2">
                    Call Recording QA Analysis System
                </p>
                <p class="text-lg text-gray-300 mb-12 max-w-2xl mx-auto">
                    Advanced quality assurance for substance abuse helplines. 
                    Analyze call recordings against comprehensive rubric criteria using AI-powered transcription and analysis.
                </p>

                <!-- Feature Cards -->
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-left">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">CTM Integration</h3>
                        <p class="text-gray-300 text-sm">Seamless integration with Call Tracking Metrics for call retrieval and management.</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-left">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">AI Transcription</h3>
                        <p class="text-gray-300 text-sm">AssemblyAI-powered transcription for accurate call documentation.</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-left">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">25-Criteria Rubric</h3>
                        <p class="text-gray-300 text-sm">Comprehensive quality scoring with Zero Tolerance Policy (ZTP) auto-fail.</p>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-[#0A1628] font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        <span>Sign In</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-colors">
                        Create Account
                    </a>
                    @endif
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 px-8 text-center">
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} BOB-AGS Quality Assurance System
            </p>
        </footer>
    </body>
</html>
