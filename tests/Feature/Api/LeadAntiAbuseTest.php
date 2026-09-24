<?php

use App\Models\Lead;

test('descarta silenciosamente envio com honeypot sem persistir no banco', function () {
    $payload = [
        'nome' => 'Robô Spammer',
        'email' => 'bot@spam.com',
        'telefone' => '(11) 91917-0763',
        'consent' => true,
        'website' => 'https://spam-link.com', // Campo honeypot preenchido
    ];

    $response = $this->postJson('/api/leads', $payload);

    // Retorna 201 amigável para enganar o bot
    $response->assertStatus(201)
        ->assertJson(['ok' => true]);

    // Zero registros no banco de dados
    $this->assertDatabaseMissing('leads', [
        'email' => 'bot@spam.com',
    ]);
});

test('deduplica submissões com mesmo e-mail em janela curta', function () {
    $payload = [
        'nome' => 'Maria Silva',
        'email' => 'maria.dedupe@teste.com',
        'telefone' => '(11) 99999-1111',
        'consent' => true,
    ];

    // Primeiro envio -> gravado
    $this->postJson('/api/leads', $payload)->assertStatus(201);
    expect(Lead::where('email', 'maria.dedupe@teste.com')->count())->toBe(1);

    // Segundo envio imediato (duplo-clique acidental)
    $this->postJson('/api/leads', $payload)->assertStatus(201);

    // Continua sendo apenas 1 registro no banco
    expect(Lead::where('email', 'maria.dedupe@teste.com')->count())->toBe(1);
});

test('aplica rate limiter após limite de envios por minuto', function () {
    $payload = fn ($i) => [
        'nome' => "Visitante {$i}",
        'email' => "visitante{$i}@teste.com",
        'telefone' => '(11) 97777-000'.$i,
        'consent' => true,
    ];

    // Limite definido: 3 por minuto por IP
    $this->postJson('/api/leads', $payload(1))->assertStatus(201);
    $this->postJson('/api/leads', $payload(2))->assertStatus(201);
    $this->postJson('/api/leads', $payload(3))->assertStatus(201);

    // 4º envio no mesmo minuto deve retornar 429 Too Many Requests
    $response = $this->postJson('/api/leads', $payload(4));

    $response->assertStatus(429)
        ->assertJson([
            'ok' => false,
        ]);
});
