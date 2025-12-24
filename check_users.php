<?php
require 'vendor/autoload.php';

try {
    $db = \App\Model\Database::getInstance();
    $stmt = $db->query('SELECT id, pseudo, email, date_inscription FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo 'Utilisateurs en base: ' . count($users) . "\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Pseudo: {$user['pseudo']}, Email: {$user['email']}\n";
    }

    if (count($users) === 0) {
        echo "\n❌ Aucun utilisateur trouvé en base !\n";
        echo "Il faut créer un compte utilisateur d'abord.\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>