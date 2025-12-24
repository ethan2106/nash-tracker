<?php

/**
 * Composant: Scripts JavaScript pour settings.
 *
 * @description Lazy loading graphique + fonctions export PDF/CSV
 *
 * @var array $allHistoriqueMesures Toutes les mesures pour le graphique Chart.js
 */

declare(strict_types=1);
?>
<!-- ============================================================
     SCRIPTS JAVASCRIPT SETTINGS
     - chartLoader() : Lazy loading du graphique Chart.js
     - exportData(), quickExport(), downloadExport(), previewReport()
     ============================================================ -->
<script>
// ===== LAZY LOADING GRAPHIQUE =====
function chartLoader() {
    return {
        chartLoaded: false,
        initLazyLoading() {
            // Utiliser Intersection Observer pour détecter quand le graphique entre dans la vue
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.chartLoaded) {
                        this.loadChartData();
                    }
                });
            }, {
                threshold: 0.1 // Charger quand 10% du graphique est visible
            });

            // Observer la section principale du graphique (toujours visible)
            observer.observe(this.$el);

            // Fallback: charger automatiquement après 3 secondes si pas encore chargé
            setTimeout(() => {
                if (!this.chartLoaded) {
                    this.loadChartData();
                }
            }, 3000);
        },
        async loadChartData() {
            try {
                // Simuler un délai de chargement pour l'effet visuel
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Les données sont déjà chargées côté serveur, on active juste l'affichage
                this.chartLoaded = true;
                
                // Initialiser le graphique après un court délai pour s'assurer que le canvas est visible
                setTimeout(() => {
                    this.initChart();
                }, 100);
                
            } catch (error) {
                console.error('Erreur lors du chargement du graphique:', error);
                // En cas d'erreur, afficher quand même le graphique avec les données disponibles
                this.chartLoaded = true;
                setTimeout(() => this.initChart(), 100);
            }
        },
        initChart() {
            const allHistoriqueMesures = <?= json_encode($allHistoriqueMesures, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            if (allHistoriqueMesures && allHistoriqueMesures.length > 0) {
                const ctx = document.getElementById('historiqueChart');
                if (ctx && window.Chart) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: allHistoriqueMesures.map(m => new Date(m.date_mesure).toLocaleDateString('fr-FR')),
                            datasets: [{
                                label: 'Poids (kg)',
                                data: allHistoriqueMesures.map(m => parseFloat(m.poids)),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y'
                            }, {
                                label: 'IMC',
                                data: allHistoriqueMesures.map(m => parseFloat(m.imc)),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Évolution Poids et IMC'
                                },
                                legend: {
                                    display: true
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Poids (kg)'
                                    },
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'IMC'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                },
                            }
                        }
                    });
                }
            }
        }
    }
}

// ===== FONCTIONS D'EXPORT =====
function exportData() {
    console.log('Export function called');

    // Vérifier si l'utilisateur est sur l'onglet export
    if (typeof Alpine !== 'undefined' && Alpine.store('settingsManager')) {
        const activeTab = Alpine.store('settingsManager').activeTab;
        if (activeTab !== 'export') {
            alert('Veuillez d\'abord sélectionner l\'onglet "Export Données".');
            // Changer automatiquement vers l'onglet export
            Alpine.store('settingsManager').changeTab('export');
            return;
        }
    }

    const formatSelect = document.getElementById('export-format');
    const periodSelect = document.getElementById('export-period');

    if (!formatSelect || !periodSelect) {
        console.error('Export elements not found');
        alert('Erreur: Éléments d\'export introuvables. Actualisez la page et réessayez.');
        return;
    }

    const format = formatSelect.value;
    const period = periodSelect.value;

    downloadExport(format, period);
}

function quickExport() {
    // Export rapide : PDF des 7 derniers jours
    downloadExport('pdf', '7days');
}

async function downloadExport(format, period) {
    const btn = document.getElementById('export-btn-text');

    console.log('Format:', format, 'Period:', period);

    // Changer le texte du bouton
    const originalText = btn ? btn.textContent : 'Exporter';
    if (btn) {
        btn.textContent = 'Export en cours...';
        btn.closest('button').disabled = true;
    }

    try {
        // Utiliser fetch pour télécharger le fichier
        const response = await fetch(`?page=export&type=${format}&period=${period}`);

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        // Récupérer le contenu comme blob
        const blob = await response.blob();

        // Créer un URL pour le blob
        const url = window.URL.createObjectURL(blob);

        // Créer un lien temporaire et déclencher le téléchargement
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;

        // Déterminer le nom du fichier depuis les headers ou générer un nom
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = `rapport_sante_${new Date().toISOString().split('T')[0]}.${format}`;

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename="(.+)"/);
            if (filenameMatch) {
                filename = filenameMatch[1];
            }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Nettoyer
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        console.log('Export réussi:', filename);

    } catch (error) {
        console.error('Erreur lors de l\'export:', error);
        alert('Une erreur est survenue lors de l\'export. Veuillez réessayer.');
    } finally {
        // Restaurer le bouton
        if (btn) {
            setTimeout(() => {
                btn.textContent = originalText;
                btn.closest('button').disabled = false;
            }, 2000);
        }
    }
}

function previewReport() {
    // Ouvrir un aperçu du PDF dans un nouvel onglet
    const period = document.getElementById('export-period').value;
    window.open(`?page=export&type=pdf&period=${period}`, '_blank');
}
</script>
