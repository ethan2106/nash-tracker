<?php

/**
 * Page Mes Repas.
 *
 * @description Suivi des repas et nutrition quotidienne
 * - Affichage par type de repas (petit-déj, déjeuner, dîner, collations)
 * - Totaux nutritionnels avec objectifs
 * - Gamification : streak alimentation, badges nutrition
 *
 * @requires Alpine.js - Composant: mealsManager()
 * @see /js/components/alpine-meals.js
 */

declare(strict_types=1);

// ============================================================
// IMPORTS & DÉPENDANCES
// ============================================================
require_once __DIR__ . '/components/meal_section.php';
require_once __DIR__ . '/../Helper/view_helpers.php';

// ============================================================
// RÉCUPÉRATION DES DONNÉES (passées par le contrôleur)
// ============================================================

// Extraction explicite des variables pour la vue (plus sûr que extract)
/** @var array $mealsByType Repas groupés par type (petit_dejeuner, dejeuner, diner, collations) */
$mealsByType = $GLOBALS['mealsByType'] ?? [];

/** @var string $selectedDate Date sélectionnée (Y-m-d) */
$selectedDate = $GLOBALS['selectedDate'] ?? date('Y-m-d');

/** @var bool $isToday True si date = aujourd'hui (permet modification) */
$isToday = $GLOBALS['isToday'] ?? true;

/** @var array $totals Totaux nutritionnels (calories, proteines, glucides, etc.) */
$totals = $GLOBALS['totals'] ?? [];

/** @var array|null $objectifs Objectifs utilisateur (calories_perte, proteines_max, etc.) */
$objectifs = $GLOBALS['objectifs'] ?? null;

/** @var array $sections Configuration des sections repas (icône, couleur, etc.) */
$sections = $GLOBALS['sections'] ?? [];

/** @var string $csrf_token Token CSRF pour les formulaires */
$csrf_token = $GLOBALS['csrf_token'] ?? '';

/** @var int $streakNutrition Streak de jours avec repas loggés */
$streakNutrition = $GLOBALS['streakNutrition'] ?? 0;

/** @var array $badgesNutrition Badges nutrition gagnés et à gagner */
$badgesNutrition = $GLOBALS['badgesNutrition'] ?? ['earned' => [], 'toEarn' => []];

// ============================================================
// VALEURS PAR DÉFAUT (évite undefined côté Alpine.js)
// ============================================================
$defaultObjectifs = [
    'calories_perte' => 2000,
    'proteines_max' => 150,
    'graisses_sat_max' => 22,
    'sucres_max' => 50,
    'fibres_min' => 25,
];
$objectifs = $objectifs ?? $defaultObjectifs;

// ============================================================
// RENDU HTML
// ============================================================
ob_start();
?>
<!-- Script Alpine.js pour la page repas -->
<script src="/js/components/alpine-meals.js?v=1.0.1"></script>

<!-- Fallback si JavaScript désactivé -->
<noscript>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-exclamation-triangle text-yellow-600"></i>
            <p class="text-yellow-800 text-sm">JavaScript est désactivé. Certaines fonctionnalités interactives ne sont pas disponibles.</p>
        </div>
    </div>
</noscript>

<!-- ============================================================
     CONTAINER PRINCIPAL
     - Alpine.js: x-data="mealsManager()"
     - Data attributes: passage des données PHP → JS
     ============================================================ -->
<div class="min-h-screen"
     x-data="mealsManager()"
     data-meals='<?= htmlspecialchars(json_encode($mealsByType), ENT_QUOTES); ?>'
     data-selected-date='<?= htmlspecialchars($selectedDate, ENT_QUOTES); ?>'
     data-is-today='<?= json_encode($isToday); ?>'
     data-totals='<?= htmlspecialchars(json_encode($totals), ENT_QUOTES); ?>'
     data-objectifs='<?= htmlspecialchars(json_encode($objectifs), ENT_QUOTES); ?>'
     data-csrf-token='<?= htmlspecialchars($csrf_token, ENT_QUOTES); ?>'
     data-streak="<?= $streakNutrition; ?>"
     data-badges-earned='<?= htmlspecialchars(json_encode($badgesNutrition['earned']), ENT_QUOTES); ?>'
     x-init="init()">
    
    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <!-- ==================== HEADER + SÉLECTEUR DATE ==================== -->
        <?php require __DIR__ . '/components/meals/header.php'; ?>

        <!-- ==================== GAMIFICATION (Streak + Badges) ==================== -->
        <?php require __DIR__ . '/components/meals/badges.php'; ?>

        <!-- ==================== TOTAUX JOURNALIERS NUTRITION ==================== -->
        <?php require __DIR__ . '/components/meals/daily-totals.php'; ?>

        <!-- ==================== SECTIONS REPAS (4 types) ==================== -->
        <!-- Petit-déjeuner, Déjeuner, Dîner, Collations -->
        <div class="space-y-6">
        <?php
        foreach ($sections as $type => $config)
        {
            // Utilise le composant meal_section existant
            renderMealSection($mealsByType[$type] ?? [], $type, $config, $csrf_token, $isToday);
        }
?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
