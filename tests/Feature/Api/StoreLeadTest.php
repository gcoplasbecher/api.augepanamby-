<?php

use App\Events\LeadReceived;
use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

test('registra um novo lead com sucesso e dispara notificação', function () {
    Notification::fake();
    Event::fake([LeadReceived::class]);

    $payload = [
        'nome' => 'Ana Clara Menezes',
        'email' => 'Ana.Clara@Exemplo.com',
        'telefone' => '(11) 91917-0763',
        'como_conheceu' => 'instagram',
        'mensagem' => 'Gostaria de agendar visita na unidade de 51m²',
        'consent' => true,
    ];

    $response = $this->postJson('/api/leads', $payload, [
        'Referer' => 'https://augepanamby.net.br/',
        'User-Agent' => 'Astro/Mozilla Tester',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'ok' => true,
            'message' => 'Recebemos sua solicitação! Entraremos em contato em breve.',
        ]);

    $this->assertDatabaseHas('leads', [
        'nome' => 'Ana Clara Menezes',
        'email' => 'ana.clara@exemplo.com',
        'telefone' => '(11) 91917-0763',
        'telefone_e164' => '+5511919170763',
        'como_conheceu' => 'instagram',
        'status' => 'novo',
        'consent_version' => '2026-09',
    ]);

    $lead = Lead::where('email', 'ana.clara@exemplo.com')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->consent_at)->not->toBeNull()
        ->and($lead->ip_hash)->not->toBeNull()
        ->and($lead->ip_hash)->not->toBe('127.0.0.1'); // Nunca salva IP cru

    Event::assertDispatched(LeadReceived::class);
});

test('envia a notificação por e-mail para o endereço configurado', function () {
    Notification::fake();
    config(['leads.notify_email' => 'corretor@augepanamby.net.br']);

    $payload = [
        'nome' => 'Carlos Eduardo',
        'email' => 'carlos@teste.com',
        'telefone' => '(11) 98888-1234',
        'como_conheceu' => 'google',
        'mensagem' => 'Qual o valor da planta de 70m²?',
        'consent' => true,
    ];

    $this->postJson('/api/leads', $payload)->assertStatus(201);

    Notification::assertSentOnDemand(
        NewLeadNotification::class,
        function ($notification, $channels, $notifiable) {
            return $notifiable->routes['mail'] === 'corretor@augepanamby.net.br';
        }
    );
});
