<?php
/**
 * Dashboard v2 - Page centrale post-login avec vue d'ensemble complète
 * Affiche: Score santé, stats clés, graphiques, timeline, actions rapides.
 */
$pageTitle = $viewData['pageTitle'];
$pageSubtitle = $viewData['pageSubtitle'];
$user = $viewData['user'];
$dashboard = $viewData['dashboard'] ?? [];
$objectifs = $dashboard['objectifs'] ?? null;
$stats = $dashboard['stats'] ?? [];
$dailyGoals = $dashboard['dailyGoals'] ?? [];
$recentActivity = $dashboard['recentActivity'] ?? [];
$userConfig = $viewData['userConfig'] ?? [];
$scores = $dashboard['scores'] ?? [];
$scoreComponents = $scores['components'] ?? [];
$scoreGlobal = isset($scores['global']) ? (int)$scores['global'] : 0;



// Compatibilité avec le rendu détaillé existant (issues de $dashboard['scores'])
$imcScore = $scoreComponents['IMC'] ?? 0;
$caloriesScore = $scoreComponents['Calories'] ?? 0;
$activityScore = $scoreComponents['Activité'] ?? 0;

// Couleur et label du score (helpers de présentation)
$scoreColor = getScoreColor($scoreGlobal);
$scoreLabel = getScoreLabel($scoreGlobal);
$scoreColorTailwind = getScoreTailwindColor($scoreGlobal);

// Alertes NAFLD
$naflAlerts = [];
if ($objectifs && $objectifs['imc'] > 30)
{
    $naflAlerts[] = ['type' => 'danger', 'message' => 'IMC > 30 : Risque élevé de NAFLD', 'icon' => 'fa-triangle-exclamation'];
} elseif ($objectifs && $objectifs['imc'] > 25)
{
    $naflAlerts[] = ['type' => 'warning', 'message' => 'IMC > 25 : Surveillez votre poids', 'icon' => 'fa-circle-exclamation'];
}
?>

<script src="/js/components/alpine-dashboard.js?v=<?= time(); ?>"></script>
<?php if (!empty($viewData['toasts']))
{ ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        try {
            var toasts = <?= json_encode($viewData['toasts']); ?>;
            if (Array.isArray(toasts)) {
                toasts.forEach(function(t){
                    if (t && t.message) { window.showNotification(t.message, t.type || 'info', 6000); }
                });
            }
        } catch (e) { console.error('Toast render error', e); }
    });
</script>
<?php } ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50"
     x-data="dashboardManager()"
     x-init="init()"
     @keydown.window="handleKeydown($event)">

    <!-- Header avec status -->
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        👋 <?= e($pageTitle); ?>
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Bienvenue 
                        <span class="font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                            <?= e($user['pseudo'] ?? 'Utilisateur'); ?>
                        </span>
                        ! Voici votre bilan du jour ✨
                    </p>
                </div>
                <button @click="refreshDashboard()"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                        :class="{ 'opacity-50 cursor-not-allowed': isLoading }">
                    <i class="fa-solid fa-rotate mr-2" :class="{ 'fa-spin': isLoading }"></i>
                    <span>Actualiser</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Alertes NAFLD importantes -->
        <?php if (!empty($naflAlerts))
        { ?>
        <div class="space-y-3" x-show="isLoaded" x-transition>
            <?php foreach ($naflAlerts as $alert)
            { ?>
            <div class="bg-<?= $alert['type'] === 'danger' ? 'red' : 'orange'; ?>-50 border-l-4 border-<?= $alert['type'] === 'danger' ? 'red' : 'orange'; ?>-500 p-4 rounded-r-xl">
                <div class="flex items-center">
                    <i class="fa-solid <?= $alert['icon']; ?> text-<?= $alert['type'] === 'danger' ? 'red' : 'orange'; ?>-600 text-xl mr-3"></i>
                    <p class="text-<?= $alert['type'] === 'danger' ? 'red' : 'orange'; ?>-800 font-medium"><?= e($alert['message']); ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <!-- Hero Section - Score de Santé Global -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 p-1 shadow-2xl"
             x-show="isLoaded" 
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    
                    <!-- Score Global Circulaire -->
                    <div class="text-center">
                        <div class="relative inline-block"
                             x-data="{
                                radius: 75,
                                score: 0,
                                target: <?= (int)$scoreGlobal; ?>,
                                circumference() { return 2 * Math.PI * this.radius },
                                dashoffset() { return this.circumference() * (1 - this.score / 100) },
                                init() {
                                    let step = () => {
                                        if (this.score < this.target) {
                                            this.score = Math.min(this.score + 1, this.target);
                                            requestAnimationFrame(step);
                                        }
                                    };
                                    requestAnimationFrame(step);
                                }
                             }"
                             x-init="init()">
                            <svg class="w-36 h-36 md:w-44 md:h-44 transform -rotate-90">
                                <circle cx="88" cy="88" r="75" stroke="#e5e7eb" stroke-width="14" fill="none"/>
                                <circle cx="88" cy="88" r="75" 
                                        stroke="url(#scoreGradient)" 
                                        stroke-width="14" 
                                        fill="none"
                                        stroke-linecap="round"
                                        class="transition-all duration-1000 ease-out"
                                        :style="{ strokeDasharray: circumference(), strokeDashoffset: dashoffset() }"/>
                                <defs>
                                    <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:rgb(59, 130, 246);stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:rgb(168, 85, 247);stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:rgb(236, 72, 153);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent" x-text="Math.round(score)">0</span>
                                <span class="text-sm text-slate-500 font-medium">/100</span>
                            </div>
                        </div>
                        <p class="mt-4 text-lg font-bold text-<?= $scoreColorTailwind; ?>-600"><?= $scoreLabel; ?></p>
                        <p class="text-sm text-slate-500">Score de santé du jour</p>
                        
                        <!-- Détail du score (collapse) -->
                        <button @click="showScoreDetails = !showScoreDetails"
                                class="mt-3 text-xs text-blue-600 hover:text-blue-700 font-medium underline">
                            <span x-text="showScoreDetails ? 'Masquer détails' : 'Comment est calculé ?'"></span>
                        </button>
                    </div>

                    <!-- Indicateurs rapides 2x2 -->
                    <div class="md:col-span-2 grid grid-cols-2 gap-3 md:gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 border border-blue-200 transform hover:scale-105 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs md:text-sm text-blue-600 font-medium">IMC Actuel</p>
                                    <p class="text-xl md:text-2xl font-bold text-blue-900"><?= number_format($objectifs['imc'] ?? 0, 1); ?></p>
                                    <p class="text-xs text-blue-700 mt-1">
                                        <?php
                                        $imc = $objectifs['imc'] ?? 0;
if ($imc < 18.5)
{
    echo 'Maigreur';
} elseif ($imc < 25)
{
    echo 'Normal';
} elseif ($imc < 30)
{
    echo 'Surpoids';
} else
{
    echo 'Obésité';
}
?>
                                    </p>
                                </div>
                                <i class="fa-solid fa-scale-balanced text-2xl md:text-3xl text-blue-500"></i>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 border border-green-200 transform hover:scale-105 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs md:text-sm text-green-600 font-medium">Objectif Calories</p>
                                    <p class="text-xl md:text-2xl font-bold text-green-900"><?= round($stats['objectifs_completion'] ?? 0); ?>%</p>
                                    <p class="text-xs text-green-700 mt-1">Aujourd'hui</p>
                                </div>
                                <i class="fa-solid fa-fire text-2xl md:text-3xl text-green-500"></i>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-4 border border-purple-200 transform hover:scale-105 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs md:text-sm text-purple-600 font-medium">Risque NAFLD</p>
                                    <p class="text-sm md:text-lg font-bold text-purple-900">
                                        <?php
if (!$objectifs)
{
    echo 'N/A';
} elseif ($objectifs['imc'] > 30)
{
    echo 'Élevé';
} elseif ($objectifs['imc'] > 25)
{
    echo 'Moyen';
} else
{
    echo 'Faible';
}
?>
                                    </p>
                                    <p class="text-xs text-purple-700 mt-1">Basé sur IMC</p>
                                </div>
                                <i class="fa-solid fa-shield-heart text-2xl md:text-3xl text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails du score (collapse) -->
                <div x-show="showScoreDetails"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-y-0"
                     x-transition:enter-end="opacity-100 transform scale-y-100"
                     class="mt-6 pt-6 border-t border-slate-200 grid grid-cols-2 md:grid-cols-3 gap-3">

                    <!-- IMC -->
                    <div x-data="{
                            val: 0,
                            target: <?= isset($imcScore) ? (int)round($imcScore) : 0; ?>,
                            started: false,
                            animate() {
                                if (this.started) return;
                                this.started = true;
                                const dur = 600; // ms
                                const frames = Math.max(12, Math.round(dur / 16));
                                const step = () => {
                                    this.val = Math.min(this.target, Math.ceil(this.val + (this.target / frames)));
                                    if (this.val < this.target) requestAnimationFrame(step);
                                };
                                requestAnimationFrame(step);
                            }
                         }"
                         x-effect="if(showScoreDetails) animate()"
                         class="text-center p-3 bg-blue-50 rounded-xl">
                        <i class="fa-solid fa-scale-balanced text-blue-600 text-xl mb-2"></i>
                        <p class="text-xs text-slate-600">IMC</p>
                        <p class="text-lg font-bold text-blue-900">
                            <span x-text="val"></span>/25
                        </p>
                    </div>

                    <!-- Calories -->
                    <div x-data="{
                            val: 0,
                            target: <?= isset($caloriesScore) ? (int)round($caloriesScore) : 0; ?>,
                            started: false,
                            animate() {
                                if (this.started) return;
                                this.started = true;
                                const dur = 600;
                                const frames = Math.max(12, Math.round(dur / 16));
                                const step = () => {
                                    this.val = Math.min(this.target, Math.ceil(this.val + (this.target / frames)));
                                    if (this.val < this.target) requestAnimationFrame(step);
                                };
                                requestAnimationFrame(step);
                            }
                         }"
                         x-effect="if(showScoreDetails) animate()"
                         class="text-center p-3 bg-green-50 rounded-xl">
                        <i class="fa-solid fa-fire text-green-600 text-xl mb-2"></i>
                        <p class="text-xs text-slate-600">Calories</p>
                        <p class="text-lg font-bold text-green-900">
                            <span x-text="val"></span>/25
                        </p>
                    </div>

                    <!-- Activité -->
                    <div x-data="{
                            val: 0,
                            target: <?= isset($activityScore) ? (int)round($activityScore) : 0; ?>,
                            started: false,
                            animate() {
                                if (this.started) return;
                                this.started = true;
                                const dur = 600;
                                const frames = Math.max(12, Math.round(dur / 16));
                                const step = () => {
                                    this.val = Math.min(this.target, Math.ceil(this.val + (this.target / frames)));
                                    if (this.val < this.target) requestAnimationFrame(step);
                                };
                                requestAnimationFrame(step);
                            }
                         }"
                         x-effect="if(showScoreDetails) animate()"
                         class="text-center p-3 bg-purple-50 rounded-xl">
                        <i class="fa-solid fa-person-running text-purple-600 text-xl mb-2"></i>
                        <p class="text-xs text-slate-600">Activité</p>
                        <p class="text-lg font-bold text-purple-900">
                            <span x-text="val"></span>/25
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé + Conseils + Actions Rapides -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Résumé & Conseils (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <?php // Ancien composant supprimé pour éviter les redondances?>
                <?php include __DIR__ . '/daily_tips.php'; ?>
                <?php include __DIR__ . '/daily_summary.php'; ?>
            </div>

            <!-- Actions Rapides (1/3) -->
            <div class="space-y-4" x-show="isLoaded" x-transition>
                <h3 class="text-lg font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-bolt text-yellow-500 mr-2"></i>
                    Actions rapides
                </h3>

                <a href="?page=food"
                   class="block group relative overflow-hidden bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fa-solid fa-utensils text-white text-3xl"></i>
                            <i class="fa-solid fa-arrow-right text-white opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all"></i>
                        </div>
                        <h4 class="text-white font-bold text-lg">Ajouter un repas</h4>
                        <p class="text-green-100 text-sm mt-1">Enregistrer ce que vous mangez</p>
                    </div>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </a>

                <a href="?page=activity"
                   class="block group relative overflow-hidden bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fa-solid fa-person-running text-white text-3xl"></i>
                            <i class="fa-solid fa-arrow-right text-white opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all"></i>
                        </div>
                        <h4 class="text-white font-bold text-lg">Activité physique</h4>
                        <p class="text-purple-100 text-sm mt-1">Enregistrer vos exercices</p>
                    </div>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </a>

                <a href="?page=imc"
                   class="block group relative overflow-hidden bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fa-solid fa-scale-balanced text-white text-3xl"></i>
                            <i class="fa-solid fa-arrow-right text-white opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all"></i>
                        </div>
                        <h4 class="text-white font-bold text-lg">Calculer IMC</h4>
                        <p class="text-orange-100 text-sm mt-1">Mettre à jour vos mesures</p>
                    </div>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </a>
            </div>
        </div>

        <!-- Timeline des Activités Récentes -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-xl border border-slate-200"
             x-show="isLoaded" 
             x-transition>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600 mr-3"></i>
                    Activités récentes
                </h3>
                <a href="?page=profile" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Voir tout <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if (empty($recentActivity))
            { ?>
                <!-- Timeline placeholder si aucune activité -->
                <div class="relative pl-8 space-y-6">
                    <!-- Ligne verticale -->
                    <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-300 via-purple-300 to-pink-300"></div>

                    <!-- Item placeholder 1 -->
                    <div class="relative">
                        <div class="absolute left-[-2rem] top-1 w-6 h-6 bg-slate-300 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                            <i class="fa-solid fa-clock text-white text-xs"></i>
                        </div>
                        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200">
                            <p class="text-slate-600 text-sm">Aucune activité enregistrée aujourd'hui</p>
                            <p class="text-xs text-slate-500 mt-2">Commencez par ajouter un repas ou une activité physique</p>
                        </div>
                    </div>
                </div>
            <?php } else
            { ?>
                <!-- Timeline avec vraies données -->
                <div class="relative pl-8 space-y-6">
                    <!-- Ligne verticale -->
                    <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-300 via-purple-300 to-pink-300"></div>

                    <?php foreach ($recentActivity as $activity)
                    { ?>
                    <div class="relative">
                        <div class="absolute left-[-2rem] top-1 w-6 h-6 bg-<?= $activity['color']; ?>-500 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                            <i class="fa-solid <?= $activity['icon']; ?> text-white text-xs"></i>
                        </div>
                        <div class="bg-gradient-to-br from-<?= $activity['color']; ?>-50 to-<?= $activity['color']; ?>-100 rounded-xl p-4 border border-<?= $activity['color']; ?>-200 transform hover:scale-105 transition-all duration-300">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900"><?= e($activity['title']); ?></p>
                                    <p class="text-sm text-slate-600 mt-1"><?= e($activity['description']); ?></p>
                                </div>
                                <span class="text-xs text-slate-500 whitespace-nowrap ml-4"><?= formatRelativeTime($activity['datetime']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <!-- Conseil du jour (existant) -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-6 md:p-8 border-2 border-amber-200 shadow-xl"
             x-show="isLoaded" 
             x-transition>
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-lightbulb text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-amber-900 mb-2">💡 Astuce bien-être</h3>
                    <p class="text-amber-800 leading-relaxed">
                        Privilégiez les aliments riches en fibres (fruits, légumes, céréales complètes) pour améliorer votre santé hépatique. 
                        Objectif : 25-30g de fibres par jour !
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
