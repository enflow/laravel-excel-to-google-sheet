<?php

namespace Enflow\LaravelExcelToGoogleSheet\Commands;

use Enflow\LaravelExcelToGoogleSheet\GoogleSheetPusher;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class PushAllExportsToGoogleSheets extends Command
{
    public $signature = 'push-all-exports-to-google-sheets';

    public $description = 'Push exports to Google Sheets';

    public function handle(GoogleSheetPusher $googleSheetPusher): int
    {
        collect(config('excel-to-google-sheet.exports', []))
            ->each(function (string $_, string $export) {
                $this->warn("Pushing {$export} to Google Sheets...");

                try {
                    $this->call('push-export-to-google-sheets', [
                        '--export' => $export,
                    ]);
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
