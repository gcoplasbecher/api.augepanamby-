<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novo Lead — Auge Panamby</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F8F6; font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #5E535B;">

  <div style="display: none; font-size: 1px; color: #F8F8F6; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
    Novo contato recebido: {{ $lead->nome }} &bull; {{ $phoneFormatted }} &bull; {{ $lead->email }}
  </div>

  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8F8F6; padding: 24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; border: 1px solid #E5E5E5;">

          <!-- Header Verde com Logo -->
          <tr>
            <td align="center" style="background-color: #1F8D56; padding: 32px 24px;">
              <a href="https://augepanamby.net.br" target="_blank" style="text-decoration: none;">
                <img src="https://augepanamby.net.br/images/logo.png" alt="Auge Panamby" width="220" style="display: block; max-width: 220px; width: 100%; height: auto;" />
              </a>
              <div style="margin-top: 14px;">
                <span style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); color: #FFFFFF; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; padding: 5px 14px; border-radius: 9999px;">
                  ✦ Novo Lead da Landing Page
                </span>
              </div>
            </td>
          </tr>

          <!-- Corpo do E-mail -->
          <tr>
            <td style="padding: 32px 32px 24px 32px;">
              <h1 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #3D343B; line-height: 1.3;">
                Olá, equipe de vendas!
              </h1>
              <p style="margin: 0 0 24px 0; font-size: 14px; color: #8A7E86; line-height: 1.5;">
                Um novo cliente em potencial acabou de solicitar apresentação sobre o <strong>Auge Panamby</strong>:
              </p>

              <!-- Card de Dados -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8F8F6; border: 1px solid #E5E5E5; border-radius: 12px; margin-bottom: 24px;">
                <tr>
                  <td style="padding: 20px;">
                    <div style="margin-bottom: 14px;">
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7E86; padding-bottom: 3px;">Nome Completo</div>
                      <div style="font-size: 16px; font-weight: 700; color: #3D343B;">{{ $lead->nome }}</div>
                    </div>

                    <div style="margin-bottom: 14px;">
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7E86; padding-bottom: 3px;">Telefone / WhatsApp</div>
                      <div style="font-size: 15px; font-weight: 600;">
                        <a href="tel:{{ $lead->telefone_e164 ?? $lead->telefone }}" style="color: #1F8D56; text-decoration: none;">📞 {{ $phoneFormatted }}</a>
                      </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7E86; padding-bottom: 3px;">E-mail</div>
                      <div style="font-size: 14px;">
                        <a href="mailto:{{ $lead->email }}" style="color: #1F8D56; text-decoration: none;">✉️ {{ $lead->email }}</a>
                      </div>
                    </div>

                    @if($lead->como_conheceu)
                    <div style="margin-bottom: 14px;">
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7E86; padding-bottom: 3px;">Como Conheceu</div>
                      <span style="display: inline-block; background-color: #C7E8D1; color: #1F8D56; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 4px;">
                        {{ ucfirst($lead->como_conheceu) }}
                      </span>
                    </div>
                    @endif

                    @if($lead->mensagem)
                    <div>
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #8A7E86; padding-bottom: 3px;">Mensagem do Cliente</div>
                      <div style="font-size: 13px; color: #5E535B; line-height: 1.6; background-color: #FFFFFF; padding: 12px; border-radius: 8px; border-left: 3px solid #2FB66D; font-style: italic;">
                        "{{ $lead->mensagem }}"
                      </div>
                    </div>
                    @endif
                  </td>
                </tr>
              </table>

              <!-- Botões de Ação -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
                <tr>
                  <td align="center">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td align="center" style="border-radius: 10px; background-color: #25D366; padding: 2px;">
                          <a href="{{ $whatsUrl }}" target="_blank" style="display: inline-block; padding: 14px 24px; font-size: 14px; font-weight: 700; color: #FFFFFF; text-decoration: none;">
                            💬 Chamar no WhatsApp
                          </a>
                        </td>
                        <td width="12"></td>
                        <td align="center" style="border-radius: 10px; background-color: #FFFFFF; border: 1px solid #2FB66D;">
                          <a href="mailto:{{ $lead->email }}?subject=Re:%20Apresenta%C3%A7%C3%A3o%20Auge%20Panamby" target="_blank" style="display: inline-block; padding: 13px 20px; font-size: 14px; font-weight: 600; color: #1F8D56; text-decoration: none;">
                            ✉️ Responder E-mail
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Nota LGPD -->
              <div style="border-top: 1px dashed #E5E5E5; padding-top: 14px; font-size: 11px; color: #8A7E86; line-height: 1.5;">
                🛡️ <strong>Registro de Conformidade LGPD (Lei 13.709/2018):</strong><br>
                Consentimento explícito aceito em {{ $lead->consent_at?->format('d/m/Y \à\s H:i:s') ?? now()->format('d/m/Y \à\s H:i:s') }} (versão {{ $lead->consent_version ?? '2026-09' }}).
              </div>
            </td>
          </tr>

          <!-- Rodapé Institucional -->
          <tr>
            <td align="center" style="background-color: #F8F8F6; padding: 18px 24px; border-top: 1px solid #E5E5E5;">
              <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; color: #5E535B;">
                Auge Panamby — O novo padrão de viver no Panamby
              </p>
              <p style="margin: 0; font-size: 11px; color: #8A7E86;">
                Rua Marie Nader Calfat, 499 — Panamby, São Paulo/SP &bull; <a href="https://augepanamby.net.br" target="_blank" style="color: #1F8D56; text-decoration: none;">augepanamby.net.br</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
