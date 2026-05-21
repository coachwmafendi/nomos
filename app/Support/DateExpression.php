<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DateExpression
{
    public static function year(string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "EXTRACT(YEAR FROM {$column})"
            : "CAST(strftime('%Y', {$column}) AS INTEGER)";
    }

    public static function month(string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "EXTRACT(MONTH FROM {$column})"
            : "CAST(strftime('%m', {$column}) AS INTEGER)";
    }

    public static function day(string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "EXTRACT(DAY FROM {$column})"
            : "CAST(strftime('%d', {$column}) AS INTEGER)";
    }

    public static function dayOfWeek(string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "EXTRACT(DOW FROM {$column})"
            : "CAST(strftime('%w', {$column}) AS INTEGER)";
    }

    public static function yearMonth(string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "TO_CHAR({$column}, 'YYYY-MM')"
            : "strftime('%Y-%m', datetime({$column}))";
    }

    public static function format(string $format, string $column = 'date'): string
    {
        $driver = DB::connection()->getDriverName();

        $pgFormats = [
            '%Y' => 'YYYY',
            '%m' => 'MM',
            '%d' => 'DD',
            '%w' => 'DOW',
        ];

        if ($driver === 'pgsql') {
            $pgFormat = str_replace(array_keys($pgFormats), array_values($pgFormats), $format);

            return "TO_CHAR({$column}, '{$pgFormat}')";
        }

        return "strftime('{$format}', {$column})";
    }
}
