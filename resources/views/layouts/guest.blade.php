<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BOB-AGS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Figtree', sans-serif; }
            .bg-navy-900 { background-color: #0A1628; }
            .text-navy-900 { color: #0A1628; }
            .border-navy-900 { border-color: #0A1628; }
            .hover\:bg-navy-800:hover { background-color: #1A2640; }
            .focus\:border-navy-900:focus { border-color: #0A1628; }
            .focus\:ring-navy-900:focus { --tw-ring-color: #0A1628; }
            .hover\:text-navy-900:hover { color: #0A1628; }
            .active\:bg-navy-900:active { background-color: #0A1628; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-navy-900 min-h-screen">
        <div class="min-h-screen flex">
            <!-- Left Side - Logo & Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-navy-900 flex-col justify-center items-center p-12">
                <div class="text-center">
                    <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB Logo" class="h-80 w-auto mb-8 mx-auto">
                    <p class="text-xl text-blue-200 mb-6">AI-Powered Call Quality Assurance</p>
                    <p class="text-gray-300 max-w-md mx-auto leading-relaxed">
                        Evaluates substance abuse helpline calls against a structured 25-criterion rubric with AI-powered analysis.
                    </p>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
                <div class="w-full sm:max-w-md">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB Logo" class="h-20 w-auto mx-auto mb-4">
                    </div>

                    <div class="bg-white shadow-lg overflow-hidden sm:rounded-lg border border-gray-100">
                        <div class="px-8 py-8">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
