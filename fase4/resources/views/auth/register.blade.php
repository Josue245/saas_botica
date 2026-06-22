<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Mi Botica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">

<div class="w-full max-w-lg">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl mb-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Mi Botica</h1>
        <p class="text-gray-500 text-sm mt-1">Sistema de gestión para farmacias en Perú</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-1">Crea tu cuenta gratis</h2>
        <p class="text-sm text-gray-500 mb-6">14 días de prueba · Sin tarjeta de crédito</p>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.tenant') }}" class="space-y-4">
            @csrf

            <!-- Datos de la empresa -->
            <div class="border-b border-gray-100 pb-4 mb-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tu empresa</p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Razón social <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                               placeholder="Ej: Botica León S.A.C."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('razon_social') border-red-400 @enderror">
                        @error('razon_social')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                        <input type="text" name="ruc" value="{{ old('ruc') }}"
                               placeholder="20123456789 (opcional)"
                               maxlength="11"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruc') border-red-400 @enderror">
                        @error('ruc')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Correo de la empresa <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email_empresa" value="{{ old('email_empresa') }}"
                               placeholder="contacto@mibotica.pe"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email_empresa') border-red-400 @enderror">
                        @error('email_empresa')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Datos del administrador -->
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tu cuenta de administrador</p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_admin" value="{{ old('nombre_admin') }}"
                               placeholder="Juan Pérez"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre_admin') border-red-400 @enderror">
                        @error('nombre_admin')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Correo de acceso <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email_admin" value="{{ old('email_admin') }}"
                               placeholder="juan@mibotica.pe"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email_admin') border-red-400 @enderror">
                        @error('email_admin')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password"
                                   placeholder="Mínimo 8 caracteres"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation"
                                   placeholder="Repite la contraseña"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm mt-2">
                Crear mi cuenta gratis →
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-4">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Inicia sesión</a>
        </p>
    </div>

    <!-- Planes -->
    <div class="mt-6 grid grid-cols-3 gap-3 text-center text-xs text-gray-500">
        <div class="bg-white rounded-xl p-3 shadow-sm">
            <div class="font-semibold text-gray-700">Free</div>
            <div>1 usuario · 100 productos</div>
        </div>
        <div class="bg-white rounded-xl p-3 shadow-sm border-2 border-blue-200">
            <div class="font-semibold text-blue-700">Pro S/. 189/mes</div>
            <div>10 usuarios · multi-sucursal</div>
        </div>
        <div class="bg-white rounded-xl p-3 shadow-sm">
            <div class="font-semibold text-gray-700">Enterprise</div>
            <div>Ilimitado · API access</div>
        </div>
    </div>
</div>

</body>
</html>
