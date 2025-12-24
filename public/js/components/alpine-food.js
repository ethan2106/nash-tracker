document.addEventListener('alpine:init', () => {
    Alpine.data('foodSearchManager', () => ({
        // Lire le paramètre tab de l'URL pour définir l'onglet actif par défaut
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'text',
        searchQuery: '',
        barcodeQuery: '',
        manualForm: {
            name: '',
            brand: '',
            calories: '',
            proteins: '',
            carbs: '',
            fat: '',
            fiber: '',
            quantity: 100
        },
        showQuantityModal: false,
        showDetailsModal: false,
        selectedFood: null,
        quantity: 1,
        mealType: window.currentMealType || 'repas',

        init() {
            // Initialisation vide
        },

        switchTab(tab) {
            this.activeTab = tab;
        },

        // Helper: Récupère la valeur d'un nutriment
        getNutrientValue(food, key) {
            if (!food) return 0;
            
            // Mapping des clés API vers les clés simplifiées
            const keyMap = {
                'energy-kcal': ['calories', 'energy-kcal_100g', 'energy-kcal'],
                'proteins': ['proteins', 'proteins_100g'],
                'carbohydrates': ['carbs', 'carbohydrates_100g', 'carbohydrates'],
                'sugars': ['sugars', 'sugars_100g'],
                'fat': ['fat', 'fat_100g'],
                'saturated-fat': ['saturatedFat', 'saturated-fat_100g', 'saturated-fat'],
                'fiber': ['fiber', 'fiber_100g'],
                'salt': ['salt', 'salt_100g']
            };
            
            // Chercher d'abord dans les propriétés directes de food
            const possibleKeys = keyMap[key] || [key + '_100g', key];
            for (const k of possibleKeys) {
                if (food[k] !== undefined && food[k] !== null) {
                    return parseFloat(food[k]) || 0;
                }
            }
            
            // Chercher ensuite dans food.nutriments
            if (food.nutriments) {
                for (const k of possibleKeys) {
                    if (food.nutriments[k] !== undefined && food.nutriments[k] !== null) {
                        return parseFloat(food.nutriments[k]) || 0;
                    }
                }
                // Essayer aussi les clés avec _100g dans nutriments
                const value = food.nutriments[key + '_100g'] ?? food.nutriments[key];
                if (value !== undefined && value !== null) {
                    return parseFloat(value) || 0;
                }
            }
            
            return 0;
        },

        // Helper: Formate un nutriment pour affichage
        formatNutrient(food, key, unit) {
            const value = this.getNutrientValue(food, key);
            if (value === 0) return '—';
            return value.toFixed(1).replace('.', ',') + ' ' + unit;
        },

        openQuantityModal(foodData) {
            this.selectedFood = foodData;
            this.quantity = 1;
            this.showQuantityModal = true;
        },

        closeQuantityModal() {
            this.showQuantityModal = false;
            this.selectedFood = null;
            this.quantity = 1;
        },

        openDetailsModal(foodData) {
            this.selectedFood = foodData;
            this.showDetailsModal = true;
        },

        closeDetailsModal() {
            this.showDetailsModal = false;
            this.selectedFood = null;
        },

        addToMeal() {
            if (!this.selectedFood || !this.quantity) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=food' + (this.mealType !== 'repas' ? '&meal_type=' + encodeURIComponent(this.mealType) : '');

            // Add meal type
            const mealTypeInput = document.createElement('input');
            mealTypeInput.type = 'hidden';
            mealTypeInput.name = 'meal_type';
            mealTypeInput.value = this.mealType;
            form.appendChild(mealTypeInput);

            // Add quantity
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = this.quantity;
            form.appendChild(quantityInput);

            // Add food data
            const foodDataInput = document.createElement('input');
            foodDataInput.type = 'hidden';
            foodDataInput.name = 'food_data';
            foodDataInput.value = JSON.stringify(this.selectedFood);
            form.appendChild(foodDataInput);

            // Add action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'add_food';
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        },

        saveFood(foodData) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=food' + (this.mealType !== 'repas' ? '&meal_type=' + encodeURIComponent(this.mealType) : '');

            const foodDataInput = document.createElement('input');
            foodDataInput.type = 'hidden';
            foodDataInput.name = 'food_data';
            foodDataInput.value = JSON.stringify(foodData);
            form.appendChild(foodDataInput);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'save_to_db';
            actionInput.value = '1';
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        },

        useSuggestion(suggestion) {
            this.searchQuery = suggestion;
            // Auto-submit the search form
            const searchForm = document.querySelector('form[action*="search_type=text"]');
            if (searchForm) {
                // Set the input value and submit
                const input = searchForm.querySelector('input[name="search_query"]');
                if (input) {
                    input.value = suggestion;
                    searchForm.submit();
                }
            }
        },

        submitManualForm() {
            const form = document.querySelector('form[action*="add_manual"]');
            if (form) {
                form.submit();
            }
        },

        resetManualForm() {
            this.manualForm = {
                name: '',
                brand: '',
                calories: '',
                proteins: '',
                carbs: '',
                fat: '',
                fiber: '',
                quantity: 100
            };
        }
    }));
});