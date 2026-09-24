<?php

namespace App\Listeners;

use App\Events\LeadReceived;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendLeadNotification
{
    public function handle(LeadReceived $event): void
    {
        $notifyEmail = config('leads.notify_email');

        if (empty($notifyEmail)) {
            Log::warning('Nenhum e-mail de notificação de leads configurado (LEAD_NOTIFY_EMAIL).');

            return;
        }

        try {
            Notification::route('mail', $notifyEmail)
                ->notify(new NewLeadNotification($event->lead));
        } catch (\Throwable $e) {
            // Em caso de falha no envio de e-mail, loga mas não quebra o request do cliente
            Log::error('Falha ao enviar e-mail de novo lead: '.$e->getMessage(), [
                'lead_id' => $event->lead->id,
            ]);
        }
    }
}
