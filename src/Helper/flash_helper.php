<?php

/**
 * Helper pour les notifications flash
 * Utilise le système de notifications global Alpine.js.
 */

/**
 * Définit un message flash dans la session.
 *
 * @param string $message Le message à afficher
 * @param string $type Type de notification: 'success', 'error', 'warning', 'info'
 */
function setFlashMessage($message, $type = 'info')
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

/**
 * Raccourcis pour les types courants.
 */
function flashSuccess($message)
{
    setFlashMessage($message, 'success');
}

function flashError($message)
{
    setFlashMessage($message, 'error');
}

function flashWarning($message)
{
    setFlashMessage($message, 'warning');
}

function flashInfo($message)
{
    setFlashMessage($message, 'info');
}

/**
 * Récupère et supprime le message flash.
 *
 * @return array|null ['message' => string, 'type' => string] ou null
 */
function getFlashMessage()
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    if (isset($_SESSION['flash']))
    {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        // Normaliser le format
        if (is_array($flash))
        {
            return [
                'message' => $flash['msg'] ?? $flash['message'] ?? '',
                'type' => $flash['type'] ?? 'info',
            ];
        }

        return [
            'message' => (string)$flash,
            'type' => 'info',
        ];
    }

    return null;
}

/**
 * Vérifie si un message flash existe.
 *
 * @return bool
 */
function hasFlashMessage()
{
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    return isset($_SESSION['flash']) && !empty($_SESSION['flash']);
}
