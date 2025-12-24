<?php
require 'vendor/autoload.php';

$db = \App\Model\Database::getInstance();
$stmt = $db->query('SELECT pseudo, email, mot_de_passe FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Utilisateurs et mots de passe hashés ===\n";
foreach ($users as $user) {
    echo "Utilisateur: {$user['pseudo']} - Email: {$user['email']}\n";
    echo "Hash: {$user['mot_de_passe']}\n";

    // Tester quelques mots de passe courants
    $commonPasswords = ['password', '123456', 'motdepasse', 'admin', 'test'];
    foreach ($commonPasswords as $pwd) {
        if (password_verify($pwd, $user['mot_de_passe'])) {
            echo "✅ Mot de passe trouvé: '$pwd'\n";
            break;
        }
    }
    echo "\n";
}
?>