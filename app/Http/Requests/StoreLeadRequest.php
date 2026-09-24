<?php

namespace App\Http\Requests;

use App\Enums\LeadSource;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => is_string($this->nome) ? trim($this->nome) : $this->nome,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
            'telefone' => is_string($this->telefone) ? trim($this->telefone) : $this->telefone,
            'como_conheceu' => is_string($this->como_conheceu) ? trim($this->como_conheceu) : $this->como_conheceu,
            'mensagem' => is_string($this->mensagem) ? trim($this->mensagem) : $this->mensagem,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:180'],
            'telefone' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! PhoneNumber::isValidBr((string) $value)) {
                        $fail('O número de telefone/WhatsApp informado é inválido.');
                    }
                },
            ],
            'como_conheceu' => ['nullable', 'string', Rule::in(LeadSource::values())],
            'mensagem' => ['nullable', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
            'website' => ['nullable', 'string', 'max:100'], // Honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 120 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo 180 caracteres.',
            'telefone.required' => 'O telefone / WhatsApp é obrigatório.',
            'como_conheceu.in' => 'Opção de origem inválida.',
            'mensagem.max' => 'A mensagem deve ter no máximo 2000 caracteres.',
            'consent.required' => 'É necessário concordar com os termos para prosseguir.',
            'consent.accepted' => 'É necessário concordar com a política de privacidade e termos.',
        ];
    }
}
