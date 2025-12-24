<?php

namespace App\Config;

/**
 * Simple Router for query-based routing (?page=XXX)
 * Supports GET/POST for forms compatibility.
 * Keeps existing ?page=XXX links intact.
 */
class Router
{
    private array $routes = [];
    private \Psr\Container\ContainerInterface $container;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;
        $this->defineRoutes();
    }

    private function defineRoutes(): void
    {
        // Routes migrated from switch
        $this->routes['home'] = [
            'GET' => $this->view('home'),
        ];
        $this->routes['login'] = [
            'GET'  => $this->view('login'),
            'POST' => $this->controller(\App\Controller\UserController::class, 'handleLoginPage'),
        ];
        $this->routes['register'] = [
            'GET'  => $this->view('register'),
            'POST' => $this->controller(\App\Controller\UserController::class, 'handleRegisterPage'),
        ];
        $this->routes['food'] = [
            'GET'  => $this->controller(\App\Controller\FoodController::class, 'handleFoodPage'),
            'POST' => $this->controller(\App\Controller\FoodController::class, 'handleFoodPage'),
        ];
        $this->routes['catalog'] = [
            'GET'  => $this->controller(\App\Controller\FoodController::class, 'handleCatalogPage'),
            'POST' => $this->controller(\App\Controller\FoodController::class, 'handleCatalogPage'),
        ];
        $this->routes['meals'] = [
            'GET'  => $this->controller(\App\Controller\MealController::class, 'index'),
            'POST' => $this->controller(\App\Controller\MealController::class, 'index'),
        ];
        $this->routes['meal'] = [
            'GET'  => $this->controller(\App\Controller\MealController::class, 'index'),
            'POST' => $this->controller(\App\Controller\MealController::class, 'index'),
        ];
        $this->routes['save_objectifs'] = [
            'POST' => $this->controller(\App\Controller\ImcController::class, 'save'),
        ];
        $this->routes['profile'] = [
            'GET' => $this->controller(\App\Controller\ProfileController::class, 'showProfile'),
        ];
        $this->routes['imc'] = [
            'GET' => $this->controller(\App\Controller\ImcController::class, 'showImc'),
        ];
        $this->routes['settings'] = [
            'GET' => $this->controller(\App\Controller\SettingsController::class, 'showSettings'),
        ];
        $this->routes['logout'] = [
            'GET'  => $this->controller(\App\Controller\UserController::class, 'handleLogout'),
            'POST' => $this->controller(\App\Controller\UserController::class, 'handleLogout'),
        ];
        $this->routes['api_imc_data'] = [
            'GET' => $this->controller(\App\Controller\ImcController::class, 'handleApiImcDataInstance'),
        ];
        $this->routes['api_check_unique'] = [
            'GET' => $this->controller(\App\Controller\UserController::class, 'handleApiCheckUnique'),
        ];
        $this->routes['api_medicaments_jour'] = [
            'GET' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiGetPrisesJour'),
        ];
        $this->routes['api_medicament'] = [
            'GET' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiGetMedicament'),
        ];
        $this->routes['api_medicaments'] = [
            'GET' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiGetMedicaments'),
        ];
        $this->routes['api_marquer_pris'] = [
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiMarquerPris'),
        ];
        $this->routes['api_annuler_pris'] = [
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiAnnulerPris'),
        ];
        $this->routes['api_create_medicament'] = [
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiCreateMedicament'),
        ];
        $this->routes['api_update_medicament'] = [
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiUpdateMedicament'),
        ];
        $this->routes['api_delete_medicament'] = [
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiDeleteMedicament'),
        ];
        $this->routes['api_historique_medicaments'] = [
            'GET' => $this->controller(\App\Controller\MedicamentController::class, 'handleApiHistorique'),
        ];
        $this->routes['settings/update-email'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'updateEmail'),
        ];
        $this->routes['settings/update-pseudo'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'updatePseudo'),
        ];
        $this->routes['settings/update-password'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'updatePassword'),
        ];
        $this->routes['settings/delete-account'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'deleteAccount'),
        ];
        $this->routes['settings/update-user-config'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'updateUserConfig'),
        ];
        $this->routes['settings/reset-user-config'] = [
            'POST' => $this->controller(\App\Controller\SettingsController::class, 'resetUserConfig'),
        ];
        $this->routes['reports'] = [
            'GET'  => $this->controller(\App\Controller\ReportsController::class, 'handleReportsPage'),
            'POST' => $this->controller(\App\Controller\ReportsController::class, 'handleReportsPage'),
        ];
        $this->routes['report'] = [
            'GET'  => $this->controller(\App\Controller\ReportsController::class, 'handleReportsPage'),
            'POST' => $this->controller(\App\Controller\ReportsController::class, 'handleReportsPage'),
        ];
        $this->routes['export'] = [
            'GET'  => $this->controller(\App\Controller\ReportsController::class, 'handleExport'),
            'POST' => $this->controller(\App\Controller\ReportsController::class, 'handleExport'),
        ];
        $this->routes['medicaments'] = [
            'GET'  => $this->controller(\App\Controller\MedicamentController::class, 'handleMedicamentsPage'),
            'POST' => $this->controller(\App\Controller\MedicamentController::class, 'handleMedicamentsPage'),
        ];
        $this->routes['activity'] = [
            'GET' => $this->controller(\App\Controller\ActivityController::class, 'showActivity'),
        ];
        $this->routes['activity/add'] = [
            'POST' => $this->controller(\App\Controller\ActivityController::class, 'ajouterActivite'),
        ];
        $this->routes['activity/delete'] = [
            'POST' => $this->controller(\App\Controller\ActivityController::class, 'supprimerActivite'),
        ];
        $this->routes['activity/today'] = [
            'GET' => $this->controller(\App\Controller\ActivityController::class, 'getActivitesAujourdhui'),
        ];
        $this->routes['activity/history'] = [
            'GET' => $this->controller(\App\Controller\ActivityController::class, 'getHistoriqueActivites'),
        ];
        $this->routes['walktrack'] = [
            'GET' => $this->controller(\App\Controller\WalkTrackController::class, 'showWalkTrack'),
        ];
        $this->routes['walktrack/add'] = [
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'ajouterMarche'),
        ];
        $this->routes['walktrack/edit'] = [
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'modifierMarche'),
        ];
        $this->routes['walktrack/delete'] = [
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'supprimerMarche'),
        ];
        $this->routes['walktrack/today'] = [
            'GET' => $this->controller(\App\Controller\WalkTrackController::class, 'getMarchesAujourdhui'),
        ];
        $this->routes['walktrack/history'] = [
            'GET' => $this->controller(\App\Controller\WalkTrackController::class, 'getHistorique'),
        ];
        $this->routes['walktrack/objectives'] = [
            'GET'  => $this->controller(\App\Controller\WalkTrackController::class, 'getObjectifs'),
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'updateObjectifs'),
        ];
        $this->routes['walktrack/routes'] = [
            'GET' => $this->controller(\App\Controller\WalkTrackController::class, 'getParcoursFavoris'),
        ];
        $this->routes['walktrack/routes/save'] = [
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'sauvegarderParcours'),
        ];
        $this->routes['walktrack/routes/delete'] = [
            'POST' => $this->controller(\App\Controller\WalkTrackController::class, 'supprimerParcours'),
        ];
        $this->routes['walktrack/data'] = [
            'GET' => $this->controller(\App\Controller\WalkTrackController::class, 'getPageData'),
        ];
    }

    public function dispatch(string $page, string $method): bool
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$page][$method]))
        {
            // Check for prefix matches
            foreach ($this->routes as $route => $methods)
            {
                if (str_starts_with($page, $route) && isset($methods[$method]))
                {
                    $handler = $methods[$method];
                    $handler($page);

                    return true;
                }
            }

            return false;
        }

        $handler = $this->routes[$page][$method];
        $handler($page);

        return true;
    }

    private function view(string $view): callable
    {
        return function (string $page) use ($view)
        {
            include __DIR__ . "/../View/{$view}.php";
        };
    }

    private function controller(string $class, string $method): callable
    {
        return function (string $page) use ($class, $method)
        {
            if (!method_exists($class, $method))
            {
                throw new \RuntimeException("Controller method {$class}::{$method} not found");
            }

            $controller = $this->container->get($class);
            $result = $controller->$method();

            // If the method returns an array, assume it's API data and JSON encode it
            if (is_array($result))
            {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit;
            }
        };
    }
}
