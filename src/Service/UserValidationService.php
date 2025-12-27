<?php

namespace App\Service;

use App\Model\User;

/**
 * UserValidationService - Gère la validation des données utilisateur
 * Responsabilités :
 * - Vérification d'unicité email/pseudo
 * - Requêtes de validation sans logique métier.
 */
class UserValidationService
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Vérifie si un email est déjà pris.
     */
    public function isEmailTaken(string $email): bool
    {
        $stmt = $this->userModel->getDb()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        return (bool)$stmt->fetch();
    }

    /**
     * Vérifie si un pseudo est déjà pris.
     */
    public function isPseudoTaken(string $pseudo): bool
    {
        $stmt = $this->userModel->getDb()->prepare('SELECT id FROM users WHERE pseudo = ? LIMIT 1');
        $stmt->execute([$pseudo]);

        return (bool)$stmt->fetch();
    }

    /**
     * Vérifie l'unicité pour l'API.
     */
    public function checkUniqueness(string $email = '', string $pseudo = ''): array
    {
        $response = ['email_taken' => false, 'pseudo_taken' => false];

        if ($email)
        {
            $response['email_taken'] = $this->isEmailTaken($email);
        }

        if ($pseudo)
        {
            $response['pseudo_taken'] = $this->isPseudoTaken($pseudo);
        }

        return $response;
    }
}
