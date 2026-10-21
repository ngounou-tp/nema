<?php

use App\Models\Cooperative;
use App\Models\Producer;

test('a cooperative lists only its member producers', function () {
    $cooperative = Cooperative::factory()->create();
    $members = Producer::factory()->count(2)->for($cooperative)->create();
    Producer::factory()->create();
    Producer::factory()->independent()->create();

    expect($cooperative->producers->modelKeys())->toEqualCanonicalizing($members->modelKeys());
});

test('filieres are stored as a list', function () {
    $cooperative = Cooperative::factory()->create(['filieres' => ['cacao', 'manioc']]);

    expect($cooperative->fresh()->filieres)->toBe(['cacao', 'manioc']);
});

test('deleting the manager keeps the cooperative without a manager', function () {
    $cooperative = Cooperative::factory()->create();

    $cooperative->manager->delete();

    expect($cooperative->fresh())->not->toBeNull()
        ->and($cooperative->fresh()->manager_id)->toBeNull();
});
