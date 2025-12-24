<?php
require 'vendor/autoload.php';

try {
    $db = \App\Model\Database::getInstance();
    $sql = file_get_contents('migrations/005_dishes.sqlite.sql');
    $db->exec($sql);
    echo "Migration 005_dishes applied successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}