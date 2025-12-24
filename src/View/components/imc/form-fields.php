<?php

/**
 * Composant: Formulaire données utilisateur IMC.
 *
 * @description Champs: taille, poids, année, sexe, activité, objectif
 * @requires Alpine.js - Variables: taille, poids, annee, sexe, activite, objectif
 * @requires Alpine.js - Méthode: onInputChange()
 *
 * @var array $data Données du formulaire (depuis ImcController)
 * @var callable $escape Fonction d'échappement HTML
 */

declare(strict_types=1);

$currentYear = (int)date('Y');
?>
<!-- ============================================================
     FORMULAIRE DONNÉES UTILISATEUR
     - 6 champs avec validation visuelle Alpine.js
     - Indicateurs vert/rouge selon validité
     ============================================================ -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    
    <!-- ===== TAILLE (cm) ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Taille (cm)</label>
        <div class="relative">
            <input type="number" step="0.1" min="100" max="250" name="taille" id="taille"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-300 transition shadow-sm" required
                   x-model="taille" @input="onInputChange()"
                   :class="{ 'border-red-400': taille < 100 || taille > 250, 'border-green-400': taille >= 100 && taille <= 250 }">
            <i class="fa-solid absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"
               :class="{
                   'fa-check-circle text-green-500': taille >= 100 && taille <= 250,
                   'fa-exclamation-circle text-red-500': taille < 100 || taille > 250
               }"></i>
        </div>
        <p x-show="taille < 100 || taille > 250" class="text-red-600 text-xs mt-1" x-transition>
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>La taille doit être entre 100 et 250 cm
        </p>
    </div>
    
    <!-- ===== POIDS (kg) ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Poids (kg)</label>
        <div class="relative">
            <input type="number" step="0.1" min="30" max="300" name="poids" id="poids"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-green-300 transition shadow-sm" required
                   x-model="poids" @input="onInputChange()"
                   :class="{ 'border-red-400': poids < 30 || poids > 300, 'border-green-400': poids >= 30 && poids <= 300 }">
            <i class="fa-solid absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"
               :class="{
                   'fa-check-circle text-green-500': poids >= 30 && poids <= 300,
                   'fa-exclamation-circle text-red-500': poids < 30 || poids > 300
               }"></i>
        </div>
        <p x-show="poids < 30 || poids > 300" class="text-red-600 text-xs mt-1" x-transition>
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Le poids doit être entre 30 et 300 kg
        </p>
    </div>
    
    <!-- ===== ANNÉE DE NAISSANCE ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Année de naissance</label>
        <div class="relative">
            <input type="number" min="1920" max="<?= $currentYear; ?>" name="annee" id="annee"
                   class="pl-10 p-3 md:p-3 p-4 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-purple-300 transition shadow-sm" required
                   x-model="annee" @input="onInputChange()"
                   :class="{ 'border-red-400': annee < 1920 || annee > <?= $currentYear; ?>, 'border-green-400': annee >= 1920 && annee <= <?= $currentYear; ?> }">
            <i class="fa-solid absolute right-3 top-1/2 -translate-y-1/2 transition-all duration-300"
               :class="{
                   'fa-check-circle text-green-500': annee >= 1920 && annee <= <?= $currentYear; ?>,
                   'fa-exclamation-circle text-red-500': annee < 1920 || annee > <?= $currentYear; ?>
               }"></i>
        </div>
        <p x-show="annee < 1920 || annee > <?= $currentYear; ?>" class="text-red-600 text-xs mt-1" x-transition>
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>L'année doit être entre 1920 et <?= $currentYear; ?>
        </p>
    </div>
    
    <!-- ===== SEXE ===== -->
    <div class="flex flex-col gap-4">
        <label class="font-semibold text-gray-700">Sexe</label>
        <select name="sexe" id="sexe" class="pl-10 p-3 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-pink-300 transition shadow-sm" required
                x-model="sexe" @change="onInputChange()">
            <option value="homme">Homme</option>
            <option value="femme">Femme</option>
        </select>
    </div>
    
    <!-- ===== NIVEAU D'ACTIVITÉ (pleine largeur) ===== -->
    <div class="flex flex-col gap-4 md:col-span-2">
        <label class="font-semibold text-gray-700">Niveau d'activité</label>
        <select name="activite" id="activite" class="pl-10 p-3 w-full rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-yellow-300 transition shadow-sm" required
                x-model="activite" @change="onInputChange()">
            <option value="sedentaire">Sédentaire (peu ou pas d'exercice)</option>
            <option value="leger">Léger (1-3j/semaine)</option>
            <option value="modere">Modéré (3-5j/semaine)</option>
            <option value="intense">Intense (6-7j/semaine)</option>
        </select>
    </div>
    
    <!-- ===== OBJECTIF (pleine largeur) ===== -->
    <div class="flex flex-col gap-4 md:col-span-2">
        <label class="font-semibold text-gray-700">Objectif</label>
        <select name="objectif" id="objectif" class="pl-10 p-3 w-full rounded-xl border border-green-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-green-300 transition shadow-sm" required
                x-model="objectif" @change="onInputChange()">
            <option value="perte">Perte de poids</option>
            <option value="maintien">Maintien du poids</option>
        </select>
    </div>
</div>
