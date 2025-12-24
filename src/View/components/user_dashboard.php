<?php
/**
 * Component: Section dashboard pour utilisateurs connectés avec Alpine.js.
 */

// Utilise les données préparées par le controller
$pageTitle = $viewData['pageTitle'];
$pageSubtitle = $viewData['pageSubtitle'];
$user = $viewData['user'];
$dashboard = $viewData['dashboard'] ?? [];
?>

<script src="/js/components/alpine-dashboard.js?v=<?= time(); ?>"></script>

<div class="min-h-screen bg-slate-50 pb-20"
     x-data="dashboardManager()"
     @keydown.window="handleKeydown($event)">

    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900" x-text="isLoaded ? '<?= e($pageTitle); ?>' : 'Chargement...'" x-transition></h1>
                    <p class="text-sm text-slate-500 mt-1" x-text="isLoaded ? '<?= e($pageSubtitle); ?>' : ''" x-transition></p>
                </div>
                <div class="hidden sm:block text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span x-text="isLoaded ? 'En ligne' : 'Connexion...'" x-transition></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <?php include __DIR__ . '/dashboard_cards.php'; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                <?php include __DIR__ . '/daily_goals.php'; ?>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="?page=imc"
                       class="group relative bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:border-green-300 hover:shadow-md transition-all duration-300 transform hover:scale-105"
                       @mouseenter="cardHover($el, true)"
                       @mouseleave="cardHover($el, false)"
                       @click="animateIcon($el.querySelector('i'))">
                        <div class="absolute top-0 right-0 -mr-1 -mt-1 w-20 h-20 bg-green-50 rounded-bl-full opacity-50 transition-opacity group-hover:opacity-100"></div>
                        <i class="fa-solid fa-weight-scale text-3xl text-green-500 mb-3 block transition-transform duration-200"></i>
                        <h3 class="font-bold text-slate-800 group-hover:text-green-600 transition-colors">Poids & IMC</h3>
                        <p class="text-xs text-slate-500 mt-1">Mettre à jour mes mesures</p>
                    </a>

                    <a href="?page=food"
                       class="group relative bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:border-blue-300 hover:shadow-md transition-all duration-300 transform hover:scale-105"
                       @mouseenter="cardHover($el, true)"
                       @mouseleave="cardHover($el, false)"
                       @click="animateIcon($el.querySelector('i'))">
                        <div class="absolute top-0 right-0 -mr-1 -mt-1 w-20 h-20 bg-blue-50 rounded-bl-full opacity-50 transition-opacity group-hover:opacity-100"></div>
                        <i class="fa-solid fa-utensils text-3xl text-blue-500 mb-3 block transition-transform duration-200"></i>
                        <h3 class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Repas</h3>
                        <p class="text-xs text-slate-500 mt-1">Ajouter un aliment</p>
                    </a>

                    <a href="?page=profile"
                       class="group relative bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:border-purple-300 hover:shadow-md transition-all duration-300 transform hover:scale-105"
                       @mouseenter="cardHover($el, true)"
                       @mouseleave="cardHover($el, false)"
                       @click="animateIcon($el.querySelector('i'))">
                        <div class="absolute top-0 right-0 -mr-1 -mt-1 w-20 h-20 bg-purple-50 rounded-bl-full opacity-50 transition-opacity group-hover:opacity-100"></div>
                        <i class="fa-solid fa-user-gear text-3xl text-purple-500 mb-3 block transition-transform duration-200"></i>
                        <h3 class="font-bold text-slate-800 group-hover:text-purple-600 transition-colors">Profil</h3>
                        <p class="text-xs text-slate-500 mt-1">Gérer mes objectifs</p>
                    </a>
                </div>

            </div>

            <div class="space-y-8">

                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 border border-amber-100 transform transition-all duration-300 hover:scale-102 hover:shadow-lg">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-lightbulb text-amber-500 text-xl mt-1 transition-transform duration-300 hover:rotate-12"></i>
                        <div>
                            <h3 class="font-bold text-amber-900 text-sm uppercase tracking-wide mb-1">Conseil du jour</h3>
                            <p class="text-amber-800 text-sm italic leading-relaxed">
                                "La régularité bat l'intensité. Mieux vaut un petit effort chaque jour qu'un gros effort une fois par mois."
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bouton de rafraîchissement -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                    <button @click="refreshData()"
                            class="w-full flex items-center justify-center gap-2 py-2 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-lg transition-colors duration-200"
                            :class="{ 'opacity-50 cursor-not-allowed': !isLoaded }"
                            :disabled="!isLoaded">
                        <i class="fa-solid fa-rotate-right text-slate-500 transition-transform duration-300" data-refresh-icon></i>
                        <span class="text-sm font-medium">Actualiser les données</span>
                    </button>
                    <p class="text-xs text-slate-500 text-center mt-2">Raccourci: Ctrl+R</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Tooltip global -->
    <div x-show="activeTooltip"
         x-transition
         class="fixed z-50 bg-slate-900 text-white text-sm px-3 py-2 rounded-lg shadow-lg pointer-events-none max-w-xs"
         :style="`left: ${activeTooltip?.x || 0}px; top: ${activeTooltip?.y || 0}px`"
         x-text="activeTooltip?.content || ''">
    </div>
</div>
