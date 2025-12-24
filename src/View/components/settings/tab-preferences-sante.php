<?php

/**
 * Composant: Onglet Préférences Santé.
 *
 * @description Seuils personnalisés : activité, graisses, sucres, IMC, conditions médicales
 * @requires Alpine.js - Variables: userConfig, errors, loading, updateUserConfig(), resetConfig(), validateConfig()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     ONGLET PRÉFÉRENCES SANTÉ
     - Objectif activité quotidienne
     - Limite graisses saturées
     - Limite sucres rapides
     - Seuils IMC (sous-poids, normal, surpoids)
     - Conditions médicales (cardiaque, diabète, autres)
     ============================================================ -->
<div x-show="activeTab === 'preferences-sante'"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-4"
     x-transition:enter-end="opacity-100 transform translate-x-0">

    <div class="space-y-6">
        
        <!-- ===== TITRE SECTION ===== -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Préférences de Santé Personnalisées</h2>
            <p class="text-gray-600">Ajustez vos seuils de santé selon vos besoins personnels</p>
        </div>

        <!-- ===== OBJECTIF ACTIVITÉ QUOTIDIENNE ===== -->
        <div class="bg-white rounded-2xl p-6 border border-emerald-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-person-running text-emerald-600"></i>
                Objectif d'activité quotidienne
            </h3>
            <form @submit.prevent="updateUserConfig('activite_objectif_minutes')" class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minutes par jour</label>
                        <input type="number"
                               step="5"
                               min="10"
                               max="180"
                               x-model.number="userConfig.activite_objectif_minutes"
                               @input="validateConfig('activite_objectif_minutes')"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300
                                      focus:outline-none focus:ring-2 focus:ring-emerald-300
                                      transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Recommandation : 30–60 minutes d'activité modérée</p>
                        <p x-show="errors['activite_objectif_minutes']"
                           x-text="errors['activite_objectif_minutes']"
                           class="text-red-600 text-sm mt-1"></p>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                :disabled="loading['activite_objectif_minutes']"
                                :class="loading['activite_objectif_minutes'] ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-4 py-3 bg-emerald-500 hover:bg-emerald-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-save mr-2"></i>
                            <span x-text="loading['activite_objectif_minutes'] ? '...' : 'Sauver'"></span>
                        </button>
                        <button type="button"
                                @click="resetConfig('activite_objectif_minutes')"
                                class="px-4 py-3 bg-gray-500 hover:bg-gray-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-undo mr-2"></i>
                            Défaut
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== LIMITE GRAISSES SATURÉES ===== -->
        <div class="bg-white rounded-2xl p-6 border border-green-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bacon text-green-600"></i>
                Limite graisses saturées quotidiennes
            </h3>
            <form @submit.prevent="updateUserConfig('lipides_max_g')" class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Grammes par jour</label>
                        <input type="number"
                               step="1"
                               min="10"
                               max="50"
                               x-model="userConfig.lipides_max_g"
                               @input="validateConfig('lipides_max_g')"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300
                                      focus:outline-none focus:ring-2 focus:ring-green-300
                                      transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Recommandation OMS : max 22g/jour pour adultes</p>
                        <p x-show="errors['lipides_max_g']"
                           x-text="errors['lipides_max_g']"
                           class="text-red-600 text-sm mt-1"></p>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                :disabled="loading['lipides_max_g']"
                                :class="loading['lipides_max_g'] ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-4 py-3 bg-green-500 hover:bg-green-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-save mr-2"></i>
                            <span x-text="loading['lipides_max_g'] ? '...' : 'Sauver'"></span>
                        </button>
                        <button type="button"
                                @click="resetConfig('lipides_max_g')"
                                class="px-4 py-3 bg-gray-500 hover:bg-gray-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-undo mr-2"></i>
                            Défaut
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== LIMITE SUCRES RAPIDES ===== -->
        <div class="bg-white rounded-2xl p-6 border border-purple-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-candy-cane text-purple-600"></i>
                Limite sucres rapides quotidiens
            </h3>
            <form @submit.prevent="updateUserConfig('sucres_max_g')" class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Grammes par jour</label>
                        <input type="number"
                               step="1"
                               min="20"
                               max="100"
                               x-model="userConfig.sucres_max_g"
                               @input="validateConfig('sucres_max_g')"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300
                                      focus:outline-none focus:ring-2 focus:ring-purple-300
                                      transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Recommandation OMS : max 50g/jour pour adultes</p>
                        <p x-show="errors['sucres_max_g']"
                           x-text="errors['sucres_max_g']"
                           class="text-red-600 text-sm mt-1"></p>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                :disabled="loading['sucres_max_g']"
                                :class="loading['sucres_max_g'] ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-4 py-3 bg-purple-500 hover:bg-purple-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-save mr-2"></i>
                            <span x-text="loading['sucres_max_g'] ? '...' : 'Sauver'"></span>
                        </button>
                        <button type="button"
                                @click="resetConfig('sucres_max_g')"
                                class="px-4 py-3 bg-gray-500 hover:bg-gray-600
                                       text-white font-semibold rounded-xl shadow-lg
                                       hover:shadow-xl hover:scale-105 transition-all">
                            <i class="fa-solid fa-undo mr-2"></i>
                            Défaut
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== SEUILS IMC ===== -->
        <div class="bg-white rounded-2xl p-6 border border-orange-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-weight-scale text-orange-600"></i>
                Seuils d'Indice de Masse Corporelle (IMC)
            </h3>
            <p class="text-sm text-gray-600 mb-4">
                Ces seuils déterminent vos catégories de poids dans les conseils personnalisés.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Sous-poids -->
                <div class="bg-red-50 rounded-xl p-4">
                    <h4 class="font-semibold text-red-700 mb-2">Sous-poids</h4>
                    <form @submit.prevent="updateUserConfig('imc_seuil_sous_poids')" class="space-y-2">
                        <input type="number"
                               step="0.1"
                               min="15.0"
                               max="20.0"
                               x-model="userConfig.imc_seuil_sous_poids"
                               @input="validateConfig('imc_seuil_sous_poids')"
                               class="w-full px-3 py-2 rounded-lg border border-red-300
                                      focus:outline-none focus:ring-2 focus:ring-red-300
                                      transition-all text-sm"
                               required>
                        <div class="flex gap-1">
                            <button type="submit"
                                    :disabled="loading['imc_seuil_sous_poids']"
                                    class="flex-1 px-2 py-1 bg-red-500 hover:bg-red-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <span x-text="loading['imc_seuil_sous_poids'] ? '...' : 'OK'"></span>
                            </button>
                            <button type="button"
                                    @click="resetConfig('imc_seuil_sous_poids')"
                                    class="px-2 py-1 bg-gray-500 hover:bg-gray-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <i class="fa-solid fa-undo"></i>
                            </button>
                        </div>
                        <p x-show="errors['imc_seuil_sous_poids']"
                           x-text="errors['imc_seuil_sous_poids']"
                           class="text-red-600 text-xs mt-1"></p>
                    </form>
                </div>

                <!-- Normal -->
                <div class="bg-green-50 rounded-xl p-4">
                    <h4 class="font-semibold text-green-700 mb-2">Normal</h4>
                    <form @submit.prevent="updateUserConfig('imc_seuil_normal')" class="space-y-2">
                        <input type="number"
                               step="0.1"
                               min="20.0"
                               max="30.0"
                               x-model="userConfig.imc_seuil_normal"
                               @input="validateConfig('imc_seuil_normal')"
                               class="w-full px-3 py-2 rounded-lg border border-green-300
                                      focus:outline-none focus:ring-2 focus:ring-green-300
                                      transition-all text-sm"
                               required>
                        <div class="flex gap-1">
                            <button type="submit"
                                    :disabled="loading['imc_seuil_normal']"
                                    class="flex-1 px-2 py-1 bg-green-500 hover:bg-green-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <span x-text="loading['imc_seuil_normal'] ? '...' : 'OK'"></span>
                            </button>
                            <button type="button"
                                    @click="resetConfig('imc_seuil_normal')"
                                    class="px-2 py-1 bg-gray-500 hover:bg-gray-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <i class="fa-solid fa-undo"></i>
                            </button>
                        </div>
                        <p x-show="errors['imc_seuil_normal']"
                           x-text="errors['imc_seuil_normal']"
                           class="text-red-600 text-xs mt-1"></p>
                    </form>
                </div>

                <!-- Surpoids -->
                <div class="bg-orange-50 rounded-xl p-4">
                    <h4 class="font-semibold text-orange-700 mb-2">Surpoids</h4>
                    <form @submit.prevent="updateUserConfig('imc_seuil_surpoids')" class="space-y-2">
                        <input type="number"
                               step="0.1"
                               min="25.0"
                               max="35.0"
                               x-model="userConfig.imc_seuil_surpoids"
                               @input="validateConfig('imc_seuil_surpoids')"
                               class="w-full px-3 py-2 rounded-lg border border-orange-300
                                      focus:outline-none focus:ring-2 focus:ring-orange-300
                                      transition-all text-sm"
                               required>
                        <div class="flex gap-1">
                            <button type="submit"
                                    :disabled="loading['imc_seuil_surpoids']"
                                    class="flex-1 px-2 py-1 bg-orange-500 hover:bg-orange-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <span x-text="loading['imc_seuil_surpoids'] ? '...' : 'OK'"></span>
                            </button>
                            <button type="button"
                                    @click="resetConfig('imc_seuil_surpoids')"
                                    class="px-2 py-1 bg-gray-500 hover:bg-gray-600
                                           text-white text-xs font-semibold rounded transition-all">
                                <i class="fa-solid fa-undo"></i>
                            </button>
                        </div>
                        <p x-show="errors['imc_seuil_surpoids']"
                           x-text="errors['imc_seuil_surpoids']"
                           class="text-red-600 text-xs mt-1"></p>
                    </form>
                </div>
            </div>

            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-xs text-blue-700">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    <strong>Note :</strong> IMC < seuil sous-poids = sous-poids,
                    seuil sous-poids ≤ IMC < seuil normal = normal,
                    seuil normal ≤ IMC < seuil surpoids = surpoids,
                    IMC ≥ seuil surpoids = obésité.
                </p>
            </div>
        </div>

        <!-- ===== CONDITIONS MÉDICALES ===== -->
        <div class="bg-white rounded-2xl border p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-heartbeat text-red-600"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-800">Conditions Médicales</h4>
                    <p class="text-sm text-gray-600">Pour des conseils adaptés à votre santé</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Problèmes cardiaques -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Problèmes cardiaques</p>
                        <p class="text-sm text-gray-600">Angine, infarctus, hypertension, etc.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               :checked="userConfig.medical_cardiac == 1"
                               @change="userConfig.medical_cardiac = $event.target.checked ? 1 : 0; updateUserConfig('medical_cardiac')"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>

                <!-- Diabète -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Diabète</p>
                        <p class="text-sm text-gray-600">Type 1, type 2 ou gestationnel</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               :checked="userConfig.medical_diabetes == 1"
                               @change="userConfig.medical_diabetes = $event.target.checked ? 1 : 0; updateUserConfig('medical_diabetes')"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Autres conditions -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-800">Autres conditions médicales</p>
                        <p class="text-sm text-gray-600">Arthrite, insuffisance rénale, etc.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               :checked="userConfig.medical_other == 1"
                               @change="userConfig.medical_other = $event.target.checked ? 1 : 0; updateUserConfig('medical_other')"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-xs text-yellow-700">
                    <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                    <strong>Important :</strong> Ces informations nous aident à adapter les conseils. Elles ne remplacent pas l'avis médical professionnel.
                </p>
            </div>
        </div>

    </div>
</div>
