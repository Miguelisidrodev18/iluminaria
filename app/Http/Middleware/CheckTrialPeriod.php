<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialPeriod
{
    public function handle(Request $request, Closure $next): Response
    {
        // TRIAL_ENABLED=false → sistema bloqueado sin importar la fecha
        if (!env('TRIAL_ENABLED', true)) {
            if (!$request->routeIs('trial.bloqueado')) {
                return redirect()->route('trial.bloqueado');
            }
            return $next($request);
        }

        $expiry = Carbon::parse(env('TRIAL_EXPIRY_DATE'))->endOfDay();
        $now    = Carbon::now();
        $days   = (int) $now->diffInDays($expiry, false);

        if ($days < 0) {
            if (!$request->routeIs('trial.bloqueado')) {
                return redirect()->route('trial.bloqueado');
            }
        } else {
            View::share('trialDiasRestantes', $days);
        }

        return $next($request);
    }
}
