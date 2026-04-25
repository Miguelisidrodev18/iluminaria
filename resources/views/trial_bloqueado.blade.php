<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Bloqueado — Estelar Software</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0a0f1e 0%, #0d1b3e 50%, #0a1628 100%);
            min-height: 100vh;
        }
        .stars {
            position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0;
        }
        .star {
            position: absolute; background: white; border-radius: 50%;
            animation: twinkle 3s infinite alternate;
        }
        @keyframes twinkle {
            0%   { opacity: 0.2; }
            100% { opacity: 0.9; }
        }
        .glass-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(148,163,184,0.2);
        }
        .silver-text {
            background: linear-gradient(135deg, #94a3b8, #e2e8f0, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .blue-glow {
            box-shadow: 0 0 30px rgba(59,130,246,0.4), 0 0 60px rgba(59,130,246,0.15);
        }
        .btn-whatsapp {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transition: all 0.3s ease;
        }
        .btn-whatsapp:hover {
            background: linear-gradient(135deg, #15803d, #166534);
            box-shadow: 0 0 20px rgba(22,163,74,0.4);
            transform: translateY(-1px);
        }
        .btn-email {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(148,163,184,0.3);
            transition: all 0.3s ease;
        }
        .btn-email:hover {
            background: rgba(255,255,255,0.14);
            transform: translateY(-1px);
        }
        .lock-ring {
            animation: pulse-ring 2s ease-in-out infinite;
        }
        @keyframes pulse-ring {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4), 0 0 20px rgba(239,68,68,0.3); }
            50%       { box-shadow: 0 0 0 12px rgba(239,68,68,0), 0 0 30px rgba(239,68,68,0.2); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    {{-- Estrellas decorativas --}}
    <div class="stars" id="stars"></div>

    <div class="relative z-10 w-full max-w-md">

        {{-- Logo / Marca Estelar Software --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center blue-glow">
                    <i class="fas fa-star text-white text-lg"></i>
                </div>
                <span class="text-2xl font-bold tracking-wide silver-text">Estelar Software</span>
            </div>
            <p class="text-slate-500 text-xs tracking-widest uppercase">Soluciones Empresariales</p>
        </div>

        {{-- Tarjeta principal --}}
        <div class="glass-card rounded-2xl p-8 text-center">

            {{-- Icono de bloqueo --}}
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-red-500/10 border-2 border-red-500/60 flex items-center justify-center lock-ring">
                    <i class="fas fa-lock text-red-400 text-3xl"></i>
                </div>
            </div>

            <h1 class="text-2xl font-bold text-white mb-2">Acceso Bloqueado</h1>
            <p class="text-slate-400 text-sm leading-relaxed mb-6">
                La licencia de este sistema ha vencido o no está activa.<br>
                Contacta a <span class="text-blue-400 font-semibold">Estelar Software</span> para
                activar o renovar tu licencia y continuar usando el sistema.
            </p>

            {{-- Separador plateado --}}
            <div class="h-px bg-linear-to-r from-transparent via-slate-500/50 to-transparent mb-6"></div>

            {{-- Datos de contacto --}}
            <div class="space-y-3 text-left mb-6">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-slate-700/50">
                    <div class="w-9 h-9 rounded-lg bg-blue-600/20 flex items-center justify-center shrink-0">
                        <i class="fas fa-building text-blue-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wide">Empresa</p>
                        <p class="text-white font-semibold text-sm">Estelar Software</p>
                    </div>
                </div>

                <a href="mailto:{{ env('TRIAL_CONTACT_EMAIL') }}"
                   class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-slate-700/50 hover:border-blue-500/50 hover:bg-blue-500/5 transition-all group">
                    <div class="w-9 h-9 rounded-lg bg-blue-600/20 flex items-center justify-center shrink-0 group-hover:bg-blue-600/30 transition-colors">
                        <i class="fas fa-envelope text-blue-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wide">Correo</p>
                        <p class="text-blue-300 text-sm group-hover:text-blue-200">{{ env('TRIAL_CONTACT_EMAIL') }}</p>
                    </div>
                </a>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-slate-700/50">
                    <div class="w-9 h-9 rounded-lg bg-slate-500/20 flex items-center justify-center shrink-0">
                        <i class="fas fa-phone text-slate-300 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wide">Teléfono / WhatsApp</p>
                        <p class="text-slate-200 text-sm font-medium">{{ env('TRIAL_CONTACT_PHONE') }}</p>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="space-y-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', env('TRIAL_CONTACT_PHONE')) }}"
                   target="_blank"
                   class="btn-whatsapp w-full inline-flex items-center justify-center gap-2 text-white font-semibold px-6 py-3 rounded-xl">
                    <i class="fab fa-whatsapp text-xl"></i>
                    Contactar por WhatsApp
                </a>
                <a href="mailto:{{ env('TRIAL_CONTACT_EMAIL') }}"
                   class="btn-email w-full inline-flex items-center justify-center gap-2 text-slate-200 font-semibold px-6 py-3 rounded-xl">
                    <i class="fas fa-envelope"></i>
                    Enviar correo
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-slate-600 text-xs mt-6">
            &copy; {{ date('Y') }} Estelar Software &mdash; Todos los derechos reservados
        </p>
    </div>

    <script>
        // Generar estrellas aleatorias
        const container = document.getElementById('stars');
        for (let i = 0; i < 80; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            const size = Math.random() * 2.5 + 0.5;
            star.style.cssText = `
                width:${size}px; height:${size}px;
                top:${Math.random()*100}%;
                left:${Math.random()*100}%;
                animation-delay:${Math.random()*4}s;
                animation-duration:${2+Math.random()*3}s;
            `;
            container.appendChild(star);
        }
    </script>
</body>
</html>
