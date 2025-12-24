<?php

/**
 * Vue home.php - Page d'accueil / Dashboard
 * - Affichage conditionnel guest/logged
 * - Score santé global, stats clés, timeline.
 */

declare(strict_types=1);

// Inclusion des helpers de vue
require_once __DIR__ . '/../Helper/view_helpers.php';

// Inclusion des dépendances
$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

$title = 'Accueil - Suivi Nash';

/** @var array|null $user */
$user = $_SESSION['user'] ?? null;

// Préparation des données via le controller
require_once __DIR__ . '/../Controller/HomeController.php';
require_once __DIR__ . '/../Config/DIContainer.php';

use App\Controller\HomeController;

$container = \App\Config\DIContainer::getContainer();
$homeController = $container->get(HomeController::class);
$viewData = $homeController->prepareHomeViewData($user);

ob_start();

// Affichage conditionnel basé sur les données préparées
if (!$viewData['isLoggedIn'])
{
    include __DIR__ . '/components/guest_home.php';
} else
{
    include __DIR__ . '/components/user_dashboard_v2.php';
}

// Utilise la constante JS_PATH et la fonction includeJs() définies dans layout.php
// Exemple d'utilisation multiple : $pageJs = ['example.js', 'another.js'];

// CSS spécifique à la page d'accueil (dashboard)
// Utilise la constante CSS_PATH et la fonction includeCss() définies dans layout.php
$pageCss = ['dashboard.css'];
?>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
