<?php

// src/Config/session.php
if (session_status() !== PHP_SESSION_ACTIVE)
{
    // Déterminer dynamiquement le domaine et si la connexion est HTTPS
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Remove port if present (e.g. localhost:8080) because cookie domain must not contain port
    $host = preg_replace('/:\d+$/', '', $host);
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Choisir un SameSite compatible localement (None nécessite secure=true dans les navigateurs modernes)
    $sameSite = $isSecure ? 'None' : 'Lax';

    // Use options array (PHP >= 7.3) to include samesite
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $host ?: null,
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => $sameSite,
    ]);

    session_start();

    // Ensure a CSRF token exists for forms/layouts
    if (empty($_SESSION['csrf_token']))
    {
        try
        {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        } catch (Exception $e)
        {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(24));
        }
    }
}

function destroy_session()
{
    // Vide la session
    $_SESSION = [];
    session_unset();
    session_destroy();

    // Supprime le cookie de session
    if (ini_get('session.use_cookies'))
    {
        $params = session_get_cookie_params();
        // Use options array form to preserve samesite if available
        $cookieOptions = [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ];

        setcookie(session_name(), '', $cookieOptions);
    }
}
