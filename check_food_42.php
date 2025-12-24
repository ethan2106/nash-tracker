<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Model/database.php';

use App\Model\Database;

try {
    $db = Database::getInstance();

    // Vérifier si l'aliment 42 existe
    $stmt = $db->prepare('SELECT id, nom FROM aliments WHERE id = ?');
    $stmt->execute([42]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($food) {
        echo "Aliment trouvé: ID={$food['id']}, Nom={$food['nom']}\n";
    } else {
        echo "Aliment avec ID 42 non trouvé\n";
    }

    // Lister quelques aliments
    $stmt = $db->query('SELECT id, nom FROM aliments LIMIT 5');
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nPremiers aliments:\n";
    foreach ($foods as $food) {
        echo "ID={$food['id']}: {$food['nom']}\n";
    }

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}