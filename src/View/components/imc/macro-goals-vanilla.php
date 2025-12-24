<?php

/**
 * Composant: Objectifs macros santé NAFLD.
 *
 * @description 6 cartes d'objectifs nutritionnels avec champs cachés pour soumission
 * @requires imc-calculator.js (Vanilla JS)
 *
 * @var array $data Données utilisateur (calories_perte, sucres_max, etc.)
 * @var callable $escape Fonction d'échappement HTML
 */

declare(strict_types=1);

// Calculs par défaut pour affichage initial
$caloriesPerte = $data['calories_perte'] ?? 0;
$sucresMax = $data['sucres_max'] ?? 0;
$graissesSatMax = $data['graisses_sat_max'] ?? 0;
$proteinesMin = $data['proteines_min'] ?? 0;
$proteinesMax = $data['proteines_max'] ?? 0;
$fibresMin = $data['fibres_min'] ?? 0;
$fibresMax = $data['fibres_max'] ?? 0;
$proteinesRange = $proteinesMin . '-' . $proteinesMax;
$fibresRange = $fibresMin . '-' . $fibresMax;
?>
<!-- ============================================================
     SECTION OBJECTIFS MACROS NAFLD
     - Champs cachés pour soumission formulaire
     - 6 cartes : Calories, Sucres, Graisses, Protéines, Fibres, Activité
     - Bouton enregistrement
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-blue-100 p-8 flex flex-col gap-6 mt-16">
    <div class="w-full bg-gradient-to-r from-blue-50 via-green-50 to-yellow-50 rounded-2xl shadow-inner p-6">
        
        <!-- ===== EN-TÊTE ===== -->
        <div class="flex flex-col items-center mb-6">
            <i class="fa-solid fa-utensils text-blue-400 text-5xl mb-2 drop-shadow"></i>
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Objectifs nutritionnels quotidiens</h2>
            <p class="text-gray-500 text-center">Adaptés au suivi NAFLD</p>
        </div>
        
        <!-- ===== GRILLE DES CARTES MACROS ===== -->
        <div class="flex md:grid grid-cols-1 md:grid-cols-3 gap-6 py-2 justify-center overflow-x-auto md:overflow-visible flex-nowrap scrollbar-thin scrollbar-thumb-blue-200 scrollbar-track-blue-50" style="scroll-snap-type:x mandatory; scroll-behavior:smooth;">
            
            <!-- ===== CHAMPS CACHÉS POUR ENVOI BDD ===== -->
            <input type="hidden" name="taille" id="hidden-taille" value="<?= $escape($data['taille']); ?>">
            <input type="hidden" name="poids" id="hidden-poids" value="<?= $escape($data['poids']); ?>">
            <input type="hidden" name="annee" id="hidden-annee" value="<?= $escape($data['annee']); ?>">
            <input type="hidden" name="sexe" id="hidden-sexe" value="<?= $escape($data['sexe']); ?>">
            <input type="hidden" name="activite" id="hidden-activite" value="<?= $escape($data['activite']); ?>">
            <input type="hidden" name="objectif" id="hidden-objectif" value="<?= $escape($data['objectif']); ?>">
            <input type="hidden" name="calories_perte" id="hidden-calories-perte" value="<?= $caloriesPerte; ?>">
            <input type="hidden" name="sucres_max" id="hidden-sucres-max" value="<?= $sucresMax; ?>">
            <input type="hidden" name="graisses_sat_max" id="hidden-graisses-sat-max" value="<?= $graissesSatMax; ?>">
            <input type="hidden" name="proteines_min" id="hidden-proteines-min" value="<?= $proteinesMin; ?>">
            <input type="hidden" name="proteines_max" id="hidden-proteines-max" value="<?= $proteinesMax; ?>">
            <input type="hidden" name="fibres_min" id="hidden-fibres-min" value="<?= $fibresMin; ?>">
            <input type="hidden" name="fibres_max" id="hidden-fibres-max" value="<?= $fibresMax; ?>">
            <input type="hidden" name="sodium_max" id="hidden-sodium-max" value="<?= $data['sodium_max'] ?? 2300; ?>">
            <input type="hidden" name="imc" id="hidden-imc" value="<?= $data['imc'] ?? 0; ?>">
            <input type="hidden" name="glucides" id="hidden-glucides" value="<?= $data['glucides'] ?? 0; ?>">
            <input type="hidden" name="graisses_insaturees" id="hidden-graisses-insaturees" value="<?= $data['graisses_insaturees'] ?? 0; ?>">
            
            <!-- ===== CARTE CALORIES ===== -->
            <div class="bg-yellow-100 rounded-xl border-2 border-yellow-100 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-yellow-200 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-yellow-500 flex items-center gap-2"><i class="fa-solid fa-bolt"></i>CALORIES</span>
                <span class="font-semibold text-gray-700" id="calories-display"><?= $caloriesPerte; ?></span>
                <span class="text-xs text-gray-500">kcal/jour</span>
                <span class="text-yellow-400 text-xs">Max/jour</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-yellow-50 text-yellow-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">Maximum recommandé selon NAFLD</div>
            </div>
            
            <!-- ===== CARTE SUCRES ===== -->
            <div class="bg-pink-100 rounded-xl border-2 border-pink-100 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-pink-200 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-pink-400 flex items-center gap-2"><i class="fa-solid fa-candy-cane"></i>SUCRES</span>
                <span class="font-semibold text-gray-700" id="sucres-max"><?= $sucresMax; ?></span>
                <span class="text-xs text-gray-500">g/jour</span>
                <span class="text-pink-400 text-xs">&lt;50g/jour recommandé</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-pink-50 text-pink-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">Moins de 50g/jour pour réduire l'inflammation</div>
            </div>
            
            <!-- ===== CARTE GRAISSES SATURÉES ===== -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-yellow-300 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-yellow-400 flex items-center gap-2"><i class="fa-solid fa-bacon"></i>GRAISSES SATURÉES</span>
                <span class="font-semibold text-gray-700" id="graisses-sat-max"><?= $graissesSatMax; ?></span>
                <span class="text-xs text-gray-500">g/jour</span>
                <span class="text-yellow-400 text-xs">&lt;10% des calories</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-yellow-50 text-yellow-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">Moins de 10% des calories saturées</div>
            </div>
            
            <!-- ===== CARTE PROTÉINES ===== -->
            <div class="bg-purple-100 rounded-xl border-2 border-purple-100 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-purple-200 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-purple-400 flex items-center gap-2"><i class="fa-solid fa-dumbbell"></i>PROTÉINES</span>
                <span class="font-semibold text-gray-700" id="proteines-range"><?= $proteinesRange; ?></span>
                <span class="text-xs text-gray-500">g/jour</span>
                <span class="text-purple-400 text-xs">0.8-1g/kg recommandé</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-purple-50 text-purple-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">0.8-1g/kg/jour pour la masse musculaire</div>
            </div>
            
            <!-- ===== CARTE FIBRES ===== -->
            <div class="bg-green-100 rounded-xl border-2 border-green-100 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-green-200 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-green-400 flex items-center gap-2"><i class="fa-solid fa-seedling"></i>FIBRES</span>
                <span class="font-semibold text-gray-700" id="fibres-range"><?= $fibresRange; ?></span>
                <span class="text-xs text-gray-500">g/jour</span>
                <span class="text-green-400 text-xs">25-30g/jour recommandé</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-green-50 text-green-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">25-30g/jour pour la santé digestive</div>
            </div>
            
            <!-- ===== CARTE ACTIVITÉ PHYSIQUE ===== -->
            <div class="bg-blue-100 rounded-xl border-2 border-blue-100 py-6 px-4 min-w-[220px] md:min-w-[160px] flex flex-col items-center justify-center gap-2 shadow hover:shadow-xl hover:ring-2 hover:ring-blue-200 transition-all duration-300 group relative mx-2 md:mx-0 scroll-snap-align-start hover:scale-105 hover:-translate-y-1">
                <span class="text-lg font-bold text-blue-400 flex items-center gap-2"><i class="fa-solid fa-person-running"></i>ACTIVITÉ PHYSIQUE</span>
                <span class="font-semibold text-gray-700">600</span>
                <span class="text-xs text-gray-500">kcal/jour</span>
                <span class="text-blue-400 text-xs">600 kcal/jour recommandé</span>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-blue-50 text-blue-700 text-xs rounded px-2 py-1 shadow opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">600 kcal/jour ≈ 1h marche rapide</div>
            </div>
        </div>
        
        <!-- ===== BOUTON ENREGISTREMENT ===== -->
        <button type="submit" class="mt-8 w-full bg-gradient-to-r from-green-400 to-blue-500 text-white font-bold py-3 rounded-xl shadow-lg hover:scale-105 hover:shadow-2xl hover:ring-4 hover:ring-blue-300/40 hover:ring-offset-2 hover:drop-shadow-[0_0_12px_rgba(59,130,246,0.5)] transition-transform transition-shadow text-lg">
            <i class="fa-solid fa-floppy-disk mr-2"></i>Enregistrer tous les objectifs
        </button>
    </form>
    </div>
</div>
