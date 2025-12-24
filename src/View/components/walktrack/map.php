<!-- ============================================================
     CARTE OPENSTREETMAP (Leaflet)
     ============================================================ -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
    
    <!-- Header carte -->
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-map-location-dot text-blue-500"></i>
            <span class="font-semibold text-slate-700">Tracer un parcours</span>
        </div>
        <div class="flex items-center gap-2">
            <!-- Bouton ouvrir dans Google Maps -->
            <button type="button" 
                    id="btn-open-gmaps"
                    class="px-3 py-1.5 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium transition-all disabled:opacity-40 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                    disabled
                    title="Ouvrir le parcours dans Google Maps">
                <i class="fa-solid fa-external-link-alt mr-1"></i>
                Google Maps
            </button>
            <!-- Bouton annuler dernier point -->
            <button type="button" 
                    id="btn-undo-point"
                    class="px-3 py-1.5 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 text-sm font-medium transition-all disabled:opacity-40 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                    disabled>
                <i class="fa-solid fa-undo mr-1"></i>
                Annuler
            </button>
            <!-- Bouton reset -->
            <button type="button" 
                    id="btn-reset-map"
                    class="px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 text-sm font-medium transition-all">
                <i class="fa-solid fa-trash-alt mr-1"></i>
                Effacer
            </button>
        </div>
    </div>
    
    <!-- Barre de recherche d'adresse -->
    <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-emerald-50 border-b border-slate-200">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" 
                       id="address-search"
                       placeholder="Rechercher une adresse, rue, ville..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm shadow-sm"
                       autocomplete="off">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <!-- Loading spinner -->
                <div id="address-search-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                    <i class="fa-solid fa-spinner fa-spin text-blue-500"></i>
                </div>
            </div>
            <button type="button" 
                    id="btn-add-address"
                    class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                <i class="fa-solid fa-plus mr-1"></i>
                Ajouter
            </button>
        </div>
        <!-- Résultats de recherche (dropdown) -->
        <div id="address-results" class="hidden mt-2 max-h-48 overflow-y-auto bg-white rounded-xl border border-slate-200 shadow-lg">
            <!-- Rempli dynamiquement par JS -->
        </div>
        <!-- Adresse sélectionnée -->
        <div id="selected-address" class="hidden mt-2 px-3 py-2 bg-white rounded-lg border border-emerald-200 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-emerald-700">
                    <i class="fa-solid fa-map-pin text-emerald-500 mr-2"></i>
                    <span id="selected-address-text"></span>
                </span>
                <button type="button" id="btn-clear-address" class="text-slate-400 hover:text-red-500">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
        <!-- Aide -->
        <p class="mt-2 text-xs text-slate-500">
            <i class="fa-solid fa-lightbulb text-amber-400 mr-1"></i>
            Cherchez une adresse et cliquez "Ajouter" pour l'ajouter au parcours. Ex: "12 rue de la Paix, Paris"
        </p>
        
        <!-- Info géolocalisation -->
        <div class="mt-2 px-3 py-2 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-xs text-blue-700">
                <i class="fa-solid fa-info-circle text-blue-500 mr-1"></i>
                <strong>Position de départ :</strong> Route du Villard 299, 01560 Lescheroux<br>
                <span class="text-blue-600">
                    ℹ️ La géolocalisation automatique nécessite HTTPS. En développement (HTTP), la carte utilise votre position approximative par IP ou la position par défaut.
                </span>
            </p>
        </div>
    </div>
    
    <!-- Carte -->
    <div id="walktrack-map" class="w-full h-[400px]"></div>
    
    <!-- Footer carte : infos distance -->
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-4 text-sm text-slate-600">
            <span>
                <i class="fa-solid fa-map-marker-alt text-green-500 mr-1"></i>
                Points : <strong id="map-points-count">0</strong>
            </span>
            <span>
                <i class="fa-solid fa-ruler text-blue-500 mr-1"></i>
                Distance : <strong id="map-distance">0.00</strong> km
            </span>
        </div>
        <div class="text-xs text-slate-400">
            Cliquez sur la carte pour ajouter des points
        </div>
    </div>
</div>

<!-- Modal parcours favoris -->
<div id="modal-parcours" class="fixed inset-0 z-[var(--z-modal)] hidden">
    <div class="absolute inset-0 bg-black/50" id="modal-parcours-overlay"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">
                <i class="fa-solid fa-star text-purple-500 mr-2"></i>
                Parcours favoris
            </h3>
            <button type="button" id="btn-close-modal-parcours" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Liste parcours -->
        <div id="parcours-list" class="max-h-60 overflow-y-auto space-y-2">
            <!-- Rempli dynamiquement par JS -->
        </div>
        
        <!-- Sauvegarder nouveau parcours -->
        <div class="mt-4 pt-4 border-t border-slate-200">
            <div class="flex items-center gap-2">
                <input type="text" 
                       id="input-parcours-name"
                       placeholder="Nom du parcours..."
                       class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-400 focus:border-transparent text-sm">
                <button type="button" 
                        id="btn-save-parcours"
                        class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition-all disabled:opacity-50"
                        disabled>
                    <i class="fa-solid fa-save mr-1"></i>
                    Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>
