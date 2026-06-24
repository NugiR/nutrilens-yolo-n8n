<?php

namespace App\Enums;

enum MealLogStatus: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Failed = 'failed';
}
