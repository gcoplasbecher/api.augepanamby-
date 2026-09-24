<?php

namespace App\Enums;

enum LeadStatus: string
{
    case Novo = 'novo';
    case EmAtendimento = 'em_atendimento';
    case VisitaAgendada = 'visita_agendada';
    case Qualificado = 'qualificado';
    case Descartado = 'descartado';
    case Convertido = 'convertido';

    public function label(): string
    {
        return match ($this) {
            self::Novo => 'Novo',
            self::EmAtendimento => 'Em Atendimento',
            self::VisitaAgendada => 'Visita Agendada',
            self::Qualificado => 'Qualificado',
            self::Descartado => 'Descartado',
            self::Convertido => 'Convertido',
        };
    }
}
