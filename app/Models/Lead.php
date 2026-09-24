<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use Prunable;
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'telefone_e164',
        'como_conheceu',
        'mensagem',
        'status',
        'consent_at',
        'consent_version',
        'ip_hash',
        'user_agent',
        'referer',
        'assigned_to',
        'notes',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'consent_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    /**
     * Query de prunable para o comando model:prune do Laravel.
     * Seleciona leads antigos não convertidos para remoção permanente após retenção.
     */
    public function prunable(): Builder
    {
        $days = (int) config('leads.retention_days', 730);

        return static::where('created_at', '<=', now()->subDays($days))
            ->where('status', '!=', LeadStatus::Convertido);
    }

    /**
     * Anonimiza os dados do lead mantendo a métrica numérica sem dados pessoais (LGPD art. 16, IV).
     */
    public function anonymize(): void
    {
        $this->forceFill([
            'nome' => 'Anonimizado (LGPD)',
            'email' => sprintf('anonimizado-%d@augepanamby.local', $this->id),
            'telefone' => '0000000000',
            'telefone_e164' => '+550000000000',
            'mensagem' => null,
            'ip_hash' => null,
            'user_agent' => null,
            'referer' => null,
            'notes' => null,
            'anonymized_at' => now(),
        ])->save();
    }

    /**
     * Verifica se é anônimo.
     */
    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }
}
