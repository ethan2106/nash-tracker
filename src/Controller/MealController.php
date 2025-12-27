<?php

namespace App\Controller;

use App\Helper\ResponseHelper;
use App\Model\MealModel;
use App\Service\ActivityService;
use App\Service\GamificationService;
use App\Service\MealManager;

class MealController extends BaseApiController
{
    private MealModel $mealModel;

    private MealManager $mealManager;

    private ?ActivityService $activityService;

    private GamificationService $gamificationService;

    public function __construct(
        MealModel $mealModel,
        MealManager $mealManager,
        ?ActivityService $activityService,
        GamificationService $gamificationService
    ) {
        $this->mealModel = $mealModel;
        $this->mealManager = $mealManager;
        $this->activityService = $activityService;
        $this->gamificationService = $gamificationService;
    }

    /**
     * Gérer la page meals : affichage des repas du jour.
     */
    public function index(): void
    {
        // Traiter les actions POST avant d'afficher la page
        self::handleRemoveFoodFromMeal($this->mealManager);
        self::handleDeleteMeal($this->mealManager);

        // Récupérer et valider la date
        $selectedDate = $this->getValidatedDate();
        $isToday = $selectedDate === date('Y-m-d');

        $mealsByType = $this->mealManager->getMealsByDate($selectedDate);
        $csrf_token = $_SESSION['csrf_token'] ?? '';

        // Détecter les requêtes AJAX et retourner du JSON
        if ($this->isAjaxRequest())
        {
            $this->handleAjaxMealsRequest($mealsByType, $selectedDate, $isToday, $csrf_token);

            return;
        }

        // Pour les requêtes normales, afficher la page HTML
        $this->handleHtmlMealsRequest($mealsByType, $selectedDate, $isToday, $csrf_token);
    }

    /**
     * Récupérer et valider la date depuis les paramètres GET.
     */
    private function getValidatedDate(): string
    {
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        // Valider la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate))
        {
            $selectedDate = date('Y-m-d');
        }

        return $selectedDate;
    }

    /**
     * Gérer les requêtes AJAX pour les repas.
     */
    private function handleAjaxMealsRequest(array $mealsByType, string $selectedDate, bool $isToday, string $csrf_token): void
    {
        // Vérifier que l'utilisateur est connecté pour les requêtes AJAX
        if (!isset($_SESSION['user']))
        {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
            exit;
        }

        $user_id = $_SESSION['user']['id'] ?? null;
        $totals = $this->computeTotalsWithActivities($mealsByType, $user_id, $selectedDate);
        $sections = $this->getMealSectionsConfig();

        // Inclure les dépendances nécessaires pour générer le HTML
        require_once __DIR__ . '/../View/components/meal_section.php';
        require_once __DIR__ . '/../Helper/view_helpers.php';

        // Générer le HTML des sections repas pour AJAX
        ob_start();
        foreach ($sections as $type => $config)
        {
            /* @phpstan-ignore-next-line */
            renderMealSection($mealsByType[$type] ?? [], $type, $config, $csrf_token, $isToday);
        }
        $mealsHtml = ob_get_clean();

        // Retourner la réponse JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'mealsByType' => $mealsByType,
            'selectedDate' => $selectedDate,
            'isToday' => $isToday,
            'totals' => $totals,
            'sections' => $sections,
            'mealsHtml' => $mealsHtml,
            'csrf_token' => $csrf_token,
        ]);
        exit;
    }

    /**
     * Gérer les requêtes HTML pour les repas.
     */
    private function handleHtmlMealsRequest(array $mealsByType, string $selectedDate, bool $isToday, string $csrf_token): void
    {
        // Calculer les données supplémentaires pour la vue
        $user_id = $_SESSION['user']['id'] ?? null;
        $totals = $this->computeTotalsWithActivities($mealsByType, $user_id, $selectedDate);
        $objectifs = $this->getUserObjectives($user_id);
        $sections = $this->getMealSectionsConfig();

        // Calculer la gamification nutrition
        $gamificationNutrition = $this->calculateNutritionGamification($mealsByType, $totals, $objectifs);

        $GLOBALS['mealsByType'] = $mealsByType;
        $GLOBALS['selectedDate'] = $selectedDate;
        $GLOBALS['isToday'] = $isToday;
        $GLOBALS['totals'] = $totals;
        $GLOBALS['objectifs'] = $objectifs;
        $GLOBALS['sections'] = $sections;
        $GLOBALS['csrf_token'] = $csrf_token;
        $GLOBALS['streakNutrition'] = $gamificationNutrition['streak'];
        $GLOBALS['badgesNutrition'] = $gamificationNutrition['badges'];
        require_once __DIR__ . '/../View/meals.php';
    }

    /**
     * Calculer la gamification nutrition (streak et badges).
     */
    private function calculateNutritionGamification(array $mealsByType, array $totals, ?array $objectifs): array
    {
        // Streak nutrition (simplifié - à améliorer avec historique réel)
        $hasLoggedMeals = !empty($mealsByType);
        $streakNutrition = $hasLoggedMeals ? 1 : 0;

        // Badges nutritionnels basés sur la qualité des repas
        $badgesNutrition = [
            'earned' => [],
            'toEarn' => [],
        ];

        // Badge "Équilibre parfait" si calories entre 80% et 100% de l'objectif
        $cals = $totals['calories'] ?? 0;
        $objCals = $objectifs['calories_perte'] ?? 2000;
        if ($cals > 0 && $cals <= $objCals && $cals >= ($objCals * 0.8))
        {
            $badgesNutrition['earned'][] = [
                'id' => 'equilibre_parfait',
                'label' => 'Équilibre parfait',
                'icon' => '⚖️',
                'description' => 'Apport calorique optimal aujourd\'hui',
            ];
        }

        return [
            'streak' => $streakNutrition,
            'badges' => $badgesNutrition,
        ];
    }

    /**
     * Calculer les totaux journaliers incluant les activités sportives.
     */
    private function computeTotalsWithActivities(array $mealsByType, ?int $user_id, string $selectedDate): array
    {
        $totals = $this->calculateDailyTotals($mealsByType);

        // Ajouter les calories dépensées lors d'activités physiques
        if ($user_id && $this->activityService !== null)
        {
            $rawSportCalories = $this->activityService->getCaloriesBurnedByDate($user_id, $selectedDate);
            $effectiveSportCalories = max(0, $rawSportCalories - 500);
            $totals['calories'] += $effectiveSportCalories;
            $totals['sport_calories'] = $effectiveSportCalories;
            $totals['raw_sport_calories'] = $rawSportCalories;
        } else
        {
            // Fallback si ActivityService n'est pas disponible
            $totals['sport_calories'] = 0;
            $totals['raw_sport_calories'] = 0;
        }

        return $totals;
    }

    /**
     * Récupérer les objectifs de l'utilisateur.
     */
    private function getUserObjectives(?int $user_id): ?array
    {
        if (!$user_id)
        {
            return null;
        }

        require_once __DIR__ . '/../Model/ObjectifsModel.php';

        return \App\Model\ObjectifsModel::getByUser($user_id);
    }

    /**
     * Configuration des sections repas.
     */
    private function getMealSectionsConfig(): array
    {
        return [
            'petit-dejeuner' => [
                'icon' => 'fa-sun',
                'color' => 'text-yellow-500',
                'bg' => 'bg-yellow-50',
                'border' => 'border-yellow-200',
                'btn' => 'bg-yellow-500',
                'label' => 'Petit-déjeuner',
                'id' => 'meals-petit-dejeuner',
                'empty_icon' => 'fa-coffee',
                'empty_text' => 'Aucun petit-déjeuner',
            ],
            'dejeuner' => [
                'icon' => 'fa-utensils',
                'color' => 'text-orange-500',
                'bg' => 'bg-orange-50',
                'border' => 'border-orange-200',
                'btn' => 'bg-orange-500',
                'label' => 'Déjeuner',
                'id' => 'meals-dejeuner',
                'empty_icon' => 'fa-utensils',
                'empty_text' => 'Aucun déjeuner',
            ],
            'gouter' => [
                'icon' => 'fa-cookie-bite',
                'color' => 'text-pink-500',
                'bg' => 'bg-pink-50',
                'border' => 'border-pink-200',
                'btn' => 'bg-pink-500',
                'label' => 'Goûter',
                'id' => 'meals-gouter',
                'empty_icon' => 'fa-cookie-bite',
                'empty_text' => 'Aucun goûter',
            ],
            'diner' => [
                'icon' => 'fa-moon',
                'color' => 'text-indigo-500',
                'bg' => 'bg-indigo-50',
                'border' => 'border-indigo-200',
                'btn' => 'bg-indigo-500',
                'label' => 'Dîner',
                'id' => 'meals-diner',
                'empty_icon' => 'fa-moon',
                'empty_text' => 'Aucun dîner',
            ],
            'en-cas' => [
                'icon' => 'fa-apple-whole',
                'color' => 'text-green-500',
                'bg' => 'bg-green-50',
                'border' => 'border-green-200',
                'btn' => 'bg-green-500',
                'label' => 'En-cas',
                'id' => 'meals-en-cas',
                'empty_icon' => 'fa-apple-whole',
                'empty_text' => 'Aucun en-cas',
            ],
        ];
    }

    /**
     * Préparer les données pour la vue meals (séparation MVC).
     */
    public function showMeals(): array
    {
        // Récupérer et valider la date
        $selectedDate = $this->getValidatedDate();
        $isToday = $selectedDate === date('Y-m-d');

        $mealsByType = $this->mealManager->getMealsByDate($selectedDate);

        // Récupérer l'utilisateur
        $user_id = $_SESSION['user']['id'] ?? null;

        // Calculer les totaux journaliers incluant les activités sportives
        $totals = $this->computeTotalsWithActivities($mealsByType, $user_id, $selectedDate);

        // Récupérer les objectifs de l'utilisateur
        $objectifs = $this->getUserObjectives($user_id);

        // Configuration des sections repas
        $sections = $this->getMealSectionsConfig();

        return [
            'mealsByType' => $mealsByType,
            'selectedDate' => $selectedDate,
            'isToday' => $isToday,
            'totals' => $totals,
            'objectifs' => $objectifs,
            'sections' => $sections,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
        ];
    }

    /**
     * Calculer les totaux nutritionnels journaliers.
     */
    private function calculateDailyTotals(array $mealsByType): array
    {
        $totals = [
            'calories' => 0,
            'proteines' => 0,
            'glucides' => 0,
            'lipides' => 0,
            'sucres' => 0,
            'fibres' => 0,
            'graisses_sat' => 0,
        ];

        foreach ($mealsByType as $meals)
        {
            foreach ($meals as $meal)
            {
                $totals['calories'] += $meal['calories_total'] ?? 0;
                $totals['proteines'] += $meal['proteines_total'] ?? 0;
                $totals['glucides'] += $meal['glucides_total'] ?? 0;
                $totals['lipides'] += $meal['lipides_total'] ?? 0;
                $totals['sucres'] += $meal['sucres_total'] ?? 0;
                $totals['fibres'] += $meal['fibres_total'] ?? 0;
                $totals['graisses_sat'] += $meal['graisses_sat_total'] ?? 0;
            }
        }

        return $totals;
    }

    /**
     * Traiter l'ajout d'un aliment à un repas depuis le catalogue.
     */
    public static function handleAddFoodFromCatalog(MealManager $mealManager): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['action']) || $_GET['action'] !== 'ajouter-aliment')
        {
            return;
        }

        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf))
        {
            ResponseHelper::addErrorMessage('Session invalide (CSRF).');
            ResponseHelper::redirectToMeals();
        }

        $foodId = (int)($_POST['aliment_id'] ?? $_POST['food_id'] ?? 0);

        // Accepter plusieurs clés selon la source (formulaire classique ou AJAX)
        $rawMealType = $_POST['repas_type'] ?? $_POST['meal_type'] ?? 'repas';
        $rawQuantity = $_POST['quantite_g'] ?? $_POST['quantity'] ?? 100;

        // Normaliser quantité
        $quantity = (int)$rawQuantity;
        if ($quantity <= 0)
        {
            $quantity = 100;
        }

        // Normaliser type (sans accents, en minuscules)
        $mealType = strtolower($rawMealType);
        $mealType = strtr($mealType, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c']);
        $mealType = trim($mealType);

        // Mapper variantes
        $map = [
            'breakfast' => 'petit-dejeuner',
            'petit-dejeuner' => 'petit_dejeuner',
            'dejeuner' => 'dejeuner',
            'lunch' => 'dejeuner',
            'gouter' => 'gouter',
            'snack' => 'gouter',
            'diner' => 'diner',
            'dinner' => 'diner',
            'en-cas' => 'en-cas',
            'snacks' => 'en-cas',
        ];
        if (isset($map[$mealType]))
        {
            $mealType = $map[$mealType];
        }

        if (!$foodId || !$mealType)
        {
            ResponseHelper::addErrorMessage('Données manquantes.');
            ResponseHelper::redirectToMeals();
        }

        // Debug: log request payload when adding from form that posts to ?page=meals
        $storageDir = __DIR__ . '/../../storage';
        if (!is_dir($storageDir))
        {
            mkdir($storageDir, 0755, true);
        }
        file_put_contents($storageDir . '/debug_add_requests.log', '[meals_form] ' . date('c') . ' ' . json_encode($_POST) . PHP_EOL, FILE_APPEND);

        $result = $mealManager->addFoodToMeal($foodId, $quantity, $mealType);

        if ($result['success'])
        {
            ResponseHelper::addSuccessMessage($result['message']);
        } else
        {
            ResponseHelper::addErrorMessage($result['message']);
        }

        ResponseHelper::redirectToMeals();
    }

    /**
     * Traiter la suppression d'un aliment d'un repas.
     */
    public static function handleRemoveFoodFromMeal(MealManager $mealManager): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['action']) || $_GET['action'] !== 'supprimer-aliment')
        {
            return;
        }

        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        // Parser le JSON body pour les requêtes AJAX
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Support CSRF token depuis POST (formulaires) ou header (AJAX)
        $csrf = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf))
        {
            // Si requête AJAX, retourner JSON
            if ($isAjax)
            {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session invalide (CSRF).']);
                exit;
            }
            ResponseHelper::addErrorMessage('Session invalide (CSRF).');
            ResponseHelper::redirectToMeals();
        }

        $foodId = (int)($input['aliment_id'] ?? $_POST['aliment_id'] ?? 0);
        $mealId = (int)($input['repas_id'] ?? $_POST['repas_id'] ?? 0);

        if (!$foodId || !$mealId)
        {
            if ($isAjax)
            {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
                exit;
            }
            ResponseHelper::addErrorMessage('Données manquantes.');
            ResponseHelper::redirectToMeals();
        }

        $result = $mealManager->removeFoodFromMeal($mealId, $foodId);

        // Si requête AJAX, retourner JSON
        if ($isAjax)
        {
            header('Content-Type: application/json');
            if ($result)
            {
                echo json_encode(['success' => true, 'message' => 'Aliment supprimé du repas avec succès.']);
            } else
            {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
            }
            exit;
        }

        if ($result)
        {
            ResponseHelper::addSuccessMessage('Aliment supprimé du repas avec succès.');
        } else
        {
            ResponseHelper::addErrorMessage('Erreur lors de la suppression de l\'aliment.');
        }

        ResponseHelper::redirectToMeals();
    }

    /**
     * Traiter la suppression d'un repas complet.
     */
    public static function handleDeleteMeal(MealManager $mealManager): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['action']) || $_GET['action'] !== 'supprimer-repas')
        {
            return;
        }

        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        // Support CSRF token depuis POST (formulaires) ou header (AJAX)
        $csrf = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf))
        {
            // Si requête AJAX, retourner JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session invalide (CSRF).']);
                exit;
            }
            ResponseHelper::addErrorMessage('Session invalide (CSRF).');
            ResponseHelper::redirectToMeals();
        }

        $mealId = (int)($_POST['repas_id'] ?? 0);

        if (!$mealId)
        {
            ResponseHelper::addErrorMessage('ID repas manquant.');
            ResponseHelper::redirectToMeals();
        }

        $result = $mealManager->deleteMeal($mealId);
        $message = $result ? 'Repas supprimé avec succès' : 'Erreur lors de la suppression';
        $type = $result ? 'success' : 'error';

        ResponseHelper::redirectWithFlash('meals', $type, $message);
    }
}
