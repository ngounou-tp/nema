import { getActiveLanguage } from 'laravel-vue-i18n';
import { intlLocale } from '@/lib/i18n';

/*
 * Locale-aware formats, mirrored by app/Support/Format.php:
 * « 12 500 FCFA » in French, "12,500 XAF" in English, dates in fr-CM / en-CM.
 */

/** Non-breaking space: a number and its unit never wrap apart. */
const UNIT_SPACE = ' ';

export function formatNumber(value: number, maximumFractionDigits = 0): string {
    return new Intl.NumberFormat(intlLocale(), {
        maximumFractionDigits,
    }).format(value);
}

/** CFA francs: « FCFA » in French, the ISO code « XAF » in English. */
export function formatMoney(amountXaf: number): string {
    const unit = getActiveLanguage() === 'en' ? 'XAF' : 'FCFA';

    return `${formatNumber(amountXaf)}${UNIT_SPACE}${unit}`;
}

/** Weights: kilograms up to a tonne, then tonnes. */
export function formatWeight(kilograms: number): string {
    if (kilograms >= 1000) {
        return `${formatNumber(kilograms / 1000, 1)}${UNIT_SPACE}t`;
    }

    return `${formatNumber(kilograms, 1)}${UNIT_SPACE}kg`;
}

export function formatArea(hectares: number): string {
    return `${formatNumber(hectares, 2)}${UNIT_SPACE}ha`;
}

export function formatDate(
    value: string | Date,
    options: Intl.DateTimeFormatOptions = { dateStyle: 'medium' },
): string {
    return new Intl.DateTimeFormat(intlLocale(), options).format(
        typeof value === 'string' ? new Date(value) : value,
    );
}

export function formatPercent(ratio: number): string {
    return new Intl.NumberFormat(intlLocale(), {
        style: 'percent',
        maximumFractionDigits: 0,
    }).format(ratio);
}
