# 📖 Guia de Deploy na Hostinger — Auge Panamby API

Instruções precisas e validadas para o ambiente real da Hostinger CloudLinux (`u427907551`).

---

## 🗂️ Estrutura de Diretórios no Servidor

O subdomínio `api.augepanamby.net.br` possui raiz isolada na Hostinger. A aplicação Laravel fica **fora** da pasta pública `public_html`, garantindo que arquivos como `.env`, código-fonte e logs fiquem inacessíveis pela web:

```text
/home/u427907551/domains/api.augepanamby.net.br/
├── DO_NOT_UPLOAD_HERE
├── laravel/                  <-- Repositório do projeto (fora do webroot público)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── deploy/hostinger/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   └── .env
└── public_html/              <-- Raiz pública web do subdomínio
    ├── index.php             <-- Front controller adaptado apontando para ../laravel/
    ├── .htaccess             <-- Regras de rewrite e bloqueio de segurança
    ├── favicon.ico
    └── robots.txt
```

---

## ⚙️ Pré-requisitos no Painel da Hostinger (hPanel)

Antes de rodar comandos no terminal, realize estes 4 passos no painel:

1. **Subdomínio:**
   - Acesse **Domínios** → **Subdomínios** → selecione `augepanamby.net.br`.
   - Certifique-se de que `api.augepanamby.net.br` está criado.
2. **Versão do PHP no Subdomínio:**
   - Acesse **Avançado** → **Configuração do PHP**.
   - Selecione o subdomínio `api.augepanamby.net.br` e defina como **PHP 8.4**.
3. **Certificado SSL:**
   - Acesse **Segurança** → **SSL** e garanta que o SSL está ativo para `api.augepanamby.net.br`.
4. **Banco de Dados:**
   - **Não é necessário criar banco.** O projeto usa **SQLite** (`database/database.sqlite`), criado pelo `artisan migrate`.
   - Opcional: se preferir MySQL, acesse **Bancos de Dados** → **Gerenciamento de Banco de Dados**, crie o banco e ajuste `DB_CONNECTION=mysql` + `DB_*` no `.env` antes de rodar as migrações.

---

## 🚀 Passo a Passo de Instalação Inicial via SSH

Conecte-se via SSH ao servidor:
```bash
ssh -p 65002 u427907551@212.85.6.30
```

### 1. Clonar o projeto na pasta `laravel`
```bash
cd ~/domains/api.augepanamby.net.br
git clone https://github.com/gcoplasbecher/api.augepanamby-.git laravel
cd laravel
```

### 2. Instalar dependências sem ambiente de desenvolvimento
```bash
/opt/alt/php84/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### 3. Configurar arquivo `.env` de Produção
```bash
cp .env.example .env
/opt/alt/php84/usr/bin/php artisan key:generate
nano .env
```

Ajuste as seguintes variáveis no arquivo:
```dotenv
APP_NAME="Auge Panamby API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.augepanamby.net.br
FRONTEND_URL=https://augepanamby.net.br

# Banco de dados: SQLite (padrão do projeto, arquivo já vem com database/database.sqlite)
# Em hospedagem compartilhada o volume de leads é baixo e o SQLite evita um serviço extra.
DB_CONNECTION=sqlite

# Alternativa em MySQL (caso opte por migrar depois):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=u427907551_leads
# DB_USERNAME=u427907551_leads
# DB_PASSWORD="SUA_SENHA_DO_BANCO"

# OBRIGATÓRIO: sync para envio imediato de e-mails em hospedagem compartilhada
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=contato@augepanamby.net.br
MAIL_PASSWORD="SUA_SENHA_DE_EMAIL"
MAIL_FROM_ADDRESS="contato@augepanamby.net.br"
MAIL_FROM_NAME="Auge Panamby"

# Destinatário(s) das notificações de novo lead
LEAD_NOTIFY_EMAIL=contato@augepanamby.net.br
# Fuso usado para exibir as datas no e-mail (a aplicação grava datas em UTC)
LEAD_TIMEZONE=America/Sao_Paulo
```

### 4. Executar Migrações e Caches
```bash
/opt/alt/php84/usr/bin/php artisan migrate --force
/opt/alt/php84/usr/bin/php artisan config:cache
/opt/alt/php84/usr/bin/php artisan route:cache
chmod -R 775 storage bootstrap/cache
```

### 5. Publicar o Webroot no `public_html`
```bash
# Remover página padrão da Hostinger
rm -f ../public_html/default.php

# Copiar front controller adaptado e .htaccess seguro
cp deploy/hostinger/index.php ../public_html/index.php
cp deploy/hostinger/public_html.htaccess ../public_html/.htaccess
```

### 6. Testar o Endpoint de Saúde
No terminal ou navegador:
```bash
curl -i https://api.augepanamby.net.br/up
```
A resposta esperada é status `200 OK`.

---

## ⏰ Configuração do Agendador de Tarefas (Cron Job - LGPD)

O binário `crontab` **não está disponível via SSH** nesta hospedagem (compartilhada), portanto a tarefa deve ser criada pelo painel:

1. Acesse **Avançado** → **Cron Jobs** → aba **Custom**.
2. **Intervalo:** a cada hora (`0 * * * *`). Um intervalo de 1 minuto (`* * * * *`) também funciona, mas é desnecessário: o `leads:prune` está agendado para rodar diariamente às 03:00.
3. **Comando:**
   ```bash
   /opt/alt/php84/usr/bin/php /home/u427907551/domains/api.augepanamby.net.br/laravel/artisan schedule:run
   ```

> ⚠️ Use sempre `/opt/alt/php84/usr/bin/php`. O `/usr/bin/php` disponível no shell é o PHP 8.3, e o `composer.lock` exige **PHP >= 8.4.1** — o comando falharia com `Composer detected issues in your platform`.
>
> Nas primeiras horas, mantenha o cron **sem** `>> /dev/null 2>&1` para que o hPanel registre a saída e você consiga confirmar que a tarefa executou. Depois de validar, pode silenciar a saída.

Para conferir o que está agendado a qualquer momento:
```bash
/opt/alt/php84/usr/bin/php artisan schedule:list
/opt/alt/php84/usr/bin/php artisan schedule:run
```

---

## 🔄 Como Fazer Atualizações Futuras

Para publicar novas versões com um único comando:

```bash
cd ~/domains/api.augepanamby.net.br/laravel
bash deploy/hostinger/deploy.sh
```

O script `deploy.sh` executa `git pull`, atualiza dependências do Composer, roda migrações com segurança, atualiza os caches e mantém as permissões corretas.
