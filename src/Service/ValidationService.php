<?php

namespace App\Service;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

/**
 * Service de validation centralisée utilisant Respect Validation.
 * Fournit des règles de validation réutilisables et des erreurs normalisées.
 */
class ValidationService
{
    /**
     * Valide les données d'inscription utilisateur.
     *
     * @param array $data
     * @return array Erreurs de validation (vide si valide)
     */
    public function validateUserRegistration(array $data): array
    {
        $errors = [];

        // Pseudo
        if (!v::stringType()->length(2, 50)->alnum('_-')->validate($data['pseudo'] ?? ''))
        {
            $errors['pseudo'] = 'Le pseudo doit contenir entre 2 et 50 caractères alphanumériques.';
        }

        // Email
        if (!v::email()->validate($data['email'] ?? ''))
        {
            $errors['email'] = 'Adresse email invalide.';
        }

        // Password
        if (!v::stringType()->length(8, 255)->validate($data['password'] ?? ''))
        {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        // Password confirm
        if (($data['password_confirm'] ?? '') !== ($data['password'] ?? ''))
        {
            $errors['password_confirm'] = 'La confirmation du mot de passe ne correspond pas.';
        }

        return $errors;
    }

    /**
     * Valide les données de connexion.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validateUserLogin(array $data): array
    {
        $errors = [];

        if (!v::email()->validate($data['email'] ?? ''))
        {
            $errors['email'] = 'Adresse email invalide.';
        }

        if (!v::stringType()->notEmpty()->validate($data['password'] ?? ''))
        {
            $errors['password'] = 'Mot de passe requis.';
        }

        return $errors;
    }

    /**
     * Valide l'email utilisateur.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validateEmail(array $data): array
    {
        $errors = [];

        if (!v::email()->validate($data['email'] ?? ''))
        {
            $errors['email'] = 'Adresse email invalide.';
        }

        return $errors;
    }

    /**
     * Valide le pseudo utilisateur.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validatePseudo(array $data): array
    {
        $errors = [];

        if (!v::stringType()->length(2, 50)->alnum('_-')->validate($data['pseudo'] ?? ''))
        {
            $errors['pseudo'] = 'Le pseudo doit contenir entre 2 et 50 caractères alphanumériques.';
        }

        return $errors;
    }

    /**
     * Valide les données de changement de mot de passe.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validatePasswordChange(array $data): array
    {
        $errors = [];

        if (!v::stringType()->notEmpty()->validate($data['current_password'] ?? ''))
        {
            $errors['current_password'] = 'Mot de passe actuel requis.';
        }

        if (!v::stringType()->length(8, 255)->validate($data['new_password'] ?? ''))
        {
            $errors['new_password'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        }

        if (($data['new_password_confirm'] ?? '') !== ($data['new_password'] ?? ''))
        {
            $errors['new_password_confirm'] = 'La confirmation du nouveau mot de passe ne correspond pas.';
        }

        return $errors;
    }

    /**
     * Valide la suppression de compte.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validateDeleteAccount(array $data): array
    {
        $errors = [];

        if (!v::stringType()->notEmpty()->validate($data['password'] ?? ''))
        {
            $errors['password'] = 'Mot de passe requis.';
        }

        if (($data['confirmation'] ?? '') !== 'SUPPRIMER')
        {
            $errors['confirmation'] = 'Veuillez taper SUPPRIMER pour confirmer.';
        }

        return $errors;
    }

    /**
     * Valide les données d'ajout d'aliment.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validateFoodAddition(array $data): array
    {
        $errors = [];

        if (!v::intType()->positive()->validate($data['aliment_id'] ?? 0))
        {
            $errors['aliment_id'] = 'ID d\'aliment invalide.';
        }

        if (!v::floatType()->positive()->max(5000)->validate($data['quantite_g'] ?? 0))
        {
            $errors['quantite_g'] = 'Quantité invalide (1-5000g).';
        }

        if (!v::in(['petit-dejeuner', 'dejeuner', 'gouter', 'diner', 'en-cas'])->validate($data['meal_type'] ?? ''))
        {
            $errors['meal_type'] = 'Type de repas invalide.';
        }

        return $errors;
    }

    /**
     * Valide les données d'objectif nutritionnel.
     *
     * @param array $data
     * @return array Erreurs de validation
     */
    public function validateNutritionGoals(array $data): array
    {
        $errors = [];

        if (!v::intType()->between(2020, 2030)->validate($data['annee'] ?? 0))
        {
            $errors['annee'] = 'Année invalide (2020-2030).';
        }

        if (!v::in(['H', 'F'])->validate($data['sexe'] ?? ''))
        {
            $errors['sexe'] = 'Sexe invalide (H ou F).';
        }

        if (!v::intType()->between(0, 120)->validate($data['age'] ?? 0))
        {
            $errors['age'] = 'Âge invalide (0-120).';
        }

        if (!v::intType()->between(50, 250)->validate($data['taille_cm'] ?? 0))
        {
            $errors['taille_cm'] = 'Taille invalide (50-250 cm).';
        }

        if (!v::floatType()->between(20, 300)->validate($data['poids_kg'] ?? 0))
        {
            $errors['poids_kg'] = 'Poids invalide (20-300 kg).';
        }

        if (!v::in(['sedentaire', 'leger', 'modere', 'actif', 'tres_actif'])->validate($data['activite'] ?? ''))
        {
            $errors['activite'] = 'Niveau d\'activité invalide.';
        }

        return $errors;
    }

    /**
     * Valide une valeur individuelle avec une règle donnée.
     *
     * @param mixed $value
     * @param callable $rule Fonction qui retourne un validateur Respect
     * @return string|null Message d'erreur ou null si valide
     */
    public function validateField($value, callable $rule): ?string
    {
        try
        {
            $validator = $rule();
            $validator->assert($value);

            return null;
        } catch (ValidationException $e)
        {
            return $e->getMessage();
        }
    }
}
