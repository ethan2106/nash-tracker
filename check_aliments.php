<?php
require_once 'vendor/autoload.php';
$db = new PDO('sqlite:storage/db.sqlite');
$result = $db->query('SELECT r.id as repas_id, r.type_repas, ra.aliment_id, a.nom, ra.quantite_g FROM repas r LEFT JOIN repas_aliments ra ON r.id = ra.repas_id LEFT JOIN aliments a ON ra.aliment_id = a.id WHERE r.date_heure >= date("now", "-1 day") ORDER BY r.date_heure DESC');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo 'Repas ID: ' . $row['repas_id'] . ', Type: ' . $row['type_repas'] . ', Aliment: ' . ($row['nom'] ?? 'AUCUN') . ', Qty: ' . ($row['quantite_g'] ?? '0') . 'g' . PHP_EOL;
}
?>