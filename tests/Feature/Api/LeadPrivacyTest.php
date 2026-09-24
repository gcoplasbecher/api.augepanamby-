<?php

use App\Enums\LeadStatus;
use App\Models\Lead;

test('grava prova de consentimento da LGPD e hash do IP', function () {
    $this->postJson('/api/leads', [
        'nome' => 'Lucas Rocha',
        'email' => 'lucas.rocha@exemplo.com',
        'telefone' => '(11) 91111-2222',
        'consent' => true,
    ], ['REMOTE_ADDR' => '189.40.10.50'])->assertStatus(201);

    $lead = Lead::where('email', 'lucas.rocha@exemplo.com')->firstOrFail();

    expect($lead->consent_at)->not->toBeNull()
        ->and($lead->consent_version)->toBe('2026-09')
        ->and($lead->ip_hash)->not->toBeNull()
        ->and(strlen($lead->ip_hash))->toBe(64); // SHA-256 HMAC
});

test('artisan leads:prune anonimiza leads antigos mantendo contadores', function () {
    // Lead recente (não deve ser afetado)
    $recent = Lead::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    // Lead expirado (mais de 730 dias)
    $expired = Lead::factory()->create([
        'created_at' => now()->subDays(800),
        'email' => 'expirado@antigo.com',
        'nome' => 'Nome Pessoal Antigo',
    ]);

    // Lead convertido (mantido para histórico contábil)
    $converted = Lead::factory()->create([
        'created_at' => now()->subDays(800),
        'status' => LeadStatus::Convertido,
        'email' => 'convertido@cliente.com',
    ]);

    $this->artisan('leads:prune', ['--days' => 730])
        ->assertSuccessful();

    // Recente intocado
    expect($recent->fresh()->isAnonymized())->toBeFalse();

    // Expirado foi devidamente anonimizado
    $expiredFresh = $expired->fresh();
    expect($expiredFresh->isAnonymized())->toBeTrue()
        ->and($expiredFresh->nome)->toBe('Anonimizado (LGPD)')
        ->and($expiredFresh->email)->not->toBe('expirado@antigo.com')
        ->and($expiredFresh->telefone)->toBe('0000000000');

    // Convertido intocado
    expect($converted->fresh()->isAnonymized())->toBeFalse();
});

test('artisan leads:forget elimina dados a pedido do titular sob o art. 18 da LGPD', function () {
    $lead = Lead::factory()->create([
        'email' => 'titular.lgpd@exemplo.com',
        'nome' => 'Titular Reclamante',
        'telefone' => '(11) 94444-5555',
        'telefone_e164' => '+5511944445555',
    ]);

    $this->artisan('leads:forget', [
        'identifier' => 'titular.lgpd@exemplo.com',
        '--force' => true,
    ])->assertSuccessful();

    $leadFresh = $lead->fresh();
    expect($leadFresh->isAnonymized())->toBeTrue()
        ->and($leadFresh->nome)->toBe('Anonimizado (LGPD)')
        ->and($leadFresh->email)->not->toBe('titular.lgpd@exemplo.com');
});
