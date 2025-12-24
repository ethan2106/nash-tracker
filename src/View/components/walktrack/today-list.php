<!-- ============================================================
     LISTE DES MARCHES DU JOUR
     ============================================================ -->
<div class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-6">
    
    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-list text-slate-500"></i>
        Marches d'aujourd'hui
    </h3>
    
    <div id="today-walks-list" class="space-y-3">
        <?php if (!empty($marches))
        { ?>
            <?php foreach ($marches as $marche)
            {
                $hasRoute = !empty($marche['route_points']);
                // route_points peut être un array ou une string JSON
                $routePointsJson = '';
                if ($hasRoute)
                {
                    if (is_array($marche['route_points']))
                    {
                        $routePointsJson = json_encode($marche['route_points']);
                    } else
                    {
                        $routePointsJson = $marche['route_points'];
                    }
                }
                $routePointsAttr = htmlspecialchars($routePointsJson, ENT_QUOTES, 'UTF-8');
                ?>
                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 transition-all walk-item <?= $hasRoute ? 'cursor-pointer' : ''; ?>" 
                     data-walk-id="<?= $marche['id']; ?>"
                     data-has-route="<?= $hasRoute ? 'true' : 'false'; ?>"
                     data-route-points="<?= $routePointsAttr; ?>">
                    
                    <div class="flex items-center gap-4">
                        <!-- Icône type -->
                        <div class="w-12 h-12 rounded-xl <?= $marche['walk_type'] === 'marche_rapide' ? 'bg-blue-100' : 'bg-green-100'; ?> flex items-center justify-center">
                            <i class="fa-solid <?= $marche['walk_type'] === 'marche_rapide' ? 'fa-person-walking-arrow-right text-blue-600' : 'fa-person-walking text-green-600'; ?> text-xl"></i>
                        </div>
                        
                        <div>
                            <div class="font-semibold text-slate-700 flex items-center gap-2">
                                <?= $marche['walk_type'] === 'marche_rapide' ? 'Marche rapide' : 'Marche normale'; ?>
                                <?php if ($hasRoute)
                                { ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fa-solid fa-route mr-1"></i>Parcours
                                    </span>
                                <?php } ?>
                            </div>
                            <div class="text-sm text-slate-500">
                                <?php if (!empty($marche['start_time']) && !empty($marche['end_time']))
                                { ?>
                                    <span class="text-green-600 font-medium">
                                        <?= date('H:i', strtotime($marche['start_time'])); ?> → <?= date('H:i', strtotime($marche['end_time'])); ?>
                                    </span> • 
                                <?php } ?>
                                <?= number_format($marche['distance_km'], 2, ',', ''); ?> km 
                                • <?= $marche['duration_minutes']; ?> min
                                • <?= $marche['calories_burned']; ?> kcal
                            </div>
                            <?php if (!empty($marche['note']))
                            { ?>
                                <div class="text-xs text-slate-400 mt-1 italic">
                                    "<?= htmlspecialchars($marche['note']); ?>"
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <?php if ($hasRoute)
                        { ?>
                            <!-- Bouton voir trajet -->
                            <button type="button" 
                                    class="btn-view-route p-2 rounded-lg text-blue-400 hover:text-blue-600 hover:bg-blue-100 transition-all"
                                    data-route-points="<?= $routePointsAttr; ?>"
                                    title="Voir le trajet sur la carte">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </button>
                        <?php } ?>
                        
                        <!-- Bouton éditer -->
                        <button type="button" 
                                class="btn-edit-walk p-2 rounded-lg text-amber-400 hover:text-amber-600 hover:bg-amber-50 transition-all"
                                data-walk-id="<?= $marche['id']; ?>"
                                data-walk-type="<?= $marche['walk_type']; ?>"
                                data-distance="<?= $marche['distance_km']; ?>"
                                data-duration="<?= $marche['duration_minutes']; ?>"
                                data-start-time="<?= $marche['start_time'] ?? ''; ?>"
                                data-end-time="<?= $marche['end_time'] ?? ''; ?>"
                                data-note="<?= htmlspecialchars($marche['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                title="Modifier">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        
                        <!-- Heure -->
                        <span class="text-sm text-slate-400">
                            <?= date('H:i', strtotime($marche['created_at'] ?? 'now')); ?>
                        </span>
                        
                        <!-- Bouton supprimer -->
                        <button type="button" 
                                class="btn-delete-walk p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-all"
                                data-walk-id="<?= $marche['id']; ?>"
                                title="Supprimer">
                            <i class="fa-solid fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            <?php } ?>
        <?php } else
        { ?>
            <div class="text-center py-8 text-slate-400" id="no-walks-message">
                <i class="fa-solid fa-shoe-prints text-4xl mb-3"></i>
                <p class="text-sm">Aucune marche aujourd'hui</p>
                <p class="text-xs mt-1">Tracez un parcours sur la carte et ajoutez votre première marche !</p>
            </div>
        <?php } ?>
    </div>
</div>
