<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessAttachmentJob;
use App\Models\Attachment;
use Illuminate\Console\Command;

class ProcessAttachments extends Command
{
    protected $signature = 'process:attachments {--limit=50 : Maximum number of attachments to process}';

    protected $description = 'Process pending attachments in the pipeline';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $attachments = Attachment::query()
            ->where('processing_status', 'pending')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($attachments as $attachment) {
            ProcessAttachmentJob::dispatch($attachment);
            $this->line("Dispatched attachment: {$attachment->file_name}");
        }

        $this->info("Dispatched {$attachments->count()} attachment job(s).");

        return self::SUCCESS;
    }
}
