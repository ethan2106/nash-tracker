/**
 * WalkTrack - Map Module
 * Carte Leaflet et routing OSRM
 */

(function(WT) {
    'use strict';

    const state = WT.state;
    const config = WT.config;

    // ================================================================
    // INITIALISATION CARTE
    // ================================================================
    WT.initMap = function() {
        state.map = L.map('walktrack-map').setView(config.DEFAULT_POSITION, 13);
        
        // Tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(state.map);

        // Géolocalisation (avec fallback IP)
        tryGeolocation();

        // Clic sur la carte = ajouter un point
        state.map.on('click', handleMapClick);

        // Boutons
        document.getElementById('btn-undo-point')?.addEventListener('click', WT.undoLastPoint);
        document.getElementById('btn-reset-map')?.addEventListener('click', WT.resetMap);
        document.getElementById('btn-open-gmaps')?.addEventListener('click', WT.openInGoogleMaps);
    };

    /**
     * Essaie la géolocalisation navigateur, sinon fallback par IP
     */
    async function tryGeolocation() {
        // Vérifier si on est en HTTPS ou localhost (géolocalisation autorisée)
        const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

        if (navigator.geolocation && isSecure) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    state.map.setView([pos.coords.latitude, pos.coords.longitude], 15);
                    console.log('Géolocalisation navigateur OK');
                },
                async (err) => {
                    console.log('Géolocalisation navigateur échouée:', err.message);
                    if (err.code === err.PERMISSION_DENIED) {
                        console.log('Permission refusée, utilisation de la position par défaut');
                        // Rester sur la position par défaut
                    } else {
                        await fallbackGeoIP();
                    }
                },
                { timeout: 5000 }
            );
        } else {
            console.log('Géolocalisation non disponible (HTTPS requis ou non supporté)');
            await fallbackGeoIP();
        }
    }

    /**
     * Fallback: géolocalisation par IP (moins précis mais fonctionne toujours)
     */
    async function fallbackGeoIP() {
        try {
            // API gratuite qui donne la position approximative par IP
            const response = await fetch('https://ipapi.co/json/');
            if (!response.ok) throw new Error('Erreur API');
            
            const data = await response.json();
            
            if (data.latitude && data.longitude) {
                state.map.setView([data.latitude, data.longitude], 12);
                console.log(`Position par IP: ${data.city}, ${data.country_name}`);
            }
        } catch (err) {
            console.log('Fallback IP échoué, position par défaut (Paris)');
            // On reste sur la position par défaut (Paris)
        }
    }

    // ================================================================
    // GESTION DES POINTS
    // ================================================================
    function handleMapClick(e) {
        const { lat, lng } = e.latlng;
        
        // Ajouter le point
        state.routePoints.push({ lat, lng });
        
        // Créer le marqueur
        const markerIcon = state.routePoints.length === 1 ? 
            createIcon('green', 'A') : 
            createIcon('blue', state.routePoints.length.toString());
        
        const marker = L.marker([lat, lng], { icon: markerIcon }).addTo(state.map);
        state.markers.push(marker);
        
        // Mettre à jour la polyline avec OSRM
        updateRouteWithOSRM();
        
        // Activer les boutons
        updateMapButtons();
    }

    /**
     * Met à jour l'état des boutons de la carte selon les points
     */
    function updateMapButtons() {
        const hasPoints = state.routePoints.length > 0;
        const hasRoute = state.routePoints.length >= 2;
        
        const btnUndo = document.getElementById('btn-undo-point');
        const btnGmaps = document.getElementById('btn-open-gmaps');
        
        if (btnUndo) btnUndo.disabled = !hasPoints;
        if (btnGmaps) btnGmaps.disabled = !hasRoute;
    }

    function createIcon(color, label) {
        const colors = {
            green: '#22c55e',
            blue: '#3b82f6',
            red: '#ef4444'
        };
        
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: ${colors[color] || colors.blue};
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 12px;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            ">${label}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
    }

    // ================================================================
    // ROUTING OSRM
    // ================================================================
    async function updateRouteWithOSRM() {
        if (state.routePoints.length < 2) {
            if (state.polyline) {
                state.map.removeLayer(state.polyline);
                state.polyline = null;
            }
            state.routeGeometry = [];
            updateDistanceDisplay(0);
            return;
        }

        if (state.isRoutingInProgress) return;
        state.isRoutingInProgress = true;

        const distanceElem = document.getElementById('map-distance');
        if (distanceElem) distanceElem.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const coords = state.routePoints.map(p => `${p.lng},${p.lat}`).join(';');
            const url = `https://router.project-osrm.org/route/v1/foot/${coords}?overview=full&geometries=geojson`;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur OSRM');

            const data = await response.json();

            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                throw new Error('Pas de route trouvée');
            }

            const route = data.routes[0];
            const distanceKm = route.distance / 1000;
            state.routeGeometry = route.geometry.coordinates.map(c => [c[1], c[0]]);

            if (state.polyline) {
                state.map.removeLayer(state.polyline);
            }

            state.polyline = L.polyline(state.routeGeometry, {
                color: '#3b82f6',
                weight: 5,
                opacity: 0.8
            }).addTo(state.map);

            updateDistanceDisplay(distanceKm);

        } catch (error) {
            console.error('Erreur routing OSRM:', error);
            fallbackToStraightLine();
        } finally {
            state.isRoutingInProgress = false;
        }
    }

    function fallbackToStraightLine() {
        if (state.polyline) {
            state.map.removeLayer(state.polyline);
        }

        if (state.routePoints.length >= 2) {
            const latlngs = state.routePoints.map(p => [p.lat, p.lng]);
            state.polyline = L.polyline(latlngs, {
                color: '#ef4444',
                weight: 4,
                opacity: 0.8,
                dashArray: '10, 10'
            }).addTo(state.map);
        }

        let total = 0;
        for (let i = 1; i < state.routePoints.length; i++) {
            const p1 = state.routePoints[i - 1];
            const p2 = state.routePoints[i];
            total += WT.haversineDistance(p1.lat, p1.lng, p2.lat, p2.lng);
        }
        updateDistanceDisplay(total);
        
        WT.showToast('Route non trouvée, distance approximative', 'error');
    }

    function updateDistanceDisplay(distanceKm) {
        state.totalDistance = distanceKm;

        const pointsElem = document.getElementById('map-points-count');
        const distanceElem = document.getElementById('map-distance');
        const inputDistance = document.getElementById('input-distance');
        const inputRoutePoints = document.getElementById('input-route-points');
        const btnSave = document.getElementById('btn-save-parcours');

        if (pointsElem) pointsElem.textContent = state.routePoints.length;
        if (distanceElem) distanceElem.textContent = distanceKm.toFixed(2);
        if (inputDistance) inputDistance.value = distanceKm.toFixed(2);
        if (inputRoutePoints) inputRoutePoints.value = JSON.stringify(state.routePoints);
        if (btnSave) btnSave.disabled = state.routePoints.length < 2;

        // Mettre à jour les calories estimées
        if (typeof WT.updateEstimatedCalories === 'function') {
            WT.updateEstimatedCalories();
        }
    }

    // ================================================================
    // ACTIONS CARTE
    // ================================================================
    WT.undoLastPoint = function() {
        if (state.routePoints.length === 0) return;
        
        state.routePoints.pop();
        
        const lastMarker = state.markers.pop();
        if (lastMarker) {
            state.map.removeLayer(lastMarker);
        }
        
        updateRouteWithOSRM();
        
        updateMapButtons();
    };

    WT.resetMap = function() {
        state.markers.forEach(m => state.map.removeLayer(m));
        state.markers = [];
        
        if (state.polyline) {
            state.map.removeLayer(state.polyline);
            state.polyline = null;
        }
        
        state.routePoints = [];
        state.totalDistance = 0;
        
        updateDistanceDisplay(0);
        
        updateMapButtons();
    };

    WT.loadRoute = function(routePoints) {
        WT.resetMap();
        
        routePoints.forEach((point, index) => {
            state.routePoints.push(point);
            
            const markerIcon = index === 0 ? 
                createIcon('green', 'A') : 
                createIcon('blue', (index + 1).toString());
            
            const marker = L.marker([point.lat, point.lng], { icon: markerIcon }).addTo(state.map);
            state.markers.push(marker);
        });
        
        updateRouteWithOSRM().then(() => {
            if (state.polyline) {
                state.map.fitBounds(state.polyline.getBounds(), { padding: [50, 50] });
            }
        });
        
        updateMapButtons();
    };

    /**
     * Ajouter un point depuis coordonnées (utilisé par recherche adresse)
     */
    WT.addPointToRoute = function(lat, lng, label = null) {
        state.routePoints.push({ lat, lng });
        
        const isFirst = state.routePoints.length === 1;
        const markerIcon = isFirst ? 
            createIcon('green', 'A') : 
            createIcon('blue', state.routePoints.length.toString());
        
        const marker = L.marker([lat, lng], { icon: markerIcon });
        
        if (label) {
            marker.bindPopup(`<b>${isFirst ? 'Départ' : 'Étape ' + state.routePoints.length}</b><br>${label}`);
        }
        
        marker.addTo(state.map);
        state.markers.push(marker);
        
        updateRouteWithOSRM();
        
        updateMapButtons();
    };

})(window.WalkTrack);
