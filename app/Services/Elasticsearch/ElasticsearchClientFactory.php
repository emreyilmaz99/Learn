<?php

namespace App\Services\Elasticsearch;

use Illuminate\Support\Facades\Http;

class ElasticsearchClientFactory
{
    protected string $baseUrl;
    protected ?string $user;
    protected ?string $pass;
    protected bool $skipTls;

    public function __construct()
    {
        // Simplified: read Elasticsearch connection options directly from environment
        $esHost = env('ES_HOST');
        $esPort = env('ES_PORT');
        $this->user = env('ES_USER');
        $this->pass = env('ES_PASSWORD');
        $this->skipTls = filter_var(env('ES_SKIP_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN);

        if (empty($esHost)) {
            throw new \RuntimeException('Elasticsearch host not configured');
        }

        $esHostFull = $esHost;
        if (!preg_match('#^https?://#i', $esHostFull)) {
            $useHttps = filter_var(env('ES_FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN);
            $esHostFull = ($useHttps ? 'https://' : 'http://') . $esHostFull;
        }

        if (!empty($esPort)) {
            $parts = parse_url($esHostFull);
            if (empty($parts['port'])) {
                $esHostFull = rtrim($esHostFull, '/') . ':' . $esPort;
            }
        }

        $this->baseUrl = rtrim($esHostFull, '/');
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Return a configured PendingRequest (Laravel HTTP client) for calling ES.
     */
    public function client(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::withHeaders(['Accept' => 'application/json'])->baseUrl($this->baseUrl)->timeout(10);

        if (!empty($this->user) && !empty($this->pass)) {
            $client = $client->withBasicAuth($this->user, $this->pass);
        }

        if ($this->skipTls) {
            $client = $client->withOptions(['verify' => false]);
        }

        return $client;
    }
}
