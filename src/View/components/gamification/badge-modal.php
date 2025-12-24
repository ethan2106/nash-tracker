<?php
/**
 * Modal détails d'un badge - Réutilisable.
 *
 * Props Alpine.js requises:
 * - showBadgeModal: bool
 * - selectedBadge: object avec { icon, label, earned, condition, tip, progress?, target?, hint? }
 *
 * Le composant est 100% générique et fonctionne pour repas, activité, etc.
 */
?>

<!-- Modal Badge Details -->
<div x-show="showBadgeModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[var(--z-modal)] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @click.self="showBadgeModal = false"
     @keydown.escape.window="showBadgeModal = false">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 transform"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="text-center">
            <!-- Icon -->
            <div class="text-6xl mb-4" :class="selectedBadge?.earned ? '' : 'grayscale'" x-text="selectedBadge?.icon"></div>
            
            <!-- Label -->
            <h3 class="text-xl font-bold mb-2" :class="selectedBadge?.earned ? 'text-amber-700' : 'text-gray-600'" x-text="selectedBadge?.label"></h3>
            
            <!-- Status badge -->
            <div class="mb-4">
                <span x-show="selectedBadge?.earned" class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                    <i class="fa-solid fa-check-circle"></i> Débloqué !
                </span>
                <span x-show="!selectedBadge?.earned" class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                    <i class="fa-solid fa-lock"></i> À débloquer
                </span>
            </div>
            
            <!-- Condition -->
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="text-sm text-gray-500 mb-1">Condition</div>
                <div class="text-gray-800 font-medium" x-text="selectedBadge?.condition"></div>
            </div>
            
            <!-- Tip -->
            <div class="bg-blue-50 rounded-xl p-4 mb-4">
                <div class="text-sm text-blue-500 mb-1">💡 Conseil</div>
                <div class="text-blue-800" x-text="selectedBadge?.tip"></div>
            </div>
            
            <!-- Progress bar for unearned badges -->
            <div x-show="!selectedBadge?.earned && selectedBadge?.progress !== undefined" class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progression</span>
                    <span x-text="selectedBadge?.hint"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-3 rounded-full transition-all duration-500"
                         :style="`width: ${Math.min(100, (selectedBadge?.progress / selectedBadge?.target) * 100)}%`"></div>
                </div>
            </div>
            
            <!-- Close button -->
            <button @click="showBadgeModal = false" 
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>
