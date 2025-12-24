<?php
/**
 * Liste de badges (gagnés + à débloquer) - Réutilisable.
 *
 * Props attendues (Alpine.js):
 * - badgesEarned: array de badges gagnés
 * - badgesToEarn: array de badges à débloquer
 * - openBadgeModal(badge, earned): fonction pour ouvrir le modal
 *
 * Props PHP optionnelles:
 * - $category: string pour personnaliser le contexte (repas, activite)
 * - $showClickHint: bool pour afficher l'indication de clic (default: true)
 */
$category = $category ?? 'général';
$showClickHint = $showClickHint ?? true;
?>

<!-- Badges Section -->
<div class="mt-4 pt-4 border-t border-gray-200">
    <?php if ($showClickHint)
    { ?>
    <div class="text-xs text-gray-500 mb-3 text-center">
        <i class="fa-solid fa-hand-pointer mr-1"></i> Clique sur un badge pour voir les détails
    </div>
    <?php } ?>
    
    <!-- Badges gagnés -->
    <div x-show="badgesEarned.length > 0" class="mb-3">
        <div class="text-xs text-green-600 font-semibold mb-2 flex items-center gap-1">
            <i class="fa-solid fa-trophy"></i> Badges gagnés (<span x-text="badgesEarned.length"></span>)
        </div>
        <div class="flex flex-wrap gap-2">
            <template x-for="badge in badgesEarned" :key="badge.label">
                <button @click="openBadgeModal(badge, true)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-yellow-100 to-amber-100 rounded-full text-sm font-medium text-amber-800 border border-amber-300 shadow-sm hover:shadow-md hover:scale-105 transition-all cursor-pointer">
                    <span x-text="badge.icon" class="text-lg"></span>
                    <span x-text="badge.label"></span>
                </button>
            </template>
        </div>
    </div>
    
    <!-- Badges à gagner -->
    <div x-show="badgesToEarn.length > 0">
        <div class="text-xs text-gray-500 font-semibold mb-2 flex items-center gap-1">
            <i class="fa-solid fa-lock"></i> À débloquer (<span x-text="badgesToEarn.length"></span>)
        </div>
        <div class="flex flex-wrap gap-2">
            <template x-for="badge in badgesToEarn" :key="badge.label">
                <button @click="openBadgeModal(badge, false)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 rounded-full text-sm font-medium text-gray-400 border border-gray-200 opacity-70 hover:opacity-100 hover:shadow-md transition-all cursor-pointer">
                    <span x-text="badge.icon" class="text-lg grayscale"></span>
                    <span x-text="badge.label"></span>
                    <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded-full ml-1" x-text="badge.hint"></span>
                </button>
            </template>
        </div>
    </div>
    
    <!-- Message si aucun badge -->
    <div x-show="badgesEarned.length === 0 && badgesToEarn.length === 0" class="text-center text-gray-400 py-4">
        <i class="fa-solid fa-medal text-2xl mb-2"></i>
        <p class="text-sm">Aucun badge disponible</p>
    </div>
</div>
