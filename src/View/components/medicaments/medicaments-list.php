<?php

/**
 * Composant: Liste des médicaments.
 * @description Affiche la liste des médicaments organisés par sections
 * @requires Alpine.js - Variables: loading, medicamentsSections, togglePrise(), editMedicament(), deleteMedicament()
 */

declare(strict_types=1);
?>
<!-- ========== ÉTAT DE CHARGEMENT ========== -->
<div x-show="loading" class="text-center py-12">
    <i class="fa-solid fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
    <p class="text-gray-600">Chargement des médicaments...</p>
</div>

<!-- ========== LISTE DES MÉDICAMENTS ========== -->
<div x-show="!loading" id="medicaments-container">
    <div x-show="medicamentsSections">
        <template x-for="section in medicamentsSections" :key="section.type">
            <div class="medicaments-section mb-8" x-show="section.medicaments && section.medicaments.length > 0">
                <!-- En-tête de section -->
                <div class="flex items-center mb-4 bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/50">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" :style="`background: ${section.badgeBg}`">
                        <i :class="section.icon" :style="`color: ${section.color}`"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800" x-text="section.title"></h2>
                    <span class="ml-auto text-sm px-3 py-1 rounded-full font-semibold" 
                          :style="`background: ${section.badgeBg}; color: ${section.badgeColor}`"
                          x-text="section.medicaments.length + ' médicament(s)'"></span>
                </div>
                
                <!-- Liste des médicaments de la section -->
                <div class="grid gap-4">
                    <div x-show="section.medicaments">
                        <template x-for="medicament in section.medicaments" :key="'med-' + medicament.id">
                            <article class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 medicament-card border border-white/50 hover:shadow-xl transition-shadow"
                                     :aria-label="'Médicament: ' + medicament.nom">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-semibold text-gray-800 mr-3" x-text="medicament.nom"></h3>
                                        <span class="flex items-center text-sm px-3 py-1 rounded-full" :style="`background: ${section.badgeBg}; color: ${section.badgeColor}`">
                                            <i :class="section.icon" class="mr-1" aria-hidden="true"></i>
                                            <span x-text="section.label"></span>
                                        </span>
                                    </div>
                                    <div class="flex space-x-2" role="group" aria-label="Actions">
                                        <button @click="editMedicament(medicament.id)"
                                                class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors"
                                                :aria-label="'Modifier ' + medicament.nom">
                                            <i class="fa-solid fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button @click="deleteMedicament(medicament.id)"
                                                class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                :aria-label="'Supprimer ' + medicament.nom">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Boutons de prise par période -->
                                <div class="flex flex-wrap justify-center gap-3" role="group" aria-label="Périodes de prise">
                                    <template x-for="periode in medicament.heures_prise" :key="periode">
                                        <button @click="togglePrise(medicament.id, periode)"
                                                :class="getButtonClass(medicament.prises[periode])"
                                                :aria-pressed="medicament.prises[periode] === 'pris'"
                                                :aria-label="capitalizePeriode(periode) + ': ' + (medicament.prises[periode] === 'pris' ? 'Pris' : 'Non pris')"
                                                class="periode-btn flex items-center justify-center py-3 px-4 rounded-xl font-medium transition-all">
                                            <i :class="getButtonIcon(medicament.prises[periode])" class="mr-2" aria-hidden="true"></i>
                                            <span x-text="capitalizePeriode(periode)"></span>
                                        </button>
                                    </template>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- État vide -->
        <div x-show="medicamentsSections && medicamentsSections.every(s => !s.medicaments || s.medicaments.length === 0)" 
             class="text-center py-12 bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-blue-100">
            <i class="fa-solid fa-pills text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun médicament</h3>
            <p class="text-gray-500">Ajoutez votre premier médicament pour commencer</p>
        </div>
    </div>
</div>

<!-- ========== BOUTONS D'ACTION ========== -->
<div class="flex flex-wrap justify-center gap-4 mt-8" x-show="!loading">
    <button @click="showModal()"
            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all">
        <i class="fa-solid fa-plus mr-2"></i>Ajouter un Médicament
    </button>
    <button @click="showHistorique()"
            class="bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white font-semibold py-3 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all">
        <i class="fa-solid fa-history mr-2"></i>Voir l'Historique
    </button>
</div>
