<?php

/**
 * Composant: Card Nutriment individuel.
 *
 * @description Affiche un nutriment avec icône, valeur, barre de progression
 * @param string $name       Nom du nutriment (ex: "Protéines")
 * @param string $icon       Classe FontAwesome (ex: "fa-drumstick-bite")
 * @param string $colorFrom  Couleur dégradé début (ex: "red-400")
 * @param string $colorTo    Couleur dégradé fin (ex: "red-500")
 * @param float  $value      Valeur actuelle
 * @param string $unit       Unité (ex: "g", "mg", "kcal")
 * @param float  $goal       Objectif (pour la barre de progression)
 * @param string $goalType   Type: "min", "max", ou "target"
 * @param string $alpineColor Méthode Alpine.js pour la couleur (ex: "getProteinesColor()")
 */

declare(strict_types=1);

/**
 * Render une carte nutriment.
 *
 * @param array{
 *     name: string,
 *     icon: string,
 *     colorFrom: string,
 *     colorTo: string,
 *     value: float,
 *     unit: string,
 *     goal: float,
 *     goalType: string,
 *     alpineColor: string,
 *     id: string
 * } $nutriment Configuration du nutriment
 */
function renderNutrimentCard(array $nutriment): void
{
    $name = $nutriment['name'];
    $icon = $nutriment['icon'];
    $colorFrom = $nutriment['colorFrom'];
    $colorTo = $nutriment['colorTo'];
    $value = $nutriment['value'];
    $unit = $nutriment['unit'];
    $goal = $nutriment['goal'];
    $goalType = $nutriment['goalType'];
    $alpineColor = $nutriment['alpineColor'];
    $id = $nutriment['id'];

    $percentage = $goal > 0 ? min(100, ($value / $goal) * 100) : 0;
    $goalLabel = match ($goalType)
    {
        'min' => 'Min',
        'max' => 'Max',
        default => 'Objectif',
    };
    $goalColorClass = $goalType === 'max' ? 'text-red-600' : 'text-green-600';
    ?>
    <!-- Card <?= $name; ?> -->
    <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <!-- Icône + Nom -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-<?= $colorFrom; ?> to-<?= $colorTo; ?> rounded-lg flex items-center justify-center">
                    <i class="fa-solid <?= $icon; ?> text-white text-sm"></i>
                </div>
                <span class="font-semibold text-gray-700"><?= $name; ?></span>
            </div>
            <!-- Valeur -->
            <div class="text-right">
                <div class="text-xl font-bold" :class="<?= $alpineColor; ?>" id="<?= $id; ?>">
                    <?= formatNumber($value, $unit === 'mg' ? 0 : 1); ?>
                </div>
                <div class="text-xs text-gray-500"><?= $unit; ?></div>
            </div>
        </div>
        
        <!-- Barre de progression -->
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-gradient-to-r from-<?= $colorFrom; ?> to-<?= $colorTo; ?> h-2 rounded-full transition-all duration-300"
                 style="width: <?= $percentage; ?>%">
            </div>
        </div>
        
        <!-- Objectif -->
        <div class="text-xs text-gray-500 mt-1">
            <span class="<?= $goalColorClass; ?>">
                <?= $goalLabel; ?>: <?= formatNumber($goal, $unit === 'mg' ? 0 : 0); ?><?= $unit; ?>
            </span>
        </div>
    </div>
    <?php
}
