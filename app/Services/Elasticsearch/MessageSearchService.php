<?php

namespace App\Services\Elasticsearch;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class MessageSearchService
{
    protected \Illuminate\Http\Client\PendingRequest $client;
    protected string $searchUrl;

    public function __construct()
    {
        // Prefer explicit service-level host, then the generic elasticsearch.host, then env
        $esHost = config('services.elasticsearch.host') ?? config('elasticsearch.host') ?? env('ES_HOST');
        // Port preference: services config -> elasticsearch.default.hosts[0].port -> env
        $esPort = config('services.elasticsearch.port') ?? (config('elasticsearch.default.hosts.0.port') ?? null) ?? env('ES_PORT');
        $esUser = config('services.elasticsearch.username') ?? env('ES_USER');
        $esPassword = config('services.elasticsearch.password') ?? env('ES_PASSWORD');
        $esSkipTls = config('services.elasticsearch.skip_tls_verify') ?? filter_var(env('ES_SKIP_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN);

        if (!$esHost) {
            throw new \RuntimeException('Elasticsearch not configured');
        }

        // Build full host URL including scheme and port so requests target Elasticsearch (e.g. http://127.0.0.1:9200)
        $esHostFull = $esHost;
        if (!preg_match('#^https?://#i', $esHostFull)) {
            $useHttps = filter_var(env('ES_FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN);
            $esHostFull = ($useHttps ? 'https://' : 'http://') . $esHostFull;
        }

        // Ensure port is present (append if missing and a port is configured)
        if (!empty($esPort)) {
            $parts = parse_url($esHostFull);
            if (empty($parts['port'])) {
                $esHostFull = rtrim($esHostFull, '/') . ':' . $esPort;
            }
        }

        $this->searchUrl = rtrim($esHostFull, '/') . '/messages/_search';

        $client = Http::withHeaders(['Accept' => 'application/json'])->timeout(10);
        if ($esUser && $esPassword) {
            $client = $client->withBasicAuth($esUser, $esPassword);
        }
        if ($esSkipTls) {
            $client = $client->withOptions(['verify' => false]);
        }

        $this->client = $client;
    }

    /**
     * Execute a search against the `messages` index.
     * Returns array: ['data' => [...], 'meta' => [...]]
     */
    public function search(string $q, int $page = 1, int $perPage = 20): array
    {
        $from = max(0, ($page - 1) * $perPage);

        $body = $this->buildQuery($q, $from, $perPage);

        try {
            // Log outgoing request body for debugging
            if (config('app.debug')) {
                Log::debug('ES request', ['url' => $this->searchUrl, 'body' => $body]);
            }

            $resp = $this->client->post($this->searchUrl, $body);
            if (!$resp->successful()) {
                Log::warning('ES search non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new \RuntimeException('Search failed');
            }

            $data = $resp->json();
            // Log incoming response for debugging
            if (config('app.debug')) {
                Log::debug('ES response', ['status' => $resp->status(), 'body' => $data]);
            }
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? (int) ($data['hits']['total'] ?? count($hits));

            $items = $this->processHits($hits);

            // fetch users
            $userIds = [];
            foreach ($items as $it) {
                if (!empty($it['sender_id'])) $userIds[] = $it['sender_id'];
                if (!empty($it['receiver_id'])) $userIds[] = $it['receiver_id'];
            }
            $userIds = array_values(array_unique($userIds));
            $users = [];
            if (count($userIds)) {
                $users = User::whereIn('id', $userIds)->get()->keyBy('id');
            }

            $result = [];
            foreach ($items as $it) {
                $sender = null;
                $receiver = null;
                if (!empty($it['sender_id']) && isset($users[$it['sender_id']])) {
                    $u = $users[$it['sender_id']];
                    $sender = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
                } else {
                    $sender = ['id' => $it['sender_id'] ?? null, 'name' => $it['sender_email'] ?? null, 'email' => $it['sender_email'] ?? null];
                }
                if (!empty($it['receiver_id']) && isset($users[$it['receiver_id']])) {
                    $u = $users[$it['receiver_id']];
                    $receiver = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
                } else {
                    $receiver = ['id' => $it['receiver_id'] ?? null, 'name' => $it['receiver_email'] ?? null, 'email' => $it['receiver_email'] ?? null];
                }

                $result[] = [
                    'id' => $it['id'] ?? null,
                    'title' => $it['title'] ?? null,
                    'content' => $it['content'] ?? null,
                    'sender' => $sender,
                    'receiver' => $receiver,
                    'created_at' => $it['created_at'] ?? null,
                    'updated_at' => $it['updated_at'] ?? null,
                ];
            }

            $totalPages = (int) ceil($total / $perPage);

            return [
                'data' => $result,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'page' => $page,
                    'total_pages' => $totalPages,
                ]
            ];
        } catch (\Throwable $e) {
            Log::error('ES search failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function buildQuery(string $q, int $from, int $perPage): array
    {
        if ($q !== '') {
            // Aggressive approach: force wildcard around query and use query_string
            $wildcardQ = "*{$q}*";
            $fields = [
                'title.prefix^2',
                'content.prefix',
            ];

            return [
                'from' => $from,
                'size' => $perPage,
                'query' => [
                    'query_string' => [
                        'query' => $wildcardQ,
                        'fields' => $fields,
                    ]
                ],
                'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
                'highlight' => [
                    'fields' => [
                        'title' => (object)[],
                        'content' => (object)[],
                    ]
                ],
            ];
        }

        return [
            'from' => $from,
            'size' => $perPage,
            'query' => ['match_all' => (object)[]],
            'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
        ];
    }

    protected function processHits(array $hits): array
    {
        return array_map(fn($h) => $h['_source'] ?? [], $hits);
    }
}
