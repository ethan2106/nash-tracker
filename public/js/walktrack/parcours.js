/**
 * WalkTrack - Parcours Module
 * Gestion des parcours favoris
 */

(function(WT) {
    'use strict';

    // ================================================================
    // MODAL PARCOURS FAVORIS
    // ================================================================
    WT.initParcoursModal = function() {
        const btnOpen = document.getElementById('btn-parcours-favoris');
        const modal = document.getElementById('modal-parcours');
        const overlay = document.getElementById('modal-parcours-overlay');
        const btnClose = document.getElementById('btn-close-modal-parcours');
        const btnSave = document.getElementById('btn-save-parcours');
        
        if (!btnOpen || !modal) return;
        
        btnOpen.addEventListener('click', () => {
            modal.classList.remove('hidden');
            loadParcoursList();
        });
        
        overlay?.addEventListener('click', () => modal.classList.add('hidden'));
        btnClose?.addEventListener('click', () => modal.classList.add('hidden'));
        
        if (btnSave) {
            btnSave.addEventListener('click', handleSaveParcours);
        }
    };

    async function loadParcoursList() {
        const container = document.getElementById('parcours-list');
        const parcours = window.walktrackData?.parcoursFavoris || [];
        
        if (parcours.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-slate-400">
                    <i class="fa-solid fa-star text-2xl mb-2"></i>
                    <p class="text-sm">Aucun parcours sauvegardé</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = parcours.map(p => `
            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-purple-50 cursor-pointer transition-all parcours-item" 
                 data-route='${JSON.stringify(p.route_points)}'>
                <div>
                    <div class="font-medium text-slate-700">${WT.escapeHtml(p.name)}</div>
                    <div class="text-sm text-slate-400">${p.distance_km} km</div>
                </div>
                <button type="button" class="btn-delete-parcours text-red-400 hover:text-red-600 p-2" data-route-id="${p.id}">
                    <i class="fa-solid fa-trash-alt"></i>
                </button>
            </div>
        `).join('');
        
        // Handlers pour charger un parcours
        container.querySelectorAll('.parcours-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.closest('.btn-delete-parcours')) return;
                
                const routePoints = JSON.parse(item.dataset.route);
                WT.loadRoute(routePoints);
                document.getElementById('modal-parcours').classList.add('hidden');
                WT.showNotification('Parcours chargé !', 'success');
            });
        });
        
        // Handlers pour supprimer
        container.querySelectorAll('.btn-delete-parcours').forEach(btn => {
            btn.addEventListener('click', handleDeleteParcours);
        });
    }

    async function handleSaveParcours() {
        const name = document.getElementById('input-parcours-name').value.trim();
        
        if (!name) {
            WT.showNotification('Entrez un nom pour le parcours', 'error');
            return;
        }
        
        if (WT.state.routePoints.length < 2) {
            WT.showNotification('Tracez au moins 2 points sur la carte', 'error');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('distance_km', WT.state.totalDistance.toFixed(2));
            formData.append('route_points', JSON.stringify(WT.state.routePoints));
            
            const response = await fetch('?page=walktrack/routes/save', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                WT.showNotification('Parcours sauvegardé !', 'success');
                document.getElementById('input-parcours-name').value = '';
                document.getElementById('modal-parcours').classList.add('hidden');
                setTimeout(() => location.reload(), 500);
            } else {
                WT.showNotification(data.error || 'Erreur', 'error');
            }
        } catch (err) {
            WT.showNotification('Erreur de connexion', 'error');
        }
    }

    async function handleDeleteParcours(e) {
        e.stopPropagation();
        
        const btn = e.currentTarget;
        const routeId = btn.dataset.routeId;
        
        if (!confirm('Supprimer ce parcours ?')) return;
        
        try {
            const formData = new FormData();
            formData.append('route_id', routeId);
            
            const response = await fetch('?page=walktrack/routes/delete', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                btn.closest('.parcours-item').remove();
                WT.showNotification('Parcours supprimé', 'success');
            } else {
                WT.showNotification(data.error || 'Erreur', 'error');
            }
        } catch (err) {
            WT.showNotification('Erreur de connexion', 'error');
        }
    }

})(window.WalkTrack);
