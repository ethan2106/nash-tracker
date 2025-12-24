<?php
/**
 * Composant de notifications global
 * À inclure dans layout.php pour afficher les notifications sur toutes les pages.
 *
 * Utilise Alpine.js pour les animations et la gestion d'état
 */
?>

<!-- Container de notifications global -->
<div x-data="notificationManager()" 
    x-init="init()"
    class="fixed top-4 right-4 z-[10000] space-y-2 max-w-md pointer-events-none">
    
    <template x-for="notification in notifications" :key="notification.id">
           <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
               class="flex items-center gap-3 px-6 py-4 rounded-2xl shadow-lg text-white font-semibold cursor-pointer pointer-events-auto"
             :class="getNotificationClass(notification.type)"
             @click="hide(notification.id)">
            
            <!-- Icône -->
            <i class="fa-solid text-xl" :class="getNotificationIcon(notification.type)"></i>
            
            <!-- Message -->
            <span class="flex-1" x-text="notification.message"></span>
            
            <!-- Bouton fermer -->
            <button @click.stop="hide(notification.id)" 
                    class="hover:bg-white/20 rounded-lg p-1 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    </template>
</div>

<?php
/**
 * Message flash PHP (si présent dans la session)
 * Sera automatiquement affiché par Alpine.js au chargement.
 */
if (isset($_SESSION['flash']) && !empty($_SESSION['flash']))
{
    $flash = $_SESSION['flash'];
    $message = is_array($flash) ? ($flash['msg'] ?? $flash['message'] ?? '') : $flash;
    $type = is_array($flash) ? ($flash['type'] ?? 'info') : 'info';

    if (!empty($message))
    {
        echo '<div data-flash-message="' . htmlspecialchars($message, ENT_QUOTES) . '" 
                   data-flash-type="' . htmlspecialchars($type, ENT_QUOTES) . '" 
                   style="display:none;"></div>';
    }

    unset($_SESSION['flash']);
}
?>
