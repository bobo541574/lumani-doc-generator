<?php

declare(strict_types=1);

namespace App\Services;

class AIService
{
    public function generate(string $prompt, array $context = []): string
    {
        $driver = config('ai.driver', 'none');

        return match ($driver) {
            'openai' => $this->viaOpenAI($prompt, $context),
            'anthropic' => $this->viaAnthropic($prompt, $context),
            default => throw new \RuntimeException('AI driver not configured'),
        };
    }

    private function viaOpenAI(string $prompt, array $context): string
    {
        $client = \OpenAI::client(config('ai.openai.api_key'));

        $system = 'You are a technical documentation specialist.';
        $message = $this->withContext($prompt, $context);

        $response = $client->chat()->create([
            'model' => config('ai.openai.model', 'gpt-4o'),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $response->choices[0]->message->content;
    }

    private function viaAnthropic(string $prompt, array $context): string
    {
        $response = \Anthropic\Laravel\Facades\Anthropic::messages()->create([
            'model' => config('ai.anthropic.model', 'claude-3-5-sonnet-20241022'),
            'max_tokens' => (int) config('ai.generation.max_tokens', 4096),
            'messages' => [
                ['role' => 'user', 'content' => $this->withContext($prompt, $context)],
            ],
        ]);

        return $response->content[0]->text;
    }

    private function withContext(string $prompt, array $context): string
    {
        if (empty($context)) {
            return $prompt;
        }

        $lines = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) && $value !== '') {
                $lines[] = "{$key}: {$value}";
            }
        }

        if (empty($lines)) {
            return $prompt;
        }

        return $prompt . "\n\n--- Context ---\n" . implode("\n", $lines);
    }
}