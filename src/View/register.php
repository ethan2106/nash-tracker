<?php
/**
 * Vue register.php - Formulaire d'inscription utilisateur avec validation Alpine.js temps réel.
 */
$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

$title = 'Inscription - Suivi Nash';

// =======================
// Includes composants
// =======================
include __DIR__ . '/components/alert.php';

// Génération du token CSRF
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf_token = $_SESSION['csrf_token'];

// Préparer l'alerte à partir de $data ou de la session flash
$alert = '';
$alertType = $data['alertType'] ?? null;
$alertMsg = $data['alertMsg'] ?? null;
if (!empty($alertType) && !empty($alertMsg))
{
    $alert = alert($alertType, $alertMsg);
}
if (!empty($_SESSION['flash']))
{
    $alert = alert($_SESSION['flash']['type'], $_SESSION['flash']['msg']);
    unset($_SESSION['flash']);
}

ob_start();
?>

<script src="/js/components/alpine-auth.js?v=<?= time(); ?>"></script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 flex items-center justify-center p-4"
     x-data="authManager()">
    <div class="w-full max-w-lg bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-green-100">

        <!-- Header -->
        <div class="text-center mb-6">
            <div class="text-5xl mb-4">
                <i class="fa-solid fa-user-plus text-green-500"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Inscription</h1>
            <p class="text-gray-600">Créez votre compte pour suivre votre santé</p>
        </div>

        <!-- Alertes -->
        <?php if ($alert)
        { ?>
            <div class="mb-6">
                <?= $alert; ?>
            </div>
        <?php } ?>

        <!-- Formulaire d'inscription -->
        <form method="post" action="?page=register"
              class="space-y-6"
              id="registerForm"
              novalidate
              @submit="submitForm($event)">

            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

            <!-- Champ Pseudo -->
            <div>
                <label for="pseudo" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fa-solid fa-user text-green-400 mr-2"></i>Pseudo
                </label>
                <div class="relative">
                          <input id="pseudo"
                              name="pseudo"
                              type="text"
                              x-model="formData.pseudo"
                              @blur="validatePseudo(); checkPseudoUniqueness()"
                              @input="validatePseudo()"
                              @keydown.enter="focusNextField('pseudo')"
                           placeholder="Votre pseudo (3-20 caractères)"
                           class="w-full pl-4 pr-12 py-3 rounded-xl border bg-white/80 focus:outline-none focus:ring-2 transition-all duration-200 shadow-sm"
                           :class="errors.pseudo ? 'border-red-300 focus:ring-red-300' : 'border-green-100 focus:ring-green-400'"
                           autocomplete="username"
                           minlength="3"
                           maxlength="20"
                           required>
                    <div class="absolute right-3 top-3">
                        <i class="fa-solid text-lg transition-colors"
                           :class="errors.pseudo ? 'fa-times-circle text-red-400' : 'fa-check-circle text-green-400'"
                           x-show="!errors.pseudo && formData.pseudo"
                           x-transition></i>
                                <i class="fa-solid fa-exclamation-triangle text-red-400 text-lg"
                                    x-show="errors.pseudo"
                                    x-transition></i>
                                <!-- Spinner pendant la vérification -->
                                <i class="fa-solid fa-spinner fa-spin text-gray-400 text-lg"
                                    x-show="isCheckingPseudo"
                                    x-cloak
                                    x-transition></i>
                    </div>
                </div>
                <!-- Message d'erreur -->
                     <p id="pseudoError" class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                         x-show="errors.pseudo"
                         x-text="errors.pseudo"
                         x-transition
                         aria-live="polite"></p>
            </div>

            <!-- Champ Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fa-solid fa-envelope text-blue-400 mr-2"></i>Email
                </label>
                <div class="relative">
                          <input id="email"
                              name="email"
                              type="email"
                              x-model="formData.email"
                              @blur="validateEmail(); checkEmailUniqueness()"
                              @input="validateEmail()"
                              @keydown.enter="focusNextField('email')"
                           placeholder="votre.email@exemple.com"
                           class="w-full pl-4 pr-12 py-3 rounded-xl border bg-white/80 focus:outline-none focus:ring-2 transition-all duration-200 shadow-sm"
                           :class="errors.email ? 'border-red-300 focus:ring-red-300' : 'border-blue-100 focus:ring-blue-400'"
                           autocomplete="email"
                           required>
                    <div class="absolute right-3 top-3">
                        <i class="fa-solid text-lg transition-colors"
                           :class="errors.email ? 'fa-times-circle text-red-400' : 'fa-check-circle text-green-400'"
                           x-show="!errors.email && formData.email"
                           x-transition></i>
                                <i class="fa-solid fa-exclamation-triangle text-red-400 text-lg"
                                    x-show="errors.email"
                                    x-transition></i>
                                <!-- Spinner pendant la vérification -->
                                <i class="fa-solid fa-spinner fa-spin text-gray-400 text-lg"
                                    x-show="isCheckingEmail"
                                    x-cloak
                                    x-transition></i>
                    </div>
                </div>
                <!-- Message d'erreur -->
                     <p id="emailError" class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                         x-show="errors.email"
                         x-text="errors.email"
                         x-transition
                         aria-live="polite"></p>
            </div>

            <!-- Champ Mot de passe -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fa-solid fa-lock text-purple-400 mr-2"></i>Mot de passe
                </label>
                <div class="relative">
                    <input id="password"
                           name="password"
                           :type="showPassword ? 'text' : 'password'"
                           x-model="formData.password"
                           @blur="validatePassword()"
                           @input="validatePassword()"
                           @keydown.enter="focusNextField('password')"
                           placeholder="Minimum 8 caractères"
                           class="w-full pl-4 pr-12 py-3 rounded-xl border bg-white/80 focus:outline-none focus:ring-2 transition-all duration-200 shadow-sm"
                           :class="errors.password ? 'border-red-300 focus:ring-red-300' : 'border-purple-100 focus:ring-purple-400'"
                           autocomplete="new-password"
                           minlength="8"
                           required>
                    <!-- Bouton toggle visibilité -->
                    <button type="button"
                            @click="togglePasswordVisibility('password')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid text-lg"
                           :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <!-- Barre de force du mot de passe -->
                <div class="mt-2" x-show="formData.password">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs text-gray-600">Force du mot de passe</span>
                        <span class="text-xs font-semibold"
                              :class="passwordStrength < 30 ? 'text-red-500' : passwordStrength < 60 ? 'text-yellow-500' : passwordStrength < 80 ? 'text-blue-500' : 'text-green-500'"
                              x-text="getPasswordStrengthText()"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300"
                             :class="getPasswordStrengthColor()"
                             :style="`width: ${passwordStrength}%`"></div>
                    </div>
                </div>

                <!-- Message d'erreur -->
                <p class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                   x-show="errors.password"
                   x-text="errors.password"
                   x-transition></p>
            </div>

            <!-- Champ Confirmation mot de passe -->
            <div>
                <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fa-solid fa-lock text-purple-400 mr-2"></i>Confirmer le mot de passe
                </label>
                <div class="relative">
                    <input id="password_confirm"
                           name="password_confirm"
                           :type="showPasswordConfirm ? 'text' : 'password'"
                           x-model="formData.password_confirm"
                           @blur="validatePasswordConfirm()"
                           @input="validatePasswordConfirm()"
                           @keydown.enter="handleKeydown($event, 'confirm')"
                           placeholder="Répétez votre mot de passe"
                           class="w-full pl-4 pr-12 py-3 rounded-xl border bg-white/80 focus:outline-none focus:ring-2 transition-all duration-200 shadow-sm"
                           :class="errors.password_confirm ? 'border-red-300 focus:ring-red-300' : 'border-purple-100 focus:ring-purple-400'"
                           autocomplete="new-password"
                           minlength="8"
                           required>
                    <!-- Bouton toggle visibilité -->
                    <button type="button"
                            @click="togglePasswordVisibility('confirm')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid text-lg"
                           :class="showPasswordConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                    <!-- Indicateur de correspondance -->
                    <div class="absolute right-10 top-3">
                        <i class="fa-solid text-lg transition-colors"
                           :class="!errors.password_confirm && formData.password_confirm && formData.password === formData.password_confirm ? 'fa-check-circle text-green-400' : 'fa-times-circle text-red-400'"
                           x-show="formData.password_confirm"
                           x-transition></i>
                    </div>
                </div>
                <!-- Message d'erreur -->
                <p class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                   x-show="errors.password_confirm"
                   x-text="errors.password_confirm"
                   x-transition></p>
            </div>

            <!-- Bouton d'inscription -->
            <button type="submit"
                    :disabled="isSubmitting || !formData.pseudo || !formData.email || !formData.password || !formData.password_confirm"
                    class="w-full bg-gradient-to-r from-success-500 to-primary-500 hover:from-success-600 hover:to-primary-600 disabled:from-gray-400 disabled:to-gray-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 disabled:transform-none disabled:cursor-not-allowed shadow-lg">
                <span x-show="!isSubmitting">
                    <i class="fa-solid fa-user-plus mr-2"></i>S'inscrire
                </span>
                <span x-show="isSubmitting" class="flex items-center justify-center">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>Inscription...
                </span>
            </button>
        </form>

        <!-- Liens -->
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <a href="?page=login"
               class="text-blue-600 hover:text-blue-800 transition-colors flex items-center text-sm">
                <i class="fa-solid fa-arrow-right mr-1"></i>Déjà inscrit ? Se connecter
            </a>
            <a href="?page=home"
               class="text-green-600 hover:text-green-800 transition-colors flex items-center text-sm">
                <i class="fa-solid fa-house mr-1"></i>Accueil
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
