<?php
require 'vendor/autoload.php';
$repo = new \App\Repository\FoodRepository();
$foods = $repo->searchSavedFoods('pou');
echo 'Found: ' . count($foods) . ' foods' . PHP_EOL;
foreach ($foods as $food) {
    echo $food['nom'] . PHP_EOL;
}
?>