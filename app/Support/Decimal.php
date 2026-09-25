<?php

namespace App\Support;

final class Decimal
{
    // APUNTE:
    // Las operaciones de dinero y cantidades comerciales pasan por bcmath para
    // evitar errores de redondeo típicos de los float en PHP.
    public static function add(mixed $left, mixed $right, int $scale = 2): string
    {
        return bcadd(self::value($left), self::value($right), $scale);
    }

    public static function sub(mixed $left, mixed $right, int $scale = 2): string
    {
        return bcsub(self::value($left), self::value($right), $scale);
    }

    public static function mul(mixed $left, mixed $right, int $scale = 2): string
    {
        return self::round(bcmul(self::value($left), self::value($right), $scale + 3), $scale);
    }

    public static function compare(mixed $left, mixed $right, int $scale = 2): int
    {
        return bccomp(self::value($left), self::value($right), $scale);
    }

    public static function round(mixed $value, int $scale = 2): string
    {
        $number = self::value($value);
        $step = self::roundingStep($scale);

        if (str_starts_with($number, '-')) {
            return bcsub($number, $step, $scale);
        }

        return bcadd($number, $step, $scale);
    }

    private static function value(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return (string) $value;
    }

    private static function roundingStep(int $scale): string
    {
        if ($scale <= 0) {
            return '0.5';
        }

        return '0.'.str_repeat('0', $scale).'5';
    }
}
