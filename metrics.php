<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charge les dépendances via Composer
require 'vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\APC;

// Créez ou récupérez un registre des collecteurs
$adapter = new APC();
$registry = new CollectorRegistry($adapter);

// Récupérez la méthode HTTP
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'unknown';

// Vérifiez si la requête est pour /metrics. Si oui, ne pas incrémenter le compteur.
if ($_SERVER['REQUEST_URI'] !== '/metrics') {
    // Créez un compteur global pour toutes les requêtes
    $counter = $registry->getOrRegisterCounter(
        'app',
        'http_requests_total',
        'Nombre total de requêtes HTTP',
        ['method']
    );
    $counter->incBy(1, ['method' => $method]);
}

// Temps d'exécution de la requête
$executionGauge = $registry->getOrRegisterGauge(
    'app',
    'execution_time',
    'Temps d\'exécution de la requête en secondes',
    []
);
$start = microtime(true);
usleep(100); // Exemple de logique
$executionGauge->set(microtime(true) - $start);

// Mémoire utilisée
$memoryGauge = $registry->getOrRegisterGauge(
    'app',
    'memory_usage',
    'Mémoire utilisée en octets',
    []
);
$memoryGauge->set(memory_get_usage());

// Ajouter une métrique supplémentaire pour le temps d'exécution avec un label
$executionGaugeWithLabel = $registry->getOrRegisterGauge(
    'app',
    'execution_time_with_label',
    'Temps d\'exécution par type de méthode HTTP',
    ['method']
);
$executionGaugeWithLabel->set(microtime(true) - $start, ['method' => $method]);

// Afficher les métriques uniquement si l'URL demandée est `/metrics`
if ($_SERVER['REQUEST_URI'] === '/metrics') {
    header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
    echo (new RenderTextFormat())->render($registry->getMetricFamilySamples());
    exit;
}