<?php
/**
 * Composant Header Hero - Avatar, infos utilisateur et stats rapides.
 * @param array $user - Données utilisateur
 * @param array $stats - Statistiques du jour
 * @param array $data - Données complètes (pour userConfig)
 */

// Inclure le helper pour le logout sécurisé
require_once __DIR__ . '/../../components/csrf_logout_link.php';
?>
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
        <!-- Avatar et Infos Principales -->
        <div class="flex flex-col items-center lg:items-start text-center lg:text-left">
            <div class="relative mb-6 group">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 shadow-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-3xl">
                    <i class="fa-solid fa-user text-white text-5xl transition-transform group-hover:scale-110" aria-hidden="true"></i>
                </div>
                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full border-4 border-white shadow-lg flex items-center justify-center animate-pulse">
                    <i class="fa-solid fa-heart-pulse text-white text-sm" aria-hidden="true"></i>
                </div>
                <div class="absolute -top-2 -left-2 px-3 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full border-2 border-white shadow-lg">
                    <span class="text-white text-xs font-bold">VIP</span>
                </div>
            </div>
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-2">
                Bonjour, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    <?= e($user['pseudo'] ?? 'Utilisateur'); ?>
                </span>
            </h1>
            <p class="text-lg text-gray-600 mb-4">Votre tableau de bord santé personnalisé</p>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-50 to-blue-100 rounded-full hover:from-blue-100 hover:to-blue-200 transition-all duration-300 cursor-default shadow-sm hover:shadow-md border border-blue-200">
                    <i class="fa-solid fa-envelope text-blue-600" aria-hidden="true"></i>
                    <span class="text-blue-700 font-medium"><?= e($user['email'] ?? ''); ?></span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-50 to-emerald-100 rounded-full hover:from-green-100 hover:to-emerald-200 transition-all duration-300 cursor-default shadow-sm hover:shadow-md border border-green-200">
                    <i class="fa-solid fa-calendar-check text-green-600" aria-hidden="true"></i>
                    <span class="text-green-700 font-medium">Membre depuis <?php
                        $dateInscription = $user['date_inscription'] ?? null;
if ($dateInscription)
{
    $date = new DateTime($dateInscription);
    $moisFr = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    echo ucfirst($moisFr[(int)$date->format('n') - 1] . ' ' . $date->format('Y'));
} else
{
    echo 'N/A';
}
?></span>
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="flex items-center gap-4 mt-6 lg:mt-0">
            <a href="?page=settings"
               class="flex items-center gap-2 px-4 py-2 bg-white/80 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl shadow-md hover:shadow-lg transition-all hover:scale-105 border border-gray-200">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                Paramètres
            </a>
            <?php echo csrf_logout_link('Déconnexion', 'flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all hover:scale-105'); ?>
        </div>
    </div>
    
    <!-- Stats Rapides -->
    <?php if ($stats)
    { ?>
    <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
        <?php
        $imc = $stats['imc'];
        include __DIR__ . '/cards/imc-card.php';
        include __DIR__ . '/cards/calories-card.php';
        include __DIR__ . '/cards/objectifs-card.php';
        ?>
    </div>
    <?php } ?>
</div>
