<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('sync:external-data')->twiceDaily();
Schedule::command('process:attachments')->everyFiveMinutes();
Schedule::command('tenant:health')->hourly();
Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('partitions:create')->daily();
