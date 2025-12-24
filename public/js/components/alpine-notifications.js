/**
 * Système de Notifications Global - Alpine.js
 * Gère toutes les notifications de l'application de manière unifiée
 */

window.notificationManager = function() {
    return {
        notifications: [],
        nextId: 1,

        init() {
            // Écouter les événements personnalisés pour ajouter des notifications
            // Éviter les doublons d'event listeners
            if (!window.hasNotificationListener) {
                window.addEventListener('show-notification', (event) => {
                    this.show(event.detail.message, event.detail.type, event.detail.duration);
                });
                window.hasNotificationListener = true;
            }

            // Vérifier s'il y a un message flash PHP au chargement
            const flashMessage = document.querySelector('[data-flash-message]');
            if (flashMessage) {
                const message = flashMessage.dataset.flashMessage;
                const type = flashMessage.dataset.flashType || 'info';
                this.show(message, type);
                flashMessage.remove(); // Supprimer après affichage
            }
        },

        /**
         * Affiche une notification
         * @param {string} message - Le message à afficher
         * @param {string} type - Type: 'success', 'error', 'warning', 'info'
         * @param {number} duration - Durée en ms (0 = permanent)
         */
        show(message, type = 'info', duration = 5000) {
            const id = this.nextId++;
            const notification = {
                id,
                message,
                type,
                show: false
            };

            this.notifications.push(notification);

            // Afficher avec un léger délai pour l'animation
            setTimeout(() => {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].show = true;
                }
            }, 100);

            // Auto-dismiss si durée définie
            if (duration > 0) {
                setTimeout(() => {
                    this.hide(id);
                }, duration);
            }

            return id;
        },

        /**
         * Masque une notification
         * @param {number} id - ID de la notification
         */
        hide(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index !== -1) {
                this.notifications[index].show = false;
                // Supprimer après l'animation
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        /**
         * Raccourcis pour les types courants
         */
        success(message, duration = 5000) {
            return this.show(message, 'success', duration);
        },

        error(message, duration = 7000) {
            return this.show(message, 'error', duration);
        },

        warning(message, duration = 6000) {
            return this.show(message, 'warning', duration);
        },

        info(message, duration = 5000) {
            return this.show(message, 'info', duration);
        },

        /**
         * Retourne la classe CSS appropriée selon le type
         */
        getNotificationClass(type) {
            // Utilise des classes Tailwind natives (CDN) pour éviter la transparence
            const classes = {
                success: 'bg-green-600/95 border border-white/10',
                error: 'bg-red-600/95 border border-white/10',
                warning: 'bg-yellow-500/95 border border-white/10',
                info: 'bg-blue-600/95 border border-white/10'
            };
            return classes[type] || classes.info;
        },

        /**
         * Retourne l'icône appropriée selon le type
         */
        getNotificationIcon(type) {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            return icons[type] || icons.info;
        }
    };
};

/**
 * Fonction globale pour déclencher une notification depuis n'importe où
 */
window.showNotification = function(message, type = 'info', duration = 5000) {
    window.dispatchEvent(new CustomEvent('show-notification', {
        detail: { message, type, duration }
    }));
};
