<?php
// Quick validation script for SQLite migration

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\Database;
use App\Model\UserModel;

try {
    // Test DB connection
    $db = Database::getInstance();
    echo "DB connection OK\n";

    // Test a simple query
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "\n";

    // Test UserModel
    $userModel = new UserModel();
    echo "UserModel instantiated OK\n";

    echo "Validation successful: SQLite migration works!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}