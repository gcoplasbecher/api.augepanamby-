<?php

return [
    /*
     | E-mail do corretor/equipe comercial para receber novos leads
     */
    'notify_email' => env('LEAD_NOTIFY_EMAIL', 'contato@augepanamby.net.br'),

    /*
     | Prazo de retenção de dados pessoais sob a LGPD (em dias).
     | Padrão: 730 dias (24 meses). Após este período, leads sem conversão são anonimizados.
     */
    'retention_days' => (int) env('LEAD_RETENTION_DAYS', 730),

    /*
     | Versão vigente do Termo de Consentimento / Política de Privacidade
     */
    'consent_version' => env('LEAD_CONSENT_VERSION', '2026-09'),

    /*
     | Janela de deduplicação (em minutos) para evitar duplo-clique / spam acidental
     */
    'dedupe_minutes' => (int) env('LEAD_DEDUPE_MINUTES', 5),
];
