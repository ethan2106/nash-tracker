<?php

/**
 * Composant: Pagination du catalogue.
 * @description Contrôles de pagination pour la liste des aliments
 * @requires Alpine.js - Variables: page, totalPages, filteredFoods, prevPage(), nextPage()
 */

declare(strict_types=1);
?>
<!-- ========== CONTRÔLES DE PAGINATION ========== -->
<div class="flex justify-between items-center mt-8 px-6" x-show="totalPages > 1">
    <button @click="prevPage()"
            :disabled="page === 1"
            :class="page === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600 text-white'"
            class="px-6 py-3 rounded-2xl font-semibold transition-all duration-300 shadow-lg">
        <i class="fa-solid fa-chevron-left mr-2"></i>Précédent
    </button>

    <div class="flex items-center gap-4">
        <span class="text-gray-600 font-medium">
            Page <span class="font-bold text-blue-600" x-text="page"></span> sur <span class="font-bold text-blue-600" x-text="totalPages"></span>
        </span>
        <span class="text-sm text-gray-500">
            (<span x-text="filteredFoods.length"></span> aliments filtrés)
        </span>
    </div>

    <button @click="nextPage()"
            :disabled="page === totalPages"
            :class="page === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600 text-white'"
            class="px-6 py-3 rounded-2xl font-semibold transition-all duration-300 shadow-lg">
        Suivant<i class="fa-solid fa-chevron-right ml-2"></i>
    </button>
</div>
