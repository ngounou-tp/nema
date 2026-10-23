<?php

use App\Models\Cooperative;
use App\Models\Producer;

test('a cooperative only manages its own members', function () {
    $cooperative = Cooperative::factory()->create();
    $member = Producer::factory()->for($cooperative)->create();
    $outsider = Producer::factory()->create();
    $independent = Producer::factory()->independent()->create();

    expect($cooperative->manager->can('update', $member))->toBeTrue()
        ->and($cooperative->manager->can('delete', $member))->toBeTrue()
        ->and($cooperative->manager->can('update', $outsider))->toBeFalse()
        ->and($cooperative->manager->can('delete', $outsider))->toBeFalse()
        ->and($cooperative->manager->can('update', $independent))->toBeFalse();
});

test('a producer edits their own profile but cannot remove themselves from the cooperative', function () {
    $producer = Producer::factory()->create();

    expect($producer->user->can('update', $producer))->toBeTrue()
        ->and($producer->user->can('delete', $producer))->toBeFalse()
        ->and($producer->user->can('update', Producer::factory()->create()))->toBeFalse();
});
