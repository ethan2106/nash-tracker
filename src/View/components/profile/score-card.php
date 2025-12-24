<?php
/**
 * Card Score Santé - Score NAFLD et recommandations.
 * @param array $data - Contient userConfig, realScore
 * @param array $currentNutrition - Nutrition actuelle du jour
 * @param array $objectifs - Objectifs de l'utilisateur
 */
?>
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-800">Score Santé</h3>
    </div>
    
    <!-- Score Global -->
    <div class="text-center mb-8">
        <?php $realScore = $data['realScore'] ?? 0; ?>
        <div class="relative w-32 h-32 mx-auto mb-4">
            <svg class="w-32 h-32 transform -rotate-90 score-circle" viewBox="0 0 36 36">
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                      fill="none" stroke="#E5E7EB" stroke-width="2"/>
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                      fill="none" stroke="#10B981" stroke-width="2"
                      stroke-dasharray="0 100"/>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-3xl font-bold text-green-600" x-text="counters.score.current">0</span>
            </div>
        </div>
        <p class="text-gray-600 font-medium">Score global NAFLD</p>
        <p class="text-sm text-gray-500 mt-1">Basé sur vos repas d'aujourd'hui</p>
        
        <!-- Bouton d'explication du score -->
        <button @click="showScoreDetails = !showScoreDetails"
                class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2 mx-auto transition-colors">
            <i class="fa-solid" :class="showScoreDetails ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            <span>Comment est calculé mon score ?</span>
        </button>
    </div>
    
    <!-- Détail du calcul du score (collapse) -->
    <div x-show="showScoreDetails" 
         x-collapse
         class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border border-blue-200">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-info-circle text-blue-600"></i>
            Décomposition du Score NAFLD (100 points max)
        </h4>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-white/70 rounded-lg">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-weight-scale text-blue-600"></i>
                    <span class="text-sm font-medium text-gray-700">IMC dans la zone normale (risque NAFLD)</span>
                </div>
                <span class="text-sm font-bold text-blue-600">40 points max</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-white/70 rounded-lg">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-purple-600"></i>
                    <span class="text-sm font-medium text-gray-700">Âge favorable (risque NAFLD augmente avec l'âge)</span>
                </div>
                <span class="text-sm font-bold text-purple-600">15 points max</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-white/70 rounded-lg">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-person-running text-green-600"></i>
                    <span class="text-sm font-medium text-gray-700">Activité physique régulière (protecteur NAFLD)</span>
                </div>
                <span class="text-sm font-bold text-green-600">25 points max</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-white/70 rounded-lg">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-orange-600"></i>
                    <span class="text-sm font-medium text-gray-700">Alimentation équilibrée (objectifs respectés)</span>
                </div>
                <span class="text-sm font-bold text-orange-600">20 points max</span>
            </div>
        </div>
        <p class="text-xs text-gray-600 mt-3 italic">
            Le score NAFLD est recalculé quotidiennement selon les critères médicaux de risque de stéatose hépatique.
        </p>
    </div>
    
    <!-- Recommandations -->
    <div class="space-y-3">
        <?php if ($currentNutrition['calories'] < $objectifs['calories_perte'] * 0.8)
        { ?>
        <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-exclamation-triangle text-yellow-600"></i>
                <span class="text-sm text-yellow-800 font-medium">Calories insuffisantes aujourd'hui</span>
            </div>
        </div>
        <?php } else
        { ?>
        <div class="p-3 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle text-green-600"></i>
                <span class="text-sm text-green-800 font-medium">Objectif calories atteint</span>
            </div>
        </div>
        <?php } ?>
        
        <?php if ($currentNutrition['proteines'] < $objectifs['proteines_min'])
        { ?>
        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                <span class="text-sm text-blue-800 font-medium">Augmentez les protéines (viande, poisson, œufs, légumineuses)</span>
            </div>
        </div>
        <?php } else
        { ?>
        <div class="p-3 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle text-green-600"></i>
                <span class="text-sm text-green-800 font-medium">Apport protéique correct</span>
            </div>
        </div>
        <?php } ?>
        
        <?php if ($currentNutrition['fibres'] < $objectifs['fibres_min'])
        { ?>
        <div class="p-3 bg-orange-50 rounded-lg border border-orange-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-orange-600"></i>
                <span class="text-sm text-orange-800 font-medium">Mangez plus de fibres (légumes, fruits, céréales complètes)</span>
            </div>
        </div>
        <?php } else
        { ?>
        <div class="p-3 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle text-green-600"></i>
                <span class="text-sm text-green-800 font-medium">Apport en fibres correct</span>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
