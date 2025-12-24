<?php

/**
 * Exemple d'utilisation directe de la fonction includeJs()
 * Cette fonction peut être utilisée partout dans l'application.
 */

// Inclure les scripts directement (sans passer par $pageJs)
includeJs(['example.js', 'charts.js']);

// Ou inclure un seul script
includeJs(['charts.js']);
