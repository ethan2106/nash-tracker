<!-- ============================================================
     HEADER WALKTRACK
     ============================================================ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
            <i class="fa-solid fa-person-walking text-green-500"></i>
            WalkTrack
        </h1>
        <p class="text-slate-500 mt-1">Suivi de vos marches et parcours</p>
    </div>
    
    <div class="flex items-center gap-3">
        <!-- Bouton parcours favoris -->
        <button type="button" 
                id="btn-parcours-favoris"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/90 hover:bg-purple-50 text-purple-700 font-semibold shadow-md transition-all border border-purple-200">
            <i class="fa-solid fa-star text-purple-400"></i>
            <span class="hidden sm:inline">Mes parcours</span>
        </button>
        
        <!-- Date du jour -->
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/90 text-slate-600 shadow-md border border-slate-200">
            <i class="fa-solid fa-calendar text-slate-400"></i>
            <span><?= date('d/m/Y'); ?></span>
        </div>
    </div>
</div>
