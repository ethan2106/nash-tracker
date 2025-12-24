<?php
/**
 * Section Graphiques - Évolution nutritionnelle et activité récente.
 */
?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Graphique Nutritionnel -->
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">Évolution Nutritionnelle</h3>
        </div>
        <div class="h-64 flex items-center justify-center">
            <canvas id="nutritionChart" width="400" height="200" class="max-w-full h-auto"></canvas>
        </div>
    </div>
    
    <!-- Activité Récente -->
    <div id="recent-activities" class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">Activité Récente</h3>
        </div>
        <div class="space-y-4">
            <!-- État de chargement -->
            <div x-show="loading" class="text-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
                <p class="text-gray-600">Chargement...</p>
            </div>
            
            <!-- Timeline des activités avec ligne de connexion -->
            <div class="relative">
                <!-- Ligne verticale de connexion -->
                <div x-show="activities.length > 0" class="absolute left-6 top-8 bottom-8 w-0.5 bg-gradient-to-b from-blue-300 via-purple-300 to-pink-300"></div>
                
                <template x-for="(activity, index) in activities" :key="activity.id">
                    <div class="relative flex items-start gap-4 mb-4 group"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <!-- Icône avec badge de connexion -->
                        <div class="relative z-10 flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300"
                                 :class="{
                                     'bg-gradient-to-br from-blue-400 to-blue-600': activity.type === 'repas',
                                     'bg-gradient-to-br from-purple-400 to-purple-600': activity.type === 'medicament',
                                     'bg-gradient-to-br from-orange-400 to-orange-600': activity.type === 'activite',
                                     'bg-gradient-to-br from-gray-400 to-gray-600': !['repas', 'medicament', 'activite'].includes(activity.type)
                                 }">
                                <i class="fa-solid text-white text-lg"
                                   :class="{
                                       'fa-utensils': activity.type === 'repas',
                                       'fa-pills': activity.type === 'medicament',
                                       'fa-dumbbell': activity.type === 'activite',
                                       'fa-circle': !['repas', 'medicament', 'activite'].includes(activity.type)
                                   }"></i>
                            </div>
                        </div>
                        
                        <!-- Contenu de l'activité avec hover effet -->
                        <div class="flex-1 bg-white/60 backdrop-blur-sm rounded-2xl p-4 shadow-md border border-white/50 hover:bg-white/80 hover:shadow-lg transition-all duration-300">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 mb-1" x-html="escapeHtml(activity.description)"></p>
                                    <p class="text-sm text-gray-600 flex items-center gap-2 flex-wrap">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-clock text-gray-400 text-xs"></i>
                                            <span x-text="formatActivityDate(activity.date)"></span>
                                        </span>
                                        <span x-show="activity.valeur && Number(activity.valeur) > 0" class="flex items-center gap-1">
                                            <span class="text-gray-400">•</span>
                                            <i class="fa-solid fa-chart-simple text-gray-400 text-xs"></i>
                                            <span class="font-medium" x-text="Number(activity.valeur).toLocaleString()"></span>
                                            <span x-text="activity.unite || ''"></span>
                                        </span>
                                    </p>
                                </div>
                                
                                <!-- Badge de statut (si objectif atteint) -->
                                <div x-show="activity.objectif_atteint" 
                                     class="flex-shrink-0 px-3 py-1 bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 text-xs font-semibold rounded-full border border-green-200">
                                    <i class="fa-solid fa-check mr-1"></i>
                                    Objectif atteint
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Message si aucune activité -->
            <div x-show="!loading && activities.length === 0" class="text-center py-8 text-gray-500">
                <i class="fa-solid fa-inbox text-4xl mb-4"></i>
                <p>Aucune activité récente</p>
            </div>
            
            <!-- Pagination -->
            <div x-show="totalPages > 1" class="mt-4 flex items-center justify-end gap-3">
                <button @click="loadActivities(currentPage - 1)"
                        :disabled="currentPage <= 1 || loading"
                        :class="currentPage <= 1 || loading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-4 py-2 bg-white rounded-xl border hover:bg-gray-50 transition-colors">
                    &laquo; Précédent
                </button>
                <span class="text-sm text-gray-600">
                    Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </span>
                <button @click="loadActivities(currentPage + 1)"
                        :disabled="currentPage >= totalPages || loading"
                        :class="currentPage >= totalPages || loading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-4 py-2 bg-white rounded-xl border hover:bg-gray-50 transition-colors">
                    Suivant &raquo;
                </button>
            </div>
        </div>
    </div>
</div>
