<?php

/**
 * Helpers pour les vues - Fonctions utilitaires d'affichage.
 */

/**
 * Échappe et affiche une valeur HTML.
 */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche une valeur avec échappement automatique.
 */
function h($value)
{
    echo e($value);
}

/**
 * Vérifie si une valeur est définie et non vide.
 */
function hasValue($value)
{
    return isset($value) && !empty($value);
}

/**
 * Affiche une valeur par défaut si vide.
 */
function defaultValue($value, $default = '')
{
    return hasValue($value) ? $value : $default;
}

/**
 * Formate une date.
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (!$date)
    {
        return '';
    }

    return date($format, strtotime($date));
}

/**
 * Formate un nombre avec séparateur de milliers.
 */
function formatNumber($number, $decimals = 0)
{
    if (!is_numeric($number))
    {
        return $number;
    }

    return number_format($number, $decimals, ',', ' ');
}

/**
 * Calcule le pourcentage de progression (0-100).
 */
function calculateProgress($current, $target)
{
    if (!is_numeric($current) || !is_numeric($target) || $target <= 0)
    {
        return 0;
    }

    return min(100, ($current / $target) * 100);
}

/**
 * Détermine la catégorie IMC.
 */
function getIMCCategory($imc)
{
    if ($imc < 18.5)
    {
        return 'Sous-poids';
    }
    if ($imc < 25)
    {
        return 'Normal';
    }
    if ($imc < 30)
    {
        return 'Surpoids';
    }

    return 'Obésité';
}

/**
 * Calcule le score IMC (0-25 points) avec décroissance progressive et plancher à 1 au-delà de 35.
 */
function getIMCScorePoints($imc)
{
    $imc = (float)$imc;
    if ($imc <= 25.0)
    {
        return 25;
    }
    if ($imc <= 35.0)
    {
        $points = (int)round(25 - (($imc - 25.0) * 1.5));

        return max(1, $points);
    }

    return 1;
}

/**
 * Message conseil IMC, ton rassurant et non alarmiste.
 */
function getIMCAdvice($imc)
{
    $imc = (float)$imc;
    if ($imc < 18.5)
    {
        return 'Pensez à en parler avec un professionnel si la perte de poids est involontaire.';
    }
    if ($imc < 25)
    {
        return 'Objectif: maintenir un IMC entre 18,5 et 25 avec une hygiène de vie équilibrée.';
    }
    if ($imc <= 35)
    {
        return 'De petits ajustements (alimentation, activité) peuvent aider à revenir vers la zone 18,5–25.';
    }

    return "Si votre IMC est supérieur à 35, n'hésitez pas à consulter un professionnel de santé pour un accompagnement adapté.";
}

/**
 * Formate une activité récente pour affichage.
 */
function formatActivityDate($dateString)
{
    if (!$dateString)
    {
        return '';
    }

    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);

    if ($diff->days === 0)
    {
        return 'Aujourd\'hui ' . $date->format('H:i');
    } elseif ($diff->days === 1)
    {
        return 'Hier ' . $date->format('H:i');
    } else
    {
        return $date->format('d/m/Y H:i');
    }
}

/**
 * Formate un datetime en temps relatif (Il y a X min, Aujourd'hui HH:MM, etc.)
 * Version optimisée pour la timeline.
 */
function formatRelativeTime($datetime)
{
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 3600)
    {
        $minutes = round($diff / 60);

        return "Il y a $minutes min";
    } elseif ($diff < 86400)
    {
        $hours = round($diff / 3600);

        return "Il y a $hours h";
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', $now))
    {
        return "Aujourd'hui " . date('H:i', $timestamp);
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', $now - 86400))
    {
        return 'Hier ' . date('H:i', $timestamp);
    } else
    {
        return date('d/m à H:i', $timestamp);
    }
}

/**
 * Récupère la couleur hex basée sur le score de santé (0-100).
 */
function getScoreColor($score)
{
    if ($score >= 80)
    {
        return '#10b981'; // green-500
    } elseif ($score >= 60)
    {
        return '#3b82f6'; // blue-500
    } elseif ($score >= 40)
    {
        return '#f97316'; // orange-500
    } else
    {
        return '#ef4444'; // red-500
    }
}

/**
 * Récupère le label basé sur le score de santé (0-100).
 */
function getScoreLabel($score)
{
    if ($score >= 80)
    {
        return 'Excellent';
    } elseif ($score >= 60)
    {
        return 'Bon';
    } elseif ($score >= 40)
    {
        return 'Moyen';
    } else
    {
        return 'À améliorer';
    }
}

/**
 * Couleur Tailwind de la note (pour classes utilitaires)
 * Renvoie l'alias de couleur: green | blue | orange | red.
 */
function getScoreTailwindColor($score)
{
    if ($score >= 80)
    {
        return 'green';
    }
    if ($score >= 60)
    {
        return 'blue';
    }
    if ($score >= 40)
    {
        return 'orange';
    }

    return 'red';
}
