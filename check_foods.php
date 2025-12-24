<?php
/**
 * Script pour vérifier les aliments dans la base de données
 */

$dbPath = __DIR__ . '/storage/db.sqlite';

if (!file_exists($dbPath)) {
    die("Base de données non trouvée : $dbPath\n");
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== VÉRIFICATION DES ALIMENTS DANS LA BDD ===\n\n";

    // Compter tous les aliments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM aliments");
    $total = $stmt->fetch()['total'];
    echo "Total aliments : $total\n\n";

    // Vérifier spécifiquement "Blanc de poulet"
    $stmt = $pdo->prepare("SELECT id, nom, openfoodfacts_id FROM aliments WHERE nom LIKE ?");
    $stmt->execute(['%Blanc de poulet%']);
    $poulet = $stmt->fetch();

    if ($poulet) {
        echo "Blanc de poulet trouvé :\n";
        echo "- ID : " . $poulet['id'] . "\n";
        echo "- Nom : " . $poulet['nom'] . "\n";
        echo "- OpenFoodFacts ID : " . ($poulet['openfoodfacts_id'] ?: 'NULL (manuel)') . "\n\n";
    } else {
        echo "Blanc de poulet NON trouvé dans la base de données.\n\n";
    }

    // Lister tous les aliments manuels restants
    $stmt = $pdo->query("SELECT id, nom FROM aliments WHERE openfoodfacts_id IS NULL OR openfoodfacts_id = '' ORDER BY nom LIMIT 20");
    $manuels = $stmt->fetchAll();

    if (empty($manuels)) {
        echo "Aucun aliment manuel trouvé.\n";
    } else {
        echo "Aliments manuels restants (" . count($manuels) . ") :\n";
        foreach ($manuels as $aliment) {
            echo "- " . $aliment['nom'] . " (ID: " . $aliment['id'] . ")\n";
        }
    }

    // Vérifier les autres_infos pour Blanc de poulet
    $stmt = $pdo->prepare("SELECT autres_infos FROM aliments WHERE nom LIKE ?");
    $stmt->execute(['%Blanc de poulet%']);
    $pouletInfos = $stmt->fetch();

    if ($pouletInfos) {
        echo "autres_infos pour Blanc de poulet : " . ($pouletInfos['autres_infos'] ?: 'NULL') . "\n";

        // Parser le JSON pour voir la source
        $autresInfos = json_decode($pouletInfos['autres_infos'] ?: '{}', true);
        if ($autresInfos === null) {
            echo "Erreur JSON decode: " . json_last_error_msg() . "\n";
            $source = 'manuel';
        } else {
            $source = $autresInfos['source'] ?? 'manuel';
        }
        echo "Source déterminée : $source\n\n";
    }

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}