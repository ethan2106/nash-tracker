<?php

/**
 * Page: Catalogue Alimentaire.
 * @description Interface de consultation et recherche des aliments
 * @requires Alpine.js pour la gestion interactive du catalogue
 */

declare(strict_types=1);

$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

$title = 'Catalogue Alimentaire - Suivi Nash';
$user = $_SESSION['user'] ?? null;

// Assurer que les variables sont définies
$savedFoods = $savedFoods ?? [];
$totalFoods = $totalFoods ?? 0;
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$perPage = $perPage ?? 12;

// Inclure les helpers pour les composants de qualité alimentaire
require_once __DIR__ . '/../Helper/food_quality_helpers.php';

// Redirection si non connecté
if (!$user)
{
    header('Location: ?page=login');
    exit;
}

ob_start();
?>

<script src="/js/components/alpine-catalog.js?v=<?= time(); ?>"></script>

<script>
    // Passer les données PHP à Alpine.js
    window.catalogFoods = <?= json_encode($savedFoods ?? []); ?>;
    window.csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';
</script>

<!-- ========== CONTENU PRINCIPAL ========== -->
<div class="min-h-screen"
     x-data="catalogManager()"
     x-init="init()">
    
    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <?php
        // En-tête avec recherche et statistiques
        include __DIR__ . '/components/catalog/header.php';
?>
        
        <?php
// Liste des aliments - inclut l'état vide
include __DIR__ . '/components/catalog/food-list.php';
?>
        
        <?php
// Pagination
include __DIR__ . '/components/catalog/pagination.php';
?>
        
    </div>
    
    <?php
    // Modal détails aliment
    include __DIR__ . '/components/catalog/details-modal.php';
?>
    
    <?php
// Modal quantité pour ajout au repas
include __DIR__ . '/components/catalog/quantity-modal.php';
?>
    
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
?>
