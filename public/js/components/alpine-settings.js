/**
 * Alpine.js Component - Settings Manager
 * Gestion des paramètres utilisateur : onglets, validation, AJAX
 */

window.settingsManager = function() {
    return {
        // État des onglets
        activeTab: 'compte',
        
        // État des formulaires
        loading: {
            email: false,
            pseudo: false,
            password: false,
            delete: false,
            'eau_objectif_litre': false,
            'activite_objectif_minutes': false,
            'lipides_max_g': false,
            'sucres_max_g': false,
            'imc_seuil_sous_poids': false,
            'imc_seuil_normal': false,
            'imc_seuil_surpoids': false,
            'notify_hydration_enabled': false,
            'notify_activity_enabled': false,
            'notify_goals_enabled': false,
            'notify_quiet_start_hour': false,
            'notify_quiet_end_hour': false
        },
        
        // Données formulaires
        email: '',
        pseudo: '',
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
        deletePassword: '',
        deleteConfirmation: '',
        
        // Configuration utilisateur
        userConfig: {
            eau_objectif_litre: 2.0,
            activite_objectif_minutes: 30,
            lipides_max_g: 22,
            sucres_max_g: 50,
            imc_seuil_sous_poids: 18.5,
            imc_seuil_normal: 25.0,
            imc_seuil_surpoids: 30.0,
            notify_hydration_enabled: 1,
            notify_activity_enabled: 1,
            notify_goals_enabled: 1,
            notify_quiet_start_hour: 22,
            notify_quiet_end_hour: 7
        },

        // Édition locale des heures silencieuses (staging avant sauvegarde)
        quietStart: 22,
        quietEnd: 7,
        
        // Validation
        errors: {
            email: '',
            pseudo: '',
            password: '',
            delete: '',
            'eau_objectif_litre': '',
            'activite_objectif_minutes': '',
            'lipides_max_g': '',
            'sucres_max_g': '',
            'imc_seuil_sous_poids': '',
            'imc_seuil_normal': '',
            'imc_seuil_surpoids': '',
            'notify_hydration_enabled': '',
            'notify_activity_enabled': '',
            'notify_goals_enabled': '',
            'notify_quiet_start_hour': '',
            'notify_quiet_end_hour': ''
        },
        
        // Force du mot de passe
        passwordStrength: 0,
        passwordStrengthLabel: '',
        
        // Initialisation
        init() {
            // Récupérer valeurs actuelles depuis data attributes
            this.email = this.$el.dataset.email || '';
            this.pseudo = this.$el.dataset.pseudo || '';
            
            // Charger configuration utilisateur
            const userConfigData = this.$el.dataset.userConfig;
            if (userConfigData) {
                try {
                    this.userConfig = { ...this.userConfig, ...JSON.parse(userConfigData) };
                } catch (e) {
                    console.error('Erreur parsing userConfig:', e);
                }
            }

            // Initialiser les heures silencieuses éditables depuis la config
            this.quietStart = Number(this.userConfig.notify_quiet_start_hour ?? 22);
            this.quietEnd = Number(this.userConfig.notify_quiet_end_hour ?? 7);
        },
        
        // Changer d'onglet
        changeTab(tab) {
            this.activeTab = tab;
            this.resetErrors();
        },
        
        // Reset erreurs
        resetErrors() {
            this.errors = {
                email: '',
                pseudo: '',
                password: '',
                delete: '',
                'eau_objectif_litre': '',
                'activite_objectif_minutes': '',
                'lipides_max_g': '',
                'sucres_max_g': '',
                'imc_seuil_sous_poids': '',
                'imc_seuil_normal': '',
                'imc_seuil_surpoids': '',
                'notify_hydration_enabled': '',
                'notify_activity_enabled': '',
                'notify_goals_enabled': '',
                'notify_quiet_start_hour': '',
                'notify_quiet_end_hour': '',
                'quiet_hours': ''
            };
        },
        
        // Validation email temps réel
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.email) {
                this.errors.email = 'Email requis';
                return false;
            }
            if (!emailRegex.test(this.email)) {
                this.errors.email = 'Email invalide';
                return false;
            }
            this.errors.email = '';
            return true;
        },
        
        // Validation pseudo temps réel
        validatePseudo() {
            const pseudoRegex = /^[a-zA-Z0-9_-]{3,50}$/;
            if (!this.pseudo) {
                this.errors.pseudo = 'Pseudo requis';
                return false;
            }
            if (!pseudoRegex.test(this.pseudo)) {
                this.errors.pseudo = 'Pseudo invalide (3-50 caractères, alphanumérique, _ -)';
                return false;
            }
            this.errors.pseudo = '';
            return true;
        },
        
        // Validation mot de passe temps réel
        validatePassword() {
            if (!this.newPassword) {
                this.passwordStrength = 0;
                this.passwordStrengthLabel = '';
                this.errors.password = '';
                return false;
            }
            
            // Calcul force mot de passe
            let strength = 0;
            if (this.newPassword.length >= 8) strength++;
            if (this.newPassword.length >= 12) strength++;
            if (/[a-z]/.test(this.newPassword) && /[A-Z]/.test(this.newPassword)) strength++;
            if (/[0-9]/.test(this.newPassword)) strength++;
            if (/[^a-zA-Z0-9]/.test(this.newPassword)) strength++;
            
            this.passwordStrength = strength;
            
            if (strength <= 1) this.passwordStrengthLabel = 'Faible';
            else if (strength <= 3) this.passwordStrengthLabel = 'Moyen';
            else this.passwordStrengthLabel = 'Fort';
            
            // Validation
            if (this.newPassword.length < 8) {
                this.errors.password = 'Minimum 8 caractères requis';
                return false;
            }
            if (this.newPassword !== this.confirmPassword) {
                this.errors.password = 'Les mots de passe ne correspondent pas';
                return false;
            }
            this.errors.password = '';
            return true;
        },
        
        // Couleur force mot de passe
        getPasswordStrengthColor() {
            if (this.passwordStrength <= 1) return 'bg-red-500';
            if (this.passwordStrength <= 3) return 'bg-orange-500';
            return 'bg-green-500';
        },
        
        // Largeur barre force mot de passe
        getPasswordStrengthWidth() {
            return `${(this.passwordStrength / 5) * 100}%`;
        },
        
        // Validation configuration utilisateur
        validateConfig(key) {
            const value = parseFloat(this.userConfig[key]);
            const configRanges = {
                'eau_objectif_litre': { min: 0.5, max: 5.0 },
                'activite_objectif_minutes': { min: 10, max: 180 },
                'lipides_max_g': { min: 10, max: 50 },
                'sucres_max_g': { min: 20, max: 100 },
                'imc_seuil_sous_poids': { min: 15.0, max: 20.0 },
                'imc_seuil_normal': { min: 20.0, max: 30.0 },
                'imc_seuil_surpoids': { min: 25.0, max: 35.0 },
                'notify_hydration_enabled': { min: 0, max: 1 },
                'notify_activity_enabled': { min: 0, max: 1 },
                'notify_goals_enabled': { min: 0, max: 1 },
                'notify_quiet_start_hour': { min: 0, max: 23 },
                'notify_quiet_end_hour': { min: 0, max: 23 }
            };
            
            if (isNaN(value)) {
                this.errors[key] = 'Valeur numérique requise';
                return false;
            }
            
            const range = configRanges[key];
            if (value < range.min) {
                this.errors[key] = `Minimum ${range.min} requis`;
                return false;
            }
            if (value > range.max) {
                this.errors[key] = `Maximum ${range.max} autorisé`;
                return false;
            }
            
            this.errors[key] = '';
            return true;
        },

        // Validation dédiée aux heures silencieuses (ensemble)
        validateQuietHours() {
            this.errors['quiet_hours'] = '';
            const s = Number(this.quietStart);
            const e = Number(this.quietEnd);
            if (Number.isNaN(s) || Number.isNaN(e)) {
                this.errors['quiet_hours'] = 'Heures invalides';
                return false;
            }
            if (s < 0 || s > 23 || e < 0 || e > 23) {
                this.errors['quiet_hours'] = 'Les heures doivent être entre 0 et 23';
                return false;
            }
            if (s === e) {
                this.errors['quiet_hours'] = 'La période silencieuse ne peut pas durer 24h';
                return false;
            }
            return true;
        },

        // Sauvegarder les heures silencieuses ensemble (1 seule notification)
        async saveQuietHours() {
            if (!this.validateQuietHours()) return;

            // Empêcher requêtes concurrentes
            if (this.loading['notify_quiet_start_hour'] || this.loading['notify_quiet_end_hour']) return;

            this.loading['notify_quiet_start_hour'] = true;
            this.loading['notify_quiet_end_hour'] = true;
            this.errors['quiet_hours'] = '';

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            try {
                // Maj début
                let fd1 = new FormData();
                fd1.append('config_key', 'notify_quiet_start_hour');
                fd1.append('config_value', String(this.quietStart));
                fd1.append('csrf_token', csrf);
                const r1 = await fetch('?page=settings/update-user-config', { method: 'POST', body: fd1 });
                const j1 = await r1.json();
                if (!j1.success) {
                    this.errors['quiet_hours'] = j1.message || 'Erreur enregistrement heure de début';
                    window.showNotification(this.errors['quiet_hours'], 'error');
                    return;
                }

                // Maj fin
                let fd2 = new FormData();
                fd2.append('config_key', 'notify_quiet_end_hour');
                fd2.append('config_value', String(this.quietEnd));
                fd2.append('csrf_token', csrf);
                const r2 = await fetch('?page=settings/update-user-config', { method: 'POST', body: fd2 });
                const j2 = await r2.json();
                if (!j2.success) {
                    this.errors['quiet_hours'] = j2.message || 'Erreur enregistrement heure de fin';
                    window.showNotification(this.errors['quiet_hours'], 'error');
                    return;
                }

                // Sync state et feedback
                this.userConfig.notify_quiet_start_hour = this.quietStart;
                this.userConfig.notify_quiet_end_hour = this.quietEnd;
                window.showNotification('Période silencieuse enregistrée', 'success');
            } catch (error) {
                console.error(error);
                this.errors['quiet_hours'] = 'Erreur de connexion';
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading['notify_quiet_start_hour'] = false;
                this.loading['notify_quiet_end_hour'] = false;
            }
        },

        // Rétablir les valeurs par défaut pour les heures silencieuses
        resetQuietHoursDefaults() {
            this.quietStart = 22;
            this.quietEnd = 7;
            this.saveQuietHours();
        },
        
        // Mettre à jour configuration utilisateur (AJAX)
        async updateUserConfig(key) {
            try {
                if (!this.validateConfig(key)) return;
                
                // Empêcher les appels multiples
                if (this.loading[key]) return;
                
                this.loading[key] = true;
                this.resetErrors();
                
                const formData = new FormData();
                formData.append('config_key', key);
                formData.append('config_value', this.userConfig[key]);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/update-user-config', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.showNotification(result.message, 'success');
                } else {
                    this.errors[key] = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading[key] = false;
            }
        },
        
        // Réinitialiser configuration utilisateur (AJAX)
        async resetConfig(key) {
            try {
                if (!confirm('Êtes-vous sûr de vouloir réinitialiser cette valeur à sa valeur par défaut ?')) {
                    return;
                }
                
                this.loading[key] = true;
                this.resetErrors();
                
                const formData = new FormData();
                formData.append('config_key', key);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/reset-user-config', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Recharger la page pour obtenir les nouvelles valeurs
                    window.showNotification(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.errors[key] = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading[key] = false;
            }
        },
        
        // Mettre à jour email (AJAX)
        async updateEmail() {
            if (!this.validateEmail()) return;
            
            this.loading.email = true;
            this.resetErrors();
            
            try {
                const formData = new FormData();
                formData.append('email', this.email);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/update-email', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.showNotification(result.message, 'success');
                    // Mettre à jour affichage pseudo dans header si nécessaire
                } else {
                    this.errors.email = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading.email = false;
            }
        },
        
        // Mettre à jour pseudo (AJAX)
        async updatePseudo() {
            if (!this.validatePseudo()) return;
            
            this.loading.pseudo = true;
            this.resetErrors();
            
            try {
                const formData = new FormData();
                formData.append('pseudo', this.pseudo);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/update-pseudo', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.showNotification(result.message, 'success');
                    // Recharger pour mettre à jour le header
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.errors.pseudo = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading.pseudo = false;
            }
        },
        
        // Mettre à jour mot de passe (AJAX)
        async updatePassword() {
            if (!this.currentPassword) {
                this.errors.password = 'Mot de passe actuel requis';
                return;
            }
            if (!this.validatePassword()) return;
            
            this.loading.password = true;
            this.resetErrors();
            
            try {
                const formData = new FormData();
                formData.append('current_password', this.currentPassword);
                formData.append('new_password', this.newPassword);
                formData.append('confirm_password', this.confirmPassword);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/update-password', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.showNotification(result.message, 'success');
                    // Reset formulaire
                    this.currentPassword = '';
                    this.newPassword = '';
                    this.confirmPassword = '';
                    this.passwordStrength = 0;
                    this.passwordStrengthLabel = '';
                } else {
                    this.errors.password = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading.password = false;
            }
        },
        
        // Supprimer compte (AJAX avec confirmation)
        async deleteAccount() {
            if (!this.deletePassword) {
                this.errors.delete = 'Mot de passe requis';
                return;
            }
            if (this.deleteConfirmation !== 'SUPPRIMER') {
                this.errors.delete = 'Veuillez taper SUPPRIMER pour confirmer';
                return;
            }
            
            // Double confirmation
            if (!confirm('Êtes-vous absolument sûr(e) ? Cette action est IRRÉVERSIBLE. Toutes vos données seront perdues.')) {
                return;
            }
            
            this.loading.delete = true;
            this.resetErrors();
            
            try {
                const formData = new FormData();
                formData.append('password', this.deletePassword);
                formData.append('confirmation', this.deleteConfirmation);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('?page=settings/delete-account', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.showNotification(result.message, 'success');
                    // Redirection vers home
                    setTimeout(() => window.location.href = result.redirect, 1500);
                } else {
                    this.errors.delete = result.message;
                    window.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.showNotification('Erreur de connexion', 'error');
            } finally {
                this.loading.delete = false;
            }
        }
    };
};
