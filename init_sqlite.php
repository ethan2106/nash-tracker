<?php
// Script to initialize SQLite DB for Nash-Tracker

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/storage/db.sqlite';

// Create DB if not exists
if (!file_exists($dbPath)) {
    touch($dbPath);
    echo "Created SQLite DB at $dbPath\n";
} else {
    echo "SQLite DB already exists at $dbPath\n";
}

// Connect
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Run schema
$sql = file_get_contents(__DIR__ . '/suivi_nash.sqlite.sql');
try {
    $pdo->exec($sql);
    echo "Schema applied successfully\n";
} catch (Exception $e) {
    echo "Error in schema: " . $e->getMessage() . "\n";
    exit(1);
}

// Run migrations
$migrations = [
    __DIR__ . '/migrations/001_add_indexes.sqlite.sql',
    __DIR__ . '/migrations/002_walktrack.sqlite.sql',
    __DIR__ . '/migrations/003_walktrack_times.sqlite.sql',
    __DIR__ . '/migrations/004_remove_hydration.sqlite.sql',
];

foreach ($migrations as $migration) {
    if (file_exists($migration)) {
        echo "Running migration: " . basename($migration) . "\n";
        $sql = file_get_contents($migration);
        $pdo->exec($sql);
    }
}

echo "All migrations applied\n";
echo "SQLite DB initialized successfully\n";