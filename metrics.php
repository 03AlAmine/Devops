<?php
require 'vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory; // ou utilisez APCu pour le stockage persistant.

// Configurez le registre des collecteurs
$registry = new CollectorRegistry(new InMemory());

// Créez une métrique pour les requêtes HTTP
$counter = $registry->getOrRegisterCounter('app', 'http_requests_total', 'Nombre total de requêtes', ['method']);
$counter->incBy(1, ['method' => $_SERVER['REQUEST_METHOD']]);

// Affichez les métriques au format texte
$renderer = new RenderTextFormat();
header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
echo $renderer->render($registry->getMetricFamilySamples());
?>
