<?php

/**
 * Composant: Navigation onglets paramètres.
 *
 * @description 5 onglets : Compte, Profil Santé, Préférences, Notifications, Export
 * @requires Alpine.js - Variables: activeTab, changeTab()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     NAVIGATION ONGLETS
     - 5 boutons onglets avec états actifs colorés
     - Compte (bleu), Profil Santé (vert), Préférences (orange),
       Notifications (violet), Export (indigo)
     ============================================================ -->
<div class="flex border-b border-gray-200">
    <!-- Onglet Compte -->
    <button @click="changeTab('compte')"
            :class="activeTab === 'compte' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 px-6 py-4 font-semibold transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-user"></i>
        Compte
    </button>
    
    <!-- Onglet Profil Santé -->
    <button @click="changeTab('profil-sante')"
            :class="activeTab === 'profil-sante' ? 'bg-green-50 text-green-700 border-b-2 border-green-500' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 px-6 py-4 font-semibold transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-heartbeat"></i>
        Profil Santé
    </button>
    
    <!-- Onglet Préférences Santé -->
    <button @click="changeTab('preferences-sante')"
            :class="activeTab === 'preferences-sante' ? 'bg-orange-50 text-orange-700 border-b-2 border-orange-500' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 px-6 py-4 font-semibold transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-sliders-h"></i>
        Préférences Santé
    </button>
    
    <!-- Onglet Notifications -->
    <button @click="changeTab('notifications')"
            :class="activeTab === 'notifications' ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-500' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 px-6 py-4 font-semibold transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-bell"></i>
        Notifications
    </button>
    
    <!-- Onglet Export -->
    <button @click="changeTab('export')"
            :class="activeTab === 'export' ? 'bg-indigo-50 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 px-6 py-4 font-semibold transition-all flex items-center justify-center gap-2">
        <i class="fa-solid fa-download"></i>
        Export Données
    </button>
</div>
