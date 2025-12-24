<?php
// Script pour remplacer toutes les occurrences de quantite_grammes par quantite_g
$files = glob('src/**/*.php');

$replaced = 0;
$totalReplacements = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;

    // Remplacer quantite_grammes par quantite_g
    $content = str_replace('quantite_grammes', 'quantite_g', $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $replacements = substr_count($content, 'quantite_g') - substr_count($originalContent, 'quantite_g');
        $totalReplacements += $replacements;
        $replaced++;
        echo "✅ $file: $replacements remplacements\n";
    }
}

echo "\n📊 Résumé:\n";
echo "Fichiers modifiés: $replaced\n";
echo "Remplacements totaux: $totalReplacements\n";
?>