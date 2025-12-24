document.addEventListener('alpine:init', () => {
    // Fonction utilitaire pour fetch avec protection JSON
    window.fetchJson = async function(url, options = {}) {
        const response = await fetch(url, options);
        const text = await response.text();

        if (!text) {
            throw new Error('Réponse serveur vide');
        }

        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON invalide reçu du serveur:', text);
            throw new Error('Réponse serveur invalide (pas du JSON)');
        }
    };

    Alpine.data('activityManager', () => ({
        activity: {
            type: '',
            duration: 0,
            calories: null
        },
        todaysActivities: window.todaysActivities || [],
        totalCalories: window.totalCalories || 0,
        history: window.historyData || [],
        estimatedCalories: 0,
        loading: false,
        notifications: [],

        // Nouvelles propriétés pour le calculateur avancé
        showCalculator: false,
        calculator: {
            activity: '',
            duration: 30,
            intensity: 'moderate',
            weight: 70,
            result: 0
        },
        userProfile: window.userProfile || {},

        get bonusCalories() {
            return this.totalCalories > 500 ? this.totalCalories - 500 : 0;
        },

        init() {
            // Récupérer les données du profil depuis les attributs data
            const el = this.$el;
            if (el) {
                try {
                    const userProfileData = el.getAttribute('data-user-profile');
                    if (userProfileData) {
                        this.userProfile = JSON.parse(userProfileData);
                        // Pré-remplir le poids dans le calculateur si disponible
                        if (this.userProfile.weight) {
                            this.calculator.weight = this.userProfile.weight;
                        }
                    }
                } catch (e) {
                    console.warn('Erreur parsing user profile data:', e);
                }
            }

            this.updateCaloriesEstimation();
        },

        updateCaloriesEstimation() {
            if (this.activity.type && this.activity.duration > 0 && this.activity.calories == null) {
                // Utiliser le poids du profil pour une estimation plus précise
                const userWeight = this.userProfile.weight || 70; // Poids par défaut si non défini

                // Coefficients basés sur MET (kcal/min pour le poids de l'utilisateur)
                const coefficients = {
                    'marche': 4.2,      // Marche modérée (3-5 km/h)
                    'course': 7.0,      // Course légère (8-9 km/h)
                    'velo': 5.5,        // Vélo modéré
                    'natation': 6.0,    // Natation modérée
                    'yoga': 2.5,        // Yoga/Hatha
                    'musculation': 3.5, // Musculation légère
                    'danse': 4.5,       // Danse
                    'tennis': 5.5,      // Tennis double
                    'football': 6.5,    // Football modéré
                    'basketball': 6.0   // Basketball
                };

                // Calcul MET ajusté au poids: (MET × poids × durée) / 60
                const met = coefficients[this.activity.type];
                const durationHours = this.activity.duration / 60;
                this.estimatedCalories = Math.round(met * userWeight * durationHours);
            } else {
                this.estimatedCalories = 0;
            }
        },

        // Nouvelles méthodes pour les fonctionnalités avancées
        getActivityAverages(activityType) {
            const averages = {
                'marche': '15-25 cal/10min (3-5 km/h)',
                'course': '25-35 cal/10min (8-9 km/h)',
                'velo': '20-30 cal/10min (modéré)',
                'natation': '25-35 cal/10min (modérée)',
                'yoga': '8-15 cal/10min (Hatha)',
                'musculation': '12-20 cal/10min (légère)',
                'danse': '15-25 cal/10min',
                'tennis': '20-30 cal/10min (double)',
                'football': '25-35 cal/10min (modéré)',
                'basketball': '25-35 cal/10min'
            };
            return averages[activityType] || 'Variable selon intensité';
        },

        calculateAdvancedCalories() {
            if (!this.calculator.activity || !this.calculator.duration) {
                return;
            }

            // Utiliser le poids du profil si aucun poids n'est saisi
            let weight = this.calculator.weight;
            if (!weight && this.userProfile.weight) {
                weight = this.userProfile.weight;
                this.calculator.weight = weight; // Mettre à jour l'affichage
            }

            if (!weight) {
                this.showNotification('Veuillez saisir votre poids ou le définir dans votre profil IMC', 'error');
                return;
            }

            // Coefficients MET de base
            const baseMET = {
                'marche': 3.8,
                'course': 8.3,
                'velo': 6.8,
                'natation': 7.0,
                'yoga': 2.5,
                'musculation': 3.0,
                'danse': 4.8,
                'tennis': 7.3,
                'football': 8.0,
                'basketball': 8.0
            };

            // Ajustement selon intensité
            const intensityMultiplier = {
                'light': 0.8,
                'moderate': 1.0,
                'vigorous': 1.3
            };

            const met = baseMET[this.calculator.activity] * intensityMultiplier[this.calculator.intensity];
            const duration = this.calculator.duration / 60; // Convertir en heures

            // Formule MET: calories = MET × poids × durée (heures)
            this.calculator.result = Math.round(met * weight * duration);
        },

        applyCalculatedCalories() {
            if (this.calculator.result > 0) {
                this.activity.calories = this.calculator.result;
                this.showCalculator = false;
                this.showNotification('Calories appliquées depuis le calculateur MET', 'success');
            }
        },

        async addActivity() {
            if (!this.activity.type || this.activity.duration <= 0) {
                this.showNotification('Veuillez remplir tous les champs', 'error');
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('type_activite', this.activity.type);
                formData.append('duree_minutes', this.activity.duration);
                if (this.activity.calories != null && this.activity.calories > 0) {
                    formData.append('calories', this.activity.calories);
                }

                const result = await fetchJson('?page=activity/add', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    this.showNotification('Activité ajoutée avec succès !', 'success');
                    this.resetForm();

                    // Recharger les données
                    await this.loadTodaysActivities();
                    await this.loadHistory();
                } else {
                    this.showNotification(result.error || 'Erreur lors de l\'ajout', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteActivity(activityId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('activite_id', activityId);

                const result = await fetchJson('?page=activity/delete', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    this.showNotification('Activité supprimée', 'success');
                    await this.loadTodaysActivities();
                    await this.loadHistory();
                } else {
                    this.showNotification(result.error || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showNotification('Erreur de connexion', 'error');
            }
        },

        async loadTodaysActivities() {
            try {
                const result = await fetchJson('?page=activity/today');
                if (result.success) {
                    this.todaysActivities = result.activites;
                    this.totalCalories = result.total_calories;
                }
            } catch (error) {
                console.error('Erreur chargement activités:', error);
            }
        },

        async loadHistory() {
            try {
                const result = await fetchJson('?page=activity/history');
                if (result.success) {
                    this.history = result.historique;
                }
            } catch (error) {
                console.error('Erreur chargement historique:', error);
            }
        },

        resetForm() {
            this.activity = { type: '', duration: 0, calories: null };
            this.estimatedCalories = 0;
        },

        showNotification(message, type = 'info') {
            const id = Date.now();
            this.notifications.push({
                id,
                message,
                type,
                show: true
            });

            setTimeout(() => {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index > -1) {
                    this.notifications[index].show = false;
                    setTimeout(() => {
                        this.notifications.splice(index, 1);
                    }, 300);
                }
            }, 3000);
        },

        getActivityEmoji(type) {
            const emojis = {
                'marche': '🚶', 'course': '🏃', 'velo': '🚴', 'natation': '🏊',
                'yoga': '🧘', 'musculation': '💪', 'danse': '💃', 'tennis': '🎾',
                'football': '⚽', 'basketball': '🏀'
            };
            return emojis[type] || '🏃‍♂️';
        },

        formatActivityType(type) {
            const labels = {
                'marche': 'Marche rapide', 'course': 'Course', 'velo': 'Vélo',
                'natation': 'Natation', 'yoga': 'Yoga', 'musculation': 'Musculation',
                'danse': 'Danse', 'tennis': 'Tennis', 'football': 'Football', 'basketball': 'Basketball'
            };
            return labels[type] || type;
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);

            if (date.toDateString() === today.toDateString()) {
                return 'Aujourd\'hui';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'Hier';
            } else {
                return date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'short' });
            }
        }
    }));
});