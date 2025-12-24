<?php

/**
 * Page Paramètres.
 *
 * @description Gestion complète du compte utilisateur et préférences
 *
 * Composants inclus:
 * - header.php              : En-tête avec titre et lien retour
 * - tabs-navigation.php     : Navigation 5 onglets
 * - tab-compte.php          : Onglet compte (email, pseudo, mdp, suppression)
 * - tab-profil-sante.php    : Onglet profil santé (historique objectifs, graphique)
 * - tab-preferences-sante.php : Onglet préférences (seuils, conditions médicales)
 * - tab-notifications.php   : Onglet notifications (toggles, période silencieuse)
 * - tab-export.php          : Onglet export données (PDF/CSV)
 * - scripts.php             : JavaScript (chartLoader, export functions)
 */

declare(strict_types=1);

// ============================================================
// CSRF TOKEN
// ============================================================
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// LES DONNÉES SONT PRÉPARÉES PAR LE CONTROLLER
// ============================================================
/** @var array $historiqueMesures */
$historiqueMesures = $data['historiqueMesures'] ?? [];
/** @var array $allHistoriqueMesures */
$allHistoriqueMesures = $data['allHistoriqueMesures'] ?? [];
/** @var array $mesuresPagination */
$mesuresPagination = $data['mesuresPagination'] ?? [];
/** @var array $userConfig */
$userConfig = $data['userConfig'] ?? [];
$userConfigJson = json_encode($userConfig);
if ($userConfigJson === false) {
    $userConfigJson = '{}'; // Fallback to empty object
}

// ============================================================
// CONFIGURATION PAGE
// ============================================================
$title = 'Paramètres - Suivi Nash';
$pageJs = ['alpine-settings.js'];

/** @var callable $e Helper pour échapper le HTML */
$e = fn (string|int|float|null $str): string => htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');

// ============================================================
// DÉBUT DU CONTENU
// ============================================================
ob_start();
?>

<!-- Meta CSRF Token -->
<meta name="csrf-token" content="<?= $_SESSION['csrf_token']; ?>">

<!-- ============================================================
     CONTENEUR PRINCIPAL ALPINE.JS
     - x-data="settingsManager()" défini dans alpine-settings.js
     - data-* pour initialisation des valeurs
     ============================================================ -->
<div class="min-h-screen"
     x-data="settingsManager()"
     data-email="<?= $e($user['email'] ?? ''); ?>"
     data-pseudo="<?= $e($user['pseudo'] ?? ''); ?>"
     data-user-config='<?= $userConfigJson; ?>'
     x-init="init()">
    
    <div class="max-w-5xl mx-auto px-6 py-8">
        
        <!-- ========== HEADER ========== -->
        <?php include __DIR__ . '/components/settings/header.php'; ?>

        <!-- ========== CONTENEUR ONGLETS ========== -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/50 overflow-hidden">
            
            <!-- ========== NAVIGATION ONGLETS ========== -->
            <?php include __DIR__ . '/components/settings/tabs-navigation.php'; ?>

            <!-- ========== CONTENU DES ONGLETS ========== -->
            <div class="p-8">
                
                <!-- ========== ONGLET COMPTE ========== -->
                <?php include __DIR__ . '/components/settings/tab-compte.php'; ?>

                <!-- ========== ONGLET PROFIL SANTÉ ========== -->
                <?php include __DIR__ . '/components/settings/tab-profil-sante.php'; ?>

                <!-- ========== ONGLET PRÉFÉRENCES SANTÉ ========== -->
                <?php include __DIR__ . '/components/settings/tab-preferences-sante.php'; ?>

                <!-- ========== ONGLET NOTIFICATIONS ========== -->
                <?php include __DIR__ . '/components/settings/tab-notifications.php'; ?>

                <!-- ========== ONGLET EXPORT DONNÉES ========== -->
                <?php include __DIR__ . '/components/settings/tab-export.php'; ?>

            </div>
        </div>

    </div>
</div>

<!-- ========== SCRIPTS JAVASCRIPT ========== -->
<?php include __DIR__ . '/components/settings/scripts.php'; ?>

<?php
// ============================================================
// FIN DU CONTENU & LAYOUT
// ============================================================
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
