<!-- ============================================================
     FORMULAIRE AJOUT MARCHE
     ============================================================ -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-6">
    
    <!-- Header avec toggle mode -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-green-500" id="form-icon"></i>
            <span id="form-title">Enregistrer une marche</span>
        </h3>
        
        <!-- Toggle Mode Simulation -->
        <label class="flex items-center gap-2 cursor-pointer">
            <span class="text-sm text-slate-500">Simulation</span>
            <div class="relative">
                <input type="checkbox" id="toggle-simulation" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-checked:bg-purple-500 rounded-full transition-colors"></div>
                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5 shadow"></div>
            </div>
        </label>
    </div>
    
    <!-- Info mode simulation (caché par défaut) -->
    <div id="simulation-info" class="hidden mb-4 p-3 rounded-xl bg-purple-50 border border-purple-200">
        <div class="flex items-start gap-2">
            <i class="fa-solid fa-flask text-purple-500 mt-0.5"></i>
            <div class="text-sm text-purple-700">
                <strong>Mode Simulation</strong> : Planifiez votre parcours et visualisez les stats avant d'enregistrer.
            </div>
        </div>
    </div>
    
    <form id="form-add-walk" class="space-y-4">
        
        <!-- Type de marche -->
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2">Type de marche</label>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="walk_type" value="marche" checked class="sr-only peer">
                    <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all text-center">
                        <i class="fa-solid fa-person-walking text-2xl text-green-500 mb-1"></i>
                        <div class="text-sm font-medium text-slate-700">Marche normale</div>
                        <div class="text-xs text-slate-400">~4 km/h</div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="walk_type" value="marche_rapide" class="sr-only peer">
                    <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center">
                        <i class="fa-solid fa-person-walking-arrow-right text-2xl text-blue-500 mb-1"></i>
                        <div class="text-sm font-medium text-slate-700">Marche rapide</div>
                        <div class="text-xs text-slate-400">~6 km/h</div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- Distance (auto ou manuelle) -->
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2" for="input-distance">
                Distance (km)
                <span class="text-xs text-slate-400 ml-1">(auto-calculée depuis la carte)</span>
            </label>
            <div class="relative">
                <input type="number" 
                       id="input-distance"
                       name="distance_km"
                       step="0.01"
                       min="0.1"
                       max="100"
                       placeholder="0.00"
                       required
                       class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-green-400 focus:border-transparent text-lg">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">km</span>
            </div>
        </div>
        
        <!-- Heures départ / arrivée -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-2" for="input-start-time">
                    Heure de départ
                </label>
                <input type="time" 
                       id="input-start-time"
                       name="start_time"
                       class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-green-400 focus:border-transparent text-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-2" for="input-end-time">
                    Heure d'arrivée
                </label>
                <input type="time" 
                       id="input-end-time"
                       name="end_time"
                       class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-green-400 focus:border-transparent text-lg">
            </div>
        </div>
        
        <!-- Durée (auto-calculée ou manuelle) -->
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2" for="input-duration">
                Durée (minutes)
                <span class="text-xs text-slate-400 ml-1" id="duration-source">(ou saisie manuelle)</span>
            </label>
            <div class="relative">
                <input type="number" 
                       id="input-duration"
                       name="duration_minutes"
                       min="1"
                       max="600"
                       placeholder="30"
                       required
                       class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-green-400 focus:border-transparent text-lg">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">min</span>
            </div>
        </div>
        
        <!-- Note optionnelle -->
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-2" for="input-note">
                Note <span class="text-xs text-slate-400">(optionnel)</span>
            </label>
            <input type="text" 
                   id="input-note"
                   name="note"
                   placeholder="Ex: Tour du parc, beau temps..."
                   maxlength="200"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-green-400 focus:border-transparent">
        </div>
        
        <!-- Champs cachés -->
        <input type="hidden" id="input-route-points" name="route_points" value="">
        <input type="hidden" name="walk_date" value="<?= date('Y-m-d'); ?>">
        
        <!-- Boutons : Normal OU Simulation -->
        <div id="btn-normal-mode">
            <button type="submit" 
                    id="btn-submit-walk"
                    class="w-full py-4 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold text-lg shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                <i class="fa-solid fa-plus"></i>
                <span>Ajouter cette marche</span>
            </button>
        </div>
        
        <!-- Boutons mode simulation (caché par défaut) -->
        <div id="btn-simulation-mode" class="hidden space-y-3">
            <!-- Bouton simuler -->
            <button type="button" 
                    id="btn-simulate"
                    class="w-full py-4 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold text-lg shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-flask"></i>
                <span>Simuler ce parcours</span>
            </button>
        </div>
        
        <!-- Résultat simulation (caché par défaut) -->
        <div id="simulation-result" class="hidden mt-4 p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-300">
            <h4 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-chart-line"></i>
                Résultat de la simulation
            </h4>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-white/80 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-600" id="sim-distance">0.00</div>
                    <div class="text-xs text-slate-500">km</div>
                </div>
                <div class="bg-white/80 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-600" id="sim-duration">0</div>
                    <div class="text-xs text-slate-500">min estimées</div>
                </div>
                <div class="bg-white/80 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-orange-600" id="sim-calories">0</div>
                    <div class="text-xs text-slate-500">kcal</div>
                </div>
                <div class="bg-white/80 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-purple-600" id="sim-speed">0.0</div>
                    <div class="text-xs text-slate-500">km/h</div>
                </div>
            </div>
            
            <!-- Actions après simulation -->
            <div class="flex gap-2">
                <button type="button" 
                        id="btn-modify-simulation"
                        class="flex-1 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-pencil"></i>
                    Modifier
                </button>
                <button type="submit" 
                        id="btn-confirm-simulation"
                        class="flex-1 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
    
    <!-- Estimation calories -->
    <div class="mt-4 p-3 rounded-xl bg-orange-50 border border-orange-200 flex items-center gap-3">
        <i class="fa-solid fa-fire text-orange-500"></i>
        <div class="text-sm">
            <span class="text-slate-600">Calories estimées :</span>
            <strong class="text-orange-600" id="estimated-calories">0</strong>
            <span class="text-slate-500">kcal</span>
        </div>
    </div>
</div>
