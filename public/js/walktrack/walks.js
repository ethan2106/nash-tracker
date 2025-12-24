/**
 * WalkTrack - Walks Module
 * CRUD des marches (visualisation, édition, suppression)
 */

(function(WT) {
    'use strict';

    // ================================================================
    // SUPPRESSION & VISUALISATION
    // ================================================================
    WT.initDeleteHandlers = function() {
        // Boutons supprimer
        document.querySelectorAll('.btn-delete-walk').forEach(btn => {
            btn.addEventListener('click', handleDeleteWalk);
        });
        
        // Boutons voir le trajet
        document.querySelectorAll('.btn-view-route').forEach(btn => {
            btn.addEventListener('click', handleViewRoute);
        });
        
        // Clic sur la carte entière de la marche (si elle a un parcours)
        document.querySelectorAll('.walk-item[data-has-route="true"]').forEach(item => {
            item.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                
                const routePoints = item.dataset.routePoints;
                if (routePoints) {
                    viewRouteOnMap(JSON.parse(routePoints));
                }
            });
        });
    };

    function handleViewRoute(e) {
        e.stopPropagation();
        const btn = e.currentTarget;
        const routePointsJson = btn.dataset.routePoints;
        
        if (!routePointsJson) {
            WT.showToast('Pas de parcours enregistré', 'error');
            return;
        }
        
        try {
            const routePoints = JSON.parse(routePointsJson);
            viewRouteOnMap(routePoints);
        } catch (err) {
            console.error('Erreur parsing route:', err);
            WT.showToast('Erreur de chargement du parcours', 'error');
        }
    }

    function viewRouteOnMap(routePoints) {
        if (!routePoints || routePoints.length === 0) {
            WT.showToast('Parcours vide', 'error');
            return;
        }
        
        WT.loadRoute(routePoints);
        
        const mapElement = document.getElementById('walktrack-map');
        if (mapElement) {
            mapElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        WT.showToast(`Parcours chargé (${routePoints.length} points)`, 'success');
    }

    async function handleDeleteWalk(e) {
        e.stopPropagation();
        const btn = e.currentTarget;
        const walkId = btn.dataset.walkId;
        
        if (!confirm('Supprimer cette marche ?')) return;
        
        try {
            const formData = new FormData();
            formData.append('walk_id', walkId);
            
            const response = await fetch('?page=walktrack/delete', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const walkItem = btn.closest('.walk-item');
                if (walkItem) {
                    walkItem.remove();
                }
                WT.showNotification('Marche supprimée', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                WT.showNotification(data.error || 'Erreur', 'error');
            }
        } catch (err) {
            WT.showNotification('Erreur de connexion', 'error');
        }
    }

    // ================================================================
    // ÉDITION
    // ================================================================
    WT.initEditHandlers = function() {
        const modal = document.getElementById('modal-edit-walk');
        const overlay = document.getElementById('modal-edit-overlay');
        const btnClose = document.getElementById('btn-close-edit-modal');
        const btnCancel = document.getElementById('btn-cancel-edit');
        const form = document.getElementById('form-edit-walk');
        
        if (!modal) return;
        
        // Boutons éditer
        document.querySelectorAll('.btn-edit-walk').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openEditModal(btn.dataset);
            });
        });
        
        // Fermer la modale
        overlay?.addEventListener('click', closeEditModal);
        btnClose?.addEventListener('click', closeEditModal);
        btnCancel?.addEventListener('click', closeEditModal);
        
        // Soumettre
        form?.addEventListener('submit', handleEditWalk);
        
        // Calcul auto durée
        const startTime = document.getElementById('edit-start-time');
        const endTime = document.getElementById('edit-end-time');
        
        if (startTime && endTime) {
            startTime.addEventListener('change', calculateEditDurationFromTimes);
            endTime.addEventListener('change', calculateEditDurationFromTimes);
        }
    };

    function openEditModal(data) {
        const modal = document.getElementById('modal-edit-walk');
        if (!modal) return;
        
        document.getElementById('edit-walk-id').value = data.walkId;
        document.getElementById('edit-duration').value = data.duration;
        document.getElementById('edit-start-time').value = data.startTime || '';
        document.getElementById('edit-end-time').value = data.endTime || '';
        document.getElementById('edit-note').value = data.note || '';
        
        // Type de marche
        const typeDisplay = document.getElementById('edit-walk-type-display');
        if (data.walkType === 'marche_rapide') {
            typeDisplay.innerHTML = '<i class="fa-solid fa-person-walking-arrow-right text-blue-500"></i><span>Marche rapide</span>';
        } else {
            typeDisplay.innerHTML = '<i class="fa-solid fa-person-walking text-green-500"></i><span>Marche normale</span>';
        }
        
        // Distance
        document.getElementById('edit-distance-display').innerHTML = 
            `${parseFloat(data.distance).toFixed(2).replace('.', ',')} km <span class="text-xs text-slate-400 ml-2">(non modifiable)</span>`;
        
        document.getElementById('edit-duration-source').textContent = '';
        
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('modal-edit-walk');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function calculateEditDurationFromTimes() {
        const startTime = document.getElementById('edit-start-time').value;
        const endTime = document.getElementById('edit-end-time').value;
        const durationInput = document.getElementById('edit-duration');
        const durationSource = document.getElementById('edit-duration-source');
        
        const duration = WT.calculateDurationFromTimes(startTime, endTime);
        
        if (duration) {
            durationInput.value = duration;
            durationSource.textContent = `(${startTime} → ${endTime})`;
            durationSource.classList.add('text-green-600');
        }
    }

    async function handleEditWalk(e) {
        e.preventDefault();
        
        const form = e.target;
        const btn = document.getElementById('btn-save-edit');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('?page=walktrack/edit', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                WT.showNotification('Marche modifiée !', 'success');
                closeEditModal();
                setTimeout(() => location.reload(), 500);
            } else {
                WT.showNotification(data.error || 'Erreur lors de la modification', 'error');
            }
        } catch (err) {
            console.error('Erreur:', err);
            WT.showNotification('Erreur de connexion', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

})(window.WalkTrack);
