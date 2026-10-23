<?php

use App\Enums\DiagnosisStatus;
use App\Enums\Filiere;
use App\Enums\Locale;
use App\Enums\LotStep;
use App\Enums\OrderStatus;
use App\Enums\QualityGrade;
use App\Enums\Region;
use App\Enums\UserRole;

test('every enum value has a label in each supported locale', function (string $locale) {
    app()->setLocale($locale);

    $cases = [...UserRole::cases(), ...Filiere::cases(), ...Region::cases(), ...LotStep::cases(), ...QualityGrade::cases(), ...OrderStatus::cases(), ...DiagnosisStatus::cases(), ...Locale::cases()];

    foreach ($cases as $case) {
        expect($case->label())->not->toStartWith('enums.');
    }
})->with(['fr', 'en']);

test('labels follow the current locale', function () {
    app()->setLocale('fr');
    $french = [Filiere::Manioc->label(), OrderStatus::Delivered->label(), DiagnosisStatus::Uncertain->label()];

    app()->setLocale('en');
    $english = [Filiere::Manioc->label(), OrderStatus::Delivered->label(), DiagnosisStatus::Uncertain->label()];

    expect($french)->toBe(['Manioc', 'Livrée', 'Diagnostic incertain'])
        ->and($english)->toBe(['Cassava', 'Delivered', 'Uncertain diagnosis']);
});

test('the supported locales match the locale enum', function () {
    expect(config('app.supported_locales'))->toBe(array_column(Locale::cases(), 'value'));
});
