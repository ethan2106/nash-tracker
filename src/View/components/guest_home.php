<?php
/**
 * Component: Page d'accueil visiteurs (Landing page moderne).
 */
$pageTitle = $viewData['pageTitle'];
$pageSubtitle = $viewData['pageSubtitle'];
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50">
    
    <!-- HERO Section -->
    <div class="relative overflow-hidden">
        <!-- Decorative background -->
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-pink-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full shadow-lg mb-8">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-sm font-semibold text-slate-700">100% Gratuit • Aucune carte requise</span>
            </div>

            <!-- Title -->
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold leading-tight mb-6">
                <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Prenez le contrôle
                </span>
                <br />
                <span class="text-slate-900">de votre santé hépatique</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-xl sm:text-2xl text-slate-600 max-w-3xl mx-auto mb-4 leading-relaxed">
                Votre compagnon quotidien pour gérer la <strong class="text-purple-600">stéatose hépatique (NASH/NAFLD)</strong>.
                <br />Nutrition, activité physique et suivi de vos objectifs.
            </p>

            <!-- Disclaimer -->
            <div class="inline-flex items-start gap-3 px-6 py-4 bg-amber-50 border-l-4 border-amber-400 rounded-xl mb-12 max-w-3xl">
                <i class="fa-solid fa-circle-info text-amber-600 text-xl mt-1" aria-hidden="true"></i>
                <p class="text-sm text-amber-800 text-left">
                    <strong>Information importante :</strong> Ce service est un outil de suivi personnel de votre santé et nutrition. 
                    Il ne remplace en aucun cas un avis ou un suivi médical par un professionnel de santé qualifié.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-12">
                <a href="?page=register"
                   class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl hover:shadow-purple-500/50 transform hover:scale-105 transition-all duration-300 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        <i class="fa-solid fa-rocket" aria-hidden="true"></i>
                        Commencer gratuitement
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
                
                <a href="?page=login"
                   class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-2xl shadow-lg hover:shadow-xl hover:border-purple-400 hover:text-purple-600 transition-all duration-300">
                    <i class="fa-solid fa-user mr-2" aria-hidden="true"></i>
                    Connexion
                </a>
            </div>

            <!-- Social Proof / Stats -->
            <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto pt-8 border-t border-slate-200">
                <div>
                    <p class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">100%</p>
                    <p class="text-sm text-slate-600 mt-1">Gratuit</p>
                </div>
                <div>
                    <p class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">24/7</p>
                    <p class="text-sm text-slate-600 mt-1">Disponible</p>
                </div>
                <div>
                    <p class="text-3xl font-bold bg-gradient-to-r from-pink-600 to-blue-600 bg-clip-text text-transparent">0€</p>
                    <p class="text-sm text-slate-600 mt-1">Aucun frais</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-extrabold text-slate-900 mb-4">
                Tout ce dont vous avez besoin pour <span class="text-purple-600">réussir</span>
            </h2>
            <p class="text-xl text-slate-600">Des outils simples et puissants pour votre santé au quotidien</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Feature 1 -->
            <div class="group relative bg-white rounded-3xl p-8 shadow-xl border border-slate-100 hover:border-blue-300 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100 to-blue-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Suivi IMC & Objectifs</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Calculez votre IMC, définissez vos objectifs caloriques personnalisés et suivez votre progression avec des graphiques intuitifs.
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="group relative bg-white rounded-3xl p-8 shadow-xl border border-slate-100 hover:border-green-300 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-100 to-green-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Journal Nutritionnel</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Enregistrez vos repas, scannez des codes-barres, et analysez vos macronutriments (calories, protéines, glucides, lipides).
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="group relative bg-white rounded-3xl p-8 shadow-xl border border-slate-100 hover:border-purple-300 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-100 to-purple-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-person-running" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Activité Physique</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Enregistrez vos exercices, calculez les calories brûlées et visualisez votre niveau d'activité avec le système MET.
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="group relative bg-white rounded-3xl p-8 shadow-xl border border-slate-100 hover:border-orange-300 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-100 to-orange-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-shield-heart" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Alertes NAFLD</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Recevez des alertes personnalisées basées sur votre IMC et vos habitudes alimentaires pour prévenir la stéatose hépatique.
                    </p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="group relative bg-white rounded-3xl p-8 shadow-xl border border-slate-100 hover:border-indigo-300 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-bl-full opacity-50"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Rapports Détaillés</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Générez des rapports hebdomadaires et mensuels de votre évolution nutritionnelle et de santé globale.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- CTA Final -->
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-3xl p-12 shadow-2xl overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white opacity-10 rounded-full -ml-32 -mb-32"></div>
            
            <div class="relative text-center text-white">
                <h2 class="text-4xl font-extrabold mb-4">Prêt à transformer votre santé ?</h2>
                <p class="text-xl mb-8 text-blue-100">Rejoignez-nous gratuitement dès aujourd'hui et commencez votre parcours vers une meilleure santé hépatique.</p>
                
                <a href="?page=register"
                   class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-purple-600 bg-white rounded-2xl shadow-2xl hover:shadow-white/50 transform hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-sparkles mr-2"></i>
                    Créer mon compte gratuit
                    <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
                
                <p class="mt-6 text-sm text-blue-100">
                    <i class="fa-solid fa-lock mr-2"></i>
                    Vos données sont sécurisées et ne seront jamais partagées
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-slate-200">
        <div class="text-center text-slate-600">
            <p class="text-sm flex items-center justify-center gap-2 flex-wrap">
                <i class="fa-solid fa-heart text-red-500"></i>
                <span>Fait avec passion pour votre santé hépatique</span>
                <span class="hidden sm:inline">•</span>
                <span class="text-purple-600 font-semibold">Créé par une personne atteinte de NASH</span>
            </p>
        </div>
    </div>

</div>
