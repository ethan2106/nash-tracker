<?php

/**
 * Composant: Onglet Notifications.
 *
 * @description Gestion des notifications : toggles, période silencieuse
 * @requires Alpine.js - Variables: userConfig, quietStart, quietEnd, errors, loading, saveQuietHours(), resetQuietHoursDefaults()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     ONGLET NOTIFICATIONS
     - Toggles : activité, objectifs atteints
     - Période silencieuse (heures de début/fin)
     ============================================================ -->
<div x-show="activeTab === 'notifications'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-4"
     x-transition:enter-end="opacity-100 transform translate-x-0">
    
    <div class="space-y-8">
        
        <!-- ===== HEADER NOTIFICATIONS ===== -->
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fa-solid fa-bell text-purple-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Notifications</h3>
                <p class="text-gray-600">Personnalise tes rappels et alertes</p>
            </div>
        </div>

        <!-- ===== TOGGLES PRINCIPAUX ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Toggle Activité -->
            <div class="bg-white rounded-2xl border p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">Activité</p>
                        <p class="text-xs text-gray-500">Rappels pour bouger un peu</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" class="sr-only peer"
                               :checked="userConfig.notify_activity_enabled == 1"
                               @change="userConfig.notify_activity_enabled = $event.target.checked ? 1 : 0; updateUserConfig('notify_activity_enabled')">
                        <div class="relative w-14 h-8 rounded-full bg-gray-200 transition-colors duration-200 shadow-inner peer-focus:ring-2 peer-focus:ring-purple-300 peer-checked:bg-gradient-to-r peer-checked:from-purple-500 peer-checked:to-indigo-500">
                            <span class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transform transition-all duration-200 peer-checked:translate-x-6"></span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Toggle Objectifs atteints -->
            <div class="bg-white rounded-2xl border p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">Objectifs atteints</p>
                        <p class="text-xs text-gray-500">Lorsqu'un objectif est atteint</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" class="sr-only peer"
                               :checked="userConfig.notify_goals_enabled == 1"
                               @change="userConfig.notify_goals_enabled = $event.target.checked ? 1 : 0; updateUserConfig('notify_goals_enabled')">
                        <div class="relative w-14 h-8 rounded-full bg-gray-200 transition-colors duration-200 shadow-inner peer-focus:ring-2 peer-focus:ring-purple-300 peer-checked:bg-gradient-to-r peer-checked:from-purple-500 peer-checked:to-indigo-500">
                            <span class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transform transition-all duration-200 peer-checked:translate-x-6"></span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- ===== PÉRIODE SILENCIEUSE ===== -->
        <div class="bg-purple-50 rounded-2xl border border-purple-200 p-5">
            <p class="font-semibold text-purple-900 mb-2">Période silencieuse</p>
            <p class="text-sm text-purple-800 mb-4">Aucune notification entre ces heures</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-600">Début</label>
                    <select class="mt-1 w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-purple-300" 
                            x-model.number="quietStart">
                        <?php for ($h = 0; $h <= 23; $h++)
                        { ?>
                            <option value="<?= $h; ?>"><?= str_pad((string)$h, 2, '0', STR_PAD_LEFT); ?>:00</option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Fin</label>
                    <select class="mt-1 w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-purple-300" 
                            x-model.number="quietEnd">
                        <?php for ($h = 0; $h <= 23; $h++)
                        { ?>
                            <option value="<?= $h; ?>"><?= str_pad((string)$h, 2, '0', STR_PAD_LEFT); ?>:00</option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <p class="text-xs text-purple-700 mt-3" x-show="!errors['quiet_hours']">
                Astuce : par exemple 22:00 → 07:00 pour la nuit.
            </p>
            <p class="text-sm text-red-600 mt-3" x-show="errors['quiet_hours']" x-text="errors['quiet_hours']"></p>

            <div class="mt-4 flex items-center gap-3">
                <button type="button"
                        @click="saveQuietHours()"
                        :disabled="loading['notify_quiet_start_hour'] || loading['notify_quiet_end_hour']"
                        :class="(loading['notify_quiet_start_hour'] || loading['notify_quiet_end_hour']) ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-5 py-2.5 rounded-xl text-white font-semibold shadow-md bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 transition-all">
                    <i class="fa-solid fa-save mr-2"></i>
                    <span x-text="(loading['notify_quiet_start_hour'] || loading['notify_quiet_end_hour']) ? 'Enregistrement...' : 'Sauver'"></span>
                </button>
                <button type="button"
                        @click="resetQuietHoursDefaults()"
                        class="px-5 py-2.5 rounded-xl font-semibold shadow-sm border border-purple-300 text-purple-800 bg-white hover:bg-purple-50 transition-all">
                    <i class="fa-solid fa-rotate-left mr-2"></i>
                    Défaut
                </button>
            </div>
        </div>
    </div>
</div>
