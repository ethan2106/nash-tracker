/**
 * JavaScript pour la gestion des médicaments
 * Fichier séparé pour une meilleure organisation
 */

// Modal management variables
let editingMedicament = null;

// Modal elements
const modal = document.getElementById('medicament-modal');
const form = document.getElementById('medicament-form');
const modalTitle = document.getElementById('modal-title');

// Open modal for adding new medication
document.getElementById('add-medicament-btn').addEventListener('click', () => {
    openModal(null);
});

// Open modal for adding first regular medication
document.getElementById('add-first-regular-btn')?.addEventListener('click', () => {
    openModal(null, 'regulier');
});

// Open modal for editing medication
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-medicament-btn')) {
        const btn = e.target.closest('.edit-medicament-btn');
        const medicament = JSON.parse(btn.dataset.medicament);
        openModal(medicament);
    }
});

// Close modal functions
document.getElementById('close-modal').addEventListener('click', closeModal);
document.getElementById('cancel-btn').addEventListener('click', closeModal);

// Close modal when clicking outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeModal();
    }
});

// Open modal function
function openModal(medicament = null, forceType = null) {
    editingMedicament = medicament;

    if (medicament) {
        modalTitle.textContent = 'Modifier le médicament';
        populateForm(medicament);
    } else {
        modalTitle.textContent = 'Ajouter un médicament';
        form.reset();
        document.getElementById('medicament-id').value = '';

        // ensure the form type is always 'regulier' in the UI
        const typeInput = form.querySelector('input[name="type"]');
        if (typeInput) {
            typeInput.value = 'regulier';
        }
        toggleFieldsVisibility('regulier');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Focus on first field
    document.getElementById('nom').focus();
}

// Close modal function
function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    form.reset();
    editingMedicament = null;
    document.body.style.overflow = '';
}

// Populate form with medication data
function populateForm(medicament) {
    document.getElementById('medicament-id').value = medicament.id || '';
    document.getElementById('nom').value = medicament.nom || '';
    document.getElementById('dose').value = medicament.dose || '';
    document.getElementById('frequence').value = medicament.frequence || '';
    document.getElementById('date_debut').value = medicament.date_debut || '';
    document.getElementById('date_fin').value = medicament.date_fin || '';
    document.getElementById('notes').value = medicament.notes || '';

    // Ensure the type is set to 'regulier' in the form UI
    const typeInput = form.querySelector('input[name="type"]');
    if (typeInput) {
        typeInput.value = 'regulier';
    }
    toggleFieldsVisibility('regulier');

    // Set checkboxes for times
    let heuresPrise = [];
    try {
        heuresPrise = medicament.heure_prise ? JSON.parse(medicament.heure_prise) : [];
    } catch (e) {
        heuresPrise = medicament.heure_prise ? medicament.heure_prise.split(',').map(h => h.trim()) : [];
    }

    document.querySelectorAll('input[name="heure_prise[]"]').forEach(checkbox => {
        checkbox.checked = heuresPrise.includes(checkbox.value);
    });

    toggleFieldsVisibility(medicament.type);
}

// Toggle field visibility based on medication type
function toggleFieldsVisibility(type) {
    const frequenceGroup = document.getElementById('frequence-container');
    const heurePriseGroup = document.getElementById('heures-container');

    // Only regular medications are supported in the UI — always show frequency / times
    if (frequenceGroup) frequenceGroup.style.display = 'block';
    if (heurePriseGroup) heurePriseGroup.style.display = 'block';
}

// No dynamic type toggling — type is fixed to 'regulier' in the UI

// Form submission
form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const isEdit = formData.get('medicament_id');

    // Validate required fields
    if (!formData.get('nom') || !formData.get('type')) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    // Validate times for regular medications
    if (formData.get('type') === 'regulier') {
        const selectedTimes = formData.getAll('heure_prise[]');
        if (selectedTimes.length === 0) {
            showNotification('Veuillez sélectionner au moins une heure de prise pour un médicament régulier', 'error');
            return;
        }
    }

    try {
        const response = await fetch(`?page=medicaments&action=${isEdit ? 'update' : 'add'}`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
});

// Delete medication
document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-medicament-btn')) {
        const btn = e.target.closest('.delete-medicament-btn');
        const medicamentName = btn.closest('.bg-gradient-to-r').querySelector('h3')?.textContent || 'ce médicament';

        if (confirm(`Êtes-vous sûr de vouloir supprimer "${medicamentName.trim()}" ? Cette action est irréversible.`)) {
            deleteMedicament(btn.dataset.id);
        }
    }
});

async function deleteMedicament(medicamentId) {
    try {
        const formData = new FormData();
        formData.append('medicament_id', medicamentId);

        const response = await fetch('?page=medicaments&action=delete', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Mark medication as taken
document.addEventListener('click', function(e) {
    if (e.target.closest('.periode-btn') && !e.target.closest('.periode-btn').disabled) {
        const btn = e.target.closest('.periode-btn');
        markAsTaken(btn.dataset.medicamentId, btn.dataset.periode, btn);
    }
});

async function markAsTaken(medicamentId, periode, buttonElement) {
    try {
        const formData = new FormData();
        formData.append('medicament_id', medicamentId);
        formData.append('date', window.selectedDate);
        formData.append('periode', periode);

        const response = await fetch('?page=medicaments&action=marquer_pris', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');

            // Update button state
            buttonElement.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
            buttonElement.classList.add('bg-green-500', 'text-white', 'cursor-default');
            buttonElement.disabled = true;

            const icon = buttonElement.querySelector('i');
            if (icon) {
                icon.className = 'fa-solid fa-check mr-1';
            }

            // Update all buttons for this medication if periodes_pris is provided
            if (result.periodes_pris) {
                const buttons = document.querySelectorAll(`.periode-btn[data-medicament-id="${medicamentId}"]`);
                buttons.forEach(b => {
                    const p = b.dataset.periode;
                    const isPris = !!result.periodes_pris[p];
                    b.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
                    if (isPris) {
                        b.classList.add('bg-green-500', 'text-white', 'cursor-default');
                        b.disabled = true;
                        const icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-solid fa-check mr-1';
                        }
                    } else {
                        b.classList.add('bg-blue-100', 'text-blue-700');
                        b.disabled = false;
                        const icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-regular fa-clock mr-1';
                        }
                    }
                });
            }
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Cancel medication as taken
document.addEventListener('click', function(e) {
    if (e.target.closest('.annuler-btn')) {
        const btn = e.target.closest('.annuler-btn');
        cancelAsTaken(btn.dataset.medicamentId, btn.dataset.periode, btn);
    }
});

async function cancelAsTaken(medicamentId, periode, buttonElement) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette prise ?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('medicament_id', medicamentId);
        formData.append('date', window.selectedDate);
        formData.append('periode', periode);

        const response = await fetch('?page=medicaments&action=annuler_pris', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');

            // Find the corresponding periode button and update it
            const periodeBtn = buttonElement.parentElement.querySelector('.periode-btn');
            if (periodeBtn) {
                periodeBtn.classList.remove('bg-green-500', 'text-white', 'cursor-default');
                periodeBtn.classList.add('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
                periodeBtn.disabled = false;

                const icon = periodeBtn.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-clock mr-1';
                }

                const label = periodeBtn.textContent.replace('Validé', '').replace('Valider', '').trim();
                periodeBtn.innerHTML = `<i class="fa-solid fa-clock mr-1"></i> ${label}`;
            }

            // Remove the cancel button
            buttonElement.remove();

            // Update all buttons for this medication if periodes_pris is provided
            if (result.periodes_pris) {
                const buttons = document.querySelectorAll(`.periode-btn[data-medicament-id="${medicamentId}"]`);
                buttons.forEach(b => {
                    const p = b.dataset.periode;
                    const isPris = !!result.periodes_pris[p];
                    b.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
                    if (isPris) {
                        b.classList.add('bg-green-500', 'text-white', 'cursor-default');
                        b.disabled = true;
                        const icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-solid fa-check mr-1';
                        }
                    } else {
                        b.classList.add('bg-blue-100', 'text-blue-700');
                        b.disabled = false;
                        const icon = b.querySelector('i');
                        if (icon) {
                            icon.className = 'fa-regular fa-clock mr-1';
                        }
                    }
                });

                // Update cancel buttons
                buttons.forEach(b => {
                    const p = b.dataset.periode;
                    const isPris = !!result.periodes_pris[p];
                    const container = b.parentElement;
                    const existingCancelBtn = container.querySelector('.annuler-btn');

                    if (isPris && !existingCancelBtn) {
                        // Add cancel button
                        const cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'annuler-btn px-2 py-2 rounded-xl font-medium text-sm focus:outline-none transition-all duration-200 bg-red-100 text-red-700 hover:bg-red-200 hover:shadow-md';
                        cancelBtn.dataset.medicamentId = medicamentId;
                        cancelBtn.dataset.periode = p;
                        cancelBtn.title = 'Annuler cette prise';
                        cancelBtn.innerHTML = '<i class="fa-solid fa-undo"></i>';
                        container.appendChild(cancelBtn);
                    } else if (!isPris && existingCancelBtn) {
                        // Remove cancel button
                        existingCancelBtn.remove();
                    }
                });
            }
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Occasional medication handlers removed — occasional meds are hidden (we show only regular meds)

// Cancel medication dose
document.addEventListener('click', function(e) {
    if (e.target.closest('.annuler-pris-btn')) {
        const btn = e.target.closest('.annuler-pris-btn');
        cancelMedicationDose(btn.dataset.medicamentId, btn.dataset.periode, btn);
    }
});

async function cancelMedicationDose(medicamentId, periode, buttonElement) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette prise ?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('medicament_id', medicamentId);
        formData.append('periode', periode);
        formData.append('date', window.selectedDate);

        const response = await fetch('?page=medicaments&action=annuler_pris', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');

            // Update button appearance to show it's not taken
            buttonElement.classList.remove('bg-green-500', 'text-white', 'cursor-default');
            buttonElement.classList.add('text-green-600', 'hover:text-green-800', 'hover:bg-green-100');
            buttonElement.classList.remove('annuler-pris-btn');
            buttonElement.classList.add('marquer-pris-btn');
            buttonElement.disabled = false;
            buttonElement.title = 'Marquer comme pris';

            const icon = buttonElement.querySelector('i');
            if (icon) {
                icon.className = 'fa-solid fa-check text-lg';
            }

            // Update period status
            const periodContainer = buttonElement.closest('.flex.items-center');
            const periodStatus = periodContainer.querySelector('.text-sm');
            if (periodStatus) {
                periodStatus.className = 'text-sm text-gray-500';
                periodStatus.textContent = 'Non pris';
            }
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Cleanup duplicates
document.getElementById('cleanup-duplicates-btn').addEventListener('click', async function() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer les médicaments en double ? Cette action est irréversible.')) {
        return;
    }

    try {
        const response = await fetch('?page=medicaments&action=cleanup_duplicates', {
            method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.error || 'Erreur inconnue', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
});

// Notification system
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-6 right-6 px-6 py-4 rounded-2xl shadow-2xl z-50 font-semibold text-white text-lg ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-2xl"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Set default type if none selected
    if (!document.querySelector('input[name="type"]:checked')) {
        document.getElementById('type-regulier').checked = true;
    }
    // Note: toggleFieldsVisibility is called when modal opens, not on page load
});


