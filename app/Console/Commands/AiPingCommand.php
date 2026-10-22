<?php

namespace App\Console\Commands;

use App\Services\AI\AiClient;
use App\Services\AI\AiManager;
use App\Services\AI\Exceptions\AiProviderException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('nema:ai-ping')]
#[Description('Send a tiny request to each configured AI provider and show its status and latency')]
class AiPingCommand extends Command
{
    /**
     * Calls the clients directly: no cache, no quota, no demo fallback.
     */
    public function handle(): int
    {
        $textProviders = array_map('trim', explode(',', (string) config('nema.ai.text_providers')));
        $rows = [];
        $failed = false;

        foreach (AiManager::PROVIDERS as $name => $class) {
            /** @var AiClient $client */
            $client = app($class);
            $role = implode(' + ', array_filter([
                in_array($name, $textProviders, true) ? 'text #'.(array_search($name, $textProviders, true) + 1) : null,
                $name === 'gemini' ? 'vision' : null,
            ])) ?: 'unused';

            if (! $client->isConfigured()) {
                $rows[] = [$name, $role, $client->model(), '<comment>not configured</comment>', '-'];

                continue;
            }

            try {
                $response = $client->chat(
                    [['role' => 'user', 'content' => 'Reply with the single word: pong']],
                    ['max_tokens' => 256, 'temperature' => 0, 'timeout' => 15],
                );

                $rows[] = [$name, $role, $client->model(), '<info>OK</info>', $response->durationMs.' ms'];
            } catch (AiProviderException $exception) {
                $failed = true;
                $rows[] = [$name, $role, $client->model(), '<error>FAILED</error> '.$exception->getMessage(), '-'];
            }
        }

        $this->table(['Provider', 'Role', 'Model', 'Status', 'Latency'], $rows);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
