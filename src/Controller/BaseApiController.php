<?php

namespace App\Controller;

use App\Helper\JsonResponseTrait;

/**
 * BaseApiController - Classe de base pour les contrôleurs.
 * Fournit des méthodes communes : authentification, CSRF, réponses JSON.
 */
abstract class BaseApiController
{
    use JsonResponseTrait;

    /**
     * Vérifie que l'utilisateur est authentifié.
     * Retourne l'ID utilisateur ou null si non connecté.
     */
    protected function getUserId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Vérifie que l'utilisateur est authentifié.
     * Retourne les données utilisateur ou null.
     */
    protected function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Exige une authentification. Redirige vers login si non connecté.
     * Retourne l'ID utilisateur.
     */
    protected function requireAuth(): int
    {
        $userId = $this->getUserId();
        if ($userId === null)
        {
            header('Location: ?page=login');
            exit;
        }

        return $userId;
    }

    /**
     * Exige une authentification pour les requêtes AJAX.
     * Retourne une erreur JSON 401 si non connecté.
     */
    protected function requireAuthJson(): int
    {
        $userId = $this->getUserId();
        if ($userId === null)
        {
            $this->jsonError(['error' => 'Non authentifié'], 401);
        }

        return $userId;
    }

    /**
     * Vérifie le token CSRF.
     * Retourne true si valide, false sinon.
     */
    protected function validateCsrf(?string $token = null): bool
    {
        $token = $token ?? $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken))
        {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Exige un token CSRF valide. Retourne une erreur JSON si invalide.
     */
    protected function requireCsrfJson(): void
    {
        if (!$this->validateCsrf())
        {
            $this->jsonError(['error' => 'Token CSRF invalide'], 403);
        }
    }

    /**
     * Exige un token CSRF valide. Redirige avec erreur flash si invalide.
     */
    protected function requireCsrf(string $redirectUrl = '?page=home'): void
    {
        if (!$this->validateCsrf())
        {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg' => 'Token CSRF invalide.',
            ];
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Vérifie authentification + CSRF pour les requêtes AJAX.
     * Retourne l'ID utilisateur.
     */
    protected function requireAuthAndCsrfJson(): int
    {
        $userId = $this->requireAuthJson();
        $this->requireCsrfJson();

        return $userId;
    }

    /**
     * Démarre la session si nécessaire.
     */
    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
    }

    /**
     * Vérifie si la requête est une requête AJAX.
     */
    protected function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
