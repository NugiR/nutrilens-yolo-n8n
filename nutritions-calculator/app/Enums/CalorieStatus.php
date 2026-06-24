<?php

namespace App\Enums;

enum CalorieStatus: string
{
    case Kurang = 'kurang';
    case Cukup = 'cukup';
    case Kelebihan = 'kelebihan';

    public function label(): string
    {
        return match ($this) {
            self::Kurang => 'Kurang kalori',
            self::Cukup => 'Cukup kalori',
            self::Kelebihan => 'Kelebihan kalori',
        };
    }

    /** Card background color classes for history view */
    public function bgClass(): string
    {
        return match ($this) {
            self::Kurang => 'bg-[#FFF3CD] border-[#FFECB5]',
            self::Cukup => 'bg-[#D4EDDA] border-[#C3E6CB]',
            self::Kelebihan => 'bg-[#F8D7DA] border-[#F5C6CB]',
        };
    }
}
