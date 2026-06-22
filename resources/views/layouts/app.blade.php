<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Dashboard') · {{ config('app.name', 'Mi Botica') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',
                            500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body{font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        /* Scrollbar fino para el sidebar */
        .scroll-thin::-webkit-scrollbar{width:6px}
        .scroll-thin::-webkit-scrollbar-thumb{background:rgba(148,163,184,.3);border-radius:9999px}
        [x-cloak]{display:none}
    </style>
</head>
<body class="h-full bg-slate-100 text-slate-700">
<div x-data="{ open: false }" @keydown.escape.window="open = false" class="min-h-full">

    @include('partials.sidebar')

    {{-- Contenido principal --}}
    <div class="lg:pl-64 flex flex-col min-h-screen transition-all">
        @include('partials.topbar')

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @include('partials.flash')
            </div>
            @yield('contenido')
        </main>

        <footer class="px-6 py-4 text-center text-xs text-slate-400 border-t border-slate-200 bg-white/50">
            Mi Botica · Sistema de gestión farmacéutica · v1.0 — © {{ date('Y') }}
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
@stack('scripts')
</body>
</html>
