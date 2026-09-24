<?php

namespace App\Enums;

enum LeadSource: string
{
    case Google = 'google';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Youtube = 'youtube';
    case Indicacao = 'indicacao';
    case Outro = 'outro';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
