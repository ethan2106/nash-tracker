<?php

/**
 * Page: Recherche Aliments.
 * @description Interface de recherche et ajout d'aliments au catalogue
 * @requires Alpine.js pour la gestion interactive
 */

declare(strict_types=1);

// Variables préparées par le contrôleur
$title = $title ?? 'Recherche Aliments - Suivi Nash';
$user = $user ?? null;
$mealType = $mealType ?? 'repas';
$currentMealLabel = $currentMealLabel ?? 'Repas';
$mealTypeLabels = $mealTypeLabels ?? [];
$query = $query ?? '';
$searchType = $searchType ?? 'text';
$results = $searchResults ?? [];
$error = $searchError ?? '';
$singleProduct = $singleProduct ?? null;

// Inclure les helpers pour les composants de qualité alimentaire
require_once __DIR__ . '/../Helper/food_quality_helpers.php';

ob_start();
?>

<!-- ========== CONTENU PRINCIPAL ========== -->
<div class="min-h-screen" x-data="foodSearchManager()">
    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <?php
        // Header de la page
        include __DIR__ . '/components/food/header.php';
?>
        
        <?php
        // Formulaires de recherche (texte, code-barre, manuel)
        include __DIR__ . '/components/food/search-form.php';
?>
        
        <?php
// Résultats de recherche
include __DIR__ . '/components/food/search-results.php';
?>
        
        <?php
// Produit unique (recherche par code-barre)
include __DIR__ . '/components/food/single-product.php';
?>

    </div>
    
    <?php
    // Modal de détails complets (doit être dans le scope Alpine)
    include __DIR__ . '/components/food/details-modal.php';
?>
</div>

<!-- ========== SCRIPTS ========== -->
<script>
window.currentMealType = '<?= htmlspecialchars($mealType); ?>';
window.mealTypeLabels = <?= json_encode($mealTypeLabels); ?>;
</script>
<script src="/js/components/alpine-food.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
