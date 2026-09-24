<?php

namespace App\Providers;

use App\Events\LeadReceived;
use App\Listeners\SendLeadNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registro explícito do ouvinte de notificação de leads
        Event::listen(LeadReceived::class, SendLeadNotification::class);

        // Anti-abuse: Rate limiter rigoroso para submissão pública de leads
        // 3 envios por minuto + teto de 20 por hora por IP
        RateLimiter::for('leads', function (Request $request) {
            $key = $request->ip() ?: 'anonymous';

            return [
                Limit::perMinute(3)->by($key)->response(function () {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Muitas tentativas em pouco tempo. Por favor, aguarde um instante ou fale diretamente pelo WhatsApp.',
                    ], 429);
                }),
                Limit::perHour(20)->by($key)->response(function () {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Limite de solicitações atingido para este período. Fale conosco pelo WhatsApp.',
                    ], 429);
                }),
            ];
        });
    }
}
