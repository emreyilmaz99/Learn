<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Services\Elasticsearch\ElasticsearchClientFactory;

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

        // Use the centralized Elasticsearch client factory to build requests.
        try {
            $factory = new ElasticsearchClientFactory();
            $client = $factory->client();
            $baseUrl = rtrim($factory->baseUrl(), '/');
        } catch (\Throwable $e) {
            Log::debug('IndexMessageJob skipped because Elasticsearch client could not be created', ['id' => $this->messageId, 'error' => $e->getMessage()]);
            return;
        }

        if (empty($baseUrl)) {
            Log::debug('IndexMessageJob skipped because ES base url is empty', ['id' => $this->messageId]);
            return;
        }

        $indexUrlBase = $baseUrl . '/messages/_doc/' . $this->messageId;

        
        if (!$message) {
            try {
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
                'sender_email' => $message->sender?->email ?? null,
                'receiver_email' => $message->receiver?->email ?? null,
                'sender_name' => $message->sender?->name ?? null,
                'receiver_name' => $message->receiver?->name ?? null,
                'created_at' => $message->created_at?->toIso8601String(),
                'updated_at' => $message->updated_at?->toIso8601String(),
            ];

           
            $response = $client->put($indexUrl, $payload);
            if (!$response->successful()) {
                Log::warning('IndexMessageJob index returned non-success', ['id' => $this->messageId, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('IndexMessageJob failed', ['id' => $this->messageId, 'error' => $e->getMessage()]);
            
            throw $e;
        }

                // Clear only the cache entry related to this message and remove it from the index set.
                $cacheKey = "message:{$this->messageId}";
                try {
                        Cache::store('redis')->forget($cacheKey);
                        Redis::connection('cache')->srem('message_index', $cacheKey);
                } catch (\Throwable $e) {
                        Log::debug('IndexMessageJob: cache cleanup failed', ['id' => $this->messageId, 'error' => $e->getMessage()]);
                }
    }
}
