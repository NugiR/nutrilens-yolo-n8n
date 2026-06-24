<?php

namespace App\Enums;

enum MealType: string
{
    case Pagi = 'pagi';
    case Siang = 'siang';
    case Malam = 'malam';

    public function label(): string
    {
        return match ($this) {
            self::Pagi => 'Makan Pagi',
            self::Siang => 'Makan Siang',
            self::Malam => 'Makan Malam',
        };
    }
}
