<?php
require_once 'vendor/autoload.php';
$db = new PDO('sqlite:storage/db.sqlite');
$result = $db->query('SELECT id, user_id, type_repas, date_heure FROM repas WHERE id = 10');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID: ' . $row['id'] . ', User: ' . $row['user_id'] . ', Type: ' . $row['type_repas'] . ', Date: ' . $row['date_heure'] . PHP_EOL;
}
?>