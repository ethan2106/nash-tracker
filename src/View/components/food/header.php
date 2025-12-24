<?php

/**
 * Composant: Header Page Food (Recherche Aliments).
 *
 * @description En-tête avec titre, icône et description
 * @var string $mealType Type de repas actuel
 * @var string $currentMealLabel Label du repas actuel
 */

declare(strict_types=1);
?>
<!-- ============================================================
     HEADER PAGE RECHERCHE ALIMENTS
     - Titre avec icône
     - Description et indication du repas en cours
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex items-center gap-4">
        <div class="text-green-500 text-4xl">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Recherche d'Aliments</h1>
            <p class="text-gray-600">Recherchez et ajoutez des aliments à votre catalogue personnel</p>
            <?php if (!empty($currentMealLabel))
            { ?>
            <p class="text-sm text-green-600 mt-1">
                <i class="fa-solid fa-utensils mr-1" aria-hidden="true"></i>
                Ajout au repas : <strong><?= htmlspecialchars($currentMealLabel); ?></strong>
            </p>
            <?php } ?>
        </div>
    </div>
</div>
