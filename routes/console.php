<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Scheduled Tasks
|--------------------------------------------------------------------------
|
| Sob a LGPD (Lei 13.709/2018), dados pessoais devem ser mantidos apenas
| pelo período estritamente necessário para cumprir sua finalidade.
| A rotina abaixo é executada diariamente de madrugada para anonimizar leads
| anteriores à janela de retenção (padrão: 730 dias / 2 anos).
|
*/

Schedule::command('leads:prune')
    ->dailyAt('03:00')
    ->name('lgpd-leads-prune')
    ->withoutOverlapping();
