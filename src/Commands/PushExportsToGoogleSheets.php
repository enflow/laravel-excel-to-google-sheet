<?php

namespace Enflow\LaravelExcelToGoogleSheet\Commands;

use Enflow\LaravelExcelToGoogleSheet\Exceptions\InvalidConfiguration;
use Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelToGoogleSheet\GoogleSheetPusher;
use Illuminate\Console\Command;
use Throwable;

class PushExportsToGoogleSheets extends Command
{
    public $signature = 'push-exports-to-google-sheets {--export=}';

    public $description = 'Push exports to Google Sheets';

    public function handle(GoogleSheetPusher $googleSheetPusher): int
    {
        collect(config('excel-to-google-sheet.exports', []))
            ->filter(fn (string $export) => ! $this->option('export') || $this->option('export') === $export)
            ->each(function (string $export) use ($googleSheetPusher) {
                if (! class_exists($export)) {
                    throw InvalidConfiguration::exportDoesNotExist($export);
                }

                $this->warn("Pushing {$export} to Google Sheets...");

                try {
                    /** @var \Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet $exporter */
                    $exporter = new $export();

                    if (! $exporter instanceof ExportableToGoogleSheet) {
                        throw InvalidConfiguration::exportMustImplementExportableToGoogleSheet($export);
                    }

                    $googleSheetPusher->__invoke($exporter);

                    $this->info("Pushed {$export} to Google Sheets");
                } catch (Throwable $e) {
                    throw_if(app()->environment('local'), $e);

                    $this->error("Failed to push {$export} to Google Sheets: {$e->getMessage()}. Continuing...");

                    report($e);
                }
            });

        $this->comment('All done');

        return self::SUCCESS;
    }
}
