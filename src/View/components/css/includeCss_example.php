<?php

/**
 * Exemple d'utilisation directe de la fonction includeCss()
 * Cette fonction peut être utilisée partout dans l'application.
 */

// Inclure les styles directement (sans passer par $pageCss)
includeCss(['dashboard.css', 'forms.css']);

// Ou inclure un seul style
includeCss(['animations.css']);
