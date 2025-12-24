<?php
// Utilise les données préparées par le controller
$dashboard = $viewData['dashboard'] ?? [];
$objectifs = $dashboard['objectifs'] ?? null;
$dailyGoals = $dashboard['dailyGoals'] ?? [];

if ($objectifs)
{
    ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6" x-show="isLoaded" x-transition>
    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
        <i class="fa-solid fa-bullseye text-red-500 mr-2"></i> Progression du jour
    </h2>
    <div class="space-y-6">
        <?php foreach ($dailyGoals as $index => $goal)
        { ?>
        <div x-show="isLoaded" x-transition:enter.delay.<?= $index * 100; ?>ms>
            <div class="flex justify-between items-end mb-1">
                <span class="text-sm font-medium text-slate-700 flex items-center gap-2">
                    <i class="fa-solid <?= $goal['icon']; ?> text-<?= $goal['color']; ?>-500 w-5 transition-transform duration-200 hover:scale-110"></i>
                    <?= $goal['label']; ?>
                </span>
                <span class="text-xs text-slate-500">
                    <span class="font-bold text-slate-700 transition-all duration-300"
                          x-init="animateCounter($el, <?= is_numeric($goal['current']) ? (float)$goal['current'] : 0; ?>, 1000)"
                          data-unit="<?= $goal['unit']; ?>">0</span>
                    / <?= is_numeric($goal['total']) ? number_format((float)$goal['total'], $goal['unit'] === 'kcal' ? 0 : 1, ',', ' ') : $goal['total']; ?> <?= $goal['unit']; ?>
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-<?= $goal['color']; ?>-500 h-2.5 rounded-full transition-all duration-1000 ease-out delay-<?= $index * 200; ?>ms"
                     data-progress-bar="<?= $goal['progress']; ?>"
                     style="width: 0%;"></div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>
