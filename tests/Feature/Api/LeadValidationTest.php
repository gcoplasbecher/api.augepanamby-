<?php

test('rejeita submissão sem os campos obrigatórios', function () {
    $response = $this->postJson('/api/leads', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nome', 'email', 'telefone', 'consent']);
});

test('rejeita e-mail inválido', function () {
    $response = $this->postJson('/api/leads', [
        'nome' => 'João Silva',
        'email' => 'email-invalido-sem-arroba',
        'telefone' => '(11) 91917-0763',
        'consent' => true,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('rejeita telefone inválido', function () {
    $response = $this->postJson('/api/leads', [
        'nome' => 'João Silva',
        'email' => 'joao@exemplo.com',
        'telefone' => '12345',
        'consent' => true,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['telefone']);
});

test('exige consentimento sob a LGPD', function () {
    $response = $this->postJson('/api/leads', [
        'nome' => 'João Silva',
        'email' => 'joao@exemplo.com',
        'telefone' => '(11) 91917-0763',
        'consent' => false,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['consent']);
});

test('rejeita opção desconhecida em como_conheceu', function () {
    $response = $this->postJson('/api/leads', [
        'nome' => 'João Silva',
        'email' => 'joao@exemplo.com',
        'telefone' => '(11) 91917-0763',
        'como_conheceu' => 'origem_hacker_desconhecida',
        'consent' => true,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['como_conheceu']);
});
