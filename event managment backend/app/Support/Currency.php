<?php

namespace App\Support;

class Currency
{
    private const SYMBOL = "\u{20B9}";

    private const SYMBOLS = [
        'INR' => "\u{20B9}",
        'USD' => '$',
        'EUR' => "\u{20AC}",
        'GBP' => "\u{00A3}",
        'SGD' => 'SGD ',
        'AED' => 'AED ',
        'BRL' => 'R$',
    ];

    public static function inr(float|int|string|null $amount, int $decimals = 2): string
    {
        $value = (float) ($amount ?? 0);
        $sign = $value < 0 ? '-' : '';
        $absoluteValue = abs($value);
        $formatted = number_format($absoluteValue, $decimals, '.', '');
        [$wholePart, $fractionPart] = array_pad(explode('.', $formatted), 2, null);

        if (strlen($wholePart) > 3) {
            $lastThreeDigits = substr($wholePart, -3);
            $remainingDigits = substr($wholePart, 0, -3);
            $remainingDigits = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remainingDigits) ?: $remainingDigits;
            $wholePart = $remainingDigits.','.$lastThreeDigits;
        }

        return $sign.self::SYMBOL.$wholePart.($decimals > 0 && $fractionPart !== null ? '.'.$fractionPart : '');
    }

    public static function compactInr(float|int|string|null $amount, int $decimals = 2): string
    {
        $value = (float) ($amount ?? 0);
        $sign = $value < 0 ? '-' : '';
        $absoluteValue = abs($value);

        if ($absoluteValue >= 10000000) {
            return $sign.self::SYMBOL.self::trim(number_format($absoluteValue / 10000000, $decimals, '.', '')).' Cr';
        }

        if ($absoluteValue >= 100000) {
            return $sign.self::SYMBOL.self::trim(number_format($absoluteValue / 100000, $decimals, '.', '')).' Lakh';
        }

        if ($absoluteValue >= 1000) {
            return $sign.self::SYMBOL.self::trim(number_format($absoluteValue / 1000, 1, '.', '')).'K';
        }

        return self::inr($value, 0);
    }

    public static function format(float|int|string|null $amount, string $currencyCode = 'INR', int $decimals = 0): string
    {
        $code = strtoupper($currencyCode ?: 'INR');

        if ($code === 'INR') {
            return self::inr($amount, $decimals);
        }

        $value = (float) ($amount ?? 0);
        $symbol = self::SYMBOLS[$code] ?? $code.' ';

        return $symbol.number_format($value, $decimals);
    }

    private static function trim(string $value): string
    {
        return rtrim(rtrim($value, '0'), '.');
    }
}
