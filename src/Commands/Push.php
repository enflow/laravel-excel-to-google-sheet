<?php

namespace Enflow\LaravelExcelExporter\Commands;

use Enflow\LaravelExcelExporter\PushHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Push extends Command
{
    public $signature = 'excel-exporter:push {--export=}';

    public $description = 'Push a specific excel to exporter';

    public function handle(): int
    {
        $export = $this->askExport();

        if (! $export) {
            return self::FAILURE;
        }

        if (! class_exists($export)) {
            $this->error("Export `{$export}` does not exist");

            return self::FAILURE;
        }

        $this->increaseMemoryLimitIfRequired();

        app(PushHandler::class, ['command' => $this])->__invoke(app($export));

        return self::SUCCESS;
    }

    private function askExport(): ?string
    {
        $exports = config('excel-exporter.exports', []);

        if ($this->option('export')) {
            $export = Arr::get($exports, $this->option('export'));

            if (empty($export)) {
                $this->error("Export `{$this->option('export')}` does not exist.");

                return null;
            }

            return $export;
        }

        $exportName = $this->choice('Which export do you want to push?', array_keys($exports));

        if (empty($exports[$exportName])) {
            $this->error('No export selected.');

            return null;
        }

        return $exports[$exportName];
    }

    private function increaseMemoryLimitIfRequired(): void
    {
        $memoryLimit = config('excel-exporter.memory_limit');
        if ($memoryLimit === null) {
            return;
        }

        // `phpoffice/phpspreadsheet` uses a lot of memory while processing the export.
        // We'll allow the memory limit to be configured in the config.
        // TODO: search for a more long term solution.
        ini_set('memory_limit', $memoryLimit);
    }
}
