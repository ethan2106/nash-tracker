<?php

/**
 * Composant: Notifications Toast.
 *
 * @description Système de notifications toast (succès/erreur/info)
 * @requires Alpine.js - Variable: notifications (array)
 * @note Position fixed top-right, z-50
 * @note Animations CSS via x-transition
 */

declare(strict_types=1);
?>
<!-- ============================================================
     NOTIFICATIONS TOAST
     - Position: fixed top-4 right-4
     - Types: success (vert), error (rouge), info (bleu)
     - Animation: slide-in depuis la droite
     ============================================================ -->
<div class="fixed top-4 right-4 z-50 space-y-2">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="px-6 py-3 rounded-2xl shadow-lg font-semibold text-white"
             :class="notification.type === 'success' ? 'bg-green-500' : notification.type === 'error' ? 'bg-red-500' : 'bg-sky-500'">
            <span x-text="notification.message"></span>
        </div>
    </template>
</div>
