<?php

namespace Enflow\LaravelExcelExporter\Commands;

use Enflow\LaravelExcelExporter\PushHandler;
use Illuminate\Console\Command;
use Throwable;

class PushAll extends Command
{
    public $signature = 'excel-exporter:push-all';

    public $description = 'Push all Laravel Excel exports';

    public function handle(): int
    {
        collect(config('excel-exporter.exports', []))
            ->each(function (string $_, string $export) {
                $this->warn("Pushing {$export}...");

                try {
                    $this->call('excel-exporter:push', [
                        '--export' => $export,
                    ]);
                } catch (Throwable $e) {
                    throw_if(app()->environment('local'), $e);

                    $this->error("Failed to push {$export}: {$e->getMessage()}. Continuing...");

                    report($e);
                }
            });

        $this->comment('All done');

        return self::SUCCESS;
    }
}
