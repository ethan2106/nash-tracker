<?php

/**
 * Page Calcul IMC & Besoins Santé.
 *
 * @description Vue principale IMC avec calculs métaboliques et objectifs NAFLD
 *
 * Composants inclus:
 * - header.php          : En-tête avec titre et icône
 * - form-fields.php     : Champs formulaire (taille, poids, année, sexe, activité, objectif)
 * - imc-display.php     : Affichage visuel IMC avec barre et catégories
 * - nafld-timeline.php  : Timeline 3 étapes parcours NAFLD
 * - caloric-needs.php   : BMR, TDEE et objectifs caloriques
 * - macro-goals.php     : 6 cartes d'objectifs macros NAFLD + bouton submit
 * - chart.php           : Graphique évolution Chart.js
 */

declare(strict_types=1);

// ============================================================
// INITIALISATION & DÉPENDANCES
// ============================================================
$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

require_once __DIR__ . '/../Service/ServiceContainer.php';
require_once __DIR__ . '/../Model/ImcModel.php';
require_once __DIR__ . '/../Helper/view_helpers.php';

// ============================================================
// CONFIGURATION PAGE
// ============================================================
$title = 'Calcul IMC - Suivi Nash';
$pageJs = ['imc-calculator.js']; // Vanilla JS au lieu d'Alpine

/** @var array|null $user */
$user = $_SESSION['user'] ?? null;

// LES DONNÉES SONT PRÉPARÉES PAR LE CONTROLLER QUI APPELLE CETTE VUE

// ============================================================
// HELPERS & CALCULS UX
// ============================================================

/** @var callable $escape Helper pour échapper le HTML */
$escape = fn (string|int|float|null $str): string => htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');

/** @var float $imcValue */
$imcValue = isset($data['imc']) ? (float)$data['imc'] : 0.0;

/** @var int $imcPoints */
$imcPoints = function_exists('getIMCScorePoints') ? getIMCScorePoints($imcValue) : 0;

/** @var string $imcAdvice */
$imcAdvice = function_exists('getIMCAdvice') ? getIMCAdvice($imcValue) : '';

// ============================================================
// DÉBUT DU CONTENU
// ============================================================
ob_start();
?>

<!-- ============================================================
     CONTENEUR PRINCIPAL - VANILLA JS
     - data-imc-calculator pour initialisation
     - data-* pour les valeurs initiales
     ============================================================ -->
<div class="min-h-screen"
     data-imc-calculator
     data-taille="<?= $escape($data['taille'] ?? ''); ?>"
     data-poids="<?= $escape($data['poids'] ?? ''); ?>"
     data-annee="<?= $escape($data['annee'] ?? ''); ?>"
     data-sexe="<?= $escape($data['sexe'] ?? ''); ?>"
     data-activite="<?= $escape($data['activite'] ?? ''); ?>"
     data-objectif="<?= $escape($data['objectif'] ?? 'perte'); ?>">
    
    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <!-- ========== HEADER ========== -->
        <?php include __DIR__ . '/components/imc/header.php'; ?>

        <!-- ========== FORMULAIRE PRINCIPAL ========== -->
        <div class="bg-white/60 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-blue-100">
            <!-- Classes Tailwind sûres pour éviter la purge CSS -->
            <div class="hidden bg-blue-200 text-blue-800 bg-green-200 text-green-800 bg-orange-200 text-orange-800 bg-red-200 text-red-800"></div>
            
            <form method="post" action="?page=save_objectifs">
                
                <!-- ========== CHAMPS FORMULAIRE ========== -->
                <?php include __DIR__ . '/components/imc/form-fields-vanilla.php'; ?>
                
                <!-- ========== AFFICHAGE VISUEL IMC ========== -->
                <?php include __DIR__ . '/components/imc/imc-display-vanilla.php'; ?>
                
                <!-- ========== TIMELINE NAFLD ========== -->
                <?php include __DIR__ . '/components/imc/nafld-timeline.php'; ?>
                
                <!-- ========== BESOINS CALORIQUES ========== -->
                <?php include __DIR__ . '/components/imc/caloric-needs-vanilla.php'; ?>
                
                <!-- ========== OBJECTIFS MACROS NAFLD (inclut bouton submit et ferme </form>) ========== -->
                <?php include __DIR__ . '/components/imc/macro-goals-vanilla.php'; ?>
        </div>

        <!-- ========== GRAPHIQUE ÉVOLUTION ========== -->
        <?php include __DIR__ . '/components/imc/chart.php'; ?>
        
    </div>
</div>

<?php
// ============================================================
// FIN DU CONTENU & LAYOUT
// ============================================================
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>

<!-- ========== DÉPENDANCES EXTERNES ========== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ========== FALLBACK NOSCRIPT ========== -->
<noscript>
    <style>
        .animate-bounce, .animate-fade-in, .transition-all, .transition-colors, .transition-opacity, .transition-shadow, .transition-transform {
            animation: none !important;
            transition: none !important;
        }
    </style>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-[var(--z-overlay)]">
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-800 mb-2">JavaScript requis</h3>
            <p class="text-gray-600 mb-4">Cette page nécessite JavaScript pour fonctionner correctement. Veuillez activer JavaScript dans votre navigateur.</p>
            <a href="?page=profile" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">Retour au profil</a>
        </div>
    </div>
</noscript>
