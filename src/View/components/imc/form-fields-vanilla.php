<?php

/**
 * Composant: Formulaire données utilisateur IMC.
 *
 * @description Champs: taille, poids, année, sexe, activité, objectif
 * @requires imc-calculator.js (Vanilla JS)
 *
 * @var array $data Données du formulaire (depuis ImcController)
 * @var callable $escape Fonction d'échappement HTML
 */

declare(strict_types=1);

$currentYear = (int)date('Y');
?>
<!-- ============================================================
     FORMULAIRE DONNÉES UTILISATEUR
     - 6 champs avec validation visuelle via Vanilla JS
     - Indicateurs vert/rouge selon validité
     ============================================================ -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    
    <!-- ===== TAILLE (cm) ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Taille (cm)</label>
        <div class="relative">
            <input type="number" step="0.1" min="100" max="250" name="taille" id="taille"
                   value="<?= $escape($data['taille']); ?>"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-green-400 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-300 transition shadow-sm" required>
            <i class="fa-solid fa-check-circle text-green-500 absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"></i>
        </div>
        <p class="text-red-600 text-xs mt-1" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>La taille doit être entre 100 et 250 cm
        </p>
    </div>
    
    <!-- ===== POIDS (kg) ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Poids (kg)</label>
        <div class="relative">
            <input type="number" step="0.1" min="30" max="300" name="poids" id="poids"
                   value="<?= $escape($data['poids']); ?>"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-green-400 bg-white/80 focus:outline-none focus:ring-2 focus:ring-green-300 transition shadow-sm" required>
            <i class="fa-solid fa-check-circle text-green-500 absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"></i>
        </div>
        <p class="text-red-600 text-xs mt-1" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Le poids doit être entre 30 et 300 kg
        </p>
    </div>
    
    <!-- ===== ANNÉE DE NAISSANCE ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Année de naissance</label>
        <div class="relative">
            <input type="number" min="1920" max="<?= $currentYear; ?>" name="annee" id="annee"
                   value="<?= $escape($data['annee']); ?>"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-green-400 bg-white/80 focus:outline-none focus:ring-2 focus:ring-purple-300 transition shadow-sm" required>
            <i class="fa-solid fa-check-circle text-green-500 absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"></i>
        </div>
        <p class="text-red-600 text-xs mt-1" style="display: none;">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>L'année doit être entre 1920 et <?= $currentYear; ?>
        </p>
    </div>
    
    <!-- ===== SEXE ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Sexe</label>
        <select name="sexe" id="sexe" class="pl-10 p-3 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-pink-300 transition shadow-sm" required>
            <option value="homme" <?= ($data['sexe'] ?? 'homme') === 'homme' ? 'selected' : ''; ?>>Homme</option>
            <option value="femme" <?= ($data['sexe'] ?? '') === 'femme' ? 'selected' : ''; ?>>Femme</option>
        </select>
    </div>
    
    <!-- ===== NIVEAU D'ACTIVITÉ (pleine largeur) ===== -->
    <div class="flex flex-col gap-4 md:col-span-2">
        <label class="font-semibold text-gray-700">Niveau d'activité</label>
        <select name="activite" id="activite" class="pl-10 p-3 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-yellow-300 transition shadow-sm" required>
            <option value="sedentaire" <?= ($data['activite'] ?? 'sedentaire') === 'sedentaire' ? 'selected' : ''; ?>>Sédentaire (peu ou pas d'exercice)</option>
            <option value="leger" <?= ($data['activite'] ?? '') === 'leger' ? 'selected' : ''; ?>>Léger (1-3j/semaine)</option>
            <option value="modere" <?= ($data['activite'] ?? '') === 'modere' ? 'selected' : ''; ?>>Modéré (3-5j/semaine)</option>
            <option value="intense" <?= ($data['activite'] ?? '') === 'intense' ? 'selected' : ''; ?>>Intense (6-7j/semaine)</option>
        </select>
    </div>
    
    <!-- ===== OBJECTIF (pleine largeur) ===== -->
    <div class="flex flex-col gap-4 md:col-span-2">
        <label class="font-semibold text-gray-700">Objectif</label>
        <select name="objectif" id="objectif" class="pl-10 p-3 w-full rounded-xl border border-green-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-green-300 transition shadow-sm" required>
            <option value="perte" <?= ($data['objectif'] ?? 'perte') === 'perte' ? 'selected' : ''; ?>>Perte de poids</option>
            <option value="maintien" <?= ($data['objectif'] ?? '') === 'maintien' ? 'selected' : ''; ?>>Maintien du poids</option>
        </select>
    </div>
</div>
