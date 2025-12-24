<?php
require_once 'vendor/autoload.php';
require_once 'src/Config/session.php';

try {
    $pdo = \App\Model\Database::getInstance();

    // Trouver l'utilisateur par email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(['jimdonne1609@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
        echo "Utilisateur ID: $userId\n\n";

// Montrer la structure de la table objectifs_nutrition (SQLite)
        $stmt = $pdo->prepare('PRAGMA table_info(objectifs_nutrition)');
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "=== STRUCTURE TABLE objectifs_nutrition ===\n";
        foreach ($columns as $col) {
            echo $col['name'] . " - " . $col['type'] . "\n";
        }

        echo "\n=== VOS DONNÉES ACTIVES ===\n";
        $stmt = $pdo->prepare('SELECT * FROM objectifs_nutrition WHERE user_id = ? AND actif = 1');
        $stmt->execute([$userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            echo "ID enregistrement: " . $data['id'] . "\n";
            echo "Poids: " . $data['poids'] . " kg\n";
            echo "Taille: " . $data['taille'] . " cm\n";
            echo "Année: " . $data['annee'] . "\n";
            echo "IMC: " . $data['imc'] . "\n";
            echo "Date création: " . $data['date_debut'] . "\n";
        } else {
            echo "AUCUNE DONNÉE ACTIVE TROUVÉE\n";
        }
    } else {
        echo "Utilisateur non trouvé\n";
    }
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . "\n";
}
?>

