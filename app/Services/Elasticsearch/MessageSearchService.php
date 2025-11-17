<?php

namespace App\Services\Elasticsearch;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageSearchService
{
    /**
     * Execute a search against the `messages` index.
     * Returns array: ['data' => [...], 'meta' => [...]]
     *
     * @param string $q
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws \Throwable
     */
    public function search(string $q, int $page = 1, int $perPage = 20): array
    {
        $from = max(0, ($page - 1) * $perPage);

        $esHost = config('services.elasticsearch.host') ?? env('ES_HOST');
        $esPort = env('ES_PORT');
        $esUser = config('services.elasticsearch.username') ?? env('ES_USER');
        $esPassword = config('services.elasticsearch.password') ?? env('ES_PASSWORD');
        $esSkipTls = config('services.elasticsearch.skip_tls_verify') ?? filter_var(env('ES_SKIP_TLS_VERIFY', false), FILTER_VALIDATE_BOOLEAN);

        if (!$esHost) {
            throw new \RuntimeException('Elasticsearch not configured');
        }

        $esHostFull = $esHost;
        if (!preg_match('#^https?://#i', $esHostFull)) {
            $scheme = env('ES_FORCE_HTTPS', 'false');
            $esHostFull = (filter_var($scheme, FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://') . $esHostFull;
        }
        if (!empty($esPort)) {
            $parts = parse_url($esHostFull);
            if (empty($parts['port'])) {
                $esHostFull = rtrim($esHostFull, '/') . ':' . $esPort;
            }
        }

        // build query using multi_match for title+content and highlight
        if ($q !== '') {
            $body = [
                'from' => $from,
                'size' => $perPage,
                'query' => [
                    'multi_match' => [
                        'query' => $q,
                        'fields' => ['title^2', 'content'],
                        'operator' => 'and'
                    ]
                ],
                'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
                'highlight' => ['fields' => ['title' => new \stdClass(), 'content' => new \stdClass()]],
            ];
        } else {
            $body = [
                'from' => $from,
                'size' => $perPage,
                'query' => ['match_all' => (object)[]],
                'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
            ];
        }

        try {
            $client = Http::withHeaders(['Accept' => 'application/json'])->timeout(10);
            if ($esUser && $esPassword) {
                $client = $client->withBasicAuth($esUser, $esPassword);
            }
            if ($esSkipTls) {
                $client = $client->withOptions(['verify' => false]);
            }

            $resp = $client->post(rtrim($esHostFull, '/') . '/messages/_search', $body);
            if (!$resp->successful()) {
                Log::warning('ES search non-success', ['status' => $resp->status(), 'body' => $resp->body()]);
                throw new \RuntimeException('Search failed');
            }

            $data = $resp->json();
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? (int) ($data['hits']['total'] ?? count($hits));

            $items = array_map(fn($h) => $h['_source'] ?? [], $hits);

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
}
