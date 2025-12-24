/**
 * Composant Alpine.js pour le dashboard utilisateur v2
 * Gère l'état, animations, interactions, et actualisation
 */
function dashboardManager() {
    return {
        // État d'initialisation
        isLoaded: false,
        isLoading: false,

        // Collapse du détail du score
        showScoreDetails: false,

        // Animations des barres de progression
        animatedBars: new Set(),

        // Tooltips
        activeTooltip: null,

        // Initialisation
        init() {
            // Délai pour permettre au DOM de se charger
            setTimeout(() => {
                this.isLoaded = true;
                this.animateProgressBars();
                this.animateScoreCircle();
            }, 100);
        },

        // Animer le cercle de score
        animateScoreCircle() {
            const circle = document.querySelector('[data-score]');
            if (!circle) return;

            const score = parseInt(circle.getAttribute('data-score'));
            const radius = 75;
            const circumference = 2 * Math.PI * radius;
            
            // Animation from 0 to score
            circle.style.strokeDashoffset = circumference; // Start at 0%
            
            setTimeout(() => {
                const offset = circumference * (1 - score / 100);
                circle.style.strokeDashoffset = offset;
            }, 200);
        },

        // Actualiser le dashboard
        refreshDashboard() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            
            // Simuler un rechargement (en production: fetch API)
            setTimeout(() => {
                window.location.reload();
            }, 500);
        },

        // Animation des barres de progression
        animateProgressBars() {
            const progressBars = document.querySelectorAll('[data-progress-bar]');
            progressBars.forEach((bar, index) => {
                setTimeout(() => {
                    const targetWidth = bar.getAttribute('data-progress-bar');
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.transition = 'width 1.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        bar.style.width = targetWidth + '%';
                    }, 200);
                }, index * 200); // Délai échelonné pour chaque barre
            });
        },

        // Animation des compteurs numériques
        animateCounter(element, targetValue, duration = 1000) {
            if (!element || this.animatedBars.has(element)) return;

            this.animatedBars.add(element);
            const startValue = 0;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Fonction d'easing pour une animation fluide
                const easeOutCubic = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.floor(startValue + (targetValue - startValue) * easeOutCubic);

                element.textContent = this.formatNumber(currentValue, element.getAttribute('data-unit'));

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        },

        // Formatage des nombres
        formatNumber(num, unit) {
            if (unit === 'kcal') {
                return num.toLocaleString('fr-FR');
            } else if (unit === 'L' || unit === 'kg') {
                return num.toFixed(1).replace('.', ',');
            }
            return num.toString();
        },

        // Gestion des tooltips
        showTooltip(event, content) {
            this.activeTooltip = {
                content: content,
                x: event.clientX + 10,
                y: event.clientY + 10
            };
        },

        hideTooltip() {
            this.activeTooltip = null;
        },

        // Animation au survol des cartes
        cardHover(card, isHover) {
            if (isHover) {
                card.style.transform = 'translateY(-2px)';
                card.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
            } else {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '';
            }
        },

        // Animation des icônes au clic
        animateIcon(icon) {
            icon.style.transform = 'scale(1.2) rotate(10deg)';
            setTimeout(() => {
                icon.style.transform = 'scale(1) rotate(0deg)';
            }, 200);
        },

        // Rafraîchissement des données (futur)
        refreshData() {
            // Animation de chargement
            const refreshIcon = document.querySelector('[data-refresh-icon]');
            if (refreshIcon) {
                refreshIcon.style.transform = 'rotate(360deg)';
                refreshIcon.style.transition = 'transform 0.5s ease';

                // Ici on pourrait faire un appel AJAX pour rafraîchir les données
                setTimeout(() => {
                    refreshIcon.style.transform = 'rotate(0deg)';
                }, 500);
            }
        },

        // Gestion des raccourcis clavier
        handleKeydown(event) {
            // Ctrl/Cmd + R pour rafraîchir
            if ((event.ctrlKey || event.metaKey) && event.key === 'r') {
                event.preventDefault();
                this.refreshData();
            }

            // Échap pour fermer les tooltips
            if (event.key === 'Escape') {
                this.hideTooltip();
            }
        }
    }
}