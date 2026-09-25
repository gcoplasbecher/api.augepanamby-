<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Support\PhoneNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cleanPhone = PhoneNumber::clean($this->lead->telefone_e164 ?? $this->lead->telefone);
        $whatsUrl = 'https://wa.me/'.$cleanPhone.'?text='.urlencode('Olá '.$this->lead->nome.', recebemos seu contato sobre o Auge Panamby!');
        $phoneFormatted = PhoneNumber::formatBr($this->lead->telefone);

        return (new MailMessage)
            ->subject('🎯 Novo Lead: '.$this->lead->nome.' — Auge Panamby')
            ->replyTo($this->lead->email, $this->lead->nome)
            ->view('emails.lead-notification', [
                'lead' => $this->lead,
                'phoneFormatted' => $phoneFormatted,
                'whatsUrl' => $whatsUrl,
            ]);
    }
}
