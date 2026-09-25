<?php

use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Carbon;

test('renderiza template de e-mail html e texto sem erros', function () {
    $lead = Lead::factory()->create([
        'nome' => 'Renata Albuquerque',
        'email' => 'renata@exemplo.com',
        'telefone' => '(11) 98765-4321',
        'como_conheceu' => 'instagram',
        'mensagem' => 'Gostaria de mais detalhes sobre o empreendimento',
        'consent_at' => Carbon::parse('2026-09-24 12:00:00', 'UTC'),
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
        ->and($html)->toContain('24/09/2026 às 09:00:00') // UTC -> America/Sao_Paulo
        ->and($text)->toContain('AUGE PANAMBY — NOVO LEAD RECEBIDO')
        ->and($text)->toContain('24/09/2026 às 09:00:00')
        ->and($text)->toContain('Renata Albuquerque');
});

test('omite a origem e a mensagem quando não informadas', function () {
    $lead = Lead::factory()->create([
        'nome' => 'Joana Prado',
        'email' => 'joana@exemplo.com',
        'telefone' => '(11) 91234-5678',
        'como_conheceu' => null,
        'mensagem' => null,
    ]);

    $mail = (new NewLeadNotification($lead))->toMail((object) []);

    $html = view($mail->view, $mail->viewData)->render();

    expect($html)->toContain('Joana Prado')
        ->and($html)->not->toContain('Como Conheceu')
        ->and($html)->not->toContain('Mensagem do Cliente');
});
