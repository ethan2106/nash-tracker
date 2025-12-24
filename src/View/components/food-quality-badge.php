<?php
/**
 * Composant Badge de Notation Alimentaire (créatif)
 * Badge avec anneau de progression animé, lettre, pourcentage et tooltip riche.
 *
 * @param array $grade Données de notation depuis FoodQualityService
 * @param string $size Taille du badge (sm, md, lg)
 * @param bool $showTooltip Afficher le tooltip détaillé
 * @param int|null $foodId ID de l'aliment (pour ID stable du gradient SVG)
 * @param array|null $foodDetails Détails nutritionnels de l'aliment (optionnel, pour tooltip enrichi)
 */

// Extraction des variables pour sécurité
$grade = $grade ?? [];
$size = $size ?? 'md';
$showTooltip = $showTooltip ?? true;
$foodId = $foodId ?? null;
$foodDetails = $foodDetails ?? null;

if (empty($grade))
{
    return;
}

// Classes CSS selon la taille (container)
$containerSizes = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-base',
];

// Taille du disque + SVG selon la taille
$circleMap = [
    'sm' => ['box' => 22, 'r' => 8, 'wrapper' => 'w-6 h-6 text-xs'],
    'md' => ['box' => 24, 'r' => 9, 'wrapper' => 'w-7 h-7 text-sm'],
    'lg' => ['box' => 28, 'r' => 10, 'wrapper' => 'w-8 h-8 text-base'],
];
$circleConf = $circleMap[$size] ?? $circleMap['md'];

$containerClasses = $containerSizes[$size] ?? $containerSizes['md'];

// Données pour l'anneau de progression
$percentage = (int)($grade['percentage'] ?? 0);
$radius = (float)$circleConf['r'];
$circumference = 2 * M_PI * $radius;
$offset = $circumference * (1 - max(0, min(100, $percentage)) / 100);

// Palette de couleurs améliorée (override)
$palette = $palette ?? 'vivid'; // vivid | legacy
$letter = strtoupper($grade['grade'] ?? 'E');
$colorMap = [
    'A' => ['bg' => 'bg-green-50',    'text' => 'text-green-700',    'border' => 'border-green-200',    'stops' => ['#16a34a', '#15803d']], // vert cohérent avec text-green-700
    'B' => ['bg' => 'bg-lime-50',     'text' => 'text-lime-700',     'border' => 'border-lime-200',     'stops' => ['#65a30d', '#4d7c0f']], // lime-600 → lime-700 pour cohérence
    'C' => ['bg' => 'bg-yellow-50',   'text' => 'text-yellow-700',   'border' => 'border-yellow-200',   'stops' => ['#ca8a04', '#a16207']], // yellow-600 → yellow-700 pour cohérence
    'D' => ['bg' => 'bg-orange-50',  'text' => 'text-orange-700',  'border' => 'border-orange-200',  'stops' => ['#ea580c', '#c2410c']], // orange-600 → orange-700 pour cohérence
    'E' => ['bg' => 'bg-red-50',      'text' => 'text-red-700',      'border' => 'border-red-200',      'stops' => ['#dc2626', '#b91c1c']], // red-600 → red-700 pour cohérence
];
$preset = $colorMap[$letter] ?? $colorMap['E'];
$bgClass = $preset['bg'];
$textClass = $preset['text'];
$borderClass = $preset['border'] ?? 'border-gray-200';
$gradStops = $preset['stops'];
if ($palette === 'legacy')
{
    // Mode compatibilité avec anciennes classes CSS (échappement sécurisé)
    $bgClass = htmlspecialchars($grade['bg_color'] ?? $bgClass);
    $textClass = htmlspecialchars($grade['text_color'] ?? $textClass);
    $borderClass = htmlspecialchars($grade['border_color'] ?? $borderClass);
    $gradStops = null; // Désactiver gradients SVG en mode legacy
}

// ID stable pour le gradient SVG (toujours valide pour XML)
if ($foodId !== null)
{
    // Utiliser foodId si disponible, s'assurer qu'il commence par une lettre
    $gradId = 'fqb_' . (is_numeric($foodId) ? 'id_' . $foodId : $foodId);
} else
{
    // Générer un hash unique et positif pour grade + percentage
    $hashInput = ($grade['grade'] ?? 'E') . '_' . $percentage;
    $gradId = 'fqb_' . sprintf('%u', crc32($hashInput));
}

// Mapping pour pictogrammes et libellés
$iconMap = [
    'A' => 'fa-circle-check',
    'B' => 'fa-thumbs-up', // changé pour être plus positif
    'C' => 'fa-triangle-exclamation',
    'D' => 'fa-triangle-exclamation',
    'E' => 'fa-skull-crossbones',
];
$labelMap = [
    'A' => 'Excellent pour le foie',
    'B' => 'Très bien',
    'C' => 'Moyen',
    'D' => 'À limiter',
    'E' => 'À éviter absolument',
];
$icon = $iconMap[$letter] ?? 'fa-skull-crossbones';
$shortLabel = !empty($grade['label']) ? $grade['label'] : ($labelMap[$letter] ?? 'À éviter');
$description = $grade['description'] ?? 'Évaluation nutritionnelle de cet aliment';

// Conseils NAFLD selon la note
$nafldAdvice = [
    'A' => 'Parfait pour votre foie ! Riche en nutriments protecteurs et pauvre en sucres/fructose.',
    'B' => 'Bon choix pour la santé hépatique. Faible impact sur la stéatose.',
    'C' => 'À consommer avec modération. Surveillez votre consommation globale.',
    'D' => 'Peut favoriser la stéatose hépatique. Limitez fortement la consommation.',
    'E' => '⚠️ Très mauvais pour le foie ! Évitez autant que possible - riche en sucres et graisses saturées.',
];
$advice = $nafldAdvice[$letter] ?? $nafldAdvice['E'];

// Préparer les données nutritionnelles pour le tooltip (si disponibles)
$nutritionDetails = [];
if ($foodDetails)
{
    if (isset($foodDetails['proteines_100g']))
    {
        $val = (float)$foodDetails['proteines_100g'];
        $status = $val >= 15 ? 'good' : ($val >= 7 ? 'ok' : 'low');
        $nutritionDetails['proteins'] = ['value' => $val, 'status' => $status, 'label' => 'Protéines'];
    }
    if (isset($foodDetails['fibres_100g']))
    {
        $val = (float)$foodDetails['fibres_100g'];
        $status = $val >= 3 ? 'good' : ($val >= 1.5 ? 'ok' : 'low');
        $nutritionDetails['fiber'] = ['value' => $val, 'status' => $status, 'label' => 'Fibres'];
    }
    if (isset($foodDetails['acides_gras_satures_100g']))
    {
        $val = (float)$foodDetails['acides_gras_satures_100g'];
        $status = $val <= 3 ? 'good' : ($val <= 7 ? 'ok' : 'bad');
        $nutritionDetails['satfat'] = ['value' => $val, 'status' => $status, 'label' => 'Graisses sat.'];
    }
    if (isset($foodDetails['sucres_100g']))
    {
        $val = (float)$foodDetails['sucres_100g'];
        $status = $val <= 5 ? 'good' : ($val <= 12 ? 'ok' : 'bad');
        $nutritionDetails['sugar'] = ['value' => $val, 'status' => $status, 'label' => 'Sucres'];
    }
}
?>

<!-- Badge de notation avec tooltip Alpine.js -->
<span class="relative inline-block" 
      x-data="{ showTip: false }" 
      @mouseenter="showTip = true" 
      @mouseleave="showTip = false"
      @focus="showTip = true"
      @blur="showTip = false"
      tabindex="0"
      role="button"
      aria-describedby="<?= $gradId; ?>-tooltip">
    
    <!-- Badge principal -->
    <span class="inline-flex items-center gap-2 rounded-full font-semibold shadow-sm border border-current/20 <?= $bgClass; ?> <?= $textClass; ?> <?= $containerClasses; ?> transition-all hover:scale-105 cursor-help">

        <!-- Disque avec anneau de progression -->
        <span class="relative inline-flex items-center justify-center rounded-full bg-white/95 ring-1 ring-current ring-offset-1 ring-offset-transparent <?= $circleConf['wrapper']; ?> font-extrabold">
            <!-- Anneau SVG avec animation d'entrée -->
            <svg width="<?= $circleConf['box']; ?>" height="<?= $circleConf['box']; ?>" viewBox="0 0 <?= $circleConf['box']; ?> <?= $circleConf['box']; ?>" class="absolute -rotate-90">
                <?php $cx = $circleConf['box'] / 2;
$cy = $circleConf['box'] / 2; ?>
                <?php if ($gradStops)
                { ?>
                <defs>
                    <linearGradient id="<?= $gradId; ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="<?= $gradStops[0]; ?>" />
                        <stop offset="100%" stop-color="<?= $gradStops[1]; ?>" />
                    </linearGradient>
                </defs>
                <?php } ?>
                <!-- Fond de l'anneau -->
                <circle cx="<?= $cx; ?>" cy="<?= $cy; ?>" r="<?= $radius; ?>" class="opacity-20" stroke="currentColor" stroke-width="3" fill="none"></circle>
                <!-- Progression avec animation (fallback graceful pour navigateurs sans <animate>) -->
                <circle cx="<?= $cx; ?>" cy="<?= $cy; ?>" r="<?= $radius; ?>" 
                        stroke="<?= $gradStops ? 'url(#' . $gradId . ')' : 'currentColor'; ?>" 
                        stroke-width="3" 
                        fill="none"
                        stroke-linecap="round"
                        style="stroke-dasharray: <?= round($circumference, 2); ?>; stroke-dashoffset: <?= round($offset, 2); ?>;"
                        data-progress-offset="<?= round($offset, 2); ?>"
                        data-progress-circumference="<?= round($circumference, 2); ?>">
                    <!-- Animation SVG avec fallback graceful (affiche progression finale si <animate> non supporté) -->
                    <animate attributeName="stroke-dashoffset" 
                             from="<?= round($circumference, 2); ?>" 
                             to="<?= round($offset, 2); ?>" 
                             dur="0.8s" 
                             fill="freeze" 
                             calcMode="spline"
                             keySplines="0.4 0 0.2 1"/>
                </circle>
            </svg>
            <!-- Lettre avec fade-in -->
            <span class="relative z-10 <?= $textClass; ?> animate-[fqb-fade_0.5s_ease-out_0.3s_both]">
                <?= htmlspecialchars($grade['grade']); ?>
            </span>
        </span>

        <!-- Pourcentage + libellé + pictogramme -->
        <span class="inline-flex items-center gap-1 tracking-tight">
            <i class="fa-solid <?= $icon; ?>" aria-hidden="true"></i>
            <span class="font-bold"><?= $percentage; ?>%</span>
            <span class="opacity-80 font-medium"><?= htmlspecialchars($shortLabel); ?></span>
        </span>
    </span>

    <!-- Tooltip riche -->
    <?php if ($showTooltip)
    { ?>
    <div x-show="showTip"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         x-cloak
         id="<?= $gradId; ?>-tooltip"
         role="tooltip"
         class="absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 w-72 p-4 rounded-xl shadow-xl border <?= $borderClass; ?> bg-white/95 backdrop-blur-sm text-sm text-gray-700">
        
        <!-- Flèche du tooltip -->
        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px">
            <div class="border-8 border-transparent border-t-white"></div>
        </div>
        
        <!-- Header du tooltip -->
        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
            <div class="flex items-center justify-center w-10 h-10 rounded-full <?= $bgClass; ?> <?= $textClass; ?> font-bold text-lg">
                <?= htmlspecialchars($grade['grade']); ?>
            </div>
            <div>
                <div class="font-bold <?= $textClass; ?>"><?= htmlspecialchars($shortLabel); ?> (<?= $percentage; ?>%)</div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars($description); ?></div>
            </div>
        </div>
        
        <!-- Détails nutritionnels (si disponibles) -->
        <?php if (!empty($nutritionDetails))
        { ?>
        <div class="grid grid-cols-2 gap-2 mb-3 pb-3 border-b border-gray-100">
            <?php foreach ($nutritionDetails as $key => $nutrient)
            {
                $statusColors = [
                    'good' => 'text-emerald-600 bg-emerald-50',
                    'ok' => 'text-amber-600 bg-amber-50',
                    'low' => 'text-gray-500 bg-gray-50',
                    'bad' => 'text-rose-600 bg-rose-50',
                ];
                $statusIcons = [
                    'good' => 'fa-check',
                    'ok' => 'fa-minus',
                    'low' => 'fa-arrow-down',
                    'bad' => 'fa-xmark',
                ];
                $color = $statusColors[$nutrient['status']] ?? $statusColors['ok'];
                $statusIcon = $statusIcons[$nutrient['status']] ?? 'fa-minus';
                ?>
            <div class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $color; ?>">
                <i class="fa-solid <?= $statusIcon; ?> text-xs" aria-hidden="true"></i>
                <span class="text-xs">
                    <span class="font-medium"><?= htmlspecialchars($nutrient['label']); ?></span>
                    <span class="opacity-75"><?= number_format($nutrient['value'], 1); ?>g</span>
                </span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        
        <!-- Conseil NAFLD -->
        <div class="flex items-start gap-2">
            <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5" aria-hidden="true"></i>
            <p class="text-xs text-gray-600 leading-relaxed">
                <span class="font-semibold">Conseil NAFLD :</span> <?= htmlspecialchars($advice); ?>
            </p>
        </div>
    </div>
    <?php } ?>
</span>

<!-- Animation keyframes -->
<style>
@keyframes fqb-fade {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}
[x-cloak] { display: none !important; }
</style>
