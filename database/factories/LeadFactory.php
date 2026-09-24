<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => '(11) 98765-4321',
            'telefone_e164' => '+5511987654321',
            'como_conheceu' => fake()->randomElement(LeadSource::values()),
            'mensagem' => fake()->sentence(),
            'status' => LeadStatus::Novo,
            'consent_at' => now(),
            'consent_version' => '2026-09',
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
            'referer' => 'https://augepanamby.net.br/',
        ];
    }
}
