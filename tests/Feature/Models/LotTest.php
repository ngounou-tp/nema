<?php

use App\Models\Lot;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

test('a new lot gets a random version 4 uuid code', function () {
    $lot = Lot::factory()->create();

    expect(Str::isUuid($lot->code))->toBeTrue()
        ->and($lot->code[14])->toBe('4')
        ->and($lot->getRouteKey())->toBe($lot->code);
});

test('two lots cannot share the same code', function () {
    $lot = Lot::factory()->create();

    expect(fn () => Lot::factory()->create(['code' => $lot->code]))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('traceability steps are stored in order', function () {
    $steps = [
        ['label' => 'harvest', 'date' => '2026-07-06', 'location' => 'Obala'],
        ['label' => 'fermentation', 'date' => '2026-07-07', 'location' => 'Obala'],
    ];

    $lot = Lot::factory()->create(['steps' => $steps]);

    expect($lot->fresh()->steps)->toBe($steps)
        ->and($lot->fresh()->harvest_date)->toBeInstanceOf(CarbonInterface::class);
});
