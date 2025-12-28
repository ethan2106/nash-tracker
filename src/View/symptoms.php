<?php
/**
 * Vue symptoms.php - Journal des symptômes.
 */

declare(strict_types=1);

// Inclusion des helpers de vue
require_once __DIR__ . '/../Helper/view_helpers.php';

$title = 'Journal des symptômes - Suivi Nash';

$pageJs = ['alpine-symptoms.js'];

// Inclure le layout
ob_start();
?>

<div class="min-h-screen bg-[radial-gradient(circle_at_top,#e0f2fe,transparent_55%),radial-gradient(circle_at_20%_20%,#fef3c7,transparent_40%),radial-gradient(circle_at_90%_10%,#ede9fe,transparent_45%),linear-gradient(180deg,#f8fafc,white)]"
     x-data="symptomManager"
     x-init="init()"
     @keydown.window="handleKeydown($event)"
     data-symptoms="<?= htmlspecialchars(json_encode($symptoms)); ?>"
     data-symptom-types="<?= htmlspecialchars(json_encode($symptomTypes)); ?>"
     data-start-date="<?= date('Y-m-d', strtotime('-30 days')); ?>"
     data-end-date="<?= date('Y-m-d'); ?>"
     data-today="<?= date('Y-m-d'); ?>">

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Journal des symptômes</h1>
                <p class="text-sm text-slate-500">Suivez vos symptômes pour identifier des patterns</p>
            </div>
            <button @click="showAddModal = true"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                <i class="fa-solid fa-plus mr-2"></i>
                Ajouter symptôme
            </button>
        </div>

        <!-- Filtres -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Date de début</label>
                <input type="date" x-model="startDate" class="rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Date de fin</label>
                <input type="date" x-model="endDate" class="rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button @click="loadSymptoms()"
                        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    <i class="fa-solid fa-filter mr-2"></i>
                    Filtrer
                </button>
            </div>
        </div>

        <!-- Liste des symptômes -->
        <div x-show="symptoms.length > 0" class="space-y-4">
            <template x-for="symptom in symptoms" :key="symptom.id">
                <div class="flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                            <i class="fa-solid fa-heartbeat"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900" x-text="getSymptomLabel(symptom.symptom_type)"></p>
                            <p class="text-sm text-slate-500" x-text="'Intensité: ' + symptom.intensity + '/10 - ' + formatDate(symptom.date)"></p>
                            <p x-show="symptom.notes" class="text-sm text-slate-600" x-text="symptom.notes"></p>
                        </div>
                    </div>
                    <button @click="deleteSymptom(symptom.id)"
                            class="text-red-600 hover:text-red-700 p-2">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </template>
        </div>

        <div x-show="symptoms.length === 0" class="text-center py-12">
            <i class="fa-solid fa-heartbeat text-4xl text-slate-300 mb-4"></i>
            <p class="text-slate-500">Aucun symptôme enregistré pour cette période.</p>
        </div>
    </div>
</div>

<!-- Modal ajout symptôme -->
<div x-show="showAddModal" x-cloak
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
     @keydown.escape.window="showAddModal = false">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900">Ajouter un symptôme</h2>
            <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form @submit.prevent="addSymptom()">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type de symptôme</label>
                    <select x-model="newSymptom.type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionner...</option>
                        <template x-for="(label, type) in symptomTypes" :key="type">
                            <option :value="type" x-text="label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Intensité (1-10)</label>
                    <input type="range" min="1" max="10" x-model="newSymptom.intensity" class="w-full">
                    <div class="text-center text-sm text-slate-600" x-text="'Niveau: ' + newSymptom.intensity"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                    <input type="date" x-model="newSymptom.date" required class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optionnel)</label>
                    <textarea x-model="newSymptom.notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" @click="showAddModal = false" class="flex-1 px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                    Annuler
                </button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>