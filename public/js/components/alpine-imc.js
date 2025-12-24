/**
 * Gestionnaire Alpine.js pour la page IMC
 * Migre la logique JavaScript vanilla vers un composant réactif Alpine.js
 * Inclut calculs IMC/BMR/TDEE, animations, et gestion du graphique Chart.js
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('imcManager', () => ({
        // Données réactives injectées depuis PHP via data attributes
        taille: 175,
        poids: 86,
        annee: 1990,
        sexe: 'homme',
        activite: 'sedentaire',
        objectif: 'perte',

        // État du graphique
        chart: null,
        chartData: null,

        // État des animations
        animatingElements: new Set(),

        // Ratios nutritionnels NAFLD (en % des calories totales)
        nafldRatios: {
            sucres_max: 0.05,      // 5% max des calories
            graisses_sat_max: 0.03, // 3% max des calories (10% des graisses totales)
            proteines_min: 0.15,    // 15% min des calories
            proteines_max: 0.20,    // 20% max des calories
            fibres_min: 0.014,      // 1.4g/100kcal min
            fibres_max: 0.017       // 1.7g/100kcal max
        },

        init() {
            // Injecter les données depuis les data attributes
            const el = this.$el;
            this.taille = parseFloat(el.dataset.taille) || 175;
            this.poids = parseFloat(el.dataset.poids) || 86;
            this.annee = parseInt(el.dataset.annee) || 1990;
            this.sexe = el.dataset.sexe || 'homme';
            this.activite = el.dataset.activite || 'sedentaire';
            this.objectif = el.dataset.objectif || 'perte';

            // Créer la fonction debounced une seule fois
            this.debouncedUpdate = this.debounce(() => {
                // Capturer toutes les valeurs AVANT $nextTick pour éviter la réactivité Alpine
                const values = {
                    imc: parseFloat(this.imc.toFixed(1)),
                    bmr: Math.round(this.bmr),
                    tdee: Math.round(this.tdee),
                    caloriesPerte: Math.round(this.caloriesPerte),
                    caloriesMaintien: Math.round(this.caloriesMaintien),
                    caloriesMasse: Math.round(this.caloriesMasse)
                };

                this.$nextTick(() => {
                    this.animateNumber('.text-5xl', values.imc, 1);
                    this.animateElement('.px-4.py-1');
                    this.animateElement('.text-gray-600');
                    this.animateNumber('#bmr-value', values.bmr, 0);
                    this.animateNumber('#tdee-value', values.tdee, 0);
                    this.animateNumber('#perte-value', values.caloriesPerte, 0);
                    this.animateNumber('#maintien-value', values.caloriesMaintien, 0);
                    this.animateNumber('#masse-value', values.caloriesMasse, 0);
                    this.updateChartDataWithValues(values);
                });
            }, 250);

            // Initialiser le graphique au chargement
            this.$nextTick(() => {
                this.initChart();
                // loadChartData() supprimé car redondant - le graphique utilise les données calculées
            });
        },

        /**
         * Calcule l'IMC
         */
        get imc() {
            if (!this.validateInputs()) return 0;
            return this.poids / Math.pow(this.taille / 100, 2);
        },

        /**
         * Calcule la catégorie IMC avec couleur
         */
        get imcInfo() {
            const imc = this.imc;
            if (imc < 18.5) return { cat: 'Maigreur', color: '#3b82f6', bg: 'blue-200' };
            if (imc < 25) return { cat: 'Normal', color: '#22c55e', bg: 'green-200' };
            if (imc < 30) return { cat: 'Surpoids', color: '#f97316', bg: 'orange-200' };
            return { cat: 'Obésité', color: '#ef4444', bg: 'red-200' };
        },

        /**
         * Position du marqueur IMC en pourcentage
         */
        get imcMarkerPosition() {
            const imc = this.imc;
            const minIMC = 16, maxIMC = 40;
            const percent = ((imc - minIMC) / (maxIMC - minIMC)) * 100;
            return Math.max(0, Math.min(100, percent));
        },

        /**
         * Calcule le BMR (Métabolisme de Base)
         */
        get bmr() {
            if (!this.validateInputs()) return 0;
            const age = new Date().getFullYear() - this.annee;
            if (this.sexe === 'homme') {
                return 10 * this.poids + 6.25 * this.taille - 5 * age + 5;
            } else {
                return 10 * this.poids + 6.25 * this.taille - 5 * age - 161;
            }
        },

        /**
         * Calcule le TDEE (Dépense Énergétique Totale)
         */
        get tdee() {
            const facteurs = {
                'sedentaire': 1.2,
                'leger': 1.375,
                'modere': 1.55,
                'intense': 1.725
            };
            return this.bmr * (facteurs[this.activite] || 1.2);
        },

        /**
         * Calories pour perte de poids
         */
        get caloriesPerte() {
            // Perte de poids basée sur TDEE réel avec déficit de 20%, jamais sous BMR
            const cible = Math.round(this.tdee * 0.8);
            return Math.max(Math.round(this.bmr), cible);
        },

        /**
         * Calories pour maintien
         */
        get caloriesMaintien() {
            return Math.round(this.tdee);
        },

        /**
         * Calories pour prise de masse
         */
        get caloriesMasse() {
            return Math.round(this.tdee * 1.15);
        },

        /**
         * Calculs nutritionnels NAFLD (en grammes)
         */
        get sucresMax() {
            return Math.round(this.caloriesPerte * this.nafldRatios.sucres_max);
        },

        get graissesSatMax() {
            return Math.round(this.caloriesPerte * this.nafldRatios.graisses_sat_max);
        },

        get proteinesRange() {
            const min = Math.round(this.poids * 0.8);
            const max = Math.round(this.poids * 1.0);
            return `${min}-${max}`;
        },

        get fibresRange() {
            const min = Math.round(this.caloriesPerte * this.nafldRatios.fibres_min);
            const max = Math.round(this.caloriesPerte * this.nafldRatios.fibres_max);
            return `${min}-${max}`;
        },

        /**
         * Validation des inputs
         */
        validateInputs() {
            return this.taille >= 100 && this.taille <= 250 &&
                   this.poids >= 30 && this.poids <= 300 &&
                   this.annee >= 1920 && this.annee <= new Date().getFullYear();
        },

        /**
         * Met à jour les champs cachés du formulaire
         */
        updateHiddenInputs() {
            const hiddenFields = {
                'hidden-taille': this.taille,
                'hidden-poids': this.poids,
                'hidden-annee': this.annee,
                'hidden-sexe': this.sexe,
                'hidden-activite': this.activite,
                'hidden-objectif': this.objectif,
                'hidden-imc': this.imc.toFixed(1),
                'hidden-calories-perte': this.caloriesPerte
            };

            Object.entries(hiddenFields).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.value = value;
            });
        },

        /**
         * Animation d'un élément avec bounce
         */
        animateElement(el) {
            // Si on passe un string, on querySelector, sinon on prend directement l'élément
            if (typeof el === 'string') {
                el = document.querySelector(el);
            }

            if (!el || this.animatingElements.has(el)) return;

            this.animatingElements.add(el);
            el.classList.add('animate-bounce');
            setTimeout(() => {
                el.classList.remove('animate-bounce');
                this.animatingElements.delete(el);
            }, 1000);
        },

        /**
         * Animation d'un nombre spécifique
         */
        animateNumber(selector, to, precision = 1) {
            const el = document.querySelector(selector);
            if (!el) return;

            const from = parseFloat(el.textContent.replace(/\s|,/g, '')) || 0;
            let start = null;

            const step = (ts) => {
                if (!start) start = ts;
                const progress = Math.min((ts - start) / 700, 1);
                const value = from + (to - from) * progress;
                el.textContent = value.toFixed(precision);
                if (progress < 1) requestAnimationFrame(step);
            };

            requestAnimationFrame(step);
        },

        /**
         * Utility pour debounce les appels de fonction
         */
        debounce(func, wait) {
            return (...args) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => func.apply(this, args), wait);
            };
        },

        /**
         * Gestionnaire d'événement pour les inputs (avec debounce)
         */
        onInputChange() {
            if (!this.validateInputs()) {
                this.showValidationError();
                return;
            }

            // Mettre à jour les champs cachés immédiatement
            this.updateHiddenInputs();

            // Utiliser la fonction debounced stockée
            this.debouncedUpdate();
        },

        /**
         * Affiche les erreurs de validation
         */
        showValidationError() {
            const inputs = ['taille', 'poids', 'annee'];
            inputs.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.add('border-red-500');
                    setTimeout(() => el.classList.remove('border-red-500'), 2000);
                }
            });
        },

        /**
         * Initialise le graphique Chart.js
         */
        initChart() {
            const ctx = document.getElementById('imcChart');
            if (!ctx || !window.Chart) return;

            // Éviter la réinitialisation si le graphique existe déjà
            if (this.chart) {
                return;
            }

            // Copier les valeurs dans des variables simples pour éviter la réactivité Alpine
            const initialData = [
                parseFloat(this.imc.toFixed(1)),
                Math.round(this.bmr),
                Math.round(this.tdee),
                Math.round(this.caloriesPerte),
                Math.round(this.caloriesMaintien),
                Math.round(this.caloriesMasse)
            ];

            this.chart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['IMC', 'BMR', 'TDEE', 'Perte', 'Maintien', 'Masse'],
                    datasets: [{
                        label: 'Valeurs santé',
                        data: initialData,
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
                    },
                    elements: {
                        bar: {
                            borderRadius: 8,
                            borderWidth: 2
                        }
                    }
                }
            });
        },

        /**
         * Met à jour les données du graphique sans recréer l'instance
         */
        updateChartData() {
            if (!this.chart || !this.chart.data || !this.chart.data.datasets || !this.chart.data.datasets[0]) return;

            // Copier les valeurs dans des variables simples pour éviter la réactivité Alpine
            // pendant la mise à jour de Chart.js (évite boucle infinie)
            const data = [
                parseFloat(this.imc.toFixed(1)),
                Math.round(this.bmr),
                Math.round(this.tdee),
                Math.round(this.caloriesPerte),
                Math.round(this.caloriesMaintien),
                Math.round(this.caloriesMasse)
            ];

            this.chart.data.datasets[0].data = data;
            this.chart.update('none'); // 'none' pour éviter les animations qui peuvent causer des problèmes
        },

        /**
         * Met à jour le graphique avec des valeurs pré-calculées (évite réactivité)
         */
        updateChartDataWithValues(values) {
            if (!this.chart || !this.chart.data || !this.chart.data.datasets || !this.chart.data.datasets[0]) return;

            this.chart.data.datasets[0].data = [
                values.imc,
                values.bmr,
                values.tdee,
                values.caloriesPerte,
                values.caloriesMaintien,
                values.caloriesMasse
            ];
            this.chart.update('none');
        },

        /**
         * Animation d'un élément spécifique au survol
         */
        onNumberHover(element) {
            if (element && element.classList) {
                this.animateElement(element);
            }
        }
    }));
});