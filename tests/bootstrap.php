<?php

// Bootstrap for PHPUnit tests
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// For tests, use in-memory SQLite
if (getenv('APP_ENV') === 'testing')
{
    putenv('DB_PATH=:memory:');
    $_ENV['DB_PATH'] = ':memory:';

    // Reset database instance to use new config
    \App\Model\Database::resetInstance();
}

// Initialize test database
$dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../storage/db.sqlite';

if ($dbPath === ':memory:')
{
    // Get the database instance (this will create it with :memory:)
    $db = \App\Model\Database::getInstance();

    // Load schema
    $schemaFile = __DIR__ . '/../suivi_nash.sqlite.sql';
    if (file_exists($schemaFile))
    {
        $sql = file_get_contents($schemaFile);
        $db->exec($sql);
        // Removed echo to avoid header issues in tests
    }

    // Load migrations
    $migrations = [
        __DIR__ . '/../migrations/001_add_indexes.sqlite.sql',
        __DIR__ . '/../migrations/002_walktrack.sqlite.sql',
        __DIR__ . '/../migrations/003_walktrack_times.sqlite.sql',
    ];

    foreach ($migrations as $migration)
    {
        if (file_exists($migration))
        {
            $sql = file_get_contents($migration);
            $db->exec($sql);
        }
    }
}
