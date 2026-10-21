<?php

use App\Enums\MessageRole;
use App\Models\AiConversation;
use App\Models\AiMessage;

test('a conversation keeps its messages and their metadata', function () {
    $conversation = AiConversation::factory()->create();
    AiMessage::factory()->for($conversation, 'conversation')->create();
    AiMessage::factory()->assistant()->for($conversation, 'conversation')->create();

    $answer = $conversation->messages()->where('role', MessageRole::Assistant)->sole();

    expect($conversation->messages)->toHaveCount(2)
        ->and($conversation->user->aiConversations->modelKeys())->toBe([$conversation->id])
        ->and($answer->sources)->toBeArray()
        ->and($answer->provider)->toBe('groq');
});

test('deleting a conversation deletes its messages', function () {
    $conversation = AiConversation::factory()->create();
    AiMessage::factory()->count(3)->for($conversation, 'conversation')->create();

    $conversation->delete();

    expect(AiMessage::count())->toBe(0);
});
