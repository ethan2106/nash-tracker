<?php

/**
 * Composant: Header Page Activités.
 *
 * @description Titre principal + Tooltip explicatif du système MET
 * @note Le tooltip utilise CSS pure (group-hover) pour l'affichage
 */

declare(strict_types=1);
?>
<!-- ============================================================
     HEADER PAGE ACTIVITÉS
     - Titre + icône
     - Tooltip informatif sur le système MET (hover)
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex items-center gap-4 mb-2">
        <div class="text-green-500 text-4xl">
            <i class="fa-solid fa-person-running" aria-hidden="true"></i>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Activités Physiques</h1>
            <p class="text-gray-600">Suivez vos efforts et brûlez des calories !</p>
            <p class="text-sm text-green-600 mt-1">
                <i class="fa-solid fa-fire mr-1" aria-hidden="true"></i>Calculs basés sur le système MET médical
            </p>
        </div>
    </div>

    <!-- Tooltip informatif sur les nouvelles valeurs MET -->
    <div class="inline-block mt-4">
        <div class="relative group">
            <button class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 hover:bg-blue-100 transition-colors">
                <span class="text-sm">ℹ️</span>
                <span class="text-sm font-medium">Calculs mis à jour (MET)</span>
            </button>
            <div class="absolute z-10 invisible group-hover:visible bg-gray-900 text-white text-xs rounded-lg py-3 px-4 mt-2 w-96 shadow-lg">
                <div class="font-semibold mb-2 text-blue-300">🧮 Comment calculons-nous vos calories ?</div>
                <p class="mb-3 leading-relaxed">Nous utilisons le système MET (Metabolic Equivalent of Task), une méthode scientifique reconnue qui mesure l'intensité des activités physiques.</p>

                <div class="mb-3">
                    <div class="font-medium text-green-300 mb-1">✨ Avantages du système MET :</div>
                    <ul class="text-gray-200 space-y-1 ml-2">
                        <li>• <strong>Précision médicale</strong> : Basé sur des études scientifiques</li>
                        <li>• <strong>Personnalisé</strong> : Adapte au poids réel (depuis IMC)</li>
                        <li>• <strong>Réaliste</strong> : Évite les surestimations</li>
                    </ul>
                </div>

                <div class="border-t border-gray-600 pt-2">
                    <div class="font-medium text-yellow-300 mb-1">📊 Exemples de valeurs MET :</div>
                    <div class="text-gray-300 text-xs space-y-1">
                        <div>• Repos : 1 MET (1 kcal/kg/h)</div>
                        <div>• Marche lente : 3 MET</div>
                        <div>• Course modérée : 7 MET</div>
                        <div>• Natation : 6 MET</div>
                    </div>
                </div>

                <div class="mt-3 p-2 bg-blue-900 rounded text-xs">
                    <strong>💡 Conseil :</strong> Plus votre poids est précis dans la section IMC, plus les calculs sont exacts !
                </div>

                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
            </div>
        </div>
    </div>
</div>
