<?php

test('responde a preflight CORS OPTIONS para origens permitidas', function () {
    $response = $this->withHeaders([
        'Origin' => 'https://augepanamby.net.br',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'Content-Type, X-Requested-With',
    ])->options('/api/leads');

    $response->assertStatus(204)
        ->assertHeader('Access-Control-Allow-Origin', 'https://augepanamby.net.br');
});

test('adiciona cabecalho CORS nas respostas POST da landing', function () {
    $payload = [
        'nome' => 'Visitante CORS',
        'email' => 'cors@teste.com',
        'telefone' => '(11) 91917-0763',
        'consent' => true,
    ];

    $response = $this->withHeaders([
        'Origin' => 'https://augepanamby.net.br',
    ])->postJson('/api/leads', $payload);

    $response->assertStatus(201)
        ->assertHeader('Access-Control-Allow-Origin', 'https://augepanamby.net.br');
});

test('endpoint de health check /up responde 200', function () {
    $this->get('/up')->assertStatus(200);
});
