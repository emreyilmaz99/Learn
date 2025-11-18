<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $svc = $app->make(App\Services\Elasticsearch\MessageSearchService::class);
    $r = new ReflectionClass($svc);
    $prop = $r->getProperty('searchUrl');
    $prop->setAccessible(true);
    echo "searchUrl = " . $prop->getValue($svc) . PHP_EOL;

    // Optionally try a real search (commented out because index may not exist)
    // $res = $svc->search('dfa', 1, 1);
    // var_export($res);
        echo "env ES_PORT = " . getenv('ES_PORT') . PHP_EOL;
        echo "config services.elasticsearch.port = ";
        var_export(config('services.elasticsearch.port'));
        echo PHP_EOL;
        echo "config elasticsearch.host = ";
        var_export(config('elasticsearch.host'));
        echo PHP_EOL;
        echo "config elasticsearch.default.hosts = ";
        var_export(config('elasticsearch.default.hosts'));
        echo PHP_EOL;
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
}
