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

        $message = (new MailMessage)
            ->subject('🎯 Novo Lead Recebido: '.$this->lead->nome.' — Auge Panamby')
            ->greeting('Olá, equipe Auge Panamby!')
            ->line('Um novo cliente em potencial acabou de preencher o formulário na landing page:')
            ->line('**Nome:** '.$this->lead->nome)
            ->line('**E-mail:** '.$this->lead->email)
            ->line('**Telefone:** '.PhoneNumber::formatBr($this->lead->telefone));

        if ($this->lead->como_conheceu) {
            $message->line('**Como nos conheceu:** '.ucfirst($this->lead->como_conheceu));
        }

        if ($this->lead->mensagem) {
            $message->line('**Mensagem:**')
                ->line('> '.e($this->lead->mensagem));
        }

        $message->action('Chamar no WhatsApp', $whatsUrl)
            ->line('Data do recebimento: '.$this->lead->created_at?->format('d/m/Y \à\s H:i'))
            ->salutation('Equipe Auge Panamby');

        return $message;
    }
}
