<?php

/**
 * UI Fallbacks Helper - Messages d'erreur user-friendly et états de chargement.
 *
 * Fonctions helper pour afficher des états d'erreur, de chargement, et de vide
 * de manière cohérente dans toute l'application.
 */

/**
 * Affiche un état de chargement stylisé.
 *
 * @param string $message Message personnalisé (optionnel)
 * @param string $size Taille: 'sm', 'md', 'lg' (défaut: 'md')
 * @return string HTML du loader
 */
function renderLoader(string $message = 'Chargement...', string $size = 'md'): string
{
    $sizes = [
        'sm' => 'w-8 h-8 text-xl',
        'md' => 'w-12 h-12 text-2xl',
        'lg' => 'w-16 h-16 text-3xl',
    ];

    $iconSize = $sizes[$size] ?? $sizes['md'];

    return <<<HTML
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <div class="{$iconSize} text-blue-500 mb-4">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        <p class="text-gray-600 font-medium">{$message}</p>
    </div>
HTML;
}

/**
 * Affiche un message d'erreur user-friendly.
 *
 * @param string $message Message d'erreur
 * @param string $type Type: 'error', 'warning', 'info' (défaut: 'error')
 * @param bool $dismissible Peut être fermé par l'utilisateur
 * @return string HTML du message
 */
function renderError(string $message, string $type = 'error', bool $dismissible = true): string
{
    $styles = [
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => 'fa-circle-exclamation',
            'iconColor' => 'text-red-600',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' => 'fa-triangle-exclamation',
            'iconColor' => 'text-yellow-600',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'fa-circle-info',
            'iconColor' => 'text-blue-600',
        ],
    ];

    $style = $styles[$type] ?? $styles['error'];
    $dismissBtn = $dismissible ? '<button onclick="this.parentElement.remove()" class="ml-auto text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>' : '';

    return <<<HTML
    <div class="p-4 rounded-xl border {$style['bg']} {$style['border']} mb-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid {$style['icon']} {$style['iconColor']} text-lg mt-0.5"></i>
            <div class="flex-1">
                <p class="{$style['text']} font-medium">{$message}</p>
            </div>
            {$dismissBtn}
        </div>
    </div>
HTML;
}

/**
 * Affiche un état vide (pas de données).
 *
 * @param string $message Message principal
 * @param string $description Description optionnelle
 * @param string|null $actionLabel Label du bouton d'action (optionnel)
 * @param string|null $actionUrl URL du bouton d'action
 * @return string HTML de l'état vide
 */
function renderEmpty(string $message, string $description = '', ?string $actionLabel = null, ?string $actionUrl = null): string
{
    $descHtml = $description ? "<p class='text-gray-500 mb-4'>{$description}</p>" : '';
    $actionHtml = '';

    if ($actionLabel && $actionUrl)
    {
        $actionHtml = <<<HTML
        <a href="{$actionUrl}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all">
            <i class="fa-solid fa-plus"></i>
            {$actionLabel}
        </a>
HTML;
    }

    return <<<HTML
    <div class="text-center py-12">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
            <i class="fa-solid fa-inbox text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">{$message}</h3>
        {$descHtml}
        {$actionHtml}
    </div>
HTML;
}

/**
 * Affiche un message de succès avec animation.
 *
 * @param string $message Message de succès
 * @param int $autoHideSeconds Secondes avant masquage auto (0 = pas de masquage auto)
 * @return string HTML du message
 */
function renderSuccess(string $message, int $autoHideSeconds = 5): string
{
    $autoHide = $autoHideSeconds > 0 ? "setTimeout(() => this.remove(), {$autoHideSeconds}000)" : '';

    return <<<HTML
    <div class="p-4 rounded-xl border bg-green-50 border-green-200 mb-4 animate-fade-in" 
         x-data 
         x-init="{$autoHide}">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-green-600 text-lg mt-0.5"></i>
            <div class="flex-1">
                <p class="text-green-800 font-medium">{$message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
HTML;
}

/**
 * Wrapper pour try/catch avec affichage d'erreur user-friendly.
 *
 * @param callable $callback Fonction à exécuter
 * @param string $errorMessage Message d'erreur par défaut
 * @return mixed Résultat de la callback ou false en cas d'erreur
 */
function tryCatchUI(callable $callback, string $errorMessage = 'Une erreur est survenue')
{
    try
    {
        return $callback();
    } catch (Exception $e)
    {
        // Log l'erreur pour debug
        error_log('UI Error: ' . $e->getMessage());

        // Affiche un message user-friendly
        echo renderError($errorMessage, 'error');

        return false;
    }
}

/**
 * Vérifie si des données sont disponibles et affiche un fallback si vide.
 *
 * @param mixed $data Données à vérifier
 * @param string $emptyMessage Message si vide
 * @param string $emptyDescription Description optionnelle
 * @return bool True si données disponibles, false sinon
 */
function checkDataOrShowEmpty($data, string $emptyMessage, string $emptyDescription = ''): bool
{
    if (empty($data))
    {
        echo renderEmpty($emptyMessage, $emptyDescription);

        return false;
    }

    return true;
}

/**
 * Affiche un skeleton loader pour le chargement de contenu.
 *
 * @param string $type Type: 'card', 'list', 'table' (défaut: 'card')
 * @param int $count Nombre d'éléments à afficher
 * @return string HTML du skeleton
 */
function renderSkeleton(string $type = 'card', int $count = 3): string
{
    $html = '<div class="animate-pulse space-y-4">';

    for ($i = 0; $i < $count; $i++)
    {
        switch ($type)
        {
            case 'card':
                $html .= <<<'HTML'
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                </div>
HTML;

                break;

            case 'list':
                $html .= <<<'HTML'
                <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-gray-200">
                    <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                    <div class="flex-1">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
HTML;

                break;

            case 'table':
                $html .= '<div class="h-12 bg-gray-200 rounded mb-2"></div>';

                break;
        }
    }

    $html .= '</div>';

    return $html;
}
