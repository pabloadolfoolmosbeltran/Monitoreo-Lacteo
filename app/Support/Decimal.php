<?php

namespace App\Support;

final class Decimal
{
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
