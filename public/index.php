<?php
/**
 * IMPORTANT : Conservez l'architecture MVC actuelle.
 * Ce fichier agit comme un routeur central et délègue aux contrôleurs ou aux fichiers de routes modulaires (dossier routes/).
 * N'ajoutez PAS de logique CRUD, de logique métier ou d'opérations de base de données ici.
 * Toute la logique doit être gérée dans les contrôleurs appropriés.
 *
 * Pour ajouter une nouvelle page :
 * - Pages centrales (auth, vues simples) : ajoutez un case dans le switch.
 * - Pages modulaires : créez/modifiez un fichier dans routes/ et ajoutez la délégation dans le default (if in_array).
 *
 * Rappel : Les fichiers routes/ ne contiennent que des instanciations de contrôleurs et des appels handle*Page() - pas de logique métier.
 */
require_once '../vendor/autoload.php';

// Whoops for pretty error pages in dev (only for non-API requests)
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);

// Skip Whoops for API requests to return JSON errors instead of HTML
if (!isset($_GET['page']) || !str_starts_with($_GET['page'], 'api_')) {
    $whoops->register();
}

// Monolog logger
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

$logger = new Logger('nash-tracker');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../storage/app.log', Logger::WARNING));
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushProcessor(new PsrLogMessageProcessor());

use App\Controller\MealController;
use App\Controller\FoodController;
use App\Controller\MedicamentController;
use App\Controller\ReportsController;
use App\Controller\SettingsController;
use App\Config\DIContainer;

// public/index.php
// point d'entrée de l'application

if (session_status() === PHP_SESSION_NONE) session_start();
// Toujours inclure la gestion de session en tout début
require_once '../src/Config/session.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Log application start
$logger->info('Application started', ['page' => $page]);

$container = DIContainer::getContainer();

// ============================================================
// URL magique pour vider le cache (dev only)
// Usage: ?cache=clear&token=nash2025dev&type=all
// Types: all, food_quality, dashboard, profile, nutrition, openfoodfacts
// ============================================================
if (isset($_GET['cache']) && $_GET['cache'] === 'clear') {
    $secretToken = 'nash2025dev'; // Change ce token !
    $providedToken = $_GET['token'] ?? '';
    
    if (!hash_equals($secretToken, $providedToken)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token invalide']);
        exit;
    }
    
    $type = $_GET['type'] ?? 'all';
    $cacheService = new \App\Service\CacheService();
    $cleared = 0;
    
    if ($type === 'all') {
        $cleared = $cacheService->clearAll();
        $message = "Tout le cache vidé ($cleared fichiers)";
    } else {
        $cleared = $cacheService->clearNamespace($type);
        $message = "Cache '$type' vidé ($cleared fichiers)";
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cleared' => $cleared,
        'type' => $type,
        'timestamp' => date('c')
    ]);
    exit;
}

// Ajouter les endpoints API publics ici (ex: vérifications côté client)
$publicPages = ['login', 'register', 'home', 'api_imc_data', 'api_recent_activities', 'api_check_unique'];
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
if (!isset($_SESSION['user']) && !in_array($page, $publicPages) && !$isAjaxRequest) {
    header('Location: ?page=login');
    exit;
}

// Router dispatch
$router = new \App\Config\Router($container);
$dispatched = $router->dispatch($page, $_SERVER['REQUEST_METHOD']);

// If not dispatched, 404
if (!$dispatched) {
    http_response_code(404);
    echo "Page non trouvée.";
}

// ...existing code...