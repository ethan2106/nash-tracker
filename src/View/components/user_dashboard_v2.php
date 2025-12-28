<?php
/**
 * Dashboard v2 - Modern data-driven overview.
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

$scoreLabel = getScoreLabel($scoreGlobal);
$scoreColorTailwind = getScoreTailwindColor($scoreGlobal);

$imc = (float)($objectifs['imc'] ?? 0);
$caloriesConsumed = (float)($stats['calories_consumed'] ?? 0);
$caloriesTarget = (float)($stats['calories_target'] ?? ($objectifs['calories_perte'] ?? 0));
$caloriesPct = $caloriesTarget > 0 ? min(100, ($caloriesConsumed / $caloriesTarget) * 100) : 0;

$activityMinutes = (int)($stats['activity_minutes_today'] ?? 0);
$activityGoal = (int)($userConfig['activite_objectif_minutes'] ?? 0);
$activityPct = $activityGoal > 0 ? min(100, ($activityMinutes / $activityGoal) * 100) : 0;

$completion = (int)($stats['objectifs_completion'] ?? 0);
$completion = max(0, min(100, $completion));

$proteinesConsumed = (float)($stats['proteines_consumed'] ?? 0);
$glucidesConsumed = (float)($stats['glucides_consumed'] ?? 0);
$lipidesConsumed = (float)($stats['lipides_consumed'] ?? 0);

$colorWhitelist = ['blue', 'green', 'purple', 'orange', 'red', 'yellow', 'slate'];
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

<div class="min-h-screen bg-[radial-gradient(circle_at_top,#e0f2fe,transparent_55%),radial-gradient(circle_at_20%_20%,#fef3c7,transparent_40%),radial-gradient(circle_at_90%_10%,#ede9fe,transparent_45%),linear-gradient(180deg,#f8fafc,white)]"
     x-data="dashboardManager()"
     x-init="init()"
     @keydown.window="handleKeydown($event)">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white/80 backdrop-blur-xl rounded-3xl p-6 border border-white/60 shadow-xl">
            <div>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-fuchsia-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                            <?= e($pageTitle); ?>
                        </h1>
                        <p class="text-sm text-slate-500">
                            <?= e($pageSubtitle); ?>
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-fuchsia-600 px-4 py-1 text-xs font-semibold text-white shadow-md">
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                        Score du jour: <?= $scoreGlobal; ?>/100
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                        Objectifs: <?= $completion; ?>%
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="refreshDashboard()"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300"
                        :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                        :disabled="isLoading"
                        aria-busy="isLoading ? 'true' : 'false'">
                    <i class="fa-solid fa-rotate mr-2" :class="{ 'fa-spin': isLoading }" aria-hidden="true"></i>
                    <span>Rafraichir</span>
                </button>
                <a href="?page=food"
                   class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                    <i class="fa-solid fa-utensils mr-2" aria-hidden="true"></i>
                    Ajouter repas
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2 bg-white/90 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-slate-200 relative overflow-hidden">
                <div class="absolute inset-x-0 -top-24 h-32 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 blur-2xl"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Score global</p>
                        <p class="text-4xl font-bold text-slate-900 mt-2"><?= $scoreGlobal; ?>/100</p>
                        <p class="text-sm font-semibold text-<?= $scoreColorTailwind; ?>-600 mt-1"><?= $scoreLabel; ?></p>
                    </div>
                    <div class="h-20 w-20 rounded-full border-8 border-slate-100 bg-white shadow-inner flex items-center justify-center text-lg font-bold text-slate-900">
                        <?= $scoreGlobal; ?>
                    </div>
                </div>
                <div class="mt-6 space-y-3">
                    <?php foreach ($scoreComponents as $label => $value)
                    {
                        $labelLower = strtolower((string)$label);
                        $max = 100;
                        if (stripos($labelLower, 'imc') !== false)
                        {
                            $max = 40;
                        } elseif (stripos($labelLower, 'activ') !== false)
                        {
                            $max = 25;
                        } elseif (stripos($labelLower, 'nutri') !== false)
                        {
                            $max = 20;
                        } elseif (stripos($labelLower, 'calor') !== false)
                        {
                            $max = 25;
                        } elseif (stripos($labelLower, 'age') !== false || stripos($labelLower, 'ge') !== false)
                        {
                            $max = 15;
                        }
                        $pct = $max > 0 ? min(100, ($value / $max) * 100) : 0;
                        ?>
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span><?= e($label); ?></span>
                            <span><?= (int)$value; ?>/<?= (int)$max; ?></span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600" style="width: <?= round($pct); ?>%"></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <div class="bg-white/90 rounded-3xl p-6 shadow-xl border border-slate-200 bg-gradient-to-br from-emerald-50/70 to-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Calories</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?= round($caloriesConsumed); ?> kcal</p>
                <p class="text-xs text-slate-500 mt-1">Cible: <?= round($caloriesTarget); ?> kcal</p>
                <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-green-600" style="width: <?= round($caloriesPct); ?>%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-600"><?= round($caloriesPct); ?>% de l'objectif</p>
            </div>

            <div class="bg-white/90 rounded-3xl p-6 shadow-xl border border-slate-200 bg-gradient-to-br from-purple-50/70 to-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Activite</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?= $activityMinutes; ?> min</p>
                <p class="text-xs text-slate-500 mt-1">Objectif: <?= $activityGoal; ?> min</p>
                <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-fuchsia-600" style="width: <?= round($activityPct); ?>%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-600"><?= round($activityPct); ?>% de l'objectif</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-slate-900">Synthese nutrition</h2>
                        <span class="text-sm text-slate-500">Completion: <?= $completion; ?>%</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl border border-slate-200 p-4 bg-gradient-to-br from-purple-50 to-white">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Proteines</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?= round($proteinesConsumed); ?> g</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4 bg-gradient-to-br from-amber-50 to-white">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Glucides</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?= round($glucidesConsumed); ?> g</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4 bg-gradient-to-br from-teal-50 to-white">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lipides</p>
                            <p class="text-2xl font-bold text-slate-900 mt-2"><?= round($lipidesConsumed); ?> g</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900 mb-6">Objectifs du jour</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($dailyGoals as $goal)
                        {
                            $color = in_array($goal['color'], $colorWhitelist, true) ? $goal['color'] : 'slate';
                            ?>
                        <div class="rounded-2xl border border-slate-200 p-4 bg-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-8 w-8 rounded-lg bg-<?= $color; ?>-100 text-<?= $color; ?>-600 flex items-center justify-center shadow-sm">
                                        <i class="fa-solid <?= e($goal['icon']); ?>" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?= e($goal['label']); ?></p>
                                        <p class="text-xs text-slate-500"><?= e($goal['target']); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-900"><?= round($goal['current']); ?> <?= e($goal['unit']); ?></p>
                                    <p class="text-xs text-slate-500"><?= round($goal['progress']); ?>%</p>
                                </div>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-<?= $color; ?>-500" style="width: <?= round($goal['progress']); ?>%"></div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200 bg-gradient-to-br from-blue-50/70 to-white">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Profil sante</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">IMC actuel</span>
                            <span class="font-semibold text-slate-900"><?= number_format($imc, 1); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Objectifs completes</span>
                            <span class="font-semibold text-slate-900"><?= $completion; ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Activites recentes</h2>
                        <a href="?page=profile" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">Voir tout</a>
                    </div>
                    <?php if (empty($recentActivity))
                    { ?>
                        <p class="text-sm text-slate-500">Aucune activite enregistree aujourd'hui.</p>
                    <?php } else
                    { ?>
                        <div class="space-y-3">
                            <?php foreach ($recentActivity as $activity)
                            {
                                $color = in_array($activity['color'], $colorWhitelist, true) ? $activity['color'] : 'slate';
                                ?>
                            <div class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 bg-slate-50">
                                <span class="h-9 w-9 rounded-xl bg-<?= $color; ?>-100 text-<?= $color; ?>-600 flex items-center justify-center shadow-sm">
                                    <i class="fa-solid <?= e($activity['icon']); ?>" aria-hidden="true"></i>
                                </span>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-900"><?= e($activity['title']); ?></p>
                                    <p class="text-xs text-slate-500"><?= e($activity['description']); ?></p>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap"><?= formatRelativeTime($activity['datetime']); ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
