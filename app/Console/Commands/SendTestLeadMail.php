<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendTestLeadMail extends Command
{
    protected $signature = 'leads:mail-test
                            {email? : Destinatário do teste (padrão: LEAD_NOTIFY_EMAIL)}
                            {--dry-run : Apenas renderiza o e-mail nos dois formatos, sem enviar}';

    protected $description = 'Valida o SMTP de produção enviando (ou renderizando) a notificação de novo lead';

    public function handle(): int
    {
        $to = (string) ($this->argument('email') ?: config('leads.notify_email'));

        if ($to === '') {
            $this->error('Nenhum destinatário informado e LEAD_NOTIFY_EMAIL está vazio.');

            return self::FAILURE;
        }

        $lead = new Lead([
            'nome' => 'Lead de Teste (validação SMTP)',
            'email' => 'lead.teste@augepanamby.net.br',
            'telefone' => '(11) 99999-0000',
            'telefone_e164' => '+5511999990000',
            'como_conheceu' => 'outro',
            'mensagem' => 'E-mail de teste disparado pelo comando leads:mail-test.',
            'consent_at' => now(),
            'consent_version' => (string) config('leads.consent_version', '2026-09'),
            'ip_hash' => 'teste',
        ]);

        $notification = new NewLeadNotification($lead);

        $this->table(['Configuração', 'Valor'], [
            ['Destinatário', $to],
            ['Mailer', (string) config('mail.default')],
            ['Host', config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port')],
            ['Scheme', (string) config('mail.mailers.smtp.scheme')],
            ['Username', (string) config('mail.mailers.smtp.username')],
            ['Remetente', config('mail.from.address').' ('.config('mail.from.name').')'],
            ['Fuso dos horários', (string) config('leads.timezone')],
        ]);

        if ($this->option('dry-run')) {
            $mail = $notification->toMail((object) []);
            $views = is_array($mail->view) ? $mail->view : ['html' => $mail->view];

            foreach ($views as $type => $view) {
                $rendered = view($view, $mail->viewData)->render();
                $this->line(sprintf('  view [%s] %s -> %d bytes', $type, $view, strlen($rendered)));
            }

            $this->info('Dry-run concluído: nenhum e-mail foi enviado.');

            return self::SUCCESS;
        }

        try {
            Notification::route('mail', $to)->notify($notification);
        } catch (Throwable $e) {
            $this->error('Falha no envio: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('E-mail aceito pelo SMTP para '.$to.'.');

        return self::SUCCESS;
    }
}
