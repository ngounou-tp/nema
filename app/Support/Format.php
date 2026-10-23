<?php

namespace App\Support;

use DateTimeInterface;
use IntlDateFormatter;
use NumberFormatter;

/**
 * Locale-aware formats, mirrored by resources/js/lib/format.ts:
 * « 12 500 FCFA » in French, "12,500 XAF" in English, dates in fr-CM / en-CM.
 */
final class Format
{
    /**
     * Non-breaking space between a number and its unit, so they never wrap apart.
     */
    private const string UNIT_SPACE = "\u{00A0}";

    public static function number(int|float $value, int $decimals = 0, ?string $locale = null): string
    {
        $formatter = new NumberFormatter(self::intlLocale($locale), NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return (string) $formatter->format($value);
    }

    /**
     * Amount in CFA francs: « FCFA » in French, the ISO code « XAF » in English.
     */
    public static function money(int $amountXaf, ?string $locale = null): string
    {
        $unit = self::language($locale) === 'en' ? 'XAF' : 'FCFA';

        return self::number($amountXaf, 0, $locale).self::UNIT_SPACE.$unit;
    }

    /**
     * Kilograms up to a tonne, then tonnes.
     */
    public static function weight(int|float $kilograms, ?string $locale = null): string
    {
        if ($kilograms >= 1000) {
            return self::number($kilograms / 1000, 1, $locale).self::UNIT_SPACE.'t';
        }

        return self::number($kilograms, 1, $locale).self::UNIT_SPACE.'kg';
    }

    public static function area(int|float $hectares, ?string $locale = null): string
    {
        return self::number($hectares, 2, $locale).self::UNIT_SPACE.'ha';
    }

    public static function date(DateTimeInterface $date, ?string $locale = null): string
    {
        $formatter = new IntlDateFormatter(self::intlLocale($locale), IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, $date->getTimezone());

        return (string) $formatter->format($date);
    }

    private static function language(?string $locale): string
    {
        return $locale ?? app()->getLocale();
    }

    private static function intlLocale(?string $locale): string
    {
        return self::language($locale).'-CM';
    }
}
