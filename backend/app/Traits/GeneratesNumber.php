<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GeneratesNumber
{
    public static function generateNumber(string $prefix, string $table, string $column, string $dateFormat = 'Y'): string
    {
        $datePart = now()->format($dateFormat);
        $pattern = $prefix . '-' . $datePart . '-%';

        $lastNumber = DB::table($table)
            ->where($column, 'like', $pattern)
            ->orderBy($column, 'desc')
            ->value($column);

        if (!$lastNumber) {
            $sequence = 1;
        } else {
            $parts = explode('-', $lastNumber);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . '-' . $datePart . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function generateSimpleNumber(string $prefix, string $table, string $column, int $padLength = 3): string
    {
        $pattern = $prefix . '-%';
        $lastNumbers = DB::table($table)
            ->where($column, 'like', $pattern)
            ->pluck($column)
            ->map(fn ($c) => (int) preg_replace('/\D/', '', $c));

        $sequence = ($lastNumbers->isEmpty() ? 0 : $lastNumbers->max()) + 1;

        return $prefix . '-' . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);
    }
}
