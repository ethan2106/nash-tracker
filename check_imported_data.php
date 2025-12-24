<?php
require 'vendor/autoload.php';

$db = \App\Model\Database::getInstance();

$tables = ['activites_physiques', 'aliments', 'historique_mesures', 'repas', 'medicaments', 'prises_medicaments'];

echo "=== État des données après import ===\n";

foreach ($tables as $table) {
    $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "$table: {$result['count']} enregistrements\n";
}

// Afficher les détails des utilisateurs
echo "\n=== Utilisateurs ===\n";
$stmt = $db->query("SELECT id, pseudo, email, date_inscription FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "- ID: {$user['id']}, Pseudo: {$user['pseudo']}, Email: {$user['email']}\n";
}

// Afficher quelques activités physiques
echo "\n=== Activités physiques (échantillon) ===\n";
$stmt = $db->query("SELECT user_id, type_activite, duree_minutes, date_heure FROM activites_physiques LIMIT 3");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($activities as $activity) {
    echo "- User {$activity['user_id']}: {$activity['type_activite']} ({$activity['duree_minutes']} min) - {$activity['date_heure']}\n";
}
?>