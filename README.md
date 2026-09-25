# 🚀 API Auge Panamby

Backend API REST em **Laravel 13** construído exclusivamente para captura, validação, proteção anti-spam, conformidade com a **LGPD (Lei 13.709/2018)** e notificação de novos leads da landing page **Auge Panamby**.

🔗 **Landing Page:** [https://augepanamby.net.br](https://augepanamby.net.br)  
📦 **Repositório Front-end:** [../augepanamby](../augepanamby)

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Framework | **Laravel 13.x** (PHP 8.4) em modo API enxuta |
| Banco de Dados | SQLite (desenvolvimento/testes) / MySQL 8.x (produção Hostinger) |
| Testes | **Pest 5.x** + PHPUnit |
| Code Style | **Laravel Pint** |
| Processamento | `dispatchAfterResponse` (notificação sem gargalo e sem daemon em hospedagem compartilhada) |

---

## 📋 Endpoints da API

### `POST /api/leads`
Captura um novo lead enviado pelo formulário da landing page.

#### Headers
```http
Content-Type: application/json
Accept: application/json
Origin: https://augepanamby.net.br
```

#### Payload (JSON)
```json
{
  "nome": "Mariana Souza",
  "email": "mariana.souza@exemplo.com",
  "telefone": "(11) 91917-0763",
  "como_conheceu": "instagram",
  "mensagem": "Gostaria de agendar visita na unidade Garden de 70m²",
  "consent": true,
  "website": ""
}
```

| Campo | Tipo | Obrigatório | Regras |
|---|---|---|---|
| `nome` | string | Sim | Mínimo 3 e máximo 120 caracteres |
| `email` | string | Sim | Formato de e-mail válido (RFC) |
| `telefone` | string | Sim | Celular ou fixo BR com DDD válido (10 ou 11 dígitos) |
| `como_conheceu` | string | Não | `google`, `instagram`, `facebook`, `youtube`, `indicacao`, `outro` |
| `mensagem` | string | Não | Máximo 2000 caracteres |
| `consent` | boolean | Sim | Aceite obrigatório dos termos e política de privacidade (LGPD) |
| `website` | string | Não | **Honeypot anti-spam** (deve permanecer vazio) |

#### Respostas
- `201 Created`: `{"ok": true, "message": "Recebemos sua solicitação! Entraremos em contato em breve."}`
- `422 Unprocessable Content`: Erros detalhados de validação por campo.
- `429 Too Many Requests`: Limite de tentativas excedido (anti-abuso).

---

### `GET /up`
Health check nativo para monitoramento (UptimeRobot, etc.) retornando `200 OK`.

---

## 🛡️ Anti-Spam e Blindagem

1. **Honeypot Silencioso (`website`):** Robôs que preenchem campos ocultos recebem resposta `201 Created` simulada, sem qualquer persistência no banco e sem envio de e-mails.
2. **Rate Limiting por IP (`throttle:leads`):**
   - Máximo de **3 submissões por minuto** por IP.
   - Teto de **20 submissões por hora** por IP.
3. **Deduplicação Inteligente:** Evita cadastros duplicados causados por múltiplos cliques ou refresh acidental na mesma janela de 5 minutos por e-mail ou telefone E.164.
4. **CORS Restrito:** Permite chamadas apenas da landing page oficial e ambientes locais de desenvolvimento (`config/cors.php`).

---

## ⚖️ Conformidade com a LGPD (Lei nº 13.709/2018)

- **Base Legal (Consentimento - art. 7º, I):** Armazenamento de data/hora (`consent_at`) e versão vigente do termo (`consent_version`).
- **Minimização de Dados (art. 6º, III):** O endereço IP **nunca é salvo em texto puro**. É gerado um hash irreversível `ip_hash` via `hash_hmac('sha256', $ip, $appKey)` exclusivamente para auditoria e controle de abuso.
- **Ciclo de Retenção e Expurgo (art. 16):**
  - Rotina automática configurada para anonimizar leads com mais de **730 dias (2 anos)** não convertidos.
  - Comando:
    ```bash
    php artisan leads:prune
    php artisan leads:prune --dry-run
    php artisan leads:prune --delete # Para exclusão física definitiva
    ```
- **Direito do Titular à Eliminação (art. 18, VI):**
  - Eliminação ou anonimização imediata a pedido do titular por e-mail ou telefone:
    ```bash
    php artisan leads:forget titular@exemplo.com
    php artisan leads:forget 11919170763 --force
    ```

---

## ✉️ Notificação por E-mail

Quando um lead válido é recebido, o evento `LeadReceived` é disparado em `dispatchAfterResponse()`. O ouvinte envia um e-mail estruturado via canal `mail` para `LEAD_NOTIFY_EMAIL` contendo:
- Nome, E-mail, Telefone formatado
- Origem declarada e mensagem do cliente
- **Botão com link direto para iniciar conversa no WhatsApp** com mensagem contextualizada

---

## 🚀 Deploy em Produção (Hostinger)

O guia passo a passo completo e validado no ambiente real da Hostinger CloudLinux está documentado em:
👉 **[`deploy/hostinger/README.md`](deploy/hostinger/README.md)**

### Resumo Rápido da Arquitetura:
- **Subdomínio:** `api.augepanamby.net.br` configurado com **PHP 8.3** e SSL ativo no hPanel.
- **Isolamento de Segurança:** O repositório Laravel é clonado em `~/domains/api.augepanamby.net.br/laravel` (fora do alcance público da web).
- **Webroot (`public_html`):** Recebe o front controller seguro `deploy/hostinger/index.php` e `.htaccess`, garantindo que arquivos como `.env` e `storage/logs` fiquem inacessíveis.
- **Deploy em 1 Comando:** Execute `bash deploy/hostinger/deploy.sh` para atualizar o projeto com migrações e caches automáticos.
- **Cron Job (LGPD):**
  ```bash
  /opt/alt/php83/usr/bin/php /home/u427907551/domains/api.augepanamby.net.br/laravel/artisan schedule:run >> /dev/null 2>&1
  ```

---

## 🧪 Testes Automatizados

```bash
# Rodar todos os testes
./vendor/bin/pest

# Rodar com filtro
./vendor/bin/pest --filter=LeadPrivacyTest

# Formatação de código
./vendor/bin/pint
```

