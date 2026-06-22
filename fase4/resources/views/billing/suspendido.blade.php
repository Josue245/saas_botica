<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Suscripción suspendida — Mi Botica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
<div class="max-w-md text-center">
    <div class="text-6xl mb-4">⚠️</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Suscripción suspendida</h1>
    <p class="text-gray-500 mb-6">
        Tu acceso está temporalmente suspendido. Renueva tu plan para continuar usando Mi Botica.
    </p>
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif
    <a href="mailto:soporte@mibotica.pe"
       class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
        Contactar soporte
    </a>
</div>
</body>
</html>
