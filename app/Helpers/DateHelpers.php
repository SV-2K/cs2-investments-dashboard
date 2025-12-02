<?php

namespace App\Helpers;

class DateHelpers
{
    public static function convertSteamDate(string $date): string
    {
        // From "Nov 03 2025 00: +0" to "2025-11-03"

        $months = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3,
            'Apr' => 4, 'May' => 5, 'Jun' => 6,
            'Jul' => 7, 'Aug' => 8, 'Sep' => 9,
            'Oct' => 10, 'Nov' => 11, 'Dec' => 12,
        ];

        $date = explode(' ', substr($date, 0, 11));

        return $date[2] . '-' . $months[$date[0]] . '-' . $date[1];
    }
}
