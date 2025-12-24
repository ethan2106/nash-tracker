<?php
require 'vendor/autoload.php';
require 'src/Model/Database.php';
try {
    $db = App\Model\Database::getInstance();
    $result = $db->query('PRAGMA table_info(user_config)');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo 'Columns in user_config:' . PHP_EOL;
    foreach ($columns as $col) {
        echo '  ' . $col['name'] . ' (' . $col['type'] . ')' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
