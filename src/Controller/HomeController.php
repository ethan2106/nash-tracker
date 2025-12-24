<?php

namespace App\Controller;

use App\Model\UserConfigModel;
use App\Service\DashboardService;
use App\Service\GamificationService;
use App\Service\NutritionService;
use App\Service\ActivityService;
use App\Model\Database;

/**
 * HomeController - Contrôleur pour la page d'accueil/dashboard.
 *
 * Responsabilités :
 * - Routage vers la vue home
 * - Injection des données du dashboard via DashboardService
 */
class HomeController
{
    private DashboardService $dashboardService;

    private UserConfigModel $userConfigModel;

    private GamificationService $gamificationService;

    private NutritionService $nutritionService;

    private ActivityService $activityService;

    public function __construct(DashboardService $dashboardService, UserConfigModel $userConfigModel, GamificationService $gamificationService, NutritionService $nutritionService = null, ActivityService $activityService = null)
    {
        $this->dashboardService = $dashboardService;
        $this->userConfigModel = $userConfigModel;
        $this->gamificationService = $gamificationService;
        $this->nutritionService = $nutritionService ?? new NutritionService((new Database())->getConnection());
        $this->activityService = $activityService ?? new ActivityService((new Database())->getConnection());
    }

    /**
     * Détermine l'action du jour basée sur les objectifs.
     */
    private function getDailyAction($userId, $objectifs, $currentNutrition, $activityMinutes)
    {
        if (!$objectifs) {
            return "Ajoutez votre premier repas pour commencer";
        }

        $caloriesConsumed = $currentNutrition['calories'] ?? 0;
        $caloriesTarget = $objectifs['calories_perte'] ?? 0;
        $proteinesConsumed = $currentNutrition['proteines'] ?? 0;
        $proteinesTarget = $objectifs['proteines_min'] ?? 0;

        // Règle 1: Nutrition prioritaire
        if ($caloriesTarget > 0 && $caloriesConsumed < $caloriesTarget * 0.5 ||
            $proteinesTarget > 0 && $proteinesConsumed < $proteinesTarget * 0.5) {
            return "Ajoutez un repas équilibré pour prendre soin de votre foie";
        }

        // Règle 2: Activité
        if ($activityMinutes < 20) {
            return "Marchez 10 minutes pour booster votre énergie";
        }

        // Règle 3: Suivi médical
        if (($objectifs['imc'] ?? 0) > 25) {
            return "Mesurez votre IMC cette semaine";
        }

        // Règle 4: Pause méritée
        return "Prenez une pause méritée – vous faites du bon travail !";
    }

    /**
     * Détermine l'URL de destination pour l'action du jour.
     */
    private function getDailyActionUrl($userId, $objectifs, $currentNutrition, $activityMinutes)
    {
        if (!$objectifs) {
            return "?page=meals"; // Premier repas
        }

        $caloriesConsumed = $currentNutrition['calories'] ?? 0;
        $caloriesTarget = $objectifs['calories_perte'] ?? 0;
        $proteinesConsumed = $currentNutrition['proteines'] ?? 0;
        $proteinesTarget = $objectifs['proteines_min'] ?? 0;

        // Règle 1: Nutrition prioritaire
        if ($caloriesTarget > 0 && $caloriesConsumed < $caloriesTarget * 0.5 ||
            $proteinesTarget > 0 && $proteinesConsumed < $proteinesTarget * 0.5) {
            return "?page=meals"; // Ajouter un repas
        }

        // Règle 2: Activité
        if ($activityMinutes < 20) {
            return "?page=walktrack"; // Activité physique
        }

        // Règle 3: Suivi médical
        if (($objectifs['imc'] ?? 0) > 25) {
            return "?page=imc"; // Mesure IMC
        }

        // Règle 4: Pause méritée
        return "?page=profile"; // Voir le profil
    }

    /**
     * Détermine l'état de la journée.
     */
    private function getDayState($currentNutrition, $activityMinutes, $objectifs)
    {
        $hasData = ($currentNutrition['calories'] ?? 0) > 0 || $activityMinutes > 0;

        if (!$hasData) {
            return 'empty';
        }

        // Calculer % objectifs (simplifié)
        $caloriesCompletion = $objectifs && $objectifs['calories_perte'] > 0 ?
            min(100, ($currentNutrition['calories'] ?? 0) / $objectifs['calories_perte'] * 100) : 0;
        $proteinesCompletion = $objectifs && $objectifs['proteines_min'] > 0 ?
            min(100, ($currentNutrition['proteines'] ?? 0) / $objectifs['proteines_min'] * 100) : 0;
        $activityCompletion = min(100, $activityMinutes / 30 * 100); // Objectif 30 min

        $avgCompletion = ($caloriesCompletion + $proteinesCompletion + $activityCompletion) / 3;

        if ($avgCompletion > 80) return 'success';
        if ($avgCompletion > 30) return 'partial';
        return 'late';
    }

    /**
     * Génère un conseil dynamique basé sur les données réelles.
     */
    private function getDynamicAdvice($objectifs, $currentNutrition, $activityMinutes, $scoreGlobal)
    {
        // Conseils basés sur la nutrition
        $caloriesConsumed = $currentNutrition['calories'] ?? 0;
        $caloriesTarget = $objectifs['calories_perte'] ?? 0;
        $proteinesConsumed = $currentNutrition['proteines'] ?? 0;
        $proteinesTarget = $objectifs['proteines_min'] ?? 0;

        // Si pas assez de protéines
        if ($proteinesTarget > 0 && $proteinesConsumed < $proteinesTarget * 0.7) {
            return "🍗 Augmentez votre apport en protéines – essentielles pour votre foie !";
        }

        // Si trop de calories
        if ($caloriesTarget > 0 && $caloriesConsumed > $caloriesTarget * 1.2) {
            return "⚖️ Attention à votre équilibre calorique pour atteindre vos objectifs";
        }

        // Si pas assez d'activité
        if ($activityMinutes < 15) {
            return "🏃‍♂️ Un peu d'activité physique booste votre métabolisme hépatique";
        }

        // Si score faible
        if ($scoreGlobal < 50) {
            return "📈 Concentrez-vous sur vos repas équilibrés pour améliorer votre score";
        }

        // Si objectifs atteints
        if ($scoreGlobal > 75) {
            return "🌟 Excellente journée ! Continuez sur cette lancée";
        }

        // Conseil par défaut sur les fibres
        return "🥦 Privilégiez les aliments riches en fibres pour votre santé hépatique";
    }

    /**
     * Prépare toutes les données pour la vue home.
     */
    public function prepareHomeViewData($user)
    {
        $isLoggedIn = $user !== null;

        $viewData = [
            'isLoggedIn' => $isLoggedIn,
            'pageTitle' => $isLoggedIn ? 'Tableau de bord' : 'Prenez le contrôle de votre Santé Hépatique',
            'pageSubtitle' => $isLoggedIn
                ? 'Bonjour ' . ($user['pseudo'] ?? 'Utilisateur') . ', voici votre bilan du jour.'
                : 'Votre compagnon quotidien pour gérer la stéatose hépatique (NASH/NAFLD). Nutrition, IMC, et suivi médical en toute simplicité.',
            'user' => $user,
        ];

        if ($isLoggedIn)
        {
            $dashboardData = $this->dashboardService->getDashboardData($user);
            $viewData['dashboard'] = $dashboardData;

            // Récupérer la configuration utilisateur
            $viewData['userConfig'] = $this->userConfigModel->getAll($user['id']);

            // Calculer les données de gamification
            $scoreGlobal = $dashboardData['scores']['global'] ?? 0;
            $viewData['levelData'] = $this->gamificationService->computeLevel((int)($scoreGlobal * 10));

            // Propager les toasts générés par le service vers la vue
            $viewData['toasts'] = $dashboardData['toasts'] ?? [];

            // NOUVELLES DONNÉES POUR LE PROTOTYPE
            $objectifs = $dashboardData['objectifs'] ?? null;
            $currentNutrition = $this->nutritionService->getCurrentNutrition($user['id']);
            // TODO: Calculer activité du jour (somme des minutes d'aujourd'hui)
            $activityMinutes = 0; // Temporaire

            $viewData['dailyAction'] = $this->getDailyAction($user['id'], $objectifs, $currentNutrition, $activityMinutes);
            $viewData['dayState'] = $this->getDayState($currentNutrition, $activityMinutes, $objectifs);
            $viewData['currentNutrition'] = $currentNutrition;
            $viewData['activityMinutes'] = $activityMinutes;
            $viewData['dynamicAdvice'] = $this->getDynamicAdvice($objectifs, $currentNutrition, $activityMinutes, $scoreGlobal);
            $viewData['dailyActionUrl'] = $this->getDailyActionUrl($user['id'], $objectifs, $currentNutrition, $activityMinutes);
        }

        return $viewData;
    }

    /**
     * Récupère les données du tableau de bord
     */
    public function getDashboardData($user)
    {
        return $this->dashboardService->getDashboardData($user);
    }
}
