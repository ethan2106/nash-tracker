// Alpine.js Catalog Manager - Gestion du catalogue alimentaire
function catalogManager() {
    return {
        // État réactif
        searchQuery: '',
        showDetailsModal: false,
        selectedFood: {},
        foods: [],
        page: 1,
        perPage: 12,

        // Initialisation
        init() {
            console.log('CatalogManager initialized');
            // Initialiser avec les données PHP
            this.foods = window.catalogFoods || [];
            console.log('Foods loaded:', this.foods.length);

            // Initialiser les gestionnaires du modal de quantité
            this.initQuantityModal();
        },

        // Chargement des aliments (simulation - à remplacer par appel API réel)
        loadFoods() {
            // Cette fonction sera appelée depuis PHP avec les données
            // Pour l'instant, on utilise les données passées depuis PHP
        },

        // Recherche dans le catalogue
        get filteredFoods() {
            if (!this.searchQuery.trim()) {
                return this.foods;
            }

            const query = this.searchQuery.toLowerCase().trim();
            return this.foods.filter(food =>
                food.nom.toLowerCase().includes(query)
            );
        },

        // Aliments paginés (pour l'affichage)
        get paginatedFoods() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredFoods.slice(start, start + this.perPage);
        },

        // Nombre total de pages
        get totalPages() {
            return Math.ceil(this.filteredFoods.length / this.perPage) || 1;
        },

        // Fonction pour effacer la recherche
        clearSearch() {
            this.searchQuery = '';
            this.page = 1;
        },

        // Méthode pour définir la recherche (avec remise à zéro de la page)
        setSearchQuery(query) {
            this.searchQuery = query;
            this.page = 1;
        },

        // Navigation de page
        nextPage() {
            if (this.page < this.totalPages) {
                this.page++;
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
            }
        },

        // Aller à une page spécifique
        goToPage(pageNum) {
            if (pageNum >= 1 && pageNum <= this.totalPages) {
                this.page = pageNum;
            }
        },

        // Ouvrir le modal de détails
        openDetailsModal(food) {
            this.selectedFood = food;
            this.showDetailsModal = true;
        },

        // Fermer le modal
        closeDetailsModal() {
            this.showDetailsModal = false;
            this.selectedFood = {};
        },

        // Formater les valeurs nutritionnelles
        formatNutrient(value, unit = 'g') {
            if (!value || value === 0) return '-';
            return `${value}${unit}`;
        },

        // Obtenir la couleur pour un nutriment
        getNutrientColor(nutriment) {
            const colors = {
                calories: 'blue',
                proteines: 'green',
                glucides: 'yellow',
                lipides: 'red',
                fibres: 'purple',
                sucres: 'pink'
            };
            return colors[nutriment] || 'gray';
        },

        // Obtenir les classes CSS pour le badge de qualité
        getQualityClasses(grade) {
            const classes = {
                'A': 'bg-green-100 text-green-800',
                'B': 'bg-blue-100 text-blue-800',
                'C': 'bg-yellow-100 text-yellow-800',
                'D': 'bg-red-100 text-red-800'
            };
            return classes[grade] || 'bg-gray-100 text-gray-800';
        },

        // Vérifier si l'aliment vient d'une API
        isFromApi(food) {
            // Vérifier d'abord openfoodfacts_id qui est plus fiable
            if (food && food.openfoodfacts_id) return true;

            // Fallback vers autres_infos si pas d'openfoodfacts_id
            if (!food || !food.autres_infos) return false;
            const autresInfos = this.parseAutresInfos(food.autres_infos);
            const source = autresInfos.source || 'manuel';
            return ['api', 'openfoodfacts'].includes(source);
        },

        // Parser les autres infos JSON
        parseAutresInfos(autresInfos) {
            try {
                return JSON.parse(autresInfos || '{}');
            } catch {
                return {};
            }
        },

        // Initialisation du modal de quantité
        initQuantityModal() {
            // Attendre que le DOM soit chargé
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setupQuantityModal());
            } else {
                this.setupQuantityModal();
            }
        },

        // Configuration du modal de quantité
        setupQuantityModal() {
            // Variables pour stocker les données de l'aliment en cours
            let currentFoodData = null;

            // Gestion du modal de quantité
            const quantityModal = document.getElementById('quantity-modal');
            const closeQuantityModalBtn = document.getElementById('close-quantity-modal');
            const cancelAddToMealBtn = document.getElementById('cancel-add-to-meal');
            const confirmAddToMealBtn = document.getElementById('confirm-add-to-meal');
            const quantityInput = document.getElementById('quantity-input');
            const mealTypeSelect = document.getElementById('meal-type-select');

            // Vérifier que tous les éléments existent
            if (!quantityModal || !closeQuantityModalBtn || !cancelAddToMealBtn || !confirmAddToMealBtn || !quantityInput || !mealTypeSelect) {
                console.error('Certains éléments du modal de quantité sont manquants');
                return;
            }

            // Fonction pour ouvrir le modal de quantité
            window.openQuantityModal = function(foodData) {
                currentFoodData = foodData;

                // Remplir les informations de base
                document.getElementById('quantity-food-name').textContent = foodData.name;
                document.getElementById('quantity-base-calories').textContent = foodData.calories;
                document.getElementById('quantity-base-proteins').textContent = foodData.proteins;
                document.getElementById('quantity-base-carbs').textContent = foodData.carbs;
                document.getElementById('quantity-base-sugars').textContent = foodData.sugars;
                document.getElementById('quantity-base-fat').textContent = foodData.fat;
                document.getElementById('quantity-base-saturated-fat').textContent = foodData.saturatedFat;

                // Calculer et afficher les valeurs pour 100g
                updateCalculatedValues(100);

                // Afficher le modal
                quantityModal.classList.remove('hidden');
                quantityModal.classList.add('flex');

                // Focus sur l'input quantité
                setTimeout(() => quantityInput.focus(), 100);
            };

            // Fonction pour mettre à jour les valeurs calculées
            function updateCalculatedValues(quantity) {
                if (!currentFoodData) return;

                const ratio = quantity / 100;

                const calcCalories = Math.round(currentFoodData.calories * ratio);
                const calcProteins = (currentFoodData.proteins * ratio).toFixed(1);
                const calcCarbs = (currentFoodData.carbs * ratio).toFixed(1);
                const calcSugars = (currentFoodData.sugars * ratio).toFixed(1);
                const calcFat = (currentFoodData.fat * ratio).toFixed(1);
                const calcSaturatedFat = (currentFoodData.saturatedFat * ratio).toFixed(1);

                document.getElementById('quantity-display').textContent = quantity;
                document.getElementById('quantity-calc-calories').textContent = calcCalories + ' kcal';
                document.getElementById('quantity-calc-proteins').textContent = calcProteins + 'g';
                document.getElementById('quantity-calc-carbs').textContent = calcCarbs + 'g';
                document.getElementById('quantity-calc-sugars').textContent = calcSugars + 'g';
                document.getElementById('quantity-calc-fat').textContent = calcFat + 'g';
                document.getElementById('quantity-calc-saturated-fat').textContent = calcSaturatedFat + 'g';
            }

            // Gestion des changements de quantité
            quantityInput.addEventListener('input', function() {
                const quantity = parseInt(this.value) || 100;
                updateCalculatedValues(quantity);
            });

            // Gestion de la fermeture du modal
            closeQuantityModalBtn.addEventListener('click', function() {
                quantityModal.classList.add('hidden');
                quantityModal.classList.remove('flex');
                currentFoodData = null;
            });

            cancelAddToMealBtn.addEventListener('click', function() {
                quantityModal.classList.add('hidden');
                quantityModal.classList.remove('flex');
                currentFoodData = null;
            });

            // Gestion de la confirmation d'ajout
            confirmAddToMealBtn.addEventListener('click', function() {
                if (!currentFoodData) return;

                const quantity = parseInt(quantityInput.value) || 100;
                const mealType = mealTypeSelect.value;

                // Désactiver le bouton pendant le traitement
                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Ajout en cours...';

                // Préparer les données pour la requête AJAX
                const formData = new URLSearchParams({
                    'add_to_meal_from_catalog': '1',
                    'food_id': currentFoodData.id,
                    'food_name': currentFoodData.name,
                    'meal_type': mealType,
                    'quantity': quantity.toString(),
                    'csrf_token': window.csrfToken || ''
                });

                // Envoyer la requête AJAX
                fetch('?page=catalog', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Réponse du serveur:', data);
                    
                    // Fermer le modal
                    quantityModal.classList.add('hidden');
                    quantityModal.classList.remove('flex');
                    currentFoodData = null;
                    
                    // Vérifier le succès basé sur data.success
                    if (data.success === true) {
                        // Afficher une notification de succès
                        if (window.showNotification) {
                            window.showNotification('Aliment ajouté au repas avec succès !', 'success');
                        } else {
                            alert('Aliment ajouté au repas avec succès !');
                        }
                        
                        // Puisque nous sommes sur la page catalogue, rediriger vers les repas
                        // pour voir l'aliment ajouté
                        setTimeout(() => {
                            window.location.href = '?page=meals';
                        }, 1500); // Délai pour laisser voir la notification
                    } else {
                        if (window.showNotification) {
                            window.showNotification('Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
                        } else {
                            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de l\'ajout:', error);
                    alert('Erreur lors de l\'ajout de l\'aliment');
                })
                .finally(() => {
                    // Réactiver le bouton
                    this.disabled = false;
                    this.innerHTML = 'Ajouter au repas';
                });
            });

            // Gestion des boutons d'ajout au repas depuis le catalogue
            document.addEventListener('click', function(e) {
                if (e.target.closest('.add-to-meal-from-catalog-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.add-to-meal-from-catalog-btn');

                    const foodData = {
                        id: button.getAttribute('data-food-id'),
                        name: button.getAttribute('data-food-name'),
                        calories: parseFloat(button.getAttribute('data-food-calories')) || 0,
                        proteins: parseFloat(button.getAttribute('data-food-proteins')) || 0,
                        carbs: parseFloat(button.getAttribute('data-food-carbs')) || 0,
                        sugars: parseFloat(button.getAttribute('data-food-sugars')) || 0,
                        fat: parseFloat(button.getAttribute('data-food-fat')) || 0,
                        saturatedFat: parseFloat(button.getAttribute('data-food-saturated-fat')) || 0
                    };

                    window.openQuantityModal(foodData);
                }
            });
        }
    }
}

// Exposer la fonction globalement pour Alpine.js
window.catalogManager = catalogManager;