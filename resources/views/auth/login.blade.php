<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión · {{ config('app.name', 'Mi Botica') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#ecfdf5',100:'#d1fae5',400:'#34d399',500:'#10b981',
                            600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',ui-sans-serif,system-ui,sans-serif}</style>
</head>
<body class="h-full bg-slate-100">
    <div class="min-h-full flex">
        {{-- Panel de marca --}}
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-slate-900 via-slate-800 to-brand-800 text-white p-12 flex-col justify-between overflow-hidden">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 w-96 h-96 bg-brand-400/10 rounded-full blur-3xl"></div>
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-brand-500 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                    </svg>
                </div>
                <span class="text-2xl font-extrabold tracking-tight">Mi Botica</span>
            </div>
            <div class="relative z-10">
                <h1 class="text-4xl font-extrabold leading-tight">Sistema de gestión<br>para tu farmacia</h1>
                <p class="mt-4 text-slate-300 max-w-md">Ventas, inventario, vencimientos, compras, clientes y reportes. Todo en un solo lugar, con control en tiempo real.</p>
                <div class="mt-8 grid grid-cols-2 gap-4 max-w-md">
                    <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
                        <p class="text-2xl font-bold">POS</p>
                        <p class="text-sm text-slate-300">Punto de venta ágil</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
                        <p class="text-2xl font-bold">Stock</p>
                        <p class="text-sm text-slate-300">Lotes y vencimientos</p>
                    </div>
                </div>
            </div>
            <p class="relative z-10 text-xs text-slate-400">© {{ date('Y') }} Mi Botica · Todos los derechos reservados</p>
        </div>

        {{-- Formulario --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-slate-800">Mi Botica</span>
                </div>

                <h2 class="text-2xl font-bold text-slate-800">Bienvenido de nuevo</h2>
                <p class="mt-1 text-sm text-slate-500">Ingresa tus credenciales para acceder al sistema.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="mt-1 block w-full rounded-lg border-slate-300 border px-3 py-2.5 text-slate-800 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 focus:outline-none"
                               placeholder="admin@mibotica.test">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 block w-full rounded-lg border-slate-300 border px-3 py-2.5 text-slate-800 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 focus:outline-none"
                               placeholder="••••••••">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            Recordarme
                        </label>
                    </div>
                    <button type="submit"
                            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-white font-semibold shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/40 transition">
                        Iniciar sesión
                    </button>
                </form>

                <div class="mt-8 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-500">
                    <span class="font-semibold text-slate-600">Credenciales de prueba:</span><br>
                    admin@mibotica.test · contraseña: <span class="font-mono">password</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
