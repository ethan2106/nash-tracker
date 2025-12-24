<?php
require 'vendor/autoload.php';

try {
    $db = \App\Model\Database::getInstance();

    // Vérifier la structure de la table users
    $stmt = $db->query('PRAGMA table_info(users)');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo 'Structure de la table users:' . "\n";
    foreach ($columns as $col) {
        echo "- {$col['name']}: {$col['type']}" . (isset($col['notnull']) && $col['notnull'] ? ' NOT NULL' : '') . "\n";
    }

    // Vérifier s'il y a des tables
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "\nTables dans la base:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>