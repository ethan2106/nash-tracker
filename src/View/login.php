<?php
/**
 * Vue login.php - Formulaire de connexion utilisateur avec validation Alpine.js temps réel.
 */
$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

$title = 'Connexion - Suivi Nash';

// =======================
// Includes composants
// =======================
include __DIR__ . '/components/alert.php';

// Préparer l'alerte à partir de GET ou de la session flash
$alert = '';
if (!empty($_GET['alertType']) && !empty($_GET['alertMsg']))
{
    $alert = alert($_GET['alertType'], $_GET['alertMsg']);
}
if (!empty($_SESSION['flash']))
{
    $alert = alert($_SESSION['flash']['type'], $_SESSION['flash']['msg']);
    unset($_SESSION['flash']);
}

// =======================
// Génération des tokens CSRF
// =======================
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf_token = $_SESSION['csrf_token'];

ob_start();
?>

<script src="/js/components/alpine-auth.js?v=<?= time(); ?>"></script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 flex items-center justify-center p-4"
     x-data="authManager()">
    <div class="w-full max-w-lg bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-blue-100">

        <!-- Header -->
        <div class="text-center mb-6">
            <div class="text-5xl mb-4">
                <i class="fa-solid fa-right-to-bracket text-blue-500"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Connexion</h1>
            <p class="text-gray-600">Connectez-vous à votre compte</p>
        </div>

        <!-- Alertes -->
        <?php if ($alert)
        { ?>
            <div class="mb-6">
                <?= $alert; ?>
            </div>
        <?php } ?>

        <!-- Formulaire de connexion -->
        <form method="post" action="?page=login"
              class="space-y-6"
              autocomplete="on"
              name="login_form"
              @submit="submitForm($event)">

            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

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
                           @blur="validateEmail()"
                           @input="validateEmail()"
                           @keydown.enter="handleKeydown($event, 'email')"
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
                    </div>
                </div>
                <!-- Message d'erreur -->
                <p class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                   x-show="errors.email"
                   x-text="errors.email"
                   x-transition></p>
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
                           @keydown.enter="handleKeydown($event, 'password')"
                           placeholder="Votre mot de passe"
                           class="w-full pl-4 pr-12 py-3 rounded-xl border bg-white/80 focus:outline-none focus:ring-2 transition-all duration-200 shadow-sm"
                           :class="errors.password ? 'border-red-300 focus:ring-red-300' : 'border-purple-100 focus:ring-purple-400'"
                           autocomplete="current-password"
                           required>
                    <!-- Bouton toggle visibilité -->
                    <button type="button"
                            @click="togglePasswordVisibility('password')"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid text-lg"
                           :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                <!-- Message d'erreur -->
                <p class="text-red-500 text-sm mt-1 transition-opacity duration-200"
                   x-show="errors.password"
                   x-text="errors.password"
                   x-transition></p>
            </div>

            <!-- Se souvenir de moi -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox"
                           name="remember"
                           value="1"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <span class="ml-2 text-sm text-gray-700">Se souvenir de moi</span>
                </label>

                <!-- Lien mot de passe oublié (futur) -->
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                    Mot de passe oublié ?
                </a>
            </div>

            <!-- Bouton de connexion -->
            <button type="submit"
                    :disabled="isSubmitting || !formData.email || !formData.password"
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 disabled:transform-none disabled:cursor-not-allowed shadow-lg">
                <span x-show="!isSubmitting">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>Se connecter
                </span>
                <span x-show="isSubmitting" class="flex items-center justify-center">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>Connexion...
                </span>
            </button>
        </form>

        <!-- Liens -->
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <a href="?page=register"
               class="text-blue-600 hover:text-blue-800 transition-colors flex items-center text-sm">
                <i class="fa-solid fa-user-plus mr-1"></i>Créer un compte
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
?>
