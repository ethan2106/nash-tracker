<?php

namespace App\Service;

/**
 * CsrfService - Gère la protection CSRF
 * Responsabilités :
 * - Génération des tokens CSRF
 * - Validation des tokens CSRF
 * - Gestion du stockage en session
 */
class CsrfService
{
    /**
     * Génère et retourne un token CSRF
     * Le stocke en session s'il n'existe pas
     */
    public function getToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
            } catch (\Exception $e) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(24));
            }
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valide un token CSRF fourni
     */
    public function validateToken(string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return !empty($token) && hash_equals($sessionToken, $token);
    }

    /**
     * Valide un token CSRF depuis POST data
     * Lance une exception si invalide
     */
    public function validatePostToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateToken($token)) {
            throw new \InvalidArgumentException('Token CSRF invalide.');
        }
    }

    /**
     * Régénère le token CSRF (utile après certaines actions)
     */
    public function regenerateToken(): string
    {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        } catch (\Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(24));
        }

        return $_SESSION['csrf_token'];
    }
}