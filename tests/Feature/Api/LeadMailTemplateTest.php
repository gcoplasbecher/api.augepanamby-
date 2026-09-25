<?php

use App\Models\Lead;
use App\Notifications\NewLeadNotification;

test('renderiza template de e-mail html e texto sem erros', function () {
    $lead = Lead::factory()->create([
        'nome' => 'Renata Albuquerque',
        'email' => 'renata@exemplo.com',
        'telefone' => '(11) 98765-4321',
        'como_conheceu' => 'instagram',
        'mensagem' => 'Gostaria de mais detalhes sobre o empreendimento',
        'consent_at' => now(),
        'consent_version' => '2026-09',
    ]);

    $notification = new NewLeadNotification($lead);
    $mail = $notification->toMail((object) []);

    $html = view($mail->view, $mail->viewData)->render();
    $text = view('emails.lead-notification-text', $mail->viewData)->render();

    expect($html)->toContain('Renata Albuquerque')
        ->and($html)->toContain('(11) 98765-4321')
        ->and($html)->toContain('https://augepanamby.net.br/images/logo.png')
        ->and($html)->toContain('Chamar no WhatsApp')
        ->and($html)->toContain('Registro de Conformidade LGPD')
        ->and($text)->toContain('AUGE PANAMBY — NOVO LEAD RECEBIDO')
        ->and($text)->toContain('Renata Albuquerque');
});
