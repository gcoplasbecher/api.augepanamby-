<?php

namespace App\Console\Commands;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Console\Command;

class PruneLeads extends Command
{
    protected $signature = 'leads:prune
                            {--days= : Dias de retenção (padrão definido em config/leads.php)}
                            {--delete : Excluir permanentemente em vez de anonimizar}
                            {--dry-run : Apenas simular sem alterar o banco de dados}';

    protected $description = 'Executa a rotina de retenção da LGPD, anonimizando ou excluindo leads antigos';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('leads.retention_days', 730));
        $cutoff = now()->subDays($days);
        $hardDelete = (bool) $this->option('delete');
        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            'LGPD Prune: Buscando leads não convertidos anteriores a %s (%d dias)...',
            $cutoff->format('d/m/Y H:i'),
            $days
        ));

        $query = Lead::where('created_at', '<=', $cutoff)
            ->where('status', '!=', LeadStatus::Convertido);

        if (! $hardDelete) {
            // Se for anonimizar, não pega os que já estão anonimizados
            $query->whereNull('anonymized_at');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nenhum lead elegível para retenção neste momento.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn(sprintf('[DRY-RUN] %d lead(s) seriam %s.', $count, $hardDelete ? 'excluídos' : 'anonimizados'));

            return self::SUCCESS;
        }

        $processed = 0;
        $query->chunkById(100, function ($leads) use (&$processed, $hardDelete) {
            foreach ($leads as $lead) {
                if ($hardDelete) {
                    $lead->forceDelete();
                } else {
                    $lead->anonymize();
                }
                $processed++;
            }
        });

        $this->info(sprintf('Sucesso: %d lead(s) foram %s sob a política LGPD.', $processed, $hardDelete ? 'excluídos' : 'anonimizados'));

        return self::SUCCESS;
    }
}
