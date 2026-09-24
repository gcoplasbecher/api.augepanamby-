<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Support\PhoneNumber;
use Illuminate\Console\Command;

class ForgetLead extends Command
{
    protected $signature = 'leads:forget
                            {identifier : E-mail, ID ou Telefone do titular para eliminação dos dados}
                            {--force : Pular confirmação}
                            {--delete : Excluir permanentemente em vez de anonimizar}';

    protected $description = 'Atende à solicitação de eliminação de dados pessoais do titular (LGPD art. 18, VI)';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));
        $e164 = PhoneNumber::toE164($identifier);

        $query = Lead::query();

        if (is_numeric($identifier) && strlen($identifier) < 9) {
            $query->where('id', (int) $identifier);
        } else {
            $query->where(function ($q) use ($identifier, $e164) {
                $q->where('email', strtolower($identifier));
                if ($e164) {
                    $q->orWhere('telefone_e164', $e164);
                }
            });
        }

        $leads = $query->get();

        if ($leads->isEmpty()) {
            $this->error('Nenhum lead encontrado com o identificador informado: '.$identifier);

            return self::FAILURE;
        }

        $this->info(sprintf('Encontrado(s) %d registro(s) para o titular:', $leads->count()));
        foreach ($leads as $lead) {
            $this->line(sprintf(' - ID: %d | Nome: %s | E-mail: %s | Telefone: %s', $lead->id, $lead->nome, $lead->email, $lead->telefone));
        }

        $hardDelete = (bool) $this->option('delete');
        $action = $hardDelete ? 'EXCLUIR PERMANENTEMENTE' : 'ANONIMIZAR';

        if (! $this->option('force') && ! $this->confirm("Confirma que deseja {$action} estes dados sob a LGPD?")) {
            $this->warn('Operação cancelada.');

            return self::SUCCESS;
        }

        foreach ($leads as $lead) {
            if ($hardDelete) {
                $lead->forceDelete();
            } else {
                $lead->anonymize();
            }
        }

        $this->info(sprintf('Dados de %d lead(s) foram devidamente %s.', $leads->count(), $hardDelete ? 'excluídos' : 'anonimizados'));

        return self::SUCCESS;
    }
}
