<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Direct config
$cfgHost = config('elasticsearch.host');
$cfgDefault = config('elasticsearch.default.hosts');
$matchish = \Matchish\ScoutElasticSearch\ElasticSearch\Config\Config::hosts();

echo "config('elasticsearch.host') = "; var_export($cfgHost); echo PHP_EOL;
echo "config('elasticsearch.default.hosts') = "; var_export($cfgDefault); echo PHP_EOL;
echo "Matchish Config::hosts() = "; var_export($matchish); echo PHP_EOL;

$tb = \Elastic\Transport\TransportBuilder::create()->setHosts($matchish);
echo "TransportBuilder->getHosts() = "; var_export($tb->getHosts()); echo PHP_EOL;

$np = $tb->getNodePool();
// Explicitly set hosts on the node pool (same action TransportBuilder->build() performs)
$np->setHosts($matchish);
// Try to reflect into SimpleNodePool nodes count
$ref = new ReflectionClass($np);
$prop = $ref->getProperty('nodes');
$prop->setAccessible(true);
$nodes = $prop->getValue($np);
if (is_array($nodes)) {
    echo "NodePool nodes count = " . count($nodes) . PHP_EOL;
    var_export($nodes);
    echo PHP_EOL;
} else {
    echo "NodePool nodes not an array" . PHP_EOL;
}
