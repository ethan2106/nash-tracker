// Alpine.js Medicaments Manager - Version ultra-simplifiée et moderne
// Note: Ce manager gère uniquement la logique UI. Les appels API/DB sont dans MedicamentController.php

function medicamentsManager() {
    return {
        // État réactif
        currentDate: new Date(),
        loading: false,
        showMedicamentModal: false,
        showHistoriqueModal: false,
        loadingHistorique: false,
        historiqueData: [],
        savingMedicament: false,
        editingMedicament: null,

        // Formulaire du médicament
        medicamentForm: {
            id: '',
            nom: '',
            dose: '',
            type: 'regulier',
            frequence: '',
            heures_prise: [],
            notes: ''
        },

        medicamentsSections: [
            {
                type: 'regulier',
                title: 'Médicaments Réguliers',
                icon: 'fa-solid fa-calendar-check',
                color: '#2563eb',
                badgeBg: '#dbeafe',
                badgeColor: '#1e40af',
                label: 'Régulier',
                medicaments: []
            },
            {
                type: 'ponctuel',
                title: 'Médicaments Ponctuels',
                icon: 'fa-solid fa-clock',
                color: '#ea580c',
                badgeBg: '#fed7aa',
                badgeColor: '#9a3412',
                label: 'Ponctuel',
                medicaments: []
            }
        ],

        // ==================== UTILITAIRES SÉCURITÉ ====================
        
        /**
         * Échappe les caractères HTML pour prévenir XSS
         * @param {string} str - Chaîne à échapper
         * @returns {string} - Chaîne échappée
         */
        escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Échappe un objet médicament entier
         * @param {Object} med - Objet médicament
         * @returns {Object} - Objet avec valeurs échappées
         */
        sanitizeMedicament(med) {
            return {
                ...med,
                nom: this.escapeHtml(med.nom),
                dose: this.escapeHtml(med.dose),
                notes: this.escapeHtml(med.notes),
                frequence: this.escapeHtml(med.frequence)
            };
        },

        // ==================== INITIALISATION ====================

        async init() {
            // S'assurer que les modals sont fermés au démarrage
            this.showMedicamentModal = false;
            this.showHistoriqueModal = false;
            
            // Écouter les événements clavier pour accessibilité
            this.setupKeyboardNavigation();
            
            await this.loadMedicaments();
        },

        /**
         * Configure la navigation clavier pour l'accessibilité
         */
        setupKeyboardNavigation() {
            // Le focus trap est géré par Alpine x-trap si disponible
            // Sinon, on gère manuellement avec Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.showHistoriqueModal) {
                        this.closeHistorique();
                    } else if (this.showMedicamentModal) {
                        this.showMedicamentModal = false;
                    }
                }
            });
        },

        // ==================== NAVIGATION DATE ====================

        changeDate(delta) {
            this.currentDate.setDate(this.currentDate.getDate() + delta);
            this.loadMedicaments();
        },

        formatDate(date) {
            return date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        // ==================== CHARGEMENT DONNÉES ====================

        async loadMedicaments() {
            this.loading = true;
            try {
                const dateStr = this.currentDate.toISOString().split('T')[0];
                const response = await fetch(`?page=api_medicaments_jour&date=${dateStr}`);
                const data = await response.json();

                if (data.success) {
                    this.processMedicaments(data.medicaments);
                }
            } catch (error) {
                console.error('Erreur chargement:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Traite et sépare les médicaments par section avec sanitization
         */
        processMedicaments(medicamentsData) {
            // Reset sections
            this.medicamentsSections.forEach(section => {
                section.medicaments = [];
            });

            // Distribuer les médicaments dans les bonnes sections
            Object.entries(medicamentsData).forEach(([id, med]) => {
                // Ajouter l'ID et sanitizer
                med.id = id;
                const sanitizedMed = this.sanitizeMedicament(med);
                
                const section = this.medicamentsSections.find(s => s.type === (med.type || 'regulier'));
                if (section) {
                    section.medicaments.push(sanitizedMed);
                }
            });
        },

        // Toggle prise de médicament
        async togglePrise(medId, periode) {
            const section = this.medicamentsSections.find(s =>
                s.medicaments.some(m => m.id == medId)
            );
            if (!section) return;

            const med = section.medicaments.find(m => m.id == medId);
            if (!med) return;

            const currentStatus = med.prises[periode];
            const action = currentStatus === 'pris' ? 'api_annuler_pris' : 'api_marquer_pris';

            try {
                const response = await fetch(`?page=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        medicament_id: medId,
                        periode: periode,
                        date: this.currentDate.toISOString().split('T')[0]
                    })
                });

                const data = await response.json();
                if (data.success) {
                    med.prises = data.prises;
                }
            } catch (error) {
                console.error('Erreur toggle:', error);
            }
        },

        // Modal functions
        showModal(medicament = null) {
            this.editingMedicament = medicament;
            if (medicament) {
                // Mode édition
                this.medicamentForm = {
                    id: medicament.id,
                    nom: medicament.nom,
                    dose: medicament.dose || '',
                    type: medicament.type || 'regulier',
                    frequence: medicament.frequence || '',
                    heures_prise: Array.isArray(medicament.heures_prise) ? [...medicament.heures_prise] : [],
                    notes: medicament.notes || ''
                };
            } else {
                // Mode ajout
                this.resetForm();
            }
            this.showMedicamentModal = true;
        },

        resetForm() {
            this.medicamentForm = {
                id: '',
                nom: '',
                dose: '',
                type: 'regulier',
                frequence: '',
                heures_prise: [],
                notes: ''
            };
        },

        async saveMedicament() {
            if (!this.medicamentForm.nom.trim()) return;

            this.savingMedicament = true;
            try {
                const isEdit = this.medicamentForm.id;
                const endpoint = isEdit ? 'api_update_medicament' : 'api_create_medicament';

                const response = await fetch(`?page=${endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.medicamentForm)
                });

                const data = await response.json();
                if (data.success) {
                    this.showMedicamentModal = false;
                    this.resetForm();
                    await this.loadMedicaments();
                } else {
                    alert('Erreur lors de la sauvegarde: ' + (data.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur sauvegarde:', error);
                alert('Erreur lors de la sauvegarde');
            } finally {
                this.savingMedicament = false;
            }
        },

        async editMedicament(id) {
            try {
                const response = await fetch(`?page=api_medicament&id=${id}`);
                const data = await response.json();

                if (data.success) {
                    this.showModal(data.medicament);
                } else {
                    alert('Erreur lors du chargement du médicament');
                }
            } catch (error) {
                console.error('Erreur chargement médicament:', error);
                alert('Erreur lors du chargement du médicament');
            }
        },

        async deleteMedicament(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?')) return;

            try {
                const response = await fetch('?page=api_delete_medicament', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const data = await response.json();
                if (data.success) {
                    await this.loadMedicaments();
                } else {
                    alert('Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur suppression:', error);
                alert('Erreur lors de la suppression');
            }
        },

        showHistorique() {
            this.showHistoriqueModal = true;
            this.loadHistorique();
        },

        closeHistorique() {
            this.showHistoriqueModal = false;
            this.historiqueData = [];
        },

        async loadHistorique() {
            this.loadingHistorique = true;
            try {
                // Charger les 30 derniers jours
                const endDate = new Date().toISOString().split('T')[0];
                const startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                
                const response = await fetch(`?page=api_historique_medicaments&start=${startDate}&end=${endDate}`);
                const data = await response.json();

                if (data.success) {
                    this.historiqueData = this.groupHistoriqueByDate(data.historique || []);
                }
            } catch (error) {
                console.error('Erreur chargement historique:', error);
            } finally {
                this.loadingHistorique = false;
            }
        },

        groupHistoriqueByDate(prises) {
            const grouped = {};
            prises.forEach(prise => {
                const date = prise.date_prise || prise.date;
                if (!grouped[date]) {
                    grouped[date] = { date: date, prises: [] };
                }
                grouped[date].prises.push(prise);
            });
            return Object.values(grouped).sort((a, b) => new Date(b.date) - new Date(a.date));
        },

        formatHistoriqueDate(dateStr) {
            const date = new Date(dateStr);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === today.toDateString()) {
                return "Aujourd'hui";
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'Hier';
            }
            return date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            });
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return 'à ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },

        // Statistiques pour le header
        getTotalPrisesToday() {
            let total = 0;
            this.medicamentsSections.forEach(section => {
                section.medicaments.forEach(med => {
                    if (med.prises) {
                        Object.values(med.prises).forEach(status => {
                            if (status === 'pris') total++;
                        });
                    }
                });
            });
            return total;
        },

        getMedicamentsCount(type) {
            const section = this.medicamentsSections.find(s => s.type === type);
            return section ? section.medicaments.length : 0;
        },

        // Classes et icônes des boutons
        getButtonClass(status) {
            return status === 'pris'
                ? 'bg-green-500 hover:bg-green-600 text-white pris'
                : 'bg-blue-100 hover:bg-blue-200 text-blue-800 non-pris';
        },

        getButtonIcon(status) {
            return status === 'pris' ? 'fa-solid fa-check' : 'fa-solid fa-clock';
        },

        capitalizePeriode(periode) {
            return periode.charAt(0).toUpperCase() + periode.slice(1);
        }
    }
}

// Exposer la fonction globalement pour Alpine.js
window.medicamentsManager = medicamentsManager;