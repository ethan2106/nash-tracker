<?php
// Utilise les données préparées par le controller
$dashboard = $viewData['dashboard'] ?? [];
$objectifs = $dashboard['objectifs'] ?? null;
$stats = $dashboard['stats'] ?? [];

if ($objectifs)
{
    ?>
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4" x-show="isLoaded" x-transition>
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-100 transform transition-all duration-300 hover:scale-105 hover:shadow-lg"
         @mouseenter="cardHover($el, true)"
         @mouseleave="cardHover($el, false)">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-50 rounded-md p-3">
                    <i class="fa-solid fa-scale-balanced text-blue-600 text-xl transition-transform duration-200 hover:scale-110"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">IMC Actuel</dt>
                        <dd>
                            <div class="text-lg font-bold text-slate-900 transition-all duration-300"
                                 x-init="animateCounter($el, <?= $objectifs['imc']; ?>, 800)"
                                 data-unit="">0</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-100 transform transition-all duration-300 hover:scale-105 hover:shadow-lg"
         @mouseenter="cardHover($el, true)"
         @mouseleave="cardHover($el, false)">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-50 rounded-md p-3">
                    <i class="fa-solid fa-fire text-green-600 text-xl transition-transform duration-200 hover:scale-110"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Objectif Calories</dt>
                        <dd>
                            <div class="text-lg font-bold text-slate-900 transition-all duration-300"
                                 x-init="animateCounter($el, <?= $objectifs['calories_perte']; ?>, 800)"
                                 data-unit="kcal">0</div>
                            <span class="text-xs text-slate-400 font-normal">kcal</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-100 transform transition-all duration-300 hover:scale-105 hover:shadow-lg"
         @mouseenter="cardHover($el, true)"
         @mouseleave="cardHover($el, false)">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-50 rounded-md p-3">
                    <i class="fa-solid fa-chart-pie text-purple-600 text-xl transition-transform duration-200 hover:scale-110"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Objectifs atteints</dt>
                        <dd>
                            <div class="flex items-baseline">
                                <div class="text-lg font-bold text-slate-900 transition-all duration-300"
                                     x-init="animateCounter($el, <?= $stats['objectifs_completion'] ?? 0; ?>, 800)"
                                     data-unit="%">0</div>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
