<?php
/**
 * Toast notification "Badge débloqué !" - Réutilisable.
 *
 * Props Alpine.js:
 * - showAchievementToast: bool
 * - achievementBadge: object avec { icon, label }
 *
 * Le toast s'auto-ferme après 4 secondes.
 * Peut être déclenché depuis n'importe quelle page.
 */
$position = $position ?? 'top-right'; // top-right, top-center, bottom-right, bottom-center
$autoClose = $autoClose ?? 4000; // ms

// Classes de position
$positionClasses = [
    'top-right' => 'top-4 right-4',
    'top-center' => 'top-4 left-1/2 -translate-x-1/2',
    'bottom-right' => 'bottom-4 right-4',
    'bottom-center' => 'bottom-4 left-1/2 -translate-x-1/2',
];

$posClass = $positionClasses[$position] ?? $positionClasses['top-right'];
?>

<!-- Achievement Toast -->
<div x-show="showAchievementToast"
     x-init="$watch('showAchievementToast', value => { if(value) setTimeout(() => showAchievementToast = false, <?= $autoClose; ?>) })"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 transform translate-y-[-20px] scale-95"
     x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="fixed <?= $posClass; ?> z-[200]">
    
    <div class="bg-gradient-to-r from-amber-100 via-yellow-100 to-amber-100 rounded-2xl shadow-2xl p-4 border-2 border-amber-300 max-w-sm">
        <div class="flex items-center gap-3">
            <!-- Confetti animation -->
            <div class="relative">
                <span class="text-4xl animate-bounce" x-text="achievementBadge?.icon">🏆</span>
                <div class="absolute -top-1 -right-1 text-lg animate-ping">✨</div>
            </div>
            
            <div class="flex-1">
                <div class="text-xs text-amber-600 font-semibold uppercase tracking-wide">
                    🎉 Badge débloqué !
                </div>
                <div class="text-lg font-bold text-amber-800" x-text="achievementBadge?.label">
                    Nouveau badge
                </div>
            </div>
            
            <!-- Close button -->
            <button @click="showAchievementToast = false" 
                    class="text-amber-400 hover:text-amber-600 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <!-- Progress bar auto-close -->
        <div class="mt-3 w-full bg-amber-200 rounded-full h-1 overflow-hidden">
            <div class="bg-amber-500 h-1 rounded-full animate-shrink origin-left"
                 style="animation: shrink <?= $autoClose; ?>ms linear forwards;"></div>
        </div>
    </div>
</div>

<style>
@keyframes shrink {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
.animate-shrink {
    animation: shrink <?= $autoClose; ?>ms linear forwards;
}
</style>
