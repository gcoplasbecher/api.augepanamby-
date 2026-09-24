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

## 🚀 Guia de Deploy na Hostinger

### Subdomínio Recomendado: `api.augepanamby.net.br`

1. **Criar Subdomínio no hPanel:**
   - Acesse o painel da Hostinger → **Domínios** → **Subdomínios**.
   - Crie: `api.augepanamby.net.br`.
   - Aponte a pasta raiz (**Document Root**) para: `domains/augepanamby.net.br/api.augepanamby/public`.

2. **Subir os Arquivos via SSH ou Git:**
   ```bash
   cd ~/domains/augepanamby.net.br
   git clone https://github.com/gcoplasbecher/api.augepanamby-.git api.augepanamby
   cd api.augepanamby
   ```

3. **Instalar Dependências sem Dev:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Configurar o `.env` de Produção:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edite `.env`:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://api.augepanamby.net.br
   FRONTEND_URL=https://augepanamby.net.br

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u123456789_leads
   DB_USERNAME=u123456789_user
   DB_PASSWORD=sua_senha_forte

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.hostinger.com
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   MAIL_USERNAME=contato@augepanamby.net.br
   MAIL_PASSWORD=senha_do_email
   MAIL_FROM_ADDRESS="contato@augepanamby.net.br"
   MAIL_FROM_NAME="Auge Panamby"

   LEAD_NOTIFY_EMAIL=contato@augepanamby.net.br
   ```

5. **Executar Migrations e Cache:**
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```

6. **Permissões de Pastas:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

7. **Configurar Cron Job (hPanel):**
   - Agendador de tarefas: a cada 1 minuto (`* * * * *`)
   - Comando:
     ```bash
     /usr/bin/php /home/u123456789/domains/augepanamby.net.br/api.augepanamby/artisan schedule:run >> /dev/null 2>&1
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

