<?php

/**
 * Page WalkTrack - Suivi des marches.
 *
 * - Carte interactive OpenStreetMap/Leaflet
 * - Tracé de parcours A → B
 * - Stats et objectifs personnalisés
 * - Gamification (streak, badges)
 * - Historique des marches
 * - Favoris parcours
 */

declare(strict_types=1);

// Session
$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

$title = 'WalkTrack - Suivi Nash';

/** @var array|null $user */
$user = $_SESSION['user'] ?? null;

// LES DONNÉES SONT PRÉPARÉES PAR LE CONTROLLER QUI APPELLE CETTE VUE

// Données préparées par le contrôleur
$marches = $marches ?? [];
$totals = array_merge([
    'distance_km' => 0,
    'duration_minutes' => 0,
    'calories' => 0,
    'count' => 0,
], $totals ?? []);
$historique = $historique ?? [];
$objectifs = array_merge([
    'km_per_day' => 5,
    'days_per_week' => 4,
], $objectifs ?? []);
$progression = $progression ?? [];
$streak = $streak ?? 0;
$totalJours = $totalJours ?? 0;
$badges = $badges ?? ['earned' => [], 'toEarn' => []];
$parcoursFavoris = $parcours ?? [];
$userWeight = $userWeight ?? 70;

ob_start();
?>

<!-- ============================================================
     PAGE WALKTRACK
     Structure: Header + Stats + Carte/Formulaire + Historique
     ============================================================ -->
<div class="min-h-screen" id="walktrack-app">

    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <!-- ==================== HEADER ==================== -->
        <?php require __DIR__ . '/components/walktrack/header.php'; ?>

        <!-- ==================== GAMIFICATION (Streak + Badges) ==================== -->
        <?php require __DIR__ . '/components/walktrack/badges.php'; ?>

        <!-- ==================== STATS DU JOUR + PROGRESSION ==================== -->
        <?php require __DIR__ . '/components/walktrack/stats.php'; ?>

        <!-- ==================== GRILLE PRINCIPALE ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            
            <!-- COLONNE GAUCHE: Carte + Formulaire (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Carte OpenStreetMap -->
                <?php require __DIR__ . '/components/walktrack/map.php'; ?>
                
                <!-- Formulaire d'ajout -->
                <?php require __DIR__ . '/components/walktrack/add-walk.php'; ?>
            </div>

            <!-- COLONNE DROITE: Objectifs + Historique (1/3) -->
            <div class="space-y-6">
                
                <!-- Objectifs -->
                <?php require __DIR__ . '/components/walktrack/objectives.php'; ?>
                
                <!-- Historique -->
                <?php require __DIR__ . '/components/walktrack/history.php'; ?>
            </div>
        </div>

        <!-- ==================== LISTE DES MARCHES DU JOUR ==================== -->
        <?php require __DIR__ . '/components/walktrack/today-list.php'; ?>
    </div>
</div>

<!-- ============================================================
     MODALE ÉDITION MARCHE
     ============================================================ -->
<div id="modal-edit-walk" class="fixed inset-0 z-[var(--z-modal)] hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="modal-edit-overlay"></div>
    
    <!-- Contenu -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative animate-fade-in">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen text-amber-500"></i>
                    Modifier la marche
                </h3>
                <button type="button" id="btn-close-edit-modal" class="p-2 rounded-lg hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-times text-slate-400"></i>
                </button>
            </div>
            
            <!-- Formulaire -->
            <form id="form-edit-walk" class="p-5 space-y-4">
                <input type="hidden" id="edit-walk-id" name="walk_id">
                
                <!-- Type de marche (lecture seule) -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Type de marche</label>
                    <div id="edit-walk-type-display" class="p-3 rounded-xl bg-slate-100 text-slate-600 flex items-center gap-2">
                        <i class="fa-solid fa-person-walking"></i>
                        <span>Marche normale</span>
                    </div>
                </div>
                
                <!-- Distance (lecture seule) -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2">Distance</label>
                    <div id="edit-distance-display" class="p-3 rounded-xl bg-slate-100 text-slate-600">
                        0.00 km
                        <span class="text-xs text-slate-400 ml-2">(non modifiable - tracé sur carte)</span>
                    </div>
                </div>
                
                <!-- Heures départ / arrivée -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2" for="edit-start-time">
                            Heure de départ
                        </label>
                        <input type="time" 
                               id="edit-start-time"
                               name="start_time"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent text-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-2" for="edit-end-time">
                            Heure d'arrivée
                        </label>
                        <input type="time" 
                               id="edit-end-time"
                               name="end_time"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent text-lg">
                    </div>
                </div>
                
                <!-- Durée -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2" for="edit-duration">
                        Durée (minutes)
                        <span class="text-xs text-slate-400 ml-1" id="edit-duration-source"></span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="edit-duration"
                               name="duration_minutes"
                               min="1"
                               max="600"
                               required
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent text-lg">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">min</span>
                    </div>
                </div>
                
                <!-- Note -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-2" for="edit-note">
                        Note <span class="text-xs text-slate-400">(optionnel)</span>
                    </label>
                    <input type="text" 
                           id="edit-note"
                           name="note"
                           maxlength="200"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                
                <!-- Boutons -->
                <div class="flex gap-3 pt-2">
                    <button type="button" 
                            id="btn-cancel-edit"
                            class="flex-1 py-3 px-4 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition-all font-medium">
                        Annuler
                    </button>
                    <button type="submit" 
                            id="btn-save-edit"
                            class="flex-1 py-3 px-4 rounded-xl bg-amber-500 text-white hover:bg-amber-600 transition-all font-medium flex items-center justify-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================
     DONNÉES GLOBALES JAVASCRIPT
     ============================================================ -->
<script>
// Données initiales pour JS
window.walktrackData = {
    marches: <?= json_encode($marches, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    totals: <?= json_encode($totals, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    historique: <?= json_encode($historique, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    objectifs: <?= json_encode($objectifs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    progression: <?= json_encode($progression, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    streak: <?= $streak; ?>,
    totalJours: <?= $totalJours; ?>,
    badges: <?= json_encode($badges, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    parcoursFavoris: <?= json_encode($parcoursFavoris, JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
    userWeight: <?= $userWeight; ?>
};
</script>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- WalkTrack JS Modules -->
<script src="/js/walktrack/core.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/map.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/address.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/form.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/walks.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/parcours.js?v=<?= time(); ?>"></script>
<script src="/js/walktrack/main.js?v=<?= time(); ?>"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
