<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Importaciones') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="border-b shadow-sm" style="background-color: #2B2E2C; border-color: #3A3E3B;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo y Título -->
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            <i class="fas fa-lightbulb text-2xl mr-3" style="color: #F7D600;"></i>
                            <span class="text-xl font-bold text-white">KYRIOS</span>
                        </a>
                    </div>

                    <!-- Usuario y Rol -->
                    <div class="flex items-center space-x-4">
                        <!-- Rol Badge -->
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if(auth()->user()->role->nombre == 'Administrador') bg-[#F7D600]/20 text-[#F7D600]
                            @elseif(auth()->user()->role->nombre == 'Vendedor') bg-green-100 text-green-800
                            @elseif(auth()->user()->role->nombre == 'Almacenero') bg-[#F7D600]/20 text-[#F7D600]
                            @else bg-[#F7D600]/10 text-[#F7D600]
                            @endif">
                            <i class="fas fa-user-tag mr-1"></i>
                            {{ auth()->user()->role->nombre }}
                        </span>

                        <!-- Dropdown de Usuario -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-white hover:text-[#F7D600] focus:outline-none">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                @click.away="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                {{-- <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-edit mr-2"></i>Perfil
                                </a> --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Banner periodo de prueba -->
        @if(isset($trialDiasRestantes))
            @if($trialDiasRestantes <= 2)
                <div class="w-full bg-red-600 text-white text-sm font-semibold text-center py-2 px-4 flex items-center justify-center gap-2">
                    <i class="fas fa-exclamation-triangle animate-pulse"></i>
                    @if($trialDiasRestantes == 0)
                        ¡Tu periodo de prueba vence HOY! Contacta a Estelar Software para activar tu licencia.
                    @elseif($trialDiasRestantes == 1)
                        ¡Te queda 1 día de prueba! Contacta a Estelar Software para activar tu licencia.
                    @else
                        ¡Te quedan {{ $trialDiasRestantes }} días de prueba! Contacta a Estelar Software para activar tu licencia.
                    @endif
                    <span class="mx-2">|</span>
                    <a href="mailto:{{ env('TRIAL_CONTACT_EMAIL') }}" class="underline hover:text-red-200">
                        {{ env('TRIAL_CONTACT_EMAIL') }}
                    </a>
                    <span class="mx-1">·</span>
                    <span>{{ env('TRIAL_CONTACT_PHONE') }}</span>
                </div>
            @elseif($trialDiasRestantes <= 7)
                <div class="w-full bg-yellow-500 text-yellow-900 text-sm font-semibold text-center py-2 px-4 flex items-center justify-center gap-2">
                    <i class="fas fa-clock"></i>
                    Te quedan <strong class="mx-1">{{ $trialDiasRestantes }} días</strong> de periodo de prueba.
                    Contacta a Estelar Software:
                    <a href="mailto:{{ env('TRIAL_CONTACT_EMAIL') }}" class="underline ml-1">{{ env('TRIAL_CONTACT_EMAIL') }}</a>
                    <span class="mx-1">·</span>
                    <span>{{ env('TRIAL_CONTACT_PHONE') }}</span>
                </div>
            @endif
        @endif

        <!-- Page Content -->
        <main class="py-6">
            <!-- Alertas -->
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Contenido Principal -->
            @yield('content')
        </main>
    </div>


</body>
</html>