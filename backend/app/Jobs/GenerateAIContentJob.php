<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Document;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class GenerateAIContentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function __construct(public Document $document, public array $options = [])
    {
    }

    public function handle(): void
    {
        Redis::throttle('ai-content')
            ->allow(10)
            ->every(30)
            ->then(fn () => $this->generate(), fn () => $this->release(30));
    }

    private function generate(): void
    {
        $document = $this->document;

        $prompt = $this->buildPrompt($document, $this->options);

        try {
            $content = app(AIService::class)->generate($prompt, [
                'document_id' => $document->id,
                'project_id' => $document->project_id,
                'content_type' => $document->content_type,
                ...$this->options,
            ]);

            $document->update([
                'content' => $content,
                'ai_generated' => true,
                'ai_metadata' => [
                    'generated_by' => 'AIService',
                    'model' => config('ai.driver'),
                    'prompt_version' => '1.0',
                    'confidence_score' => null,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }

    private function buildPrompt(Document $document, array $options): string
    {
        $instruction = $options['instruction']
            ?? 'Write clear, accurate, well-structured technical documentation.';

        $context = $this->summarize($document->content);

        return trim(<<<PROMPT
{$instruction}

# Document Title
{$document->title}

# Existing Content
{$context}
PROMPT);
    }

    private function summarize(?string $content): string
    {
        if (!$content) {
            return '(No existing content)';
        }

        return mb_strlen($content) > 8000
            ? mb_substr($content, 0, 8000) . '…'
            : $content;
    }
}