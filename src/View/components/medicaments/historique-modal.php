<?php

/**
 * Composant: Modal historique des prises.
 * @description Modal Alpine.js affichant l'historique des prises de médicaments
 * @requires Alpine.js - Variables: showHistoriqueModal, historiqueData, loadHistorique(), closeHistorique()
 */

declare(strict_types=1);
?>
<!-- ========== MODAL HISTORIQUE ========== -->
<div x-show="showHistoriqueModal"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-historique-title"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[var(--z-overlay)] flex items-center justify-center p-4"
     @click.self="closeHistorique()">
    
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden"
         x-show="showHistoriqueModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-trap.noscroll="showHistoriqueModal">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-purple-500 to-pink-600">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-history text-white text-2xl" aria-hidden="true"></i>
                <h3 id="modal-historique-title" class="text-2xl font-bold text-white">Historique des Prises</h3>
            </div>
            <button @click="closeHistorique()" 
                    class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/20 transition-colors"
                    aria-label="Fermer l'historique">
                <i class="fa-solid fa-times text-2xl" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Contenu -->
        <div class="p-6 overflow-y-auto max-h-[calc(85vh-180px)]">
            <!-- Chargement -->
            <div x-show="loadingHistorique" class="text-center py-12">
                <i class="fa-solid fa-spinner fa-spin text-4xl text-purple-500 mb-4"></i>
                <p class="text-gray-600">Chargement de l'historique...</p>
            </div>
            
            <!-- Liste historique -->
            <div x-show="!loadingHistorique && historiqueData.length > 0">
                <template x-for="jour in historiqueData" :key="jour.date">
                    <div class="mb-6">
                        <!-- Date -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fa-solid fa-calendar text-purple-600"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800" x-text="formatHistoriqueDate(jour.date)"></h4>
                        </div>
                        
                        <!-- Prises du jour -->
                        <div class="bg-gray-50 rounded-2xl p-4 ml-5 border-l-4 border-purple-300">
                            <template x-for="prise in jour.prises" :key="prise.id || (prise.medicament_id + '-' + prise.periode)">
                                <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-full flex items-center justify-center"
                                              :class="prise.status === 'pris' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                            <i :class="prise.status === 'pris' ? 'fa-solid fa-check' : 'fa-solid fa-times'"></i>
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-800" x-text="prise.nom"></p>
                                            <p class="text-sm text-gray-500">
                                                <span x-text="capitalizePeriode(prise.periode)"></span>
                                                <span x-show="prise.dose" class="ml-2">• <span x-text="prise.dose"></span></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium"
                                              :class="prise.status === 'pris' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                              x-text="prise.status === 'pris' ? 'Pris' : 'Non pris'"></span>
                                        <p class="text-xs text-gray-400 mt-1" x-show="prise.timestamp" x-text="formatTime(prise.timestamp)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- État vide -->
            <div x-show="!loadingHistorique && historiqueData.length === 0" class="text-center py-12">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-history text-3xl text-gray-400"></i>
                </div>
                <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun historique</h4>
                <p class="text-gray-500">L'historique de vos prises apparaîtra ici</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button @click="closeHistorique()" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition-colors">
                <i class="fa-solid fa-times mr-2"></i>Fermer
            </button>
        </div>
    </div>
</div>
