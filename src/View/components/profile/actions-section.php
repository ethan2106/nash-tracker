<?php
/**
 * Section Actions - Actions rapides et conseils personnalisés.
 * @param array $data - Contient personalizedAdvice
 */
?>
<div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50">
    <div class="text-center mb-8">
        <h3 class="text-3xl font-bold text-gray-800 mb-4">Actions et Conseils Personnalisés</h3>
        <p class="text-lg text-gray-600">Continuez votre progression vers une meilleure santé hépatique</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="?page=imc" class="group bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/50 hover:shadow-xl transition-all hover:scale-105">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-scale-balanced text-white text-2xl"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Mettre à jour IMC</h4>
                <p class="text-gray-600">Recalculez vos objectifs nutritionnels</p>
            </div>
        </a>
        
        <a href="?page=medicaments" class="group bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/50 hover:shadow-xl transition-all hover:scale-105">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-pills text-white text-2xl"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Gérer Médicaments</h4>
                <p class="text-gray-600">Suivez vos traitements quotidiens</p>
            </div>
        </a>
    </div>
    
    <!-- Conseils NAFLD -->
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-2xl p-6 border border-yellow-200">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-lightbulb text-white text-xl"></i>
            </div>
            <div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Conseils NAFLD Personnalisés</h4>
                <ul class="space-y-2">
                    <?php
                    $advice = $data['personalizedAdvice'] ?? [];
if (empty($advice))
{
    // Fallback si pas de conseils personnalisés
    ?>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-info-circle text-blue-600 mt-1 flex-shrink-0"></i>
                            <span class="text-gray-700">Configurez votre profil pour recevoir des conseils personnalisés.</span>
                        </li>
                        <?php
} else
{
    foreach ($advice as $tip)
    {
        $iconClass = 'fa-solid ' . ($tip['icon'] ?? 'fa-info-circle');
        $colorClass = 'text-' . ($tip['color'] ?? 'blue') . '-600';
        ?>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-green-600 mt-1 flex-shrink-0"></i>
                                <span class="text-gray-700"><?= htmlspecialchars($tip['text']); ?></span>
                            </li>
                            <?php
    }
}
?>
                </ul>
            </div>
        </div>
    </div>
</div>
