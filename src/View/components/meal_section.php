<?php
/**
 * Affiche une section repas factorisée.
 * @param array $meals Liste des repas pour ce type (avec aliments inclus)
 * @param string $type Nom du type (petit-dejeuner, dejeuner, gouter, diner, en-cas)
 * @param array $config Config d'affichage (icône, couleur, label, id)
 * @param string $csrf_token
 * @param bool $isToday Si c'est aujourd'hui (pour activer/désactiver l'édition)
 */

// Inclure les helpers pour les composants de qualité alimentaire
require_once __DIR__ . '/../../Helper/food_quality_helpers.php';

function renderMealSection($meals, $type, $config, $csrf_token, $isToday = true)
{
    ?>
    <div class="bg-white/70 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/30 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-2xl bg-gradient-to-br <?= $config['bg']; ?> <?= $config['color']; ?>">
                    <i class="fa-solid <?= $config['icon']; ?> text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?= $config['label']; ?></h3>
                    <p class="text-sm text-gray-600">Gérez vos aliments pour ce repas</p>
                </div>
            </div>
            <?php if ($isToday) { ?>
            <button @click="addFood('<?= htmlspecialchars($type); ?>')" class="px-6 py-3 <?= $config['btn']; ?> text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Ajouter un aliment</span>
            </button>
            <?php } ?>
        </div>
        <div id="<?= $config['id']; ?>" class="space-y-4">
            <?php
            // Filtrer les repas qui ont au moins un aliment
            $validMeals = array_filter($meals, function ($meal) {
                return ($meal['aliment_count'] ?? 0) > 0;
            });
    ?>
            <?php if (!empty($validMeals)) { ?>
                <?php foreach ($validMeals as $meal) { ?>
                <div class="flex items-center justify-between <?= $config['bg']; ?> rounded-lg px-4 py-3 border <?= $config['border']; ?>">
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">
                            <?= htmlspecialchars($meal['aliment_count'] ?? 0); ?> aliment<?= ($meal['aliment_count'] ?? 0) > 1 ? 's' : ''; ?>
                        </span>
                        <span class="text-sm font-semibold <?= $config['color']; ?>">
                            <?= htmlspecialchars(number_format($meal['calories_total'] ?? 0, 0)); ?> kcal
                        </span>
                        <?php if (($meal['aliment_count'] ?? 0) > 1 && $isToday) { ?>
                        <button type="button" @click="confirmDeleteMeal(<?= htmlspecialchars($meal['id']); ?>, '<?= htmlspecialchars($type); ?>')" class="bg-red-100 hover:bg-red-200 text-red-600 px-2 py-1 rounded ml-2" title="Supprimer le repas complet">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <?php } ?>
                    </div>
                </div>
                <?php
                // Afficher la liste des aliments pour ce repas
                $aliments = $meal['aliments'] ?? [];
                if (!empty($aliments)) {
                    echo '<div class="flex flex-col gap-4 mt-2">';
                    foreach ($aliments as $aliment) {
                        $image = !empty($aliment['image_path']) ? $aliment['image_path'] : '';

                        // S'assurer que le chemin d'image commence par /
                        if (!empty($image) && !str_starts_with($image, '/')) {
                            $image = '/' . $image;
                        }

                        $hasImage = !empty($image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image);

                        // Si pas d'image locale, chercher dans public/images/foods
                        if (!$hasImage && !empty($aliment['openfoodfacts_id'])) {
                            $foodImagePath = __DIR__ . '/../../public/images/foods/' . $aliment['openfoodfacts_id'] . '.jpg';
                            if (file_exists($foodImagePath)) {
                                $image = '/images/foods/' . $aliment['openfoodfacts_id'] . '.jpg';
                                $hasImage = true;
                            }
                        }

                        $qte = (float)$aliment['quantite_g'];
                        $cal = round((float)$aliment['calories_100g'] * $qte / 100, 1);
                        $prot = round((float)$aliment['proteines_100g'] * $qte / 100, 1);
                        $gluc = round((float)$aliment['glucides_100g'] * $qte / 100, 1);
                        $sucre = round((float)$aliment['sucres_100g'] * $qte / 100, 1);
                        $lip = round((float)$aliment['lipides_100g'] * $qte / 100, 1);
                        $ags = round((float)$aliment['acides_gras_satures_100g'] * $qte / 100, 1);
                        $fibres = round((float)$aliment['fibres_100g'] * $qte / 100, 1);
                        $sel = round((float)($aliment['sel_100g'] ?? 0) * $qte / 100, 2);
                        $nutriscore = !empty($aliment['nutriscore']) ? strtoupper($aliment['nutriscore']) : 'N/A';
                        $marque = '';
                        if (!empty($aliment['autres_infos'])) {
                            $infos = json_decode($aliment['autres_infos'], true);
                            $marque = $infos['marque'] ?? '';
                        }
                        echo '<div class="bg-white/95 rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 group relative overflow-hidden">';
                        // Bouton supprimer toujours visible en haut à droite
                        echo '<button type="button" @click="confirmDeleteFood(' . $aliment['id'] . ', ' . $meal['id'] . ')" class="absolute top-4 right-4 z-10 bg-red-500 hover:bg-red-600 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-110" title="Supprimer cet aliment">';
                        echo '<i class="fa-solid fa-trash text-sm"></i>';
                        echo '</button>';

                        echo '<div class="flex items-start gap-4 pr-16">'; // Espace pour le bouton supprimer
                        if ($hasImage) {
                            echo '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($aliment['nom']) . '" class="w-20 h-20 object-cover rounded-xl shadow-md border-2 border-white flex-shrink-0">';
                        } else {
                            echo '<div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl shadow-md border-2 border-white flex items-center justify-center flex-shrink-0">';
                            echo '<i class="fa-solid fa-utensils text-blue-600 text-3xl"></i>';
                            echo '</div>';
                        }
                        echo '<div class="flex-1 min-w-0">';
                        // Calculer et afficher le badge de qualité
                        $nutriments = [
                            'proteins_100g' => $aliment['proteines_100g'] ?? 0,
                            'saturated-fat_100g' => $aliment['acides_gras_satures_100g'] ?? 0,
                            'fiber_100g' => $aliment['fibres_100g'] ?? 0,
                            'sugars_100g' => $aliment['sucres_100g'] ?? 0,
                            'energy-kcal_100g' => $aliment['calories_100g'] ?? 0,
                        ];
                        $qualityData = getFoodQualityData($nutriments);
                        echo '<div class="flex items-center gap-3 mb-2">';
                        echo '<h4 class="font-bold text-gray-800 text-xl leading-tight">' . htmlspecialchars($aliment['nom']) . '</h4>';
                        echo renderFoodQualityBadgeFromData($nutriments, 'sm');
                        echo '</div>';
                        if ($marque) {
                            echo '<p class="text-sm text-gray-600 mb-2"><i class="fa-solid fa-building mr-1"></i>' . htmlspecialchars($marque) . '</p>';
                        }
                        echo '<div class="flex items-center gap-4 text-sm mb-4">';
                        echo '<div class="flex items-center gap-1">';
                            echo '<i class="fa-solid fa-weight-hanging ' . $config['color'] . '"></i>';
                            echo '<span class="font-semibold ' . $config['color'] . '">' . $qte . 'g</span>';
                            echo '</div>';
                            echo '<div class="flex items-center gap-1">';
                            echo '<i class="fa-solid fa-fire ' . $config['color'] . '"></i>';
                            echo '<span class="font-bold ' . $config['color'] . '">' . $cal . ' kcal</span>';
                            // Ajout du label rose avec lettre + pourcentage
                            $gradeInfo = $qualityData['grade'] ?? null;
                            if ($gradeInfo) {
                                echo '<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-pink-100 text-pink-800 text-xs font-bold">'
                                    . '<span class="mr-1">' . htmlspecialchars($gradeInfo['grade']) . '</span>'
                                    . (int)$gradeInfo['percentage'] . '%'
                                    . '</span>';
                            }
                            echo '</div>';
                            echo '</div>';

                            // Grille des macros : 2 lignes de 4 éléments chacune
                            echo '<div class="space-y-3">';
                            // Ligne 1 : 4 macros principales
                            echo '<div class="grid grid-cols-4 gap-2">';
                            echo '<div class="bg-yellow-50 rounded-lg p-2 text-center border border-yellow-100">';
                            echo '<div class="text-sm font-bold text-yellow-700">' . $cal . '</div>';
                            echo '<div class="text-xs text-yellow-600">Cal</div>';
                            echo '</div>';
                            echo '<div class="bg-purple-50 rounded-lg p-2 text-center border border-purple-100">';
                            echo '<div class="text-sm font-bold text-purple-700">' . $prot . 'g</div>';
                            echo '<div class="text-xs text-purple-600">Prot</div>';
                            echo '</div>';
                            echo '<div class="bg-blue-50 rounded-lg p-2 text-center border border-blue-100">';
                            echo '<div class="text-sm font-bold text-blue-700">' . $gluc . 'g</div>';
                            echo '<div class="text-xs text-blue-600">Gluc</div>';
                            echo '</div>';
                            echo '<div class="bg-orange-50 rounded-lg p-2 text-center border border-orange-100">';
                            echo '<div class="text-sm font-bold text-orange-700">' . $lip . 'g</div>';
                            echo '<div class="text-xs text-orange-600">Lip</div>';
                            echo '</div>';
                            echo '</div>';

                            // Ligne 2 : 4 macros secondaires
                            echo '<div class="grid grid-cols-4 gap-2">';
                            echo '<div class="bg-pink-50 rounded-lg p-2 text-center border border-pink-100">';
                            echo '<div class="text-sm font-bold text-pink-700">' . $sucre . 'g</div>';
                            echo '<div class="text-xs text-pink-600">Sucres</div>';
                            echo '</div>';
                            echo '<div class="bg-red-50 rounded-lg p-2 text-center border border-red-100">';
                            echo '<div class="text-sm font-bold text-red-700">' . $ags . 'g</div>';
                            echo '<div class="text-xs text-red-600">AGS</div>';
                            echo '</div>';
                            echo '<div class="bg-cyan-50 rounded-lg p-2 text-center border border-cyan-100">';
                            echo '<div class="text-sm font-bold text-cyan-700">' . $sel . 'g</div>';
                            echo '<div class="text-xs text-cyan-600">Sel</div>';
                            echo '</div>';
                            echo '<div class="bg-green-50 rounded-lg p-2 text-center border border-green-100">';
                            echo '<div class="text-sm font-bold text-green-700">' . $fibres . 'g</div>';
                            echo '<div class="text-xs text-green-600">Fibres</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';

                            // Nutriscore
                            if ($nutriscore !== 'N/A') {
                                echo '<div class="flex justify-center mt-3">';
                                echo '<div class="flex items-center gap-2">';
                                echo '<span class="text-sm text-gray-600">Nutriscore:</span>';
                                echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold ';
                                switch ($nutriscore) {
                                    case 'A':
                                        echo 'bg-green-100 text-green-800 border border-green-200';
                                        break;
                                    case 'B':
                                        echo 'bg-lime-100 text-lime-800 border border-lime-200';
                                        break;
                                    case 'C':
                                        echo 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                        break;
                                    case 'D':
                                        echo 'bg-orange-100 text-orange-800 border border-orange-200';
                                        break;
                                    case 'E':
                                        echo 'bg-red-100 text-red-800 border border-red-200';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-700 border border-gray-200';
                                }
                                echo '"><i class="fa-solid fa-star mr-1"></i>' . $nutriscore . '</span>';
                                echo '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    ?>
                <?php } ?>
            <?php } else { ?>
            <div class="text-center text-gray-500 py-4">
                <i class="fa-solid <?= $config['empty_icon']; ?> text-3xl mb-2"></i>
                <p><?= $config['empty_text']; ?></p>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
