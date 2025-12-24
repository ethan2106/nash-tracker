/**
 * IMC Calculator - Vanilla JS
 * Remplace alpine-imc.js pour éviter les conflits avec Chart.js
 * 
 * @description Gestion complète de la page IMC : calculs, UI, graphique
 */

const IMCCalculator = {
    // ============================================================
    // DONNÉES
    // ============================================================
    data: {
        taille: 175,
        poids: 86,
        annee: 1990,
        sexe: 'homme',
        activite: 'sedentaire',
        objectif: 'perte'
    },

    // Ratios nutritionnels NAFLD
    nafldRatios: {
        sucres_max: 0.05,
        graisses_sat_max: 0.03,
        proteines_min: 0.15,
        proteines_max: 0.20,
        fibres_min: 0.014,
        fibres_max: 0.017
    },

    // Références
    chart: null,
    debounceTimer: null,

    // ============================================================
    // INITIALISATION
    // ============================================================
    init() {
        // Récupérer les données depuis les data attributes
        const container = document.querySelector('[data-imc-calculator]');
        if (container) {
            this.data.taille = parseFloat(container.dataset.taille) || 175;
            this.data.poids = parseFloat(container.dataset.poids) || 86;
            this.data.annee = parseInt(container.dataset.annee) || 1990;
            this.data.sexe = container.dataset.sexe || 'homme';
            this.data.activite = container.dataset.activite || 'sedentaire';
            this.data.objectif = container.dataset.objectif || 'perte';
        }

        // Lier les événements
        this.bindEvents();

        // Mise à jour initiale
        this.updateAll();

        // Initialiser le graphique
        this.initChart();

        console.log('IMC Calculator initialized');
    },

    // ============================================================
    // ÉVÉNEMENTS
    // ============================================================
    bindEvents() {
        // Inputs numériques
        ['taille', 'poids', 'annee'].forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                input.value = this.data[field];
                input.addEventListener('input', (e) => {
                    this.data[field] = parseFloat(e.target.value) || 0;
                    this.onInputChange(field, input);
                });
            }
        });

        // Selects
        ['sexe', 'activite', 'objectif'].forEach(field => {
            const select = document.getElementById(field);
            if (select) {
                select.value = this.data[field];
                select.addEventListener('change', (e) => {
                    this.data[field] = e.target.value;
                    this.debouncedUpdate();
                });
            }
        });
    },

    onInputChange(field, input) {
        // Validation visuelle immédiate
        this.updateValidationUI(field, input);
        // Mise à jour débounced
        this.debouncedUpdate();
    },

    debouncedUpdate() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            if (this.validateInputs()) {
                this.updateAll();
            }
        }, 250);
    },

    // ============================================================
    // CALCULS
    // ============================================================
    calculateIMC() {
        if (!this.validateInputs()) return 0;
        return this.data.poids / Math.pow(this.data.taille / 100, 2);
    },

    calculateBMR() {
        if (!this.validateInputs()) return 0;
        const age = new Date().getFullYear() - this.data.annee;
        if (this.data.sexe === 'homme') {
            return 10 * this.data.poids + 6.25 * this.data.taille - 5 * age + 5;
        } else {
            return 10 * this.data.poids + 6.25 * this.data.taille - 5 * age - 161;
        }
    },

    calculateTDEE() {
        const facteurs = {
            'sedentaire': 1.2,
            'leger': 1.375,
            'modere': 1.55,
            'intense': 1.725
        };
        return this.calculateBMR() * (facteurs[this.data.activite] || 1.2);
    },

    calculateCaloriesPerte() {
        const bmr = this.calculateBMR();
        const tdee = this.calculateTDEE();
        const cible = Math.round(tdee * 0.8);
        return Math.max(Math.round(bmr), cible);
    },

    calculateCaloriesMaintien() {
        return Math.round(this.calculateTDEE());
    },

    calculateCaloriesMasse() {
        return Math.round(this.calculateTDEE() * 1.15);
    },

    getIMCInfo(imc) {
        if (imc < 18.5) return { cat: 'Maigreur', color: '#3b82f6', bg: 'blue-200' };
        if (imc < 25) return { cat: 'Normal', color: '#22c55e', bg: 'green-200' };
        if (imc < 30) return { cat: 'Surpoids', color: '#f97316', bg: 'orange-200' };
        return { cat: 'Obésité', color: '#ef4444', bg: 'red-200' };
    },

    getIMCMarkerPosition(imc) {
        const minIMC = 16, maxIMC = 40;
        const percent = ((imc - minIMC) / (maxIMC - minIMC)) * 100;
        return Math.max(0, Math.min(100, percent));
    },

    // Calculs nutritionnels NAFLD
    calculateSucresMax() {
        return Math.round(this.calculateCaloriesPerte() * this.nafldRatios.sucres_max);
    },

    calculateGraissesSatMax() {
        return Math.round(this.calculateCaloriesPerte() * this.nafldRatios.graisses_sat_max);
    },

    calculateProteinesRange() {
        const min = Math.round(this.data.poids * 0.8);
        const max = Math.round(this.data.poids * 1.0);
        return `${min}-${max}`;
    },

    calculateFibresRange() {
        const calories = this.calculateCaloriesPerte();
        const min = Math.round(calories * this.nafldRatios.fibres_min);
        const max = Math.round(calories * this.nafldRatios.fibres_max);
        return `${min}-${max}`;
    },

    // ============================================================
    // VALIDATION
    // ============================================================
    validateInputs() {
        const currentYear = new Date().getFullYear();
        return this.data.taille >= 100 && this.data.taille <= 250 &&
               this.data.poids >= 30 && this.data.poids <= 300 &&
               this.data.annee >= 1920 && this.data.annee <= currentYear;
    },

    updateValidationUI(field, input) {
        const currentYear = new Date().getFullYear();
        const validations = {
            taille: this.data.taille >= 100 && this.data.taille <= 250,
            poids: this.data.poids >= 30 && this.data.poids <= 300,
            annee: this.data.annee >= 1920 && this.data.annee <= currentYear
        };

        const isValid = validations[field];
        const icon = input.parentElement.querySelector('i.fa-solid');
        const errorMsg = input.parentElement.parentElement.querySelector('p');

        // Classes input
        input.classList.remove('border-red-400', 'border-green-400');
        input.classList.add(isValid ? 'border-green-400' : 'border-red-400');

        // Icône
        if (icon) {
            icon.classList.remove('fa-check-circle', 'fa-exclamation-circle', 'text-green-500', 'text-red-500');
            icon.classList.add(isValid ? 'fa-check-circle' : 'fa-exclamation-circle');
            icon.classList.add(isValid ? 'text-green-500' : 'text-red-500');
        }

        // Message d'erreur
        if (errorMsg) {
            errorMsg.style.display = isValid ? 'none' : 'block';
        }
    },

    // ============================================================
    // MISE À JOUR UI
    // ============================================================
    updateAll() {
        const imc = this.calculateIMC();
        const bmr = this.calculateBMR();
        const tdee = this.calculateTDEE();
        const caloriesPerte = this.calculateCaloriesPerte();
        const caloriesMaintien = this.calculateCaloriesMaintien();
        const caloriesMasse = this.calculateCaloriesMasse();
        const imcInfo = this.getIMCInfo(imc);

        // IMC principal
        this.updateElement('.imc-value', imc.toFixed(1));
        this.updateElement('.imc-category', imcInfo.cat);
        
        // Badge catégorie - style dynamique
        const badge = document.querySelector('.imc-category');
        if (badge) {
            badge.style.backgroundColor = `${imcInfo.color}20`;
            badge.style.color = imcInfo.color;
        }

        // Marqueur barre IMC
        const marker = document.getElementById('imc-marker');
        if (marker) {
            const position = this.getIMCMarkerPosition(imc);
            marker.style.left = `${position}%`;
            marker.style.backgroundColor = imcInfo.color;
            marker.style.boxShadow = `0 0 8px 2px ${imcInfo.color}`;
        }

        // Besoins caloriques
        this.updateElement('#bmr-value', `${Math.round(bmr)} kcal/jour`);
        this.updateElement('#tdee-value', `${Math.round(tdee)} kcal/jour`);
        this.updateElement('#perte-value', `${caloriesPerte} kcal/jour`);
        this.updateElement('#maintien-value', `${caloriesMaintien} kcal/jour`);
        this.updateElement('#masse-value', `${caloriesMasse} kcal/jour`);

        // Progression TDEE (SVG)
        const tdeePath = document.querySelector('.tdee-progress');
        if (tdeePath) {
            tdeePath.setAttribute('stroke-dasharray', `${(tdee/3000)*100} 100`);
        }

        // Objectifs NAFLD
        this.updateElement('#sucres-max', this.calculateSucresMax());
        this.updateElement('#graisses-sat-max', this.calculateGraissesSatMax());
        this.updateElement('#proteines-range', this.calculateProteinesRange());
        this.updateElement('#fibres-range', this.calculateFibresRange());

        // Champs cachés pour le formulaire
        this.updateHiddenInputs(imc, caloriesPerte);

        // Graphique
        this.updateChart(imc, bmr, tdee, caloriesPerte, caloriesMaintien, caloriesMasse);
    },
        // Mise à jour simple du texte d'un élément
    updateElement(selector, value) {
        const el = document.querySelector(selector);
        if (el) el.textContent = value;
    },

    updateHiddenInputs(imc, caloriesPerte) {
        // Calculs pour les macros NAFLD
        const sucresMax = this.calculateSucresMax();
        const graissesSatMax = this.calculateGraissesSatMax();
        const proteinesMin = Math.round(this.data.poids * 0.8);
        const proteinesMax = Math.round(this.data.poids * 1.0);
        const fibresMin = Math.round(caloriesPerte * this.nafldRatios.fibres_min);
        const fibresMax = Math.round(caloriesPerte * this.nafldRatios.fibres_max);

        const fields = {
            'hidden-taille': this.data.taille,
            'hidden-poids': this.data.poids,
            'hidden-annee': this.data.annee,
            'hidden-sexe': this.data.sexe,
            'hidden-activite': this.data.activite,
            'hidden-objectif': this.data.objectif,
            'hidden-imc': imc.toFixed(1),
            'hidden-calories-perte': caloriesPerte,
            'hidden-sucres-max': sucresMax,
            'hidden-graisses-sat-max': graissesSatMax,
            'hidden-proteines-min': proteinesMin,
            'hidden-proteines-max': proteinesMax,
            'hidden-fibres-min': fibresMin,
            'hidden-fibres-max': fibresMax
        };

        Object.entries(fields).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) el.value = value;
        });
    },

    // ============================================================
    // GRAPHIQUE CHART.JS
    // ============================================================
    initChart() {
        const ctx = document.getElementById('imcChart');
        if (!ctx || !window.Chart) {
            console.warn('Chart.js not available');
            return;
        }

        const imc = this.calculateIMC();
        const bmr = this.calculateBMR();
        const tdee = this.calculateTDEE();

        this.chart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['IMC', 'BMR', 'TDEE', 'Perte', 'Maintien', 'Masse'],
                datasets: [{
                    label: 'Valeurs santé',
                    data: [
                        parseFloat(imc.toFixed(1)),
                        Math.round(bmr),
                        Math.round(tdee),
                        this.calculateCaloriesPerte(),
                        this.calculateCaloriesMaintien(),
                        this.calculateCaloriesMasse()
                    ],
                    backgroundColor: [
                        '#3b82f6', '#facc15', '#22c55e', '#f97316', '#22c55e', '#3b82f6'
                    ],
                    borderRadius: 8,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Profil santé actuel'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e0e7ff' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { color: '#e0e7ff' },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    },

    updateChart(imc, bmr, tdee, perte, maintien, masse) {
        if (!this.chart) return;

        this.chart.data.datasets[0].data = [
            parseFloat(imc.toFixed(1)),
            Math.round(bmr),
            Math.round(tdee),
            perte,
            maintien,
            masse
        ];
        this.chart.update('none');
    }
};

// ============================================================
// DÉMARRAGE
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    IMCCalculator.init();
});
