<?php

namespace App\Console\Commands;

use App\Jobs\IndexMessageJob;
use App\Models\Message;
use Illuminate\Console\Command;

class ReindexMessages extends Command
{
    protected $signature = 'messages:es:reindex {--batch=500}';
    protected $description = 'Bulk dispatch index jobs for existing messages to Elasticsearch';

    public function handle(): int
    {
        $batch = (int) $this->option('batch');
        $this->info("Dispatching index jobs in batches of {$batch}...");

        Message::query()->orderBy('id')->chunk($batch, function ($messages) {
            foreach ($messages as $m) {
                dispatch(new IndexMessageJob($m->id));
                $this->line("Dispatched: {$m->id}");
            }
        });

        $this->info('Dispatch complete.');
        return 0;
    }
}
