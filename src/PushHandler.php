<?php

namespace Enflow\LaravelExcelExporter;

use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writer;
use Throwable;

readonly class PushHandler
{
    public function __construct(
        private ?Command $command = null,
    ) {}

    public function __invoke(Exportable $export): void
    {
        PusherFactory::make($export)->each(function (Pusher $pusher) use ($export) {
            $this->log('Pushing via '.$pusher::class, 'info');

            $writer = app(Writer::class)->export($export, Excel::CSV);
            $temporaryFilePath = $writer->getLocalPath();

            $this->log('Clearing destination...');

            // Ensure the export destination is empty.
            $pusher->clear();

            $handle = fopen($temporaryFilePath, 'r');

            $this->log('Pushing data...');

            try {
                LazyCollection::make(function () use ($handle) {
                    while ($line = fgetcsv($handle)) {
                        yield $line;
                    }
                })->chunk(5000)->each(function (LazyCollection $chunk, int $index) use ($pusher) {
                    $this->log("Inserting chunk #{$index} (".number_format($chunk->count()).' rows)...');

                    $pusher->insert($chunk, $index);
                });

                $this->log('Finished pushing data.');
            } catch (Throwable $e) {
                $this->log('Something went wrong: '.$e->getMessage(), 'error');

                // If something went wrong, we should clear the destination to avoid partial data.
                $pusher->clear();

                throw $e;
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }

                // Clean up the local file
                if (is_file($temporaryFilePath) && file_exists($temporaryFilePath)) {
                    unlink($temporaryFilePath);
                }
            }
        });
    }

    private function log(string $log, string $level = 'warn'): void
    {
        $this->command?->{$level}($level === 'info' ? $log : "- {$log}");
    }
}
