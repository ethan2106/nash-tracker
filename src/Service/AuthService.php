<?php

namespace App\Service;

use App\Model\User;

/**
 * AuthService - Gère l'authentification des utilisateurs
 * Responsabilités :
 * - Login/register via UserModel
 * - Gestion des sessions utilisateur
 * - Cookies "remember me"
 * - Logout sécurisé
 */
class AuthService
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Authentifie un utilisateur et gère la session
     */
    public function login(array $data): array
    {
        // Si déjà connecté, redirige
        if (!empty($_SESSION['user'])) {
            header('Location: ?page=home');
            exit;
        }

        $result = $this->userModel->login($data);

        if ($result['success'] && isset($result['user'])) {
            $this->createUserSession($result['user']);
        }

        return $result;
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function register(array $data): array
    {
        return $this->userModel->register($data);
    }

    /**
     * Déconnecte l'utilisateur de manière sécurisée
     */
    public function logout(): void
    {
        // Supprimer l'utilisateur de la session
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }

        // Prévenir la fixation de session
        session_regenerate_id(true);

        // Régénérer le token CSRF
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        } catch (\Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(24));
        }
    }

    /**
     * Gère le cookie "remember me"
     */
    public function handleRememberMe(array $postData): void
    {
        if (isset($postData['remember']) && $postData['remember'] == '1') {
            setcookie('remember_email', $postData['email'], time() + 3600 * 24 * 30, '/', '', true, true);
        }
    }

    /**
     * Récupère l'email mémorisé depuis le cookie
     */
    public function getRememberedEmail(): string
    {
        return $_COOKIE['remember_email'] ?? '';
    }

    /**
     * Crée la session utilisateur après login réussi
     */
    private function createUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'] ?? '',
            'date_inscription' => $user['date_inscription'] ?? null,
        ];
    }
}