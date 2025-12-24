<?php

/**
 * Composant: Onglet Profil Santé.
 *
 * @description Historique objectifs + graphique évolution poids/IMC
 * @requires Alpine.js - x-data="chartLoader"
 *
 * @var array $historiqueObjectifs Liste des objectifs utilisateur
 * @var array $historiqueMesures Mesures poids/IMC paginées
 * @var array $mesuresPagination Infos pagination (currentPage, totalPages, total)
 * @var callable $e Fonction d'échappement HTML
 */

declare(strict_types=1);
?>
<!-- ============================================================
     ONGLET PROFIL SANTÉ
     - Historique des objectifs nutritionnels
     - Graphique évolution poids/IMC avec lazy loading
     - Pagination
     ============================================================ -->
<div x-show="activeTab === 'profil-sante'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-4"
     x-transition:enter-end="opacity-100 transform translate-x-0">
    
    <div class="space-y-6">
        
        <!-- ===== TITRE SECTION OBJECTIFS ===== -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Historique de vos objectifs</h2>
            <p class="text-gray-600">Consultez l'évolution de vos objectifs nutritionnels dans le temps</p>
        </div>

        <!-- ===== LISTE DES OBJECTIFS ===== -->
        <?php if (!empty($historiqueObjectifs))
        { ?>
            <div class="space-y-4">
                <?php foreach ($historiqueObjectifs as $index => $objectif)
                { ?>
                <div class="bg-white rounded-2xl p-6 border <?= $objectif['actif'] ? 'border-green-300 ring-2 ring-green-200' : 'border-gray-200'; ?> relative">
                    <?php if ($objectif['actif'])
                    { ?>
                        <div class="absolute -top-3 -right-3 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                            ACTIF
                        </div>
                    <?php } ?>
                    
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">
                                Objectif <?= $index + 1; ?>
                            </h3>
                            <p class="text-sm text-gray-600">
                                Du <?= date('d/m/Y', strtotime($objectif['date_debut'] ?? 'now')); ?>
                                <?php if ($objectif['date_fin'] ?? null)
                                { ?>
                                    au <?= date('d/m/Y', strtotime($objectif['date_fin'] ?? 'now')); ?>
                                <?php } else
                                { ?>
                                    <span class="text-green-600 font-semibold">• En cours</span>
                                <?php } ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Créé le</div>
                            <div class="font-semibold text-gray-700"><?= date('d/m/Y', strtotime($objectif['created_at'] ?? 'now')); ?></div>
                        </div>
                    </div>
                    
                    <!-- Stats objectif -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <div class="text-2xl font-bold text-blue-600"><?= number_format($objectif['calories_perte'], 0); ?></div>
                            <div class="text-xs text-gray-600">kcal/jour</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <div class="text-2xl font-bold text-green-600"><?= number_format($objectif['proteines_min'], 1); ?>g</div>
                            <div class="text-xs text-gray-600">Protéines min</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-xl">
                            <div class="text-2xl font-bold text-purple-600"><?= number_format($objectif['fibres_min'], 1); ?>g</div>
                            <div class="text-xs text-gray-600">Fibres min</div>
                        </div>
                        <div class="text-center p-3 bg-orange-50 rounded-xl">
                            <div class="text-2xl font-bold text-orange-600"><?= number_format($objectif['imc'], 1); ?></div>
                            <div class="text-xs text-gray-600">IMC</div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } else
        { ?>
            <!-- État vide : aucun objectif -->
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-chart-line text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Aucun objectif défini</h3>
                <p class="text-gray-600 mb-6">Commencez par calculer votre IMC et définir vos objectifs</p>
                <a href="?page=imc" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 
                          text-white font-semibold rounded-xl shadow-lg 
                          hover:shadow-xl hover:scale-105 transition-all">
                    <i class="fa-solid fa-calculator"></i>
                    Calculer mon IMC
                </a>
            </div>
        <?php } ?>
    </div>

    <!-- ===== SECTION GRAPHIQUE ÉVOLUTION POIDS/IMC ===== -->
    <div class="mt-8" 
         x-data="chartLoader"
         x-init="initLazyLoading()">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Évolution de votre poids et IMC</h2>
            <p class="text-gray-600">Suivez vos progrès dans le temps avec des graphiques interactifs</p>
        </div>

        <?php if (!empty($historiqueMesures))
        { ?>
            <div class="bg-white rounded-2xl p-6 border border-green-200 shadow-lg">
                <!-- Loading State -->
                <div x-show="!chartLoaded" class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                        <p class="text-gray-600">Chargement du graphique...</p>
                    </div>
                </div>
                
                <!-- Chart Container -->
                <div x-show="chartLoaded" x-transition>
                    <canvas id="historiqueChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="mt-4 text-center text-sm text-gray-500">
                Données sur les <?= count($historiqueMesures); ?> mesures affichées (total: <?= $mesuresPagination['total'] ?? 0; ?>)
            </div>

            <!-- ===== PAGINATION ===== -->
            <?php if (($mesuresPagination['totalPages'] ?? 0) > 1)
            { ?>
                <div class="mt-6 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <?php if ($mesuresPagination['currentPage'] > 1)
                        { ?>
                            <a href="?page=settings&page_mesures=<?= $mesuresPagination['currentPage'] - 1; ?>"
                               class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Précédent
                            </a>
                        <?php } ?>

                        <?php for ($i = max(1, $mesuresPagination['currentPage'] - 2); $i <= min($mesuresPagination['totalPages'], $mesuresPagination['currentPage'] + 2); $i++)
                        { ?>
                            <a href="?page=settings&page_mesures=<?= $i; ?>"
                               class="px-3 py-2 text-sm font-medium <?= $i === $mesuresPagination['currentPage'] ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-500 bg-white border-gray-300'; ?> border rounded-md hover:bg-gray-50">
                                <?= $i; ?>
                            </a>
                        <?php } ?>

                        <?php if ($mesuresPagination['currentPage'] < $mesuresPagination['totalPages'])
                        { ?>
                            <a href="?page=settings&page_mesures=<?= $mesuresPagination['currentPage'] + 1; ?>"
                               class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Suivant
                            </a>
                        <?php } ?>
                    </nav>
                </div>
            <?php } ?>
        <?php } else
        { ?>
            <!-- État vide : aucune mesure -->
            <div class="text-center py-12 bg-white rounded-2xl border border-gray-200">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-weight-scale text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune mesure historique</h3>
                <p class="text-gray-600 mb-6">Sauvegardez vos calculs IMC pour voir l'évolution</p>
                <a href="?page=imc" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 
                          text-white font-semibold rounded-xl shadow-lg 
                          hover:shadow-xl hover:scale-105 transition-all">
                    <i class="fa-solid fa-calculator"></i>
                    Calculer et sauvegarder
                </a>
            </div>
        <?php } ?>
    </div>
</div>
