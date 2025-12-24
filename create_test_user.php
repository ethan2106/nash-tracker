<?php
require 'vendor/autoload.php';

// Simuler une session pour l'inscription
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['csrf_token'] = 'test_token_123';

echo "=== Création d'un compte utilisateur de test ===\n";

// Données de test
$testData = [
    'pseudo' => 'testuser',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirm' => 'password123'
];

$userModel = new \App\Model\User();
$result = $userModel->register($testData);

if ($result['success']) {
    echo "✅ Compte créé avec succès !\n";
    echo "Email: test@example.com\n";
    echo "Mot de passe: password123\n";
    echo "Pseudo: testuser\n";
} else {
    echo "❌ Erreur lors de la création: " . $result['message'] . "\n";
}

// Vérifier que le compte a été créé
$db = \App\Model\Database::getInstance();
$stmt = $db->query('SELECT id, pseudo, email FROM users WHERE email = "test@example.com"');
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "\n✅ Compte vérifié en base:\n";
    echo "- ID: {$user['id']}\n";
    echo "- Pseudo: {$user['pseudo']}\n";
    echo "- Email: {$user['email']}\n";
} else {
    echo "\n❌ Compte non trouvé en base\n";
}
?>