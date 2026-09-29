<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function process(Attachment $attachment): void
    {
        $this->handleThumbnail($attachment);
        $this->handleTextExtraction($attachment);
    }

    private function handleThumbnail(Attachment $attachment): void
    {
        if ($attachment->type !== 'image') {
            return;
        }

        try {
            $intervention = app('image');

            $image = $intervention->read(
                Storage::disk(config('storage.attachments_disk'))->get($attachment->file_key)
            );

            $thumbnailKey = "thumbnails/{$attachment->file_key}";

            Storage::disk(config('storage.public_disk'))
                ->put($thumbnailKey, $image->toWebp(80));

            $attachment->update([
                'thumbnail_key' => $thumbnailKey,
                'thumbnail_url' => Storage::disk(config('storage.public_disk'))->url($thumbnailKey),
            ]);
        } catch (\Throwable) {
            // Thumbnail generation failure should not fail the attachment
        }
    }

    private function handleTextExtraction(Attachment $attachment): void
    {
        if ($attachment->mime_type !== 'application/pdf' && !str_starts_with($attachment->mime_type, 'text/')) {
            return;
        }

        try {
            $path = Storage::disk(config('storage.attachments_disk'))
                ->path($attachment->file_key);

            $content = match (true) {
                str_starts_with($attachment->mime_type, 'text/') => file_get_contents($path),
                $attachment->mime_type === 'application/pdf' => $this->extractPdfText($path),
                default => null,
            };

            if ($content !== null) {
                $attachment->update([
                    'extracted_text' => mb_substr($content, 0, 100000),
                ]);
            }
        } catch (\Throwable) {
            // Extraction failure should not fail the attachment
        }
    }

    private function extractPdfText(string $path): ?string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return null;
        }

        $parser = new \Smalot\PdfParser\Parser();
        $text = $parser->parseFile($path)->getText();

        return $text;
    }
}
