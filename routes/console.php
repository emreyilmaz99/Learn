<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Message;
use App\Jobs\IndexMessageJob;

Artisan::command('messages:es:reindex {--batch=500}', function () {
    $batch = (int) $this->option('batch');
    $this->info("Dispatching index jobs in batches of {$batch}...");

    Message::query()->orderBy('id')->chunk($batch, function ($messages) {
        foreach ($messages as $m) {
            dispatch(new IndexMessageJob($m->id));
            $this->line("Dispatched: {$m->id}");
        }
    });

    $this->info('Dispatch complete.');
})->describe('Bulk dispatch index jobs for existing messages to Elasticsearch');
