<?php

namespace App\Helper;

/**
 * JsonResponseTrait - Fournit des méthodes utilitaires pour les réponses JSON.
 */
trait JsonResponseTrait
{
    /**
     * Envoie une réponse JSON de succès.
     */
    protected function jsonSuccess(array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    /**
     * Envoie une réponse JSON d'erreur.
     */
    protected function jsonError(array $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        $response = ['success' => false];
        $response = array_merge($response, $message);
        echo json_encode($response);
        exit;
    }

    /**
     * Envoie une réponse JSON générique.
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
