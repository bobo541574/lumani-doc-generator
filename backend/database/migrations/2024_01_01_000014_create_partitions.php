<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Provision initial partitions for the declaratively range-partitioned
     * tables (incoming_data_queue, activity_logs) so writes succeed out of the
     * box. Ongoing provisioning is handled by the scheduler via
     * `partitions:create` (see routes/console.php).
     */
    public function up(): void
    {
        Artisan::call('partitions:create');
    }

    public function down(): void
    {
        // Partitions are dropped with their parent tables; nothing to undo here.
    }
};
