<?php

namespace App\Services\AI;

/**
 * Groq: main text provider (fast open-weight models).
 */
class GroqClient extends OpenAiCompatibleClient
{
    protected function configKey(): string
    {
        return 'groq';
    }

    protected function endpoint(): string
    {
        return 'https://api.groq.com/openai/v1/chat/completions';
    }
}
