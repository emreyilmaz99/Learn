<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageSearchController extends Controller
{
    /**
     * Search messages using Elasticsearch. Expects query param `q`.
     * Returns JSON in the same shape the front-end expects: { data: [ ... ] }
     */
    public function search(Request $request)
    {
    $q = $request->query('q', '');
    $page = max(1, (int) $request->query('page', 1));
    $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $esHost = config('services.elasticsearch.host') ?? env('ES_HOST');
        $esPort = env('ES_PORT');
        $esUser = config('services.elasticsearch.username') ?? env('ES_USER');
        $esPassword = config('services.elasticsearch.password') ?? env('ES_PASSWORD');
        $esSkipTls = config('services.elasticsearch.skip_tls_verify') ?? filter_var(env('ES_SKIP_TLS_VERIFY', false), FILTER_VALIDATE_BOOLEAN);

        // Build full ES base url (ensure scheme and port if provided)
        $esHostFull = $esHost;
        if ($esHostFull && !preg_match('#^https?://#i', $esHostFull)) {
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
            return response()->json(['data' => [], 'message' => 'Elasticsearch not configured'], 500);
        }

        $body = [];
        $from = ($page - 1) * $perPage;

        if ($q !== '') {
            $body = [
                'from' => $from,
                'size' => $perPage,
                'query' => [
                    'match' => [
                        'title' => [
                            'query' => $q,
                            'operator' => 'and'
                        ]
                    ]
                ]
            ];
        } else {
            // If no query provided, return latest messages from ES with pagination
            $body = [
                'from' => $from,
                'size' => $perPage,
                'query' => [ 'match_all' => (object)[] ],
                'sort' => [ [ 'created_at' => ['order' => 'desc'] ] ],
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
                return response()->json(['data' => [], 'error' => 'Search failed'], 500);
            }

            $data = $resp->json();
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? (int) ($data['hits']['total'] ?? count($hits));

            // map sources
            $items = array_map(function ($h) {
                return $h['_source'] ?? [];
            }, $hits);

            // fetch user names for sender/receiver (small DB ok)
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

            return response()->json([
                'data' => $result,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'page' => $page,
                    'total_pages' => $totalPages,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('ES search failed', ['error' => $e->getMessage()]);
            return response()->json(['data' => [], 'error' => 'Search error'], 500);
        }
    }
}
