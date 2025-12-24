<?php

/**
 * Génère un champ de formulaire standard avec étiquette et icône.
 *
 * Usage : include 'form-input.php'; echo form_input('email', 'Email', '', 'email', false, $icon_email, 'email', 'email-id', 'custom-class');
 *
 * @param string $name Nom du champ (attribut name)
 * @param string $label Texte de l'étiquette
 * @param string $value Valeur par défaut du champ
 * @param string $type Type d'input (text, email, password, etc.)
 * @param bool $required Le champ est-il requis ?
 * @param string $icon Code HTML pour l'icône (fontawesome ou autre)
 * @param string $autocomplete Valeur de l'attribut autocomplete
 * @param string $id ID du champ (sinon utilise le name)
 * @param string $class Classes CSS supplémentaires ou de remplacement pour l'input
 * @return string Le code HTML complet du champ de formulaire
 */
function form_input($name, $label, $value = '', $type = 'text', $required = false, $icon = '', $autocomplete = '', $id = '', $class = '')
{
    $iconHtml = $icon ? $icon . ' ' : '';
    $autocompleteAttr = $autocomplete ? ' autocomplete="' . $autocomplete . '"' : '';
    $idAttr = $id ? ' id="' . $id . '"' : '';

    // Si $class est fourni, il est utilisé. Sinon, on utilise les classes par défaut (focus:ring-blue-300).
    // Note: Pour écraser le focus, l'utilisateur doit fournir toutes les classes.
    $inputClass = $class ?: 'w-full p-3 rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-300 transition shadow-sm';

    // Le code PHP est maintenant corrigé pour supporter le neuvième paramètre $class
    return '<div class="mb-4">
        <label for="' . ($id ?: $name) . '" class="block mb-1 font-semibold text-gray-700">' . $iconHtml . htmlspecialchars($label) . '</label>
        <input type="' . $type . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" class="' . $inputClass . '"' . $idAttr . $autocompleteAttr . ' ' . ($required ? 'required' : '') . '>
    </div>';
}
