<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php $empresa = \App\Models\Empresa::instancia(); @endphp

        {{-- Fondo oscuro con acento dorado --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
             style="background: linear-gradient(135deg, #1F2220 0%, #2B2E2C 60%, #333836 100%);">

            {{-- Círculo decorativo dorado (inspirado en el punto del logo) --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none" aria-hidden="true">
                <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-10"
                     style="background: radial-gradient(circle, #F7D600 0%, transparent 70%);"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full opacity-5"
                     style="background: radial-gradient(circle, #F7D600 0%, transparent 70%);"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center">
                {{-- Logo / Ícono --}}
                <a href="/" class="flex items-center justify-center mb-6">
                    @if($empresa?->logo_url)
                        <img src="{{ $empresa->logo_url }}" alt="Logo"
                             class="h-28 w-28 object-contain drop-shadow-2xl rounded-2xl bg-white/10 p-2">
                    @else
                        {{-- Ícono default estilo Kyrios --}}
                        <div class="w-24 h-24 rounded-full flex items-center justify-center shadow-2xl"
                             style="background-color: #F7D600;">
                            <i class="fas fa-lightbulb text-[#2B2E2C] text-5xl"></i>
                        </div>
                    @endif
                </a>

                <h1 class="text-center text-3xl font-bold drop-shadow-lg mb-1" style="color: #F7D600;">
                    {{ $empresa?->nombre_display ?? 'KYRIOS' }}
                </h1>
                <p class="text-center mb-8 text-sm tracking-widest uppercase"
                   style="color: rgba(247,214,0,0.55); letter-spacing: 0.2em;">
                    luces &amp; entorno
                </p>
            </div>

            {{-- Tarjeta del formulario --}}
            <div class="relative z-10 w-full sm:max-w-md mt-2">
                {{-- Barra dorada superior --}}
                <div class="h-1 rounded-t-lg" style="background-color: #F7D600;"></div>
                <div class="px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-b-lg">
                    {{ $slot }}
                </div>
            </div>

            <div class="relative z-10 mt-6 text-center text-sm" style="color: rgba(247,214,0,0.45);">
                <p>&copy; {{ date('Y') }} {{ $empresa?->nombre_display ?? 'Kyrios Luces & Entorno' }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
</html>
