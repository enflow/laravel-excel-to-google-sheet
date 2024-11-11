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

        if (empty($export)) {
            $this->error('No export selected');

            return self::FAILURE;
        }

        if (! class_exists($export)) {
            $this->error("Export `{$export}` does not exist");

            return self::FAILURE;
        }

        $this->warn("Pushing {$export}...");

        $this->increaseMemoryLimitIfRequired();

        /** @var \Enflow\LaravelExcelExporter\Exportable $exporter */
        $exporter = new $export();

        $pushHandler = app(PushHandler::class);
        $pushHandler->__invoke($exporter);

        $this->info("Pushed {$export}");

        return self::SUCCESS;
    }

    private function askExport(): ?string
    {
        $exports = config('excel-exporter.exports', []);

        if ($this->option('export')) {
            return Arr::get($exports, $this->option('export'));
        }

        $exportName = $this->choice('Which export do you want to push?', array_keys($exports));

        return $exports[$exportName] ?? null;
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
