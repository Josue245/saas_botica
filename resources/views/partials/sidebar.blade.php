@php
    // Helpers de estilo para enlaces del menú
    $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors';
    $linkIdle = 'text-slate-300 hover:bg-white/5 hover:text-white';
    $linkActive = 'bg-brand-600 text-white shadow-sm';
    $section = 'px-3 pt-5 pb-1 text-[11px] font-bold uppercase tracking-wider text-brand-400/80';
@endphp

{{-- Backdrop móvil --}}
<div x-show="open" x-cloak x-transition.opacity @click="open=false"
     class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

{{-- Drawer: oculto a la izquierda en móvil, siempre visible en escritorio (lg) --}}
<aside :class="open ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col transition-transform duration-200 lg:translate-x-0">

    {{-- Logo --}}
    <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
        <div class="w-9 h-9 rounded-lg bg-brand-500 flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
            </svg>
        </div>
        <span class="text-lg font-extrabold text-white tracking-tight">Mi Botica</span>
    </div>

    {{-- Navegación --}}
    <nav @click="if (window.innerWidth < 1024 && $event.target.closest('a')) open = false"
         class="flex-1 overflow-y-auto scroll-thin px-3 py-3 space-y-0.5">

        <p class="{{ $section }}">Visión General</p>
        <a href="{{ route('dashboard') }}" class="{{ $linkBase }} {{ request()->routeIs('dashboard') ? $linkActive : $linkIdle }}">
            <x-icon name="grid" /> Dashboard
        </a>

        <p class="{{ $section }}">Comercio &amp; Ventas</p>
        <div x-data="{ o: {{ request()->routeIs('caja.*') ? 'true' : 'false' }} }">
            <button @click="o=!o" class="{{ $linkBase }} {{ $linkIdle }} w-full justify-between">
                <span class="flex items-center gap-3"><x-icon name="cash" /> Gestión de Caja</span>
                <svg class="w-4 h-4 transition-transform" :class="o&&'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="o" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                <a href="{{ route('caja.index') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('caja.index') ? $linkActive : $linkIdle }}">Apertura / Cierre</a>
                <a href="{{ route('caja.movimientos') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('caja.movimientos') ? $linkActive : $linkIdle }}">Movimientos</a>
            </div>
        </div>
        <a href="{{ route('pos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('pos.*') ? $linkActive : $linkIdle }}">
            <x-icon name="cart" /> Punto de Venta (POS)
        </a>
        <a href="{{ route('ventas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('ventas.*') ? $linkActive : $linkIdle }}">
            <x-icon name="list" /> Historial Ventas
        </a>
        <a href="{{ route('clientes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('clientes.*') ? $linkActive : $linkIdle }}">
            <x-icon name="users" /> Clientes
        </a>

        <p class="{{ $section }}">Logística &amp; Inventario</p>
        <div x-data="{ o: {{ request()->routeIs('productos.*') || request()->routeIs('categorias.*') ? 'true' : 'false' }} }">
            <button @click="o=!o" class="{{ $linkBase }} {{ $linkIdle }} w-full justify-between">
                <span class="flex items-center gap-3"><x-icon name="box" /> Catálogo Maestro</span>
                <svg class="w-4 h-4 transition-transform" :class="o&&'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="o" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                <a href="{{ route('productos.index') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('productos.*') ? $linkActive : $linkIdle }}">Productos</a>
                <a href="{{ route('categorias.index') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('categorias.*') ? $linkActive : $linkIdle }}">Categorías</a>
            </div>
        </div>
        <a href="{{ route('compras.index') }}" class="{{ $linkBase }} {{ request()->routeIs('compras.*') ? $linkActive : $linkIdle }}">
            <x-icon name="truck" /> Compras
        </a>
        <a href="{{ route('proveedores.index') }}" class="{{ $linkBase }} {{ request()->routeIs('proveedores.*') ? $linkActive : $linkIdle }}">
            <x-icon name="building" /> Proveedores
        </a>
        <div x-data="{ o: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
            <button @click="o=!o" class="{{ $linkBase }} {{ $linkIdle }} w-full justify-between">
                <span class="flex items-center gap-3"><x-icon name="layers" /> Inventario</span>
                <svg class="w-4 h-4 transition-transform" :class="o&&'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="o" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                <a href="{{ route('inventario.index') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('inventario.index') ? $linkActive : $linkIdle }}">Stock Actual</a>
                <a href="{{ route('inventario.lotes') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('inventario.lotes') ? $linkActive : $linkIdle }}">Lotes y Vencimientos</a>
                <a href="{{ route('inventario.ajustes') }}" class="{{ $linkBase }} py-2 {{ request()->routeIs('inventario.ajustes') ? $linkActive : $linkIdle }}">Ajustes de Stock</a>
            </div>
        </div>

        <p class="{{ $section }}">Gerencia &amp; Control</p>
        <a href="{{ route('alertas.index') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs('alertas.*') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="bell" /> Alertas Sanitarias</span>
            @if(($alertasCount ?? 0) > 0)
                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-[11px] font-bold rounded-full bg-red-500 text-white">{{ $alertasCount }}</span>
            @endif
        </a>
        <a href="{{ route('reportes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('reportes.*') ? $linkActive : $linkIdle }}">
            <x-icon name="chart" /> Reportes PDF/Excel
        </a>

        <p class="{{ $section }}">Ajustes &amp; Sistema</p>
        <a href="{{ route('billing.index') }}" class="{{ $linkBase }} {{ request()->routeIs('billing.*') ? $linkActive : $linkIdle }}">
            <x-icon name="credit-card" /> Mi Suscripción
        </a>
        <a href="{{ route('sucursales.index') }}" class="{{ $linkBase }} {{ request()->routeIs('sucursales.*') ? $linkActive : $linkIdle }}">
            <x-icon name="building" /> Sucursales
        </a>
        <a href="{{ route('personal.index') }}" class="{{ $linkBase }} {{ request()->routeIs('personal.*') ? $linkActive : $linkIdle }}">
            <x-icon name="badge" /> Gestión de Personal
        </a>
        <a href="{{ route('configuracion.index') }}" class="{{ $linkBase }} {{ request()->routeIs('configuracion.*') ? $linkActive : $linkIdle }}">
            <x-icon name="cog" /> Configuración General
        </a>
        <a href="{{ route('auditoria.index') }}" class="{{ $linkBase }} {{ request()->routeIs('auditoria.*') ? $linkActive : $linkIdle }}">
            <x-icon name="shield" /> Logs de Auditoría
        </a>
        <a href="{{ route('respaldos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('respaldos.*') ? $linkActive : $linkIdle }}">
            <x-icon name="database" /> Base de Datos &amp; Respaldos
        </a>
        <a href="{{ route('facturacion.index') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs('facturacion.*') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="receipt" /> Facturación Electrónica</span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">OFF</span>
        </a>

        <div class="pt-3 mt-2 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="{{ $linkBase }} {{ $linkIdle }} w-full text-left">
                    <x-icon name="logout" /> Salir
                </button>
            </form>
        </div>
    </nav>
</aside>
