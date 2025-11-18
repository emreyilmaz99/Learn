<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Http\Client\Curl\Client as CurlClient;
use Elastic\Transport\TransportBuilder;
use Elastic\Transport\NodePool\NodePoolInterface;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // bind a PSR-18 client implementation so Elastic client can be resolved
        $this->app->bind(Psr18ClientInterface::class, function ($app) {
            // Http\Client\Curl\Client will discover PSR-17 factories (nyholm/psr7 installed)
            return new CurlClient();
        });

        // Bind a default NodePool implementation so the container can instantiate Transport
        $this->app->singleton(NodePoolInterface::class, function ($app) {
            // Build hosts list from config; supports both array and simple host:port entries
            $hosts = [];

            $cfgHosts = config('elasticsearch.default.hosts') ?? [];
            foreach ($cfgHosts as $h) {
                if (is_array($h)) {
                    $host = $h['host'] ?? null;
                    $port = $h['port'] ?? null;
                    if ($host) {
                        $entry = $host . ($port ? ':' . $port : '');
                        $hosts[] = $entry;
                    }
                } elseif (is_string($h) && $h !== '') {
                    $hosts[] = $h;
                }
            }

            // Fallback to legacy single host value
            if (empty($hosts)) {
                $single = config('elasticsearch.host');
                if (!empty($single)) {
                    $hosts[] = $single;
                }
            }

            // Ensure hosts have scheme and include credentials for the transport builder; prefer https if forced
            $scheme = filter_var(env('ES_FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://';
            $esUser = config('elasticsearch.default.username') ?? env('ES_USER');
            $esPass = config('elasticsearch.default.password') ?? env('ES_PASSWORD');
            $hosts = array_map(function ($h) use ($scheme, $esUser, $esPass) {
                if (preg_match('#^https?://#i', $h)) {
                    return $h;
                }

                $auth = '';
                if (!empty($esUser)) {
                    $auth = rawurlencode($esUser) . ':' . rawurlencode($esPass) . '@';
                }
                return $scheme . $auth . $h;
            }, $hosts);

            $builder = TransportBuilder::create();
            // ensure the NodePool receives the hosts (TransportBuilder->build() applies hosts, but
            // when returning the NodePool directly we must set them ourselves)
            $nodePool = $builder->getNodePool();
            if (!empty($hosts)) {
                $nodePool = $nodePool->setHosts($hosts);
            }

            return $nodePool;
        });

        // Ensure a PSR logger exists for the Elasticsearch client
        $this->app->singleton(LoggerInterface::class, function () {
            return new NullLogger();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
