/**
 * WalkTrack - Core Module
 * État global, configuration et utilitaires
 */

window.WalkTrack = window.WalkTrack || {};

(function(WT) {
    'use strict';

    // ================================================================
    // ÉTAT GLOBAL
    // ================================================================
    WT.state = {
        map: null,
        markers: [],
        polyline: null,
        routePoints: [],      // Points cliqués par l'utilisateur
        routeGeometry: [],    // Géométrie complète du trajet (via OSRM)
        totalDistance: 0,
        isRoutingInProgress: false
    };

    // ================================================================
    // CONFIGURATION
    // ================================================================
    WT.config = {
        // Valeurs MET (Metabolic Equivalent of Task)
        MET_VALUES: {
            marche: 3.5,        // Marche normale 4-5 km/h
            marche_rapide: 5.0  // Marche rapide 6-7 km/h
        },
        // Vitesses moyennes (km/h)
        SPEEDS: {
            marche: 4.5,
            marche_rapide: 6.0
        },
        // Position par défaut (Lescheroux - route du Villard 299, 01560)
        DEFAULT_POSITION: [46.3936, 5.1547]
    };

    // Mode simulation
    WT.isSimulationMode = false;

    // ================================================================
    // UTILITAIRES
    // ================================================================
    
    /**
     * Récupérer le poids de l'utilisateur
     */
    WT.getUserWeight = function() {
        return window.walktrackData?.userWeight || 70;
    };

    /**
     * Échapper le HTML
     */
    WT.escapeHtml = function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    /**
     * Afficher une notification toast
     */
    WT.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg z-50 text-sm font-medium transition-all transform translate-y-0 opacity-100 ${
            type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'
        }`;
        toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    /**
     * Afficher une notification en haut
     */
    WT.showNotification = function(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        const notif = document.createElement('div');
        notif.className = `fixed top-4 right-4 z-[var(--z-modal)] px-4 py-3 rounded-lg text-white shadow-lg ${colors[type] || colors.info} animate-fade-in`;
        notif.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${WT.escapeHtml(message)}</span>
            </div>
        `;
        
        document.body.appendChild(notif);
        
        setTimeout(() => {
            notif.style.opacity = '0';
            notif.style.transition = 'opacity 0.3s';
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    };

    /**
     * Calculer les calories
     */
    WT.calculateCalories = function(type, durationMinutes) {
        const met = WT.config.MET_VALUES[type] || WT.config.MET_VALUES.marche;
        const poids = WT.getUserWeight();
        const durationHours = durationMinutes / 60;
        return Math.round(met * poids * durationHours);
    };

    /**
     * Calculer la durée depuis des heures
     */
    WT.calculateDurationFromTimes = function(startTime, endTime) {
        if (!startTime || !endTime) return null;
        
        const [startH, startM] = startTime.split(':').map(Number);
        const [endH, endM] = endTime.split(':').map(Number);
        
        let startMinutes = startH * 60 + startM;
        let endMinutes = endH * 60 + endM;
        
        // Passage minuit
        if (endMinutes < startMinutes) {
            endMinutes += 24 * 60;
        }
        
        const durationMinutes = endMinutes - startMinutes;
        
        if (durationMinutes > 0 && durationMinutes < 600) {
            return durationMinutes;
        }
        return null;
    };

    /**
     * Formule Haversine pour distance entre 2 points
     */
    WT.haversineDistance = function(lat1, lon1, lat2, lon2) {
        const R = 6371; // Rayon de la Terre en km
        const dLat = WT.toRad(lat2 - lat1);
        const dLon = WT.toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(WT.toRad(lat1)) * Math.cos(WT.toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    };

    WT.toRad = function(deg) {
        return deg * (Math.PI / 180);
    };

    /**
     * Générer l'URL Google Maps pour le parcours actuel
     */
    WT.getGoogleMapsUrl = function() {
        const points = WT.state.routePoints;
        if (!points || points.length < 2) {
            return null;
        }
        
        // Format moderne Google Maps avec mode marche
        // https://www.google.com/maps/dir/?api=1&origin=lat,lng&destination=lat,lng&waypoints=lat,lng|lat,lng&travelmode=walking
        const origin = `${points[0].lat},${points[0].lng}`;
        const destination = `${points[points.length - 1].lat},${points[points.length - 1].lng}`;
        
        let url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}&travelmode=walking`;
        
        // Ajouter les waypoints intermédiaires s'il y en a
        if (points.length > 2) {
            const waypoints = points.slice(1, -1).map(p => `${p.lat},${p.lng}`).join('|');
            url += `&waypoints=${waypoints}`;
        }
        
        return url;
    };

    /**
     * Ouvrir le parcours dans Google Maps
     */
    WT.openInGoogleMaps = function() {
        const url = WT.getGoogleMapsUrl();
        if (url) {
            window.open(url, '_blank');
        } else {
            WT.showToast('Ajoutez au moins 2 points sur la carte', 'error');
        }
    };

})(window.WalkTrack);
