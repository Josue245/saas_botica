<header class="sticky top-0 z-20 h-16 bg-white border-b border-slate-200 flex items-center gap-3 px-4 sm:px-6">
    {{-- Toggle menú (móvil) --}}
    <button @click="open=!open" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
    </button>

    {{-- Buscador --}}
    <div class="flex-1 min-w-0 max-w-xl">
        <div class="relative">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" placeholder="Buscar producto, cliente o código…"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm text-slate-700 focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        </div>
    </div>

    <div class="flex items-center gap-2 ml-auto">
        {{-- Notificaciones --}}
        <a href="{{ route('alertas.index') }}" class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100" title="Alertas sanitarias">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
            @if(($alertasCount ?? 0) > 0)
                <span class="absolute top-1 right-1 inline-flex items-center justify-center min-w-4 h-4 px-1 text-[10px] font-bold rounded-full bg-red-500 text-white">{{ $alertasCount }}</span>
            @endif
        </a>

        {{-- Perfil --}}
        <div x-data="{ p:false }" class="relative">
            <button @click="p=!p" class="flex items-center gap-2 p-1 pr-2 rounded-lg hover:bg-slate-100">
                <span class="w-9 h-9 rounded-full bg-brand-600 text-white flex items-center justify-center text-sm font-bold">{{ auth()->user()?->iniciales() ?? 'AD' }}</span>
                <span class="hidden sm:block text-left">
                    <span class="block text-sm font-semibold text-slate-700 leading-tight">{{ auth()->user()?->name ?? 'Administrador' }}</span>
                    <span class="block text-xs text-slate-400 capitalize leading-tight">{{ auth()->user()?->rol ?? 'admin' }}</span>
                </span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="p" x-cloak @click.outside="p=false"
                 x-transition
                 class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5">
                <a href="{{ route('configuracion.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Configuración</a>
                <a href="{{ route('personal.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Mi perfil</a>
                <div class="my-1 border-t border-slate-100"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </div>
</header>
