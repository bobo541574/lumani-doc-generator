<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Attachment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentProcessed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Attachment $attachment, public bool $success = true)
    {
    }
}
