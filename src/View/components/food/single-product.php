<?php

/**
 * Composant: Affichage produit unique (code-barre).
 * @description Affiche les détails d'un produit trouvé par code-barre
 * @requires Alpine.js - Variables: openQuantityModal(), saveFood()
 * @var array|null $singleProduct Produit trouvé par code-barre
 * @var string $mealType Type de repas
 * @var string $currentMealLabel Label du repas
 */

declare(strict_types=1);

if (!$singleProduct)
{
    return;
}
?>
<!-- ========== PRODUIT UNIQUE (CODE-BARRE) ========== -->
<div class="space-y-4">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Produit trouvé</h2>

    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6">
        <div class="flex items-start gap-6">
            <!-- Image du produit -->
            <div class="flex-shrink-0">
                <?php if (!empty($singleProduct['image']))
                { ?>
                <img src="<?= htmlspecialchars($singleProduct['image']); ?>"
                     alt="<?= htmlspecialchars($singleProduct['name']); ?>"
                     class="w-32 h-32 object-cover rounded-xl shadow-md">
                <?php } else
                { ?>
                <div class="w-32 h-32 bg-gray-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-utensils text-gray-400 text-3xl"></i>
                </div>
                <?php } ?>
            </div>

            <!-- Infos détaillées du produit -->
            <div class="flex-1">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-2xl font-bold text-gray-800">
                                <?= htmlspecialchars($singleProduct['name']); ?>
                            </h3>
                            <?= renderFoodQualityBadgeFromData($singleProduct['nutriments'] ?? [], 'md'); ?>
                        </div>
                        <?php if (!empty($singleProduct['brands']))
                        { ?>
                        <p class="text-lg text-gray-600 mb-2">Marque: <?= htmlspecialchars($singleProduct['brands']); ?></p>
                        <?php } ?>
                        <p class="text-sm text-gray-500">Code-barre: <?= htmlspecialchars($singleProduct['barcode']); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fa-solid fa-check-circle mr-1"></i>Produit trouvé
                        </span>
                    </div>
                </div>

                <!-- Valeurs nutritionnelles détaillées -->
                <?php if (!empty($singleProduct['nutriments']))
                { ?>
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Valeurs nutritionnelles (pour 100g)</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4">
                        <?php
                        $nutrients = [
                            'energy-kcal' => ['label' => 'Calories', 'unit' => 'kcal', 'color' => 'yellow'],
                            'proteins' => ['label' => 'Protéines', 'unit' => 'g', 'color' => 'purple'],
                            'carbohydrates' => ['label' => 'Glucides', 'unit' => 'g', 'color' => 'blue'],
                            'sugars' => ['label' => 'Sucres', 'unit' => 'g', 'color' => 'pink'],
                            'fat' => ['label' => 'Lipides', 'unit' => 'g', 'color' => 'orange'],
                            'saturated-fat' => ['label' => 'Graisses sat.', 'unit' => 'g', 'color' => 'red'],
                            'fiber' => ['label' => 'Fibres', 'unit' => 'g', 'color' => 'green'],
                            'salt' => ['label' => 'Sel', 'unit' => 'g', 'color' => 'gray'],
                        ];

                    foreach ($nutrients as $key => $config)
                    {
                        $value = $singleProduct['nutriments'][$key . '_100g'] ?? $singleProduct['nutriments'][$key] ?? null;
                        if ($value !== null && $value > 0)
                        {
                            ?>
                        <div class="bg-<?= $config['color']; ?>-50 rounded-xl p-3 text-center">
                            <div class="text-lg font-bold text-<?= $config['color']; ?>-700">
                                <?= number_format($value, 1, ',', ' '); ?>
                                <span class="text-sm font-normal"><?= $config['unit']; ?></span>
                            </div>
                            <div class="text-xs text-<?= $config['color']; ?>-600"><?= $config['label']; ?></div>
                        </div>
                        <?php
                        }
                    }
                    ?>
                    </div>
                </div>
                <?php } ?>

                <!-- Informations supplémentaires -->
                <?php if (!empty($singleProduct['ingredients']) || !empty($singleProduct['categories']))
                { ?>
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <?php if (!empty($singleProduct['ingredients']))
                    { ?>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Ingrédients</h4>
                        <p class="text-gray-600 text-sm leading-relaxed"><?= htmlspecialchars($singleProduct['ingredients']); ?></p>
                    </div>
                    <?php } ?>

                    <?php if (!empty($singleProduct['categories']))
                    { ?>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Catégories</h4>
                        <p class="text-gray-600 text-sm"><?= htmlspecialchars($singleProduct['categories']); ?></p>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                    <?php
                    // Préparer les données du produit pour JavaScript
                    $productData = [
                        'name' => $singleProduct['name'],
                        'brands' => $singleProduct['brands'] ?? '',
                        'barcode' => $singleProduct['barcode'] ?? '',
                        'code' => $singleProduct['barcode'] ?? '',
                        'image' => $singleProduct['image'] ?? '',
                        'nutriments' => $singleProduct['nutriments'] ?? [],
                        'ingredients' => $singleProduct['ingredients'] ?? '',
                        'categories' => $singleProduct['categories'] ?? '',
                        'calories' => $singleProduct['nutriments']['energy-kcal_100g'] ?? $singleProduct['nutriments']['energy-kcal'] ?? 0,
                        'proteins' => $singleProduct['nutriments']['proteins_100g'] ?? $singleProduct['nutriments']['proteins'] ?? 0,
                        'carbs' => $singleProduct['nutriments']['carbohydrates_100g'] ?? $singleProduct['nutriments']['carbohydrates'] ?? 0,
                        'sugars' => $singleProduct['nutriments']['sugars_100g'] ?? $singleProduct['nutriments']['sugars'] ?? 0,
                        'fat' => $singleProduct['nutriments']['fat_100g'] ?? $singleProduct['nutriments']['fat'] ?? 0,
                        'saturatedFat' => $singleProduct['nutriments']['saturated-fat_100g'] ?? $singleProduct['nutriments']['saturated-fat'] ?? 0,
                    ];
$productJson = htmlspecialchars(json_encode($productData), ENT_QUOTES, 'UTF-8');
?>
                    <!-- Bouton Détails -->
                    <button @click="openDetailsModal(<?= $productJson; ?>)" 
                            class="px-6 py-3 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl font-semibold transition-colors">
                        <i class="fa-solid fa-eye mr-2"></i>Détails complets
                    </button>
                    <?php if ($mealType !== 'repas')
                    { ?>
                    <button @click="openQuantityModal(<?= $productJson; ?>)" 
                            class="px-6 py-3 bg-gradient-to-r from-sky-500 to-purple-500 hover:from-sky-600 hover:to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <i class="fa-solid fa-plus mr-2"></i>Ajouter au <?= htmlspecialchars($currentMealLabel); ?>
                    </button>
                    <?php } ?>
                    <button @click="saveFood(<?= $productJson; ?>)" 
                            class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-colors">
                        <i class="fa-solid fa-save mr-2"></i>Sauvegarder en base
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
