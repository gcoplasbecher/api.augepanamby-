<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeadStatus;
use App\Events\LeadReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Recebe e processa uma submissão de contato da landing page.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        // 1. Honeypot check (silencioso para não instruir bots)
        if ($request->filled('website')) {
            Log::info('Lead descartado silenciosamente por honeypot.', [
                'ip_hash' => $this->hashIp($request->ip()),
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Recebemos sua solicitação! Entraremos em contato em breve.',
            ], 201);
        }

        $validated = $request->validated();
        $email = $validated['email'];
        $telefoneE164 = PhoneNumber::toE164($validated['telefone']);

        // 2. Deduplicação por e-mail ou telefone dentro da janela configurada
        $dedupeMinutes = (int) config('leads.dedupe_minutes', 5);
        $recentDuplicate = Lead::where('created_at', '>=', now()->subMinutes($dedupeMinutes))
            ->where(function ($query) use ($email, $telefoneE164) {
                $query->where('email', $email);
                if ($telefoneE164) {
                    $query->orWhere('telefone_e164', $telefoneE164);
                }
            })
            ->first();

        if ($recentDuplicate) {
            return response()->json([
                'ok' => true,
                'message' => 'Recebemos sua solicitação! Entraremos em contato em breve.',
            ], 201);
        }

        // 3. Registro do Lead com dados minimizados e rastreabilidade de consentimento LGPD
        $lead = Lead::create([
            'nome' => $validated['nome'],
            'email' => $email,
            'telefone' => $validated['telefone'],
            'telefone_e164' => $telefoneE164 ?? PhoneNumber::clean($validated['telefone']),
            'como_conheceu' => $validated['como_conheceu'] ?? null,
            'mensagem' => $validated['mensagem'] ?? null,
            'status' => LeadStatus::Novo,

            // LGPD: consentimento e metadados de auditoria minimizados
            'consent_at' => now(),
            'consent_version' => (string) config('leads.consent_version', '2026-09'),
            'ip_hash' => $this->hashIp($request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 255) ?: null,
            'referer' => substr((string) $request->header('referer'), 0, 255) ?: null,
        ]);

        // 4. Disparo do evento após o envio da resposta HTTP (zero atraso para o visitante)
        dispatch(function () use ($lead) {
            LeadReceived::dispatch($lead);
        })->afterResponse();

        return response()->json([
            'ok' => true,
            'message' => 'Recebemos sua solicitação! Entraremos em contato em breve.',
        ], 201);
    }

    /**
     * Gera hash irreversível de IP com HMAC da APP_KEY (LGPD art. 6º, III - minimização).
     */
    protected function hashIp(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        $appKey = (string) config('app.key');

        return hash_hmac('sha256', $ip, $appKey);
    }
}
