document.addEventListener('alpine:init', () => {
    Alpine.data('symptomManager', () => ({
        symptoms: [],
        symptomTypes: {},
        showAddModal: false,
        startDate: '',
        endDate: '',
        newSymptom: {
            type: '',
            intensity: 5,
            date: '',
            notes: ''
        },

        init() {
            // Read data from the element's dataset
            const el = this.$el;
            this.symptoms = JSON.parse(el.dataset.symptoms || '[]');
            this.symptomTypes = JSON.parse(el.dataset.symptomTypes || '{}');
            this.startDate = el.dataset.startDate || '';
            this.endDate = el.dataset.endDate || '';
            this.newSymptom.date = el.dataset.today || '';

            this.loadSymptoms();
        },

        getSymptomLabel(type) {
            return this.symptomTypes[type] || type;
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('fr-FR');
        },

        async loadSymptoms() {
            try {
                const response = await fetch(`?page=api_symptoms&start_date=${this.startDate}&end_date=${this.endDate}`);
                const data = await response.json();
                if (data.success) {
                    this.symptoms = data.symptoms;
                }
            } catch (error) {
                console.error('Erreur chargement symptômes:', error);
            }
        },

        async addSymptom() {
            try {
                const formData = new FormData();
                formData.append('symptom_type', this.newSymptom.type);
                formData.append('intensity', this.newSymptom.intensity);
                formData.append('date', this.newSymptom.date);
                formData.append('notes', this.newSymptom.notes);

                const response = await fetch('?page=symptoms/add', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.showAddModal = false;
                    this.newSymptom = { type: '', intensity: 5, date: this.$el.dataset.today, notes: '' };
                    this.loadSymptoms();
                    window.showNotification('Symptôme ajouté avec succès', 'success');
                } else {
                    window.showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
                }
            } catch (error) {
                console.error('Erreur ajout symptôme:', error);
                window.showNotification('Erreur lors de l\'ajout', 'error');
            }
        },

        async deleteSymptom(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce symptôme ?')) return;

            try {
                const formData = new FormData();
                formData.append('symptom_id', id);

                const response = await fetch('?page=symptoms/delete', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.loadSymptoms();
                    window.showNotification('Symptôme supprimé', 'success');
                } else {
                    window.showNotification(data.message || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur suppression symptôme:', error);
                window.showNotification('Erreur lors de la suppression', 'error');
            }
        },

        handleKeydown(event) {
            if (event.key === 'Escape' && this.showAddModal) {
                this.showAddModal = false;
            }
        }
    }));
});