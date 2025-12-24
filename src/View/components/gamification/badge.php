<?php
/**
 * Composant Badge unique - Réutilisable.
 *
 * Props attendues:
 * - $badge: array avec 'icon', 'label', 'earned' (optionnel: 'hint')
 * - $earned: bool (état du badge)
 * - $onClick: string (action Alpine.js au clic, ex: "openBadgeModal(badge, true)")
 *
 * Ou utiliser avec Alpine.js x-for directement
 */
$earned = $earned ?? ($badge['earned'] ?? false);
$icon = $badge['icon'] ?? '🏆';
$label = $badge['label'] ?? 'Badge';
$hint = $badge['hint'] ?? null;
?>

<?php if ($earned)
{ ?>
    <button <?= isset($onClick) ? "@click=\"{$onClick}\"" : ''; ?>
            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-yellow-100 to-amber-100 rounded-full text-sm font-medium text-amber-800 border border-amber-300 shadow-sm hover:shadow-md hover:scale-105 transition-all cursor-pointer">
        <span class="text-lg"><?= htmlspecialchars($icon); ?></span>
        <span><?= htmlspecialchars($label); ?></span>
    </button>
<?php } else
{ ?>
    <button <?= isset($onClick) ? "@click=\"{$onClick}\"" : ''; ?>
            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 rounded-full text-sm font-medium text-gray-400 border border-gray-200 opacity-70 hover:opacity-100 hover:shadow-md transition-all cursor-pointer">
        <span class="text-lg grayscale"><?= htmlspecialchars($icon); ?></span>
        <span><?= htmlspecialchars($label); ?></span>
        <?php if ($hint)
        { ?>
            <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded-full ml-1"><?= htmlspecialchars($hint); ?></span>
        <?php } ?>
    </button>
<?php } ?>
