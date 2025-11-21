<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IndexMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
        $this->onQueue('indexing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = Message::find($this->messageId);

        $esHost = config('services.elasticsearch.host') ?? env('ES_HOST');
        $esPort = env('ES_PORT');
        $esUser = config('services.elasticsearch.username') ?? env('ES_USER');
        $esPassword = config('services.elasticsearch.password') ?? env('ES_PASSWORD');
        $esSkipTls = config('services.elasticsearch.skip_tls_verify') ?? filter_var(env('ES_SKIP_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN);

        // Build full ES base url (ensure scheme and port if provided)
        $esHostFull = $esHost;
        if ($esHostFull && !preg_match('#^https?://#i', $esHostFull)) {
            // Default to HTTP for local/dev unless ES_FORCE_HTTPS is set to true
            $scheme = env('ES_FORCE_HTTPS', 'false');
            $esHostFull = (filter_var($scheme, FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://') . $esHostFull;
        }
        if (!empty($esPort)) {
            $parts = parse_url($esHostFull);
            if (empty($parts['port'])) {
                $esHostFull = rtrim($esHostFull, '/') . ':' . $esPort;
            }
        }

        if (!$esHost) {
            // ES not configured; log and return
            Log::debug('IndexMessageJob skipped because ES_HOST not set', ['id' => $this->messageId]);
            return;
        }

        // Ensure scheme: if host has no scheme, default to https because modern ES images enable security
        if (!Str::startsWith($esHost, ['http://', 'https://'])) {
            // Default to HTTP for local/dev unless ES_FORCE_HTTPS is set to true
            $scheme = env('ES_FORCE_HTTPS', 'false');
            $esHost = (filter_var($scheme, FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://') . $esHost;
        }

            $indexUrlBase = rtrim($esHostFull, '/') . '/messages/_doc/' . $this->messageId;

        // If message doesn't exist in DB, delete from ES index
        if (!$message) {
            try {
                $client = Http::withHeaders(['Accept' => 'application/json'])->timeout(10)->retry(2, 100);
                if ($esUser && $esPassword) {
                    $client = $client->withBasicAuth($esUser, $esPassword);
                }
                if (filter_var($esSkipTls, FILTER_VALIDATE_BOOLEAN)) {
                    // Dev-only: skip TLS verification for self-signed certs
                    $client = $client->withOptions(['verify' => false]);
                }

                $response = $client->delete($indexUrlBase);
                if (!$response->successful()) {
                    Log::warning('IndexMessageJob delete returned non-success', ['id' => $this->messageId, 'status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::error('IndexMessageJob delete failed', ['id' => $this->messageId, 'error' => $e->getMessage()]);
                throw $e;
            }
            return;
        }

        try {
            $indexUrl = $indexUrlBase;
            $payload = [
                'id' => $message->id,
                'title' => $message->title,
                'content' => $message->content,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                // include sender/receiver emails so we can search by email
                'sender_email' => $message->sender?->email ?? null,
                'receiver_email' => $message->receiver?->email ?? null,
                // include sender/receiver names for more human search (and display)
                'sender_name' => $message->sender?->name ?? null,
                'receiver_name' => $message->receiver?->name ?? null,
                'created_at' => $message->created_at?->toIso8601String(),
                'updated_at' => $message->updated_at?->toIso8601String(),
            ];

            // Use Laravel HTTP client (Guzzle) with optional BasicAuth
            $client = Http::withHeaders(['Accept' => 'application/json'])->timeout(10)->retry(2, 100);
            if ($esUser && $esPassword) {
                $client = $client->withBasicAuth($esUser, $esPassword);
            }
            if (filter_var($esSkipTls, FILTER_VALIDATE_BOOLEAN)) {
                $client = $client->withOptions(['verify' => false]);
            }

            $response = $client->put($indexUrl, $payload);
            if (!$response->successful()) {
                Log::warning('IndexMessageJob index returned non-success', ['id' => $this->messageId, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('IndexMessageJob failed', ['id' => $this->messageId, 'error' => $e->getMessage()]);
            // Job will be retried per queue settings
            throw $e;
        }

        // Mesaj indekslendikten sonra arama cache'ini temizle (tüm cache'i flush eder)
        Cache::store('redis')->getStore()->flush();
    }
}
