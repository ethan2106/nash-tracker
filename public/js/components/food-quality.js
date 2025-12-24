/**
 * Composant JavaScript pour les notations alimentaires
 * Gestion des tooltips, animations et interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips pour les badges de notation
    initQualityTooltips();

    // Animation des barres de progression
    animateProgressBars();
});

/**
 * Initialise les tooltips pour les badges de qualité
 */
function initQualityTooltips() {
    const badges = document.querySelectorAll('[data-tooltip-position]');

    badges.forEach(badge => {
        // Création du tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'quality-tooltip';
        tooltip.textContent = badge.getAttribute('title') || badge.dataset.tooltip;
        document.body.appendChild(tooltip);

        // Positionnement et affichage
        badge.addEventListener('mouseenter', function(e) {
            const rect = badge.getBoundingClientRect();
            const position = badge.dataset.tooltipPosition || 'top';

            tooltip.style.display = 'block';

            // Calcul de la position
            switch(position) {
                case 'top':
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
                    break;
                case 'bottom':
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.bottom + 8 + 'px';
                    break;
            }

            // Animation d'entrée
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(10px)';
            requestAnimationFrame(() => {
                tooltip.style.transition = 'all 0.2s ease-out';
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            });
        });

        badge.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(10px)';
            setTimeout(() => {
                tooltip.style.display = 'none';
            }, 200);
        });
    });
}

/**
 * Anime les barres de progression au scroll
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.food-quality-details .bg-gradient-to-r');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target;
                const targetWidth = bar.style.width;

                // Animation depuis 0
                bar.style.width = '0%';
                requestAnimationFrame(() => {
                    bar.style.transition = 'width 1.5s ease-out';
                    bar.style.width = targetWidth;
                });

                observer.unobserve(bar);
            }
        });
    }, { threshold: 0.1 });

    progressBars.forEach(bar => observer.observe(bar));
}

/**
 * Fonction utilitaire pour mettre à jour une notation dynamiquement
 */
window.updateFoodQuality = function(foodId, gradeData) {
    const badge = document.querySelector(`[data-food-id="${foodId}"] .quality-badge`);
    if (!badge) return;

    // Mise à jour du contenu
    badge.innerHTML = `
        <span class="font-bold">${gradeData.grade}</span>
        <span class="text-xs opacity-60">(${gradeData.percentage}%)</span>
    `;

    // Mise à jour des classes CSS
    badge.className = `inline-flex items-center gap-1.5 ${gradeData.bg_color} ${gradeData.text_color} rounded-full px-3 py-1.5 text-sm font-semibold shadow-sm border border-current/20`;

    // Mise à jour du tooltip
    badge.setAttribute('title', `${gradeData.description} (${gradeData.percentage}%)`);
};

/**
 * Fonction pour charger les notations en AJAX
 */
window.loadFoodQualities = function(foodIds) {
    if (!foodIds || foodIds.length === 0) return;

    fetch('/api/food-qualities', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ food_ids: foodIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.grades) {
            Object.entries(data.grades).forEach(([foodId, grade]) => {
                updateFoodQuality(parseInt(foodId), grade);
            });
        }
    })
    .catch(error => {
        console.error('Erreur chargement notations:', error);
    });
};