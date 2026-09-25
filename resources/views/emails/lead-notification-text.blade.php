============================================================
AUGE PANAMBY — NOVO LEAD RECEBIDO
============================================================

Olá, equipe de vendas!

Um novo cliente em potencial acabou de preencher o formulário na landing page:

* Nome: {{ $lead->nome }}
* Telefone: {{ $phoneFormatted }}
* E-mail: {{ $lead->email }}
@if($lead->como_conheceu)
* Como nos conheceu: {{ ucfirst($lead->como_conheceu) }}
@endif

@if($lead->mensagem)
* Mensagem do cliente:
  "{{ $lead->mensagem }}"
@endif

------------------------------------------------------------
AÇÕES RÁPIDAS
------------------------------------------------------------
* Iniciar conversa no WhatsApp:
  {{ $whatsUrl }}

* Responder por e-mail:
  mailto:{{ $lead->email }}

------------------------------------------------------------
REGISTRO DE CONFORMIDADE LGPD (Lei 13.709/2018)
------------------------------------------------------------
Consentimento aceito em: {{ $consentAtFormatted }} (horário de Brasília)
Versão do Termo: {{ $lead->consent_version ?? '2026-09' }}
IP Hash: {{ $lead->ip_hash ?? 'N/A' }}

Auge Panamby — Rua Marie Nader Calfat, 499, Panamby, São Paulo/SP
https://augepanamby.net.br
