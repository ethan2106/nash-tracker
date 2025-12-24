/**
 * Composant Alpine.js pour la gestion des repas
 * Gère les données des repas, le changement de date et les interactions utilisateur
 *
 * Optimisations appliquées :
 * - Alpine.js chargé dans le <head> avant les scripts de page
 * - Système de notifications global (window.showNotification)
 * - Parsing JSON sécurisé avec support des objets
 * - Changements de date avec debounce (600ms)
 * - Nettoyage DOM pour les soumissions de formulaires
 * - Événements personnalisés pour la communication inter-composants
 */
(function () {
  window.mealsManager = function() {
    return {
      // État initial
      meals: [],
      selectedDate: null,
      isToday: false,
      totals: {
        calories: 0,
        proteines: 0,
        glucides: 0,
        lipides: 0,
        sucres: 0,
        fibres: 0,
        graisses_sat: 0
      },
      objectifs: {
        calories_perte: 2000,
        proteines_max: 150,
        graisses_sat_max: 22,
        sucres_max: 50,
        fibres_min: 25
      },
      csrfToken: null,
      activities: [],
      loading: false,
      currentPage: 1,
      totalPages: 1,

      /**
       * Initialise le composant Alpine.js
       * Parse les données des attributs data-* et configure l'état initial
       */
      init() {
        try {
          const root = (() => {
            try { return this.$el || document.querySelector('[x-data="mealsManager()"]'); }
            catch (e) { return document.querySelector('[x-data="mealsManager()"]'); }
          })();

          if (!root) {
            console.warn('mealsManager: root element not found');
            return;
          }

          const safeParse = (str, fallback) => {
            if (str === undefined || str === null || str === '') return fallback;
            // si c'est déjà un objet (ex: dataset fourni autrement), retourne direct
            if (typeof str === 'object') return str;
            try {
              return JSON.parse(str);
            } catch (err) {
              try {
                const txt = document.createElement('textarea');
                txt.innerHTML = str;
                return JSON.parse(txt.value);
              } catch (err2) {
                console.error('mealsManager: failed to parse JSON', err, err2, str);
                return fallback;
              }
            }
          };

          // dataset brut
          const ds = root.dataset || {};

          this.meals = safeParse(ds.meals, []);
          this.selectedDate = ds.selectedDate || this.selectedDate || new Date().toISOString().slice(0,10);
          this.isToday = (function(v){
            if (v === undefined) return this.isToday;
            if (typeof v === 'boolean') return v;
            if (v === 'true') return true;
            if (v === 'false') return false;
            try {
              return JSON.parse(v) === true;
            } catch(e) { return v === '1' || v === '0' ? v === '1' : Boolean(v); }
          }).call(this, ds.isToday);

          this.totals = safeParse(ds.totals, this.totals);
          this.objectifs = safeParse(ds.objectifs, this.objectifs);
          this.objectifs = Object.assign({
            calories_perte: 2000,
            proteines_max: 150,
            graisses_sat_max: 22,
            sucres_max: 50,
            fibres_min: 25
          }, this.objectifs || {});

          this.csrfToken = ds.csrfToken || null;
          this.activities = safeParse(ds.activities || '[]', []);
          this.currentPage = parseInt(ds.currentPage || this.currentPage, 10) || 1;
          this.totalPages = parseInt(ds.total || this.totalPages, 10) || this.totalPages;

          this.hydrateDomTotals();

          // Écouter les demandes de rafraîchissement depuis d'autres composants
          document.addEventListener('meals:refresh-requested', (event) => {
            const date = event.detail?.date || this.selectedDate || new Date().toISOString().slice(0, 10);
            console.log('Événement meals:refresh-requested reçu, rafraîchissement pour la date:', date);
            this.loadMealsForDate(date);
          });
        } catch (err) {
          console.error('mealsManager.init error', err);
        }
      },

      /**
       * Met à jour le DOM avec les valeurs des totaux nutritionnels
       * Évite le flash de contenu non stylisé (FOUC)
       */
      hydrateDomTotals() {
        try {
          const setText = (id, v) => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.formatNumber(v, (id === 'total-calories' ? 0 : 1));
          };

          setText('total-calories', this.totals.calories ?? 0);
          setText('total-proteines', this.totals.proteines ?? 0);
          setText('total-glucides', this.totals.glucides ?? 0);
          setText('total-lipides', this.totals.lipides ?? 0);
          setText('total-sucres', this.totals.sucres ?? 0);
          setText('total-fibres', this.totals.fibres ?? 0);
          setText('total-graisses-sat', this.totals.graisses_sat ?? 0);
        } catch (err) {
          console.error('mealsManager.hydrateDomTotals error', err);
        }
      },

      /**
       * Formate un nombre selon les conventions françaises
       * @param {number} value - Valeur à formater
       * @param {number} decimals - Nombre de décimales (défaut: 1)
       * @returns {string} Nombre formaté
       */
      formatNumber(value, decimals = 1) {
        const n = Number(value || 0);
        if (decimals === 0) return Math.round(n).toLocaleString('fr-FR');
        return n.toLocaleString('fr-FR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
      },

      // Classes de couleur dynamiques pour les valeurs nutritionnelles

      /**
       * Retourne la classe CSS de couleur pour les calories
       * @returns {string} Classe Tailwind CSS
       */
      getCaloriesColor() {
        const kcal = Number(this.totals.calories || 0);
        const target = Number(this.objectifs.calories_perte || 2000);
        if (kcal <= target * 0.9) return 'text-yellow-600';
        if (kcal <= target * 1.1) return 'text-green-600';
        return 'text-red-600';
      },

      /**
       * Retourne la classe CSS de couleur pour les protéines
       * @returns {string} Classe Tailwind CSS
       */
      getProteinesColor() {
        const p = Number(this.totals.proteines || 0);
        const max = Number(this.objectifs.proteines_max || 150);
        if (p < max * 0.6) return 'text-yellow-600';
        if (p <= max) return 'text-green-600';
        return 'text-red-600';
      },

      /**
       * Retourne la classe CSS de couleur pour les lipides
       * @returns {string} Classe Tailwind CSS
       */
      getLipidesColor() {
        const l = Number(this.totals.lipides || 0);
        const max = Number(this.objectifs.graisses_sat_max || 22);
        return (l > max) ? 'text-red-600' : 'text-purple-600';
      },

      /**
       * Retourne la classe CSS de couleur pour les sucres
       * @returns {string} Classe Tailwind CSS
       */
      getSucresColor() {
        const s = Number(this.totals.sucres || 0);
        const max = Number(this.objectifs.sucres_max || 50);
        return (s > max) ? 'text-red-600' : 'text-gray-800';
      },

      /**
       * Retourne la classe CSS de couleur pour les fibres
       * @returns {string} Classe Tailwind CSS
       */
      getFibresColor() {
        const f = Number(this.totals.fibres || 0);
        const min = Number(this.objectifs.fibres_min || 25);
        return (f < min) ? 'text-yellow-600' : 'text-green-600';
      },

      /**
       * Retourne la classe CSS de couleur pour les graisses saturées
       * @returns {string} Classe Tailwind CSS
       */
      getGraissesSatColor() {
        const gs = Number(this.totals.graisses_sat || 0);
        const max = Number(this.objectifs.graisses_sat_max || 22);
        return (gs > max) ? 'text-red-600' : 'text-purple-600';
      },

      /**
       * Retourne la classe CSS de couleur pour les glucides
       * @returns {string} Classe Tailwind CSS
       */
      getGlucidesColor() {
        const g = Number(this.totals.glucides || 0);
        const max = Number(this.objectifs.glucides || 300);
        return (g > max) ? 'text-red-600' : 'text-green-600';
      },

      // Actions utilisateur

      /**
       * Change la date sélectionnée et recharge les repas
       * @param {string} newDate - Nouvelle date au format YYYY-MM-DD
       */
      changeDate(newDate) {
        if (!newDate) return;
        
        // simple throttle : ignore si dernière requête < 600ms
        if (this._lastDateChange && (Date.now() - this._lastDateChange) < 600) return;
        this._lastDateChange = Date.now();

        this.selectedDate = newDate;
        this.isToday = (newDate === new Date().toISOString().slice(0,10));
        this.loadMealsForDate(newDate);
      },

      /**
       * Navigue vers la date d'aujourd'hui
       */
      goToToday() {
        const today = new Date().toISOString().slice(0,10);
        if (this.selectedDate === today) return;
        this.changeDate(today);
      },

      /**
       * Charge les repas pour une date spécifique via AJAX
       * @param {string} dateStr - Date au format YYYY-MM-DD
       */
      async loadMealsForDate(dateStr) {
        this.loading = true;
        try {
          const res = await fetch(`?page=meals&date=${encodeURIComponent(dateStr)}`, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': this.csrfToken || ''
            },
            credentials: 'same-origin'
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          this.meals = json.mealsByType || [];
          this.totals = json.totals || this.totals;
          this.activities = json.activities || [];
          this.sections = json.sections || this.sections;
          
          // Mettre à jour le HTML des sections repas
          if (json.mealsHtml) {
            const mealsContainer = document.querySelector('.space-y-6');
            if (mealsContainer) {
              mealsContainer.innerHTML = json.mealsHtml;
            }
          }
          
          this.hydrateDomTotals();
          document.dispatchEvent(new CustomEvent('meals:loaded', { detail: { date: dateStr, totals: this.totals } }));
        } catch (err) {
          console.error('loadMealsForDate error', err);
          // notification visuelle simple (remplace par ton système de toast si tu en as un)
          window.showNotification('Impossible de charger les repas pour la date choisie', 'error');
        } finally {
          this.loading = false;
        }
      },

      /**
       * Méthode de debug pour exposer l'état interne
       * @returns {object} État actuel du composant
       */
      __debug() {
        return {
          meals: this.meals,
          totals: this.totals,
          objectifs: this.objectifs,
          selectedDate: this.selectedDate,
          isToday: this.isToday
        };
      },

      // Méthodes de compatibilité legacy

      /**
       * Redirige vers le catalogue pour ajouter un aliment
       * @param {string} mealType - Type de repas (petit-dejeuner, dejeuner, etc.)
       */
      addFood(mealType) {
        window.location.href = `?page=catalog&meal_type=${mealType}`;
      },

      /**
       * Demande confirmation avant suppression d'un repas complet
       * @param {number} mealId - ID du repas
       * @param {string} mealType - Type de repas
       */
      confirmDeleteMeal(mealId, mealType) {
        if (confirm('Supprimer ce repas et tous ses aliments ?')) {
          this.deleteMeal(mealId, mealType);
        }
      },

      /**
       * Supprime un repas complet via AJAX
       * @param {number} mealId - ID du repas
       * @param {string} mealType - Type de repas
       */
      async deleteMeal(mealId, mealType) {
        if (!mealId) return;
        try {
          this.loading = true;
          const res = await fetch('?page=meals&action=supprimer-repas', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': this.csrfToken || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({ repas_id: mealId })
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          if (json.success) {
            window.showNotification('Repas supprimé avec succès', 'success');
            if (json.mealsByType) this.meals = json.mealsByType;
            if (json.totals) { this.totals = json.totals; this.hydrateDomTotals(); }
            document.dispatchEvent(new CustomEvent('meals:loaded', { detail: { date: this.selectedDate, totals: this.totals }}));
          } else {
            showToast(json.message || 'Erreur lors de la suppression', 'error');
          }
        } catch (err) {
          console.error('deleteMeal error', err);
          window.showNotification('Impossible de supprimer le repas', 'error');
        } finally {
          this.loading = false;
        }
      },

      /**
       * Demande confirmation avant suppression d'un aliment
       * @param {number} foodId - ID de l'aliment
       * @param {number} mealId - ID du repas
       */
      confirmDeleteFood(foodId, mealId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet aliment du repas ?')) {
          this.deleteFood(foodId, mealId);
        }
      },

      /**
       * Supprime un aliment d'un repas via AJAX
       * @param {number} foodId - ID de l'aliment
       * @param {number} mealId - ID du repas
       */
      async deleteFood(foodId, mealId) {
        if (!foodId || !mealId) return;
        try {
          this.loading = true;
          const res = await fetch('?page=meals&action=supprimer-aliment', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': this.csrfToken || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({ aliment_id: foodId, repas_id: mealId })
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          if (json.success) {
            window.showNotification('Aliment supprimé avec succès', 'success');
            // Rafraîchir les données au lieu de recharger la page
            this.loadMealsForDate(this.selectedDate);
          } else {
            window.showNotification(json.message || 'Erreur lors de la suppression', 'error');
            this.loading = false;
          }
        } catch (err) {
          console.error('deleteFood error', err);
          window.showNotification('Impossible de supprimer l\'aliment', 'error');
          this.loading = false;
        }
      },

      // Méthodes helper pour la compatibilité des templates

      /**
       * Retourne les repas pour un type spécifique
       * @param {string} type - Type de repas (petit-dejeuner, dejeuner, etc.)
       * @returns {array} Liste des repas de ce type
       */
      getMealsForType(type) {
        return this.meals[type] || [];
      },

      /**
       * Vérifie si un type de repas contient des aliments
       * @param {string} type - Type de repas
       * @returns {boolean} True si le repas contient des aliments
       */
      hasMealsForType(type) {
        const meals = this.getMealsForType(type);
        return meals.some(meal => (meal.aliment_count || 0) > 0);
      }
    };
  };
})();