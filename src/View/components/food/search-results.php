<?php

/**
 * Composant: Résultats de recherche.
 * @description Affiche la liste des résultats de recherche alimentaire
 * @requires Alpine.js - Variables: openQuantityModal(), saveFood(), openDetailsModal()
 * @var array $results Résultats de recherche
 * @var string $query Terme recherché
 * @var string $error Message d'erreur éventuel
 * @var string $mealType Type de repas
 * @var string $currentMealLabel Label du repas
 */

declare(strict_types=1);
?>
<!-- ========== MESSAGE D'ERREUR ========== -->
<?php if ($error)
{ ?>
<div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="text-red-500">
            <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
        <p class="text-red-700"><?= htmlspecialchars($error); ?></p>
    </div>
</div>
<?php } ?>

<!-- ========== RÉSULTATS DE RECHERCHE ========== -->
<?php if (!empty($results))
{ ?>
<div class="space-y-4">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Résultats pour "<?= htmlspecialchars($query); ?>"</h2>

    <?php foreach ($results as $product)
    { ?>
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6 hover:shadow-xl transition-all duration-300">
        <div class="flex items-start gap-6">
            <!-- Image du produit -->
            <div class="flex-shrink-0">
                <?php if (!empty($product['image']))
                { ?>
                <img src="<?= htmlspecialchars($product['image']); ?>"
                     alt="<?= htmlspecialchars($product['name']); ?>"
                     class="w-24 h-24 object-cover rounded-xl shadow-md">
                <?php } else
                { ?>
                <div class="w-24 h-24 bg-gray-200 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-utensils text-gray-400 text-2xl"></i>
                </div>
                <?php } ?>
            </div>

            <!-- Infos produit -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-xl font-bold text-gray-800">
                        <?= htmlspecialchars($product['name']); ?>
                    </h3>
                    <?= renderFoodQualityBadgeFromData($product['nutriments'] ?? [], 'sm'); ?>
                </div>

                <?php if (!empty($product['brands']))
                { ?>
                <p class="text-gray-600 mb-3">Marque: <?= htmlspecialchars($product['brands']); ?></p>
                <?php } ?>

                <?php if (!empty($product['code']))
                { ?>
                <p class="text-sm text-gray-500 mb-3">Code-barre: <?= htmlspecialchars($product['code']); ?></p>
                <?php } ?>

                <!-- Valeurs nutritionnelles pour 100g -->
                <?php if (!empty($product['nutriments']))
                { ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <?php
                    $nutrients = [
                        'energy-kcal' => ['label' => 'Calories', 'unit' => 'kcal', 'color' => 'yellow'],
                        'proteins' => ['label' => 'Protéines', 'unit' => 'g', 'color' => 'purple'],
                        'carbohydrates' => ['label' => 'Glucides', 'unit' => 'g', 'color' => 'blue'],
                        'fat' => ['label' => 'Lipides', 'unit' => 'g', 'color' => 'orange'],
                        'saturated-fat' => ['label' => 'Graisses sat.', 'unit' => 'g', 'color' => 'red'],
                    ];

                    foreach ($nutrients as $key => $config)
                    {
                        $value = $product['nutriments'][$key . '_100g'] ?? $product['nutriments'][$key] ?? null;
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
                <?php } ?>

                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-3">
                    <?php
                    // Préparer les données du produit pour JavaScript
                    $productData = [
                        'name' => $product['name'],
                        'brands' => $product['brands'] ?? '',
                        'barcode' => $product['code'] ?? '',
                        'code' => $product['code'] ?? '',
                        'image' => $product['image'] ?? '',
                        'nutriments' => $product['nutriments'] ?? [],
                        'ingredients' => $product['ingredients'] ?? '',
                        'categories' => $product['categories'] ?? '',
                        'calories' => $product['nutriments']['energy-kcal_100g'] ?? $product['nutriments']['energy-kcal'] ?? 0,
                        'proteins' => $product['nutriments']['proteins_100g'] ?? $product['nutriments']['proteins'] ?? 0,
                        'carbs' => $product['nutriments']['carbohydrates_100g'] ?? $product['nutriments']['carbohydrates'] ?? 0,
                        'sugars' => $product['nutriments']['sugars_100g'] ?? $product['nutriments']['sugars'] ?? 0,
                        'fat' => $product['nutriments']['fat_100g'] ?? $product['nutriments']['fat'] ?? 0,
                        'saturatedFat' => $product['nutriments']['saturated-fat_100g'] ?? $product['nutriments']['saturated-fat'] ?? 0,
                    ];
        $productJson = htmlspecialchars(json_encode($productData), ENT_QUOTES, 'UTF-8');
        ?>
                    <!-- Bouton Détails -->
                    <button @click="openDetailsModal(<?= $productJson; ?>)" 
                            class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl font-semibold transition-colors">
                        <i class="fa-solid fa-eye mr-2"></i>Détails
                    </button>
                    <?php if ($mealType !== 'repas')
                    { ?>
                    <button @click="openQuantityModal(<?= $productJson; ?>)" 
                            class="px-4 py-2 bg-gradient-to-r from-sky-500 to-purple-500 hover:from-sky-600 hover:to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <i class="fa-solid fa-plus mr-2"></i>Ajouter au <?= htmlspecialchars($currentMealLabel); ?>
                    </button>
                    <?php } ?>
                    <button @click="saveFood(<?= $productJson; ?>)" 
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-colors">
                        <i class="fa-solid fa-save mr-2"></i>Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<?php } elseif (!empty($query))
{ ?>
<!-- État vide -->
<div class="text-center py-12">
    <div class="text-gray-400 text-6xl mb-4">
        <i class="fa-solid fa-search"></i>
    </div>
    <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun résultat trouvé</h3>
    <p class="text-gray-500">Essayez avec d'autres termes de recherche</p>
</div>
<?php } ?>
