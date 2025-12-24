/**
 * WalkTrack - Address Search Module
 * Recherche d'adresse via Nominatim
 */

(function(WT) {
    'use strict';

    let searchTimeout = null;
    let selectedAddress = null;

    // ================================================================
    // INITIALISATION
    // ================================================================
    WT.initAddressSearch = function() {
        const searchInput = document.getElementById('address-search');
        const btnAdd = document.getElementById('btn-add-address');
        const btnClear = document.getElementById('btn-clear-address');
        
        if (!searchInput) return;

        // Recherche avec debounce
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            hideSelectedAddress();
            
            if (query.length < 3) {
                hideAddressResults();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                searchAddress(query);
            }, 400);
        });

        // Fermer les résultats si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#address-search') && !e.target.closest('#address-results')) {
                hideAddressResults();
            }
        });

        // Bouton ajouter
        if (btnAdd) {
            btnAdd.addEventListener('click', addSelectedAddressToRoute);
        }

        // Bouton effacer sélection
        if (btnClear) {
            btnClear.addEventListener('click', hideSelectedAddress);
        }

        // Touche Entrée
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedAddress) {
                    addSelectedAddressToRoute();
                }
            }
        });
    };

    // ================================================================
    // RECHERCHE
    // ================================================================
    async function searchAddress(query) {
        const loading = document.getElementById('address-search-loading');
        const resultsDiv = document.getElementById('address-results');
        
        if (loading) loading.classList.remove('hidden');
        
        try {
            const url = `https://nominatim.openstreetmap.org/search?` + new URLSearchParams({
                q: query,
                format: 'json',
                addressdetails: 1,
                limit: 6,
                countrycodes: 'fr,be,ch,lu,ca'
            });
            
            const response = await fetch(url, {
                headers: { 'Accept-Language': 'fr' }
            });
            
            if (!response.ok) throw new Error('Erreur réseau');
            
            const results = await response.json();
            displayAddressResults(results);
            
        } catch (error) {
            console.error('Erreur recherche adresse:', error);
            resultsDiv.innerHTML = `
                <div class="px-4 py-3 text-sm text-red-600">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    Erreur de recherche. Réessayez.
                </div>
            `;
            resultsDiv.classList.remove('hidden');
        } finally {
            if (loading) loading.classList.add('hidden');
        }
    }

    function displayAddressResults(results) {
        const resultsDiv = document.getElementById('address-results');
        
        if (!results || results.length === 0) {
            resultsDiv.innerHTML = `
                <div class="px-4 py-3 text-sm text-slate-500">
                    <i class="fa-solid fa-search mr-2"></i>
                    Aucune adresse trouvée
                </div>
            `;
            resultsDiv.classList.remove('hidden');
            return;
        }
        
        resultsDiv.innerHTML = results.map((result) => {
            const icon = getAddressIcon(result.type, result.class);
            return `
                <div class="address-result px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors"
                     data-lat="${result.lat}"
                     data-lng="${result.lon}"
                     data-display="${WT.escapeHtml(result.display_name)}">
                    <div class="flex items-start gap-3">
                        <i class="${icon} text-blue-500 mt-0.5"></i>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-700 truncate">
                                ${formatAddressMain(result)}
                            </div>
                            <div class="text-xs text-slate-500 truncate">
                                ${formatAddressSecondary(result)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        resultsDiv.classList.remove('hidden');
        
        resultsDiv.querySelectorAll('.address-result').forEach(item => {
            item.addEventListener('click', () => selectAddress(item));
        });
    }

    function getAddressIcon(type, category) {
        if (type === 'house' || type === 'residential') return 'fa-solid fa-house';
        if (type === 'street' || category === 'highway') return 'fa-solid fa-road';
        if (category === 'building') return 'fa-solid fa-building';
        if (type === 'city' || type === 'town' || type === 'village') return 'fa-solid fa-city';
        return 'fa-solid fa-map-marker-alt';
    }

    function formatAddressMain(result) {
        const addr = result.address || {};
        
        if (addr.road) {
            const num = addr.house_number ? addr.house_number + ' ' : '';
            return num + addr.road;
        }
        if (addr.hamlet) return addr.hamlet;
        if (addr.village) return addr.village;
        if (addr.town) return addr.town;
        if (addr.city) return addr.city;
        
        return result.display_name.split(',')[0];
    }

    function formatAddressSecondary(result) {
        const addr = result.address || {};
        const parts = [];
        
        if (addr.postcode) parts.push(addr.postcode);
        if (addr.city || addr.town || addr.village) {
            parts.push(addr.city || addr.town || addr.village);
        }
        if (addr.county) parts.push(addr.county);
        
        return parts.join(', ') || result.display_name;
    }

    // ================================================================
    // SÉLECTION
    // ================================================================
    function selectAddress(item) {
        selectedAddress = {
            lat: parseFloat(item.dataset.lat),
            lng: parseFloat(item.dataset.lng),
            display: item.dataset.display
        };
        
        document.getElementById('selected-address-text').textContent = selectedAddress.display;
        document.getElementById('selected-address').classList.remove('hidden');
        document.getElementById('btn-add-address').disabled = false;
        
        hideAddressResults();
        document.getElementById('address-search').value = '';
        
        // Centrer la carte
        WT.state.map.setView([selectedAddress.lat, selectedAddress.lng], 16);
    }

    function hideAddressResults() {
        document.getElementById('address-results')?.classList.add('hidden');
    }

    function hideSelectedAddress() {
        selectedAddress = null;
        document.getElementById('selected-address')?.classList.add('hidden');
        const btnAdd = document.getElementById('btn-add-address');
        if (btnAdd) btnAdd.disabled = true;
    }

    function addSelectedAddressToRoute() {
        if (!selectedAddress) return;
        
        WT.addPointToRoute(selectedAddress.lat, selectedAddress.lng, selectedAddress.display);
        
        hideSelectedAddress();
        
        WT.showToast(`Point ajouté : ${formatAddressMain({ address: { road: selectedAddress.display.split(',')[0] } })}`, 'success');
    }

})(window.WalkTrack);
