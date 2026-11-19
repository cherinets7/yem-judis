<?php

namespace App\Enums;

trait HasValues
{
    /**
     * Backed values for migration enum columns.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
