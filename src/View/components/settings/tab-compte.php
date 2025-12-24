<?php

/**
 * Composant: Onglet Compte.
 *
 * @description Gestion du compte : infos, email, pseudo, mot de passe, suppression
 * @requires Alpine.js - Variables: email, pseudo, errors, loading, updateEmail(), updatePseudo(), etc.
 *
 * @var array $stats Statistiques utilisateur (date_inscription, nb_repas, nb_objectifs)
 * @var callable $e Fonction d'échappement HTML
 */

declare(strict_types=1);
?>
<!-- ============================================================
     ONGLET COMPTE
     - Informations du compte (stats)
     - Modifier email
     - Modifier pseudo
     - Modifier mot de passe
     - Zone danger (suppression compte)
     ============================================================ -->
<div x-show="activeTab === 'compte'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-4"
     x-transition:enter-end="opacity-100 transform translate-x-0">
    
    <div class="space-y-8">
        
        <!-- ===== INFORMATIONS COMPTE ===== -->
        <div class="bg-blue-50/50 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                Informations du compte
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-blue-600"></i>
                    <span class="text-gray-600">Membre depuis :</span>
                    <span class="font-semibold"><?= $e($stats['date_inscription'] ?? 'N/A'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-blue-600"></i>
                    <span class="text-gray-600">Repas enregistrés :</span>
                    <span class="font-semibold"><?= $stats['nb_repas'] ?? 0; ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-blue-600"></i>
                    <span class="text-gray-600">Objectifs créés :</span>
                    <span class="font-semibold"><?= $stats['nb_objectifs'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <!-- ===== MODIFIER EMAIL ===== -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Adresse email</h3>
            <form @submit.prevent="updateEmail()" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nouvel email</label>
                    <input type="email" 
                           x-model="email" 
                           @input="validateEmail()"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:outline-none focus:ring-2 focus:ring-blue-300 
                                  transition-all"
                           required>
                    <p x-show="errors.email" 
                       x-text="errors.email" 
                       class="text-red-600 text-sm mt-1"></p>
                </div>
                <button type="submit" 
                        :disabled="loading.email"
                        :class="loading.email ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 
                               text-white font-semibold rounded-xl shadow-lg 
                               hover:shadow-xl hover:scale-105 transition-all">
                    <i class="fa-solid fa-save mr-2"></i>
                    <span x-text="loading.email ? 'Enregistrement...' : 'Enregistrer'"></span>
                </button>
            </form>
        </div>

        <!-- ===== MODIFIER PSEUDO ===== -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Pseudo</h3>
            <form @submit.prevent="updatePseudo()" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nouveau pseudo</label>
                    <input type="text" 
                           x-model="pseudo" 
                           @input="validatePseudo()"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:outline-none focus:ring-2 focus:ring-blue-300 
                                  transition-all"
                           required>
                    <p class="text-xs text-gray-500 mt-1">3-50 caractères, alphanumérique, _ et - autorisés</p>
                    <p x-show="errors.pseudo" 
                       x-text="errors.pseudo" 
                       class="text-red-600 text-sm mt-1"></p>
                </div>
                <button type="submit" 
                        :disabled="loading.pseudo"
                        :class="loading.pseudo ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 
                               text-white font-semibold rounded-xl shadow-lg 
                               hover:shadow-xl hover:scale-105 transition-all">
                    <i class="fa-solid fa-save mr-2"></i>
                    <span x-text="loading.pseudo ? 'Enregistrement...' : 'Enregistrer'"></span>
                </button>
            </form>
        </div>

        <!-- ===== MODIFIER MOT DE PASSE ===== -->
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Mot de passe</h3>
            <form @submit.prevent="updatePassword()" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe actuel</label>
                    <input type="password" 
                           x-model="currentPassword"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:outline-none focus:ring-2 focus:ring-blue-300 
                                  transition-all"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nouveau mot de passe</label>
                    <input type="password" 
                           x-model="newPassword" 
                           @input="validatePassword()"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:outline-none focus:ring-2 focus:ring-blue-300 
                                  transition-all"
                           required>
                    <!-- Barre de force du mot de passe -->
                    <div x-show="newPassword" class="mt-2">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div :class="getPasswordStrengthColor()" 
                                 :style="`width: ${getPasswordStrengthWidth()}`"
                                 class="h-full transition-all duration-300"></div>
                        </div>
                        <p class="text-xs mt-1" 
                           :class="passwordStrength <= 1 ? 'text-red-600' : passwordStrength <= 3 ? 'text-orange-600' : 'text-green-600'"
                           x-text="'Force : ' + passwordStrengthLabel"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <input type="password" 
                           x-model="confirmPassword" 
                           @input="validatePassword()"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:outline-none focus:ring-2 focus:ring-blue-300 
                                  transition-all"
                           required>
                    <p x-show="errors.password" 
                       x-text="errors.password" 
                       class="text-red-600 text-sm mt-1"></p>
                </div>
                <button type="submit" 
                        :disabled="loading.password"
                        :class="loading.password ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 
                               text-white font-semibold rounded-xl shadow-lg 
                               hover:shadow-xl hover:scale-105 transition-all">
                    <i class="fa-solid fa-key mr-2"></i>
                    <span x-text="loading.password ? 'Enregistrement...' : 'Changer le mot de passe'"></span>
                </button>
            </form>
        </div>

        <!-- ===== ZONE DANGER - SUPPRIMER COMPTE ===== -->
        <div class="bg-red-50 rounded-2xl p-6 border-2 border-red-200">
            <h3 class="text-lg font-bold text-red-700 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Zone dangereuse
            </h3>
            <p class="text-sm text-red-600 mb-4">
                La suppression de votre compte est <strong>IRRÉVERSIBLE</strong>. 
                Toutes vos données (repas, objectifs, activités) seront définitivement perdues.
            </p>
            <form @submit.prevent="deleteAccount()" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" 
                           x-model="deletePassword"
                           class="w-full px-4 py-3 rounded-xl border border-red-300 
                                  focus:outline-none focus:ring-2 focus:ring-red-300 
                                  transition-all"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tapez <span class="font-mono bg-gray-200 px-2 py-1 rounded">SUPPRIMER</span> pour confirmer
                    </label>
                    <input type="text" 
                           x-model="deleteConfirmation"
                           class="w-full px-4 py-3 rounded-xl border border-red-300 
                                  focus:outline-none focus:ring-2 focus:ring-red-300 
                                  transition-all font-mono"
                           required>
                    <p x-show="errors.delete" 
                       x-text="errors.delete" 
                       class="text-red-600 text-sm mt-1"></p>
                </div>
                <button type="submit" 
                        :disabled="loading.delete"
                        :class="loading.delete ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 
                               text-white font-semibold rounded-xl shadow-lg 
                               transition-all">
                    <i class="fa-solid fa-trash mr-2"></i>
                    <span x-text="loading.delete ? 'Suppression...' : 'Supprimer définitivement mon compte'"></span>
                </button>
            </form>
        </div>

    </div>
</div>
