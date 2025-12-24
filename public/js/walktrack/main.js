/**
 * WalkTrack - Main Entry Point
 * Charge tous les modules et initialise l'application
 */

(function() {
    'use strict';

    function init() {
        const WT = window.WalkTrack;
        
        if (!WT) {
            console.error('WalkTrack core not loaded');
            return;
        }

        // Initialiser tous les modules
        WT.initMap();
        WT.initAddressSearch();
        WT.initFormHandlers();
        WT.initSimulationMode();
        WT.initObjectivesHandlers();
        WT.initDeleteHandlers();
        WT.initEditHandlers();
        WT.initParcoursModal();
        WT.updateEstimatedCalories();
        
        console.log('WalkTrack initialized');
    }

    // Démarrage
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
