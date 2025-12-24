<?php

/**
 * Composant: Modal ajout/édition médicament.
 * @description Modal Alpine.js pour ajouter ou modifier un médicament
 * @requires Alpine.js - Variables: showMedicamentModal, editingMedicament, medicamentForm, saveMedicament()
 */

declare(strict_types=1);
?>
<!-- ========== MODAL AJOUT/ÉDITION ========== -->
<div x-show="showMedicamentModal"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-medicament-title"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[var(--z-overlay)] flex items-center justify-center p-4"
     @click.self="showMedicamentModal = false"
     @keydown.escape.window="showMedicamentModal = false">

    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-md w-full overflow-hidden"
         x-show="showMedicamentModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-trap.noscroll="showMedicamentModal"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-indigo-600">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-pills text-white text-2xl" aria-hidden="true"></i>
                <h3 id="modal-medicament-title" class="text-xl font-bold text-white" 
                    x-text="editingMedicament ? 'Modifier le Médicament' : 'Ajouter un Médicament'"></h3>
            </div>
            <button @click="showMedicamentModal = false" 
                    class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/20 transition-colors"
                    aria-label="Fermer la fenêtre">
                <i class="fa-solid fa-times text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Contenu -->
        <div class="p-6">
            <form @submit.prevent="saveMedicament()">
                <input type="hidden" x-model="medicamentForm.id">

                <!-- Nom -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du médicament</label>
                    <input type="text" x-model="medicamentForm.nom" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>

                <!-- Dose -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dose</label>
                    <input type="text" x-model="medicamentForm.dose"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>

                <!-- Type -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select x-model="medicamentForm.type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                        <option value="regulier">Régulier</option>
                        <option value="ponctuel">Ponctuel</option>
                    </select>
                </div>

                <!-- Fréquence -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence</label>
                    <input type="text" x-model="medicamentForm.frequence" placeholder="e.g., quotidien"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>

                <!-- Périodes de prise -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Périodes de prise</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors cursor-pointer">
                            <input type="checkbox" x-model="medicamentForm.heures_prise" value="matin" class="mr-3 w-4 h-4 text-blue-600 rounded">
                            <span>Matin</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors cursor-pointer">
                            <input type="checkbox" x-model="medicamentForm.heures_prise" value="midi" class="mr-3 w-4 h-4 text-blue-600 rounded">
                            <span>Midi</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors cursor-pointer">
                            <input type="checkbox" x-model="medicamentForm.heures_prise" value="soir" class="mr-3 w-4 h-4 text-blue-600 rounded">
                            <span>Soir</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors cursor-pointer">
                            <input type="checkbox" x-model="medicamentForm.heures_prise" value="nuit" class="mr-3 w-4 h-4 text-blue-600 rounded">
                            <span>Nuit</span>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="medicamentForm.notes" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"></textarea>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showMedicamentModal = false"
                            class="px-5 py-3 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-2xl font-medium transition-colors">
                        Annuler
                    </button>
                    <button type="submit" :disabled="savingMedicament"
                            class="px-5 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-2xl font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <span x-show="!savingMedicament" x-text="editingMedicament ? 'Modifier' : 'Ajouter'"></span>
                        <span x-show="savingMedicament" x-cloak>
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i>Sauvegarde...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
