<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BulkIndexMessages extends Command
{
    protected $signature = 'es:bulk-index-messages {--batch=500}';
    protected $description = 'Bulk index messages into Elasticsearch using the _bulk API (no queue).';

    public function handle(): int
    {
        $batch = (int) $this->option('batch');

        $esHost = config('elasticsearch.host') ?: env('ES_HOST');
        $esPort = env('ES_PORT');
        $esUser = config('elasticsearch.default.username') ?: env('ES_USER');
        $esPass = config('elasticsearch.default.password') ?: env('ES_PASSWORD');
        $esSkipTls = config('elasticsearch.default.skip_tls_verify') ?? filter_var(env('ES_SKIP_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN);

        if (empty($esHost)) {
            $this->error('Elasticsearch host not configured (check config/elasticsearch.php or ES_HOST env).');
            return 1;
        }

        // build base URL
        $esHostFull = $esHost;
        if (!preg_match('#^https?://#i', $esHostFull)) {
            $scheme = filter_var(env('ES_FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://';
            $esHostFull = $scheme . $esHostFull;
        }
        if (!empty($esPort)) {
            $parts = parse_url($esHostFull);
            if (empty($parts['port'])) {
                $esHostFull = rtrim($esHostFull, '/') . ':' . $esPort;
            }
        }

        $this->info('Indexing messages to Elasticsearch at ' . $esHostFull);

        $total = 0;
        Message::query()->orderBy('id')->chunk($batch, function ($messages) use (&$total, $esHostFull, $esUser, $esPass, $esSkipTls) {
            // Build NDJSON bulk payload
            $lines = [];
            foreach ($messages as $m) {
                $meta = [
                    'index' => [
                        '_index' => 'messages',
                        '_id' => $m->id,
                    ],
                ];
                $doc = [
                    'id' => $m->id,
                    'title' => $m->title,
                    'content' => $m->content,
                    'sender_id' => $m->sender_id,
                    'receiver_id' => $m->receiver_id,
                    'sender_email' => $m->sender?->email ?? null,
                    'receiver_email' => $m->receiver?->email ?? null,
                    'sender_name' => $m->sender?->name ?? null,
                    'receiver_name' => $m->receiver?->name ?? null,
                    'created_at' => $m->created_at?->toIso8601String(),
                    'updated_at' => $m->updated_at?->toIso8601String(),
                ];

                $lines[] = json_encode($meta, JSON_UNESCAPED_UNICODE);
                $lines[] = json_encode($doc, JSON_UNESCAPED_UNICODE);
            }

            $payload = implode("\n", $lines) . "\n"; // NDJSON requires trailing newline

            $client = Http::withHeaders(['Content-Type' => 'application/x-ndjson'])->timeout(30);
            if (!empty($esUser) && !empty($esPass)) {
                $client = $client->withBasicAuth($esUser, $esPass);
            }
            if (filter_var($esSkipTls, FILTER_VALIDATE_BOOLEAN)) {
                $client = $client->withOptions(['verify' => false]);
            }

            $url = rtrim($esHostFull, '/') . '/_bulk';
            $resp = $client->withBody($payload, 'application/x-ndjson')->post($url);

            if ($resp->successful()) {
                $counted = (int) (count($lines) / 2);
                $this->info('Indexed chunk, messages: ' . $counted);
                $total += $counted;
            } else {
                $this->error('Bulk index failed: HTTP ' . $resp->status());
                $this->line($resp->body());
                // Throw to stop further chunks so user can diagnose
                throw new \RuntimeException('Bulk index failed: ' . $resp->status());
            }
        });

        $this->info("Done. Total indexed (approx): {$total}");
        return 0;
    }
}
