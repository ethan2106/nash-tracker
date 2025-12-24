<?php
/**
 * Cercle de progression SVG - Réutilisable.
 *
 * Props PHP:
 * - $size: string (sm, md, lg, xl) ou int en pixels
 * - $thickness: int (épaisseur du cercle, default: 10)
 * - $showPercentage: bool (afficher % au centre, default: true)
 * - $showLabel: bool (afficher label sous %, default: true)
 * - $label: string (label sous le %, default: "de l'objectif")
 * - $celebrationEmoji: string (emoji si 100%, default: 🎉)
 *
 * Props Alpine.js:
 * - progressPercentage: number (0-100+)
 * - progressColor: string (couleur hex ou CSS)
 */

// Tailles prédéfinies
$sizes = [
    'sm' => 96,   // w-24
    'md' => 128,  // w-32
    'lg' => 192,  // w-48
    'xl' => 256,  // w-64
];

$sizeValue = is_numeric($size ?? null) ? (int)$size : ($sizes[$size ?? 'lg'] ?? 192);
$thickness = $thickness ?? 10;
$showPercentage = $showPercentage ?? true;
$showLabel = $showLabel ?? true;
$label = $label ?? "de l'objectif";
$celebrationEmoji = $celebrationEmoji ?? '🎉';

// Calculs SVG
$viewBox = 120;
$center = 60;
$radius = 50;
$circumference = 2 * M_PI * $radius;
?>

<div class="relative">
    <div class="relative" style="width: <?= $sizeValue; ?>px; height: <?= $sizeValue; ?>px;">
        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 <?= $viewBox; ?> <?= $viewBox; ?>">
            <!-- Cercle de fond -->
            <circle cx="<?= $center; ?>" cy="<?= $center; ?>" r="<?= $radius; ?>" 
                    stroke="#e5e7eb" stroke-width="<?= $thickness; ?>" fill="none"/>
            <!-- Cercle de progression avec animation -->
            <circle cx="<?= $center; ?>" cy="<?= $center; ?>" r="<?= $radius; ?>" 
                    stroke="currentColor" stroke-width="<?= $thickness; ?>" fill="none"
                    stroke-linecap="round" 
                    :stroke="progressColor"
                    :stroke-dasharray="`<?= $circumference; ?>`"
                    :stroke-dashoffset="`<?= $circumference; ?> * (1 - progressPercentage / 100)`"
                    class="transition-all duration-1000"
                    style="filter: drop-shadow(0 0 6px currentColor);"/>
        </svg>
        
        <?php if ($showPercentage)
        { ?>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <div class="text-4xl font-bold" :style="`color: ${progressColor}`" x-text="`${Math.round(progressPercentage)}%`"></div>
                <?php if ($showLabel)
                { ?>
                <div class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($label); ?></div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
    
    <!-- Emoji de célébration si objectif atteint -->
    <div x-show="progressPercentage >= 100" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-0"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute -top-2 -right-2 text-4xl animate-bounce">
        <?= $celebrationEmoji; ?>
    </div>
</div>
