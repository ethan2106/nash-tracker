<?php
/**
 * Composant Détails de Notation Alimentaire (version relookée).
 *
 * Paramètres :
 *  - $grade : array (grade, percentage, label, description, color, text_color)
 *  - $macros : array (proteins_100g, fiber_100g, saturated-fat_100g, sugars_100g, etc.)
 */
$grade = $grade ?? [];
$macros = $macros ?? [];

if (empty($grade))
{
    return;
}

$percentage = is_numeric($grade['percentage'] ?? null) ? (float)$grade['percentage'] : 0;
$color = htmlspecialchars($grade['color'] ?? 'green');
$text_color = htmlspecialchars($grade['text_color'] ?? 'text-green-700');
?>

<div class="food-quality-details bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-200/60 p-5 shadow-lg max-w-2xl mx-auto">
    <div class="flex gap-4 items-center">
        <!-- Jauge circulaire -->
        <div class="w-28 h-28 flex-none">
            <?php
            // Calcul pour l'arc SVG
            $r = 40;
$circ = 2 * pi() * $r;
$offset = $circ * (1 - min(100, max(0, $percentage)) / 100);
$displayPerc = round($percentage);
?>
            <svg viewBox="0 0 100 100" class="w-28 h-28" role="img" aria-label="Score nutritionnel <?= $displayPerc; ?> pourcent">
                <defs>
                    <linearGradient id="g-<?= $color; ?>" x1="0" x2="1" y1="0" y2="0">
                        <stop offset="0%" stop-color="<?= $color === 'red' ? '#f87171' : ($color === 'yellow' ? '#fbbf24' : '#34d399'); ?>"/>
                        <stop offset="100%" stop-color="<?= $color === 'red' ? '#ef4444' : ($color === 'yellow' ? '#f59e0b' : '#059669'); ?>"/>
                    </linearGradient>
                </defs>

                <g transform="translate(50,50)">
                    <!-- Cercle de fond -->
                    <circle r="<?= $r; ?>" fill="none" stroke="#eef2f7" stroke-width="10"/>
                    <!-- Arc dynamique -->
                    <circle r="<?= $r; ?>" fill="none" stroke="url(#g-<?= $color; ?>)" stroke-width="10"
                            stroke-dasharray="<?= $circ; ?>" stroke-dashoffset="<?= $offset; ?>"
                            stroke-linecap="round" transform="rotate(-90)" style="transition: stroke-dashoffset .8s ease"/>
                    <!-- Texte -->
                    <text x="0" y="-4" font-size="16" font-weight="700" text-anchor="middle" class="<?= $text_color; ?>">
                        <?= $displayPerc; ?>%
                    </text>
                    <text x="0" y="12" font-size="9" text-anchor="middle" fill="#6b7280">Nutrition</text>
                </g>
            </svg>
        </div>

        <div class="flex-1">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="px-3 py-1 rounded-full bg-<?= $color; ?>-50 border <?= $text_color; ?>/90 font-semibold text-sm">
                            <?= htmlspecialchars($grade['grade']); ?>
                        </div>
                        <div class="text-lg font-bold text-gray-800"><?= htmlspecialchars($grade['label']); ?></div>
                    </div>
                    <div class="mt-1 text-sm text-gray-500"><?= htmlspecialchars($grade['description']); ?></div>
                </div>

                <div class="text-right">
                    <div class="text-xs text-gray-400">Score global</div>
                    <div class="mt-1 text-xl font-extrabold <?= $text_color; ?>"><?= $displayPerc; ?>%</div>
                </div>
            </div>

            <!-- Barre de progression large -->
            <div class="mt-4">
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full" style="width: <?= $percentage; ?>%; background: linear-gradient(90deg, rgba(52,211,153,1) 0%, rgba(5,150,105,1) 100%); transition: width .8s ease;"></div>
                </div>
                <div class="mt-2 text-xs text-gray-400">Évaluation basée sur des critères nutritionnels généraux</div>
            </div>
        </div>
    </div>

    <!-- Grille des critères améliorée -->
    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-3">
        <?php
        $criteria = [
[
    'label' => 'Protéines',
    'value' => $macros['proteins_100g'] ?? $macros['proteines_100g'] ?? 0,
    'unit' => 'g/100g',
    'good' => ($macros['proteins_100g'] ?? $macros['proteines_100g'] ?? 0) >= 15,
    'threshold' => '≥15g',
],
[
    'label' => 'Fibres',
    'value' => $macros['fiber_100g'] ?? $macros['fibres_100g'] ?? 0,
    'unit' => 'g/100g',
    'good' => ($macros['fiber_100g'] ?? $macros['fibres_100g'] ?? 0) >= 3,
    'threshold' => '≥3g',
],
[
    'label' => 'Graisses sat.',
    'value' => $macros['saturated-fat_100g'] ?? $macros['acides_gras_satures_100g'] ?? 0,
    'unit' => 'g/100g',
    'good' => ($macros['saturated-fat_100g'] ?? $macros['acides_gras_satures_100g'] ?? 0) <= 3,
    'threshold' => '≤3g',
],
[
    'label' => 'Sucres',
    'value' => $macros['sugars_100g'] ?? $macros['sucres_100g'] ?? 0,
    'unit' => 'g/100g',
    'good' => ($macros['sugars_100g'] ?? $macros['sucres_100g'] ?? 0) <= 5,
    'threshold' => '≤5g',
],
        ];

foreach ($criteria as $criterion)
{
    $val = (float)$criterion['value'];
    // normaliser l'affichage (0..100) pour la mini-barre : on considère plafond 30 pour les macros positives et 10 pour sucres/gr sat
    $cap = in_array($criterion['label'], ['Protéines', 'Fibres']) ? 30 : 10;
    $barWidth = min(100, ($val / $cap) * 100);
    $isGood = $criterion['good'];
    ?>
        <div class="p-3 rounded-xl border <?= $isGood ? 'border-green-100 bg-green-50/40' : 'border-red-100 bg-red-50/40'; ?> flex items-center justify-between gap-3 transition-shadow hover:shadow-md">
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full <?= $isGood ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                        <div class="text-sm font-medium text-gray-700"><?= htmlspecialchars($criterion['label']); ?></div>
                    </div>
                    <div class="text-sm font-semibold <?= $isGood ? 'text-green-800' : 'text-red-700'; ?>">
                        <?= number_format($val, 1); ?> <?= htmlspecialchars($criterion['unit']); ?>
                    </div>
                </div>

                <!-- Mini-barre visuelle -->
                <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full" style="width: <?= $barWidth; ?>%; <?= $isGood ? 'background: linear-gradient(90deg,#34d399,#059669);' : 'background: linear-gradient(90deg,#f87171,#ef4444);'; ?> transition: width .6s ease;"></div>
                </div>

                <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                    <div><?= $isGood ? '✓ Conforme' : '⚠ Attention'; ?></div>
                    <div class="italic"><?= htmlspecialchars($criterion['threshold']); ?></div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Note informative -->
    <div class="mt-4 p-3 rounded-xl bg-gradient-to-r from-white to-gray-50 border border-gray-100 flex items-start gap-3">
        <div class="mt-0.5 text-gray-500">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 9v2m0 4h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="text-xs text-gray-600">
            <strong>Note :</strong> Ce score est un indicateur éducatif basé sur des critères nutritionnels généraux et simplifiés. Il ne remplace pas un avis médical ou diététique professionnel.
        </div>
    </div>
</div>
