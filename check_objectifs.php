<?php
require 'vendor/autoload.php';

$db = \App\Model\Database::getInstance();
$stmt = $db->query('SELECT * FROM objectifs_nutrition WHERE user_id = 2');
$objectifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo 'Objectifs pour user 2: ' . count($objectifs) . "\n";
foreach ($objectifs as $obj) {
    echo '- ID: ' . $obj['id'] . ', Actif: ' . $obj['actif'] . ', IMC: ' . $obj['imc'] . "\n";
}

if (count($objectifs) === 0) {
    echo "\nCréation d'un objectif de test pour l'utilisateur 2...\n";

    // Créer un objectif de test
    $stmt = $db->prepare('
        INSERT INTO objectifs_nutrition (
            user_id, calories_perte, sucres_max, graisses_sat_max, proteines_min, proteines_max,
            fibres_min, fibres_max, sodium_max, taille, poids, annee, sexe, activite, imc,
            objectif, glucides, graisses_insaturees, actif
        ) VALUES (?, 1800, 50, 30, 80, 120, 25, 35, 2.3, 175, 75, 1990, \'homme\', \'modere\', 24.5, \'perte\', 200, 50, 1)
    ');
    $stmt->execute([2]);

    echo "✅ Objectif de test créé\n";
}
?>