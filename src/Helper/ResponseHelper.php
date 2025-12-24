<?php

namespace App\Helper;

/**
 * ResponseHelper - Helper pour gérer les réponses HTTP et les messages flash
 * Responsabilités :
 * - Gérer les messages flash dans la session
 * - Gérer les redirections HTTP
 * - Formater les réponses pour les vues.
 */
class ResponseHelper
{
    /**
     * Ajouter un message flash de succès.
     */
    public static function addSuccessMessage(string $message): void
    {
        self::setFlashMessage('success', $message);
    }

    /**
     * Ajouter un message flash d'erreur.
     */
    public static function addErrorMessage(string $message): void
    {
        self::setFlashMessage('error', $message);
    }

    /**
     * Définir un message flash.
     */
    public static function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'msg' => $message,
        ];
    }

    /**
     * Rediriger vers une URL.
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Rediriger vers la page food avec le type de repas.
     */
    public static function redirectToFood(?string $mealType = null): void
    {
        $url = $mealType && $mealType !== 'repas' ? '?page=food&meal_type=' . urlencode($mealType) : '?page=food';
        self::redirect($url);
    }

    /**
     * Rediriger vers la page meals.
     */
    public static function redirectToMeals(): void
    {
        self::redirect('?page=meals');
    }

    /**
     * Rediriger vers la page catalog.
     */
    public static function redirectToCatalog(): void
    {
        self::redirect('?page=catalog');
    }

    /**
     * Traiter le résultat d'une opération et gérer la réponse.
     */
    public static function handleOperationResult(array $result, ?string $successRedirect = null, ?string $errorRedirect = null): void
    {
        if (isset($result['success']) && $result['success'])
        {
            self::addSuccessMessage($result['message'] ?? 'Opération réussie');
            if ($successRedirect)
            {
                self::redirect($successRedirect);
            }
        } else
        {
            self::addErrorMessage($result['error'] ?? 'Erreur lors de l\'opération');
            if ($errorRedirect)
            {
                self::redirect($errorRedirect);
            }
        }
    }

    /**
     * Retourner une réponse JSON.
     */
    public static function jsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Rediriger avec un message flash.
     */
    public static function redirectWithFlash(string $page, string $type, string $message): void
    {
        self::setFlashMessage($type, $message);
        self::redirect("?page=$page");
    }

    /**
     * Vérifier le token CSRF.
     */
    public static function validateCsrfToken(string $token): bool
    {
        return !empty($token) &&
               isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }
}
