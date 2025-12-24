/**
 * Gestionnaire Alpine.js pour la page Eau
 * Interface moderne et interactive pour le suivi d'hydratation
 * Inclut animations, mise à jour temps réel, et design visuel
 * Variables requises: (aucune - tout est injecté via les data-attributes)
 */
function registerEauManager() {
    Alpine.data('eauManager', () => ({
        // Données réactives
        eauAujourdhui: 0,
        objectifQuotidien: 2.0, // litres
        historique: [],
        details: [],
        isLoading: false,

        // État UI
        customQuantity: '',
        showCustomInput: false,
        notifications: [],

        // Navigation historique
        selectedDate: null,
        selectedDateDetails: [],
        showingHistory: false,

        // Gamification
        streak: 0,
        todayReached: false,
        badgesEarned: [],
        badgesToEarn: [],
        
        // Score hebdomadaire
        weeklyScore: 0,
        weeklyGrade: {},
        weeklyDays: 0,
        weeklyTotal: 0,
        
        // Modal badge
        showBadgeModal: false,
        selectedBadge: null,

        init() {
            // Injecter les données depuis PHP
            this.eauAujourdhui = parseFloat(this.$el.dataset.eauAujourdhui) || 0;
            this.objectifQuotidien = parseFloat(this.$el.dataset.objectif) || 2.0;
            this.historique = JSON.parse(this.$el.dataset.historique || '[]');
            this.details = JSON.parse(this.$el.dataset.details || '[]');
            
            // Gamification data
            this.streak = parseInt(this.$el.dataset.streak) || 0;
            this.todayReached = this.$el.dataset.todayReached === 'true';
            this.badgesEarned = JSON.parse(this.$el.dataset.badgesEarned || '[]');
            this.badgesToEarn = JSON.parse(this.$el.dataset.badgesToEarn || '[]');
            
            // Weekly score data
            this.weeklyScore = parseInt(this.$el.dataset.weeklyScore) || 0;
            this.weeklyGrade = JSON.parse(this.$el.dataset.weeklyGrade || '{}');
            this.weeklyDays = parseInt(this.$el.dataset.weeklyDays) || 0;
            this.weeklyTotal = parseFloat(this.$el.dataset.weeklyTotal) || 0;
        },

        /**
         * Ouvre le modal avec les détails d'un badge
         */
        openBadgeModal(badge, earned) {
            this.selectedBadge = { ...badge, earned };
            this.showBadgeModal = true;
        },

        /**
         * Calcule le pourcentage d'objectif atteint
         */
        get progressPercentage() {
            return Math.min(100, (this.eauAujourdhui / this.objectifQuotidien) * 100);
        },

        /**
         * Calcule l'eau restante à boire
         */
        get eauRestante() {
            return Math.max(0, this.objectifQuotidien - this.eauAujourdhui);
        },

        /**
         * Couleur de progression selon l'objectif
         */
        get progressColor() {
            const percent = this.progressPercentage;
            if (percent < 50) return '#ef4444'; // rouge
            if (percent < 80) return '#f97316'; // orange
            if (percent < 100) return '#eab308'; // jaune
            return '#22c55e'; // vert
        },

        /**
         * Ajoute de l'eau avec animation
         */
        async ajouterEau(quantite) {
            if (this.isLoading) return;

            this.isLoading = true;
            try {
                const response = await fetch('?page=eau&action=ajouter', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'quantite_ml=' + encodeURIComponent(quantite)
                });

                const result = await response.json();

                if (result.success) {
                    // Animation de succès
                    this.animateWaterDrop();
                    this.showNotification(result.message, 'success');

                    // Mise à jour des données
                    await this.refreshData();

                    // Animation du compteur
                    this.animateCounter(this.eauAujourdhui - (quantite / 1000), this.eauAujourdhui);
                    
                    // Mettre à jour le streak si on vient d'atteindre l'objectif
                    if (!this.todayReached && this.eauAujourdhui >= this.objectifQuotidien) {
                        this.todayReached = true;
                        this.showNotification('🎯 Objectif atteint ! Bravo !', 'success');
                        this.celebrateGoal();
                    }
                } else {
                    this.showNotification(result.error || 'Erreur inconnue', 'error');
                }
            } catch (error) {
                this.showNotification('Erreur de connexion', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Ajoute une quantité personnalisée
         */
        ajouterCustomEau() {
            const quantite = parseInt(this.customQuantity);
            if (!quantite || quantite < 50 || quantite > 2000) {
                this.showNotification('Quantité invalide (50-2000ml)', 'error');
                return;
            }

            this.ajouterEau(quantite);
            this.customQuantity = '';
            this.showCustomInput = false;
        },

        /**
         * Supprime une consommation d'eau
         */
        async supprimerEau(eauId, buttonElement) {
            if (!confirm('Supprimer cette consommation d\'eau ?')) return;

            try {
                const response = await fetch('?page=eau&action=supprimer', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'eau_id=' + encodeURIComponent(eauId)
                });

                const result = await response.json();

                if (result.success) {
                    this.showNotification(result.message, 'success');

                    // Trouver l'élément de la liste (le parent avec bg-blue-50)
                    const listItem = buttonElement.closest('.bg-blue-50');

                    // Animation de suppression
                    listItem.style.transform = 'scale(0.8)';
                    listItem.style.opacity = '0';
                    setTimeout(() => {
                        // Retirer l'élément de la liste details
                        this.details = this.details.filter(detail => detail.id != eauId);
                        // Recalculer le total d'eau
                        this.refreshTotalEau();
                    }, 300);
                } else {
                    this.showNotification(result.error || 'Erreur inconnue', 'error');
                }
            } catch (error) {
                this.showNotification('Erreur de connexion', 'error');
            }
        },

        /**
         * Rafraîchit les données depuis le serveur
         */
        async refreshData() {
            try {
                const [eauData, historiqueData, detailData] = await Promise.all([
                    fetch('?page=eau&action=get_aujourdhui').then(r => r.json()),
                    fetch('?page=eau&action=get_historique').then(r => r.json()),
                    fetch('?page=eau&action=get_detail').then(r => r.json())
                ]);

                if (eauData.success) this.eauAujourdhui = eauData.eau_litres;
                if (historiqueData.success) this.historique = historiqueData.historique;
                if (detailData.success) this.details = detailData.details;
            } catch (error) {
                console.error('Erreur lors du rafraîchissement:', error);
            }
        },

        /**
         * Recalcule le total d'eau consommée aujourd'hui
         */
        refreshTotalEau() {
            this.eauAujourdhui = this.details.reduce((total, detail) => {
                return total + (detail.quantite_ml / 1000);
            }, 0);
        },

        /**
         * Afficher les détails d'un jour spécifique
         */
        async showDayDetails(date) {
            this.selectedDate = date;
            this.showingHistory = true;

            try {
                const response = await fetch(`?page=eau&action=get_detail_date&date=${date}`);
                const result = await response.json();

                if (result.success) {
                    this.selectedDateDetails = result.details;
                } else {
                    this.showNotification('Erreur lors du chargement des détails', 'error');
                }
            } catch (error) {
                this.showNotification('Erreur de connexion', 'error');
            }
        },

        /**
         * Gestionnaire d'événements clavier pour les cartes d'historique
         */
        handleCardKeydown(event, date) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.showDayDetails(date);
            }
        },

        /**
         * Retourner à la vue générale de l'historique
         */
        backToHistory() {
            this.selectedDate = null;
            this.selectedDateDetails = [];
            this.showingHistory = false;
        },

        /**
         * Formater une date pour l'affichage
         */
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        /**
         * Animation d'une goutte d'eau
         */
        animateWaterDrop() {
            const drop = document.createElement('div');
            drop.className = 'fixed pointer-events-none z-50 text-4xl animate-bounce';
            drop.innerHTML = '💧';
            drop.style.left = Math.random() * 100 + 'vw';
            drop.style.top = '-50px';
            drop.style.animationDuration = '2s';

            document.body.appendChild(drop);
            setTimeout(() => drop.remove(), 2000);
        },

        /**
         * Célébration quand l'objectif est atteint
         */
        celebrateGoal() {
            // Confettis d'eau
            for (let i = 0; i < 10; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'fixed pointer-events-none z-50 text-2xl';
                    confetti.innerHTML = ['💧', '🎉', '⭐', '✨'][Math.floor(Math.random() * 4)];
                    confetti.style.left = (20 + Math.random() * 60) + 'vw';
                    confetti.style.top = '-30px';
                    confetti.style.transition = 'all 3s ease-out';
                    
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.style.top = '100vh';
                        confetti.style.opacity = '0';
                        confetti.style.transform = `rotate(${Math.random() * 720}deg)`;
                    }, 50);
                    
                    setTimeout(() => confetti.remove(), 3500);
                }, i * 100);
            }
        },

        /**
         * Animation du compteur d'eau
         */
        animateCounter(from, to) {
            const element = document.querySelector('#eau-total');
            if (!element) return;

            const duration = 1000;
            const start = Date.now();

            const animate = () => {
                const elapsed = Date.now() - start;
                const progress = Math.min(elapsed / duration, 1);
                const current = from + (to - from) * progress;

                element.textContent = current.toFixed(1) + ' L';

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        },

        /**
         * Affiche une notification
         */
        showNotification(message, type = 'info') {
            const notification = {
                id: Date.now(),
                message,
                type,
                show: true
            };

            this.notifications.push(notification);

            // Auto-remove après 3 secondes
            setTimeout(() => {
                notification.show = false;
                setTimeout(() => {
                    const index = this.notifications.indexOf(notification);
                    if (index > -1) this.notifications.splice(index, 1);
                }, 300);
            }, 3000);
        },

        /**
         * Génère des bouteilles d'eau animées pour la visualisation
         */
        get waterBottles() {
            const bottles = [];
            const totalBottles = 8; // 8 bouteilles pour 2L objectif

            for (let i = 0; i < totalBottles; i++) {
                const bottleProgress = Math.max(0, Math.min(1, (this.eauAujourdhui * 1000) / (250 * (i + 1))));
                bottles.push({
                    id: i,
                    filled: bottleProgress >= 1,
                    progress: bottleProgress
                });
            }

            return bottles;
        }
    }));
}

// Register the component
if (window.Alpine) {
    registerEauManager();
} else {
    document.addEventListener('alpine:init', registerEauManager);
}