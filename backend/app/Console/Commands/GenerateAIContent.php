<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateAIContentJob;
use App\Models\Document;
use Illuminate\Console\Command;

class GenerateAIContent extends Command
{
    protected $signature = 'generate:ai-content {--document= : Specific document ID}';

    protected $description = 'Generate AI-powered documentation content';

    public function handle(): int
    {
        $query = Document::query()->where('ai_generated', false);

        if ($documentId = $this->option('document')) {
            $query->where('id', $documentId);
        }

        $documents = $query->limit(10)->get();

        foreach ($documents as $document) {
            GenerateAIContentJob::dispatch($document);
            $this->line("Dispatched AI generation for: {$document->title}");
        }

        $this->info("Dispatched {$documents->count()} AI content job(s).");

        return self::SUCCESS;
    }
}
