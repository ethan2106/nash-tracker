<?php

/**
 * Page: Gestion des Médicaments.
 * @description Interface de suivi des prises de médicaments
 * @requires Alpine.js pour la gestion interactive
 */

declare(strict_types=1);

$pageTitle = 'Gestion des Médicaments';
$pageJs = [];
$pageCss = ['medicaments.css'];

ob_start();
?>

<script src="/js/components/alpine-medicaments.js?v=<?= time(); ?>"></script>

<!-- ========== CONTENU PRINCIPAL ========== -->
<div class="min-h-screen py-8"
     x-data="medicamentsManager()"
     x-init="init()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php
        // En-tête et sélecteur de date
        include __DIR__ . '/components/medicaments/header.php';
?>
        
        <?php
// Liste des médicaments par section
include __DIR__ . '/components/medicaments/medicaments-list.php';
?>

    </div>
    
    <?php
    // Modal ajout/édition (doit être dans le scope Alpine)
    include __DIR__ . '/components/medicaments/form-modal.php';
?>
    
    <?php
// Modal historique (doit être dans le scope Alpine)
include __DIR__ . '/components/medicaments/historique-modal.php';
?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
