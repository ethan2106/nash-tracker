<?php

/**
 * Composant: Onglet Export Données.
 *
 * @description Export PDF/CSV des données de santé
 * @requires JavaScript - Fonctions: exportData(), quickExport(), previewReport(), downloadExport()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     ONGLET EXPORT DONNÉES
     - Choix format (PDF/CSV) et période
     - Bouton export + aperçu
     - Export rapide (PDF 7 jours)
     ============================================================ -->
<div x-show="activeTab === 'export'"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-4"
     x-transition:enter-end="opacity-100 transform translate-x-0">

    <div class="space-y-8">
        
        <!-- ===== HEADER EXPORT ===== -->
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="fa-solid fa-download text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Export de Données</h3>
                <p class="text-gray-600">Télécharge tes données de santé en PDF ou CSV</p>
            </div>
        </div>

        <!-- ===== OPTIONS D'EXPORT ===== -->
        <div class="bg-white rounded-2xl border p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Choisir le format et la période</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Format d'export</label>
                    <select id="export-format" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500">
                        <option value="pdf">PDF (Rapport formaté)</option>
                        <option value="csv">CSV (Données brutes)</option>
                    </select>
                </div>

                <!-- Période -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période des données</label>
                    <select id="export-period" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500">
                        <option value="7days">7 derniers jours</option>
                        <option value="30days">30 derniers jours</option>
                        <option value="90days">90 derniers jours</option>
                        <option value="1year">Dernière année</option>
                    </select>
                </div>
            </div>

            <!-- Boutons d'export -->
            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                <button type="button"
                        onclick="exportData()"
                        class="flex-1 px-6 py-3 rounded-xl text-white font-semibold shadow-md bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-download"></i>
                    <span id="export-btn-text">Exporter les données</span>
                </button>

                <button type="button"
                        onclick="previewReport()"
                        class="px-6 py-3 rounded-xl font-semibold shadow-sm border border-indigo-300 text-indigo-800 bg-white hover:bg-indigo-50 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-eye"></i>
                    Aperçu PDF
                </button>
            </div>

            <!-- Informations contenu export -->
            <div class="mt-6 bg-indigo-50 rounded-lg p-4">
                <h5 class="font-semibold text-indigo-900 mb-2">Que contient l'export ?</h5>
                <ul class="text-sm text-indigo-800 space-y-1">
                    <li>• Informations utilisateur (nom, email, date d'inscription)</li>
                    <li>• Données IMC actuelles (poids, taille, BMR, TDEE)</li>
                    <li>• Historique poids/IMC selon la période choisie</li>
                    <li>• Repas et calories du jour actuel</li>
                </ul>
            </div>
        </div>

        <!-- ===== EXPORT RAPIDE ===== -->
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl border border-indigo-200 p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Export rapide</h4>
            <p class="text-gray-600 mb-4">Télécharge immédiatement un rapport PDF des 7 derniers jours</p>

            <button type="button"
                    onclick="quickExport()"
                    class="px-6 py-3 rounded-xl text-white font-semibold shadow-md bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
                <i class="fa-solid fa-bolt"></i>
                Export rapide (PDF - 7 jours)
            </button>
        </div>
    </div>
</div>
