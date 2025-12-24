<?php
/**
 * Barre d'XP avec niveau - Réutilisable.
 *
 * Props PHP:
 * - $currentXp: int (XP actuels)
 * - $nextLevelXp: int (XP requis pour prochain niveau)
 * - $level: int (niveau actuel)
 * - $levelName: string (nom du niveau: Débutant, Intermédiaire, Expert, Master)
 * - $showLevelBadge: bool (afficher badge niveau, default: true)
 *
 * Couleurs par niveau:
 * - Débutant: green
 * - Intermédiaire: blue
 * - Expert: purple
 * - Master: amber/gold
 */
$currentXp = $currentXp ?? 0;
$nextLevelXp = $nextLevelXp ?? 100;
$level = $level ?? 1;
$levelName = $levelName ?? 'Débutant';
$showLevelBadge = $showLevelBadge ?? true;

// Calcul progression
$percentage = $nextLevelXp > 0 ? min(100, ($currentXp / $nextLevelXp) * 100) : 0;

// Couleurs selon niveau
$levelColors = [
    'Débutant' => ['bar' => 'from-green-400 to-green-600', 'badge' => 'bg-green-100 text-green-700 border-green-300', 'icon' => '🌱'],
    'Intermédiaire' => ['bar' => 'from-blue-400 to-blue-600', 'badge' => 'bg-blue-100 text-blue-700 border-blue-300', 'icon' => '⭐'],
    'Expert' => ['bar' => 'from-purple-400 to-purple-600', 'badge' => 'bg-purple-100 text-purple-700 border-purple-300', 'icon' => '🔥'],
    'Master' => ['bar' => 'from-amber-400 to-amber-600', 'badge' => 'bg-amber-100 text-amber-700 border-amber-300', 'icon' => '👑'],
];

$colors = $levelColors[$levelName] ?? $levelColors['Débutant'];
?>

<div class="bg-white/80 backdrop-blur rounded-2xl p-4 shadow-lg border border-gray-200">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <?php if ($showLevelBadge)
            { ?>
            <span class="text-2xl"><?= $colors['icon']; ?></span>
            <span class="px-2 py-1 rounded-full text-xs font-semibold border <?= $colors['badge']; ?>">
                Niveau <?= (int)$level; ?> - <?= htmlspecialchars($levelName); ?>
            </span>
            <?php } ?>
        </div>
        <div class="text-sm text-gray-600 font-medium">
            <?= number_format($currentXp); ?> / <?= number_format($nextLevelXp); ?> XP
        </div>
    </div>
    
    <!-- Barre de progression -->
    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
        <div class="bg-gradient-to-r <?= $colors['bar']; ?> h-4 rounded-full transition-all duration-700 relative"
             style="width: <?= $percentage; ?>%">
            <!-- Effet brillant -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
        </div>
    </div>
    
    <!-- Info prochain niveau -->
    <div class="mt-2 text-xs text-gray-500 text-center">
        <?php if ($percentage >= 100)
        { ?>
            <span class="text-green-600 font-semibold">🎉 Prêt pour le niveau suivant !</span>
        <?php } else
        { ?>
            Encore <?= number_format($nextLevelXp - $currentXp); ?> XP pour le prochain niveau
        <?php } ?>
    </div>
</div>
