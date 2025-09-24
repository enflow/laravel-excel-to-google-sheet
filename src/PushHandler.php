<?php

namespace Enflow\LaravelExcelExporter;

use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writer;

class PushHandler
{
    public function __invoke(Exportable $export): void
    {
        $pusher = PusherFactory::make($export);

        $writer = app(Writer::class)->export($export, Excel::CSV);
        $temporaryFilePath = $writer->getLocalPath();

        // Ensure the export destination is empty.
        $pusher->clear();

        $handle = fopen($temporaryFilePath, 'r');

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
}
