<!-- ============================================================
     OBJECTIFS PERSONNALISÉS
     ============================================================ -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-5">
    
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-bullseye text-purple-500"></i>
            Mes objectifs
        </h3>
        <button type="button" 
                id="btn-edit-objectives"
                class="text-sm text-purple-600 hover:text-purple-800 transition-all">
            <i class="fa-solid fa-edit"></i>
        </button>
    </div>
    
    <!-- Affichage objectifs -->
    <div id="objectives-display" class="space-y-3">
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Par jour</span>
            <span class="font-semibold text-slate-700" id="obj-km-day"><?= $objectifs['km_per_day']; ?> km</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Jours / semaine</span>
            <span class="font-semibold text-slate-700" id="obj-days-week"><?= $objectifs['days_per_week']; ?> jours</span>
        </div>
        <div class="flex items-center justify-between text-sm border-t border-slate-200 pt-3">
            <span class="text-slate-500">Objectif hebdo</span>
            <span class="font-bold text-purple-600" id="obj-week-total">
                <?= $objectifs['km_per_day'] * $objectifs['days_per_week']; ?> km
            </span>
        </div>
    </div>
    
    <!-- Formulaire édition (caché par défaut) -->
    <form id="form-objectives" class="hidden space-y-3">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Km par jour</label>
            <input type="number" 
                   name="km_per_day"
                   step="0.5"
                   min="0.5"
                   max="50"
                   value="<?= $objectifs['km_per_day']; ?>"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-400 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Jours par semaine</label>
            <input type="number" 
                   name="days_per_week"
                   min="1"
                   max="7"
                   value="<?= $objectifs['days_per_week']; ?>"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-400 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" 
                    class="flex-1 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition-all">
                Sauvegarder
            </button>
            <button type="button" 
                    id="btn-cancel-objectives"
                    class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-600 text-sm transition-all">
                Annuler
            </button>
        </div>
    </form>
    
    <!-- Progression semaine -->
    <?php if (!empty($progression['semaine']))
    { ?>
    <div class="mt-4 pt-4 border-t border-slate-200">
        <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-slate-500">Cette semaine</span>
            <span class="text-slate-600" id="progression-semaine-text">
                <?= number_format($progression['semaine']['actuel'], 1, ',', ''); ?> / <?= $progression['semaine']['objectif']; ?> km
            </span>
        </div>
        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-purple-400 to-purple-600 rounded-full transition-all duration-500"
                 id="progression-semaine-bar"
                 style="width: <?= min(100, $progression['semaine']['pourcentage']); ?>%"></div>
        </div>
        <div class="mt-2 text-xs text-slate-400">
            <i class="fa-solid fa-calendar-check mr-1"></i>
            <?= $progression['semaine']['jours_actifs']; ?> / <?= $progression['semaine']['jours_objectif']; ?> jours actifs
        </div>
    </div>
    <?php } ?>
</div>
