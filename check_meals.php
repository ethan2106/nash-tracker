<?php
/**
 * Script to check meal types in database
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database connection
$db = \App\Model\Database::getInstance();

echo "=== Checking meal types in database ===\n\n";

try {
    // First check table structure
    $stmt = $db->prepare("PRAGMA table_info(repas)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table 'repas' structure:\n";
    foreach ($columns as $col) {
        echo "- {$col['name']} ({$col['type']})\n";
    }
    echo "\n";

    // Then check data
    $stmt2 = $db->prepare("SELECT * FROM repas ORDER BY id DESC LIMIT 5");
    $stmt2->execute();
    $meals = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "Recent meals:\n";
    foreach ($meals as $meal) {
        echo "ID: {$meal['id']}, Type: '{$meal['type_repas']}', Data: " . json_encode($meal) . "\n";
    }

    echo "\n=== Checking aliments in meals ===\n";
    $stmt2 = $db->prepare("SELECT r.id, r.type_repas, ra.quantite_g, a.nom FROM repas r LEFT JOIN repas_aliments ra ON r.id = ra.repas_id LEFT JOIN aliments a ON ra.aliment_id = a.id ORDER BY r.id DESC LIMIT 20");
    $stmt2->execute();
    $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        echo "Meal ID: {$result['id']}, Type: '{$result['type_repas']}', Food: {$result['nom']}, Qty: {$result['quantite_g']}g\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>