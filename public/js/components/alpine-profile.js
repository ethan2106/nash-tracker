document.addEventListener('alpine:init', () => {
    Alpine.data('profileManager', () => ({
        // État des compteurs animés
        counters: {
            imc: { current: 0, target: 0 },
            calories: { current: 0, target: 0 },
            objectifs: { current: 0, target: 0 },
            eau: { current: 0, target: 0 },
            score: { current: 0, target: 0 }
        },

        // État des activités récentes
        activities: [],
        currentPage: 1,
        totalPages: 1,
        loading: false,
        
        // État du collapse explication du score
        showScoreDetails: false,

        // Référence au graphique Chart.js
        nutritionChart: null,
        chartInitialized: false, // Flag pour éviter les appels multiples

        // Animation d'un compteur
        animateCounter(counterName, duration = 1000) {
            const counter = this.counters[counterName];
            if (!counter || counter.target === 0) return;

            const startValue = counter.current;
            const endValue = counter.target;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const rawValue = startValue + (endValue - startValue) * easeOut;

                // Formater selon le type de compteur
                if (counterName === 'imc' || counterName === 'eau') {
                    counter.current = Math.round(rawValue * 10) / 10; // 1 décimale
                } else {
                    counter.current = Math.round(rawValue); // Entier
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        },

        // Charger les activités pour une page
        async loadActivities(page = 1) {
            if (this.loading) return;

            this.loading = true;
            try {
                const response = await fetch(`?page=api_recent_activities&recent_page=${page}&limit=5`, {
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Erreur HTTP');

                const data = await response.json();
                if (data.success) {
                    this.activities = data.activities || [];
                    this.currentPage = data.page || 1;
                    this.totalPages = Math.ceil((data.total || 0) / (data.limit || 5));

                    // Mettre à jour l'URL sans recharger
                    history.replaceState(null, '', `?page=profile&recent_page=${page}#recent-activities`);
                }
            } catch (error) {
                console.error('Erreur chargement activités:', error);
                // Fallback vers navigation classique
                window.location.href = `?page=profile&recent_page=${page}#recent-activities`;
            } finally {
                this.loading = false;
            }
        },

        // Formater la date d'une activité
        formatActivityDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

            if (diffDays === 0) {
                return 'Aujourd\'hui ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            } else if (diffDays === 1) {
                return 'Hier ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            } else {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
        },

        // Échapper le HTML
        escapeHtml(str) {
            if (!str) return '';
            return (''+str).replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
            });
        },

        // Animation des barres de progression
        animateProgressBars() {
            const bars = document.querySelectorAll('.progress-bar-fill');
            bars.forEach((bar, index) => {
                const width = bar.dataset.width;
                if (width && width > 0) {
                    setTimeout(() => {
                        bar.style.transition = 'width 1s ease-out';
                        bar.style.width = width + '%';
                    }, index * 200);
                }
            });
        },

        // Animation du cercle score
        animateScoreCircle() {
            const path = document.querySelector('.score-circle path:last-child');
            if (!path) return;

            const totalLength = path.getTotalLength();
            path.style.strokeDasharray = totalLength;
            path.style.strokeDashoffset = totalLength;

            const score = this.counters.score.target;
            setTimeout(() => {
                const offset = totalLength - (score / 100) * totalLength;
                path.style.strokeDashoffset = offset;
            }, 500);
        },

        // Initialisation du graphique nutritionnel (bulletproof)
        initNutritionChart() {
            // Éviter les appels multiples
            if (this.chartInitialized) {
                console.log('Chart already initialized, skipping');
                return;
            }

            const canvas = document.getElementById('nutritionChart');
            if (!canvas) {
                console.warn('Canvas nutritionChart not found');
                return;
            }

            const tryCreate = () => {
                // Vérifier que le canvas est visible et a une taille
                if (canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
                    this.createChart(canvas);
                    this.chartInitialized = true; // Marquer comme initialisé
                } else {
                    setTimeout(tryCreate, 100);
                }
            };

            tryCreate();
        },

        // Méthode séparée pour créer le graphique
        createChart(canvasElement) {
            try {
                // Vérifier le contexte 2D
                const context2d = canvasElement.getContext('2d');
                if (!context2d) {
                    console.error('Cannot get 2D context from canvas nutritionChart');
                    return;
                }

                // Détruire l'ancienne instance AVANT d'en créer une nouvelle (double sécurité)
                if (this.nutritionChart) {
                    this.nutritionChart.destroy();
                    this.nutritionChart = null;
                }

                // Données depuis data attribute
                const weeklyNutrition = this.$el.dataset.weeklyNutrition ? JSON.parse(this.$el.dataset.weeklyNutrition) : [];
                const caloriesData = weeklyNutrition.length ? weeklyNutrition.map(d => d.calories) : [0,0,0,0,0,0,0];
                const proteinesData = weeklyNutrition.length ? weeklyNutrition.map(d => d.proteines) : [0,0,0,0,0,0,0];

                // Créer immédiatement le graphique (pas de délai nécessaire)
                this.nutritionChart = new Chart(canvasElement, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Calories',
                        data: caloriesData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }, {
                        label: 'Protéines (g)',
                        data: proteinesData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 12, weight: 'bold' },
                                color: '#374151'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 2,
                            padding: 12,
                            displayColors: true,
                            boxWidth: 10,
                            boxHeight: 10,
                            bodyFont: { size: 13 },
                            titleFont: { size: 14, weight: 'bold' },
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    // Affichage formaté selon le dataset
                                    const label = context.dataset.label || '';
                                    const value = Math.round(context.parsed.y);
                                    return label + ': ' + value.toLocaleString();
                                },
                                afterBody: function(context) {
                                    // Ajouter des informations supplémentaires
                                    const caloriesValue = context[0].parsed.y;
                                    const objectif = 1800; // Vous pouvez injecter la vraie valeur depuis PHP
                                    const ecart = caloriesValue - objectif;
                                    const ecartText = ecart >= 0 ? '+' + ecart : ecart;
                                    
                                    return [
                                        '',
                                        'Objectif: ' + objectif.toLocaleString() + ' kcal',
                                        'Écart: ' + ecartText + ' kcal'
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 }
                            }
                        },
                        x: {
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 }
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 7,
                            hoverBorderWidth: 2,
                            hitRadius: 10
                        },
                        line: {
                            borderWidth: 3
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    }
                }
            });
            } catch (error) {
                console.error('Error creating nutrition chart:', error);
            }
        },

        // Initialisation
        init() {
            // Récupération des valeurs depuis les attributs data-
            const statsData = this.$el.dataset.stats ? JSON.parse(this.$el.dataset.stats) : {};

            this.counters.imc.target = parseFloat(statsData.imc) || 0;
            this.counters.calories.target = parseInt(statsData.calories_target) || 0;
            this.counters.objectifs.target = parseInt(statsData.objectifs_completion) || 0;
            this.counters.eau.target = parseFloat(statsData.eau_today) || 0;
            this.counters.score.target = parseInt(statsData.score) || 0;

            // Charger les activités initiales depuis les données PHP
            const activitiesData = this.$el.dataset.activities ? JSON.parse(this.$el.dataset.activities) : [];
            this.activities = activitiesData;
            this.currentPage = parseInt(this.$el.dataset.currentPage) || 1;
            this.totalPages = Math.ceil((parseInt(this.$el.dataset.total) || 0) / 5);

            // Animation séquentielle
            setTimeout(() => this.animateCounter('imc'), 100);
            setTimeout(() => this.animateCounter('calories'), 200);
            setTimeout(() => this.animateCounter('objectifs'), 300);
            setTimeout(() => this.animateCounter('eau'), 400);
            setTimeout(() => this.animateCounter('score', 1500), 500);

            // Animations supplémentaires
            setTimeout(() => this.animateProgressBars(), 600);
            setTimeout(() => this.animateScoreCircle(), 700);

            // Utiliser nextTick pour s'assurer que le DOM est complètement rendu
            this.$nextTick(() => {
                setTimeout(() => this.initNutritionChart(), 200);
            });
        }
    }));
});