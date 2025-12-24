/**
 * WalkTrack - Form Module
 * Formulaire d'ajout et mode simulation
 */

(function(WT) {
    'use strict';

    // ================================================================
    // FORMULAIRE AJOUT
    // ================================================================
    WT.initFormHandlers = function() {
        const form = document.getElementById('form-add-walk');
        if (!form) return;
        
        form.addEventListener('submit', handleAddWalk);
        
        // Mise à jour calories quand type ou durée change
        form.querySelectorAll('input[name="walk_type"]').forEach(input => {
            input.addEventListener('change', WT.updateEstimatedCalories);
        });
        
        const durationInput = document.getElementById('input-duration');
        if (durationInput) {
            durationInput.addEventListener('input', WT.updateEstimatedCalories);
        }
            
        // Calcul automatique de la durée via heures départ/arrivée
        const startTime = document.getElementById('input-start-time');
        const endTime = document.getElementById('input-end-time');
        
        if (startTime && endTime) {
            startTime.addEventListener('change', handleTimeChange);
            endTime.addEventListener('change', handleTimeChange);
        }
    };

    function handleTimeChange() {
        const startTime = document.getElementById('input-start-time').value;
        const endTime = document.getElementById('input-end-time').value;
        const durationInput = document.getElementById('input-duration');
        const durationSource = document.getElementById('duration-source');
        
        const duration = WT.calculateDurationFromTimes(startTime, endTime);
        
        if (duration) {
            durationInput.value = duration;
            durationSource.textContent = `(${startTime} → ${endTime})`;
            durationSource.classList.add('text-green-600');
            durationSource.classList.remove('text-slate-400');
            WT.updateEstimatedCalories();
        }
    }

    WT.updateEstimatedCalories = function() {
        const type = document.querySelector('input[name="walk_type"]:checked')?.value || 'marche';
        const duration = parseInt(document.getElementById('input-duration')?.value) || 0;
        
        const calories = WT.calculateCalories(type, duration);
        
        const elem = document.getElementById('estimated-calories');
        if (elem) {
            elem.textContent = calories;
        }
    };

    async function handleAddWalk(e) {
        e.preventDefault();
        
        const form = e.target;
        const btn = document.getElementById('btn-submit-walk');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Ajout en cours...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('?page=walktrack/add', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                WT.showNotification('Marche ajoutée avec succès !', 'success');
                form.reset();
                WT.resetMap();
                setTimeout(() => location.reload(), 500);
            } else {
                WT.showNotification(data.error || 'Erreur lors de l\'ajout', 'error');
            }
        } catch (err) {
            console.error('Erreur:', err);
            WT.showNotification('Erreur de connexion', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    // ================================================================
    // MODE SIMULATION
    // ================================================================
    WT.initSimulationMode = function() {
        const toggle = document.getElementById('toggle-simulation');
        const btnSimulate = document.getElementById('btn-simulate');
        const btnModify = document.getElementById('btn-modify-simulation');
        
        if (!toggle) return;
        
        toggle.addEventListener('change', () => {
            WT.isSimulationMode = toggle.checked;
            updateSimulationUI();
        });
        
        if (btnSimulate) {
            btnSimulate.addEventListener('click', runSimulation);
        }
        
        if (btnModify) {
            btnModify.addEventListener('click', () => {
                document.getElementById('simulation-result').classList.add('hidden');
                document.getElementById('btn-simulation-mode').classList.remove('hidden');
            });
        }
    };

    function updateSimulationUI() {
        const normalMode = document.getElementById('btn-normal-mode');
        const simulationMode = document.getElementById('btn-simulation-mode');
        const simulationInfo = document.getElementById('simulation-info');
        const simulationResult = document.getElementById('simulation-result');
        const formTitle = document.getElementById('form-title');
        const formIcon = document.getElementById('form-icon');
        
        if (WT.isSimulationMode) {
            normalMode?.classList.add('hidden');
            simulationMode?.classList.remove('hidden');
            simulationInfo?.classList.remove('hidden');
            simulationResult?.classList.add('hidden');
            if (formTitle) formTitle.textContent = 'Planifier un parcours';
            if (formIcon) formIcon.className = 'fa-solid fa-flask text-purple-500';
        } else {
            normalMode?.classList.remove('hidden');
            simulationMode?.classList.add('hidden');
            simulationInfo?.classList.add('hidden');
            simulationResult?.classList.add('hidden');
            if (formTitle) formTitle.textContent = 'Enregistrer une marche';
            if (formIcon) formIcon.className = 'fa-solid fa-plus-circle text-green-500';
        }
    }

    function runSimulation() {
        const distance = parseFloat(document.getElementById('input-distance').value) || 0;
        const type = document.querySelector('input[name="walk_type"]:checked')?.value || 'marche';
        
        if (distance <= 0) {
            WT.showToast('Tracez un parcours sur la carte d\'abord !', 'error');
            return;
        }
        
        const speed = WT.config.SPEEDS[type] || WT.config.SPEEDS.marche;
        const durationMinutes = Math.round((distance / speed) * 60);
        const calories = WT.calculateCalories(type, durationMinutes);
        
        document.getElementById('sim-distance').textContent = distance.toFixed(2);
        document.getElementById('sim-duration').textContent = durationMinutes;
        document.getElementById('sim-calories').textContent = calories;
        document.getElementById('sim-speed').textContent = speed.toFixed(1);
        
        document.getElementById('input-duration').value = durationMinutes;
        WT.updateEstimatedCalories();
        
        document.getElementById('btn-simulation-mode').classList.add('hidden');
        document.getElementById('simulation-result').classList.remove('hidden');
        
        WT.showToast('Simulation terminée !', 'success');
    }

    // ================================================================
    // OBJECTIFS
    // ================================================================
    WT.initObjectivesHandlers = function() {
        const btnEdit = document.getElementById('btn-edit-objectives');
        const btnCancel = document.getElementById('btn-cancel-objectives');
        const form = document.getElementById('form-objectives');
        const display = document.getElementById('objectives-display');
        
        if (!btnEdit || !form) return;
        
        btnEdit.addEventListener('click', () => {
            display.classList.add('hidden');
            form.classList.remove('hidden');
        });
        
        btnCancel.addEventListener('click', () => {
            form.classList.add('hidden');
            display.classList.remove('hidden');
        });
        
        form.addEventListener('submit', handleUpdateObjectives);
    };

    async function handleUpdateObjectives(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch('?page=walktrack/objectives', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                WT.showNotification('Objectifs mis à jour !', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                WT.showNotification(data.error || 'Erreur', 'error');
            }
        } catch (err) {
            WT.showNotification('Erreur de connexion', 'error');
        }
    }

})(window.WalkTrack);
