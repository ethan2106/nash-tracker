<?php

/**
 * Composant: En-tête page paramètres.
 *
 * @description Header avec titre, icône et lien retour profil
 */

declare(strict_types=1);
?>
<!-- ============================================================
     HEADER PARAMÈTRES
     - Icône engrenage
     - Titre et description
     - Lien retour au profil
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="text-blue-500 text-4xl">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Paramètres</h1>
                <p class="text-gray-600">Gérez votre compte et vos préférences</p>
            </div>
        </div>
        <a href="?page=profile" 
           class="flex items-center gap-2 px-4 py-2 bg-white/80 hover:bg-gray-50 
                  text-gray-700 font-semibold rounded-xl shadow-md 
                  transition-all hover:scale-105 border border-gray-200">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Retour au profil
        </a>
    </div>
</div>
