<?php

namespace App\Helpers;

use Carbon\Carbon;

class Format
{
    public static function currency(float|int|string|null $value, string $currency = 'IDR'): string
    {
        $value = (float) ($value ?? 0);

        return 'Rp'.number_format($value, 0, ',', '.');
    }

    public static function date(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    public static function dateTime(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)->translatedFormat('d M Y H:i');
    }
}
