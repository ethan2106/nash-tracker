<?php
/**
 * Compteur de streak (série de jours consécutifs) - Réutilisable.
 *
 * Props PHP:
 * - $streakCount: int (nombre de jours, peut être 0)
 * - $label: string (ex: "jours de suite", "jours d'activité")
 * - $emoji: string (default: 🔥)
 * - $showIfZero: bool (afficher même si streak = 0, default: false)
 *
 * Props Alpine.js (optionnel, pour dynamique):
 * - streak: int
 * - todayReached: bool (si true, affiche streak + 1)
 */
$streakCount = $streakCount ?? 0;
$label = $label ?? 'jours de suite';
$emoji = $emoji ?? '🔥';
$showIfZero = $showIfZero ?? false;
$useAlpine = $useAlpine ?? true; // Utiliser Alpine.js par défaut
?>

<?php if ($useAlpine)
{ ?>
<!-- Version Alpine.js (dynamique) -->
<div x-show="streak > 0 || todayReached<?= $showIfZero ? ' || true' : ''; ?>" 
     class="text-center bg-gradient-to-br from-orange-100 to-yellow-100 rounded-2xl p-4 border-2 border-orange-200 shadow-lg"
     x-transition>
    <div class="text-3xl mb-1"><?= $emoji; ?></div>
    <div class="text-2xl font-bold text-orange-600" x-text="todayReached ? streak + 1 : streak"></div>
    <div class="text-xs text-orange-500 font-medium"><?= htmlspecialchars($label); ?></div>
</div>

<?php } else
{ ?>
<!-- Version PHP statique -->
<?php if ($streakCount > 0 || $showIfZero)
{ ?>
<div class="text-center bg-gradient-to-br from-orange-100 to-yellow-100 rounded-2xl p-4 border-2 border-orange-200 shadow-lg">
    <div class="text-3xl mb-1"><?= $emoji; ?></div>
    <div class="text-2xl font-bold text-orange-600"><?= (int)$streakCount; ?></div>
    <div class="text-xs text-orange-500 font-medium"><?= htmlspecialchars($label); ?></div>
</div>
<?php } ?>
<?php } ?>
