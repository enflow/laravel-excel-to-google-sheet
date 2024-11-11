<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Exceptions\MustImplementExportable;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetService;
use Exception;
use Google\Exception as GoogleException;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writer;

class PushHandler
{
    public function __invoke(Exportable $export): void
    {
        $pusher = $this->pusher($export);

        if (method_exists($export, 'prepare')) {
            $export->prepare();
        }

        $writer = app(Writer::class)->export($export, Excel::CSV);
        $temporaryFilePath = $writer->getLocalPath();

        $handle = fopen($temporaryFilePath, 'r');

        $pusher->clear();

        try {
            LazyCollection::make(function () use ($handle) {
                while ($line = fgetcsv($handle)) {
                    yield $line;
                }
            })->chunk(5000)->each(function (LazyCollection $chunk) use ($pusher) {
                $pusher->insert($chunk);
            });
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }

            // Clean up the local file
            if (is_file($temporaryFilePath) && file_exists($temporaryFilePath)) {
                unlink($temporaryFilePath);
            }
        }
    }

    private function pusher(Exportable $export): Pusher
    {
        $class = match (true) {
            $export instanceof ExportableToGoogleSheet => GoogleSheetPusher::class,
            default => throw new Exception('Must implement specific pusher for exportable'),
        };

        return app($class);
    }
}
