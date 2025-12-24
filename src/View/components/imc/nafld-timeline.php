<?php

/**
 * Composant: Timeline parcours NAFLD.
 *
 * @description 3 étapes du parcours santé hépatique avec indicateurs visuels
 *
 * @var array $data Données utilisateur contenant les alertes
 * @var callable $escape Fonction d'échappement HTML
 */

declare(strict_types=1);
?>
<!-- ============================================================
     SECTION NAFLD TIMELINE
     - Timeline verticale avec 3 étapes (Objectifs, Repas, Activité)
     - Indicateurs visuels (complété, en cours, à venir)
     - Alertes conditionnelles
     ============================================================ -->
<div class="bg-gradient-to-r from-yellow-50/80 via-orange-50/80 to-red-50/80 backdrop-blur-xl rounded-3xl shadow-xl border border-yellow-200 p-8 mt-8">
    
    <!-- ===== EN-TÊTE NAFLD ===== -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
            <i class="fa-solid fa-route text-white text-xl"></i>
        </div>
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Parcours NAFLD</h3>
            <p class="text-sm text-gray-600">Suivez vos étapes vers une meilleure santé hépatique</p>
        </div>
    </div>
    
    <!-- ===== TIMELINE DES 3 ÉTAPES ===== -->
    <div class="relative">
        <!-- Ligne de connexion verticale -->
        <div class="absolute left-6 top-8 bottom-8 w-1 bg-gradient-to-b from-green-300 via-yellow-300 to-orange-300 hidden md:block"></div>
        
        <div class="space-y-6">
            
            <!-- ===== ÉTAPE 1 : OBJECTIFS DÉFINIS ✓ ===== -->
            <div class="relative flex items-start gap-4 group">
                <div class="relative z-10 flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-check text-white text-xl"></i>
                    </div>
                </div>
                <div class="flex-1 bg-white/70 backdrop-blur-sm rounded-2xl p-5 shadow-md border border-green-200 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span>Étape 1 : Objectifs nutritionnels définis</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Complété</span>
                        </h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Vos besoins caloriques et macros NAFLD sont calculés et personnalisés.</p>
                    <div class="flex items-center gap-2 text-green-600 text-sm font-medium">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>Prochaine action : Commencez à suivre vos repas quotidiens</span>
                    </div>
                </div>
            </div>
            
            <!-- ===== ÉTAPE 2 : SUIVRE REPAS ⏳ ===== -->
            <div class="relative flex items-start gap-4 group">
                <div class="relative z-10 flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform animate-pulse">
                        <i class="fa-solid fa-hourglass-half text-white text-xl"></i>
                    </div>
                </div>
                <div class="flex-1 bg-white/70 backdrop-blur-sm rounded-2xl p-5 shadow-md border border-yellow-200 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span>Étape 2 : Suivre vos repas quotidiens</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">En cours</span>
                        </h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Enregistrez vos repas pour surveiller calories, sucres et graisses saturées.</p>
                    <a href="?page=food" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-semibold rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all text-sm">
                        <i class="fa-solid fa-utensils"></i>
                        Ajouter un repas maintenant
                    </a>
                </div>
            </div>
            
            <!-- ===== ÉTAPE 3 : ACTIVITÉ PHYSIQUE ◯ ===== -->
            <div class="relative flex items-start gap-4 group">
                <div class="relative z-10 flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="flex-1 bg-white/50 backdrop-blur-sm rounded-2xl p-5 shadow-md border border-gray-200 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span>Étape 3 : Activité physique régulière</span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">À venir</span>
                        </h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Objectif : 600 kcal/jour d'activité physique (marche rapide, natation, vélo).</p>
                    <a href="?page=activity" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-400 to-blue-600 text-white font-semibold rounded-xl shadow hover:shadow-lg hover:scale-105 transition-all text-sm">
                        <i class="fa-solid fa-person-running"></i>
                        Voir mes activités
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ===== ALERTES CONDITIONNELLES ===== -->
    <?php if (!empty($data['alertes']))
    { ?>
        <div class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-xl">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-exclamation-triangle text-orange-500 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <h5 class="font-semibold text-orange-800 mb-2">Points d'attention :</h5>
                    <ul class="space-y-1">
                        <?php foreach ($data['alertes'] as $alerte)
                        { ?>
                            <li class="text-orange-700 text-sm">• <?= $escape($alerte); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
