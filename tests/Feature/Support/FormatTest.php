<?php

use App\Support\Format;

/**
 * ICU separates thousands with a narrow no-break space in French and NEMA puts a
 * no-break space before units: compare with plain spaces.
 */
function plainSpaces(string $text): string
{
    return str_replace(["\u{202F}", "\u{00A0}"], ' ', $text);
}

test('amounts read « 12 500 FCFA » in French and "12,500 XAF" in English', function () {
    expect(plainSpaces(Format::money(12500, 'fr')))->toBe('12 500 FCFA')
        ->and(Format::money(12500, 'en'))->toBe("12,500\u{00A0}XAF");
});

test('amounts follow the current locale by default', function () {
    app()->setLocale('en');

    expect(Format::money(4100))->toBe("4,100\u{00A0}XAF");
});

test('weights switch to tonnes from 1 000 kg', function () {
    expect(plainSpaces(Format::weight(850, 'fr')))->toBe('850 kg')
        ->and(plainSpaces(Format::weight(2500, 'fr')))->toBe('2,5 t')
        ->and(plainSpaces(Format::weight(2500, 'en')))->toBe('2.5 t');
});

test('dates are written in French or English as used in Cameroon', function () {
    $date = new DateTimeImmutable('2026-10-25');

    expect(plainSpaces(Format::date($date, 'fr')))->toBe('25 oct. 2026')
        ->and(Format::date($date, 'en'))->toBe('25 Oct 2026');
});
