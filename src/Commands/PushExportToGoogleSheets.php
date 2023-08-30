<?php

namespace Enflow\LaravelExcelToGoogleSheet\Commands;

use Enflow\LaravelExcelToGoogleSheet\Exceptions\InvalidConfiguration;
use Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelToGoogleSheet\GoogleSheetPusher;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class PushExportToGoogleSheets extends Command
{
    public $signature = 'push-export-to-google-sheets {--export=}';

    public $description = 'Push a specific export to Google Sheets';

    public function handle(GoogleSheetPusher $googleSheetPusher): int
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

        $this->warn("Pushing {$export} to Google Sheets...");

        /** @var \Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet $exporter */
        $exporter = new $export();

        if (! $exporter instanceof ExportableToGoogleSheet) {
            throw InvalidConfiguration::exportMustImplementExportableToGoogleSheet($export);
        }

        $googleSheetPusher->__invoke($exporter);

        $this->info("Pushed {$export} to Google Sheets");

        return self::SUCCESS;
    }

    private function askExport(): ?string
    {
        $exports = config('excel-to-google-sheet.exports', []);

        if ($this->option('export')) {
            return Arr::get($exports, $this->option('export'));
        }

        $exportName = $this->choice('Which export do you want to push?', array_keys($exports));

        return $exports[$exportName] ?? null;
    }
}
