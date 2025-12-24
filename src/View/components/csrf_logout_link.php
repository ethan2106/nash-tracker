<?php

/**
 * Helper pour générer un lien de déconnexion sécurisé avec CSRF.
 * Génère un petit formulaire POST pour le logout afin de protéger l'action avec un jeton CSRF.
 *
 * @param string $label Texte du bouton de déconnexion.
 * @param string $class Classes CSS Tailwind à appliquer au bouton (ex: 'text-red-500 hover:text-red-700').
 * @return string Le code HTML complet du formulaire de déconnexion.
 */
function csrf_logout_link($label = 'Déconnexion', $class = '')
{
    // Récupère le jeton CSRF de la session (doit être généré par le contrôleur/layout principal)
    $token = $_SESSION['csrf_token'] ?? '';

    // Icône FontAwesome
    $icon = '<i class="fa-solid fa-right-from-bracket mr-2"></i>';

    // IMPORTANT : L'action doit pointer vers un contrôleur de déconnexion dédié (?page=logout)
    return '<form method="POST" action="?page=logout" style="display: inline;">
        <input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">
        <button type="submit" class="' . htmlspecialchars($class) . '">' . $icon . htmlspecialchars($label) . '</button>
    </form>';
}
