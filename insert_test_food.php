<?php
require 'vendor/autoload.php';
$db = \App\Model\Database::getInstance();
$db->exec("INSERT INTO aliments (nom, category_id, calories_100g, proteines_100g, glucides_100g, lipides_100g) VALUES ('Blanc de poulet', 1, 165, 31, 0, 3.6)");
$db->exec("INSERT INTO aliments (nom, category_id, calories_100g, proteines_100g, glucides_100g, lipides_100g) VALUES ('Riz blanc', 2, 130, 2.7, 28, 0.3)");
echo 'Test foods inserted' . PHP_EOL;
?>